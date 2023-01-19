<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CModelObject;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\SalleOp\CActeCCAM;

/**
 * Class use to filter COperations or CConsultation on the state of the cotation
 */
class CFilterCotation implements IShortNameAutoloadable
{
    /** @var CSQLDataSource The data source */
    protected $ds;

    /** @var integer The id of the selected chir, if equal to 0, all the chir will be selected */
    protected $chir_id;

    /** @var integer The id of the selected function */
    protected $function_id;

    /** @var integer A filter on the speciality of the practitioners */
    protected $speciality;

    /** @var string The begin date of the selected time period */
    protected $begin_date;

    /** @var string The end date for the selected time period */
    protected $end_date;

    /** @var string The begin date filter for the CSejour */
    protected $begin_sejour;

    /** @var string The end date filter for the CSejour */
    protected $end_sejour;

    /** @var string The selected type of CSejour */
    protected $sejour_type;

    /** @var array The classes of the targeted objects, COperation, CConsultation, CSejour, CSejour-seance */
    protected $object_classes;

    /** @var boolean If true, the objects with unexported acts will be requested */
    protected $show_unexported_acts = 0;

    /** @var boolean If true, the objects without ccam codes will be requested */
    protected $objects_without_codes = 0;

    /** @var string A filter for the libelle for the COperations, the motif for the CConsultation */
    protected $libelle;

    /** @var array A filter for the CCAM codes */
    protected $ccam_codes = [];

    /** @var string A filter on the NDA of a CSejour */
    protected $nda;

    /** @var integer A filter on the patient id */
    protected $patient_id;

    /** @var string A filter the lock field of the CCodageCCAM */
    protected $codage_lock_status;

    /** @var boolean Only displays the user with missing codes in the stats by period mode */
    protected $only_show_missing_codes;

    /** @var boolean If set to true, all the objects within the selected period will be displayed, despite the others filters */
    protected $display_all;

    /** @var CMediusers[] */
    protected $_chirs;

    /** @var array */
    protected $_chir_ids;

    /** @var array The periods for displaying the acts */
    protected static $periods = [
        1   => '1_2',
        3   => '3_7',
        8   => '8_30',
        31  => '31_60',
        61  => '61_120',
        121 => '121_202',
        203 => '203',
    ];

    /** @var string[] */
    public static $list_codage_lock_status = [
        'unlocked',
        'locked_by_chir',
        'locked',
    ];

    /** @var string A filter on the excess fee payment type for surgeons */
    protected $excess_fee_chir_status;

    /** @var string A filter on the excess fee payment status for anesthesists */
    protected $excess_fee_anesth_status;

    /**
     * @param array $fields An associative array, with keys equals to the name of the fields
     */
    public function __construct(array $fields = [])
    {
        foreach ($fields as $_field => $_value) {
            if (property_exists($this, $_field)) {
                if (($_field == 'chir_id' && $_field == 'function_id') && $_value == 0) {
                    $_value = null;
                } elseif ($_field == 'ccam_codes') {
                    $_value = explode('|', $_value);
                }

                $this->$_field = $_value;
            }
        }

        $this->ds = CSQLDataSource::get('std');
    }

    /**
     * Load the operations for the selected chirs,
     * and count the uncreated and the unexpoted acts for each chir by period
     *
     * @param int $start The start
     * @param int $limit The number of results
     *
     * @return array
     */
    public function getStatsByPeriod(int $start = 0, int $limit = 20): array
    {
        $periods = $this->getPeriods(false);

        $results = [
            'page'  => $start,
            'chirs' => [],
        ];

        $this->_chirs = [];
        if ($this->chir_id) {
            $user                         = CMediusers::get($this->chir_id);
            $this->_chirs[$this->chir_id] = $user;
            $user->loadRefsSecondaryUsers();
            foreach ($user->_ref_secondary_users as $_secondary_user) {
                $this->_chirs[$_secondary_user->_id] = $_secondary_user;
            }
        } else {
            $user         = CMediusers::get();
            $this->_chirs = $user->loadPraticiens(PERM_READ);
        }

        $this->_chir_ids = array_keys($this->_chirs);

        $totals = [];

        foreach ($periods as $_period) {
            $operations = $this->loadOperations($_period);
            $plagesops  = CStoredObject::massLoadFwdRef($operations, 'plageop_id');
            $sejours    = CStoredObject::massLoadFwdRef($operations, 'sejour_id');
            CStoredObject::massLoadFwdRef($sejours, 'patient_id');

            foreach ($this->_chirs as $_chir) {
                if (!array_key_exists($_chir->_id, $results['chirs'])) {
                    $results['chirs'][$_chir->_id] = [
                        'chir'    => $_chir,
                        'periods' => [],
                    ];
                    $totals[$_chir->_id]           = [
                        'no_codes'              => 0,
                        'uncreated_acts'        => 0,
                        'price_uncreated_acts'  => 0,
                        'unexported_acts'       => 0,
                        'price_unexported_acts' => 0,
                    ];
                }

                $period_no_codes              = 0;
                $period_uncreated_acts        = 0;
                $period_price_uncreated_acts  = 0;
                $period_unexported_acts       = 0;
                $period_price_unexported_acts = 0;

                foreach ($operations as $_key => $_operation) {
                    if (array_key_exists($_operation->plageop_id, $plagesops)) {
                        $_plage = $plagesops[$_operation->plageop_id];
                    } else {
                        $_plage = new CPlageOp();
                    }
                    if (
                        $_operation->chir_id != $_chir->_id && $_operation->chir_2_id != $_chir->_id
                        && $_operation->chir_3_id != $_chir->_id
                        && $_operation->chir_4_id != $_chir->_id && $_operation->anesth_id != $_chir->_id
                        && $_plage->anesth_id != $_chir->_id
                    ) {
                        continue;
                    }

                    if (strlen($_operation->codes_ccam) == 0) {
                        $period_no_codes++;
                    } else {
                        $_operation->loadExtCodesCCAM();
                        $_operation->loadRefsActesCCAM();
                        $_operation->loadRefPatient();

                        $created_acts          = [];
                        $count_uncreated_acts  = 0;
                        $price_uncreated_acts  = 0;
                        $count_unexported_acts = 0;
                        $price_unexported_acts = 0;

                        /* We count the number of acts for the given chir, and the number of unexported acts */
                        foreach ($_operation->_ref_actes_ccam as $_act) {
                            $_act->loadRefExecutant();
                            $_act->_ref_executant->isAnesth();
                            $_act->_ref_executant->loadRefFunction();

                            $_full_code = "$_act->code_acte-$_act->code_activite-$_act->code_phase";
                            /* Each code is associated to an array to handle the case
                            where a code is added multiple times to the codable */
                            if (!array_key_exists($_full_code, $created_acts)) {
                                $created_acts["$_act->code_acte-$_act->code_activite-$_act->code_phase"] = [$_act];
                            } else {
                                $created_acts["$_act->code_acte-$_act->code_activite-$_act->code_phase"][] = $_act;
                            }

                            if ($_act->executant_id == $_chir->_id && !$_act->sent) {
                                $count_unexported_acts++;
                                $price_unexported_acts += $_act->getTarif();
                            }
                        }

                        /* We count the number of uncreated acts */
                        foreach ($_operation->_ext_codes_ccam as $_code) {
                            foreach ($_code->activites as $_activity_number => $_activity) {
                                foreach ($_activity->phases as $_phase_number => $_phase) {
                                    $_full_code = "$_code->code-$_activity_number-$_phase_number";
                                    $coded      = array_search($_full_code, array_keys($created_acts));

                                    if ($coded === false) {
                                        if (
                                            ($_chir->isAnesth() && $_activity_number == 4)
                                            || (!$_chir->isAnesth() && $_activity_number != 4)
                                        ) {
                                            $count_uncreated_acts++;
                                            $field                = 'tarif_g' . CContexteTarifaireCCAM::getPriceGrid(
                                                $_chir,
                                                $_operation->_ref_patient,
                                                $_operation->date
                                            );
                                            $price_uncreated_acts += $_phase->$field;
                                        }
                                    } elseif (count($created_acts[$_full_code]) == 1) {
                                        unset($created_acts[$_full_code]);
                                    } else {
                                        array_pop($created_acts[$_full_code]);
                                    }
                                }
                            }
                        }

                        $period_uncreated_acts        += $count_uncreated_acts;
                        $period_price_uncreated_acts  += $price_uncreated_acts;
                        $period_unexported_acts       += $count_unexported_acts;
                        $period_price_unexported_acts += $price_unexported_acts;
                    }
                }

                $results['chirs'][$_chir->_id]['periods'][$_period] = [
                    'no_codes'              => $period_no_codes,
                    'uncreated_acts'        => $period_uncreated_acts,
                    'price_uncreated_acts'  => $period_price_uncreated_acts,
                    'unexported_acts'       => $period_unexported_acts,
                    'price_unexported_acts' => $period_price_unexported_acts,
                ];

                $totals[$_chir->_id]['no_codes']              += $period_no_codes;
                $totals[$_chir->_id]['uncreated_acts']        += $period_uncreated_acts;
                $totals[$_chir->_id]['price_uncreated_acts']  += $period_price_uncreated_acts;
                $totals[$_chir->_id]['unexported_acts']       += $period_unexported_acts;
                $totals[$_chir->_id]['price_unexported_acts'] += $period_price_unexported_acts;
            }
        }

        foreach ($this->_chirs as $_chir) {
            if (isset($totals[$_chir->_id])) {
                if (
                    $this->only_show_missing_codes && !$totals[$_chir->_id]['no_codes']
                    && !$totals[$_chir->_id]['uncreated_acts'] && !$totals[$_chir->_id]['unexported_acts']
                ) {
                    unset($results['chirs'][$_chir->_id]);
                } else {
                    $results['chirs'][$_chir->_id]['periods']['total'] = [
                        'no_codes'              => $totals[$_chir->_id]['no_codes'],
                        'uncreated_acts'        => $totals[$_chir->_id]['uncreated_acts'],
                        'price_uncreated_acts'  => $totals[$_chir->_id]['price_uncreated_acts'],
                        'unexported_acts'       => $totals[$_chir->_id]['unexported_acts'],
                        'price_unexported_acts' => $totals[$_chir->_id]['price_unexported_acts'],
                    ];
                }
            }
        }

        $results['nb_chirs'] = count($results['chirs']);
        if ($limit) {
            $results['chirs'] = array_slice($results['chirs'], $start, $limit);
        }

        return $results;
    }

