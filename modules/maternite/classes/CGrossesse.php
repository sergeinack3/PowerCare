<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite;

use DateInterval;
use DateTime;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\IPatientRelated;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Gestion des grossesses d'une parturiente
 */
class CGrossesse extends CMbObject implements IPatientRelated
{
    // DB Table key
    public $grossesse_id;

    // DB References
    public $parturiente_id;
    public $group_id;
    public $pere_id;

    // DB Fields
    public $cycle;
    public $terme_prevu;
    public $active;
    public $datetime_cloture;
    public $multiple;
    public $nb_foetus;
    public $id_reseau;
    public $nb_grossesses_ant;
    public $nb_accouchements_ant;
    public $allaitement_maternel;
    public $date_dernieres_regles;
    public $date_debut_grossesse;
    public $determination_date_grossesse;
    public $nb_embryons_debut_grossesse;
    public $type_embryons_debut_grossesse;
    public $rques_embryons_debut_grossesse;

    public $lieu_accouchement;
    public $num_semaines;
    public $rang;
    public $rques;

    // Pregnancy follow-up
    public ?string $estimate_first_ultrasound_date  = null;
    public ?string $estimate_second_ultrasound_date = null;
    public ?string $estimate_third_ultrasound_date  = null;
    public ?string $estimate_sick_leave_date        = null;

    // Timings de l'accouchement
    public $datetime_debut_travail;
    public $datetime_accouchement;
    public $datetime_debut_surv_post_partum;
    public $datetime_fin_surv_post_partum;

    /** @var CDossierPerinat */
    public $_ref_dossier_perinat;

    /** @var CPatient */
    public $_ref_parturiente;

    /** @var CPatient */
    public $_ref_patient;

    /** @var CGroups */
    public $_ref_group;

    /** @var CPatient */
    public $_ref_pere;

    /** @var CNaissance[] */
    public $_ref_naissances = [];

    /** @var CSejour[] */
    public $_ref_sejours = [];
    /** @var  CSejour */
    public $_ref_last_sejour;
    public $_nb_ref_sejours;

    /** @var CConsultation[] */
    public $_ref_consultations = [];
    public $_nb_ref_consultations;

    /** @var CConsultation */
    public $_ref_consultations_anesth = [];
    public $_ref_last_consult_anesth;

    /** @var  CAllaitement[] */
    public $_ref_allaitements;
    /** @var  CAllaitement */
    public $_ref_last_allaitement;

    /** @var CSurvEchoGrossesse[] */
    public $_ref_surv_echographies;

    /** @var CConsultation */
    public $_ref_last_consult;

    /** @var CNaissance */
    public $_ref_last_naissance;

    /** @var CGrossesseAnt[] */
    public $_ref_grossesses_ant = [];

    // Form fields
    public $_praticiens;
    public $_date_fecondation;
    public $_date_debut_grossesse;
    public $_semaine_grossesse;
    public $_reste_semaine_grossesse;
    public $_operation_id;
    public $_allaitement_en_cours;
    public $_last_consult_id;
    public $_days_relative_acc;
    public $_rang_grossesse;
    public $_nb_jours_hospi;
    public $_terme_prevu_ddr;
    public $_terme_prevu_debut_grossesse;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'grossesse';
        $spec->key   = 'grossesse_id';

        $spec->events = [
            "suivi" => [
                "reference1" => ["CConsultation", "_last_consult_id"],
                "reference2" => ["CPatient", "parturiente_id"],
            ],
        ];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                         = parent::getProps();
        $props["parturiente_id"]       = "ref notNull class|CPatient back|grossesses";
        $props["group_id"]             = "ref class|CGroups back|grossesses";
        $props["pere_id"]              = "ref class|CPatient back|grossesses_pere";
        $props["terme_prevu"]          = "date notNull";
        $props["cycle"]                = "num min|20 max|35 default|28";
        $props["active"]               = "bool default|1";
        $props["datetime_cloture"]     = "dateTime";
        $props["multiple"]             = "bool default|0";
        $props["nb_foetus"]            = "num min|1 default|1";
        $props["id_reseau"]            = "str";
        $props["nb_grossesses_ant"]    = "num";
        $props["nb_accouchements_ant"] = "num";
        $props["allaitement_maternel"] = "bool default|0";

        // Pregnancy follow-up
        $props["estimate_first_ultrasound_date"]  = "date";
        $props["estimate_second_ultrasound_date"] = "date";
        $props["estimate_third_ultrasound_date"]  = "date";
        $props["estimate_sick_leave_date"]        = "date";

