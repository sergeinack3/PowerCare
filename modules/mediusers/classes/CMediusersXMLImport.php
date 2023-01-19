<?php

/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers;

use DOMElement;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CMbSecurity;
use Ox\Core\Import\CMbXMLObjectImport;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CPermModule;
use Ox\Mediboard\Admin\CPermObject;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Cabinet\CTarif;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\CPreferences;

/**
 * Import class for Mediusers and Users from XML file
 */
class CMediusersXMLImport extends CMbXMLObjectImport
{
    /** @var string */
    protected $import_tag;

    /** @var array */
    protected $imported = [];

    /** @var string[] */
    protected $import_order = [
        "//object[@class='CFunctions']",

        "//object[@class='CUser']",

        "//object[@class='CDiscipline']",
        "//object[@class='CSpecialtyAsip']",
        "//object[@class='CSpecCPAM']",

        "//object[@class='CMediusers']",

        "//object[@class='CPlageconsult']",
        "//object[@class='CPlageOp']",

        "//object[@class='CPermModule']",
        "//object[@class='CPermObject']",
        "//object[@class='CPreferences']",

        "//object[@class='CIdSante400']",
        "//object[@class='CUniteFonctionnelle']",
        "//object[@class='CAffectationUniteFonctionnelle']",
        "//object[@class='CTarif']",
    ];

    /** @var string */
    protected $directory;

    /** @var string */
    protected $group_id;

    /** @var array */
    protected $permissions;

    /** @var array  */
    public static $already_imported = [];

    /** @var string[] */
    private static $_ignored_classes = ['CModule', 'CGroups'];

    /** @var array  */
    private static $modules = [];

    /**
     * @inheritdoc
     */
    public function importObject(DOMElement $element): void
    {
        if (!$element) {
            return;
        }

        $id = $element->getAttribute("id");

        if (isset($this->imported[$id])) {
            return;
        }

        $_class = $element->getAttribute("class");

        if (in_array($_class, self::$_ignored_classes)) {
            return;
        }

        $imported_object  = null;
        $this->import_tag = $this->getImportTag();
        $idex             = self::lookupObject($id, $this->import_tag);

        if (isset($this->options['ignore_find']) && $this->options['ignore_find']) {
            $idex      = new CIdSante400();
            $idex->tag = $this->import_tag;
        }
        $object = null;

        if ($idex->_id) {
            $this->imported[$id] = true;
            $this->map[$id]      = $idex->loadTargetObject()->_guid;
            if ($_class == 'CMediusers') {
                [, $_id] = explode('-', $id);
                $this->map['CUser-' . $_id] = 'CUser-' . self::getIdFromGuid($this->map[$id]);
            }

            if (!array_key_exists($_class, self::$already_imported)) {
                self::$already_imported[$_class] = 1;
            } else {
                self::$already_imported[$_class]++;
            }

            return;
        }

        $store_idx = true;
        switch ($_class) {
            case "CFunctions":
                $imported_object = $this->importFunction($element, $object);
                break;
            case "CMediusers":
                $imported_object = $this->importMediusers($element, $object);
                break;
            case "CUniteFonctionnelle":
                $imported_object = $this->importUF($element, $object);
                break;
            case "CPreferences":
                $imported_object = $this->importPreferences($element, $object);
                break;
            case "CPermModule":
                $imported_object = $this->importPermModule($element, $object);
                $store_idx       = false;
                break;
            case "CPermObject":
                $imported_object = $this->importPermObject($element, $object);
                $store_idx       = false;
                break;
            case "CUser":
                // Import profile
                $imported_object = $this->importUser($element, $object);
                break;
            case "CPlageconsult":
                $imported_object = $this->importPlage($element, $object);
                break;
            case "CTarif":
                $imported_object = $this->importTarif($element, $object);
                break;
            default:
                if (in_array($_class, self::$_ignored_classes)) {
                    $store_idx = false;
                    break;
                }

                $_object = $this->getObjectFromElement($element, $object);

                $_object->loadMatchingObjectEsc();

                if ($_object && $_object->_id) {
                    $imported_object = $_object;
                } elseif ($this->storeObject($_object)) {
                    $imported_object = $_object;
                }
        }

        if ($imported_object && $imported_object->_id && $store_idx) {
            $idex->setObject($imported_object);
            $idex->id400 = $id;

            if ($msg = $idex->store()) {
                CAppUI::stepAjax($msg, UI_MSG_WARNING);
            }
        } elseif (!in_array($_class, self::$_ignored_classes)) {
            CAppUI::stepAjax("common-no-object", UI_MSG_WARNING, CAppUI::tr($_class));
        }

        if ($imported_object) {
            $this->map[$id] = $imported_object->_guid;
        }

        $this->imported[$id] = true;
    }

