<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CModelObject;
use Ox\Core\CModelObjectException;
use Ox\Core\CPlageHoraire;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\PersisterVisitorInterface;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CExercicePlace;

/**
 * Plages de consultation médicales et para-médicales
 */
class CPlageconsult extends CPlageHoraire implements ImportableInterface
{
    /** @var string */
    public const RESOURCE_TYPE = 'plageConsult';

    /** @var string */
    public const RELATION_CONSULTATIONS = 'medicalAppointments';

    /** @var string */
    public const FIELDSET_TARGET = 'target';

    public const BACKREF_CONSULTS_PATIENTS = 'consults_patient';

    public const LIBELLE_PLAGE_SUIVI_PATIENT = 'automatique_suivi_patient';

    static $minutes          = [];
    static $hours            = [];
    static $hours_start      = null;
    static $hours_stop       = null;
    static $minutes_interval = null;

    // DB Table key
    public $plageconsult_id;

    // DB References
    public $chir_id;
    public $remplacant_id;
    public $pour_compte_id;
    public $agenda_praticien_id;

    // DB fields
    public $freq;
    public $libelle;
    public $locked;
    public $remplacant_ok;
    public $desistee;
    public $color;
    public $pct_retrocession;
    public $pour_tiers;
    public $send_notifications;
    public $sync_appfine;
    public $eligible_teleconsultation;
    public $exercice_place_id;
    /** @var int */
    public $function_id;

    /** @var int */
    public $nb_places;

    // Form fields
    public $_freq;
    public $_affected;
    public $_total;
    public $_fill_rate;
    public $_nb_patients;
    public $_consult_by_categorie = [];
    public $_type_repeat;
    public $_nb_free_freq;
    public $_consultation_categorie_ids;

    public $_update_pauses;
    public $_pause_ids;
    public $_pauses;

    // Filter fields
    public $_date_min;
    public $_date_max;
    public $_function_id;
    public $_other_function_id;
    public $_user_id;

    // behaviour fields
    public $_handler_external_booking;
    public $_immediate_plage;
    public $_color_planning;

    // References
    /** @var CAgendaPraticien */
    public $_ref_agenda_praticien;

    /** @var CExercicePlace */
    public $_ref_exercice_place;

    /** @var CMediusers */
    public $_ref_chir;

    /** @var CConsultation[] */
    public $_ref_consultations = [];

    /** @var CMediusers */
    public $_ref_remplacant;

    /** @var CMediusers */
    public $_ref_pour_compte;

    /** @var CConsultation[] */
    public $_ref_pauses = [];

    /** @var CSlot[] */
    public $_ref_slots;

    /** @var CFunctions */
    public $_ref_function;

    /* @var CEvenementPatient[]|CConsultation[] */
    public $_items;

    public $_disponibilities = [];