        if (CAppUI::gconf("maternite CGrossesse date_regles_obligatoire")) {
            $props["date_dernieres_regles"] = "date notNull";
        } else {
            $props["date_dernieres_regles"] = "date";
        }
        $props["date_debut_grossesse"]           = "date";
        $props["determination_date_grossesse"]   = "enum list|ddr|ovu|echo|inc";
        $props["nb_embryons_debut_grossesse"]    = "num";
        $props["type_embryons_debut_grossesse"]  = "enum list|mm|mb|bb";
        $props["rques_embryons_debut_grossesse"] = "text helped";
        $props["lieu_accouchement"]              = "enum list|sur_site|exte default|sur_site";
        $props["num_semaines"]                   = "enum list|inf_15|15_22|sup_22_sup_500g|sup_15";
        $props["rang"]                           = "num pos";
        $props["rques"]                          = "text helped";

        $props["datetime_debut_travail"]          = "dateTime";
        $props["datetime_accouchement"]           = "dateTime";
        $props["datetime_debut_surv_post_partum"] = "dateTime";
        $props["datetime_fin_surv_post_partum"]   = "dateTime";

        $props["_last_consult_id"]             = "ref class|CConsultation";
        $props["_date_fecondation"]            = "date";
        $props["_date_debut_grossesse"]        = "date";
        $props["_semaine_grossesse"]           = "num";
        $props["_reste_semaine_grossesse"]     = "num";
        $props["_days_relative_acc"]           = "num";
        $props["_terme_prevu_ddr"]             = "date";
        $props["_terme_prevu_debut_grossesse"] = "date";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function loadRefsFwd()
    {
        $this->loadRefParturiente();
        $this->loadRefGroup();
        $this->loadRefPere();
    }

    /**
     * Chargement du dossier de périnatalité
     *
     * @return CDossierPerinat
     */
    function loadRefDossierPerinat()
    {
        $this->_ref_dossier_perinat = $this->loadUniqueBackRef("dossier_perinat");
        if (!$this->_ref_dossier_perinat->_id && $this->_id) {
            $this->_ref_dossier_perinat               = new CDossierPerinat();
            $this->_ref_dossier_perinat->grossesse_id = $this->_id;
            $this->_ref_dossier_perinat->store();
        }

        return $this->_ref_dossier_perinat;
    }

    /**
     * Chargement de la parturiente
     *
     * @return CPatient|null
     * @throws Exception
     */
    function loadRefParturiente()
    {
        return $this->_ref_parturiente = $this->loadFwdRef("parturiente_id", true);
    }

    /**
     * Chargement de l'établissement
     *
     * @return CGroups
     */
    function loadRefGroup()
    {
        return $this->_ref_group = $this->loadFwdRef("group_id", true);
    }

    /**
     * Chargement du père
     *
     * @return CPatient
     */
    function loadRefPere()
    {
        return $this->_ref_pere = $this->loadFwdRef("pere_id", true);
    }

    /**
     * Chargement des naissances associées à la grossesse
     *
     * @return CNaissance[]
     */
    function loadRefsNaissances()
    {
        return $this->_ref_naissances = $this->loadBackRefs("naissances");
    }

    /**
     * Chargement des grossesses antérieures
     *
     * @return CGrossesseAnt[]
     */
    function loadRefsGrossessesAnt()
    {
        return $this->_ref_grossesses_ant = $this->loadBackRefs("grossesses_ant", "date ASC");
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();
        $this->loadLastNaissance();

        $this->_view = "Terme prévu le " . CMbDT::dateToLocale($this->terme_prevu);
        // Nombre de semaines (aménorrhée = 41, grossesse = 39)
        $this->_date_fecondation        = CMbDT::date("-41 weeks", $this->terme_prevu);
        $this->_date_debut_grossesse    = CMbDT::date("-39 weeks", $this->terme_prevu);
        $ag                             = $this->getAgeGestationnel(
            (!$this->active && $this->datetime_cloture) ? CMbDT::date($this->datetime_cloture) : CMbDT::date()
        );
        $this->_semaine_grossesse       = $ag["SA"];
        $this->_reste_semaine_grossesse = $ag["JA"];

        // Terme prévu en fonction du cycle
        if ($this->cycle) {
            if ($this->date_dernieres_regles) {
                $this->_terme_prevu_ddr = CMbDT::date(
                    "+" . (($this->cycle - 14) + 272) . " DAYS",
                    $this->date_dernieres_regles
                );
            }
        }
        if ($this->date_debut_grossesse) {
            $this->_terme_prevu_debut_grossesse = CMbDT::date("+272 DAYS", $this->date_debut_grossesse);
        }
    }