    /**
     * @param string $directory Directory path
     *
     * @return void
     */
    public function setDirectory(string $directory): void
    {
        $this->directory = $directory;
    }

    /**
     * @param int $group_id CGroup id
     *
     * @return void
     */
    public function setGroupId(?int $group_id): void
    {
        $this->group_id = $group_id ?? CGroups::loadCurrent()->_id;
    }

    /**
     * Import a function from an XML element
     *
     * @param DOMElement $element XML element to import
     * @param CMbObject  $object  Object found
     *
     * @return CFunctions|CMbObject|null
     */
    private function importFunction(DOMElement $element, ?CMbObject $object): ?CFunctions
    {
        /** @var CFunctions $_func */
        $_func = $this->getObjectFromElement($element, $object);

        $tmp_func           = new CFunctions();
        $tmp_func->text     = $_func->text;
        $tmp_func->group_id = $this->group_id;
        $tmp_func->type     = $_func->type;

        $tmp_func->loadMatchingObjectEsc();

        if ($tmp_func && $tmp_func->_id) {
            return $tmp_func;
        }

        $_func->group_id = $this->group_id;

        // Do not create functions !
        if ($this->options['create_functions']) {
            if ($this->storeObject($_func)) {
                return $_func;
            }
        }

        return null;
    }

    /**
     * Import Mediusers from XML file
     *
     * @param DOMElement $element Mediuser to import
     * @param CMbObject  $object  Mediuser found
     *
     * @return CMbObject|CMediusers
     */
    private function importMediusers(DOMElement $element, ?CMbObject $object): ?CMediusers
    {
        /** @var CMediusers $_mediuser */
        $_mediuser = $this->getObjectFromElement($element, $object);

        $user_guid          = $_mediuser->user_id;
        $_mediuser->user_id = '';

        $_mediuser->loadMatchingObjectEsc();

        $new = (!$_mediuser->_id);
        if ($new) {
            $nodes = $this->xpath->query("//object[@id='$user_guid']");
            $elem  = $nodes->item(0);
            $user  = self::getValuesFromElement($elem);

            $tmp_usr                = new CUser();
            $tmp_usr->user_username = $user['user_username'];
            $tmp_usr->loadMatchingObjectEsc();

            if ($tmp_usr && $tmp_usr->_id) {
                $med = $tmp_usr->loadRefMediuser();
                if ($med && $med->_id) {
                    return $med;
                }
            }

            foreach ($user as $_field => $_value) {
                $field_name             = "_$_field";
                $_mediuser->$field_name = $_value;
            }

            $_mediuser->_user_password = CMbSecurity::getRandomPassword();

            if ($tmp_usr && $tmp_usr->_id) {
                $_mediuser->user_id = $tmp_usr->_id;
            }

            if ($msg = $_mediuser->store()) {
                CAppUI::stepAjax($msg, UI_MSG_WARNING);

                return null;
            }
        }

        $_user = $_mediuser->loadRefUser();

        // Create external ID for User
        $_idx = new CIdSante400();
        $_idx->setObject($_user);
        $_idx->id400 = $user_guid . '-' . $this->group_id;
        $_idx->tag   = $this->import_tag;

        if ($_msg = $_idx->store()) {
            CAppUI::stepAjax($_msg, UI_MSG_WARNING);
        }

        $this->map[$user_guid] = $user_guid;

        $_mediuser->updateFormFields();

        if (!$new) {
            CAppUI::stepAjax(CAppUI::tr("CMediusers-msg-create") . " : " . $_mediuser->_view, UI_MSG_OK);
        } else {
            CAppUI::stepAjax(CAppUI::tr("CMediusers-msg-modify") . " : " . $_mediuser->_view, UI_MSG_OK);
        }

        return $_mediuser;
    }

