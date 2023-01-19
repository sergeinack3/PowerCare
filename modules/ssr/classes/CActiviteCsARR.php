<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\Cache;
use Ox\Core\CMbArray;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Prescription\CElementPrescription;

/**
 * Activite CsARR
 */
class CActiviteCsARR extends CCsARRObject
{
    const GET_USED_CODES_FOR_CACHE = 'CActiviteCsARR.getUsedCodesFor';

    public $code;
    public $hierarchie;
    public $libelle;
    public $libelle_court;
    public $ordre;

    // Refs
    public $_ref_reference;
    public $_ref_hierarchie;
    public $_ref_hierarchies;
    public $_ref_modulateurs;
    public $_ref_notes_activites;
    public $_ref_gestes_complementaires;
    public $_ref_activites_complementaires;

    // Counts
    public $_count_elements;
    public $_count_actes;
    public $_count_actes_by_executant;

    // Distant refs
    public $_ref_elements;
    public $_ref_elements_by_cat;
    public $_ref_all_executants;

    /** @var int The id of the favori */
    public $_favori_id;
    /** @var int The number of times the code is used by the user */
    public $_occurrences;

    static $cached = [];

    /** @var CActiviteCsARR An instance used for searching codes */
    protected static $search;

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'activite';
        $spec->key   = 'code';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props = parent::getProps();

        // DB Fields
        $props["code"]          = "str notNull length|7 seekable show|0";
        $props["hierarchie"]    = "str notNull maxLength|12 seekable show|0";
        $props["libelle"]       = "str notNull seekable";
        $props["libelle_court"] = "str notNull seekable show|0";
        $props["ordre"]         = "num max|100";

