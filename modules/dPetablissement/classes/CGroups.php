<?php
/**
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Etablissement;

use Exception;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CEntity;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\Composer\CComposerScript;
use Ox\Core\CStoredObject;
use Ox\Core\Handlers\Events\ObjectHandlerEvent;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CDomain;
use Ox\Interop\Eai\CGroupDomain;
use Ox\Interop\Eai\CMbOID;
use Ox\Mediboard\Admin\CSourceLDAPLink;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CSSPI;
use Ox\Mediboard\Cabinet\CBanque;
use Ox\Mediboard\Dmi\CDMCategory;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Medimail\CMedimailAccount;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mpm\CConfigMomentUnitaire;
use Ox\Mediboard\PlanningOp\CProtocoleOperatoire;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\Sante400\CIncrementer;
use Ox\Mediboard\System\CGeoLocalisation;
use Ox\Mediboard\System\IGeocodable;
use Symfony\Component\Routing\RouterInterface;

/**
 * Group class (Etablissement)
 */
class CGroups extends CEntity implements IGeocodable
{
    /** @var string */
    public const RESOURCE_TYPE = 'group';

    /** @var string */
    public const RELATION_HYPER_TEXT_LINKS = "hyperTextLinks";

    /** @var string */
    public const FIELDSET_CONTACT    = "contact";
    public const FIELDSET_IDENTIFIER = "identifier";

    public $group_id;

    // DB Fields
    public $text;
    public $raison_sociale;
    public $oid;
    public $adresse;
    public $cp;
    public $ville;
    public $tel;
    public $fax;
    public $mail;
    public $mail_apicrypt;
    public $web;
    public $directeur;
    public $domiciliation;
    public $siret;
    public $ape;
    public $tel_anesth;
    public $service_urgences_id;
    public $pharmacie_id;
    public $finess;
    public $chambre_particuliere;
    public $ean;
    public $rcc;
    public $lat;
    public $lon;
    public $legal_entity_id;

    // Form fields
    public $_cp_court;
    public $_is_ipp_supplier = false;
    public $_is_nda_supplier = false;

    /** @var CFunctions[] */
    public $_ref_functions;

    /** @var CBlocOperatoire[] */
    public $_ref_blocs;

    /** @var CSSPI[] */
    public $_ref_sspis;

    /** @var CDMCategory[] */
    public $_ref_dm_categories;

    /** @var CService[] */
    public $_ref_services;

    /** @var CFunctions */
    public $_ref_pharmacie;

    /** @var CFunctions */
    public $_ref_service_urgences;

    /** @var self */
    static $_ref_current = null;

    /** @var CLegalEntity */
    public $_ref_legal_entity;

    /** @var CFile */
    public $_ref_logo;

    /** @var CGeoLocalisation */
    public $_ref_geolocalisation;

    /** @var CSourceLDAPLink[] */
    public $_ref_source_ldap_links;

    /** @var CBanque[] */
    public $_ref_banques;

    /** @var CProtocoleOperatoire[] */
    public $_ref_protocoles_op = [];

    private static $fields_etiq = ["ETAB NOM"];

    /** @var CMedimailAccount */
    public $_ref_medimail_account;

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'groups_mediboard';
        $spec->key   = 'group_id';

