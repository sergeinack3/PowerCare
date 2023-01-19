<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Forms;

use Exception;
use Ox\AppFine\Client\CAppFineClient;
use Ox\Components\Cache\Exceptions\CouldNotGetCache;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbMath;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CMbPath;
use Ox\Core\CMbString;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\FieldSpecs\CBoolSpec;
use Ox\Core\FieldSpecs\CEnumSpec;
use Ox\Core\FieldSpecs\CNumSpec;
use Ox\Core\FieldSpecs\CSetSpec;
use Ox\Core\Logger\LoggerLevels;
use Ox\Core\Module\CModule;
use Ox\Core\Mutex\CMbMutex;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Forms\CExClassPicture;
use Ox\Mediboard\Forms\Converter\ExObjectPDFConverter;
use Ox\Mediboard\Forms\Tag\CExClassFieldTagItem;
use Ox\Mediboard\Hospi\CTransmissionMedicale;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CBMRBHRe;
use Ox\Mediboard\Patients\CConstantComment;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CRedon;
use Ox\Mediboard\Patients\IGroupRelated;
use Ox\Mediboard\Patients\IPatientRelated;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanSoins\CAdministration;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\Search\CSearchIndexing;
use Ox\Mediboard\Search\IIndexableObject;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceSMTP;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

/**
 * Form data
 *
 * Classe qui définit la manière de stocker les instances de formulaires, il y a autant de tables "ex_object_XXX" que
 * de CExClass
 *
 * Cette classe redéfinit bon nombre de méthodes de CStoredObject à cause de la gestion des ces tables
 */
class CExObject extends CMbObject implements IPatientRelated, IIndexableObject
{
    /** @var string */
    public const CACHE_KEY = 'exclass-locales-';

    /** @var string */
    private const PDF_FILENAME = 'ex_object_pdf.pdf';

    public $ex_object_id;

    /**
     * @var int Group ID
     * @deprecated
     */
    public $group_id;

    /**
     * @var string First reference class
     * @deprecated
     */
    public $reference_class;

    /**
     * @var int First reference ID
     * @deprecated
     */
    public $reference_id;

    /**
     * @var string Second reference class
     * @deprecated
     */
    public $reference2_class;

    /**
     * @var int Second reference ID
     * @deprecated
     */
    public $reference2_id;

    /**
     * @var string Additionnal reference class
     * @deprecated
     */
    public $additional_class;

    /**
     * @var int Additionnal reference ID
     * @deprecated
     */
    public $additional_id;

    public $datetime_create;
    public $datetime_edit;
    public $owner_id;

    public $completeness_level;
    public $nb_alert_fields;

    public $object_class;
    public $object_id;
    public $_ref_object;

    public $_ex_class_id;
    public $_own_ex_class_id;
    public $_specs_already_set = false;
    public $_native_views;
    public $_event_name;
    public $_pictures_data;
    public $_quick_access_creation;
    public $_hidden_fields;
    public $_verified;

    /** @var CExClass */
    public $_ref_ex_class;

    /** @var CMbObject */
    public $_ref_reference_object_1;

    /** @var CMbObject */
    public $_ref_reference_object_2;

    /** @var CMbObject */
    public $_ref_additional_object;

    /** @var CMediusers */
    public $_ref_owner;

    /** @var CGroups */
    public $_ref_group;

    /** @var CPatient */
    public $_rel_patient;

    /** @var array Array of CExClassHostField->field => CMbObject */
    public $_ref_host_fields = [];

    public $_reported_fields           = [];
    public $_fields_display            = [];
    public $_fields_display_struct     = [];
    public $_fields_default_properties = [];
    public $_formula_result;

    static $_load_lite     = false;
    static $_multiple_load = false;

    static $_ex_specs = [];

    static $_locales_ready         = false;
    static $_locales_cache_enabled = true;

    /**
     * Custom constructor
     *
     * @param int $ex_class_id CExClass id
     */
    function __construct($ex_class_id = null)
    {
        parent::__construct();

        if (self::$_multiple_load) {
            $class = CClassMap::getInstance()->getShortName($this);

            unset(self::$spec[$class]);
            unset(self::$props[$class]);
            unset(self::$specs[$class]);
            unset(self::$backProps[$class]);
            unset(self::$backSpecs[$class]);
        }

        if ($ex_class_id) {
            if (is_string($ex_class_id) && str_starts_with($ex_class_id, 'CExClass-')) {
                [, $ex_class_id] = explode('-', $ex_class_id);
            }

            $this->setExClass($ex_class_id);
        }
    }

    /**
     * Sets the CExClass ID of $this
     * The CExLinks are not declared as backrefs as it's not compatible with CExObject
     *
     * @param int $ex_class_id CExClass ID
     *
     * @return void
     */
    function setExClass($ex_class_id = null)
    {
        if ($ex_class_id) {
            $this->_ex_class_id = $ex_class_id;
        }

        if ($this->_specs_already_set || (!$this->_ex_class_id && !$this->_own_ex_class_id)) {
            return;
        }

        if (CExObject::$_locales_cache_enabled) {
            self::initLocales();
        }

        $this->_props = $this->getProps();

        CBoolSpec::$_default_no = false;
        $this->_specs           = @$this->getSpecs(); // when creating the field
        CBoolSpec::$_default_no = true;

        $ex_class = $this->_ref_ex_class;

        $this->_class = "CExObject_{$ex_class->_id}";

        $this->_own_ex_class_id = $ex_class->_id;
        $this->_ref_ex_class    = $ex_class;

        $this->_specs_already_set = true;
    }

    /**
     * Load Ex class
     *
     * @param bool $cache Use object cache
     *
     * @return CExClass
     */
    function loadRefExClass($cache = true)
    {
        if ($cache && $this->_ref_ex_class && $this->_ref_ex_class->_id) {
            return $this->_ref_ex_class;
        }

        $id = $this->getClassId();
        if (isset(CExClass::$_list_cache[$id])) {
            return $this->_ref_ex_class = CExClass::$_list_cache[$id];
        }

        $ex_class = new CExClass();
        $ex_class->load($this->getClassId());

        return $this->_ref_ex_class = $ex_class; // can't use loadFwdRef here
    }

    /**
     * Clears locales cache
     *
     * @param bool $only_locales_ready
     *
     * @return void
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     */
    public static function clearLocales(bool $only_locales_ready = false): void
    {
        if (!$only_locales_ready) {
            // Todo: Does not return the number of deleted keys.
            Cache::deleteKeys(Cache::DISTR, self::CACHE_KEY);
        }
        self::$_locales_ready = false;
    }

    /**
     * Inits locale cache
     *
     * @param bool $force Force the regeneration
     *
     * @return void
     */
    static function initLocales($force = false)
    {
        if (!$force && self::$_locales_ready) {
            return;
        }

        $lang = CAppUI::pref("LOCALE");

        $cache = Cache::getCache(Cache::DISTR)->withCompressor();

        $cache_key = self::CACHE_KEY;

        $_all_locales = $cache->get("{$cache_key}{$lang}");

        if ($force || !$_all_locales) {
            $undefined = CAppUI::tr("Undefined");
            $ds        = CSQLDataSource::get("std");

            $_all_locales = [];

            $request = new CRequest();
            $request->addTable("ex_class_field_translation");
            $request->addWhere(
                [
                    "lang" => "= '$lang'",
                ]
            );
            $request->addLJoin(
                [
                    "ex_class_field"       => "ex_class_field.ex_class_field_id = ex_class_field_translation.ex_class_field_id",
                    "ex_concept"           => "ex_concept.ex_concept_id = ex_class_field.concept_id",
                    "ex_class_field_group" => "ex_class_field_group.ex_class_field_group_id = ex_class_field.ex_group_id",
                ]
            );
            $request->addSelect(
                [
                    "ex_class_field_translation.std",
                    "IF(ex_class_field_translation.desc  IS NOT NULL, ex_class_field_translation.desc,  ex_class_field_translation.std) AS `desc`",
                    "IF(ex_class_field_translation.court IS NOT NULL, ex_class_field_translation.court, ex_class_field_translation.std) AS `court`",
                    "ex_class_field.ex_class_field_id AS field_id",
                    "ex_class_field.name",
                    "ex_class_field.prop",
                    "ex_class_field.concept_id",
                    "ex_class_field_group.ex_class_id",
                    "ex_concept.ex_list_id",
                ]
            );

            $list = $ds->loadList($request->makeSelect());

            // Chargement des list_items par concept, field ou list
            $request = new CRequest();
            $request->addTable("ex_list_item");
            $request->addSelect(
                [
                    "ex_list_item_id",

                    "list_id",
                    "concept_id",
                    "field_id",

                    "name",
                ]
            );
            $list_items = $ds->loadList($request->makeSelect());

            // Chargement en une seule requete de toutes les traductions de champs
            $enum_list_cache = [
                "list"    => [],
                "concept" => [],
                "field"   => [],
            ];
            $mapper          = [
                "list_id"    => "list",
                "concept_id" => "concept",
                "field_id"   => "field",
            ];
            foreach ($list_items as $_item) {
                $_item_id   = $_item["ex_list_item_id"];
                $_item_name = $_item["name"];

                foreach ($mapper as $_field_name => $_to) {
                    if ($_field_value = $_item[$_field_name]) {
                        $enum_list_cache[$_to][$_field_value][$_item_id] = $_item_name;
                    }
                }
            }

            foreach ($list as $_item) {
                $_locales = [];

                $key            = "-{$_item['name']}";
                $_locales[$key] = $_item["std"];
                if ($_item["desc"]) {
                    $_locales["$key-desc"] = $_item["desc"];
                }
                if ($_item["court"]) {
                    $_locales["$key-court"] = $_item["court"];
                }

                $_ex_class_id = $_item['ex_class_id'];
                $_prefix      = "CExObject_$_ex_class_id";

                $prop = $_item["prop"];
                if (strpos($prop, "enum") === false && strpos($prop, "set") === false) {
                    if (!isset($_all_locales[$_prefix])) {
                        $_all_locales[$_prefix] = [];
                    }

                    $_all_locales[$_prefix] = array_merge($_all_locales[$_prefix], $_locales);
                    continue;
                }

                $key               = ".{$_item['name']}";
                $_locales["$key."] = $undefined;

                $concept_id = $_item["concept_id"];
                $ex_list_id = $_item["ex_list_id"];
                $field_id   = $_item["field_id"];

                $enum_list = [];
                if ($concept_id) {
                    if ($ex_list_id) {
                        if (isset($enum_list_cache["list"][$ex_list_id])) {
                            $enum_list = $enum_list_cache["list"][$ex_list_id];
                        }
                    } else {
                        if (isset($enum_list_cache["concept"][$concept_id])) {
                            $enum_list = $enum_list_cache["concept"][$concept_id];
                        }
                    }
                } else {
                    if (isset($enum_list_cache["field"][$field_id])) {
                        $enum_list = $enum_list_cache["field"][$field_id];
                    }
                }

                foreach ($enum_list as $_value => $_locale) {
                    $_locales["$key.$_value"] = $_locale;
                }

                if (!isset($_all_locales[$_prefix])) {
                    $_all_locales[$_prefix] = [];
                }

                $_all_locales[$_prefix] = array_merge($_all_locales[$_prefix], $_locales);
            }

            $cache->set("{$cache_key}{$lang}", $_all_locales);
        }

        foreach ($_all_locales as $_prefix => $_locales) {
            CAppUI::addLocales($_prefix, $_locales);
        }

        self::$_locales_ready = true;
    }

