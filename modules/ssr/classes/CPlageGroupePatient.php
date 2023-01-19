<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ssr;

use DateTime;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CModelObject;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CElementPrescription;

/**
 * The group schedule category consists of ranges
 */
class CPlageGroupePatient extends CMbObject
{
    // DB Table key
    public $plage_groupe_patient_id;

    // DB Fields
    public $categorie_groupe_patient_id;
    public $equipement_id;
    public $elements_prescription;
    public $nom;
    public $groupe_day;
    public $heure_debut;
    public $heure_fin;
    public $commentaire;
    public $actif;

    // Form fields
    public $_duree;
    public $_date;

    // References
    /** @var CCategorieGroupePatient */
    public $_ref_categorie_groupe_patient;
    /** @var CElementPrescription[] */
    public $_ref_elements_prescription;
    /** @var CSejour[] */
    public $_ref_sejours_associes;
    /** @var CEvenementSSR[] */
    public $_ref_evenements_ssr;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'plage_groupe_patient';
        $spec->key   = 'plage_groupe_patient_id';

        return $spec;
    }

    /**.
     *
     * @inheritdoc
     */
    function getProps()
    {
        $props                                = parent::getProps();
        $props["categorie_groupe_patient_id"] = "ref notNull class|CCategorieGroupePatient back|plages_groupe_ssr";
        $props["equipement_id"]               = "ref class|CEquipement back|plages_groupe_ssr";
        $props["elements_prescription"]       = "str notNull show|0";
        $props["nom"]                         = "str";
        $props["groupe_day"]                  = "enum notNull list|monday|tuesday|wednesday|thursday|friday|saturday|sunday";
        $props["heure_debut"]                 = "time notNull";
        $props["heure_fin"]                   = "time notNull moreThan|heure_debut";
        $props["commentaire"]                 = "text";
        $props["actif"]                       = "bool default|1";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();
        $this->loadRefCategorieGroupePatient();

        $this->_view = $this->_ref_categorie_groupe_patient->nom . " - ";
        $this->_view .= CAppUI::tr('CPlageGroupePatient.groupe_day.' . $this->groupe_day) . " " . CMbDT::format(
                $this->heure_debut,
                "%Hh%M"
            );

        $this->_duree = CMbDT::minutesRelative($this->heure_debut, $this->heure_fin);
    }

    /**
     * @inheritdoc
     */
    function loadView()
    {
        parent::loadView();

        $this->loadRefElementsPresciption();
        $this->loadRefSejoursAssocies($this->_date);
    }

    /**
     * Load the equipment
     *
     * @return CEquipement
     * @throws Exception
     */
    function loadRefEquipement()
    {
        return $this->_ref_equipement = $this->loadFwdRef("equipement_id");
    }

    /**
     * Load the category
     *
     * @return CCategorieGroupePatient
     * @throws Exception
     */
    function loadRefCategorieGroupePatient()
    {
        return $this->_ref_categorie_groupe_patient = $this->loadFwdRef("categorie_groupe_patient_id");
    }

    /**
     * Load the category
     *
     * @return CElementPrescription[]
     * @throws Exception
     */
    function loadRefElementsPresciption()
    {
        $elements = explode("|", $this->elements_prescription);

        $where                            = [];
        $where['element_prescription_id'] = CSQLDataSource::prepareIn($elements);

        $element_prescription = new CElementPrescription();
        $elements             = $element_prescription->loadList($where);

        return $this->_ref_elements_prescription = $elements;
    }

    /**
     * Load the list of stays associated with the group range
     *
     * @param string $date_groupe_plage
     *
     * @return CSejour[]
     * @throws Exception
     */
    function loadRefSejoursAssocies(string $date_groupe_plage = null)
    {
        $first_day_of_week = CMbDT::date("$this->groupe_day this week", $date_groupe_plage);

        $ljoin                         = [];
        $ljoin["evenement_ssr"]        = "evenement_ssr.sejour_id = sejour.sejour_id";
        $ljoin["plage_groupe_patient"] = "evenement_ssr.plage_groupe_patient_id = plage_groupe_patient.plage_groupe_patient_id";

        $where                                          = [];
        $where["sejour.annule"]                         = " = '0'";
        $where["evenement_ssr.plage_groupe_patient_id"] = " = '$this->_id'";
        $where["evenement_ssr.seance_collective_id"]    = " IS NULL";
        $where[]                                        = "DATE(evenement_ssr.debut) = '" . $first_day_of_week . "'";
        $where[]                                        = "'$first_day_of_week' BETWEEN DATE(sejour.entree) AND DATE(sejour.sortie)";

        $sejour  = new CSejour();
        $sejours = $sejour->loadList($where, null, null, "sejour.sejour_id", $ljoin);

        CStoredObject::massLoadFwdRef($sejours, 'patient_id');

        foreach ($sejours as $_sejour) {
            $_sejour->loadRefPatient();
        }

        return $this->_ref_sejours_associes = $sejours;
    }

    /**
     * Load SSR events
     *
     * @param array        $where Optional conditions
     * @param array|string $order Order SQL statement
     *
     * @return CEvenementSSR[]
     */
    function loadRefEvenementsSSR($where = [], $order = null)
    {
        return $this->_ref_evenements_ssr = $this->loadBackRefs(
            "evenements_ssr",
            $order,
            null,
            null,
            null,
            null,
            "",
            $where
        );
    }

    /**
     * Load SSR events
     *
     * @param CSejour  $sejour Stay
     * @param datetime $date   Datetime
     *
     * @return array
     */
    function calculateDatesForPlageGroup(CSejour $sejour, $date)
    {
        $date_sortie = CMbDT::date($sejour->sortie);
        $date_entree = CMbDT::date($sejour->entree);

        $first_day_of_week = CMbDT::date("$this->groupe_day this week", $date);

        $first_day_of_sejour = $first_day_of_week > $date_entree ? $first_day_of_week : CMbDT::date(
            "$this->groupe_day this week",
            $date_entree
        );
        $days                = [];

        for ($day = $first_day_of_sejour; $day <= $date_sortie; $day = CMbDT::date("+1 week", $day)) {
            $days[$day] = $day;
        }

        return $days;
    }

    /**
     * All events realized
     *
     * @param datetime|string   $debut DateTime event
     * @param CMediusers $kine  Kine
     *
     * @return bool
     */
    function allEventsRealized($debut, $kine)
    {
        $where = [
            "debut" => "= '$debut'",
        ];

        if ($kine && $kine->_id) {
            $where["therapeute_id"] = "= '$kine->_id'";
        }

        $all_events = $this->loadRefEvenementsSSR($where);

        $where["realise"] = "='1'";
        $events_realized  = $this->loadRefEvenementsSSR($where);

        $realised = false;

        if (count($all_events) > 0 && (count($all_events) == count($events_realized))) {
            $realised = true;
        }

        return $realised;
    }

    /**
     * @param null $class
     * @param bool $only_notNull
     *
     * @return CPlageGroupePatient
     * @throws \Ox\Core\CModelObjectException
     */
    public static function getSampleObject($class = null, bool $only_notNull = false): CModelObject
    {
        /** @var CPlageGroupePatient $plage_groupe */
        $plage_groupe              = parent::getSampleObject($class, $only_notNull);
        $plage_groupe->heure_debut = '08:00:00';
        $plage_groupe->heure_fin   = '10:00:00';

        if (!$only_notNull) {
            $plage_groupe->nom = "Plage n° " . rand(0, 10000);
        }

        return $plage_groupe;
    }
}