    /**
     * Import an UF from XML file
     *
     * @param DOMElement $element UF to import
     * @param CMbObject  $object  UF found
     *
     * @return CMbObject|CUniteFonctionnelle|null
     */
    private function importUF(DOMElement $element, ?CMbObject $object): ?CUniteFonctionnelle
    {
        /** @var CUniteFonctionnelle $_uf */
        $_uf = $this->getObjectFromElement($element, $object);

        $tmp_uf           = new CUniteFonctionnelle();
        $tmp_uf->code     = $_uf->code;
        $tmp_uf->libelle  = $_uf->libelle;
        $tmp_uf->type     = $_uf->type;
        $tmp_uf->group_id = $this->group_id;

        $tmp_uf->loadMatchingObjectEsc();

        if ($tmp_uf && $tmp_uf->_id) {
            return $tmp_uf;
        }

        $_uf->group_id = $this->group_id;

        if ($this->options['create_ufs']) {
            if ($this->storeObject($_uf)) {
                return $_uf;
            }
        }

        return null;
    }

    /**
     * Import an UF from XML file
     *
     * @param DOMElement $element tarif to import
     * @param CMbObject  $object  tarif found
     *
     * @return CMbObject|CTarif|null
     */
    private function importTarif(DOMElement $element, ?CMbObject $object): ?CTarif
    {
        /** @var CTarif $_tarif */
        $_tarif = $this->getObjectFromElement($element, $object);

        $tmp_tarif = new CTarif();
        if ($_tarif->chir_id) {
            $tmp_tarif->chir_id = $_tarif->chir_id;
        } elseif ($_tarif->function_id) {
            $tmp_tarif->function_id = $_tarif->function_id;
        } elseif ($_tarif->group_id) {
            $tmp_tarif->group_id = $_tarif->group_id;
        }

        $tmp_tarif->description = $_tarif->description;

        $tmp_tarif->loadMatchingObjectEsc();

        if ($tmp_tarif && $tmp_tarif->_id && !$this->options['update_tarification']) {
            return $tmp_tarif;
        }

        $_tarif->loadMatchingObjectEsc();

        if ($this->storeObject($_tarif)) {
            return $_tarif;
        }

        return null;
    }

    /**
     * Import Preference from XML file
     *
     * @param DOMElement $element Preference to import
     * @param CMbObject  $object  Preference found
     *
     * @return CMbObject|CPreferences|null
     */
    private function importPreferences(DOMElement $element, ?CMbObject $object): ?CPreferences
    {
        if (!$this->options['prefs'] && !$this->options['perms_functionnal'] && !$this->options['default_prefs']) {
            return null;
        }

        /** @var CPreferences $_pref */
        $_pref = $this->getObjectFromElement($element, $object);

        if (!$this->options['default_prefs'] || $_pref->user_id || $_pref->restricted) {
            // Pas d'import des permission fonctionnelles si la case n'est pas cochée
            if (!$this->options['perms_functionnal'] && $_pref->restricted == 1) {
                return null;
            }

            // Pas d'import des préférences normales is la case n'est pas cochée
            if (!$this->options['prefs'] && $_pref->restricted == 0) {
                return null;
            }
        }

        $_value       = $_pref->value;
        $_pref->value = null;
        $_pref->loadMatchingObjectEsc();

        if (
            $_pref->_id && (($_pref->restricted && !$this->options['update_perms_functionnal'])
            || (!$_pref->restricted && $_pref->user_id && !$this->options['update_prefs'])
            || (!$_pref->user_id && !$this->options['update_default_prefs']))
        ) {
            return $_pref;
        }

        // Can't use &bull; in the XML file
        $_pref->value = ($_value == '/htmlbull/') ? '&bull;' : $_value;

        if ($this->storeObject($_pref)) {
            return $_pref;
        }

        return null;
    }

    /**
     * Import a perm on a class from XML file
     *
     * @param DOMElement $element Perm to import
     * @param CMbObject  $object  Perm found
     *
     * @return CMbObject|CPermObject|null
     */
    private function importPermObject(DOMElement $element, ?CMbObject $object): ?CPermObject
    {
        /** @var CPermObject $_perm */
        $_perm = $this->getObjectFromElement($element, $object);

        if (($_perm && $_perm->object_id) || !class_exists($_perm->object_class)) {
            return null;
        }

        $_perm->loadMatchingObjectEsc();
        if ($_perm && $_perm->_id) {
            return $_perm;
        }

        if ($this->storeObject($_perm)) {
            return $_perm;
        }

        return null;
    }