    /**
     * Check the last engine used for the locales.
     * If the engine has changed reload the locales
     *
     * @return void
     * @throws Exception
     *
     */
    static function checkLocales()
    {
        // Cache to avoid checking multiple times per 10 minute
        $static_cache = new Cache('CExObject.checkLocales', "status", Cache::INNER_OUTER, 600);
        if ($static_cache->exists()) {
            return;
        }

        if (!$engine = Cache::getLayerEngine(Cache::DISTR)) {
            return;
        }

        $last_check = null;
        // Cache to store the last engine used for ExObjects locales
        $cache = new Cache('CExObject.checkLocales', "check", Cache::INNER_OUTER);
        if ($cache->exists()) {
            $last_check = $cache->get();
        }

        // Get engine version and compare it with the cached engine

        if (!$last_check || $engine != $last_check) {
            $mutex = new CMbMutex("CExObject::initLocales");
            if (!$mutex->lock(10)) {
                return;
            }

            // If the engine has changed rebuild locales
            CExObject::initLocales(true);
            $cache->put($engine);

            $mutex->release();
        }

        $static_cache->put(null);
    }

    function setReferenceObject_1(CMbObject $reference)
    {
        $this->_ref_reference_object_1 = $reference;
        $this->reference_class         = $reference->_class;
        $this->reference_id            = $reference->_id;
    }

    function setReferenceObject_2(CMbObject $reference)
    {
        $this->_ref_reference_object_2 = $reference;
        $this->reference2_class        = $reference->_class;
        $this->reference2_id           = $reference->_id;
    }

    function setAdditionalObject(CMbObject $reference)
    {
        $this->_ref_additional_object = $reference;
        $this->additional_class       = $reference->_class;
        $this->additional_id          = $reference->_id;
    }

    function loadRefReferenceObjects()
    {
        $this->_ref_reference_object_1 = $this->loadFwdRef("reference_id");
        $this->_ref_reference_object_2 = $this->loadFwdRef("reference2_id");
    }

    function loadRefAdditionalObject()
    {
        $this->_ref_additional_object = $this->loadFwdRef("additional_id");

        if ($this->additional_id && $this->_ref_additional_object) {
            $this->_ref_additional_object->loadComplete();
        }
    }

    /**
     * @param bool $cache
     *
     * @return CGroups
     */
    function loadRefGroup($cache = true)
    {
        return $this->_ref_group = $this->loadFwdRef("group_id", $cache);
    }

    function loadNativeViews(CExClassEvent $event)
    {
        $this->_native_views = [];

        $views          = $event->getAvailableNativeViews();
        $selected_views = explode('|', $this->_ref_ex_class->native_views);

        foreach ($views as $_name => $_class) {
            if (in_array($_name, $selected_views)) {
                $this->_native_views[$_name] = $this->getReferenceObject($_class);
            }
        }

        return $this->_native_views;
    }

    /**
     * Permet de supprimer les valeurs non presentes dans les
     * specs du champ dans ce formulaire, mais qui le sont peut
     * etre dans le meme champ dans un autre formulaire (cas d'un concept)
     *
     * @param CExClassField $field
     * @param               $value
     *
     * @return string
     */
    static function typeSetSpecIntersect($field, $value)
    {
        $field_spec = $field->getSpecObject();

        if (!$field_spec instanceof CSetSpec) {
            return $value;
        }

        $values = explode("|", $value);
        $values = array_intersect($values, $field_spec->_list);

        return implode("|", $values);
    }

    /**
     * Load ExLinks
     *
     * @param bool $load_complete Lod complete object
     *
     * @return CExLink[]
     */
    function loadRefsLinks($load_complete = false)
    {
        $where = [
            "ex_class_id"  => "= '$this->_ex_class_id'",
            "ex_object_id" => "= '$this->ex_object_id'",
        ];

        $ex_link = new CExLink();

        /** @var CExLink[] $list */
        $list = $ex_link->loadList($where);

        foreach ($list as $_link) {
            switch ($_link->level) {
                case "object":
                    $_object = $_link->loadTargetObject();
                    $this->setObject($_object);
                    break;

                case "ref1":
                    $_object = $_link->loadTargetObject();
                    $this->setReferenceObject_1($_object);
                    break;

                case "ref2":
                    $_object = $_link->loadTargetObject();
                    $this->setReferenceObject_2($_object);
                    break;

                case "add":
                    $_object = $_link->loadTargetObject();
                    $this->setAdditionalObject($_object);
                    break;

                default:
                    $_object = null;
            }

            if ($load_complete && $_object) {
                $_object->loadComplete();
            }
        }

        return $list;
    }

    /**
     * attention aux dates, il faut surement checker le log de derniere modif des champs du concept
     *
     * @fixme pas trop optimisé
     */
    function getReportedValues()
    {
        $ex_class = $this->_ref_ex_class;
        $fields   = $ex_class->loadRefsAllFields();

        if ($this->_id) {
            return $fields;
        }

        self::$_multiple_load      = true;
        CExClassField::$_load_lite = true;

        $this->loadRefsLinks();

        $latest_ex_objects = [
            $ex_class->getLatestExObject($this->_ref_object),
            $ex_class->getLatestExObject($this->_ref_reference_object_1),
            $ex_class->getLatestExObject($this->_ref_reference_object_2),
        ];

        if ($this->_ref_object->_id) {
            $this->_ref_object->loadComplete();
        }

        if ($this->_ref_reference_object_1->_id) {
            $this->_ref_reference_object_1->loadComplete();
        }

        if ($this->_ref_reference_object_2->_id) {
            $this->_ref_reference_object_2->loadComplete();
        }

        CStoredObject::massLoadFwdRef($fields, "ex_group_id");
        $all_concepts    = CStoredObject::massLoadFwdRef($fields, "concept_id");
        $all_back_fields = CStoredObject::massLoadBackRefs($all_concepts, "class_fields");

        $ex_groups = CStoredObject::massLoadFwdRef($all_back_fields, "ex_group_id");
        CStoredObject::massLoadFwdRef($ex_groups, "ex_class_id");

        // Cache de concepts
        $concepts   = [];
        $ex_classes = [];

        // on cherche les champs reportés de l'objet courant
        foreach ($fields as $_field) {
            if ($_field->disabled) {
                continue;
            }

            $field_name                          = $_field->name;
            $this->_reported_fields[$field_name] = null;

            // valeur par défaut
            $spec_obj          = $_field->getSpecObject();
            $this->$field_name = CExClassField::unescapeProp($spec_obj->default);

            $_concept = null;
            if ($_field->concept_id) {
                $_concept = $_field->loadRefConcept();
            }

            // si champ pas reporté, on passe au suivant
            if (!($_field->report_class || ($_field->concept_id && $_concept->native_field && $_field->load_native_data))) {
                continue;
            }

            // Native fields
            if ($_concept && $_concept->native_field && $_field->load_native_data) {
                [$_class, $_path] = explode(" ", $_concept->native_field, 2);

                if (isset($this->_preview)) {
                    $this->_reported_fields[$field_name] = new $_class();
                    $this->$field_name                   = "";
                } else {
                    if ($_class === "CConstantesMedicales") {
                        $_patient = $this->getReferenceObject("CPatient");
                        /** @var $_object CConstantesMedicales */
                        [$_object, $_dates] = CConstantesMedicales::getLatestFor($_patient, CMbDT::dateTime());
                        $_object->updateFormFields();
                    } elseif ($_class === 'CBMRBHRe') {
                        /** @var CPatient $_patient */
                        $_patient = $this->getReferenceObject("CPatient");

                        if (!$_patient || !$_patient->_id) {
                            /** @var CSejour $_sejour */
                            $_sejour = $this->getReferenceObject('CSejour');

                            if ($_sejour && $_sejour->_id) {
                                $_patient = $_sejour->loadRefPatient();
                            }
                        }

                        $_object = $_patient->loadRefBMRBHRe();
                    } elseif ($_class === 'CNaissance') {
                        /** @var $_sejour CSejour */
                        $_sejour = $this->getReferenceObject("CSejour");

                        if ($_sejour && $_sejour->_id) {
                            if ($_naissances = $_sejour->loadRefsNaissances()) {
                                // Only take first birth if several
                                $_object = reset($_naissances);
                            }
                        } else {
                            /** @var $_grossesse CGrossesse */
                            $_grossesse = $this->getReferenceObject('CGrossesse');

                            if ($_grossesse && $_grossesse->_id) {
                                if ($_naissances = $_grossesse->loadRefsNaissances()) {
                                    // Only take first birth if several
                                    $_object = reset($_naissances);
                                }
                            }
                        }
                    } elseif ($this->_ref_object->_class === $_class) {
                        $_object = $this->_ref_object;
                    } elseif ($this->_ref_reference_object_1->_class === $_class) {
                        $_object = $this->_ref_reference_object_1;
                    } elseif ($this->_ref_reference_object_2->_class === $_class) {
                        $_object = $this->_ref_reference_object_2;
                    }

                    if (!isset($_object) || !$_object) {
                        continue;
                    }

                    [$_object, $_path] = CExClassConstraint::getFieldAndObjectStatic($_object, $_path);
                    $_resolved = CExClassConstraint::resolveObjectFieldStatic($_object, $_path);

                    $_obj        = $_resolved["object"];
                    $_field_name = $_resolved["field"];

                    if ($_obj->$_field_name !== "" && $_obj->$_field_name !== null) {
                        // If concept with report from enum mb field
                        // we check every list item according to "code" property and we value the item id as ex_object field value
                        if ($_concept->ex_list_id) {
                            $_list_items = $_concept->loadRefExList()->loadRefItems();

                            foreach ($_list_items as $_item) {
                                if (($_item->code !== '' && $_item->code !== null) && $_item->code == trim(
                                        $_obj->{$_field_name}
                                    )) {
                                    $this->_reported_fields[$field_name] = $_object;
                                    $this->{$field_name}                 = $_item->_id;
                                }
                            }
                        } else {
                            $this->_reported_fields[$field_name] = $_object;
                            $this->$field_name                   = trim($_obj->$_field_name);
                        }
                    }
                }

                if ($this->$field_name) {
                    continue;
                }
            }

            $_report_class = $_field->report_class;

            // si champ basé sur un concept, il faut parcourir
            // tous les formulaires qui ont un champ du meme concept

            if ($_field->concept_id) {
                if (!isset($concepts[$_field->concept_id])) {
                    $_concept_fields = $_concept->loadRefClassFields();

                    // Avoid reporting disabled fields
                    $_concept_fields = array_filter(
                        $_concept_fields,
                        function ($_field) {
                            return !$_field->disabled;
                        }
                    );

                    // Changement d'ordre des champs pour prendre de préférence la valeur
                    // du dernier champ créé et/ou avec le tab_index le plus élevé
                    $_ids   = CMbArray::pluck($_concept_fields, "_id");
                    $_ranks = CMbArray::pluck($_concept_fields, "tab_index");
                    array_multisort(
                        $_ranks,
                        SORT_DESC,
                        $_ids,
                        SORT_DESC,
                        $_concept_fields
                    );

                    foreach ($_concept_fields as $_concept_field) {
                        if (!isset($ex_classes[$_concept_field->ex_group_id])) {
                            $ex_classes[$_concept_field->ex_group_id] = $_concept_field->loadRefExClass();
                        } else {
                            $_concept_field->_ref_ex_class = $ex_classes[$_concept_field->ex_group_id];
                        }
                    }

                    $concepts[$_field->concept_id] = [
                        $_concept,
                        $_concept_fields,
                    ];
                } else {
                    [, $_concept_fields] = $concepts[$_field->concept_id];
                }

                /** @var CExObject $_latest */
                $_latest       = null;
                $_latest_value = null;

                // on regarde tous les champs du concept
                foreach ($_concept_fields as $_concept_field) {
                    $_ex_class = $_concept_field->_ref_ex_class;

                    $_concept_latest = null;

                    if ($this->_ref_object->_class === $_report_class) {
                        $_concept_latest = $_ex_class->getLatestExObject($this->_ref_object);
                    } elseif ($this->_ref_reference_object_1->_class === $_report_class) {
                        $_concept_latest = $_ex_class->getLatestExObject($this->_ref_reference_object_1);
                    } elseif ($this->_ref_reference_object_2->_class === $_report_class) {
                        $_concept_latest = $_ex_class->getLatestExObject($this->_ref_reference_object_2);
                    }

                    // si pas d'objet precedemment enregistré
                    // On ne vérifie plus l'existence d'une valeur pour permettre le report de données lors du vidage d'un champ du même concept
                    // dans un autre formulaire
                    if (!$_concept_latest || !$_concept_latest->_id /*|| $_concept_latest->{$_concept_field->name} == ""*/) {
                        continue;
                    }

                    if (!$_latest) {
                        $_latest       = $_concept_latest;
                        $_latest_value = $_latest->{$_concept_field->name};
                    } else {
                        $_date = $_concept_latest->getEditDate();

                        if ($_date > $_latest->getEditDate()) {
                            $_latest       = $_concept_latest;
                            $_latest_value = $_latest->{$_concept_field->name};
                        }
                    }
                }

                if ($_latest) {
                    $_latest->loadTargetObject()->loadExView();

                    $this->_reported_fields[$field_name] = $_latest;
                    $this->$field_name                   = self::typeSetSpecIntersect($_field, $_latest_value);
                }
            } // Ceux de la meme exclass
            else {
                $escape = true;
                foreach ($latest_ex_objects as $_latest_ex_object) {
                    if ($_latest_ex_object->_id) {
                        $escape = false;
                        break;
                    }
                }

                if ($escape) {
                    continue;
                }

                // Todo: Comprendre pourquoi parfois il n'y a pas de $_latest_ex_object
                /** @var CMbObject $_base */
                $_base = null;
                foreach ($latest_ex_objects as $_latest_ex_object) {
                    if (!$_latest_ex_object) {
                        continue;
                    }

                    if ($_latest_ex_object->_ref_reference_object_1 && $_latest_ex_object->_ref_reference_object_1->_class === $_report_class) {
                        $_base = $_latest_ex_object->_ref_reference_object_1;
                        break;
                    } elseif ($_latest_ex_object->_ref_reference_object_2 && $_latest_ex_object->_ref_reference_object_2->_class === $_report_class) {
                        $_base = $_latest_ex_object->_ref_reference_object_2;
                        break;
                    } elseif ($_latest_ex_object->_ref_object && $_latest_ex_object->_ref_object->_class === $_report_class) {
                        $_base = $_latest_ex_object->_ref_object;
                        break;
                    }
                }

                if ($this->_ref_object->_id && !$_base) {
                    //$_field_view = CAppUI::tr("$this->_class-$_field->name");
                    //CAppUI::setMsg("Report de données impossible pour le champ '$_field_view'", UI_MSG_WARNING);
                    continue;
                }

                if ($_base->$field_name == "") {
                    continue;
                }

                $_base->loadTargetObject()->loadExView();
                $_base->loadLastLog();

                $this->_reported_fields[$field_name] = $_base;
                $this->$field_name                   = self::typeSetSpecIntersect($_field, $_base->$field_name);
            }
        }

        self::$_multiple_load      = false;
        CExClassField::$_load_lite = false;

        return $fields;
    }

