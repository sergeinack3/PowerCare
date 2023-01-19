<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Core\Cache;
use Ox\Core\CMbObject;
use Ox\Core\Exceptions\CouldNotMerge;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\Sante400\CIncrementer;
use Ox\Mediboard\System\CMergeLog;

/**
 * Class CDomain
 * Identification domain
 */
class CDomain extends CMbObject
{
    // DB Table key
    public $domain_id;

    // DB fields
    public $incrementer_id;
    public $actor_id;
    public $actor_class;
    public $tag;
    public $libelle;
    public $namespace_id;
    public $derived_from_idex;
    public $OID;
    public $active;

    // Form fields
    public $_is_master_ipp;
    public $_is_master_nda;
    public $_count_objects;
    public $_detail_objects = [];
    public $_force_merge    = false;

    /** @var CInteropActor */
    public $_ref_actor;
    /** @var CIncrementer */
    public $_ref_incrementer;
    /** @var CGroupDomain[] */
    public $_ref_group_domains;
    /** @var string */
    public $_identifier;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec = parent::getSpec();

        $spec->table = 'domain';
        $spec->key   = 'domain_id';

        $spec->uniques["actor"] = ["actor_id", "actor_class", "active"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props = parent::getProps();

        $props["incrementer_id"]    = "ref class|CIncrementer nullify back|domains";
        $props["actor_id"]          = "ref class|CInteropActor meta|actor_class nullify back|domain";
        $props["actor_class"]       = "str maxLength|80";
        $props["tag"]               = "str notNull";
        $props["libelle"]           = "str";
        $props["namespace_id"]      = "str";
        $props["derived_from_idex"] = "bool";
        $props["OID"]               = "str";
        $props["active"]            = "bool default|1";

        $props["_is_master_ipp"] = "bool";
        $props["_is_master_nda"] = "bool";
        $props["_count_objects"] = "num";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        // On passe tous les domaines du groupe en "non master"
        if ($this->fieldModified("active", 0)) {
            foreach ($this->loadRefsGroupDomains() as $_group_domain) {
                $_group_domain->master = "0";
                $_group_domain->store();
            }
        }

        return parent::store();
    }

    /**
     * Load actor
     *
     * @return CInteropActor
     * @throws \Exception
     */
    function loadRefActor()
    {
        if ($actor = $this->loadFwdRef("actor_id", true)) {
            return $this->_ref_actor = $actor;
        }

        return $this->_ref_actor = new CInteropActor();
    }

    /**
     * Load incrementer
     *
     * @return CIncrementer
     * @throws \Exception
     */
    function loadRefIncrementer()
    {
        if ($this->_ref_incrementer) {
            return $this->_ref_incrementer;
        }

        return $this->_ref_incrementer = $this->loadFwdRef("incrementer_id", true);
    }

    /**
     * Load groups domains
     *
     * @return CGroupDomain[]
     * @throws \Exception
     */
    function loadRefsGroupDomains()
    {
        if ($this->_ref_group_domains) {
            return $this->_ref_group_domains;
        }

        return $this->_ref_group_domains = $this->loadBackRefs("group_domains");
    }

    /**
     * Count objects
     *
     * @return int
     * @throws \Exception
     */
    function countObjects()
    {
        $idex                 = new CIdSante400();
        $idex->tag            = $this->tag;
        $this->_count_objects = $idex->countMatchingList();

        $where = [
            "tag" => " = '$this->tag'",
        ];

        return $this->_detail_objects = $idex->countMultipleList(
            $where,
            null,
            "object_class",
            null,
            ["object_class"],
            "tag"
        );
    }

    /**
     * @inheritDoc
     */
    public function merge(array $objects, bool $fast, CMergeLog $merge_log): void
    {
        if (!$this->_force_merge) {
            throw CouldNotMerge::domainMergeImpossible();
        }

        parent::merge($objects, $fast, $merge_log);
    }

    /**
     * Update the form (derived) fields plain fields
     *
     * @return void
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view = $this->libelle ? $this->libelle : $this->tag;
    }

    /**
     * If domain is master
     *
     * @return bool
     */
    function isMaster()
    {
        foreach ($this->loadRefsGroupDomains() as $_group_domain) {
            if ($_group_domain->isMasterIPP()) {
                return $this->_is_master_ipp = true;
            }

            if ($_group_domain->isMasterNDA()) {
                return $this->_is_master_nda = true;
            }
        }

        return false;
    }

    /**
     * Get all master domains
     *
     * @param string $domain_type Object class
     * @param bool   $only_master Only master domain
     *
     * @return CDomain[]
     */
    static function getAllDomains($domain_type, $only_master = true)
    {
        $cache = new Cache('CDomain.getAllDomains', [$domain_type . $only_master], Cache::INNER);
        if ($cache->exists()) {
            return $cache->get();
        }

        $group_domain               = new CGroupDomain();
        $group_domain->object_class = $domain_type;
        if ($only_master) {
            $group_domain->master = true;
        }
        /** @var CGroupDomain[] $group_domains */
        $group_domains = $group_domain->loadMatchingList();

        $domains = [];
        foreach ($group_domains as $_group_domain) {
            $domain                = $_group_domain->loadRefDomain();
            $domains[$domain->_id] = $domain;
        }

        return $cache->put($domains);
    }

    /**
     * Get master domain tag
     *
     * @param string $domain_type Object class
     * @param int    $group_id    Group
     *
     * @return CDomain
     */
    public static function getMasterDomain(string $domain_type, int $group_id = null): CDomain
    {
        $group = CGroups::loadCurrent();
        if (!$group_id) {
            $group_id = $group->_id;
        }

        $cache = new Cache('CDomain.getMasterDomain', $domain_type . $group_id, Cache::INNER);
        if ($cache->exists()) {
            return $cache->get();
        }

        $group_domain               = new CGroupDomain();
        $group_domain->object_class = $domain_type;
        $group_domain->group_id     = $group_id;
        $group_domain->master       = true;
        $group_domain->loadMatchingObject();

        $domain = new CDomain();
        $domain->load($group_domain->domain_id);

        return $cache->put($domain);
    }

    /**
     * Get master domain patient
     *
     * @param int|null $group_id
     *
     * @return CDomain
     */
    public static function getMasterDomainPatient(int $group_id = null): CDomain
    {
        return self::getMasterDomain(CGroupDomain::DOMAIN_TYPE_PATIENT, $group_id);
    }

    /**
     * Get master domain patient
     *
     * @param int|null $group_id
     *
     * @return CDomain
     */
    public static function getMasterDomainSejour(int $group_id = null): CDomain
    {
        return self::getMasterDomain(CGroupDomain::DOMAIN_TYPE_SEJOUR, $group_id);
    }

    /**
     * Load domain identifiers
     *
     * @param CMbObject $object Object
     *
     * @return array
     */
    static function loadDomainIdentifiers(CMbObject $object)
    {
        $identifiers = [];

        foreach (CDomain::getAllDomains($object->_class, false) as $_domain) {
            if (!$_domain->getObjectIdentifier($object)->_id) {
                continue;
            }

            $identifiers[$_domain->_guid] = $_domain;
        }

        return $identifiers;
    }

    /**
     * Get object identifier
     *
     * @param CMbObject $object Object
     *
     * @return string
     */
    function getObjectIdentifier(CMbObject $object)
    {
        return $this->_identifier = $object->loadLastId400($this->tag);
    }
} 