    /**
     * Import a perm for a module from XML file
     *
     * @param DOMElement $element Perm to import
     * @param CMbObject  $object  Perm found
     *
     * @return CMbObject|CPermModule|null
     */
    private function importPermModule(DOMElement $element, ?CMbObject $object): ?CPermModule
    {
        /** @var CPermModule $_perm */
        $_perm = $this->getObjectFromElement($element, $object);

        $mod_id = null;
        if ($_perm->mod_id) {
            $nodes  = $this->xpath->query("//object[@id='$_perm->mod_id']");
            $elem   = $nodes->item(0);
            $module = self::getValuesFromElement($elem);

            $_mod           = new CModule();
            $_mod->mod_name = $module['mod_name'];
            $_mod->loadMatchingObjectEsc();

            if (!$_mod || !$_mod->_id && !CModule::getInstalled($_mod->mod_name)) {
                CAppUI::stepAjax('CUser-import-module-not-exists', UI_MSG_WARNING, $module['mod_name']);

                return null;
            }
            $mod_id = $_mod->_id;
        }


        $_perm->mod_id = $mod_id;
        $_perm->loadMatchingObjectEsc();

        if ($_perm && $_perm->_id) {
            return $_perm;
        }

        if ($this->storeObject($_perm)) {
            return $_perm;
        }

        return null;
    }

    /**
     * Import a CPlageConsult or CPlageOp from a XML element
     *
     * @param DOMElement $element XML element
     * @param CMbObject  $object  Object found
     *
     * @return CMbObject|CPlageconsult|CPlageOp|mixed|null
     */
    private function importPlage(DOMElement $element, ?CMbObject $object): ?CPlageconsult
    {
        /** @var CPlageOp|CPlageconsult $_plage */
        $_plage = $this->getObjectFromElement($element, $object);

        $_plage->hasCollisions();

        if (count($_plage->_colliding_plages)) {
            $_plage = reset($_plage->_colliding_plages);
            CAppUI::stepAjax("%s '%s' retrouvée", UI_MSG_OK, CAppUI::tr($_plage->_class), $_plage->_view);
        } else {
            if (!$this->storeObject($_plage)) {
                return null;
            }
        }

        return $_plage;
    }


    /**
     * Import a profile from an XML file
     *
     * @param DOMElement $element Profile to import
     * @param CMbObject  $object  Profile found
     *
     * @return CMbObject|CUser|null
     */
    private function importUser(DOMElement $element, ?CMbObject $object): ?CUser
    {
        /** @var CUser $_user */
        $_user = $this->getObjectFromElement($element, $object);

        if (isset($this->options['new_name']) && $this->options['new_name']) {
            $_user->user_username  = $this->options['new_name'];
            $_user->user_last_name = $this->options['new_name'];
        }

        $_user->loadMatchingObjectEsc();

        $new = ($_user->_id) ? 0 : 1;

        if ($new) {
            $_user->user_password = CMbSecurity::getRandomPassword();
        }

        if ($msg = $_user->store()) {
            CAppUI::stepAjax($msg, UI_MSG_WARNING);

            return null;
        }

        $_user->updateFormFields();

        if ($new) {
            CAppUI::stepAjax(CAppUI::tr("CUser-msg-create") . " : " . $_user->_view, UI_MSG_OK);
        } else {
            CAppUI::stepAjax(CAppUI::tr("CUser-msg-modify") . " : " . $_user->_view, UI_MSG_OK);
        }


        return $_user;
    }

    /**
     * Get an existing profile from XML element
     *
     * @param bool $load_matching Try to load an existing object corresponding
     *
     * @return CUser|null
     */
    public function getProfileFromXML(bool $load_matching = true): ?CUser
    {
        $objects = $this->xpath->query("//object[@class='CUser']");

        foreach ($objects as $_elem) {
            /** @var CUser $_user */
            $_user = $this->getObjectFromElement($_elem, null);

            if ($_user->template !== '1') {
                continue;
            }

            if (!$load_matching) {
                return $_user;
            }

            $profile                = new CUser();
            $profile->user_username = $_user->user_username;
            $profile->template      = 1;
            $profile->loadMatchingObjectEsc();

            if ($profile && $profile->_id) {
                return $profile;
            }

            return $_user;
        }

        return null;
    }

    /**
     * @param string $hash_type Type of hash to get from XML
     *
     * @return string
     */
    public function getHashFromXML(string $hash_type): string
    {
        $hash = $this->xpath->query("//hash[@hash_name='$hash_type']");

        return ($hash->item(0)) ? $hash->item(0)->getAttribute('hash_value') : '';
    }