    /**
     * @inheritdoc
     */
    function loadOldObject()
    {
        if (!$this->_old) {
            $this->_old               = new self;
            $this->_old->_ex_class_id = $this->_ex_class_id;
            $this->_old->setExClass();
            $this->_old->load($this->_id);
        }

        return $this->_old;
    }

    private function camelize($str)
    {
        return preg_replace_callback(
            "/-+(.)?/",
            function ($m) {
                return ucwords($m[1]);
            },
            $str
        );
    }

    /**
     * @param CExClassField[] $fields Fields to get display of
     *
     * @return void
     */
    function setFieldsDisplay($fields)
    {
        CStoredObject::massLoadBackRefs($fields, "predicates");

        $default                      = [];
        $this->_fields_display_struct = [];

        $all_predicates = [];
        foreach ($fields as $_field) {
            if ($_field->disabled) {
                continue;
            }

            if ($_field->_count["predicates"] > 0) {
                $all_predicates += $_field->loadRefPredicates();
            }
        }

        CStoredObject::massLoadBackRefs($all_predicates, "properties");
        CStoredObject::massLoadBackRefs($all_predicates, "display_fields");
        CStoredObject::massLoadBackRefs($all_predicates, "display_messages");
        CStoredObject::massLoadBackRefs($all_predicates, "display_subgroups");
        CStoredObject::massLoadBackRefs($all_predicates, "display_pictures");

        foreach ($fields as $_field) {
            if ($_field->disabled) {
                continue;
            }

            $_affected   = [];
            $_predicates = [];

            if ($_field->_count["predicates"] > 0) {
                $_predicates = $_field->loadRefPredicates();
            }

            foreach ($_predicates as $_predicate) {
                $_struct = [
                    "operator" => $_predicate->operator,
                    "value"    => $_predicate->value,
                    "display"  => [
                        "fields"         => [],
                        "messages"       => [],
                        "subgroups"      => [],
                        "pictures"       => [],
                        "action_buttons" => [],
                        "widgets"        => [],
                    ],
                    "style"    => [
                        "fields"    => [],
                        "messages"  => [],
                        "subgroups" => [],
                    ],
                ];

                // Fields
                /** @var CExClassField[] $_display_fields */
                $_display_fields = $_predicate->loadBackRefs("display_fields");
                foreach ($_display_fields as $_display) {
                    $_struct["display"]["fields"][] = $_display->name;
                }

                // Messages
                $_display_messages = $_predicate->loadBackRefs("display_messages");
                foreach ($_display_messages as $_display) {
                    $_struct["display"]["messages"][] = $_display->_guid;
                }

                // Subgroups
                $_display_subgroups = $_predicate->loadBackRefs("display_subgroups");
                foreach ($_display_subgroups as $_display) {
                    $_struct["display"]["subgroups"][] = $_display->_guid;
                }

                // Pictures
                $_display_pictures = $_predicate->loadBackRefs("display_pictures");
                foreach ($_display_pictures as $_display) {
                    $_struct["display"]["pictures"][] = $_display->_guid;
                }

                // Action buttons
                $_action_buttons = $_predicate->loadBackRefs("display_action_buttons");
                foreach ($_action_buttons as $_display) {
                    $_struct["display"]["action_buttons"][] = $_display->_guid;
                }

                // Widgets
                $_widgets = $_predicate->loadBackRefs("display_widgets");
                foreach ($_widgets as $_display) {
                    $_struct["display"]["widgets"][] = $_display->_guid;
                }

                $_styles = $_predicate->loadRefProperties();
                foreach ($_styles as $_style) {
                    /** @var CExClassField|CExClassMessage|CExClassFieldSubgroup $_ref_object */
                    $_ref_object = $_style->loadTargetObject();

                    $default[$_ref_object->_guid] = $_ref_object->getDefaultProperties();

                    switch ($_style->object_class) {
                        case "CExClassField":
                            $_field_name                  = $_ref_object->name;
                            $_struct["style"]["fields"][] = [
                                "name"      => $_field_name,
                                "type"      => $_style->type,
                                "camelized" => $this->camelize($_style->type),
                                "value"     => $_style->value,
                            ];

                            $_affected[$_ref_object->_guid] = [
                                "type" => "field",
                                "name" => $_field_name,
                            ];
                            break;

                        case "CExClassMessage":
                            $_struct["style"]["messages"][] = [
                                "guid"      => $_ref_object->_guid,
                                "type"      => $_style->type,
                                "camelized" => $this->camelize($_style->type),
                                "value"     => $_style->value,
                            ];

                            $_affected[$_ref_object->_guid] = [
                                "type" => "message",
                                "guid" => $_ref_object->_guid,
                            ];
                            break;

                        case "CExClassFieldSubgroup":
                            $_struct["style"]["subgroups"][] = [
                                "guid"      => $_ref_object->_guid,
                                "type"      => $_style->type,
                                "camelized" => $this->camelize($_style->type),
                                "value"     => $_style->value,
                            ];

                            $_affected[$_ref_object->_guid] = [
                                "type" => "subgroup",
                                "guid" => $_ref_object->_guid,
                            ];
                            break;

                        default:
                            // ignore
                            break;
                    }
                }

                $this->_fields_display_struct[$_field->name]["predicates"][] = $_struct;
            }

            if (!empty($_affected)) {
                $this->_fields_display_struct[$_field->name]["affects"] = $_affected;
            }
        }

        $this->_fields_default_properties = $default;
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        if ($msg = $this->check()) {
            return $msg;
        }

        $new_object = !$this->_id;
        $now        = CMbDT::dateTime();

        if ($new_object) {
            $object = $this->loadTargetObject();

            $group_id = CGroups::loadCurrent()->_id;
            if ($object instanceof IGroupRelated && ($group = $object->loadRelGroup()) && $group->_id) {
                $group_id = $group->_id;
            }

            $this->group_id        = $group_id;
            $this->datetime_create = $now;
            $this->owner_id        = CMediusers::get()->_id;
        }

        $this->datetime_edit = $now;

        $this->setCompleteness();

        if ($msg = parent::store()) {
            return $msg;
        }

        if ($new_object) {
            $this->saveNativeFields();
        }

        // Save pictures
        if ($this->_pictures_data) {
            $pictures_rows = explode("|", $this->_pictures_data);

            foreach ($pictures_rows as $_row) {
                [$_picture_id, $_data] = explode("=", $_row, 2);
                $_field_parts = explode(",", $_data);
                $_data        = [];
                foreach ($_field_parts as $_single_data) {
                    [$_key, $_value] = explode(":", $_single_data);
                    $_data[$_key] = $_value;
                }

                $object_pic = new CExObjectPicture();
                $ds         = $this->getDS();

                $where = [
                    "ex_class_id"         => $ds->prepare("=?", $this->_ex_class_id),
                    "ex_object_id"        => $ds->prepare("=?", $this->_id),
                    "ex_class_picture_id" => $ds->prepare("=?", $_picture_id),
                ];
                $object_pic->loadObject($where);

                // Map fields
                foreach (CExObjectPicture::$_coord_fields as $_field) {
                    $object_pic->$_field = $_data[$_field];
                }

                $object_pic->ex_class_id            = $this->_ex_class_id;
                $object_pic->ex_object_id           = $this->_id;
                $object_pic->ex_class_picture_id    = $_picture_id;
                $object_pic->comment                = $_data["comment"];
                $object_pic->triggered_ex_class_id  = $_data["triggered_ex_class_id"];
                $object_pic->triggered_ex_object_id = $_data["triggered_ex_object_id"];
                $object_pic->store();

                // We save or update the drawing file
                if (!empty($_data["drawing"])) {
                    $content = urldecode($_data["drawing"]);
                    $content = CMbString::parseDataURI($content);

                    $file = new CFile();
                    $file->setObject($object_pic);
                    $file->file_name = "drawing.png";
                    $file->loadMatchingObject();

                    $file->fillFields();
                    $file->setContent($content["data"]);
                    $file->file_date = CMbDT::dateTime();
                    $file->file_type = $content["mime"];
                    $file->author_id = CMediusers::get()->_id;
                    $file->store();
                }
            }
        }

        // SCreate quick access object
        if ($this->_quick_access_creation) {
            $_new_obj = new $this->_quick_access_creation;

            /** @var CPrescriptionLineElement $_target_object */
            $_target_object = $this->loadTargetObject();

            if ($_new_obj instanceof CAdministration && $_target_object instanceof CPrescriptionLineElement) {
                $_new_obj->setObject($_target_object);
                $_new_obj->dateTime            = CMbDT::transform(null, null, "%Y-%m-%d %H:00:00");
                $_new_obj->quantite            = 1;
                $_new_obj->administrateur_id   = CMediusers::get()->_id;
                $_new_obj->_unite_prescription = $_target_object->_chapitre;

                $_target_object->loadRefsPrises();
                if (count($_target_object->_ref_prises)) {
                    $_new_obj->prise_id            = reset($_target_object->_ref_prises)->_id;
                    $_new_obj->_unite_prescription = null;
                }

                if ($msg = $_new_obj->store()) {
                    CAppUI::setMsg($msg, UI_MSG_WARNING);
                } else {
                    $this->setAdditionalObject($_new_obj);

                    parent::store();
                }
            }
        }

        // Links
        if ($new_object) {
            $fields = [
                "object" => ["object_class", "object_id"],
                "ref1"   => ["reference_class", "reference_id"],
                "ref2"   => ["reference2_class", "reference2_id"],
                "add"    => ["additional_class", "additional_id"],
            ];

            foreach ($fields as $_level => $_field) {
                if ($this->{$_field[0]} && $this->{$_field[1]}) {
                    $link               = new CExLink();
                    $link->object_class = $this->{$_field[0]};
                    $link->object_id    = $this->{$_field[1]};
                    $link->group_id     = $this->group_id;
                    $link->ex_object_id = $this->_id;
                    $link->ex_class_id  = $this->_ex_class_id;
                    $link->level        = $_level;
                    if ($msg = $link->store()) {
                        return $msg;
                    }
                }
            }
        }

        $formula_field = $this->_ref_ex_class->getFormulaField();
        if ($formula_field) {
            $this->_formula_result = $this->{$formula_field};
        }

        // Send email
        if ($new_object) {
            $source_smtp = CExchangeSource::get("system-message", CSourceSMTP::TYPE);
            if ($source_smtp && $source_smtp->_id) {
                $notifications = $this->loadRefExClass()->loadRefsNotifications();

                $predicates = CStoredObject::massLoadFwdRef($notifications, "predicate_id");
                CStoredObject::massLoadFwdRef($predicates, "ex_class_field_id");

                foreach ($notifications as $_notification) {
                    $_notification->sendEmail($this);
                }
            }
        }

        return null;
    }