    /**
     * Chargement des séjours associés à la grossesse
     *
     * @param array $where Additional where clauses
     *
     * @return CSejour[]
     */
    function loadRefsSejours($where = [])
    {
        return $this->_ref_sejours = $this->loadBackRefs(
            "sejours",
            "entree_prevue DESC",
            null,
            null,
            null,
            null,
            "",
            $where
        );
    }

    /**
     * Chargement du dernier séjour pour une grossesse
     *
     * @param array $where Additional where clauses
     *
     * @return CSejour|null
     */
    function loadLastSejour($where = [])
    {
        $sejours = $this->loadRefsSejours($where);

        return $this->_ref_last_sejour = empty($sejours) ? null : reset($sejours);
    }

    /**
     * Récupération du nombre de séjours liés à la grossesse
     *
     * @return int
     */
    function countRefSejours()
    {
        return $this->_nb_ref_sejours = $this->countBackRefs("sejours");
    }

    /**
     * Récupéaration du nombre de jours d'hospitalisation
     *
     * @return int
     */
    function loadNbJoursHospi()
    {
        $this->loadRefsSejours();
        $this->_nb_jours_hospi = 0;
        $date                  = CMbDT::dateTime();

        foreach ($this->_ref_sejours as $sejour) {
            $begin = CMbDT::date($sejour->entree_reelle) . ' 00:00:00';
            if ($sejour->entree_reelle && ($begin <= $date)) {
                $end = CMbDT::date($sejour->sortie) . ' 00:00:00';
                if ($end > $date) {
                    $end = $date;
                }

                $this->_nb_jours_hospi += CMbDT::daysRelative($begin, $end);
            }
        }

        return $this->_nb_jours_hospi;
    }

    /**
     * Chargement des consultations associées à la grossesse
     *
     * @param bool $with_anesth inclure ou non les consultations d'anesthésie
     *
     * @return CConsultation[]
     */
    function loadRefsConsultations($with_anesth = false)
    {
        if (!$this->_ref_consultations) {
            $this->_ref_consultations = $this->loadBackRefs(
                "consultations", "date DESC, heure DESC", null, null,
                ['plageconsult' => 'plageconsult.plageconsult_id = consultation.plageconsult_id']
            );
        }

        if ($with_anesth) {
            /** @var CConsultation $_consultation */
            foreach ($this->_ref_consultations as $_consultation) {
                $consult_anesth = $_consultation->loadRefConsultAnesth();
                if ($consult_anesth->_id) {
                    $this->_ref_consultations_anesth[$consult_anesth->_id] = $consult_anesth;
                }
            }
        }

        return $this->_ref_consultations;
    }

    /**
     * Chargement de la dernière consultation préanesthésique pour une grossesse
     *
     * @return CConsultation
     */
    function loadLastConsultAnesth()
    {
        $consultations = $this->loadRefsConsultations();
        foreach ($consultations as $_consultation) {
            $consult_anesth = $_consultation->loadRefConsultAnesth();
            if ($consult_anesth->_id) {
                return $this->_ref_last_consult_anesth = $_consultation;
            }
        }

        return $this->_ref_last_consult_anesth = new CConsultation();
    }

    /**
     * Chargement de la dernière naissance pour une grossesse
     *
     * @return CNaissance
     */
    function loadLastNaissance()
    {
        $naissances = $this->loadRefsNaissances();

        $sejours = CStoredObject::massLoadFwdRef($naissances, "sejour_enfant_id");
        CStoredObject::massLoadFwdRef($sejours, "patient_id");

        foreach ($naissances as $_naissance) {
            $_naissance->loadRefSejourEnfant()->loadRefPatient();
        }

        return $this->_ref_last_naissance = end($naissances);
    }

    /**
     * @inheritdoc
     */
    function loadView()
    {
        parent::loadView();

        $naissances = $this->loadRefsNaissances();
        $sejours    = CMbObject::massLoadFwdRef($naissances, "sejour_enfant_id");
        CMbObject::massLoadFwdRef($sejours, "patient_id");

        foreach ($naissances as $_naissance) {
            $_naissance->loadRefSejourEnfant()->loadRefPatient();
        }

        $this->loadLastAllaitement();
        $this->_ref_grossesses_ant = $this->loadRefsGrossessesAnt() ?? [];
    }

    /**
     * @inheritdoc
     */
    function loadComplete()
    {
        parent::loadComplete();

        $this->loadLastConsult();
    }