    /**
     * Compare an existing profile with a profile in the XML file.
     * CPermObject, CPermModule and CPreferences are compared
     *
     * @return array
     */
    public function compareProfileFromXML(): array
    {
        $compare = [
            'new_profile' => $this->getProfileFromXML(false),
            'old_profile' => $this->getProfileFromXML(),
        ];

        $new_perms_object = $this->getObjectsFromXML('CPermObject', 'user_id', true);
        $new_perms_mod    = $this->getPermsModuleFromXML();
        $new_prefs        = $this->getObjectsFromXML('CPreferences', 'user_id', true);

        /** @var CUser $old_user */
        $old_user = $compare['old_profile'];

        $compare['perms_object']      = $this->comparePermsObject($old_user, $new_perms_object);
        $compare['perms_module']      = $this->comparePermsModule($old_user, $new_perms_mod);
        $compare['prefs']             = $this->comparePrefs($old_user, $new_prefs);
        $compare['perms_functionnal'] = $this->comparePrefs($old_user, $new_prefs, 1);

        return $compare;
    }

    /**
     * Get a collection of objects from an XML file
     *
     * @param string $class       Class of the collection to get
     * @param string $user_field  Field to nullify from the object
     * @param bool   $ignore_refs Ignore references or load references
     *
     * @return array
     */
    private function getObjectsFromXML(string $class, ?string $user_field = null, bool $ignore_refs = false): array
    {
        $nodes = $this->getElementsByClass($class);

        if (!$nodes) {
            return [];
        }

        $objects = [];
        foreach ($nodes as $_elem) {
            $_obj = $this->getObjectFromElement($_elem, null, $ignore_refs);
            if ($user_field) {
                $_obj->$user_field = null;
            }

            $objects[$_elem->getAttribute("id")] = $_obj;
        }

        return $objects;
    }

    /**
     * Get the CPermModule objects from an XML file
     *
     * @return array
     */
    function getPermsModuleFromXML()
    {
        $perms = $this->getObjectsFromXML('CPermModule', 'user_id', true);

        $nodes_mod = $this->getElementsByClass('CModule');

        $modules = [];
        foreach ($nodes_mod as $_mod) {
            $values = self::getValuesFromElement($_mod);

            $module           = new CModule();
            $module->mod_name = $values['mod_name'];
            $module->loadMatchingObjectEsc();

            if (!$module || !$module->_id) {
                CAppUI::stepAjax('system-msg-The %s module is not installed', UI_MSG_WARNING, $values['mod_name']);
                continue;
            }

            self::$modules[$module->_id]        = $module->mod_name;
            $modules[$_mod->getAttribute('id')] = $module->_id;
        }

        $perms_module = [];
        foreach ($perms as $_key => $_perm) {
            if (array_key_exists($_perm->mod_id, $modules)) {
                $_perm->mod_id       = $modules[$_perm->mod_id];
                $perms_module[$_key] = $_perm;
            }
        }

        return $perms;
    }

    /**
     * Compare CPermObject from a user and from a XML file
     *
     * @param CUser $user             User perms have to be compare for
     * @param array $new_perms_object Perms existing in the XML file
     *
     * @return array
     */
    function comparePermsObject($user, $new_perms_object)
    {
        $perms_object = [];

        foreach ($new_perms_object as $_perm) {
            if (!array_key_exists($_perm->object_class, $perms_object)) {
                $perms_object[$_perm->object_class] = [
                    'new' => $_perm->permission,
                    'old' => -1,
                ];
            }
        }

        if ($user && $user->_id) {
            $perm_obj         = new CPermObject();
            $where            = [
                'user_id'   => "= $user->_id",
                'object_id' => "IS NULL",
            ];
            $old_perms_object = $perm_obj->loadList($where);

            foreach ($old_perms_object as $_perm) {
                if (!array_key_exists($_perm->object_class, $perms_object) && $_perm->permission != 0) {
                    $perms_object[$_perm->object_class] = [
                        'new' => -1,
                        'old' => -1,
                    ];
                }

                if (array_key_exists($_perm->object_class, $perms_object)) {
                    $perms_object[$_perm->object_class]['old'] = $_perm->permission;
                }
            }
        }

        return $perms_object;
    }