    /**
     * Export the results of the stats by period in a CSV file
     *
     * @param array $periods The periods
     * @param array $results The results
     *
     * @return CCSVFile
     */
    public function exportStatsByPeriod(array $periods, array $results): CCSVFile
    {
        $export = new CCSVFile();

        $header = [
            'Praticiens',
        ];

        foreach ($periods as $_period => $_dates) {
            $header[] = CAppUI::tr("pmsi-title-stats_cotation.$_period") . ' '
                . str_replace('<br/>', ' ', CAppUI::tr('pmsi-title-stats_cotation.no_codes'));
            $header[] = CAppUI::tr("pmsi-title-stats_cotation.$_period") . ' '
                . str_replace('<br/>', ' ', CAppUI::tr('pmsi-title-stats_cotation.number_uncreated_acts'));
            $header[] = CAppUI::tr("pmsi-title-stats_cotation.$_period") . ' '
                . str_replace('<br/>', ' ', CAppUI::tr('pmsi-title-stats_cotation.total_uncreated_acts'));
            $header[] = CAppUI::tr("pmsi-title-stats_cotation.$_period") . ' '
                . str_replace('<br/>', ' ', CAppUI::tr('pmsi-title-stats_cotation.number_unexported_acts'));
            $header[] = CAppUI::tr("pmsi-title-stats_cotation.$_period") . ' '
                . str_replace('<br/>', ' ', CAppUI::tr('pmsi-title-stats_cotation.total_unexported_acts'));
        }

        $export->writeLine($header);

        foreach ($results['chirs'] as $_result) {
            $_chir = $_result['chir'];
            $_line = [
                $_chir,
            ];

            foreach ($_result['periods'] as $_data) {
                $_line[] = $_data['no_codes'];
                $_line[] = $_data['uncreated_acts'];
                $_line[] = $_data['price_uncreated_acts'];
                $_line[] = $_data['unexported_acts'];
                $_line[] = $_data['price_unexported_acts'];
            }

            $export->writeLine($_line);
        }

        return $export;
    }

    /**
     * Load the details of the objects for the given chir for the selected period
     *
     * @param string $period The selected period
     *
     * @return array
     */
    public function getCotationDetails(string $period = null): array
    {
        $this->_chir_ids = [];

        if ($this->function_id) {
            $perm = PERM_READ;
            if (CAppUI::pref('allow_other_users_board') == 'write_right') {
                $perm = PERM_EDIT;
            }

            $chir            = new CMediusers();
            $this->_chirs    = $chir->loadPraticiens($perm, $this->function_id);
            $this->_chir_ids = array_keys($this->_chirs);
        } else {
            $chir                         = CMediusers::get($this->chir_id);
            $this->_chirs[$this->chir_id] = $chir;
            if ($this->chir_id) {
                $this->_chir_ids[] = $this->chir_id;
            }
            $chir->loadRefsSecondaryUsers();
            foreach ($chir->_ref_secondary_users as $_secondary_user) {
                $this->_chirs[$_secondary_user->_id] = $_secondary_user;
                $this->_chir_ids[]                   = $_secondary_user->_id;
            }
        }

        $objects = $this->loadObjects($period);
        $totals  = [];

        foreach ($objects as $_class => &$_codables) {
            $totals[$_class] = count($_codables);
            CStoredObject::massLoadBackRefs($_codables, 'actes_ccam');
            CStoredObject::massLoadBackRefs($_codables, 'actes_ngap');

            if ($_class == 'COperation') {
                $sejours = CStoredObject::massLoadFwdRef($_codables, 'sejour_id');
                CStoredObject::massLoadFwdRef($_codables, 'plageop_id');
                $patients = CStoredObject::massLoadFwdRef($sejours, 'patient_id');
                CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
            } elseif (in_array($_class, ['CConsultation', 'CSejour', 'CSejour-seance'])) {
                $patients = CStoredObject::massLoadFwdRef($_codables, 'patient_id');
                CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
            }

            CDatedCodeCCAM::$cache_layers = Cache::INNER_OUTER;
            /**
             * @var integer  $_key
             * @var CCodable $_codable
             */
            foreach ($_codables as $_key => $_codable) {
                $_codable->loadExtCodesCCAM();
                $_codable->loadRefsActesCCAM();
                $_codable->loadRefsCodagesCCAM();
                $_codable->loadRefsActesNGAP();
                $_codable->loadRefPatient()->updateBMRBHReStatus($_codable);

                if (in_array($_codable->_class, ['CConsultation', 'COperation'])) {
                    $_codable->loadRefSejour();
                }

                $_codable->loadRefPraticien()->loadRefFunction();

                if ($_codable->_class == 'COperation') {
                    $_codable->loadRefPlageOp();
                }

                if (
                    !count($_codable->_ext_codes_ccam) && count($_codable->_ref_actes_ngap)
                    && !$this->display_all && $_codable->_class == 'CConsultation'
                ) {
                    unset($_codables[$_key]);
                    continue;
                }

                $_codable->_actes_non_cotes = 0;
                $created_acts               = [];
                $nb_created_acts            = 0;
                $nb_total_acts              = 0;
                $nb_unexported_acts         = 0;
                /** @var boolean $coded_chir Indicate if the selected chir has coded an act for the codable */
                $coded_chir = false;

                foreach ($_codable->_ref_actes_ccam as $_act) {
                    $_act->loadRefExecutant();
                    $_act->_ref_executant->isAnesth();
                    $_act->_ref_executant->loadRefFunction();

                    $_full_code = "$_act->code_acte-$_act->code_activite-$_act->code_phase";
                    /* Each code is associated to an array to handle the case
                    where a code is added multiple times to the codable */
                    if (!array_key_exists($_full_code, $created_acts)) {
                        $created_acts[$_full_code] = [$_act];
                    } else {
                        $created_acts[$_full_code][] = $_act;
                    }

                    if ($_act->executant_id == $this->chir_id) {
                        $coded_chir = true;
                        $nb_created_acts++;

                        if (!$_act->sent && $this->show_unexported_acts) {
                            $nb_unexported_acts++;
                        }
                    }
                }

                $locked_codage = false;
                if (in_array($_codable->_class, ['CConsultation', 'COperation'])) {
                    if (
                        $this->chir_id && array_key_exists($this->chir_id, $_codable->_ref_codages_ccam)
                        && ($this->codage_lock_status != 'locked' && $this->codage_lock_status != 'locked_by_chir')
                    ) {
                        $_codage       = reset($_codable->_ref_codages_ccam[$this->chir_id]);
                        $locked_codage = ($_codage->locked == '1');
                    }
                } elseif ($_codable->_class == 'CSejour') {
                    if (
                        $this->chir_id && array_key_exists($this->chir_id, $_codable->_ref_codages_ccam)
                        && ($this->codage_lock_status != 'locked' && $this->codage_lock_status != 'locked_by_chir')
                    ) {
                        $_codages = reset($_codable->_ref_codages_ccam[$this->chir_id]);
                        if ($_codages) {
                            foreach ($_codages as $date => $_codage) {
                                $locked_codage = ($_codage->locked == '1');
                            }
                        }
                    }
                }

                /* We count the number of uncreated acts */
                foreach ($_codable->_ext_codes_ccam as $_code) {
                    foreach ($_code->activites as $_activity_number => $_activity) {
                        foreach ($_activity->phases as $_phase_number => $_phase) {
                            $_full_code = "$_code->code-$_activity_number-$_phase_number";
                            $coded      = array_search($_full_code, array_keys($created_acts));

                            if ($coded === false) {
                                if (
                                    $_class == 'CConsultation'
                                    || ($_class == 'COperation' && $chir->_id == $_codable->chir_id
                                        && $_activity_number != 4) || ($_class == 'COperation' && $_activity_number == 4
                                        && ($_codable->anesth_id == $chir->_id
                                            || $_codable->_ref_plageop->anesth_id == $chir->_id))
                                ) {
                                    $nb_total_acts++;
                                    $_codable->_actes_non_cotes++;
                                }
                            } else {
                                if (
                                    count($created_acts[$_full_code]) == 1
                                    && ($this->codage_lock_status != 'locked'
                                        || $this->codage_lock_status != 'locked_by_chir')
                                ) {
                                    $_act = $created_acts[$_full_code][0];
                                    unset($created_acts[$_full_code]);
                                } else {
                                    $_act = array_pop($created_acts[$_full_code]);
                                }

                                if ($_act->executant_id == $this->chir_id) {
                                    $nb_total_acts++;
                                }
                            }
                        }
                    }
                }

                if (
                    ($nb_total_acts <= $nb_created_acts || $locked_codage) && $coded_chir && ($nb_unexported_acts == 0
                        || ($nb_unexported_acts > 0 && !$this->show_unexported_acts))
                    && strlen($_codable->codes_ccam) != 0
                    && !$this->codage_lock_status && !$this->display_all
                ) {
                    unset($_codables[$_key]);
                }
            }

            if ($_class == 'COperation') {
                $_codables = CModelObject::naturalSort($_codables, ['_datetime']);
            } elseif ($_class == 'CConsultation') {
                $_codables = CModelObject::naturalSort($_codables, ['_date']);
            } elseif ($_class == 'CSejour') {
                $_codables = CModelObject::naturalSort($_codables, ['entree']);
            } elseif ($_class == 'CSejour-seances') {
                $_codables = CModelObject::naturalSort($_codables, ['entree']);
            }
        }

        $objects['totals'] = $totals;

        return $objects;
    }