        return $spec;
    }

    /**
     * @inheritDoc
     *
     * ATTENTION : Ne pas appeler de configurations par établissement dans cette fonction
     */
    function getProps()
    {
        $props                         = parent::getProps();
        $props["user_id"]              .= " back|groups";
        $props["text"]                 = "str notNull confidential seekable fieldset|default";
        $props["raison_sociale"]       = "str maxLength|50 fieldset|default";
        $props["oid"]                  = "str maxLength|50 fieldset|identifier";
        $props["adresse"]              = "text confidential fieldset|contact";
        $props["cp"]                   = "str minLength|4 maxLength|10 fieldset|contact";
        $props["ville"]                = "str maxLength|50 confidential fieldset|contact";
        $props["tel"]                  = "phone fieldset|contact";
        $props["fax"]                  = "phone fieldset|contact";
        $props["tel_anesth"]           = "phone fieldset|contact";
        $props["service_urgences_id"]  = "ref class|CFunctions back|services_urgence_pour";
        $props["pharmacie_id"]         = "ref class|CFunctions back|pharmacie_pour";
        $props["directeur"]            = "str maxLength|50";
        $props["domiciliation"]        = "str maxLength|9";
        $props["siret"]                = "str length|14 fieldset|identifier";
        $props["ape"]                  = "str maxLength|6 confidential fieldset|identifier";
        $props["mail"]                 = "email fieldset|contact";
        $props["mail_apicrypt"]        = "email fieldset|contact";
        $props["web"]                  = "str fieldset|contact";
        $props["finess"]               = "str length|9 confidential mask|9xS9S99999S9 fieldset|identifier";
        $props["chambre_particuliere"] = "bool notNull default|0";
        $props["ean"]                  = "str";
        $props["rcc"]                  = "str";
        $props["lat"]                  = "float";
        $props["lon"]                  = "float";
        $props["legal_entity_id"]      = "ref class|CLegalEntity back|groups";

        $props["_cp_court"] = "numchar length|2";

        $props['code'] .= " fieldset|identifier";

        return $props;
    }

    /**
     * @see parent::updateFormFields()
     */
    function updateFormFields()
    {
        parent::updateFormFields();
        $this->_view      = $this->_name;
        $this->_shortview = CMbString::truncate($this->_name);
        $this->_cp_court  = $this->cp ? substr($this->cp, 0, 2) : '';
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        $is_new = !$this->_id;

        if ($msg = parent::store()) {
            return $msg;
        }

        $cache = new Cache('CGroups', 'all', Cache::INNER_OUTER);
        $cache->rem();

        if ($is_new) {
            if (CModule::getActive("mpm")) {
                CConfigMomentUnitaire::emptySHM();
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    function delete()
    {
        $del = parent::delete();

        // Remove the cache after the delete to avoid displaying a deleted group
        $cache = new Cache('CGroups', 'all', Cache::INNER_OUTER);
        $cache->rem();

        return $del;
    }

    /**
     * Load functions with given permission
     *
     * @param int $permType Permission level
     *
     * @return CFunctions[]
     */
    function loadFunctions($permType = PERM_READ)
    {
        return $this->_ref_functions = CMediusers::loadFonctions($permType, $this->_id);
    }

    /**
     * Load blocs operatoires with given permission
     *
     * @param int    $permType    Permission level
     * @param bool   $load_salles Load salles
     * @param string $order       Ordre de chargmeent SQL
     * @param array  $where       SQL WHERE parameters
     * @param array  $whereSalle  SQL WHERE parameters
     *
     * @return CBlocOperatoire[]
     */
    function loadBlocs($permType = PERM_READ, $load_salles = true, $order = "nom", $where = [], $whereSalle = [])
    {
        $bloc       = new CBlocOperatoire();
        $whereGroup = [
            'group_id' => "= '$this->_id'",
        ];
        $where      = array_merge($where, $whereGroup);

        /** @var CBlocOperatoire[] $blocs */
        $blocs = $bloc->loadListWithPerms($permType, $where, $order);

        if ($load_salles) {
            CStoredObject::massLoadBackRefs($blocs, "salles", "nom", $whereSalle);
            foreach ($blocs as $_bloc) {
                $_bloc->loadRefsSalles($whereSalle);
            }
        }

        return $this->_ref_blocs = $blocs;
    }

    /**
     * Lazy access to a given group, default is current group.
     *
     * @param int|null $group_id
     *
     * @return false|mixed|CGroups
     * @throws Exception
     */
    public static function get($group_id = null)
    {
        CApp::failIfPublic();

        global $g;
        $group_id = $group_id ?: $g;

        $cache = new Cache('CGroups', 'all', Cache::INNER_OUTER, 60);
        if ($cache->exists()) {
            $groups = $cache->get();
        } else {
            $group  = new self();
            $groups = $group->loadList(null, "text");
            $cache->put($groups);
        }

        // Special case when build classref
        if ($groups === null && CComposerScript::$is_running) {
            $groups   = [];
            $groups[] = new CGroups();
        }

        // Special case for admin where $g isn't set and method is called without parameters
        return $groups[$group_id] ?? reset($groups);
    }

    /**
     * Load SSPI
     *
     * @param int  $permType  Permission level
     * @param bool $load_bloc Load blocs
     *
     * @return CSSPI[]
     */
    function loadSSPIs($permType = PERM_READ, $load_bloc = true)
    {
        $sspi  = new CSSPI();
        $where = [
            "group_id" => "= '$this->_id'",
        ];

        /** @var CSSPI[] */
        $sspis = $sspi->loadListWithPerms($permType, $where, "libelle");

        if ($load_bloc) {
            $links_sspi = CStoredObject::massLoadBackRefs($sspis, "links_sspi");
            CStoredObject::massLoadFwdRef($links_sspi, "bloc_id");

            foreach ($sspis as $_sspi) {
                $_sspi->loadRefsBlocs();
            }
        }

        return $this->_ref_sspis = $sspis;
    }

    /**
     * @see parent::loadRefsBack()
     */
    function loadRefsBack()
    {
        $this->loadFunctions();
    }

    /**
     * Get group's services
     *
     * @return CService[]
     */
    function loadRefsServices()
    {
        return $this->_ref_services = $this->loadBackRefs("services", "nom");
    }

    /**
     * Get pharmacy function
     *
     * @return CFunctions
     */
    function loadRefPharmacie()
    {
        return $this->_ref_pharmacie = $this->loadFwdRef("pharmacie_id");
    }

    /**
     * Get emergency function
     *
     * @return CFunctions
     */
    function loadRefServiceUrgences()
    {
        return $this->_ref_service_urgences = $this->loadFwdRef("service_urgences_id");
    }

    /**
     * Load groups with given permission
     *
     * @param int $permType Permission level
     *
     * @return self[]
     */
    static function loadGroups($permType = PERM_READ)
    {
        $cache = new Cache('CGroups', 'all', Cache::INNER_OUTER, 60);
        if ($cache->exists()) {
            $groups = $cache->get();
        } else {
            $group  = new self();
            $groups = $group->loadList(null, "text");
        }
        self::filterByPerm($groups, $permType);

        return $groups;
    }

    /**
     * @see parent::fillLimitedTemplate()
     */
    function fillLimitedTemplate(&$template, $prefixe = null)
    {
        $this->notify(ObjectHandlerEvent::BEFORE_FILL_LIMITED_TEMPLATE(), $template);

        if ($prefixe) {
            $prefixe = "$prefixe - ";
        }

        $etablissement_section = CAppUI::tr('CFunctions-group_id');

        $template->addProperty($prefixe . "$etablissement_section - " . CAppUI::tr('common-Name'), $this->text);
        $template->addProperty(
            $prefixe . "$etablissement_section - " . CAppUI::tr('CFunctions-adresse'),
            "$this->adresse \n $this->cp $this->ville"
        );
        $template->addProperty($prefixe . "$etablissement_section - " . CAppUI::tr('CFunctions-ville'), $this->ville);
        $template->addProperty(
            $prefixe . "$etablissement_section - " . CAppUI::tr('CFunctions-tel'),
            $this->getFormattedValue("tel")
        );
        $template->addProperty(
            $prefixe . "$etablissement_section - " . CAppUI::tr('CGroups-fax-court'),
            $this->getFormattedValue("fax")
        );
        $template->addProperty(
            $prefixe . "$etablissement_section - " . CAppUI::tr('CGroups-mail'),
            $this->getFormattedValue("mail")
        );
        $template->addProperty(
            $prefixe . "$etablissement_section - " . CAppUI::tr('CGroups-mail_apicrypt'),
            $this->getFormattedValue("mail_apicrypt")
        );
        $template->addProperty(
            $prefixe . "$etablissement_section - " . CAppUI::tr('CGroups-domiciliation'),
            $this->domiciliation
        );
        $template->addProperty($prefixe . "$etablissement_section - " . CAppUI::tr('CMbFieldSpec.siret'), $this->siret);
        $template->addProperty(
            $prefixe . "$etablissement_section - " . CAppUI::tr('CGroups-finess-court'),
            $this->finess
        );
        $template->addProperty($prefixe . "$etablissement_section - " . CAppUI::tr('CGroups-Ape'), $this->ape);

        $barcode = [
            "barcode" => [
                "title" => CAppUI::tr("{$this->_class}-finess"),
            ],
        ];
        $template->addBarCode(
            $prefixe . "$etablissement_section - " . CAppUI::tr('CGroups-FINESS Bar Code'),
            $this->finess,
            $barcode
        );
        $template->addProperty($prefixe . "$etablissement_section - " . CAppUI::tr('CGroups-web'), $this->web);

        $this->notify(ObjectHandlerEvent::AFTER_FILL_LIMITED_TEMPLATE(), $template);
    }

    /**
     * @see parent::fillTemplate()
     */
    function fillTemplate(&$template)
    {
        $this->fillLimitedTemplate($template);
    }

    /**
     * Load the current group
     *
     * @return self
     */
    static function loadCurrent()
    {
        return self::$_ref_current = self::get();
    }

    /**
     * @return CGroups|null
     */
    static function getCurrent()
    {
        return self::$_ref_current;
    }

    /**
     * Get DMI categories
     * todo namespace
     * @return CDMCategory[]
     */
    function loadRefsDMCategories()
    {
        return $this->_ref_dm_categories = $this->loadBackRefs("dm_categories", "nom");
    }

    /**
     * Construit le tag de l'établissement en fonction des variables de configuration
     *
     * @return string|null
     */
    function getTagGroup()
    {
        // Pas de tag sur l'établiessement
        if (null == $tag_group = CAppUI::conf("dPetablissement tag_group")) {
            return null;
        }

        return str_replace('$g', $this->_id, $tag_group);
    }

    /**
     * Récupère les congés pour un pays
     *
     * @param string $date          the date to check
     * @param bool   $includeRegion are the territory holidays included ?
     *
     * @return array
     * @deprecated use CMbDT::getHolidays instead
     *
     */
    function getHolidays($date = null, $includeRegion = true)
    {
        return CMbDT::getHolidays($date, $includeRegion);
    }

    /**
     * Charge l'idex de l'établissement
     *
     * @return string|null
     */
    function loadIdex()
    {
        $tag_group = $this->getTagGroup();

        if (!$this->_id || !$tag_group) {
            return null;
        }

        // Récupération du premier idex créé
        $order = "id400 ASC";

        // Recuperation de la valeur de l'id400
        $idex = new CIdSante400();
        $idex->setObject($this);
        $idex->tag = $tag_group;
        $idex->loadMatchingObject($order);

        return $idex->id400;
    }

    /**
     * Load the domain supplier
     *
     * @param string $domain_type Domain type (CSejour, CPatient, etc)
     *
     * @return null|CIncrementer
     */
    function loadDomainSupplier($domain_type)
    {
        if (!$this->_id) {
            return null;
        }

        $group_domain               = new CGroupDomain();
        $group_domain->object_class = $domain_type;
        $group_domain->group_id     = $this->_id;
        $group_domain->master       = true;
        $group_domain->loadMatchingObject();

        if (!$group_domain->_id) {
            return null;
        }

        return $group_domain->loadRefDomain()->loadRefIncrementer();
    }

    /**
     * Load the domain supplier
     *
     * @param string $domain_type Domain type (CSejour, CPatient, etc)
     *
     * @return null|CDomain
     */
    function loadDomain($domain_type)
    {
        if (!$this->_id) {
            return null;
        }

        $group_domain               = new CGroupDomain();
        $group_domain->object_class = $domain_type;
        $group_domain->group_id     = $this->_id;
        $group_domain->master       = true;
        $group_domain->loadMatchingObject();

        if (!$group_domain->_id) {
            return null;
        }

        return $group_domain->loadRefDomain();
    }

    /**
     * Is the group a domain supplier ?
     *
     * @param string $domain_type Domain type (CSejour, CPatient, etc)
     *
     * @return bool
     */
    function isNumberSupplier($domain_type)
    {
        $incrementer = self::loadDomainSupplier($domain_type);

        return !$incrementer || !$incrementer->_id ? 0 : 1;
    }

    /**
     * Is the group an IPP supplier ?
     *
     * @return bool
     */
    function isIPPSupplier()
    {
        return $this->_is_ipp_supplier = $this->isNumberSupplier("CPatient");
    }

    /**
     * Is the group an NDA supplier ?
     *
     * @return bool
     */
    function isNDASupplier()
    {
        return $this->_is_nda_supplier = $this->isNumberSupplier("CSejour");
    }

    /**
     * @see parent::mapEntityTo()
     */
    function mapEntityTo()
    {
        $this->_name = $this->text;
    }

    /**
     * @see parent::mapEntityFrom()
     */
    function mapEntityFrom()
    {
        $this->text = $this->_name;
    }

    /**
     * Method to load Legal Entity
     *
     * @return CLegalEntity
     */
    function loadRefLegalEntity()
    {
        return $this->_ref_legal_entity = $this->loadFwdRef("legal_entity_id", true);
    }

    /**
     * Charge le logo de l'établissement
     *
     * @return CFile
     */
    function loadRefLogo()
    {
        return $this->_ref_logo = $this->loadNamedFile("CGroups_logo.jpg");
    }

    /**
     * @see parent::completeLabelFields()
     */
    function completeLabelFields(&$fields, $params)
    {
        $fields_sejour = [
            self::$fields_etiq[0] => $this->raison_sociale,
        ];
        $fields        = array_merge($fields, $fields_sejour);
    }

    /**
     * @inheritdoc
     */
    function getGeocodeFields()
    {
        return [
            'adresse',
            'cp',
            'ville',
        ];
    }

    function getAddress()
    {
        return $this->adresse;
    }

    function getZipCode()
    {
        return $this->cp;
    }

    function getCity()
    {
        return $this->ville;
    }

    function getCountry()
    {
        return null;
    }

    function getFullAddress()
    {
        return $this->getAddress() . ' ' . $this->getZipCode() . ' ' . $this->getCity() . ' ' . $this->getCountry();
    }

    /**
     * @inheritdoc
     */
    function loadRefGeolocalisation()
    {
        return $this->_ref_geolocalisation = $this->loadUniqueBackRef('geolocalisation');
    }

    /**
     * @inheritdoc
     */
    function createGeolocalisationObject()
    {
        $this->loadRefGeolocalisation();

        if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
            $geo = new CGeoLocalisation();
            $geo->setObject($this);
            $geo->processed = '0';
            $geo->store();

            return $geo;
        } else {
            return $this->_ref_geolocalisation;
        }
    }

    /**
     * @inheritdoc
     */
    function getLatLng()
    {
        $this->loadRefGeolocalisation();

        if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
            return null;
        }

        return $this->_ref_geolocalisation->lat_lng;
    }

    /**
     * @inheritdoc
     */
    function setLatLng($latlng)
    {
        $this->loadRefGeolocalisation();

        if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
            return null;
        }

        $this->_ref_geolocalisation->lat_lng = $latlng;

        return $this->_ref_geolocalisation->store();
    }

    /**
     * @inheritdoc
     */
    static function isGeocodable()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    function getCommuneInsee()
    {
        $this->loadRefGeolocalisation();

        if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
            return null;
        }

        return $this->_ref_geolocalisation->commune_insee;
    }

    /**
     * @inheritdoc
     */
    function setCommuneInsee($commune_insee)
    {
        $this->loadRefGeolocalisation();

        if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
            return null;
        }

        $this->_ref_geolocalisation->commune_insee = $commune_insee;

        return $this->_ref_geolocalisation->store();
    }

    /**
     * @inheritdoc
     */
    function resetProcessed()
    {
        $this->loadRefGeolocalisation();

        if (!$this->_ref_geolocalisation || !$this->_ref_geolocalisation->_id) {
            return null;
        }

        $this->_ref_geolocalisation->processed = "0";

        return $this->_ref_geolocalisation->store();
    }

    function setProcessed(CGeoLocalisation $object = null)
    {
        if (!$object || !$object->_id) {
            $object = $this->loadRefGeolocalisation();
        }

        if (!$object || !$object->_id) {
            return null;
        }

        $object->processed = "1";

        return $object->store();
    }

    /**
     * Loads the LDAP source links
     *
     * @return CSourceLDAPLink[]|null
     */
    public function loadRefSourceLDAPLinks()
    {
        return $this->_ref_source_ldap_links = $this->loadBackRefs('source_ldap_links');
    }

    /**
     * Loads the LDAP source links
     *
     * @return CSourceLDAPLink[]|null
     */
    public function loadRefBanques()
    {
        return $this->_ref_banques = $this->loadBackRefs('banques');
    }

    /**
     * Charge les protocoles opératoires associés à l'établissement
     *
     * @param string $limit Limite éventelle
     *
     * @return CProtocoleOperatoire[]
     */
    public function loadRefsProtocolesOperatoires($limit = null)
    {
        return $this->_ref_protocoles_op = $this->loadBackRefs("protocoles_op", "libelle", $limit);
    }

    /**
     * Getter to fields_etiq variale
     *
     * @return array
     * @throws Exception
     */
    public static function getFieldsEtiq()
    {
        return self::$fields_etiq;
    }

    /**
     * @return Collection|null
     * @throws Exception
     */
    public function getResourceHyperTextLinks(): ?Collection
    {
        if (!$hyperlinks = $this->loadBackRefs('hypertext_links')) {
            return null;
        }

        return new Collection($hyperlinks);
    }

    public function getApiLink(RouterInterface $router): string
    {
        return $router->generate('etablissement_groups_show', ['group_id' => $this->group_id]);
    }

    /**
     * Load medimail account
     *
     * @return CMedimailAccount
     * @throws Exception
     */
    public function loadRefMedimailAccount(): CMedimailAccount
    {
        $this->_ref_medimail_account = $this->loadUniqueBackRef("medimail_account");
        $this->_ref_medimail_account->group_id = $this->group_id;

        return $this->_ref_medimail_account;
    }
}