    /**
     * Compare CPermModule from a user and from a XML file
     *
     * @param CUser $user             User perms have to be compare for
     * @param array $new_perms_module Perms existing in the XML file
     *
     * @return array
     */
    function comparePermsModule($user, $new_perms_module)
    {
        $perms_module = [];

        foreach ($new_perms_module as $_perm) {
            if (!$_perm->mod_id) {
                $perms_module['all'] = [
                    'new' => [
                        'perm' => $_perm->permission,
                        'view' => $_perm->view,
                    ],
                    'old' => [
                        'perm' => 0,
                        'view' => 0,
                    ],
                ];
            } elseif (array_key_exists($_perm->mod_id, self::$modules) && !array_key_exists(
                    self::$modules[$_perm->mod_id],
                    $perms_module
                )) {
                $perms_module[self::$modules[$_perm->mod_id]] = [
                    'new' => [
                        'perm' => $_perm->permission,
                        'view' => $_perm->view,
                    ],
                    'old' => [
                        'perm' => 0,
                        'view' => 0,
                    ],
                ];
            }
        }

        if ($user && $user->_id) {
            $perm_module      = new CPermModule();
            $where            = [
                'user_id' => "= $user->_id",
            ];
            $old_perms_module = $perm_module->loadList($where);

            foreach ($old_perms_module as $_perm) {
                $module = 'all';

                if ($_perm->mod_id) {
                    if (!array_key_exists($_perm->mod_id, self::$modules)) {
                        $mod = new CModule();
                        $mod->load($_perm->mod_id);
                        self::$modules[$mod->_id] = $mod->mod_name;
                    }

                    $module = self::$modules[$_perm->mod_id];
                }


                if (!array_key_exists($module, $perms_module) && ($_perm->permission != 0 || $_perm->_view != 0)) {
                    $perms_module[$module] = [
                        'new' => [
                            'perm' => 0,
                            'view' => 0,
                        ],
                        'old' => [
                            'perm' => $_perm->permission,
                            'view' => $_perm->view,
                        ],
                    ];
                }

                if (array_key_exists($module, $perms_module)) {
                    $perms_module[$module]['old'] = [
                        'perm' => $_perm->permission,
                        'view' => $_perm->view,
                    ];
                }
            }
        }

        return $perms_module;
    }

    /**
     * Compare user prefs with prefs existing in the XML file
     *
     * @param CUser $user        User prefs have to be compare for
     * @param array $new_prefs   Preferences from the XML file
     * @param int   $functionnal Preferences or perms functionnal
     *
     * @return array
     */
    function comparePrefs($user, $new_prefs, $functionnal = 0)
    {
        $prefs = [];

        foreach ($new_prefs as $_pref) {
            if ($_pref->restricted != $functionnal) {
                continue;
            }

            if (!array_key_exists($_pref->key, $prefs)) {
                $prefs[$_pref->key] = [
                    'old' => -1,
                    'new' => $_pref->value,
                ];
            }
        }

        if ($user && $user->_id) {
            $pref      = new CPreferences();
            $where     = [
                'user_id'    => "= $user->_id",
                'restricted' => "= '$functionnal'",
                'value'      => "IS NOT NULL",
            ];
            $old_prefs = $pref->loadList($where);


            foreach ($old_prefs as $_pref) {
                if (!array_key_exists($_pref->key, $prefs) && $_pref->value != '') {
                    $prefs[$_pref->key] = [
                        'old' => $_pref->value,
                        'new' => -1,
                    ];
                }

                if (array_key_exists($_pref->key, $prefs)) {
                    $prefs[$_pref->key]['old'] = $_pref->value;
                }
            }
        }

        return $prefs;
    }

    /**
     * Update a profile's perms
     *
     * @param array $permissions Collection of perms
     * @param array $options     Import's options
     *
     * @return void
     */
    function updateProfile($permissions = [], $options = [])
    {
        $this->permissions = $this->parsePermissions($permissions);
        $this->options     = $options;

        $profile = $this->getProfileFromXML();

        if (!$profile || !$profile->_id) {
            CAppUI::stepAjax('CUser-import-profile-not-exists', UI_MSG_ERROR);
        }

        if ($this->options['perms']) {
            $this->updatePermsModule($profile);
            $this->updatePermsObject($profile);
        }

        if ($this->options['prefs']) {
            $this->updatePrefs($profile);
        }

        if ($this->options['perms_functionnal']) {
            $this->updatePrefs($profile, '1');
        }
    }