    /**
     * Récupération de la date relative d'accouchement en jours
     *
     * @return number|null
     */
    function getDateAccouchement()
    {
        if ($this->datetime_accouchement) {
            return $this->_days_relative_acc = abs(
                CMbDT::daysRelative(CMbDT::date($this->datetime_accouchement), CMbDT::date())
            );
        }

        if (count($this->_ref_naissances)) {
            /** @var CNaissance $first_naissance */
            $first_naissance = reset($this->_ref_naissances);
            if ($first_naissance->_day_relative !== null) {
                return $this->_days_relative_acc = $first_naissance->_day_relative;
            }
        }

        return null;
    }

    /**
     * Calcul de l'age gestationnel en semaines d'aménorrhée
     *
     * @param string $date Date de référence
     *
     * @return array age gestationnel en semaines + jours
     * @throws Exception
     */
    public function getAgeGestationnel(string $date = null): array
    {
        $date      = new DateTime($date);
        $reference = new DateTime($this->_date_fecondation);

        // Get the diff between the date and the reference
        $diff_weeks_days = $date->diff($reference);

        // Floor the result because it will often contain decimals. Obviously we do NOT want to round() the value
        $weeks = floor($diff_weeks_days->days / 7);

        // Get the days by calculating the difference between the date minus the weeks and the reference.
        $diff_days = $date->sub(new DateInterval("P{$weeks}W"))->diff($reference);

        return ["SA" => $weeks, "JA" => $diff_days->days];
    }

    /**
     * Chargement de la dernière consultation pour une grossesse
     *
     * @return CConsultation|null
     */
    function loadLastConsult()
    {
        $consultations = $this->loadRefsConsultations();

        $last_consult = new CConsultation();

        if (count($consultations)) {
            $last_consult = reset($consultations);
        }

        $this->_last_consult_id = $last_consult->_id;

        return $this->_ref_last_consult = $last_consult;
    }

    /**
     * @inheritdoc
     */
    function delete()
    {
        $consults = $this->loadRefsConsultations();
        $sejours  = $this->loadRefsSejours();

        if ($msg = parent::delete()) {
            return $msg;
        }

        $msg = "";

        foreach ($consults as $_consult) {
            $_consult->grossesse_id = "";
            if ($_msg = $_consult->store()) {
                $msg .= "\n $_msg";
            }
        }


        foreach ($sejours as $_sejour) {
            $_sejour->grossesse_id = "";
            if ($_msg = $_sejour->store()) {
                $msg .= "\n $_msg";
            }
        }

        if ($msg) {
            return $msg;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        if (!$this->_id) {
            // Est-ce qu'une grossesse existe déjà pour la parturiente ?
            $grossesse                 = new self();
            $grossesse->parturiente_id = $this->parturiente_id;
            $grossesse->active         = 1;
            if ($grossesse->loadMatchingObject()) {
                return CAppUI::tr("CGrossesse-already_present");
            }

            $this->group_id = CGroups::loadCurrent()->_id;
        }

        return parent::store();
    }

    /**
     * Chargement des periodes d'allaitement d'une grossesse
     *
     * @return CAllaitement[]|null
     */
    function loadRefsAllaitement()
    {
        return $this->_ref_allaitements = $this->loadBackRefs("allaitements");
    }

    /**
     * Chargement de la dernière periode d'allaitement d'une grossesse
     *
     * @return CAllaitement
     */
    function loadLastAllaitement()
    {
        return $this->_ref_last_allaitement = $this->loadLastBackRef("allaitements", "date_debut DESC");
    }

    /**
     * Chargement des mesures d'echographie du dossier de périnatalité
     *
     * @return CStoredObject[]
     */
    function loadRefsSurvEchographies()
    {
        return $this->_ref_surv_echographies = $this->loadBackRefs('echographies', 'date ASC');
    }

    /**
     *  Vérifie que le terme de la grossesse n'est pas dépassé d'un mois
     *
     * @return bool
     */
    function isOneMonthAnterior()
    {
        $now            = CMbDT::date();
        $terme_plus_one = CMbDT::date("+1 month", $this->terme_prevu);

        return $now > $terme_plus_one;
    }

    /**
     * @see parent::fillLimitedTemplate()
     */
    function fillLimitedTemplate(&$template)
    {
        $this->loadRefParturiente()->fillLimitedTemplate($template, CAppUI::tr('CNaissance-Mother'), false);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    function loadRelPatient()
    {
        return $this->_ref_patient = $this->loadRefParturiente();
    }
}