    /**
     * Count the codables (operations or consultatoins) with no coded acts in the given period, for the given
     * executioner
     *
     * @param CMediusers $executioner The user who execute the acts
     * @param string     $class       The codable class (COperation or CConsultation)
     * @param array      $users       The chirs or anesth that made the operations
     * @param string     $date        The date
     * @param string     $period      The period duration, 3 months by default
     *
     * @return int
     */
    public static function countUncodedCodable(
        CMediusers $executioner,
        string $class,
        array $users = [],
        string $date = '',
        string $period = '3 MONTHS'
    ): int {
        if ($class != 'CConsultation' && $class != 'COperation' && $class != 'CSejour' && $class != 'CSejour-seance') {
            return 0;
        }

        if (empty($users)) {
            $users = [$executioner->_id];
            $executioner->loadRefsSecondaryUsers();
            if (count($executioner->_ref_secondary_users)) {
                $users = array_merge($users, array_keys($executioner->_ref_secondary_users));
            }
        }

        $in_users = CSQLDataSource::prepareIn($users);

        $date = CMbDT::date(null, $date);
        $from = CMbDT::date("-$period", $date);

        $where = [];
        $ljoin = [];
        switch ($class) {
            case 'CConsultation':
                $table                            = 'consultation';
                $id_field                         = 'consultation_id';
                $where[]                          = "plageconsult.date BETWEEN '$from' AND '$date'";
                $where["consultation.annule"]     = "= '0'";
                $where['consultation.patient_id'] = "IS NOT NULL";
                $where["plageconsult.chir_id"]    = $in_users;
                $ljoin["plageconsult"]            = "plageconsult.plageconsult_id = consultation.plageconsult_id";
                break;
            case 'CSejour':
                $from = CMbDT::dateTime("00:00:00", $from);
                $date = CMbDT::dateTime("23:59:59", $date);
                $table                  = 'sejour';
                $id_field               = 'sejour_id';
                $where[]                = "sejour.praticien_id $in_users";
                $where[]                = "sejour.entree BETWEEN '$from' AND '$date' 
                OR sejour.sortie BETWEEN '$from' AND '$date'";
                $where['sejour.annule'] = " = '0'";
                break;
            case 'CSejour-seance':
                $from = CMbDT::dateTime("00:00:00", $from);
                $date = CMbDT::dateTime("23:59:59", $date);
                $table                  = 'sejour';
                $id_field               = 'sejour_id';
                $where[]                = "sejour.praticien_id $in_users";
                $where[]                = "sejour.entree BETWEEN '$from' AND '$date' 
                OR sejour.sortie BETWEEN '$from' AND '$date'";
                $where['sejour.type']   = " = 'seances'";
                $where['sejour.annule'] = " = '0'";
                $class                  = 'CSejour';
                break;
            case 'COperation':
            default:
                $table                       = 'operations';
                $id_field                    = 'operation_id';
                $where[]                     = "`operations`.`chir_id` $in_users 
                    OR `operations`.`chir_2_id` $in_users OR " .
                    "`operations`.`chir_3_id` $in_users OR `operations`.`chir_4_id` $in_users OR " .
                    "(`operations`.`anesth_id` IS NULL AND `plagesop`.`anesth_id` $in_users) 
                    OR `operations`.`anesth_id` $in_users";
                $where['operations.date']    = "BETWEEN '$from' AND '$date'";
                $where['operations.annulee'] = " = '0'";
                $ljoin['plagesop']           = 'plagesop.plageop_id = operations.plageop_id';
        }

        $ds = CSQLDataSource::get('std');
        /* We get the list of operations within three months */
        $query = new CRequest();
        $query->addTable($table);
        $query->addSelect("COUNT({$id_field})");
        $query->addLJoin($ljoin);

        $subquery = new CRequest();
        $subquery->addTable('acte_ccam');
        $subquery->addColumn('acte_ccam.acte_id');
        $subquery->addWhere(
            [
                "acte_ccam.object_id = $table.$id_field",
                "acte_ccam.object_class = '$class'",
                "acte_ccam.executant_id $in_users",
            ]
        );

        $where[] = 'NOT EXISTS (' . $subquery->makeSelect() . ')';

        if ($class != 'COperation') {
            $subquery = new CRequest();
            $subquery->addTable('acte_ngap');
            $subquery->addColumn('acte_ngap.acte_ngap_id');
            $subquery->addWhere(
                [
                    "acte_ngap.object_id = $table.$id_field",
                    "acte_ngap.object_class = '$class'",
                    "acte_ngap.executant_id $in_users",
                ]
            );

            $where[] = 'NOT EXISTS (' . $subquery->makeSelect() . ')';
        }

        $query->addWhere($where);

        return (int)$ds->loadResult($query->makeSelect());
    }