    /**
     * Update the CPermModule for $profile
     *
     * @param CUser $profile The profile perms have to be update for
     *
     * @return void
     */
    function updatePermsModule($profile)
    {
        $modules_nodes = $this->xpath->query("//object[@class='CModule']");
        $xml_modules   = [];

        /** @var DOMElement $_node */
        foreach ($modules_nodes as $_node) {
            $xml_modules[$_node->getAttribute('id')] = $this->getObjectFromElement($_node, null, true);
        }

        foreach ($this->permissions['perms_module'] as $_mod => $_perms) {
            if ($_perms['perm'] == 'old' && $_perms['view'] == 'old') {
                continue;
            }

            // Get the module
            if ($_mod == 'all') {
                $mod_id = null;
            } else {
                $module           = new CModule();
                $module->mod_name = $_mod;
                $module->loadMatchingObjectEsc();

                $mod_id = $module->_id;
            }

            $xml_perm = null;
            if ($_mod == 'all') {
                $perm_node = $this->xpath->query("//object[not(@mod_id) and @class='CPermModule']");
                $xml_perm  = $this->getObjectFromElement($perm_node->item(0), null, true);
            } else {
                // Get the CPermModule from the XML
                foreach ($xml_modules as $_id => $_xml_mod) {
                    if ($_xml_mod->mod_name == $_mod) {
                        $perm_node = $this->xpath->query("//object[@mod_id='$_id' and @class='CPermModule']");
                        /** @var CPermModule $xml_perm */
                        $xml_perm = $this->getObjectFromElement($perm_node->item(0), null, true);
                        break;
                    }
                }
            }

            // Get the CPermModule from Mediboard (create it if not exists)
            $perm_mod          = new CPermModule();
            $perm_mod->mod_id  = $mod_id;
            $perm_mod->user_id = $profile->_id;
            $perm_mod->loadMatchingObjectEsc();

            if (!$xml_perm) {
                // Delete an existing CPermModule
                $perm_mod->permission = 0;
                $perm_mod->view       = 0;
            } else {
                // Set the permission field
                if ($_perms['perm'] == 'new') {
                    $perm_mod->permission = $xml_perm->permission;
                } elseif (!$perm_mod->_id) {
                    $perm_mod->permission = 0;
                }

                // Set the view field
                if ($_perms['view'] == 'new') {
                    $perm_mod->view = $xml_perm->view;
                } elseif (!$perm_mod->_id) {
                    $perm_mod->view = 0;
                }
            }

            $new = !$perm_mod->_id;

            if ($msg = $perm_mod->store()) {
                CAppUI::stepAjax($msg, UI_MSG_WARNING);
            } else {
                $msg = $new ? 'CPermModule-msg-create' : 'CPermModule-msg-modify';
                CAppUI::stepAjax($msg, UI_MSG_OK);
            }
        }
    }

    /**
     * Update the CPermObject for $profile
     *
     * @param CUser $profile The profile perms have to be update for
     *
     * @return void
     */
    function updatePermsObject($profile)
    {
        $perm_objects_nodes = $this->xpath->query("//object[@class='CPermObject']");
        $perms_xml          = [];
        foreach ($perm_objects_nodes as $_node) {
            $perms_xml[] = $this->getObjectFromElement($_node, null, true);
        }

        foreach ($this->permissions['perms_object'] as $_class => $_perm) {
            if ($_perm == 'old') {
                continue;
            }

            $perm_obj = new CPermObject();
            $where    = [
                'object_id'    => 'IS NULL',
                'object_class' => "= '$_class'",
                'user_id'      => "= $profile->_id",
            ];

            $new = false;
            if (!$perm_obj->loadObject($where)) {
                $new                    = true;
                $perm_obj->object_class = $_class;
                $perm_obj->user_id      = $profile->_id;
            }

            $xml_exists = false;
            foreach ($perms_xml as $_perm_xml) {
                if ($_perm_xml->object_class == $_class) {
                    $perm_obj->permission = $_perm_xml->permission;
                    $xml_exists           = true;
                    break;
                }
            }

            if (!$xml_exists && $perm_obj->_id) {
                $perm_obj->permission = 0;
            }

            if ($msg = $perm_obj->store()) {
                CAppUI::stepAjax($msg, UI_MSG_WARNING);
            } else {
                $msg = $new ? 'CPermObject-msg-create' : 'CPermObject-msg-modify';
                CAppUI::stepAjax($msg, UI_MSG_OK);
            }
        }
    }