    public $_freq_minutes;          // freq in minutes (int)
    public $_cumulative_minutes      = 0;    // nb minutes usef for consultation in this plage
    public $_count_duplicated_plages = 0;
    public $_hours_limit_valid;
    public $_hour_min_valid;
    public $_hour_max_valid;
    public $_slot_id;

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        self::initHoursMinutes();
    }

    /**
     * @see parent::getSpec()
     */
    function getSpec()
    {
        $spec                 = parent::getSpec();
        $spec->table          = "plageconsult";
        $spec->key            = "plageconsult_id";
        $spec->collision_keys = ["chir_id", "agenda_praticien_id"];

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps()
    {
        $props = parent::getProps();

        $props["chir_id"]                   = "ref notNull class|CMediusers seekable back|plages_consult fieldset|target";
        $props["remplacant_id"]             = "ref class|CMediusers seekable back|plages_remplacees";
        $props["pour_compte_id"]            = "ref class|CMediusers seekable back|plages_pour_compte_de";
        $props["date"]                      = "date notNull fieldset|default";
        $props["freq"]                      = "time notNull min|00:05:00 fieldset|default";
        $props["debut"]                     = "time notNull fieldset|default";
        $props["fin"]                       = "time notNull moreThan|debut fieldset|default";
        $props["libelle"]                   = "str seekable fieldset|default";
        $props["locked"]                    = "bool default|0 fieldset|extra";
        $props["remplacant_ok"]             = "bool default|0 show|0";
        $props["desistee"]                  = "bool default|0 show|0";
        $props["color"]                     = "color default|dddddd fieldset|extra";
        $props["pct_retrocession"]          = "pct default|70 show|0";
        $props["pour_tiers"]                = "bool default|0 show|0";
        $props['send_notifications']        = 'bool default|1';
        $props['sync_appfine']              = 'bool default|0';
        $props['agenda_praticien_id']       = "ref class|CAgendaPraticien back|plagesconsult";
        $props["eligible_teleconsultation"] = "bool default|0";
        $props["exercice_place_id"]         = "ref class|CExercicePlace seekable back|exercice_place_plage";
        $props["function_id"]               = "ref class|CFunctions back|function_plages";
        $props['nb_places']                 = 'num default|1';

        // Form fields
        $props["_freq"]          = "";
        $props["_affected"]      = "";
        $props["_total"]         = "";
        $props["_fill_rate"]     = "";
        $props["_type_repeat"]   = "enum list|simple|double|triple|quadruple|quintuple|sextuple|septuple|octuple|sameweek";
        $props["_update_pauses"] = "bool default|0";
        $props['_pauses']        = 'str';

        // Filter fields
        $props["_date_min"]          = "date";
        $props["_date_max"]          = "date moreThan|_date_min";
        $props["_function_id"]       = "ref class|CFunctions";
        $props["_other_function_id"] = "ref class|CFunctions";
        $props["_user_id"]           = "ref class|CMediusers";

        return $props;
    }

    /**
     * Count consultations
     *
     * @param bool $withCanceled Include cancelled consults
     * @param bool $withClosed   Include closed consults
     *
     * @return CConsultation[]
     */
    function countConsultations($withCanceled = true, $withClosed = true)
    {
        $where                    = [];
        $where["plageconsult_id"] = "= '$this->_id'";
        if (!$withCanceled) {
            $where["annule"] = "= '0'";
        }
        if (!$withClosed) {
            $where["chrono"] = "!=  '" . CConsultation::TERMINE . "'";
        }

        $consult = new CConsultation();

        return $consult->countList($where);
    }


    /**
     * Load consultations
     *
     * @param bool $withCanceled Include cancelled consults
     * @param bool $withClosed   Include closed consults
     * @param bool $withPayees   Include payed consults
     *
     * @return CConsultation[]
     */
    function loadRefsConsultations($withCanceled = true, $withClosed = true, $withPayees = true)
    {
        $where["plageconsult_id"]   = "= '$this->_id'";
        $where["type_consultation"] = "= 'consultation'";
        if (!$withCanceled) {
            $where["annule"] = "= '0'";
        }

        if (!$withClosed) {
            $where["chrono"] = "!=  '" . CConsultation::TERMINE . "'";
        }

        $this->_ref_consultations = $this->loadBackRefs('consultations', "heure", null, null, null, null, '', $where);

        foreach ($this->_ref_consultations as $_consult) {
            $this->_cumulative_minutes += ($_consult->duree * $this->_freq_minutes);
        }


        if (!$withPayees) {
            foreach ($this->_ref_consultations as $key => $consult) {
                /** @var CConsultation $consult */
                $facture = $consult->loadRefFacture();
                if ($facture->_id && $facture->patient_date_reglement) {
                    unset($this->_ref_consultations[$key]);
                }
            }
        }

        return $this->_ref_consultations;
    }

    /**
     * get the next plage for the chir_id
     *
     * @return CPlageconsult
     */
    function getNextPlage()
    {
        $plage = new CPlageconsult();
        if (!$this->_id) {
            return $plage;
        }

        $where                    = [];
        $where[]                  = " chir_id = '$this->chir_id' OR remplacant_id = '$this->remplacant_id'";
        $where["locked"]          = " != '1' ";
        $where["date"]            = "> '$this->date' ";
        $where["plageconsult_id"] = " != '$this->plageconsult_id' ";
        $plage->loadObject($where, "date ASC, debut ASC");

        return $plage;
    }

    function getPreviousPlage()
    {
        $plage = new CPlageconsult();
        if (!$this->_id) {
            return $plage;
        }

        $where                    = [];
        $where[]                  = " chir_id = '$this->chir_id' OR remplacant_id = '$this->remplacant_id'";
        $where["locked"]          = " != '1' ";
        $where["date"]            = "< '$this->date' ";
        $where["plageconsult_id"] = " != '$this->plageconsult_id' ";
        $plage->loadObject($where, "date DESC, debut DESC");

        return $plage;
    }

    /**
     * get the plage list between 2 days or for one day
     *
     * @param string      $chir_id    chir of plage
     * @param string      $date_start date of start
     * @param string|null $date_end   date of end (if null, check only for start)
     * @param array       $where      add conditions
     *
     * @return CPlageconsult[]
     */
    function loadForDays($chir_id, $date_start, $date_end = null, $where = [])
    {
        $chir          = CMediusers::get($chir_id);
        $whereChir     = $chir->getUserSQLClause();
        $plage         = new self();
        $where["date"] = $date_end ? ("BETWEEN '$date_start' AND '$date_end' ") : " = '$date_start'";
        $where[]       = "chir_id $whereChir OR remplacant_id $whereChir";

        return $plage->loadList($where, "debut ASC, fin ASC, chir_id");
    }

    /**
     * Calcul du nombre de patient dans la plage
     *
     * @param bool $include_pause count pauses too
     *
     * @return int The patient count
     * @throws Exception
     */
    public function countPatients(bool $include_pause = false): ?int
    {
        $where = [
            'annule' => "= '0'",
            "type_consultation" => "= 'consultation'"
        ];

        if (!$include_pause) {
            $where["patient_id"] = " IS NOT NULL";
        }

        return $this->_nb_patients = $this->countBackRefs(
            'consultations',
            $where,
            null,
            true,
            self::BACKREF_CONSULTS_PATIENTS
        );
    }

    /**
     * Refs consultations and fill rate loader
     *
     * @param bool $withCanceled Prise en compte des consultations annulées
     * @param bool $withClosed   Prise en compte des consultations terminées
     * @param bool $withPayees   Prise en compte des consultations payées
     *
     * @return void
     */
    function loadRefsBack($withCanceled = true, $withClosed = true, $withPayees = true)
    {
        $this->loadRefsConsultations($withCanceled, $withClosed, $withPayees);
        $this->loadFillRate();
    }

    /**
     *
     */
    function loadDisponibilities()
    {
        $fill             = [];
        $time             = $this->debut;
        $nb_plage_prise   = 0;
        $nb_place_consult = round((CMbDT::minutesRelative($this->debut, $this->fin) / $this->_freq));

        for ($a = 0; $a < $nb_place_consult; $a++) {
            if (!isset($fill[$time])) {
                $fill[$time] = 0;
            }

            //there is something ...
            foreach ($this->_ref_consultations as $_consult) {
                if ($_consult->heure >= $time && $_consult->heure < CMbDT::addTime($this->freq, $time)) {
                    $status = 0;

                    // pause
                    if (!$_consult->patient_id) {
                        $status = -1;
                    } else {
                        if (!$_consult->annule) {
                            $status = 1;
                        }
                    }
                    // repetition
                    $temp_time = $time;
                    for ($b = 0; $b < $_consult->duree; $b++) {
                        if (!isset($fill[$temp_time])) {
                            $fill[$temp_time] = 0;
                        }

                        // pause
                        if ($status < 0) {
                            $fill[$temp_time] = $status;
                        }

                        // rdv pris
                        if ($status > 0) {
                            $fill[$temp_time] = $fill[$temp_time] + $status;
                            $nb_plage_prise++;
                        }

                        $temp_time = CMbDT::addTime($this->freq, $temp_time);
                    }
                }
            }
            $time = CMbDT::addTime($this->freq, $time);
        }

        // get the data
        $dispo    = 0;
        $occupied = 0;
        foreach ($fill as $_fill) {
            if ($_fill == 0) {
                $dispo++;
            }
            if ($_fill != 0) {
                $occupied++;
            }
        }

        $this->_affected     = $occupied;
        $this->_nb_free_freq = $dispo;
        $this->_fill_rate    = $nb_place_consult != 0 ? round(($occupied / $nb_place_consult) * 100) : 0;

        return $this->_disponibilities = $fill;
    }

    /**
     * Plageconsult fill rate loader
     *
     * @return void
     * @throws Exception
     */
    public function loadFillRate(): void
    {
        if (!$this->_id) {
            return;
        }

        self::massLoadFillRate([$this]);
    }

    protected static function computeFillRate(array $results, array $plages): void
    {
        foreach ($plages as $plage) {
            $consults = [];

            if (isset($results[$plage->_id])) {
                foreach ($results[$plage->_id] as $heure => $duree) {
                    $consults[] = [
                        'debut' => $heure,
                        'fin'   => CMbDT::time("+ " . $duree * $plage->_freq . " MINUTES", $heure),
                    ];
                }
            }

            $duration  = CMbDT::minutesRelative("$plage->debut", "$plage->fin");
            $nb_plages = $duration / $plage->_freq;

            $nb_rdv_used = 0;
            for ($a = 0; $a < $nb_plages; $a++) {
                $min    = $a * $plage->_freq;
                $_heure = CMbDT::time("+ $min " . CAppUI::tr('common-minute|pl'), $plage->debut);

                foreach ($consults as $_rdv) {
                    if ($_heure >= $_rdv["debut"] && $_heure < $_rdv["fin"]) {
                        $nb_rdv_used++;
                        break;
                    }
                }
            }

            $plage->_fill_rate    = round(($nb_rdv_used / $nb_plages) * 100);
            $plage->_nb_free_freq = $nb_plages - $nb_rdv_used;
            $plage->_affected     = $nb_rdv_used;
        }
    }

    /**
     *
     * @throws Exception
     */
    public static function massLoadFillRate(array $plages = []): void
    {
        $ds = CSQLDataSource::get('std');

        $request = new CRequest();
        $request->addSelect('plageconsult_id, heure, duree');
        $request->addTable('consultation');
        $request->addWhere(
            [
                'annule'          => "!= '1'",
                'patient_id'      => 'IS NOT NULL',
                'plageconsult_id' => CSQLDataSource::prepareIn(CMbArray::pluck($plages, '_id')),
            ]
        );
        $request->addOrder('heure');

        $results = $ds->loadTree($request->makeSelect());

        self::computeFillRate($results, $plages);
    }

    /**
     * Calcul du tableau d'occupation de la plage de consultation
     *
     * @return array
     */
    function getUtilisation()
    {
        $utilisation = [];
        $old         = $this->debut;
        for ($i = $this->debut; $i < $this->fin; $i = CMbDT::addTime("+" . $this->freq, $i)) {
            if ($old > $i) {
                break;
            }
            $utilisation[$i] = 0;
            $old             = $i;
        }

        foreach ($this->_ref_consultations as $_consult) {
            if (!isset($utilisation[$_consult->heure])) {
                continue;
            }
            $emplacement = $_consult->heure;
            for ($i = 0; $i < $_consult->duree; $i++) {
                if (isset($utilisation[$emplacement])) {
                    $utilisation[$emplacement]++;
                }
                $emplacement = CMbDT::addTime("+" . $this->freq, $emplacement);
            }
        }

        return $utilisation;
    }

    /**
     * Calcul de la répartition des consultations par catégorie
     *
     * @return void
     */
    function loadCategorieFill()
    {
        if (!$this->_id) {
            return;
        }
        $query                       = "SELECT `consultation`.`categorie_id`, COUNT(`consultation`.`categorie_id`) as nb,
                     `consultation_cat`.`nom_icone`, `consultation_cat`.`nom_categorie`
              FROM `consultation`
              LEFT JOIN `consultation_cat`
                ON `consultation`.`categorie_id` = `consultation_cat`.`categorie_id`
              WHERE `consultation`.`plageconsult_id` = '$this->_id'
                AND `consultation`.`annule` = '0'
                AND `consultation`.`categorie_id` IS NOT NULL
              GROUP BY `consultation`.`categorie_id`
              ORDER BY `consultation`.`categorie_id`";
        $this->_consult_by_categorie = $this->_spec->ds->loadList($query);
    }

    /**
     * Chargement global des références
     *
     * @param bool $withCanceled Prise en compte des consultations annulées
     * @param int  $cache        Utilisation du cache
     *
     * @return void
     * @deprecated out of control resouce consumption
     *
     */
    function loadRefs($withCanceled = true, $cache = 0)
    {
        $this->loadRefsFwd($cache);
        $this->loadRefsBack($withCanceled);
    }

    /**
     * @see parent::loadRefsFwd()
     * @deprecated
     */
    function loadRefsFwd($cache = true)
    {
        $this->_ref_chir        = $this->loadFwdRef("chir_id", $cache);
        $this->_ref_remplacant  = $this->loadFwdRef("remplacant_id", $cache);
        $this->_ref_pour_compte = $this->loadFwdRef("pour_compte_id", $cache);
    }

    /**
     * Chargement de l'agenda du praticien
     *
     * @return CAgendaPraticien
     */
    function loadRefAgendaPraticien()
    {
        return $this->_ref_agenda_praticien = $this->loadFwdRef("agenda_praticien_id", true);
    }

    /**
     * Load Exercice Place
     *
     * @return \Ox\Core\CStoredObject|null
     * @throws Exception
     */
    function loadRefExercicePlace()
    {
        return $this->_ref_exercice_place = $this->loadFwdRef('exercice_place_id', true);
    }


    /**
     * Chargement du praticien
     *
     * @return CMediusers
     */
    function loadRefChir()
    {
        return $this->_ref_chir = $this->loadFwdRef("chir_id", true);
    }

    /**
     * Chargement du remplacant
     *
     * @return CMediusers
     */
    function loadRefRemplacant()
    {
        return $this->_ref_remplacant = $this->loadFwdRef("remplacant_id", true);
    }

    /**
     * Chargement du pour compte
     *
     * @return CMediusers
     */
    function loadRefPourCompte()
    {
        return $this->_ref_pour_compte = $this->loadFwdRef("pour_compte_id", true);
    }

    /**
     * Chargement de la fonction
     *
     * @return CFunctions|CStoredObject
     */
    public function loadRefFunction(): CFunctions
    {
        return $this->_ref_function = $this->loadFwdRef("function_id", true);
    }

    /**
     * Chargement des pauses
     *
     * @return CConsultation[]
     */
    function loadRefPauses()
    {
        $consult = new CConsultation();
        $where   = ['plageconsult_id' => " = $this->_id", 'patient_id' => ' IS NULL'];

        return $this->_ref_pauses = $consult->loadList($where, "heure ASC");
    }

    function countDuplicatedPlages()
    {
        $where = [
            'chir_id' => " = $this->chir_id",
            'freq'    => " = '$this->freq'",
            'debut'   => " = '$this->debut'",
            'fin'     => " = '$this->fin'",
            'date'    => " > '$this->date'",
            "WEEKDAY(`date`) = WEEKDAY('$this->date')",
        ];

        return $this->_count_duplicated_plages = $this->countList($where);
    }

    /**
     * @see parent::getPerm()
     */
    function getPerm($permType)
    {
        if (!$this->_id) {
            return parent::getPerm($permType);
        }
        if (!$this->_ref_chir) {
            $this->loadRefChir();
        }

        return $this->_ref_chir->getPerm($permType)
            && parent::getPerm($permType);
    }

    /**
     * @see parent::check()
     */
    function check()
    {
        $this->completeField("date", "debut", "fin");
        // Data checking
        $msg = null;

        if (!$this->plageconsult_id) {
            if (!$this->chir_id) {
                $msg .= CAppUI::tr('CPlageConsult-Invalid Practitioner');
            }
        }

        //plage blocked by holiday config if not immediate consultation
        if (!$this->_immediate_plage) {
            $holidays = CMbDT::getHolidays();
            if (!CAppUI::loadPref("allow_plage_holiday", $this->chir_id) && array_key_exists(
                    $this->date,
                    $holidays
                ) && !$this->_id) {
                $msg .= CAppUI::tr("CPlageConsult-errror-plage_blocked_by_holidays", $holidays[$this->date]);
            }
        }

        //chir_id se remplace lui même
        if ($this->chir_id == $this->pour_compte_id) {
            $msg .= CAppUI::tr("CPlageConsult-error-pour_compte-equal-chir_id");
        }

        if ($this->chir_id == $this->remplacant_id) {
            $msg .= CAppUI::tr("CPlageConsult-error-remplacant_id-equal-chir_id");
        }

        if ($this->_id) {
            if ($this->fieldModified("date") && $this->countConsultations(false)) {
                $msg .= CAppUI::tr("CPlageConsult-error-date_change");
            }
            if ($this->fieldModified("debut") || $this->fieldModified("fin")) {
                $this->checkLimitHours();
                if (!$this->_hours_limit_valid) {
                    $msg .= CAppUI::tr("CPlageConsult-msg-Some consultations are outside the consultation range");
                }
            }
        }

        return $msg . parent::check();
    }

    /**
     * @see parent::updateFormFields()
     */
    function updateFormFields()
    {
        parent::updateFormFields();
        $this->_total = CMbDT::timeCountIntervals($this->debut, $this->fin, $this->freq);

        if ($this->freq == "1:00:00" || $this->freq == "01:00:00") {
            $this->_freq = "60";
        } else {
            $this->_freq         = substr($this->freq, 3, 2);
            $this->_freq_minutes = CMbDT::minutesRelative("00:00:00", $this->freq);
        }
    }

    /**
     * @see parent::updatePlainFields()
     */
    function updatePlainFields()
    {
        parent::updatePlainFields();
        $this->completeField("freq");
        if ($this->_freq !== null) {
            if ($this->_freq == "60") {
                $this->freq = "01:00:00";
            } else {
                $this->freq = sprintf("00:%02d:00", $this->_freq);
            }
        }
    }

    /**
     * Find the next occurence of similar Plageconsult
     * using the _type_repeat form field
     *
     * @return int Number of weeks jumped
     */
    function becomeNext()
    {
        $week_jumped = 0;

        switch ($this->_type_repeat) {
            case "octuple" :
                $this->date  = CMbDT::date("+8 WEEK", $this->date); // 8
                $week_jumped += 8;
                break;
            case "septuple":
                $this->date  = CMbDT::date("+7 WEEK", $this->date); // 7
                $week_jumped += 7;
                break;
            case "sextuple":
                $this->date  = CMbDT::date("+6 WEEK", $this->date); // 6
                $week_jumped += 6;
                break;
            case "quintuple":
                $this->date  = CMbDT::date("+5 WEEK", $this->date); // 5
                $week_jumped += 5;
                break;
            case "quadruple":
                $this->date  = CMbDT::date("+4 WEEK", $this->date); // 4
                $week_jumped += 4;
                break;
            case "triple":
                $this->date  = CMbDT::date("+3 WEEK", $this->date); // 3
                $week_jumped += 3;
                break;
            case "double":
                $this->date  = CMbDT::date("+2 WEEK", $this->date); // 2
                $week_jumped += 2;
                break;
            case "simple":
                $this->date = CMbDT::date("+1 WEEK", $this->date); // 1
                $week_jumped++;
                break;
            case "sameweek":
                $week_number = CMbDT::weekNumberInMonth($this->date);
                $next_month  = CMbDT::monthNumber(CMbDT::date("+1 MONTH", $this->date));
                $i           = 0;
                do {
                    $this->date = CMbDT::date("+1 WEEK", $this->date);
                    $week_jumped++;
                    $i++;
                } while (
                    $i < 10 &&
                    (CMbDT::monthNumber($this->date) < $next_month) ||
                    (CMbDT::weekNumberInMonth($this->date) != $week_number)
                );
                break;
            default:
                return ++$week_jumped;
        }

        // Stockage des champs modifiés
        $debut             = $this->debut;
        $fin               = $this->fin;
        $freq              = $this->freq;
        $libelle           = $this->libelle;
        $locked            = $this->locked;
        $pour_tiers        = $this->pour_tiers;
        $color             = $this->color;
        $desistee          = $this->desistee;
        $sync_appfine      = $this->sync_appfine;
        $exercice_place_id = $this->exercice_place_id;
        $nb_places         = $this->nb_places;
        $remplacant_id     = $this->desistee ? $this->remplacant_id : "";
        $pour_compte_id    = $this->pour_compte_id;
        $pct_retrocession  = $this->pct_retrocession;
        $teleconsultation  = $this->eligible_teleconsultation;

        // Recherche de la plage suivante
        $where["date"]    = "= '$this->date'";
        $where["chir_id"] = "= '$this->chir_id'";
        $where[]          = "`debut` = '$this->debut' OR `fin` = '$this->fin'";
        if (!$this->loadObject($where)) {
            $this->plageconsult_id = null;
        }

        // Remise en place des champs modifiés
        $this->debut                     = $debut;
        $this->fin                       = $fin;
        $this->freq                      = $freq;
        $this->libelle                   = $libelle;
        $this->locked                    = $locked;
        $this->pour_tiers                = $pour_tiers;
        $this->color                     = $color;
        $this->desistee                  = $desistee;
        $this->sync_appfine              = $sync_appfine;
        $this->exercice_place_id         = $exercice_place_id;
        $this->nb_places                 = $nb_places;
        $this->remplacant_id             = $remplacant_id;
        $this->pour_compte_id            = $pour_compte_id;
        $this->pct_retrocession          = $pct_retrocession;
        $this->eligible_teleconsultation = $teleconsultation;
        $this->updateFormFields();

        if (is_array($this->_pauses)) {
            $this->_pauses = json_encode($this->_pauses);
        }

        return $week_jumped;
    }

    /**
     * @see parent::store()
     */
    function store()
    {
        $this->completeField("pour_compte_id", "chir_id");
        $change_pour_compte = $this->fieldModified("pour_compte_id");

        // Pour les reconvocations dans les urgences
        if (!CModule::getActive("oxCabinet")) {
            $this->function_id = $this->getFunctionUrgence();
        }

        $verify_slot = false;
        //Modification des créneaux si la plage est créée ou si la date, la fréquence, l'heure début ou l'heure de fin est modifié
        if (
            !$this->_id
            || $this->fieldModified("date")
            || $this->fieldModified("_freq")
            || $this->fieldModified("debut")
            || $this->fieldModified("fin")) {
            $verify_slot = true;
        }

        $function_change = false;
        if ($this->fieldModified("function_id") && CModule::getActive("oxCabinet")) {
            $function_change = true;
        }

        if ($msg = parent::store()) {
            return $msg;
        }

        if ($verify_slot) {
            $slot_service = new SlotService();
            $slot_service->verifySlot($this);
        }

        if ($function_change) {
            foreach ($this->loadRefsConsultations() as $_consultation) {
                $_consultation->rques .= " " . CAppUI::tr("oxCabinet-msg-function_changed");
                if ($msg = $_consultation->store()) {
                    return $msg;
                }
            };
        }

        if ($change_pour_compte) {
            $consults = $this->loadRefsConsultations();

            foreach ($consults as $_consult) {
                $facture               = $_consult->loadRefFacture();
                $facture->praticien_id = ($this->pour_compte_id ? $this->pour_compte_id : $this->chir_id);
                $facture->store();
            }
        }

        // Création et modification des pauses
        if ($this->_update_pauses && $this->_pauses) {
            if (!is_array($this->_pauses)) {
                $this->_pauses = utf8_encode($this->_pauses);
                $this->_pauses = json_decode($this->_pauses, true);
            }

            $this->loadRefPauses();

            foreach ($this->_pauses as $_pause) {
                if (!$_pause['pause_id'] || !isset($this->_ref_pauses[$_pause['pause_id']])) {
                    $consult                  = new CConsultation();
                    $consult->patient_id      = null;
                    $consult->plageconsult_id = $this->_id;
                } else {
                    $consult = CConsultation::findOrFail($_pause['pause_id']);
                    unset($this->_ref_pauses[$_pause['pause_id']]);
                }

                $consult->heure  = $_pause['hour'];
                $consult->duree  = $_pause['duration'];
                $consult->motif  = utf8_decode($_pause['motif']);
                $consult->chrono = 16;
                $consult->_hour  = null;
                $consult->_min   = null;
                if ($msg = $consult->store()) {
                    CAppUI::stepAjax($msg, UI_MSG_WARNING);
                }
            }

            if (!empty($this->_ref_pauses)) {
                foreach ($this->_ref_pauses as $_pause) {
                    $_pause->delete();
                }
            }
        }

        // AppFine: create appropriate objects for appfine sync purpose (ExercicePlace and ConsultationCategorie linking)
        $this->updateExercicePlaceConsultationCatLinks();

        return null;
    }

    /**
     * Adds or deletes all CPlageConsultCategorie objects, depending on selected ExercicePlace and ConsultCategorie
     * objects in form.
     */
    public function updateExercicePlaceConsultationCatLinks(): void
    {
        $datasource = $this->getDS();
        $request    = new CRequest();
        $request->addWhereClause('praticien_id', $datasource->prepare(' = ?', $this->chir_id));
        $request->addWhereClause('plage_id', $datasource->prepare(' = ?', $this->_id));
        $plage_consult_categories = (new CPlageConsultCategorie())->loadListByReq($request);

        // If no exercicePlace
        if (!$this->exercice_place_id) {
            foreach ($plage_consult_categories as $_plage_consult_categorie) {
                $_plage_consult_categorie->delete();
            }

            return;
        }

        // Parsing form element into selected objects array
        $this->_consultation_categorie_ids = explode(',', $this->_consultation_categorie_ids);

        // Predeleting objects unselected from form
        foreach ($plage_consult_categories as $_plage_consult_categorie) {
            if (!in_array($_plage_consult_categorie->consult_categorie_id, $this->_consultation_categorie_ids)) {
                $_plage_consult_categorie->delete();
            }
        }

        // Stores (or loads if already created on datasource) linking objects
        foreach ($this->_consultation_categorie_ids as $_consultation_categorie_id) {
            if (!$_consultation_categorie_id) {
                continue;
            }

            $plage_consult_categorie                       = new CPlageConsultCategorie();
            $plage_consult_categorie->praticien_id         = $this->chir_id;
            $plage_consult_categorie->plage_id             = $this->_id;
            $plage_consult_categorie->consult_categorie_id = $_consultation_categorie_id;

            if (null === $plage_consult_categorie->loadMatchingObject()) {
                $plage_consult_categorie->sync_appfine = 1;
                $plage_consult_categorie->store();
            }
        }
    }

    /**
     * Vérification de la validité des date de début et fin de la plage en fonction des heures de consultations
     *
     * @return void
     */
    function checkLimitHours()
    {
        $this->_hours_limit_valid = true;
        if (!$this->_ref_consultations) {
            $this->loadRefsConsultations(false);
        }

        if ($this->_ref_consultations) {
            $_firstconsult_time = reset($this->_ref_consultations)->heure;
            $_lastconsult_time  = end($this->_ref_consultations)->heure;
            if ($this->debut > $_firstconsult_time || $this->fin < $_lastconsult_time) {
                $this->_hours_limit_valid = false;
                $this->_hour_min_valid    = $this->debut > $_firstconsult_time ? $_firstconsult_time : $this->debut;
                $this->_hour_max_valid    = $this->fin < $_lastconsult_time ? $_lastconsult_time : $this->fin;
            }
        }
    }

    /**
     * Choix de la couleur de la plage de consultation du nouveau planning
     *
     * @param int $chir_id Praticien concerné
     *
     * @return string
     */
    function colorPlanning($chir_id)
    {
        $color = CAppUI::isMediboardExtDark() ? "#81a03e" : "#cfc";
        if ($this->remplacant_id && $this->remplacant_id != $chir_id) {
            // Je suis remplacé par un autre médecin
            $color = CAppUI::isMediboardExtDark() ? "#6a79d2" : "#3E9DF4";
        }
        if ($this->remplacant_id && $this->remplacant_id == $chir_id) {
            // Je remplace un autre médecin
            $color = CAppUI::isMediboardExtDark() ? "#d0a675" : "#FDA";
        }

        return $this->_color_planning = $color;
    }

    /**
     * Récupère les créneaux libres
     *
     * @return array
     */
    function getEmptySlots()
    {
        $slots = [];

        $this->loadRefChir();

        $praticien = $this->_ref_chir->_id;

        $consultations = $this->loadRefsConsultations();

        for ($i = 0; $i < $this->_total; $i++) {
            $minutes = $this->_freq * $i;

            $slots[$i] = [
                "date"          => $this->date,
                "prat_id"       => $praticien,
                "praticien"     => $this->_ref_chir->_view,
                "plage_id"      => $this->_id,
                "hour"          => CMbDT::time("+ $minutes minutes", $this->debut),
                "libelle_plage" => $this->libelle,
            ];

            foreach ($consultations as $_consultation) {
                $keyPlace = CMbDT::timeCountIntervals($this->debut, $_consultation->heure, $this->freq);

                // Free the slot if the consultation is canceled
                if ($_consultation->annule) {
                    continue;
                }

                for ($j = 0; $j < $_consultation->duree; $j++) {
                    if (isset($slots[($keyPlace + $j)])) {
                        unset($slots[($keyPlace + $j)]);
                    }
                }
            }
        }

        return $slots;
    }

    /**
     * Returns a collection of available appointment slots, available for online booking.
     *
     * @return array
     */
    public function getEmptySlotsNbPlaces(): array
    {
        $slots         = [];
        $consultations = $this->loadRefsConsultations(false);

        // Providing free plage consult slots
        for ($i = 0; $i < $this->_total; $i++) {
            $minutes   = $this->_freq * $i;
            $slots[$i] = [
                "date"      => $this->date,
                "hour"      => CMbDT::time("+ $minutes minutes", $this->debut),
                "nb_places" => $this->nb_places,
            ];
        }

        // For each consultation, check its date difference with plage consult start date by slot frequency
        // Then alters concerned slot with its difference, decrements its available place number
        foreach ($consultations as $_consultation) {
            $interval = CMbDT::timeCountIntervals($this->debut, $_consultation->heure, $this->freq);

            if (isset($slots[$interval])) {
                $slots[$interval]['nb_places'] = strval($slots[$interval]['nb_places'] - 1);

                // If slot is full, it will be not available anymore and then not be exported
                if ($slots[$interval]['nb_places'] == 0) {
                    unset($slots[$interval]);
                }
            }
        }

        return $slots;
    }

    /**
     * Récupère le numéro de la semaine prochaine si la plage horaire existe
     *
     * @param string $next_monday Date de l'existance de la plage a vérifier
     * @param int    $week_number Week number
     *
     * @return int|null
     */
    function getNumberNextWeek($next_monday, $week_number)
    {
        $where         = [];
        $plage         = new CPlageconsult();
        $where["date"] = " >= '$next_monday'";
        $plage->loadObject($where);

        $number_next_week = $plage->_id ? $week_number + 1 : "";

        return $number_next_week;
    }

    /**
     * Récupère les libellés de plages mis en préférence utilisateur
     *
     * @return array
     */
    static function getLibellesPref()
    {
        $libelles                   = [];
        $see_plages_consult_libelle = CAppUI::pref("see_plages_consult_libelle");
        if ($see_plages_consult_libelle) {
            $libelles = explode("|", $see_plages_consult_libelle);
            if (!array_search('automatique', $libelles, true)) {
                // Ajout des plages créées par les consultations immédiates
                $libelles[] = "automatique";
            }
        }

        return $libelles;
    }

    /**
     * @inheritdoc
     */
    public function isExportable($prat_ids = [], $date_min = null, $date_max = null, ...$additional_args)
    {
        // Check if prat is in the export list
        $prats = (!$prat_ids || in_array($this->chir_id, $prat_ids));

        // Check if the dates are ok for export
        $dates = false;
        if (!$date_min && !$date_max) {
            $dates = true;
        } elseif ($date_min && $date_max) {
            $dates = (bool)($date_min <= $this->date && $date_max >= $this->date);
        } elseif ($date_max) {
            $dates = (bool)($date_max >= $this->date);
        } elseif ($date_min) {
            $dates = (bool)($date_min <= $this->date);
        }

        return $prats && $dates;
    }

    /**
     * @return void
     */
    static function initHoursMinutes()
    {
        $start = CAppUI::gconf("dPcabinet CPlageconsult hours_start");
        $stop  = CAppUI::gconf("dPcabinet CPlageconsult hours_stop");

        CPlageconsult::$hours_start      = str_pad($start, 2, "0", STR_PAD_LEFT);
        CPlageconsult::$hours_stop       = str_pad($stop, 2, "0", STR_PAD_LEFT);
        CPlageconsult::$minutes_interval = CValue::first(
            CAppUI::gconf("dPcabinet CPlageconsult minutes_interval"),
            "15"
        );

        $hours = range($start, $stop);
        $mins  = range(0, 59, CPlageconsult::$minutes_interval);

        foreach ($hours as $key => $hour) {
            CPlageconsult::$hours[$hour] = str_pad($hour, 2, "0", STR_PAD_LEFT);
        }

        foreach ($mins as $key => $min) {
            CPlageconsult::$minutes[] = str_pad($min, 2, "0", STR_PAD_LEFT);
        }
    }

    /**
     * @inheritDoc
     */
    public function matchForImport(MatcherVisitorInterface $matcher): ImportableInterface
    {
        return $matcher->matchPlageConsult($this);
    }

    /**
     * @inheritDoc
     */
    public function persistForImport(PersisterVisitorInterface $persister): ImportableInterface
    {
        return $persister->persistObject($this);
    }

    /**
     * @return Collection|null
     * @throws ApiException
     */
    public function getResourceMedicalAppointments(): ?Collection
    {
        if (!$consultations = $this->loadRefsConsultations()) {
            return null;
        }

        return new Collection($consultations);
    }

    /**
     * @param string $datetime
     *
     * @return int|null
     */
    public function getSlotId(string $datetime): ?int
    {
        $start = "$this->date $this->debut";
        for ($i = 1; $i <= ($this->_total + 1); $i++) {
            if ($i !== 1) {
                $start = CMbDT::dateTime("+$this->_freq_minutes MINUTES", $start);
            }
            if ($start === $datetime) {
                break;
            }
        }

        if ($i > ($this->_total + 1)) {
            return null;
        }

        return $i;
    }

    /**
     * Get the emergency function for
     *
     * @param string $datetime
     *
     * @return int|string
     */
    public function getFunctionUrgence()
    {
        $user                             = CMediusers::get($this->chir_id);
        $group                            = CGroups::loadCurrent();
        $is_urgentiste_main_function      = in_array($group->service_urgences_id, [$user->function_id]);
        $is_urgentiste_secondary_function = in_array(
            $group->service_urgences_id,
            CMbArray::pluck(
                $user->loadRefsSecondaryFunctions($group->_id),
                '_id'
            )
        );
        $function_urgence_id              = "";

        if ($is_urgentiste_main_function) {
            $function_urgence_id = $user->function_id;
        } elseif ($is_urgentiste_secondary_function) {
            $function_urgence_id = $group->service_urgences_id;
        }

        return $function_urgence_id;
    }

    /**
     * Sort by date Evenement and Consultation
     * @return void
     */
    public function sortItemsByDate(): void
    {
        usort(
            $this->_items,
            function ($a, $b) {
                $a_date = $a instanceof CConsultation ? $a->heure : $a->_heure_begin_operation;
                $b_date = $b instanceof CConsultation ? $b->heure : $b->_heure_begin_operation;

                return $a_date <=> $b_date;
            }
        );
    }

    /**
     * add min freq value to getSampleObject
     * @throws CModelObjectException
     */
    public static function getSampleObject($class = null, bool $only_notNull = true): CModelObject
    {
        /** @var CPlageconsult $plage_consult */
        $plage_consult        = parent::getSampleObject($class, $only_notNull);
        $plage_consult->freq  = '01:00:00';
        $plage_consult->debut = '09:00:00';
        $plage_consult->fin   = '18:00:00';

        return $plage_consult;
    }

    /**
     * @return CSlot[]
     */
    public function loadRefsSlots($order = null, $where = [], $backnamealt = "")
    {
        return $this->_ref_slots = $this->loadBackRefs("slots", $order, null, null, null, null, $backnamealt, $where);
    }

    /**
     * @return self[]
     * @throws Exception
     */
    public function loadPlagesPerDayConsult(int $prat_id, string $date): array
    {
        $where = [
            'chir_id' => $this->getDS()->prepare('= ?', $prat_id),
            'date'    => $this->getDS()->prepare('= ?', $date),
        ];

        /** @var self[] $plagesPerDayConsult */
        $plagesPerDayConsult = $this->loadList($where);

        CStoredObject::massLoadBackRefs($plagesPerDayConsult, "consultations", 'heure ASC');

        foreach ($plagesPerDayConsult as $key => $plageConsult) {
            $plageConsult->countPatients();
            $plageConsult->loadFillRate();
            $plageConsult->loadRefsConsultations(false);
        }

        return $plagesPerDayConsult;
    }
}