    /**
     * Return all the CActeNGAP matching the filters
     *
     * @param int    $start       The starting point of the results
     * @param int    $number      The number of act to load
     * @param string $sort_column The name of the column for sorting the acts
     * @param string $sort_way    The way the acts must be sorted (asc or desc)
     * @param string $action      The action to perform (list, print or export)
     *
     * @return array
     * @throws Exception
     */
    public function getCotationsNGAP(
        int $start = 0,
        int $number = 40,
        string $sort_column = 'executant_id',
        string $sort_way = 'DESC',
        string $action = 'list'
    ): array {
        $results = [
            'total'  => 0,
            'start'  => $start,
            'number' => $number,
            /** @var CActeNGAP[] */
            'acts'   => [],
        ];
        $act     = new CActeNGAP();

        /* Preparing the query */
        $where = [
            'acte_ngap.execution' => $this->ds->prepare(
                ' BETWEEN ?1 AND ?2',
                "{$this->begin_date} 00:00:00",
                "{$this->end_date} 23:59:59"
            ),
        ];

        $ljoin = [
            'users' => 'users.user_id = acte_ngap.executant_id',
            'users AS prescripteur ON prescripteur.user_id = acte_ngap.prescripteur_id',
        ];

        $this->setWhereClausesCotations($where, $ljoin);

        $ds    = CSQLDataSource::get('std');
        $query = new CRequest();
        $query->addSelect('COUNT(DISTINCT acte_ngap.acte_ngap_id)');
        $query->addTable('acte_ngap');
        $query->addWhere($where);
        $query->addLJoin($ljoin);
        $results['total'] = $ds->loadResult($query->makeSelect());

        $select = [
            'DISTINCT(acte_ngap.acte_ngap_id)',
            "CONCAT(users.user_last_name, ' ', users.user_first_name) AS executant_name",
            "CONCAT(prescripteur.user_last_name, ' ', prescripteur.user_first_name) AS prescripteur_name",
            '(acte_ngap.montant_base + IFNULL(acte_ngap.montant_depassement, 0)) AS tarif',
        ];
        $this->getCotationPatientLeftJoin($ljoin, $select);

        $query = new CRequest();
        $query->addSelect($select);
        $query->addTable('acte_ngap');
        $query->addWhere($where);
        $query->addLJoin($ljoin);

        $order = $this->getCotationNGAPOrderClause($sort_column, $sort_way);

        if (!is_null($start) && $number) {
            $query->setLimit("{$start}, {$number}");
        }

        $query->addOrder($order);

        $rows = $ds->loadList($query->makeSelect());

        if (is_array($rows) && count($rows)) {
            $act_ngap = new CActeNGAP();
            $ids      = CMbArray::pluck($rows, 'acte_ngap_id');
            /* We resort the acts because the loadAll changes the order */
            $results['acts'] = CMbArray::ksortByArray($act_ngap->loadAll($ids), $ids);
            $objects         = CMbObject::massLoadFwdRef($results['acts'], 'object_id');

            $sejours       = [];
            $operations    = [];
            $consultations = [];
            foreach ($objects as $object) {
                switch ($object->_class) {
                    case 'CConsultation':
                        $consultations[$object->_id] = $object;
                        break;
                    case 'COperation':
                        $operations[$object->_id] = $object;
                        break;
                    case 'CSejour':
                    default:
                        $sejours[$object->_id] = $object;
                }
            }

            if (count($operations)) {
                $sejours = array_merge($sejours, CMbObject::massLoadFwdRef($operations, 'sejour_id'));
            }

            if (count($sejours)) {
                CMbObject::massLoadFwdRef($sejours, 'patient_id');
            }

            if (count($consultations)) {
                $sejours = array_merge($sejours, CMbObject::massLoadFwdRef($consultations, 'sejour_id'));
                CMbObject::massLoadFwdRef($consultations, 'patient_id');
            }

            $users = CMbObject::massLoadFwdRef($results['acts'], 'executant_id');
            $users = array_merge($users, CMbObject::massLoadFwdRef($results['acts'], 'prescripteur_id'));
            CMbObject::massLoadFwdRef($users, 'function_id');
            CSejour::massLoadNDA($sejours);

            /** @var CActeNGAP $_act */
            foreach ($results['acts'] as $_act) {
                $_act->loadRefExecutant();
                $_act->loadRefPrescripteur();
                $_act->loadTargetObject()->loadRefPatient();

                if ($_act->object_class == 'CSejour') {
                    $_act->_ref_object->loadNDA();

                    /* The patient name is removed from the sejour view in case of a print or an export */
                    if (
                        $action != 'list' && strpos(
                            $_act->_ref_object->_view,
                            $_act->_ref_object->_ref_patient->_view
                        ) === 0
                    ) {
                        $_act->_ref_object->_view = substr(
                            $_act->_ref_object->_view,
                            strlen($_act->_ref_object->_ref_patient->_view) + 3
                        );
                    }
                } else {
                    $_act->loadRefSejour();
                    $_act->_ref_object->_ref_sejour->loadNDA();
                }
            }
        }

        return $results;
    }

    /**
     *
     *
     * @param array  $where     The where clauses array
     * @param array  $ljoin     The left joins array
     * @param string $act_table The name of the act table for which to forge the where and left join clauses
     *
     * @return void
     */
    private function setWhereClausesCotations(array &$where, array &$ljoin, string $act_table = 'acte_ngap'): void
    {
        $group   = CGroups::loadCurrent();
        $tag_nda = CSejour::getTagNDA($group->_id);

        $whereGroup   = [];
        $wherePatient = [];
        $whereSejour  = [];
        foreach ($this->object_classes as $class) {
            switch ($class) {
                case 'CConsultation':
                    $sejour_table_alias    = 'consult_sejour';
                    $ljoin['consultation'] = $this->ds->prepare(
                        "{$act_table}.object_id = consultation.consultation_id AND {$act_table}.object_class = ?",
                        $class
                    );
                    $ljoin[]               = "sejour {$sejour_table_alias} 
                    ON {$sejour_table_alias}.sejour_id = consultation.sejour_id";
                    break;
                case 'COperation':
                    $sejour_table_alias  = 'op_sejour';
                    $ljoin['operations'] = $this->ds->prepare(
                        "{$act_table}.object_id = operations.operation_id AND {$act_table}.object_class = ?",
                        $class
                    );
                    $ljoin[]             = "sejour {$sejour_table_alias} 
                    ON {$sejour_table_alias}.sejour_id = operations.sejour_id";
                    break;
                case 'CSejour':
                default:
                    $sejour_table_alias = 'sejour';
                    $ljoin[]            = $this->ds->prepare(
                        "sejour ON {$sejour_table_alias}.sejour_id = {$act_table}.object_id 
                        AND {$act_table}.object_class = ?",
                        $class
                    );
            }

            $whereGroup[] = $this->ds->prepare("{$sejour_table_alias}.group_id = ?", $group->_id);
            if ($this->patient_id) {
                $wherePatient[] = $this->ds->prepare("{$sejour_table_alias}.patient_id = ?", $this->patient_id);
            } elseif ($this->nda) {
                $nda_table_alias = "{$sejour_table_alias}_nda";
                $ljoin[]         = "id_sante400 {$nda_table_alias} 
                ON {$nda_table_alias}.object_id = {$sejour_table_alias}.sejour_id";
                $wherePatient[]  = $this->ds->prepare(
                    "({$nda_table_alias}.object_class = 'CSejour' 
                    AND {$nda_table_alias}.tag = ?1 AND {$nda_table_alias}.id400 = ?2)",
                    $tag_nda,
                    $this->nda
                );
            }

            $whereObjectClass = [];
            if ($this->sejour_type) {
                $whereObjectClass[] = "{$sejour_table_alias}.type "
                    . (is_array($this->sejour_type) ? CSQLDataSource::prepareIn($this->sejour_type)
                        : $this->ds->prepare("= ?", $this->sejour_type));
            }

            if ($this->begin_sejour && $this->end_sejour) {
                $whereObjectClass[] = $this->ds->prepare(
                    "{$sejour_table_alias}.entree BETWEEN ?1 AND ?2 AND {$sejour_table_alias}.sortie BETWEEN ?1 AND ?2",
                    "{$this->begin_sejour} 00:00:00",
                    "{$this->end_sejour} 23:59:59"
                );
            }

            if (count($whereObjectClass)) {
                $whereSejour[] = implode(' AND ', $whereObjectClass);
            }
        }

