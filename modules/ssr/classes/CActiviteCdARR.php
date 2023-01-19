<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use Ox\Core\Cache;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Prescription\CElementPrescription;

/**
 * Activité CdARR
 */
class CActiviteCdARR extends CCdARRObject
{
    public $code;
    public $type;
    public $libelle;
    public $note;
    public $inclu;
    public $exclu;

    // Refs
    public $_ref_type_activite;

    // Counts
    public $_count_elements;
    public $_count_actes;
    public $_count_actes_by_executant;

    // Distant refs
    public $_ref_elements;
    public $_ref_elements_by_cat;
    public $_ref_all_executants;

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
        $props["code"]    = "str notNull length|4 seekable show|0";
        $props["type"]    = "str notNull length|2 seekable show|0";
        $props["libelle"] = "str notNull maxLength|250 seekable show|1";
        $props["note"]    = "text";
        $props["inclu"]   = "text";
        $props["exclu"]   = "text";

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
     * Chargement du type d'activité
     *
     * @return CTypeActiviteCdARR
     */
    function loadRefTypeActivite()
    {
        return $this->_ref_type_activite = CTypeActiviteCdARR::get($this->type);
    }

    /**
     * @see parent::loadView()
     */
    function loadView()
    {
        parent::loadView();
        $this->loadRefTypeActivite();
    }

    /**
     * Compte les liaisons avec de éléments de prescription
     *
     * @return int
     */
    function countElements()
    {
        $element       = new CElementPrescriptionToCdarr();
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
        $element       = new CElementPrescriptionToCdarr();
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
        $acte       = new CActeCdARR();
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
      FROM `acte_cdarr` 
      LEFT JOIN `evenement_ssr` ON  `evenement_ssr`.`evenement_ssr_id` = `acte_cdarr`.`evenement_ssr_id`
      WHERE `code` = '$this->code'
      GROUP BY `therapeute_id`";
        $acte   = new CActeCdARR();
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

        if ($activite = $cache->get("activite_cdarr_$code")) {
            return $activite;
        }

        $activite = new self();
        $activite->load($code);

        $cache->set("activite_cdarr_$code", $activite);

        return $activite;
    }
}