    /**
     * Update the CPreferences for $profile
     *
     * @param CUser  $profile    The profile prefs have to be update for
     * @param string $restricted Prefs or perms functionnal
     *
     * @return void
     */
    function updatePrefs($profile, $restricted = '0')
    {
        $prefs_xml_nodes = $this->xpath->query("//object[@class='CPreferences']");
        $prefs_xml       = [];
        foreach ($prefs_xml_nodes as $_nodes) {
            $prefs_xml[] = $this->getObjectFromElement($_nodes, null, true);
        }

        $prefs = ($restricted == '0') ? $this->permissions['preferences'] : $this->permissions['perms_functionnal'];
        foreach ($prefs as $_pref => $_value) {
            if ($_value == 'old') {
                continue;
            }

            $pref             = new CPreferences();
            $pref->restricted = $restricted;
            $pref->user_id    = $profile->_id;
            $pref->key        = $_pref;
            $pref->loadMatchingObjectEsc();

            $new = !$pref->_id;

            $xml_exists = false;
            foreach ($prefs_xml as $_pref_xml) {
                if ($_pref_xml->key == $_pref) {
                    $pref->value = $_pref_xml->value;
                    $xml_exists  = true;
                    break;
                }
            }

            if (!$xml_exists && $pref->_id) {
                $pref->value = '';
            }

            if ($msg = $pref->store()) {
                CAppUI::stepAjax($msg, UI_MSG_WARNING);
            } else {
                $msg = $new ? 'CPreferences-msg-create' : 'CPreferences-msg-modify';
                CAppUI::stepAjax($msg, UI_MSG_OK);
            }
        }
    }

    /**
     * Parse permissions and preferences to prepare the profile update
     *
     * @param array $permissions Permissions and preferences from the compare form
     *
     * @return array
     */
    function parsePermissions($permissions)
    {
        $objects = [
            'perms_module'      => $this->sanitizePermsModule(
                $permissions['perms_module'],
                $permissions['perms_module_view']
            ),
            'perms_object'      => $this->sanitizeOthers($permissions['perms_object'], 'use_', '_perms'),
            'preferences'       => $this->sanitizeOthers($permissions['preferences'], 'use_prefs_', '_prefs'),
            'perms_functionnal' => $this->sanitizeOthers(
                $permissions['perms_functionnal'],
                'use_perms_functionnal_',
                '_prefs'
            ),
        ];

        return $objects;
    }

    /**
     * Sanitize the CPermObject, CPreferences
     *
     * @param array  $objects Objects to sanitize
     * @param string $prefix  Prefixe to remove
     * @param string $suffix  Suffixe to remove
     *
     * @return array
     */
    function sanitizeOthers($objects, $prefix, $suffix)
    {
        $objects_return = [];

        foreach ($objects as $_obj) {
            $_obj    = str_replace([$prefix, $suffix], '', $_obj);
            $feature = explode('|', $_obj);

            if (!array_key_exists($feature[0], $objects_return)) {
                $objects_return[$feature[0]] = $feature[1];
            }
        }

        return $objects_return;
    }

    /**
     * Sanitize the CPermModule
     *
     * @param array $perms_module      Perms to sanitize
     * @param array $perms_module_view Perms on view to sanitize
     *
     * @return array
     */
    function sanitizePermsModule($perms_module, $perms_module_view)
    {
        $perms_mod = [];

        foreach ($perms_module as $_perm) {
            $feature = explode('|', $_perm);
            $module  = explode('_', $feature[0]);

            if (!array_key_exists($module[1], $perms_mod)) {
                $perms_mod[$module[1]] = [
                    'perm' => $feature[1],
                    'view' => '',
                ];
            }
        }

        foreach ($perms_module_view as $_view) {
            $feature = explode('|', $_view);
            $module  = explode('_', $feature[0]);

            $perms_mod[$module[1]]['view'] = $feature[1];
        }

        return $perms_mod;
    }

    public static function setIgnoredClasses(array $options): void
    {
        if (!CMbArray::get($options, 'profile')) {
            CMediusersXMLImport::$_ignored_classes[] = 'CUser';
        }

        if (!CMbArray::get($options, 'perms')) {
            CMediusersXMLImport::$_ignored_classes[] = 'CPermObject';
            CMediusersXMLImport::$_ignored_classes[] = 'CPermModule';
        }

        if (!CMbArray::get($options, 'planning')) {
            CMediusersXMLImport::$_ignored_classes[] = 'CPlageconsult';
        }
    }
}