        if ($this->chir_id) {
            $where["{$act_table}.executant_id"] = $this->ds->prepare(" = ?", $this->chir_id);
        } elseif ($this->function_id) {
            $ljoin['users_mediboard']             = "{$act_table}.executant_id = users_mediboard.user_id";
            $where['users_mediboard.function_id'] = $this->ds->prepare(" = ?", $this->function_id);
        } elseif ($this->speciality) {
            $ljoin['users_mediboard']              = "{$act_table}.executant_id = users_mediboard.user_id";
            $where['users_mediboard.spec_cpam_id'] = $this->ds->prepare(" = ?", $this->speciality);
        }

        $where[] = implode(' OR ', $whereGroup);

        if (count($wherePatient)) {
            $where[] = implode(' OR ', $wherePatient);
        }

        if (count($whereSejour)) {
            $where[] = implode(' OR ', $whereSejour);
        }
    }

    /**
     * Return the order clause for the query that filter the NGAP acts
     *
     * @param string $sort_column The name of the column for sorting the acts
     * @param string $sort_way    The way the acts must be sorted (asc or desc)
     *
     * @return string
     */
    private function getCotationNGAPOrderClause(string $sort_column, string $sort_way): string
    {
        switch ($sort_column) {
            case 'executant_id':
                $order = "executant_name {$sort_way}, acte_ngap.execution DESC, acte_ngap.code ASC";
                break;
            case 'prescripteur_id':
                $order = "prescripteur_name {$sort_way}, acte_ngap.execution DESC, acte_ngap.code ASC";
                break;
            case 'patient_id':
                $order = "patient_name {$sort_way}, acte_ngap.execution DESC, acte_ngap.code ASC";
                break;
            case '_tarif':
                $order = "tarif {$sort_way}, acte_ngap.execution DESC, acte_ngap.code ASC";
                break;
            case 'execution':
                $order = "execution {$sort_way}, executant_name DESC, acte_ngap.code ASC";
                break;
            default:
                $order = "{$sort_column} {$sort_way}, executant_name DESC, acte_ngap.code ASC";
        }

        return $order;
    }

    /**
     * Format the left join clauses and add the select for the patient name for preparing the SQL query for the
     * cotation bilan
     *
     * @param array $ljoin  The array containing the left join clauses
     * @param array $select An array containing the selected data
     *
     * @return void
     */
    private function getCotationPatientLeftJoin(array &$ljoin, array &$select): void
    {
        $patient_tables = [];
        foreach ($this->object_classes as $class) {
            switch ($class) {
                case 'CConsultation':
                    $ljoin[]          = "patients consult_patient 
                    ON consult_sejour.patient_id = consult_patient.patient_id";
                    $patient_tables[] = 'consult_patient';
                    break;
                case 'COperation':
                    $ljoin[]          = "patients op_patient ON op_patient.patient_id = op_sejour.patient_id";
                    $patient_tables[] = 'op_patient';
                    break;
                case 'CSejour':
                default:
                    $ljoin[]          = "patients sejour_patient ON sejour_patient.patient_id = sejour.patient_id";
                    $patient_tables[] = 'sejour_patient';
            }
        }

        $object_classes_count = count($patient_tables);
        $select_patient_name  = '';

        if ($object_classes_count === 1) {
            $select_patient_name = "CONCAT({$patient_tables[0]}.nom, ' ', {$patient_tables[0]}.prenom)";
        } elseif ($object_classes_count === 2) {
            $select_patient_name .= "IFNULL(CONCAT({$patient_tables[0]}.nom, ' ', {$patient_tables[0]}.prenom), "
                . "CONCAT({$patient_tables[1]}.nom, ' ', {$patient_tables[1]}.prenom))";
        } elseif ($object_classes_count === 3) {
            $select_patient_name .= "IFNULL(CONCAT({$patient_tables[0]}.nom, ' ', {$patient_tables[0]}.prenom),"
                . " IFNULL(CONCAT({$patient_tables[1]}.nom, ' ', {$patient_tables[1]}.prenom),"
                . " CONCAT({$patient_tables[2]}.nom, ' ', {$patient_tables[2]}.prenom)))";
        }
        $select_patient_name .= ' AS patient_name';
        $select[]            = $select_patient_name;
    }

    /**
     * Return a CSV of the given cotations NGAP
     *
     * @param array $results The results returned by the getCotationsNGAP function
     *
     * @return CCSVFile
     */
    public function exportCotationsNGAP(array $results): CCSVFile
    {
        $export = new CCSVFile();

        $export->writeLine(
            [
                CAppUI::tr('CPatient'),
                'NDA',
                CAppUI::tr('CActeNGAP-object_id'),
                CAppUI::tr('CActeNGAP-executant_id'),
                CAppUI::tr('CActeNGAP-prescripteur_id'),
                CAppUI::tr('CActeNGAP-execution'),
                CAppUI::tr('CActeNGAP-code'),
                CAppUI::tr('CActeNGAP-quantite'),
                CAppUI::tr('CActeNGAP-coefficient'),
                CAppUI::tr('CActeNGAP-complement'),
                CAppUI::tr('CActeNGAP-montant_base'),
                CAppUI::tr('CActeNGAP-montant_depassement'),
                CAppUI::tr('CActeNGAP-_tarif'),
            ]
        );

        /** @var CActeNGAP $act */
        foreach ($results['acts'] as $act) {
            $export->writeLine(
                [
                    $act->_ref_object->_ref_patient->_view,
                    $act->object_class == 'CSejour' ? $act->_ref_object->_NDA : $act->_ref_object->_ref_sejour->_NDA,
                    $act->_ref_object->_view,
                    $act->_ref_executant->_view,
                    $act->_ref_prescripteur->_view,
                    CMbDT::format($act->execution, CAppUI::conf('datetime')),
                    $act->code,
                    $act->quantite,
                    $act->coefficient,
                    $act->complement,
                    $act->montant_base,
                    $act->montant_depassement,
                    $act->_tarif,
                ]
            );
        }

        return $export;
    }

    /**
     * Return all the acts (CCAM and NGAP) matching the filters
     *
     * @param int    $start       The starting point of the results
     * @param int    $number      The number of act to load
     * @param string $sort_column The name of the column for sorting the acts
     * @param string $sort_way    The way the acts must be sorted (asc or desc)
     *
     * @return array
     * @throws Exception
     */
    public function getBilanCotation(
        int $start = 0,
        int $number = 30,
        string $sort_column = 'date',
        string $sort_way = 'DESC'
    ): array {
        $results = [
            'total'  => 0,
            'start'  => $start,
            'number' => $number,
            'acts'   => [],
        ];

        /* Forging the Query for the NGAP acts */
        $whereNGAP = [];
        if ($this->begin_date && $this->end_date) {
            $whereNGAP['acte_ngap.execution'] = $this->ds->prepare(
                ' BETWEEN ?1 AND ?2',
                "{$this->begin_date} 00:00:00",
                "{$this->end_date} 23:59:59"
            );
        }

        $ljoinNGAP  = ['users' => 'users.user_id = acte_ngap.executant_id'];
        $selectNGAP = [
            '`acte_ngap`.`acte_ngap_id` AS acte_ngap_id',
            'NULL AS acte_ccam_id',
            '`acte_ngap`.`execution` AS execution',
            "CONCAT_WS(' ', `users`.`user_last_name`, `users`.`user_first_name`) AS executant_name",
        ];
        $this->setWhereClausesCotations($whereNGAP, $ljoinNGAP, 'acte_ngap');
        $this->getCotationPatientLeftJoin($ljoinNGAP, $selectNGAP);
        $queryNGAP = new CRequest();
        $queryNGAP->addSelect($selectNGAP);
        $queryNGAP->addTable('acte_ngap');
        $queryNGAP->addWhere($whereNGAP);
        $queryNGAP->addLJoin($ljoinNGAP);

        /* Forging the Query for the CCAM acts */
        $whereCCAM = [];
        if ($this->begin_date && $this->end_date) {
            $whereNGAP['acte_ccam.execution'] = $this->ds->prepare(
                ' BETWEEN ?1 AND ?2',
                "{$this->begin_date} 00:00:00",
                "{$this->end_date} 23:59:59"
            );
        }

        $ljoinCCAM  = ['users' => 'users.user_id = acte_ccam.executant_id'];
        $selectCCAM = [
            'NULL AS acte_ngap_id',
            '`acte_ccam`.`acte_id` AS acte_ccam_id',
            '`acte_ccam`.`execution` AS execution',
            "CONCAT_WS(' ', `users`.`user_last_name`, `users`.`user_first_name`) AS executant_name",
        ];
        $this->setWhereClausesCotations($whereCCAM, $ljoinCCAM, 'acte_ccam');
        $this->getCotationPatientLeftJoin($ljoinCCAM, $selectCCAM);
        $queryCCAM = new CRequest();
        $queryCCAM->addSelect($selectCCAM);
        $queryCCAM->addTable('acte_ccam');
        $queryCCAM->addWhere($whereCCAM);
        $queryCCAM->addLJoin($ljoinCCAM);

        $query = '(' . $queryNGAP->makeSelect() . ') UNION ALL (' . $queryCCAM->makeSelect() . ")";

        $ds   = CSQLDataSource::get('std');
        $rows = $ds->loadList($query);

        if (is_array($rows) && count($rows)) {
            $results['total'] = count($rows);
            /* Sort the results according to the given sort parameters */
            switch ($sort_column) {
                case 'patient_id':
                    CMbArray::pluckSort($rows, $sort_way === 'DESC' ? SORT_DESC : SORT_ASC, 'patient_name');
                    break;
                case 'executant_id':
                    CMbArray::pluckSort($rows, $sort_way === 'DESC' ? SORT_DESC : SORT_ASC, 'executant_name');
                    break;
                case 'execution':
                default:
                    CMbArray::pluckSort($rows, $sort_way === 'DESC' ? SORT_DESC : SORT_ASC, 'execution');
            }

            /* Limiting the results */
            if ($start !== null && $number !== null) {
                $rows = array_slice($rows, $start, $number);
            }

            $act_ngap_ids = CMbArray::pluck($rows, 'acte_ngap_id');
            $act_ccam_ids = CMbArray::pluck($rows, 'acte_ccam_id');
            $act_ngap     = new CActeNGAP();
            $act_ccam     = new CActeCCAM();
            $acts_ngap    = $act_ngap->loadAll($act_ngap_ids);
            $acts_ccam    = $act_ccam->loadAll($act_ccam_ids);

            $sejours = [];
            if (in_array('CConsultation', $this->object_classes)) {
                $consultations = CMbObject::massLoadFwdRef($acts_ngap, 'object_id', 'CConsultation');
                $consultations = array_merge(
                    $consultations,
                    CMbObject::massLoadFwdRef($acts_ccam, 'object_id', 'CConsultation')
                );
                $sejours       = CMbObject::massLoadFwdRef($consultations, 'sejour_id');
            }

            if (in_array('COperation', $this->object_classes)) {
                $operations = CMbObject::massLoadFwdRef($acts_ngap, 'object_id', 'COperation');
                $operations = array_merge(
                    $operations,
                    CMbObject::massLoadFwdRef($acts_ccam, 'object_id', 'COperation')
                );
                $sejours    = array_merge($sejours, CMbObject::massLoadFwdRef($operations, 'sejour_id'));
            }

            if (in_array('CSejour', $this->object_classes)) {
                $sejours = array_merge($sejours, CMbObject::massLoadFwdRef($acts_ngap, 'object_id', 'CSejour'));
                $sejours = array_merge($sejours, CMbObject::massLoadFwdRef($acts_ccam, 'object_id', 'CSejour'));
            }

            $users = CMbObject::massLoadFwdRef($acts_ngap, 'executant_id');
            $users = array_merge($users, CMbObject::massLoadFwdRef($acts_ngap, 'prescripteur_id'));
            $users = array_merge($users, CMbObject::massLoadFwdRef($acts_ccam, 'executant_id'));
            CMbObject::massLoadFwdRef($users, 'function_id');

            CMbObject::massLoadFwdRef($sejours, 'patient_id');
            CSejour::massLoadNDA($sejours);

            foreach ($rows as $row) {
                if ($row['acte_ngap_id']) {
                    $act = new CActeNGAP();
                    $act->load($row['acte_ngap_id']);
                    $act->loadRefPrescripteur()->loadRefFunction();
                } else {
                    $act = new CActeCCAM();
                    $act->load($row['acte_ccam_id']);
                }

                $act->loadTargetObject();
                $act->loadRefSejour();
                $act->_ref_sejour->loadNDA();
                $act->loadRefPatient();
                $act->loadRefExecutant()->loadRefFunction();
                $results['acts'][] = $act;
            }
        }

        return $results;
    }

    /**
     * Return a CSV of the given cotations bilan
     *
     * @param array $results The results returned by the getBilanCotation function
     *
     * @return CCSVFile
     */
    public function exportBilanCotation(array $results): CCSVFile
    {
        $export = new CCSVFile();

        $export->writeLine(
            [
                CAppUI::tr('CPatient'),
                'NDA',
                CAppUI::tr('CActeNGAP-object_id'),
                CAppUI::tr('CActeNGAP-executant_id'),
                CAppUI::tr('CActeNGAP-execution'),
                CAppUI::tr('CActeNGAP-code'),
                CAppUI::tr('CActe-majoration'),
                CAppUI::tr('CActeNGAP-montant_base'),
                CAppUI::tr('CActeNGAP-montant_depassement'),
                CAppUI::tr('CActeNGAP-_tarif'),
            ]
        );

        /** @var CActeNGAP $act */
        foreach ($results['acts'] as $act) {
            /* The patient name is removed from the sejour view in case of a print or an export */
            if (strpos($act->_ref_object->_view, $act->_ref_patient->_view) === 0) {
                $act->_ref_object->_view = substr(
                    $act->_ref_object->_view,
                    strlen($act->_ref_object->_ref_patient->_view) + 3
                );
            }

            $line = [
                $act->_ref_patient->_view,
                $act->_ref_sejour->_NDA,
                $act->_ref_object->_view,
                $act->_ref_executant->_view,
                CMbDT::format($act->execution, CAppUI::conf('datetime')),
            ];

            if ($act instanceof CActeCCAM) {
                $line[] = "{$act->code_acte} {$act->code_activite} - {$act->code_phase} ("
                    . CAppUI::tr("CActeCCAM.code_association.{$act->code_association}") . ')';
                $line[] = $act->modificateurs ? implode(' ', $act->_modificateurs) : '';
            } else {
                $line[] = ($act->quantite ? "{$act->quantite} x " : '') . $act->code
                    . ($act->coefficient !== 1 ? " {$act->coefficient}" : '');
                $line[] = $act->complement ? CAppUI::tr("CActeNGAP.complement.{$act->complement}") : '';
            }

            $line[] = $act->montant_base;
            $line[] = $act->montant_depassement;
            $line[] = $act->_tarif;

            $export->writeLine($line);
        }

        return $export;
    }

    /**
     * Return the periods available for the selected dates
     *
     * @param bool $display If true, add a period for the total, and add the dates for each period in the array
     *
     * @return array
     */
    public function getPeriods(bool $display = true): array
    {
        $days_from_end_date = CMbDT::daysRelative($this->end_date, CMbDT::date());
        $days               = CMbDT::daysRelative($this->begin_date, $this->end_date) + $days_from_end_date;
        $periods            = [];

        foreach (self::$periods as $_days => $_period) {
            $_period_days = explode('_', $_period);
            if ($days >= $_period_days[0] && (203 === $_days || $days_from_end_date <= $_period_days[1])) {
                if ($display) {
                    $periods[$_period] = $this->getDatesForPeriod($_period);
                } else {
                    $periods[] = $_period;
                }
            }
        }

        if ($display) {
            $periods['total'] = ['begin' => $this->begin_date, 'end' => $this->end_date];
        }

        return $periods;
    }

    /**
     * Load the objects of the chosen classes, in the chosen time interval
     *
     * @param string $period If set, the objects will be selected within the given period
     *
     * @return array
     */
    protected function loadObjects(string $period = null): array
    {
        $objects = [];

        foreach ($this->object_classes as $_class) {
            switch ($_class) {
                case 'CConsultation':
                    $objects[$_class] = $this->loadConsultations($period);
                    break;
                case 'COperation':
                    $objects[$_class] = $this->loadOperations($period);
                    break;
                case 'CSejour':
                    $objects[$_class] = $this->loadSejours($period);
                    break;
                case 'CSejour-seance':
                    $objects[$_class] = $this->loadSejours($period, 'seances');
                    break;
                default:
            }
        }

        return $objects;
    }

    /**
     * Load the operations for the selected chirs, in the selected time interval
     *
     * @param string $period If set, the operations will be selected within the given period
     *
     * @return COperation[]
     */
    protected function loadOperations(string $period = null): array
    {
        $dates    = $this->getDatesForPeriod($period);
        $in_chirs = CSQLDataSource::prepareIn($this->_chir_ids);

        $where = [
            "`operations`.`date` BETWEEN '" . $dates['begin'] . "' AND '" . $dates['end'] . "'",
            "`operations`.`annulee` = '0'",
        ];

        if (!empty($this->_chir_ids)) {
            $where[] = "`operations`.`chir_id` $in_chirs OR `operations`.`chir_2_id` $in_chirs OR " .
                "`operations`.`chir_3_id` $in_chirs OR `operations`.`chir_4_id` $in_chirs OR " .
                "(`operations`.`anesth_id` IS NULL AND `plagesop`.`anesth_id` $in_chirs) 
                OR `operations`.`anesth_id` $in_chirs";
        }

        if (!$this->objects_without_codes && !$this->display_all) {
            $where[] = "LENGTH(`operations`.`codes_ccam`) > 0";
        }

        if ($this->libelle) {
            $where[] = "`operations`.`libelle` LIKE '%$this->libelle%'";
        }

        if (count($this->ccam_codes)) {
            $whereOr = [];

            foreach ($this->ccam_codes as $_code) {
                if ($_code != '') {
                    $whereOr[] = "`operations`.`codes_ccam` LIKE '%$_code%'";
                }
            }

            $where[] = implode(' OR ', $whereOr);
        }

        $ljoin = [
            'plagesop'        => "`operations`.`plageop_id` = `plagesop`.`plageop_id`",
            'sallesbloc'      => 'sallesbloc.salle_id = `operations`.salle_id',
            'bloc_operatoire' => 'bloc_operatoire.bloc_operatoire_id = sallesbloc.bloc_id',
        ];

        if (empty($this->_chir_ids)) {
            /* Check on the group id */
            $group                             = CGroups::loadCurrent();
            $where['bloc_operatoire.group_id'] = "= $group->_id";
        }

        if ($this->sejour_type && $this->sejour_type != 'all') {
            $ljoin['sejour'] = "`operations`.`sejour_id` = `sejour`.`sejour_id`";
            $where[]         = "`sejour`.`type` = '$this->sejour_type'";
        }

        if ($this->nda) {
            $tag = CSejour::getTagNDA();
            if (!array_key_exists('sejour', $ljoin)) {
                $ljoin['sejour'] = "`operations`.`sejour_id` = `sejour`.`sejour_id`";
            }

            $ljoin['id_sante400'] = "`id_sante400`.`object_id` = `sejour`.`sejour_id`";

            $where['id_sante400.object_class'] = " = 'CSejour'";
            $where['id_sante400.tag']          = " = '$tag'";
            $where['id_sante400.id400']        = " = '$this->nda'";
        }

        if ($this->patient_id) {
            if (!array_key_exists('sejour', $ljoin)) {
                $ljoin['sejour'] = "`operations`.`sejour_id` = `sejour`.`sejour_id`";
            }

            $where['sejour.patient_id'] = " = $this->patient_id";
        }

        switch ($this->codage_lock_status) {
            case 'unlocked':
                $where[] = "NOT EXISTS (
                      SELECT * FROM `codage_ccam` WHERE `codage_ccam`.`codable_id` = `operations`.`operation_id` AND
                      `codage_ccam`.`codable_class` = 'COperation' AND `codage_ccam`.`locked` = '1'
                    )";
                $where[] = "EXISTS (
                      SELECT * FROM `codage_ccam` WHERE `codage_ccam`.`codable_id` = `operations`.`operation_id` AND
                      `codage_ccam`.`codable_class` = 'COperation' AND `codage_ccam`.`locked` = '0'
                    ) OR NOT EXISTS (
                      SELECT * FROM `codage_ccam` WHERE `codage_ccam`.`codable_id` = `operations`.`operation_id` AND
                      `codage_ccam`.`codable_class` = 'COperation'
                    )";
                break;
            case 'locked_by_chir':
                $where[] = "EXISTS (
                      SELECT * FROM `codage_ccam`
                      WHERE `codage_ccam`.`codable_id` = `operations`.`operation_id` 
                      AND `codage_ccam`.`codable_class` = 'COperation'
                      AND `codage_ccam`.`locked` = '1' AND `codage_ccam`.`activite_anesth` = '0'
                      AND `codage_ccam`.`praticien_id` = `operations`.`chir_id`
                    )";
                $where[] = "NOT EXISTS (
                      SELECT * FROM `codage_ccam`
                      WHERE `codage_ccam`.`codable_id` = `operations`.`operation_id` 
                      AND `codage_ccam`.`codable_class` = 'COperation'
                      AND `codage_ccam`.`locked` = '1' AND `codage_ccam`.`activite_anesth` = '1'
                    )";
                break;
            case 'locked':
                $where[] = "NOT EXISTS (
                      SELECT * FROM `codage_ccam` WHERE `codage_ccam`.`codable_id` = `operations`.`operation_id` AND
                      `codage_ccam`.`codable_class` = 'COperation' AND `codage_ccam`.`locked` = '0'
                    )";
                $where[] = "EXISTS (
                      SELECT * FROM `codage_ccam` WHERE `codage_ccam`.`codable_id` = `operations`.`operation_id` AND
                      `codage_ccam`.`codable_class` = 'COperation' AND `codage_ccam`.`locked` = '1'
                    )";
                break;
            default:
        }
        if ($this->excess_fee_chir_status) {
            $where[] = "operations.depassement > 0 AND operations.reglement_dh_chir = '$this->excess_fee_chir_status'";
        } elseif ($this->excess_fee_anesth_status) {
            $where[] = "operations.depassement_anesth > 0 
            AND operations.reglement_dh_anesth = '$this->excess_fee_anesth_status'";
        }
        $operation = new COperation();

        return $operation->loadList($where, null, null, '`operations`.`operation_id`', $ljoin);
    }

    /**
     * Load the consultations for the selected chirs, in the selected time interval
     *
     * @param string $period If set, the operations will be selected within the given period
     *
     * @return CConsultation[]
     */
    protected function loadConsultations(string $period = null): array
    {
        $dates    = $this->getDatesForPeriod($period);
        $in_chirs = CSQLDataSource::prepareIn($this->_chir_ids);

        $where = [
            "`plageconsult`.`date` BETWEEN '" . $dates['begin'] . "' AND '" . $dates['end'] . "'",
            "`consultation`.`annule` = '0'",
            "`consultation`.`patient_id` IS NOT NULL",
        ];

        if (!empty($this->_chir_ids)) {
            $where[] = "`plageconsult`.`chir_id` $in_chirs";
        }

        if ($this->libelle) {
            $where[] = "`consultation`.`motif` LIKE '%$this->libelle%'";
        }

        if (count($this->ccam_codes)) {
            $whereOr = [];

            foreach ($this->ccam_codes as $_code) {
                $whereOr[] = "`consultation`.`codes_ccam` LIKE '%$_code%'";
            }

            $where[] = implode(' OR ', $whereOr);
        }

        if ($this->patient_id) {
            $where['consultation.patient_id'] = " = $this->patient_id";
        }

        switch ($this->codage_lock_status) {
            case 'unlocked':
                $where[] = "NOT EXISTS (
                      SELECT * FROM `codage_ccam` 
                      WHERE `codage_ccam`.`codable_id` = `consultation`.`consultation_id` 
                      AND `codage_ccam`.`codable_class` = 'CConsultation' 
                      AND `codage_ccam`.`locked` = '1'
                    )";
                $where[] = "EXISTS (
                      SELECT * FROM `codage_ccam` 
                      WHERE `codage_ccam`.`codable_id` = `consultation`.`consultation_id` 
                      AND `codage_ccam`.`codable_class` = 'CConsultation' AND `codage_ccam`.`locked` = '0'
                    )";
                break;
            case 'locked_by_chir':
                $where[] = "EXISTS (
                      SELECT * FROM `codage_ccam`
                      WHERE `codage_ccam`.`codable_id` = `consultation`.`consultation_id`
                      AND `codage_ccam`.`codable_class` = 'CConsultation' AND `codage_ccam`.`locked` = '1'
                      AND `codage_ccam`.`activite_anesth` = '0' 
                      AND `codage_ccam`.`praticien_id` = `plageconsult`.`chir_id`
                    )";
                $where[] = "NOT EXISTS (
                      SELECT * FROM `codage_ccam`
                      WHERE `codage_ccam`.`codable_id` = `consultation`.`consultation_id`
                      AND `codage_ccam`.`codable_class` = 'CConsultation'
                      AND `codage_ccam`.`locked` = '1' AND `codage_ccam`.`activite_anesth` = '1'
                    )";
                break;
            case 'locked':
                $where[] = "NOT EXISTS (
                      SELECT * FROM `codage_ccam` 
                      WHERE `codage_ccam`.`codable_id` = `consultation`.`consultation_id` 
                      AND `codage_ccam`.`codable_class` = 'CConsultation' AND `codage_ccam`.`locked` = '0'
                    )";
                $where[] = "EXISTS (
                      SELECT * FROM `codage_ccam` 
                      WHERE `codage_ccam`.`codable_id` = `consultation`.`consultation_id` 
                      AND `codage_ccam`.`codable_class` = 'CConsultation' AND `codage_ccam`.`locked` = '1'
                    )";
                break;
            default:
        }

        if (!$this->display_all) {
            $where[] = "NOT EXISTS (
          SELECT * FROM `acte_ngap` WHERE `acte_ngap`.`object_class` = 'CConsultation'
          AND `acte_ngap`.`object_id` = `consultation`.`consultation_id` AND `acte_ngap`.`executant_id` $in_chirs
        )";
            $where[] = "NOT EXISTS (
          SELECT * FROM `acte_ccam` WHERE `acte_ccam`.`object_class` = 'CConsultation'
          AND `acte_ccam`.`object_id` = `consultation`.`consultation_id` AND `acte_ccam`.`executant_id` $in_chirs
        )";
        }

        $ljoin = [
            'plageconsult' => "`consultation`.`plageconsult_id` = `plageconsult`.`plageconsult_id`",
        ];

        $consultation = new CConsultation();

        return $consultation->loadList($where, null, null, '`consultation`.`consultation_id`', $ljoin);
    }

    /**
     * Load the sejours for the selected chirs, in the selected time interval
     *
     * @param string $period If set, the operations will be selected within the given period
     * @param string $type   The type of sejour
     *
     * @return CSejour[]
     */
    protected function loadSejours(string $period = null, string $type = null): array
    {
        $dates    = $this->getDatesForPeriod($period, true);
        $in_chirs = CSQLDataSource::prepareIn($this->_chir_ids);

        $where = [
            "sejour.entree BETWEEN '{$dates['begin']}' AND '{$dates['end']}'"
            . " OR sejour.sortie BETWEEN '{$dates['begin']}' AND '{$dates['end']}'",
            "sejour.annule = '0'",
            'sejour.patient_id IS NOT NULL',
        ];

        if (!empty($this->_chir_ids)) {
            $where[] = "sejour.`praticien_id` $in_chirs";
        }

        if ($this->libelle) {
            $where[] = "`sejour`.`libelle` LIKE '%$this->libelle%'";
        }

        if ($type) {
            $where[] = "`sejour`.`type` = '{$type}'";
        }

        if (!$this->display_all) {
            $where[] = "NOT EXISTS (
          SELECT * FROM `acte_ngap` WHERE `acte_ngap`.`object_class` = 'CSejour'
          AND `acte_ngap`.`object_id` = `sejour`.`sejour_id` AND `acte_ngap`.`executant_id` $in_chirs
        )";
            $where[] = "NOT EXISTS (
          SELECT * FROM `acte_ccam` WHERE `acte_ccam`.`object_class` = 'CSejour'
          AND `acte_ccam`.`object_id` = `sejour`.`sejour_id` AND `acte_ccam`.`executant_id` $in_chirs
        )";
        }

        if (count($this->ccam_codes)) {
            $whereOr = [];

            foreach ($this->ccam_codes as $_code) {
                $whereOr[] = "`sejour`.`codes_ccam` LIKE '%$_code%'";
            }

            $where[] = implode(' OR ', $whereOr);
        }

        if ($this->patient_id) {
            $where['sejour.patient_id'] = " = $this->patient_id";
        }

        switch ($this->codage_lock_status) {
            case 'unlocked':
                $where[] = "NOT EXISTS (
                      SELECT * FROM `codage_ccam` WHERE `codage_ccam`.`codable_id` = `sejour`.`sejour_id` AND
                      `codage_ccam`.`codable_class` = 'CSejour' AND `codage_ccam`.`locked` = '1'
                    )";
                $where[] = "EXISTS (
                      SELECT * FROM `codage_ccam` WHERE `codage_ccam`.`codable_id` = `sejour`.`sejour_id` AND
                      `codage_ccam`.`codable_class` = 'CSejour' AND `codage_ccam`.`locked` = '0'
                    )";
                break;
            case 'locked_by_chir':
                $where[] = "EXISTS (
                      SELECT * FROM `codage_ccam`
                      WHERE `codage_ccam`.`codable_id` = `sejour`.`sejour_id`
                      AND `codage_ccam`.`codable_class` = 'CSejour' AND `codage_ccam`.`locked` = '1'
                      AND `codage_ccam`.`activite_anesth` = '0' 
                      AND `codage_ccam`.`praticien_id` = `sejour`.`praticien_id`
                    )";
                $where[] = "NOT EXISTS (
                      SELECT * FROM `codage_ccam`
                      WHERE `codage_ccam`.`codable_id` = `sejour`.`sejour_id`
                      AND `codage_ccam`.`codable_class` = 'CSejour'
                      AND `codage_ccam`.`locked` = '1' AND `codage_ccam`.`activite_anesth` = '1'
                    )";
                break;
            case 'locked':
                $where[] = "NOT EXISTS (
                      SELECT * FROM `codage_ccam` WHERE `codage_ccam`.`codable_id` = `sejour`.`sejour_id` AND
                      `codage_ccam`.`codable_class` = 'CSejour' AND `codage_ccam`.`locked` = '0'
                    )";
                $where[] = "EXISTS (
                      SELECT * FROM `codage_ccam` WHERE `codage_ccam`.`codable_id` = `sejour`.`sejour_id` AND
                      `codage_ccam`.`codable_class` = 'CSejour' AND `codage_ccam`.`locked` = '1'
                    )";
                break;
            default:
        }

        $sejour = new CSejour();

        return $sejour->loadList($where, null, null, 'sejour.sejour_id');
    }

    /**
     * Return the begining and the ending dates of the selected period of times
     *
     * @param string $period   The selected period of time
     * @param bool   $datetime If true, datetime will be returned
     *
     * @return array
     */
    protected function getDatesForPeriod(?string $period, bool $datetime = false): array
    {
        switch ($period) {
            case '1_2':
                $begin_date = CMbDT::date('-2 days');
                $end_date   = $this->end_date;
                break;
            case '3_7':
                $begin_date = CMbDT::date('-7 days');
                if ($this->begin_date > $begin_date) {
                    $begin_date = $this->begin_date;
                }
                $end_date = CMbDT::date('-3 days');
                break;
            case '8_30':
                $begin_date = CMbDT::date('-30 days');
                if ($this->begin_date > $begin_date) {
                    $begin_date = $this->begin_date;
                }
                $end_date = CMbDT::date('-8 days');
                break;
            case '31_60':
                $begin_date = CMbDT::date('-60 days');
                if ($this->begin_date > $begin_date) {
                    $begin_date = $this->begin_date;
                }
                $end_date = CMbDT::date('-31 days');
                break;
            case '61_120':
                $begin_date = CMbDT::date('-120 days');
                if ($this->begin_date > $begin_date) {
                    $begin_date = $this->begin_date;
                }
                $end_date = CMbDT::date('-61 days');
                break;
            case '121_202':
                $begin_date = CMbDT::date('-202 days');
                if ($this->begin_date > $begin_date) {
                    $begin_date = $this->begin_date;
                }
                $end_date = CMbDT::date('-121 days');
                break;
            case '203':
                $begin_date = $this->begin_date;
                $end_date   = CMbDT::date('-203 days');
                break;
            default:
                $begin_date = $this->begin_date;
                $end_date   = $this->end_date;
        }

        if ($datetime) {
            $begin_date .= ' 00:00:00';
            $end_date   .= ' 23:59:59';
        }

        return ['begin' => $begin_date, 'end' => $end_date];
    }
}