        return $props;
    }

    /**
     * @see parent::updateFormFields()
     */
    function updateFormFields()
    {
        parent::updateFormFields();
        $this->_view      = $this->code;
        $this->_shortview = $this->code;
    }

    /**
     * Charge la hiérarchie parente
     *
     * @return CActiviteCdARR
     */
    function loadRefHierarchie()
    {
        return $this->_ref_hierarchie = CHierarchieCsARR::get($this->hierarchie);
    }

    /**
     * Charge toutes les hiérarchies ancêtres
     *
     * @return CHierarchieCsARR[]
     */
    function loadRefsHierarchies()
    {
        // Codes des hiérarchies intermédiaires
        $parts = explode(".", $this->hierarchie);
        $codes = [];
        foreach ($parts as $_part) {
            $codes[] = count($codes) ? end($codes) . ".$_part" : $_part;
        }

        // Chargement des hiérarchies intermédiaires
        $hierarchie  = new CHierarchieCsARR();
        $hierarchies = $hierarchie->loadAll($codes);

        return $this->_ref_hierarchies = $hierarchies;
    }

    /**
     * Charge les notes associés, par type puis par ordre
     *
     * @return CNoteActiviteCsARR[][]
     */
    function loadRefsNotesActivites()
    {
        $note       = new CNoteActiviteCsARR();
        $note->code = $this->code;
        $notes      = [];
        /** @var CNoteActiviteCsARR $_note */
        foreach ($note->loadMatchingList("ordre") as $_note) {
            $notes[$_note->typenote][$_note->ordre] = $_note;
        }

        return $this->_ref_notes_activites = $notes;
    }

    /**
     * Charge les modulateurs associés
     *
     * @return CModulateurCsARR[]
     */
    function loadRefsModulateurs()
    {
        $modulateur = new CModulateurCsARR();
        if (!$this->code) {
            return $this->_ref_modulateurs = [];
        }
        $modulateur->code = $this->code;
        $modulateurs      = $modulateur->loadMatchingList();

        return $this->_ref_modulateurs = $modulateurs;
    }

    /**
     * Charge la reference asociée
     *
     * @return CReferenceActiviteCsARR
     */
    function loadRefReference()
    {
        $reference = new CReferenceActiviteCsARR();
        if ($this->code) {
            $reference->code = $this->code;
            $reference->loadMatchingObject();
        }

        return $this->_ref_reference = $reference;
    }


    /**
     * Chage les gestes complémentaires associés
     *
     * @return CActiviteCsARR[]
     */
    function loadRefsGestesComplementaires()
    {
        // Chargement des gestes
        $geste                             = new CGesteComplementaireCsARR();
        $geste->code_source                = $this->code;
        $gestes                            = $geste->loadMatchingList();
        $this->_ref_gestes_complementaires = $gestes;

        // Chargement directes des activités correspondantes.
        $codes                                = CMbArray::pluck($gestes, "code_cible");
        $activite                             = new CActiviteCsARR;
        $this->_ref_activites_complementaires = $activite->loadAll($codes);

        // Retour de gestes
        return $this->_ref_gestes_complementaires;
    }

    /**
     * @see parent::loadView()
     */
    function loadView()
    {
        parent::loadView();
        $this->loadRefHierarchie();
    }

    /**
     * Compte les liaisons avec de éléments de prescription
     *
     * @return int
     */
    function countElements()
    {
        $element       = new CElementPrescriptionToCsarr();
        $element->code = $this->code;

        return $this->_count_elements = $element->countMatchingList();
    }

    /**
     * Charge les liaisons avec des éléments de prescription
     *
     * @return CElementPrescriptionToCdarr[]
     */
    function loadRefsElements()
    {
        $element       = new CElementPrescriptionToCsarr();
        $element->code = $this->code;

        return $this->_ref_elements = $element->loadMatchingList();
    }

    /**
     * Charge les éléments de prescriptions associés par catégorie
     *
     * @return CElementPrescription[][]
     */
    function loadRefsElementsByCat()
    {
        $this->_ref_elements_by_cat = [];
        foreach ($this->loadRefsElements() as $_element) {
            if ($element = $_element->loadRefElementPrescription()) {
                $this->_ref_elements_by_cat[$element->category_prescription_id][] = $_element;
            }
        }

        return $this->_ref_elements_by_cat;
    }

    /**
     * Compte les actes CdARR pour ce code d'activité
     *
     * @return int
     */
    function countActes()
    {
        $acte       = new CActeCsARR();
        $acte->code = $this->code;

        return $this->_count_actes = $acte->countMatchingList();
    }

    /**
     * Charge les exécutants de cet activité et fournit le nombre d'occurences par exécutants
     *
     * @return CMediusers[]
     *
     * @see self::_count_actes_by_executant
     */
    function loadRefsAllExecutants()
    {
        // Comptage par executant
        $query  = "SELECT therapeute_id, COUNT(*)
      FROM `acte_csarr` 
      LEFT JOIN `evenement_ssr` ON  `evenement_ssr`.`evenement_ssr_id` = `acte_csarr`.`evenement_ssr_id`
      WHERE `code` = '$this->code'
      GROUP BY `therapeute_id`";
        $acte   = new CActeCsARR();
        $ds     = $acte->getDS();
        $counts = $ds->loadHashList($query);
        arsort($counts);

        // Chargement des executants
        $user = new CMediusers;
        /** @var CMediusers[] $executants */
        $executants = $user->loadAll(array_keys($counts));
        foreach ($executants as $_executant) {
            $_executant->loadRefFunction();
        }

        // Valeurs de retour
        $this->_count_actes_by_executant = $counts;

        return $this->_ref_all_executants = $executants;
    }


    /**
     * Check if the code is a favori for the given user
     *
     * @param CMediusers $user The user
     *
     * @return bool
     */
    public function isFavori($user)
    {
        $favori           = CFavoriCsARR::getFromCode($this->code, $user);
        $this->_favori_id = $favori->_id;

        return $this->_favori_id != null;
    }


    /**
     * Charge une activité par le code
     *
     * @param string $code Code d'activité
     *
     * @return self
     */
    static function get($code)
    {
        if (!$code) {
            return new self();
        }

        $cache = Cache::getCache(Cache::OUTER);

        if ($activite = $cache->get("activite_csarr_$code")) {
            $activite->loadRefReference();
            $activite->loadRefsModulateurs();

            return $activite;
        }

        $activite = new self();
        $activite->load($code);

        $cache->set("activite_csarr_$code", $activite);

        $activite->loadRefReference();
        $activite->loadRefsModulateurs();

        return $activite;
    }

    /**
     * Perform a search in the CsARR datasource
     *
     * @param string $keywords  The keywords to find
     * @param string $code      Search only in the code field
     * @param string $hierarchy A hierarchy code (can be partial)
     * @param array  $where     An optional array of where clauses
     * @param int    $start     The starting offset
     * @param int    $rows      The number of results to return
     *
     * @return CActiviteCsARR[]
     */
    public static function findCodes($keywords, $code = null, $hierarchy = null, $where = [], $start = 0, $rows = 20)
    {
        self::$search = new self;
        $ds           = self::$search->getDS();

        $where_keys = [];
        if ($keywords && $keywords != '') {
            $keywords = explode(' ', $keywords);
            foreach ($keywords as $keyword) {
                $where_keys[] = "activite.libelle LIKE '%" . addslashes($keyword) . "%'";
            }
        }

        $where_codes = [];
        if ($code && $code != '') {
            $codes = explode(' ', $code);
            foreach ($codes as $code) {
                $where_codes[] = "activite.code LIKE '" . addslashes($code) . "%'";
            }
        }

        $where_clauses = [];
        if ($where) {
            $where_clauses[] = '(' . implode(' AND ', $where) . ')';
        }

        if (count($where_codes) && count($where_keys)) {
            $where_clauses[] = ' ((' . implode(' AND ', $where_keys) . ') OR (' . implode(' OR ', $where_codes) . '))';
        } elseif (count($where_keys)) {
            $where_clauses[] = ' (' . implode(' AND ', $where_keys) . ')';
        } elseif (count($where_codes)) {
            $where_clauses[] = ' (' . implode(' AND ', $where_codes) . ')';
        }

        if ($hierarchy) {
            $where_clauses[] = "activite.hierarchie LIKE '$hierarchy%'";
        }

        self::$search->_totalSeek = self::$search->countList($where_clauses, 'code');

        return self::$search->loadList($where_clauses, 'code', "$start, $rows", 'code');
    }

    /**
     * Returns the total of results from the last search (perform a new one if necessary)
     *
     * @param string $keywords  The keywords to find
     * @param string $code      Search only in the code field
     * @param string $hierarchy A hierarchy code (can be partial)
     * @param array  $where     An optional array of where clauses
     *
     * @return int
     */
    public static function countResults($keywords, $code = null, $hierarchy = null, $where = [])
    {
        if (!self::$search) {
            self::findCodes($keywords, $code = null, $hierarchy = null, $where = []);
        }

        return self::$search->_totalSeek;
    }

    /**
     * Return the ten most used CsARR codes by the given user
     *
     * @param CMediusers $user    The user
     * @param string     $keyword A keyword to search
     *
     * @return CActiviteCsARR[]
     */
    public static function getUsedCodesFor($user, $keyword = null)
    {
        $cache = new Cache(self::GET_USED_CODES_FOR_CACHE, $user->_guid, Cache::INNER_OUTER);
        if (!$keyword && $cache->exists()) {
            $codes = $cache->get();
        } else {
            $ds = CSQLDataSource::get('std');

            $query = new CRequest();
            $query->addColumn('acte_csarr.code');
            $query->addColumn('COUNT(acte_csarr.code)', 'occurrences');
            $query->addTable('acte_csarr');
            $query->addLJoinClause('evenement_ssr', 'evenement_ssr.evenement_ssr_id = acte_csarr.evenement_ssr_id');
            $query->addWhereClause('evenement_ssr.therapeute_id', " = '{$user->_id}'");
            $query->addWhereClause(
                'acte_csarr.code',
                CSQLDataSource::prepareNotIn(CMbArray::pluck(CFavoriCsARR::getFor($user), 'code'))
            );
            $query->addGroup('acte_csarr.code');
            $query->addOrder('occurrences DESC');
            $query->setLimit(10);

            $results = $ds->loadList($query->makeSelect());

            $codes = [];
            if ($results) {
                foreach ($results as $result) {
                    $code               = CActiviteCsARR::get($result['code']);
                    $code->_occurrences = $result['occurrences'];
                    $codes[]            = $code;
                }

                if (!$keyword) {
                    $cache->put($codes);
                }
            }
        }

        return $codes;
    }

    /**
     * Reset the cache of the most used codes for the given user
     *
     * @param int $user_id The user's id
     *
     * @return void
     */
    public static function resetUsedCodesCache($user_id)
    {
        $cache = new Cache(self::GET_USED_CODES_FOR_CACHE, ["CMediusers-$user_id"], Cache::INNER_OUTER);
        if ($cache->exists()) {
            $cache->rem();
        }
    }
}