    /**
     * Save Mediboard native fields from CExObject
     *
     * @return void
     * @throws Exception
     */
    public function saveNativeFields()
    {
        if (!CAppUI::conf('forms CExConcept native_field')) {
            return;
        }

        $fields = $this->_ref_ex_class->loadRefsAllFields();

        $constante_comments = [];

        /** @var string[][] $_data_array */
        $_data_array = [];

        $taggable_fields = array_filter(
            $fields,
            function (CExClassField $_ex_field) {
                return (!$_ex_field->disabled && $this->{$_ex_field->name} !== null);
            }
        );

        $tags = [];

        if ($taggable_fields) {
            CStoredObject::massLoadBackRefs($taggable_fields, 'ex_class_field_tag_items');

            /** @var CExClassField $_field */
            foreach ($taggable_fields as $_field) {
                $_items = $_field->loadFieldTagItems();

                /** @var CExClassFieldTagItem $_item */
                foreach ($_items as $_item) {
                    if ($_item->_tag->validate($_field)) {
                        $tags[$_item->tag] = $this->{$_field->name};
                    }
                }
            }
        }

        // Get values : Class / field => Data
        foreach ($fields as $_ex_field) {
            if (!$_ex_field->concept_id || !$_ex_field->update_native_data || $_ex_field->disabled || $this->{$_ex_field->name} === null) {
                continue;
            }

            $_concept = $_ex_field->loadRefConcept();
            if ($_concept->native_field) {
                [$_class, $_field] = explode(" ", $_concept->native_field);

                if ($_concept->native_field) {
                    switch ($_class) {
                        case "CTransmissionMedicale":
                            if (!isset($_data_array[$_class])) {
                                $_data_array[$_class] = [];
                            }

                            // Special treatment for CTransmission (can be multiple)
                            $_data_array[$_class][] = [$_field => $this->{$_ex_field->name}];
                            break;

                        case "CConstantesMedicales":
                            if (!isset($_data_array[$_class])) {
                                $_data_array[$_class] = [];
                            }

                            $_data_array[$_class][$_field] = $this->{$_ex_field->name};

                            $_constante_comment = CValue::post("{$_ex_field->name}_constant_comment");
                            if ($_constante_comment !== null && $_constante_comment !== '') {
                                $constante_comments[$_field] = $_constante_comment;
                            }
                            break;

                        case "CPatient":
                        case "CBMRBHRe":
                            if (!isset($_data_array[$_class])) {
                                $_data_array[$_class] = [];
                            }

                            $_data_array[$_class][$_field] = [$this->{$_ex_field->name}, $_concept];
                            break;

                        case "CConsultation":
                        case "CSejour":
                            if (!isset($_data_array[$_class])) {
                                $_data_array[$_class] = [];
                            }

                            $_data_array[$_class][$_field] = $this->{$_ex_field->name};
                            break;

                        default:
                            // do nothing
                    }
                }
            }
        }

        // Handle reported native fields
        foreach ($_data_array as $_class => $_data) {
            // Generic case
            switch ($_class) {
                case "CConsultation":
                default:
                    $_ref_object = $this->getReferenceObject($_class);
                    if ($_ref_object && $_ref_object->_id) {
                        foreach ($_data as $_field => $_value) {
                            $_ref_object->$_field = $_value;
                        }

                        if ($msg = $_ref_object->store()) {
                            CAppUI::setMsg($msg, UI_MSG_ALERT);
                        }
                    }
                    break;

                // Special CPatient enum case
                case 'CPatient':
                    $_patient = $this->getReferenceObject($_class);

                    if ($_patient && $_patient->_id) {
                        foreach ($_data as $_field => $_values) {
                            [$_value, $_concept] = $_values;

                            if ($_concept->ex_list_id) {
                                $_list_item = $_concept->loadRefExList()->loadRefItems();

                                $modified = false;
                                foreach ($_list_item as $_item) {
                                    if ($_item->_id == $_value && ($_item->code !== '' && $_item->code !== null)) {
                                        $_patient->{$_field} = $_item->code;
                                        $modified            = true;
                                    }
                                }

                                if ($modified) {
                                    if ($_msg = $_patient->store()) {
                                        CAppUI::setMsg($_msg, UI_MSG_ALERT);
                                    }
                                }
                            }
                        }
                    }
                    break;

                // Special "CBMRBHRe" case..
                case "CBMRBHRe":
                    /** @var CPatient $_patient */
                    $_patient = $this->getReferenceObject('CPatient');

                    if (!$_patient || !$_patient->_id) {
                        /** @var CSejour $_sejour */
                        $_sejour = $this->getReferenceObject('CSejour');

                        if ($_sejour && $_sejour->_id) {
                            $_patient = $_sejour->loadRefPatient();
                        }
                    }

                    if (($_patient && $_patient->_id) && CBMRBHRe::dossierId($_patient->_id)) {
                        $_bmr = $_patient->loadRefBMRBHRe();

                        if ($_bmr && $_bmr->_id) {
                            foreach ($_data as $_field => $_values) {
                                [$_value, $_concept] = $_values;

                                if ($_concept->ex_list_id) {
                                    $_list_item = $_concept->loadRefExList()->loadRefItems();

                                    $modified = false;
                                    foreach ($_list_item as $_item) {
                                        if ($_item->_id == $_value && ($_item->code !== '' && $_item->code !== null)) {
                                            $_bmr->{$_field} = $_item->code;
                                            $modified        = true;
                                        }
                                    }

                                    if ($modified) {
                                        if ($_msg = $_bmr->store()) {
                                            CAppUI::setMsg($_msg, UI_MSG_ALERT);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    break;

                // Special "CConstantesMedicales" case
                case "CConstantesMedicales":
                    $_patient = $this->getReferenceObject("CPatient");
                    $_object  = $this->getReferenceObject("CSejour");
                    if (!$_object || !$_object->_id) {
                        $_object = $this->getReferenceObject("CConsultation");
                    }

                    if ($_object->_id) {
                        $_constante                            = new CConstantesMedicales();
                        $_constante->_new_constantes_medicales = true;
                        $_constante->patient_id                = $_patient->_id;
                        $_constante->datetime                  = ($tags['heure_saisie_constantes']) ?? CMbDT::dateTime(
                        );
                        $_constante->context_class             = $_object->_class;
                        $_constante->context_id                = $_object->_id;

                        // Do not store Constant if "use_redon" configuration is set.
                        $all_fields_are_redon = true;
                        foreach ($_data as $_field => $_value) {
                            if (!$this->canReportRedon() && CRedon::isRedon($_field)) {
                                continue;
                            } else {
                                $_constante->{$_field} = $_value;
                                $all_fields_are_redon  = false;
                            }
                        }

                        if ($all_fields_are_redon) {
                            break;
                        }

                        if ($msg = $_constante->store()) {
                            CAppUI::setMsg($msg, UI_MSG_ALERT);
                        }

                        foreach ($_data as $_field => $_value) {
                            if (isset($constante_comments[$_field])) {
                                $_comment              = new CConstantComment();
                                $_comment->comment     = $constante_comments[$_field];
                                $_comment->constant_id = $_constante->_id;
                                $_comment->constant    = $_field;

                                if ($msg = $_comment->store()) {
                                    CAppUI::setMsg($msg, UI_MSG_ALERT);
                                }
                            }
                        }
                    }
                    break;

                // Special "CTransmissionMedicale" case
                case "CTransmissionMedicale":
                    $_sejour = $this->getReferenceObject("CSejour");

                    if ($_sejour->_id) {
                        foreach ($_data as $_transmission_obj => $_transmission_data) {
                            if (!is_array($_transmission_data)) {
                                continue;
                            }

                            foreach ($_transmission_data as $_field => $_value) {
                                $_transmission            = new CTransmissionMedicale();
                                $_transmission->sejour_id = $_sejour->_id;
                                $_transmission->user_id   = CMediusers::get()->_id;
                                $_transmission->degre     = 'low';
                                $_transmission->type      = 'data';
                                $_transmission->date      = CMbDT::dateTime();
                                $_transmission->{$_field} = $_value;
                                if ($msg = $_transmission->store()) {
                                    CAppUI::setMsg($msg, UI_MSG_ALERT);
                                }
                            }
                        }
                    }
                    break;
            }
        }
    }

    public function canReportRedon(): bool
    {
        $_object = $this->getReferenceObject("CSejour");
        if (!$_object || !$_object->_id) {
            $_object = $this->getReferenceObject("CConsultation");
        }

        if ($_object !== null) {
            return !(
                ($_object instanceof IGroupRelated)
                && ($_ref_group = $_object->loadRelGroup())
                && CAppUI::gconf('dPpatients CConstantesMedicales use_redon', $_ref_group->_id)
            );
        }

        // Case of new CExObject, from inc_reported_value
        $refs = [$this->_ref_object, $this->_ref_reference_object_1, $this->_ref_reference_object_2];

        foreach ($refs as $ref) {
            if ($ref instanceof CSejour) {
                return !(
                    ($_ref_group = $ref->loadRelGroup())
                    && CAppUI::gconf('dPpatients CConstantesMedicales use_redon', $_ref_group->_id)
                );
            }
        }

        foreach ($refs as $ref) {
            if ($ref instanceof CConsultation) {
                return !(
                    ($_ref_group = $ref->loadRelGroup())
                    && CAppUI::gconf('dPpatients CConstantesMedicales use_redon', $_ref_group->_id)
                );
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    function completeLabelFields(&$fields, $params)
    {
        $fields["NOM CHAMP"]    = $params["field_name"];
        $fields["VALEUR CHAMP"] = $params["field_value"];
    }

    /**
     * Load pictures and apply custom coordinates
     *
     * @return void
     */
    function loadPictures()
    {
        $groups = $this->loadRefExClass()->loadRefsGroups();

        /** @var CExClassPicture[] $pictures */
        $pictures = [];
        foreach ($groups as $_group) {
            $pictures = array_merge($pictures, $_group->loadRefsPictures());
        }

        $ex_class = $this->loadRefExClass();

        foreach ($pictures as $_picture) {
            // DB field to form field
            $_picture->_triggered_ex_class_id = $_picture->triggered_ex_class_id;

            if (!$this->_id) {
                if ($_picture->report_class) {
                    $_concept_latest = null;

                    if ($this->_ref_object->_class === $_picture->report_class) {
                        $_concept_latest = $ex_class->getLatestExObject($this->_ref_object);
                    } elseif ($this->_ref_reference_object_1->_class === $_picture->report_class) {
                        $_concept_latest = $ex_class->getLatestExObject($this->_ref_reference_object_1);
                    } elseif ($this->_ref_reference_object_2->_class === $_picture->report_class) {
                        $_concept_latest = $ex_class->getLatestExObject($this->_ref_reference_object_2);
                    }

                    if ($_concept_latest) {
                        $object_pic = new CExObjectPicture();
                        $ds         = $object_pic->getDS();

                        $where = [
                            "ex_class_id"         => $ds->prepare("=?", $ex_class->_id),
                            "ex_object_id"        => $ds->prepare("=?", $_concept_latest->_id),
                            "ex_class_picture_id" => $ds->prepare("=?", $_picture->_id),
                        ];

                        $object_pic->loadObject($where);

                        if ($object_pic->_id) {
                            $object_pic->loadRefDrawing();
                            $_picture->_reported_ex_object_picture = $object_pic;
                        }
                    }
                }
                continue;
            }

            $object_pic = new CExObjectPicture();
            $ds         = $object_pic->getDS();

            $where = [
                "ex_class_id"         => $ds->prepare("=?", $this->_ex_class_id),
                "ex_object_id"        => $ds->prepare("=?", $this->_id),
                "ex_class_picture_id" => $ds->prepare("=?", $_picture->_id),
            ];

            $_picture->_ref_ex_object_picture = $object_pic;

            if ($object_pic->loadObject($where)) {
                foreach (CExObjectPicture::$_coord_fields as $_field) {
                    $_picture->$_field = $object_pic->$_field;
                }

                if ($object_pic->triggered_ex_class_id) {
                    $_picture->_triggered_ex_class_id  = $object_pic->triggered_ex_class_id;
                    $_picture->_triggered_ex_object_id = $object_pic->triggered_ex_object_id;
                }

                $object_pic->loadRefDrawing();

                $_picture->_comment = $object_pic->comment;
            }
        }
    }

    /// Low level methods ///////////

    /**
     * @inheritdoc
     */
    function bind($hash, $doStripSlashes = true)
    {
        $this->setExClass();

        $hash = $doStripSlashes ? array_map("stripslashes", $hash) : $hash;

        foreach ($hash as $k => $v) {
            $this->$k = $v;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    function updatePlainFields()
    {
        $specs  = $this->_specs;
        $fields = $this->getPlainFields();

        foreach ($fields as $name => $value) {
            if ($value !== null && isset($specs[$name])) {
                $this->$name = $specs[$name]->filter($value);
            }
        }
    }

    /**
     * @inheritdoc
     */
    function load($id = null)
    {
        $this->setExClass();

        return parent::load($id);
    }

    /**
     * @inheritdoc
     *
     * Used in updatePlainFields
     */
    function getPlainFields()
    {
        $this->setExClass();

        $result = [];

        foreach ($this->_props as $name => $prop) {
            if ($name[0] !== '_') {
                $result[$name] = $this->{$name};
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    function fieldModified($field, $value = null)
    {
        $this->setExClass();

        return parent::fieldModified($field, $value);
    }

    /**
     * @inheritdoc
     */
    function loadQueryList($sql, ?int $limit_time = null)
    {
        $ds   = $this->_spec->ds;
        $cur  = $ds->exec($sql);
        $list = [];

        if (!$cur) {
            return $list;
        }

        // Avoid fatal errors while trying to fetch non existing forms
        try {
            while ($row = $ds->fetchAssoc($cur)) {
                $newObject = new self($this->_ex_class_id); // $this->_class >>>> "self"
                //$newObject->setExClass();
                $newObject->bind($row, false);

                $newObject->checkConfidential();
                $newObject->updateFormFields();
                $newObject->registerCache();
                $list[$newObject->_id] = $newObject;
            }
        } catch (Throwable $e) {
            CApp::log($e->getMessage(), null, LoggerLevels::LEVEL_WARNING);
        }

        $ds->freeResult($cur);

        return $list;
    }

    /**
     * @inheritdoc
     *
     * needed or will throw errors in the field specs
     */
    function checkProperty($name)
    {
        $class = $this->_class;

        $this->_class = CClassMap::getInstance()->getShortName($this);

        // Sauvegarde se props car elles sont réinitialisées
        $props = $this->_props;

        $spec = $this->_specs[$name];
        $ret  = $spec->checkPropertyValue($this);

        if ($spec instanceof CNumSpec) {
            try {
                $spec->checkValueLimit($this->{$name});
            } catch (CMbException $e) {
                return $e->getMessage();
            }
        }

        $this->_props = $props;

        $this->_class = $class;

        return $ret;
    }
    /// End low level methods /////////

    /**
     * $spec->table is not set from getSpec because CExObject need $this->ex_class_id to know its table.
     * Use $this->getProps() to value $spec->table.
     */
    function getSpec()
    {
        $spec      = parent::getSpec();
        $spec->key = "ex_object_id";

        return $spec;
    }

    /**
     * Get ExClass id
     *
     * @return int
     */
    function getClassId()
    {
        return ($this->_ex_class_id ?: $this->_own_ex_class_id);
    }

    /**
     * Get database table name
     *
     * @return string
     */
    function getTableName()
    {
        return "ex_object_" . $this->getClassId();
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $this->loadRefExClass();
        $this->_spec->table = $this->getTableName();
        $class_id           = $this->getClassId();

        $short_name            = CClassMap::getInstance()->getShortName($this);
        $class                 = ($class_id) ? "{$short_name}_{$class_id}" : $short_name;
        $props                 = parent::getProps();
        $props["ex_object_id"] = "ref class|$class show|0";

        // Todo: Do not declare backprops on CExObject
        $props["object_id"]    = "ref notNull class|CStoredObject meta|object_class";
        $props["object_class"] = "str notNull class show|0";

        $props["_ex_class_id"]           = "ref class|CExClass";
        $props["_event_name"]            = "str";
        $props["_pictures_data"]         = "text";
        $props["_quick_access_creation"] = "str";
        $props["_hidden_fields"]         = "str";
        $props["_formula_result"]        = "str";

        // Todo: Do not declare backprops on CExObject
        $props["group_id"] = "ref class|CGroups notNull";

        // Todo: Do not declare backprops on CExObject
        $props["reference_class"] = "str class";
        $props["reference_id"]    = "ref class|CMbObject meta|reference_class";

        // Todo: Do not declare backprops on CExObject
        $props["reference2_class"] = "str class";
        $props["reference2_id"]    = "ref class|CMbObject meta|reference2_class";

        // Todo: Do not declare backprops on CExObject
        $props["additional_class"] = "str class";
        $props["additional_id"]    = "ref class|CMbObject meta|additional_class";

        $props["datetime_create"] = "dateTime";
        $props["datetime_edit"]   = "dateTime";

        // Todo: Do not declare backprops on CExObject
        $props["owner_id"] = "ref class|CMediusers";

        $props['completeness_level'] = 'enum list|none|some|all';
        $props['nb_alert_fields']    = 'num';

        if (self::$_load_lite || !$class_id) {
            return $props;
        }

        $fields = $this->_ref_ex_class->loadRefsAllFields(true);

        foreach ($fields as $_field) {
            // don't redeclare them more than once
            if (isset($this->{$_field->name})) {
                break;
            }

            $this->{$_field->name}                = null; // declaration of the field
            $props[$_field->name]                 = $_field->prop; // declaration of the field spec
            $this->_fields_display[$_field->name] = true; // display the field by default
        }

        return $props;
    }

    /**
     * @inheritdoc
     */
    function getSpecs()
    {
        $ex_class_id = $this->getClassId();

        $this->_class = CClassMap::getInstance()->getShortName($this) . "_{$ex_class_id}";

        if ($this->_id && isset(self::$_ex_specs[$ex_class_id])) {
            return self::$_ex_specs[$ex_class_id];
        }

        $specs = @parent::getSpecs(); // sometimes there is "list|"

        foreach ($specs as $_field => $_spec) {
            if ($_spec instanceof CEnumSpec) {
                foreach ($_spec->_locales as $key => $locale) {
                    $specs[$_field]->_locales[$key] = CAppUI::tr("$this->_class.$_field.$key");
                }
            }
        }

        if ($ex_class_id) {
            self::$_ex_specs[$ex_class_id] = $specs;
        }

        return $specs;
    }

    /**
     * @inheritdoc
     */
    function loadLogs()
    {
        $this->setExClass();

        parent::loadLogs();
    }

    /**
     * Get the reference object of the right class
     *
     * @param string $class The class name
     *
     * @return CMbObject|null
     */
    function getReferenceObject($class)
    {
        $fields = [
            "object_class"     => "object_id",
            "reference_class"  => "reference_id",
            "reference2_class" => "reference2_id",
        ];

        foreach ($fields as $_class => $_id) {
            if ($this->$_class === $class) {
                return $this->loadFwdRef($_id);
            }
        }
    }

    static function getValidObject($object_class)
    {
        if (!preg_match('/^CExObject_(\d+)$/', $object_class, $matches)) {
            return false;
        }

        $ex_class = new CExClass();
        if (!$ex_class->load($matches[1])) {
            return false;
        }

        return new CExObject($ex_class->_id);
    }

    /**
     * Counts ExObject stored for the object
     *
     * @param CMbObject $object    The object to load the ExObjects for
     * @param bool      $only_last Load only the latest object
     * @param string    $level     The object to load in relation to the chosen level
     *
     * @return CExObject[]|CExObject[][] The list, with ExClass IDs as key and counts as value
     */
    static function loadExObjectsFor(CMbObject $object, $only_last = false, $level = 'object')
    {
        $link = new CExLink();

        $ds    = $link->getDS();
        $where = [
            "object_class" => $ds->prepare("= %", $object->_class),
            "object_id"    => $ds->prepare("= %", $object->_id),
            "level"        => "= '$level'",
        ];

        /** @var CExLink[] $links */
        if ($only_last) {
            $links = $link->loadList($where, "ex_object_id DESC", null, "ex_class_id");
        } else {
            $links = $link->loadList($where);
        }

        CExLink::massLoadExObjects($links);

        $ex_objects = [];

        foreach ($links as $_link) {
            if (!isset($ex_objects[$_link->ex_class_id])) {
                $ex_objects[$_link->ex_class_id] = [];
            }

            $_ex = $_link->_ref_ex_object;
            $_ex->loadRefExClass();

            if ($only_last) {
                $ex_objects[$_link->ex_class_id] = $_ex;
            } else {
                $ex_objects[$_link->ex_class_id][$_link->ex_object_id] = $_ex;
            }
        }

        return $ex_objects;
    }

    /**
     * Adds the list of forms to a template manager
     *
     * @param CTemplateManager $template The template manager
     * @param CMbObject        $object   The host object
     * @param string           $name     The field name
     *
     * @return void
     */
    static function addFormsToTemplate(CTemplateManager $template, CMbObject $object, $name)
    {
        static $ex_classes = null;

        if (!CAppUI::conf("forms CExClassField doc_template_integration")) {
            return;
        }

        if (!$template->include_forms) {
            return;
        }

        if (!$template->makeFields("$name - Formulaires") && !$template->makeFields(
                "$name - Form. ",
                false,
                true,
                false
            )
            && !$template->makeFields("$name|CExObjectPicture|", false, true, false)
        ) {
            return;
        }

        $params = [
            "detail"          => 3,
            "reference_id"    => $object->_id,
            "reference_class" => $object->_class,
            "target_element"  => "ex-objects-$object->_id",
            "print"           => 1,
            "limit"           => null,
            "keep_session"    => 1,
        ];

        $formulaires = "";

        $params["limit"] = 1;
        if ($object->_id) {
            $formulaires = CApp::fetch("forms", "ajax_list_ex_object", $params);
            $formulaires = preg_replace('/\s+/', " ", $formulaires); // Remove CRLF which CKEditor transform to <br />
        }
        $template->addProperty("$name - Formulaires - Dernier", $formulaires, null, false);

        $params["limit"] = 5;
        if ($object->_id) {
            $formulaires = CApp::fetch("forms", "ajax_list_ex_object", $params);
            $formulaires = preg_replace('/\s+/', " ", $formulaires); // Remove CRLF which CKEditor transform to <br />
        }
        $template->addProperty("$name - Formulaires - 5 derniers", $formulaires, null, false);

        $params["limit"]     = 1;
        $params["only_host"] = 1;
        if ($object->_id) {
            $formulaires = CApp::fetch("forms", "ajax_list_ex_object", $params);
            $formulaires = preg_replace('/\s+/', " ", $formulaires); // Remove CRLF which CKEditor transform to <br />
        }
        $template->addProperty("$name - Formulaires - Liés", $formulaires, null, false);

        self::$_multiple_load = true;

        CExObject::initLocales();

        if ($ex_classes === null) {
            $group_id = CGroups::loadCurrent()->_id;
            $where    = [
                "ex_class.group_id = '$group_id' OR group_id IS NULL",
                "ex_class_field.in_doc_template= '1' OR ex_class_picture.in_doc_template= '1'",
            ];

            $ljoin = [
                "ex_class_field_group" => "ex_class_field_group.ex_class_id = ex_class.ex_class_id",
                "ex_class_field"       => "ex_class_field.ex_group_id = ex_class_field_group.ex_class_field_group_id",
                "ex_class_picture"     => "ex_class_picture.ex_group_id = ex_class_field_group.ex_class_field_group_id",
            ];

            $ex_class = new CExClass();
            /** @var CExClass[] $ex_classes */
            $ex_classes = $ex_class->loadList($where, "name", null, "ex_class.ex_class_id", $ljoin);

            $fields_groups = CStoredObject::massLoadBackRefs(
                $ex_classes,
                'field_groups',
                'rank, ex_class_field_group_id'
            );
            CStoredObject::massLoadBackRefs($fields_groups, 'class_pictures');

            foreach ($ex_classes as $_ex_class) {
                $_ex_class->_all_fields = $_ex_class->loadRefsAllFields();

                foreach ($_ex_class->_ref_groups as $ref_group) {
                    $ref_group->loadRefsPictures();
                }
            }
        }

        foreach ($ex_classes as $_ex_class) {
            $_name       = "Form. " . str_replace(" - ", " ", $_ex_class->name);
            $fields      = $_ex_class->_all_fields;
            $_class_name = $_ex_class->getExClassName();

            $_ex_object = null;
            if ($object->_id) {
                $_ex_object = $_ex_class->getLatestExObject($object);
            }

            if ($template->valueMode && !$_ex_object) {
                continue;
            }

            $template->addDateProperty(
                "$name - $_name - Date de saisie du form.",
                $_ex_object ? $_ex_object->datetime_create : null
            );
            $template->addTimeProperty(
                "$name - $_name - Heure de saisie du form.",
                $_ex_object ? $_ex_object->datetime_create : null
            );

            $_owner = null;
            if ($_ex_object && $_ex_object->_id) {
                $_owner = $_ex_object->loadRefOwner();
            }

            $template->addProperty("$name - $_name - Auteur du form.", $_owner ? $_owner->_view : null);
            $template->addProperty(
                "$name - $_name - Auteur (prénom) du form.",
                $_owner ? $_owner->_user_first_name : null
            );
            $template->addProperty("$name - $_name - Auteur (nom) du form.", $_owner ? $_owner->_user_last_name : null);

            foreach ($fields as $_field) {
                if (!$_field->in_doc_template) {
                    continue;
                }

                $_field_name = str_replace(" - ", " ", CAppUI::tr("$_class_name-{$_field->name}"));

                $_template_field_name = "$name - $_name - $_field_name";
                $_template_key_name   = "CExObject|$_ex_class->_id|$_field->name";

                $_has_value      = ($_ex_object && $_ex_object->_id && $_field->name != "");
                $_template_value = ($_has_value ? $_ex_object->getHtmlValue(
                    $_field->name,
                    ['no_paragraph' => true]
                ) : "");

                $template->addAdvancedData($_template_field_name, $_template_key_name, $_template_value);
            }

            $_ex_object_id = ($_ex_object && $_ex_object->_id) ? $_ex_object->_id : null;
            foreach ($_ex_class->_ref_groups as $_ex_group) {
                $_pictures = $_ex_group->loadRefsPictures();

                foreach ($_pictures as $_picture) {
                    if (!$_picture->in_doc_template) {
                        continue;
                    }

                    $_template_field_name = "$name - $_name - $_picture->name";
                    $_template_key_name   = "$name|CExObjectPicture|$_picture->_id";

                    $_options = [
                        "data"  => $_template_key_name,
                        "title" => $_picture->name,
                    ];

                    if ($_ex_object_id) {
                        $_ex_object_picture = new CExObjectPicture();
                        $where              = [
                            "ex_class_picture_id" => "= '$_picture->_id'",
                            "ex_object_id"        => "= '$_ex_object_id'",
                        ];
                        $_ex_object_picture->loadObject($where);

                        $_file              = $_ex_object_picture->loadRefDrawing();
                        $_options["width"]  = $_ex_object_picture->coord_width;
                        $_options["height"] = $_ex_object_picture->coord_height;
                    } else {
                        $_file              = $_picture->_ref_file;
                        $_options["width"]  = $_picture->coord_width;
                        $_options["height"] = $_picture->coord_height;
                    }

                    $_options["src"] = $_file->getThumbnailDataURI();

                    $template->addImageProperty($_template_field_name, $_file->_id, $_options);
                }
            }
        }

        self::$_multiple_load = false;
    }

    /**
     * Custom delete, will delete any link
     *
     * @inheritdoc
     */
    function delete()
    {
        $ex_object_id = $this->_id;
        $ex_class_id  = $this->_ex_class_id;

        if ($msg = parent::delete()) {
            return $msg;
        }

        $where = [
            "ex_class_id"  => " = '$ex_class_id'",
            "ex_object_id" => " = '$ex_object_id'",
        ];

        // Remove CExLinks
        $ex_link  = new CExLink();
        $ex_links = $ex_link->loadList($where);
        foreach ($ex_links as $_ex_link) {
            $_ex_link->delete();
        }

        // Remove CExObjectPictures
        $ex_object_picture  = new CExObjectPicture();
        $ex_object_pictures = $ex_object_picture->loadList($where);
        foreach ($ex_object_pictures as $_ex_object_picture) {
            $_ex_object_picture->delete();
        }

        // Remove triggered CExObjectPictures
        $where              = [
            "triggered_ex_class_id"  => " = '$ex_class_id'",
            "triggered_ex_object_id" => " = '$ex_object_id'",
        ];
        $ex_object_picture  = new CExObjectPicture();
        $ex_object_pictures = $ex_object_picture->loadList($where);
        foreach ($ex_object_pictures as $_ex_object_picture) {
            $_ex_object_picture->delete();
        }

        return null;
    }

    /**
     * Get owner : the person who created $this
     *
     * @return CMediusers
     */
    function loadRefOwner()
    {
        $this->getOwnerId();

        return $this->_ref_owner = $this->loadFwdRef("owner_id");
    }

    /**
     * Get owner ID, save it if it's not present
     *
     * @return int
     */
    function getOwnerId()
    {
        if (!$this->owner_id) {
            $this->updateCreationFields();
        }

        return $this->owner_id;
    }

    /**
     * Get creation date, save it if it's not present
     *
     * @return string
     */
    function getCreateDate()
    {
        if (!$this->datetime_create) {
            $this->updateCreationFields();
        }

        return $this->datetime_create;
    }

    /**
     * Get owner ID, save it if it's not present
     *
     * @return string
     */
    function getEditDate()
    {
        if (!$this->datetime_edit) {
            $this->updateEditFields();
        }

        return $this->datetime_edit;
    }

    /**
     * Update creation fields : datetime_create and owner_id
     *
     * @return void
     */
    function updateCreationFields()
    {
        if (!$this->_id || ($this->datetime_create && $this->owner_id)) {
            return;
        }

        $log = $this->loadFirstLog();

        // Don't use store here because we don't want to log this action ...
        $ds         = $this->getDS();
        $table_name = $this->getTableName();
        $query      = $ds->prepare(
            "UPDATE $table_name SET datetime_create = ?1, owner_id = ?2 WHERE ex_object_id = ?3;",
            $log->date,
            $log->user_id,
            $this->_id
        );
        $ds->exec($query);

        $this->datetime_create = $log->date;
        $this->owner_id        = $log->user_id;
    }

    /**
     * Update creation fields : datetime_create and owner_id
     *
     * @return void
     */
    function updateEditFields()
    {
        if (!$this->_id || $this->datetime_edit) {
            return;
        }

        $log = $this->loadLastLog();

        // Don't use store here because we don't want to log this action ...
        $ds         = $this->getDS();
        $table_name = $this->getTableName();
        $query      = $ds->prepare(
            "UPDATE $table_name SET datetime_edit = ?1 WHERE ex_object_id = ?2;",
            $log->date,
            $this->_id
        );
        $ds->exec($query);

        $this->datetime_edit = $log->date;
    }

    /**
     * @inheritdoc
     */
    function loadRelPatient()
    {
        $this->_rel_patient = null;
        $target             = $this->loadTargetObject();

        if (in_array(IPatientRelated::class, class_implements($target))) {
            if ($target->_id) {
                $rel_patient = $target->loadRelPatient();
            } else {
                $rel_patient = new CPatient;
            }

            $this->_rel_patient = $rel_patient;
        }

        return $this->_rel_patient;
    }

    /**
     * Get the patient_id of CMbobject
     *
     * @return CPatient
     */
    function getIndexablePatient()
    {
        return $this->loadRelPatient();
    }

    /**
     * Get the praticien_id of CMbobject
     *
     * @return CMediusers
     */
    function getIndexablePraticien()
    {
        $ref_object = $this->getReferenceObject("CSejour");
        if (!$ref_object) {
            $ref_object = $this->getReferenceObject("CConsultation");
        }
        if ($ref_object && $ref_object->loadRefPraticien()) {
            return $ref_object->_ref_praticien;
        }

        return $this->loadRefOwner();
    }

    /**
     * Loads the related fields for indexing datum
     *
     * @return array
     */
    function getIndexableData()
    {
        $prat = $this->getIndexablePraticien();
        if (!$prat) {
            $prat = new CMediusers();
        }
        $array["id"]        = $this->_id;
        $array["author_id"] = $this->getOwnerId();
        $array["prat_id"]   = $prat->_id;
        $array["title"]     = $this->loadRefExClass()->name;

        $content = CApp::fetch(
            "forms",
            "view_ex_object",
            [
                "ex_class_id"  => $this->_ex_class_id,
                "ex_object_id" => $this->_id,
            ]
        );

        $array["body"] = $this->getIndexableBody($content);
        $date          = $this->getCreateDate();
        if (!$date) {
            $date = CMbDT::dateTime();
        }
        $array["date"]        = str_replace("-", "/", $date);
        $array["function_id"] = $prat->function_id;
        $array["group_id"]    = $this->group_id;
        $array["patient_id"]  = $this->getIndexablePatient()->_id;

        $ref_object = $this->getReferenceObject("CSejour");
        if ($ref_object) {
            $array["object_ref_id"]    = $ref_object->_id;
            $array["object_ref_class"] = $ref_object->_class;
        } else {
            $ref_object                = $this->getReferenceObject("CConsultation");
            $array["object_ref_id"]    = $ref_object->_id;
            $array["object_ref_class"] = $ref_object->_class;
        }
        $array["ex_class_id"] = $this->_ex_class_id;

        return $array;
    }

    /**
     * @inheritdoc
     */
    function getIndexableBody($content)
    {
        return CSearchIndexing::getRawText($content);
    }

    /**
     * Special load target object, to load the one referenced by the ex_link
     *
     * @param bool $cache Use cache or not
     *
     * @return CMbObject
     * @throws Exception
     * @deprecated
     *
     */
    function loadTargetObject($cache = true)
    {
        // Todo: Enable after ref checking
        //    if (!$this->_ref_object || !$this->_ref_object->_id && ($this->object_class && $this->object_id)) {
        //      return $this->_ref_object = CStoredObject::loadFromGuid("{$this->object_class}-{$this->object_id}");
        //    }

        $link = new CExLink();

        $ds    = $link->getDS();
        $where = [
            "ex_class_id"  => $ds->prepare("= ?", $this->_ex_class_id),
            "ex_object_id" => $ds->prepare("= ?", $this->_id),
            "level"        => "= 'object'",
        ];

        $link->loadObject($where);

        if ($link->_id) {
            return $this->_ref_object = $link->loadTargetObject($cache);
        }

        return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
    }

    /**
     * Return idex type if it's special (e.g. AppFine/...)
     *
     * @param CIdSante400 $idex Idex
     *
     * @return string|null
     */
    function getSpecialIdex(CIdSante400 $idex)
    {
        if (CModule::getActive("appFineClient")) {
            if ($idex_type = CAppFineClient::getSpecialIdex($idex)) {
                return $idex_type;
            }
        }

        return null;
    }

    /**
     * Checks if a list of ex_objects is verified
     *
     * @param self[] $ex_objects The list to check
     *
     * @return void
     */
    static function checkVerified(array $ex_objects)
    {
        $idexs = CIdSante400::massGetMatchFor($ex_objects, "verified");

        foreach ($idexs as $_idex) {
            if (!isset($ex_objects[$_idex->object_id])) {
                continue;
            }

            $_ex_object = $ex_objects[$_idex->object_id];
            if (!$_ex_object->_id || $_ex_object->_id != $_idex->object_id) {
                continue;
            }

            $_ex_object->_verified = ($_idex->id400 == 1) ? "yes" : "no";
        }
    }

    /**
     * Checks if $this is verified
     *
     * @return bool|null
     */
    function isVerified()
    {
        if (!$this->_id) {
            return null;
        }

        $idex = CIdSante400::getMatch($this->_class, "verified", null, $this->_id);

        if ($idex->_id) {
            $this->_verified = ($idex->id400 == 1) ? "yes" : "no";
        }

        return $this->_verified;
    }

    /**
     * Should only be used for generic use cases and not by default (prefer canPerm method).
     *
     * @inheritdoc
     */
    public function getPerm($permType)
    {
        if (CExClass::inHermeticMode(true)) {
            if ($this->_ref_ex_class && $this->_ref_ex_class->group_id) {
                $group = CGroups::get($this->_ref_ex_class->group_id);

                if (!$group->canDo()->read) {
                    return false;
                }
            }
        }

        switch ($permType) {
            case PERM_READ:
                return $this->canPerm('v');

            case PERM_EDIT:
                return $this->canPerm('e');

            case PERM_DENY:
                // Todo: ExObject::canPerm('d') does not seem to be correctly implemented (nor used, anyway).
                return $this->canPerm('d');

            default:
                return parent::getPerm($permType);
        }
    }

    /**
     * Check permission vs user type
     *
     * @param string $perm Permission type (c, e, v, d)
     *
     * @return bool
     */
    function canPerm($perm)
    {
        $ex_class    = $this->loadRefExClass();
        $permissions = $ex_class->permissions;

        if (!$permissions || $permissions === "{}") {
            return true;
        }

        $permissions = $ex_class->getPermissions();

        $me   = CMediusers::get();
        $type = $me->_user_type;

        if ($this->owner_id == $me->_id) {
            return true;
        }

        foreach ($permissions as $_type => $_permissions) {
            // Not the author
            if ($_type == -10 && $this->owner_id != $me->_id) {
                if (!$this->_id) {
                    continue;
                }

                return CExClass::checkPermission($_permissions, $perm);
            }

            if ($_type == $type || $_type == 10000) {
                return CExClass::checkPermission($_permissions, $perm);
            }
        }

        return false;
    }

    /**
     * Get threshold alerts
     *
     * @param CStoredObject[] $objects Objects of the same class
     *
     * @return array
     */
    static function getThresholdAlerts(array $objects, $ex_class_id = null)
    {
        if (count($objects) === 0) {
            return [];
        }

        $object = reset($objects);

        CView::enforceSlave(false);

        if (!$ex_class_id) {
            $ex_class       = new CExClass();
            $where_ex_class = [
                "ex_class_field.disabled" => "= '0'",
                "ex_class_field.formula"  => "IS NOT NULL",
                //"ex_class_field.result_in_title"  => "= '1'",
                "ex_class_field.result_threshold_low IS NOT NULL OR ex_class_field.result_threshold_high IS NOT NULL",
            ];

            $ljoin_ex_class = [
                "ex_class_field_group" => "ex_class_field_group.ex_class_id = ex_class.ex_class_id",
                "ex_class_field"       => "ex_class_field.ex_group_id = ex_class_field_group.ex_class_field_group_id",
            ];

            $ex_class_ids = $ex_class->loadIds($where_ex_class, null, null, "ex_class_id", $ljoin_ex_class);
        } else {
            $ex_class_ids = [$ex_class_id];
        }

        CView::disableSlave();

        if (count($ex_class_ids) === 0) {
            return [];
        }

        $ex_link = new CExLink();
        $ds      = $ex_link->getDS();

        // Tout ça pour charger le dernier ex_link (datetime_create) de chaque
        // couple ex_class / object sans avoir à charger tous les ex_links
        $query = "SELECT ex_link_id, ex_class_id, object_id
              FROM ex_link
              WHERE (`object_class` = '$object->_class')
              AND (`object_id` " . $ds->prepareIn(CMbArray::pluck($objects, "_id")) . ")
              AND (`ex_class_id` " . $ds->prepareIn($ex_class_ids) . ")
              ORDER BY datetime_create DESC;";

        $ex_link_hashes = $ds->loadHashAssoc($query);
        $ex_link_ids    = [];
        foreach ($ex_link_hashes as $_hash) {
            // Simulation du group by
            $_key = $_hash["ex_class_id"] . "-" . $_hash["object_id"];
            if (!isset($ex_link_ids[$_key])) {
                $ex_link_ids[$_key] = $_hash["ex_link_id"];
            }
        }

        $ex_links = $ex_link->loadAll(array_values($ex_link_ids));

        CExLink::massLoadExObjects($ex_links);

        $alerts = [];

        foreach ($ex_links as $_ex_link) {
            /** @var CExObject $_ex_object */
            $_ex_object = $_ex_link->loadRefExObject();

            $_ex_class  = $_ex_object->loadRefExClass();
            $_ex_fields = $_ex_class->getFormulaExFields();

            foreach ($_ex_fields as $_ex_field) {
                if ($_ex_class->_id != $_ex_link->ex_class_id) {
                    continue;
                }

                $result = $_ex_object->{$_ex_field->name};

                if ($result !== null) {
                    $alert = [
                        "ex_class"       => $_ex_class,
                        "ex_class_field" => $_ex_field,
                        "ex_link"        => $_ex_link,
                        "ex_object"      => $_ex_object,
                        "result"         => $result,
                    ];

                    $_alert = null;

                    if ($_ex_field->result_threshold_low !== null && $result < $_ex_field->result_threshold_low) {
                        $_alert              = $alert;
                        $_alert["alert"]     = "low";
                        $_alert["threshold"] = $_ex_field->result_threshold_low;
                    } elseif ($_ex_field->result_threshold_high !== null && $result > $_ex_field->result_threshold_high) {
                        $_alert              = $alert;
                        $_alert["alert"]     = "high";
                        $_alert["threshold"] = $_ex_field->result_threshold_high;
                    }

                    if ($_alert) {
                        if (!isset($alerts[$_ex_link->object_id])) {
                            $alerts[$_ex_link->object_id] = [];
                        }

                        if (!isset($alerts[$_ex_link->object_id][$_ex_link->ex_class_id])) {
                            $alerts[$_ex_link->object_id][$_ex_link->ex_class_id] = [];
                        }

                        $alerts[$_ex_link->object_id][$_ex_link->ex_class_id][] = $_alert;
                    }
                }
            }
        }

        return $alerts;
    }

    /**
     * Server side formula evaluation, not exactly the same as client side (missing functions)
     *
     * @return void
     */
    function evaluateFormulas()
    {
        $fields = $this->loadRefExClass()->loadRefsAllFields();

        $variables = [];

        // Get variables
        foreach ($fields as $_field) {
            $_field_name = $_field->name;

            $_spec = $_field->getSpecObject();

            $_value = $this->{$_field_name};

            if ($_value === null || $_value === '') {
                continue;
            }

            switch ($_spec->getSpecType()) {
                case "dateTime":
                case "date":
                    $_value = CMbDT::toTimestamp($_value);
                    break;

                case "time":
                    $_value = CMbDT::toTimestamp("1970-01-01 $_value");
                    break;

                case "enum":
                    $_values = $_field->getFormulaValues();
                    if (is_array($_values) && isset($_values[$_value])) {
                        $_value = $_values[$_value];
                    }
                    break;

                default:
                    // Ignore
                    break;
            }

            $variables[$_field_name] = $_value;
        }

        // Evaluate formulas
        foreach ($fields as $_field) {
            if (!$_field->formula) {
                continue;
            }

            $_field_name = $_field->name;

            $_formula = preg_replace('/\[([^\]]+)\]/', '$\\1', $_field->formula);

            try {
                $this->{$_field_name} = CMbMath::evaluate($_formula, $variables);
            } catch (Exception $e) {
            }
        }
    }

    /**
     * Initialize completion level field
     *
     * @return void
     */
    function setCompleteness()
    {
        /** @var CExClassField[] $fields */
        $fields = $this->getCompletenessFields();

        $nb_fields       = count($fields);
        $nb_empty_fields = 0;
        $nb_alert_fields = 0;

        foreach ($fields as $_ex_field) {
            if ($this->fieldIsEmpty($_ex_field)) {
                $nb_empty_fields++;

                // If field is not set, not to consider it as a potential alert
                continue;
            }

            if ($_ex_field->formula === null) {
                continue;
            }

            if (($_ex_field->result_threshold_low !== null && ($this->{$_ex_field->name} < $_ex_field->result_threshold_low))
                || ($_ex_field->result_threshold_high !== null && ($this->{$_ex_field->name} > $_ex_field->result_threshold_high))
            ) {
                $nb_alert_fields++;
            }
        }

        $this->nb_alert_fields = $nb_alert_fields;

        $this->completeness_level = 'some';

        if (!$nb_empty_fields) {
            $this->completeness_level = 'all';
        } elseif ($nb_empty_fields === $nb_fields) {
            $this->completeness_level = 'none';
        }
    }

    /**
     * Returns fields used in completeness computation
     *
     * @return array
     */
    function getCompletenessFields()
    {
        // List of fields hidden due to predicates
        $hidden_fields = explode('|', $this->_hidden_fields ?? '');

        return array_filter(
            $this->loadRefExClass()->loadRefsAllFields(),
            function ($_ex_field) use ($hidden_fields) {
                return (!$_ex_field->disabled && !$_ex_field->auto_increment && !in_array(
                        $_ex_field->name,
                        $hidden_fields
                    ) && $_ex_field->in_completeness);
            }
        );
    }

    /**
     * Tells if a field is empty
     *
     * @param CExClassField $ex_field Field to test
     *
     * @return bool
     */
    function fieldIsEmpty($ex_field)
    {
        return (($this->{$ex_field->name} === null) || ($this->{$ex_field->name} === ''));
    }

    /**
     * @return void
     */
    function checkAutoIncrements()
    {
        foreach ($this->loadRefExClass()->loadRefsAllFields() as $_field) {
            if (!$_field->shouldAutoIncrement()) {
                continue;
            }

            $this->{$_field->name} = ($this->{$_field->name}) ? ++$this->{$_field->name} : 1;
        }
    }

    /**
     * Trying to repair CExObject object reference with CExLink
     *
     * @param integer $ex_class_id  CExClass id
     * @param integer $ex_object_id CExObject id
     * @param string  $object_guid  Object GUID that may be replaced
     * @param CExLink $ex_link      CExLink to use to repaire the CExObject
     *
     * @return bool|CMbObject
     */
    static function repairReferences($ex_class_id, $ex_object_id, $object_guid = null, CExLink $ex_link = null)
    {
        if (!$ex_class_id || !$ex_object_id || (!$object_guid && !$ex_link)) {
            return false;
        }

        if (!$ex_link) {
            $ex_link               = new CExLink();
            $ex_link->ex_class_id  = $ex_class_id;
            $ex_link->ex_object_id = $ex_object_id;
            $ex_link->level        = 'object';
            $ex_link->loadMatchingObjectEsc();
        }

        if (!$ex_link->_id) {
            return false;
        }

        $object = $ex_link->loadTargetObject();

        if ($object && $object->_id && (!$object_guid || ($object->_guid != $object_guid))) {
            $ex_object = new static($ex_class_id);
            $ex_object->load($ex_object_id);

            if (!$ex_object || !$ex_object->_id) {
                return false;
            }

            if ($object->_guid != "{$ex_object->object_class}-{$ex_object->object_id}") {
                $old_id    = $ex_object->_id;
                $old_class = $ex_object->_class;

                $ex_object->nullifyProperties();

                $ex_object->_id    = $old_id;
                $ex_object->_class = $old_class;
                $ex_object->setExClass($ex_class_id);

                switch ($ex_link->level) {
                    case 'object':
                        $ex_object->setObject($object);
                        break;

                    case 'ref1':
                        $ex_object->setReferenceObject_1($object);
                        break;

                    case 'ref2':
                        $ex_object->setReferenceObject_2($object);
                        break;

                    case 'add':
                        $ex_object->setAdditionalObject($object);
                        break;

                    default:
                }

                if ($msg = $ex_object->store()) {
                    return false;
                }
            }

            return $object;
        }

        return false;
    }


    /**
     * @param CStoredObject $object
     *
     * @return void
     * @deprecated
     */
    public function setObject(CStoredObject $object)
    {
        CMbMetaObjectPolyfill::setObject($this, $object);
    }

    /**
     * @inheritDoc
     * @todo remove
     */
    function loadRefsFwd()
    {
        parent::loadRefsFwd();
        $this->loadTargetObject();
    }

    /**
     * @inheritDoc
     */
    function makeTemplatePath($name)
    {
        if (null == $module = $this->_ref_module) {
            return null;
        }

        $path = "$module->mod_name/templates/CExObject";

        return "{$path}_{$name}.tpl";
    }

    /**
     * @param false $force
     *
     * @return CFile|null
     */
    public function toPDF($force = false): ?CFile
    {
        if (!$this->_id || !$this->_ex_class_id) {
            return null;
        }

        $file = $this->loadNamedFile(self::PDF_FILENAME);

        if (!$force && $file && $file->_id) {
            return $file;
        }

        try {
            $converter = new ExObjectPDFConverter($this, $this->loadTargetObject());

            $file->author_id    = CMediusers::get()->_id;
            $file->file_date    = CMbDT::dateTime();
            $file->file_type    = CMbPath::guessMimeType(self::PDF_FILENAME);
            $file->object_class = $this->_class;
            $file->object_id    = $this->_id;

            $file->fillFields();
            $file->updateFormFields();

            $file->setContent($converter->convert());

            if ($msg = $file->store()) {
                throw new CMbException($msg);
            }
        } catch (Exception $e) {
            CAppUI::setMsg($e->getMessage(), UI_MSG_ERROR);

            return null;
        }

        return $file;
    }
}
