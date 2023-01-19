<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\MonitoringPatient;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbMath;
use Ox\Core\CMbObject;
use Ox\Core\CModelObject;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Medicament\CMedicamentProduit;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\Mpm\CPrescriptionLineMix;
use Ox\Mediboard\Mpm\CPrescriptionLineMixItem;
use Ox\Mediboard\ObservationResult\CObservationResult;
use Ox\Mediboard\ObservationResult\CObservationResultSet;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanSoins\CAdministration;
use Ox\Mediboard\PlanSoins\CPlanificationSysteme;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLine;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;

/**
 * Description
 */
class SupervisionGraph extends CMbObject
{

    public static $limited_view_datas = 0;

    /**
     * Get observation results for this object
     *
     * @param CMbObject $object       Reference object
     * @param string    $datetime_min Datetime min
     * @param string    $datetime_max Datetime max
     * @param bool      $print        Imprimer
     *
     * @return array|CObservationResultSet[]
     * @throws Exception
     */
    static function getResultsFor(CMbObject $object, $datetime_min = null, $datetime_max = null, $print = false)
    {
        // user logs
        $request = new CRequest();
        $request->addTable("observation_result");
        $request->addSelect("*");
        $request->addLJoin(
            [
                "observation_result_set" => "observation_result_set.observation_result_set_id = observation_result.observation_result_set_id",
                "user_log"               => "observation_result_set.observation_result_set_id = user_log.object_id AND
                                     user_log.object_class = 'CObservationResultSet' AND user_log.type = 'create'",
                "users"                  => "users.user_id = user_log.user_id",
            ]
        );
        $request->addWhere(
            [
                "observation_result_set.context_class" => "= '$object->_class'",
                "observation_result_set.context_id"    => "= '$object->_id'",
                //"user_log.user_log_id"                 => "IS NOT NULL"
            ]
        );

        if ((!CAppUI::pref('show_all_datas_surveillance_timeline') && $datetime_min && $datetime_max) || $print) {
            $request->addWhere(
                [
                    "observation_result_set.datetime" => "BETWEEN '$datetime_min' AND '$datetime_max'",
                ]
            );
        }

        $request->addOrder("observation_result_set.datetime");
        $request->addOrder("observation_result.observation_result_id");

        $results = $object->_spec->ds->loadList($request->makeSelect());

        // user actions
        $request = new CRequest();
        $request->addTable("observation_result");
        $request->addSelect("*");
        $request->addLJoin(
            [
                "observation_result_set" => "observation_result_set.observation_result_set_id = observation_result.observation_result_set_id",
                "object_class"           => "object_class.object_class = 'CObservationResultSet'",
                "user_action"            => "observation_result_set.observation_result_set_id = user_action.object_id AND
                                 user_action.object_class_id = object_class.object_class_id AND user_action.type = 'create'",
                "users"                  => "users.user_id = user_action.user_id",
            ]
        );
        $request->addWhere(
            [
                "observation_result_set.context_class" => "= '$object->_class'",
                "observation_result_set.context_id"    => "= '$object->_id'",
                //"user_action.user_action_id"           => "IS NOT NULL"
            ]
        );
        if ((!CAppUI::pref('show_all_datas_surveillance_timeline') && $datetime_min && $datetime_max) || $print) {
            $request->addWhere(
                [
                    "observation_result_set.datetime" => "BETWEEN '$datetime_min' AND '$datetime_max'",
                ]
            );
        }
        $request->addOrder("observation_result_set.datetime");
        $request->addOrder("observation_result.observation_result_id");
        $results_action = $object->_spec->ds->loadList($request->makeSelect());

        $times = [];
        $data  = [];

        foreach ($results as $_result) {
            $_time         = CMbDT::toTimestamp($_result["datetime"]);
            $times[$_time] = $_result["datetime"];

            $result = CObservationResult::findOrFail($_result["observation_result_id"]);
            $result->loadUniqueValue();

            $unit_id = $result->_unit_id ?: "none";

            $label = null;
            if ($_result["label_id"]) {
                $label_obj = new CSupervisionGraphAxisValueLabel();
                $label_obj->load($_result["label_id"]);
                $label = $label_obj->title;
            }

            $float_value = $result->_value;
            $float_value = CMbFieldSpec::checkNumeric($float_value, false);

            if ($_result["user_first_name"] && $_result["user_last_name"]) {
                $_user_name = $_result["user_first_name"] . " " . $_result["user_last_name"];
                $_user_id   = $_result["user_id"];
            } else {
                foreach ($results_action as $_result_action) {
                    if ($_result["observation_result_set_id"] != $_result_action["observation_result_set_id"]) {
                        continue;
                    }

                    $_user_name = $_result_action["user_first_name"] . " " . $_result_action["user_last_name"];
                    $_user_id   = $_result_action["user_id"];
                }
            }
            $data[$result->_value_type_id][$unit_id][] = [
                0           => $_time,
                1           => $float_value,
                "ts"        => $_time,
                "value"     => $result->_value,
                "datetime"  => $_result["datetime"],
                "file_id"   => $_result["file_id"],
                "set_id"    => $_result["observation_result_set_id"],
                "result_id" => $_result["observation_result_id"],
                "label_id"  => $_result["label_id"],
                "label"     => "$label",
                "user_id"   => $_user_id,
                "user"      => $_user_name,
            ];
        }

        return [
            $data,
            $times,
        ];
    }

    /**
     * Get minimal timing value
     *
     * @param COperation $operation Intervention
     * @param string[]   $timings   Timing fields
     * @param null       $default   Default value
     * @param string     $type      Graph Type (perop, sspi, preop)
     * @param bool       $print     Print
     *
     * @return string|null
     */
    static function getMinTiming(COperation $operation, $timings, $default = null, $type = 'perop', $print = false)
    {
        $timing = null;

        foreach ($timings as $_field) {
            $_timing = $operation->{$_field};

            if ($_timing) {
                if ($timing) {
                    $timing = min($_timing, $timing);
                } else {
                    $timing = $_timing;
                }
            }
        }

        if ($operation->sortie_salle && $type == 'perop' && !$print) {
            $timing = CMbDT::dateTime("-1 hour", $operation->sortie_salle);
        }

        if (!$operation->_datetime && $type != 'preop') {
            $timing = null;
        }

        if ($timing === null) {
            return $default;
        }

        return $timing;
    }

    /**
     * Get maximal timing value
     *
     * @param COperation $operation Intervention
     * @param string[]   $timings   Timing fields
     * @param null       $default   Default value
     *
     * @return string|null
     */
    static function getMaxTiming(COperation $operation, $timings, $default = null)
    {
        $timing = null;

        foreach ($timings as $_field) {
            $_timing = $operation->{$_field};

            if ($_timing) {
                if ($timing) {
                    $timing = max($_timing, $timing);
                } else {
                    $timing = $_timing;
                }
            }
        }

        if ($timing === null) {
            return $default;
        }

        return $timing;
    }

    /**
     * Donne les heures limites d'une intervention
     *
     * @param COperation $interv Reference interv
     * @param string     $type   Type de graphique
     * @param bool       $print  Imprimer
     *
     * @return array
     */
    static function getLimitTimes(COperation $interv, $type = "perop", $print = false)
    {
        if (!$interv->_id) {
            return [0, 0, null, null];
        }

        $round_minutes = 60;
        $step_time     = 60000;

        $sejour = $interv->loadRefSejour();

        // Cas du partogramme
        if ($sejour->grossesse_id) {
            $grossesse = $sejour->loadRefGrossesse();

            if ($type === "sspi") {
                // Début
                $time_debut_op_iso = CValue::first(
                    $grossesse->datetime_debut_surv_post_partum,
                    $grossesse->datetime_accouchement,
                    CMbDT::dateTime()
                );

                // Fin
                if ($grossesse->datetime_fin_surv_post_partum) {
                    $time_fin_op_iso = $grossesse->datetime_fin_surv_post_partum;
                } else {
                    $time_fin_op_iso = !$print ? CMbDT::dateTime() : CMbDT::dateTime("+6 HOUR", $time_debut_op_iso);
                }
            } else {
                // Début
                if ($grossesse->datetime_debut_travail) {
                    $time_debut_op_iso = $grossesse->datetime_debut_travail;
                } else {
                    $min_fields        = [
                        "entree_salle",
                        "induction_debut",
                        "debut_op",
                        "_datetime_best",
                    ];
                    $time_debut_op_iso = self::getMinTiming($interv, $min_fields, CMbDT::dateTime(), $type);
                }

                // Fin
                if ($grossesse->datetime_accouchement) {
                    $time_fin_op_iso = !$print ? $grossesse->datetime_accouchement : CMbDT::dateTime(
                        "+6 HOUR",
                        $grossesse->datetime_accouchement
                    );
                } else {
                    $time_fin_op_iso = !$print ? CMbDT::dateTime() : CMbDT::dateTime("+6 HOUR", $time_debut_op_iso);
                }
            }
        } // Cas d'une intervention standard
        else {
            if ($type === "sspi") {
                // Début
                $time_debut_op_iso = $print ? $interv->entree_reveil : CMbDT::dateTime(
                    "-30 minute",
                    $interv->entree_reveil
                );

                // Fin
                $time_max = CValue::first(
                    $interv->sortie_reveil_reel,
                    $interv->sortie_reveil_possible,
                    CMbDT::dateTime("+1 HOUR", $time_debut_op_iso)
                );

                $time_fin_op_iso = $time_max;

                if ($print) {
                    $step_time = 5000;
                }
            } elseif ($type === "preop") {
                // Début
                $min_fields        = [
                    "entree_bloc",
                    "debut_prepa_preop",
                ];
                $time_debut_op_iso = self::getMinTiming($interv, $min_fields, CMbDT::dateTime("- 1 HOUR"), $type);

                // Fin
                $max_fields      = [
                    "fin_prepa_preop",
                    "entree_salle",
                ];
                $time_fin_op_iso = self::getMaxTiming(
                    $interv,
                    $max_fields,
                    CMbDT::dateTime("+ 1 HOUR", $time_debut_op_iso)
                );

                if ($print) {
                    $step_time = 5000;
                }
            } // Cas d'une interv normale (type=perop)
            else {
                $interv->updateDatetimes();

                // Début
                $min_fields        = [
                    "entree_salle",
                    "debut_op",
                ];
                $time_debut_op_iso = self::getMinTiming(
                    $interv,
                    $min_fields,
                    CMbDT::dateTime("- 1 HOUR"),
                    $type,
                    $print
                );

                // Fin
                $max_fields      = [
                    "sortie_salle",
                    "fin_op",
                    "retrait_garrot",
                ];
                $time_fin_op_iso = self::getMaxTiming(
                    $interv,
                    $max_fields,
                    CMbDT::dateTime("+ 1 HOUR", $time_debut_op_iso)
                );

                if ($print) {
                    $step_time = 5000;
                }
            }
        }

        $round = $round_minutes * $step_time; // FIXME

        if ($print) {
            $time_fin_op_iso = CMbDT::dateTime("+30 MINUTES", $time_fin_op_iso);
        }

        $timestamp_min = floor(CMbDT::toTimestamp($time_debut_op_iso) / $round) * $round;
        $timestamp_max = ceil(CMbDT::toTimestamp($time_fin_op_iso) / $round) * $round;

        return [
            $timestamp_min,
            $timestamp_max,
            $time_debut_op_iso,
            $time_fin_op_iso,
        ];
    }

    /**
     * Chargement des graphiques d'intervention
     *
     * @param COperation            $interv Intervention
     * @param CSupervisionGraphPack $pack   Pack de graphiques
     * @param string                $type   Type de graphique (perop, sspi)
     * @param array                 $items  The list of item identifiers to load
     * @param bool                  $print  Print timeline
     *
     * @return array
     * @throws Exception
     */
    static function buildGraphs(
        COperation $interv,
        CSupervisionGraphPack $pack,
        $type = "perop",
        $items = null,
        $print = false
    ) {
        [$datetime_min, $datetime_max] = self::getTimingsByType($interv, $type, $print);
        [$results, $times] = self::getResultsFor($interv, $datetime_min, $datetime_max, $print);

        [
            $time_min,
            $time_max,
            $time_debut_op_iso,
            $time_fin_op_iso,
        ] = self::getLimitTimes($interv, $type, $print);

        $graph_links = $pack->loadRefsGraphLinks();
        CStoredObject::massLoadFwdRef($graph_links, "graph_id");

        /** @var CSupervisionTimeline $current_timeline */
        $current_timeline = null;

        $graphs = [];
        foreach ($graph_links as $key_gl => $_gl) {
            $_go = $_gl->loadRefGraph();

            if ($_go->disabled) {
                unset($graph_links[$key_gl]);
                continue;
            }

            if ($items && !in_array($_go->_guid, $items)) {
                continue;
            }

            if ($_go instanceof CSupervisionGraph) {
                if ($current_timeline) {
                    $current_timeline->options["showMajorLabels"] = true;
                    $current_timeline->options["showMinorLabels"] = true;
                }

                $_go->buildGraph($results, $time_min, $time_max);
                $graphs[] = $_go;
            } elseif ($_go instanceof CSupervisionTimedData) {
                $_go->loadTimedData($results);

                $_tl          = new CSupervisionTimeline($_go);
                $_tl->options = [
                    "showMajorLabels" => false,
                    "showMinorLabels" => false,
                ];

                $graphs[]         = $_tl;
                $current_timeline = $_tl;
            } elseif ($_go instanceof CSupervisionTimedPicture) {
                $_go->loadTimedPictures($results);

                $_tl          = new CSupervisionTimeline($_go);
                $_tl->options = [
                    "showMajorLabels" => false,
                    "showMinorLabels" => false,
                ];

                $graphs[]         = $_tl;
                $current_timeline = $_tl;
            } elseif ($_go instanceof CSupervisionInstantData) {
                $_go->loadRefValueType();
                $_go->loadRefValueUnit();
                $graphs[] = $_go;
            } elseif ($_go instanceof CSupervisionTable) {
                $_go->buildTimeline(
                    $times,
                    $results,
                    $time_debut_op_iso,
                    $time_fin_op_iso,
                    $interv,
                    $type,
                    $_gl->pack_id
                );
                $graphs[] = $_go;
            }
        }

        if ($current_timeline) {
            $current_timeline->options["showMajorLabels"] = true;
            $current_timeline->options["showMinorLabels"] = true;
        }

        $yaxes_count = 0;
        foreach ($graphs as $_graph) {
            if ($_graph instanceof CSupervisionGraph) {
                $yaxes_count = max($yaxes_count, count($_graph->_graph_data["yaxes"]));
            }
        }

        foreach ($graphs as $_graph) {
            if ($_graph instanceof CSupervisionGraph && count($_graph->_graph_data["yaxes"]) < $yaxes_count) {
                $_graph->_graph_data["yaxes"] = array_pad(
                    $_graph->_graph_data["yaxes"],
                    $yaxes_count,
                    CSupervisionGraphAxis::$default_yaxis
                );
            }
        }

        return [
            $graphs,
            $yaxes_count,
            $time_min,
            $time_max,
            $time_debut_op_iso,
            $time_fin_op_iso,
        ];
    }

    /**
     * Get chronological list
     *
     * @param COperation $interv       Intervention
     * @param int        $pack_id      Pack ID
     * @param bool       $load_images  Load images' data URI
     * @param ?string    $datetime_min An optional filter on the datetime
     * @param ?string    $datetime_max An optional filter on the datetime
     *
     * @return CObservationResultSet[]
     * @throws Exception
     */
    static function getChronological(
        COperation $interv,
        $pack_id,
        $load_images = false,
        $datetime_min = null,
        $datetime_max = null
    ) {
        $where = [
            "observation_result_set.context_class" => "= '$interv->_class'",
            "observation_result_set.context_id"    => "= '$interv->_id'",
        ];
        $order = [
            "observation_result_set.datetime",
            "observation_result_set.observation_result_set_id",
        ];

        if ($datetime_min) {
            $where[] = "datetime >= '$datetime_min'";
        }

        if ($datetime_max) {
            $where[] = "datetime <= '$datetime_max'";
        }

        $pack = new CSupervisionGraphPack();
        $pack->load($pack_id);
        $graph_links = $pack->loadRefsGraphLinks();

        $list_by_datetime = [];

        $graphs = self::massLoadFwdRef($graph_links, "graph_id");

        /** @var self[] $list */
        $list = (new CObservationResultSet())->loadList($where, $order);
        $grid = [];

        // Build the data structure

        $count  = 0;
        $labels = [];
        foreach ($graphs as $_graph) {
            if ($_graph instanceof CSupervisionGraph) {
                $_axes = $_graph->loadRefsAxes();

                self::massCountBackRefs($_axes, "series");

                foreach ($_axes as $_axis) {
                    $_series = $_axis->loadRefsSeries();

                    $count += count($_series);

                    foreach ($_series as $_serie) {
                        $labels[] = $_serie;
                    }
                }
            } elseif ($_graph instanceof CSupervisionTimedData || $_graph instanceof CSupervisionTimedPicture
            ) {
                $count++;
                $labels[] = $_graph;
            }
        }

        self::massCountBackRefs($list, "observation_results");

        // Fill the data structure
        foreach ($list as $_set) {
            $results = $_set->loadRefsResults();

            self::massLoadFwdRef($results, "file_id");

            foreach ($results as $_result) {
                $_file = $_result->loadRefFile();

                if ($load_images) {
                    $_file->getDataURI();
                }

                $_result->loadRefValueUnit();
            }

            $p    = 0;
            $_row = array_fill(0, $count, null);

            foreach ($graphs as $_graph) {
                if ($_graph instanceof CSupervisionGraph) {
                    $_axes = $_graph->_ref_axes;

                    foreach ($_axes as $_axis) {
                        $_series = $_axis->_ref_series;

                        foreach ($_series as $_serie) {
                            foreach ($results as $_result) {
                                $_result->loadUniqueValue();
                                if ($_result->_value_type_id == $_serie->value_type_id
                                    && $_result->_unit_id == $_serie->value_unit_id
                                ) {
                                    $_row[$p] = $_result;
                                }
                            }

                            $p++;
                        }
                    }
                } elseif ($_graph instanceof CSupervisionTimedData || $_graph instanceof CSupervisionTimedPicture) {
                    foreach ($results as $_result) {
                        $_result->loadUniqueValue();
                        if ($_result->_value_type_id == $_graph->value_type_id
                            && $_result->_unit_id == null
                        ) {
                            $_row[$p] = $_result;
                        }
                    }

                    $p++;
                }
            }

            $grid[$_set->datetime]             = $_row;
            $list_by_datetime[$_set->datetime] = $_set;
        }

        return [$list, $grid, $graphs, $labels, $list_by_datetime];
    }

    /**
     * Build type VisJS+Flot timeline data
     *
     * @param COperation            $interv       Intervention
     * @param bool                  $readonly     Readonly
     * @param string                $type         Surveillance type (perop / sspi)
     * @param CSupervisionGraphPack $pack         Pack to use
     * @param string                $element_main Element name to display
     * @param bool                  $print        Print timeline
     *
     * @return array
     * @throws Exception
     */
    static function buildEventsGrid(
        COperation $interv,
        $readonly,
        $type,
        CSupervisionGraphPack $pack,
        $element_main = null,
        $print = false
    ) {
        $current_user = CMediusers::get();
        $order_i      = 1;

        $groups    = [];
        $items     = [];
        $period_ok = true;

        [$datetime_min, $datetime_max] = self::getTimingsByType($interv, $type, $print);

        // Gestes, Medicaments, Perfusions peranesth
        if ($element_main == "supervision-timeline-geste" || !$element_main) {
            if ($readonly) {
                $content = CAppUI::tr("CAnesthPerop");
            } else {
                $smarty = new CSmartyDP("modules/dPsalleOp");
                $smarty->assign("interv", $interv);
                $smarty->assign("type", $type);
                $smarty->assign("readonly", $readonly);
                $content = $smarty->fetch("perop/inc_timeline_group_CAnesthPerop.tpl", '', '', 0);
            }

            $groups["CAnesthPerop"] = [
                "id"        => "CAnesthPerop",
                "className" => "timeline-CAnesthPerop",
                "order"     => $order_i++,
                "content"   => $content,
            ];
        } elseif ($type === "perop") {
            $where_personnel = [];

            if (!CAppUI::pref('show_all_datas_surveillance_timeline') || $print) {
                $where_personnel["debut"] = "<= '$datetime_max'";
                $where_personnel["fin"]   = ">= '$datetime_min'";
            }

            // Personnel de l'interv
            $_affectations   = $interv->loadAffectationsPersonnel($where_personnel);
            $count_personnel = 0;
            if (is_array($_affectations)) {
                foreach ($_affectations as $affectations) {
                    foreach ($affectations as $_affectation) {
                        if (!$_affectation->debut || !$_affectation->fin) {
                            continue;
                        }

                        $items[] = [
                            "id"       => $_affectation->_guid,
                            "group"    => "CAffectationPersonnel",
                            "content"  => $_affectation->_ref_personnel->_view,
                            "start"    => CMbDT::toTimestamp($_affectation->debut),
                            "end"      => CMbDT::toTimestamp($_affectation->fin),
                            //"object"   => $_affectation,
                            "editable" => false,
                            "line_id"  => null,
                        ];
                        $count_personnel++;
                    }
                }
            }

            if ($count_personnel) {
                $groups["CAffectationPersonnel"] = [
                    "id"        => "CAffectationPersonnel",
                    "className" => "timeline-CAffectationPersonnel",
                    "order"     => $order_i++,
                    "content"   => CAppUI::tr("CAffectationPersonnel"),
                ];
            }

            // Personnel de la plage
            $plageop       = $interv->loadRefPlageOp();
            $_affectations = $plageop->loadAffectationsPersonnel($where_personnel);

            if (is_array($_affectations)) {
                foreach ($_affectations as $affectations) {
                    foreach ($affectations as $_affectation) {
                        if (!$_affectation->debut || !$_affectation->fin) {
                            continue;
                        }

                        $items[] = [
                            "id"       => $_affectation->_guid,
                            "group"    => "CAffectationPersonnel",
                            "content"  => $_affectation->_ref_personnel->_view,
                            "start"    => CMbDT::toTimestamp($_affectation->debut),
                            "end"      => CMbDT::toTimestamp($_affectation->fin),
                            "editable" => false,
                            "line_id"  => null,
                        ];
                    }
                }
            }
        }

        // Gestes perop
        if (!$element_main || $element_main == "supervision-timeline-geste") {
            $where_anesth_perop = [];

            if (!CAppUI::pref('show_all_datas_surveillance_timeline') || $print) {
                $where_anesth_perop["datetime"] = "BETWEEN '$datetime_min' AND '$datetime_max'";
            }

            $anesths_perop = $interv->loadRefsAnesthPerops($where_anesth_perop);
            CStoredObject::massLoadFwdRef($anesths_perop, "user_id");

            foreach ($anesths_perop as $_perop) {
                if ($_perop->datetime >= $datetime_min && $_perop->datetime <= $datetime_max) {
                    $period_ok = true;
                } else {
                    $period_ok = false;
                }

                $_perop->loadRefFile();
                $user_perop = $_perop->loadRefUser();

                $_content = sprintf(
                    '<span onmouseover="ObjectTooltip.createEx(this, \'%s\');"><div class="text">%s</div></span>',
                    $_perop->_guid,
                    $_perop->_view_completed
                );

                $file = "";
                if ($_perop->_ref_file && $_perop->_ref_file->_id) {
                    $file = sprintf(
                        ' <img style="height: 40px; width=100px;" src="?m=files&raw=thumbnail&document_guid=CFile-%d&' .
                        'profile=medium">',
                        $_perop->_ref_file->_id
                    );
                }

                $warning_incident = "";
                $incident         = CAppUI::tr("CAnesthPerop-incident");
                if ($_perop->incident) {
                    $warning_incident = sprintf(
                        '<i class="fas fa-exclamation-triangle" title="' . $incident . '" style="color: red"></i> '
                    );
                }

                $comment = "";
                if ($_perop->commentaire) {
                    $comment = sprintf('<div class="text">%s</div>', $_perop->commentaire);
                }

                $items[] = [
                    "id"                  => $_perop->_guid,
                    "group"               => "CAnesthPerop",
                    "content"             => $warning_incident . $_content . "" . $file . "" . $comment,
                    "start"               => CMbDT::toTimestamp($_perop->datetime),
                    "object_type"         => $_perop->_class,
                    "object_guid"         => $_perop->_guid,
                    "editable"            => (!$user_perop->_id || ($current_user->_id == $user_perop->_id)) && !$readonly && $period_ok ? true : false,
                    "period_ok"           => $period_ok,
                    "period_datetime_min" => $datetime_min,
                    "period_datetime_max" => $datetime_max,
                    "type"                => "point",
                    "line_id"             => null,
                    "user_id"             => $user_perop->_id,
                    "className"           => "planif_success",
                ];
            }
        }

        if (CModule::getActive("dPprescription") && CPrescription::isPlanSoinsActive() &&
            ($element_main == "supervision-timeline" || !$element_main)) {
            // Lignes de medicaments et d'elements
            $sejour       = $interv->loadRefSejour();
            $prescription = $sejour->loadRefPrescriptionSejour();

            if ($prescription->_id) {
                $lines = $prescription->loadPeropLines($interv->_id, false, $datetime_min, $datetime_max, $print, null, self::$limited_view_datas);

                $datetime_max_adm = min(
                    CMbDT::dateTime(
                        '+ ' .
                        CAppUI::conf(
                            'planSoins Perop delai_administration_futur',
                            $interv->getContextConfigMonitoring()
                        ) .
                        ' hours'
                    ),
                    $datetime_max
                );

                foreach ($lines as $_line_array) {
                    /** @var CPrescriptionLineElement|CPrescriptionLineMedicament|CPrescriptionLineMix $_line */
                    $_line = $_line_array["object"];
                    $_line->loadRefsPrises();

                    $key = "CPrescription._chapitres.$_line->_chapitre";

                    $unite    = "";
                    $code_cis = "";

                    if ($_line instanceof CPrescriptionLineMedicament) {
                        $unite    = $_line->_unite_reference_libelle;
                        $code_cis = $_line->code_cis;
                    } elseif ($_line instanceof CPrescriptionLineMix) {
                        $_line->calculVolumeAdministre();

                        foreach ($_line->_ref_lines as $_mix_item) {
                            $unite = $_mix_item->_unite_reference_libelle;
                        }
                    }

                    // Build view
                    $_total     = "";
                    $_total_num = 0;

                    if ($_line instanceof CPrescriptionLineElement) {
                        $_view = $_line->_view;
                    } elseif ($_line instanceof CPrescriptionLineMix) {
                        $_ucd_views = CMbArray::pluck($_line->_ref_lines, '_ucd_view');
                        $_view      = implode(" / ", $_ucd_views);
                    } else {
                        if (CAppUI::gconf("planSoins general show_dci")) {
                            $_view = $_line->_dci_view;
                        } else {
                            $_view = $_line->_ucd_view;
                        }

                        $_total     = $_line->_quantite_administration . " " . $unite;
                        $_total_num = $_line->_quantite_administration;
                    }

                    $_subkey = "$key-$_line->_guid";

                    $_subitem = [
                        "id"    => null,
                        "group" => "$key-$_line->_guid",
                    ];

                    $debit_total = 0;

                    $signature_perop_obligatoire = CAppUI::gconf("dPprescription general signature_perop_obligatoire");
                    $check_warning               = true;

                    if (!$signature_perop_obligatoire && $current_user->isIADE()) {
                        $check_warning = false;
                    }

                    // Cas Attention
                    if ((($_line instanceof CPrescriptionLineMedicament && !$_line->signee && !$_line->inscription) ||
                            $_line instanceof CPrescriptionLineMix && !$_line->signature_prat) &&
                        CAppUI::gconf("planSoins general show_unsigned_med_msg") && $check_warning) {
                        $item_datas = [
                            "id"       => "line_warning_not_signed_" . $_line->_guid,
                            "datetime" => $sejour->entree,
                            "content"  => sprintf(
                                "<div class='small-warning'>%s</div>",
                                CAppUI::tr("CPrescription-Not signed")
                            ),
                            "user_id"  => $current_user->_id,
                            "start"    => CMbDT::toTimestamp($sejour->entree),
                            "end"      => CMbDT::toTimestamp($sejour->sortie),
                            "style"    => "background-color: #ffd; z-index: 5;",
                        ];

                        $items[] = self::getBackgroundItem($_subitem, $item_datas);
                    } elseif ($_line->jour_decalage === "R" && !$interv->sortie_reveil_reel) {
                        $item_datas = [
                            "id"       => "line_warning_r_" . $_line->_guid,
                            "datetime" => $sejour->entree,
                            "content"  => sprintf(
                                "<div class='small-warning'>%s</div>",
                                CAppUI::tr("CPrescription-Wake-up output not indicated")
                            ),
                            "user_id"  => $current_user->_id,
                            "start"    => CMbDT::toTimestamp($sejour->entree),
                            "end"      => CMbDT::toTimestamp($sejour->sortie),
                            "style"    => "background-color: #ffd; z-index: 5;",
                        ];

                        $items[] = self::getBackgroundItem($_subitem, $item_datas);
                    } elseif ($_line->jour_decalage === "ER" && !$interv->entree_reveil) {
                        $item_datas = [
                            "id"       => "line_warning_er_" . $_line->_guid,
                            "datetime" => $sejour->entree,
                            "content"  => sprintf(
                                "<div class='small-warning'>%s</div>",
                                CAppUI::tr("CPrescription-Wake-up entry not indicated")
                            ),
                            "user_id"  => $current_user->_id,
                            "start"    => CMbDT::toTimestamp($sejour->entree),
                            "end"      => CMbDT::toTimestamp($sejour->sortie),
                            "style"    => "background-color: #ffd; z-index: 5;",
                        ];

                        $items[] = self::getBackgroundItem($_subitem, $item_datas);
                    } elseif ($_line->jour_decalage === "ALR" && !$interv->debut_alr) {
                        $item_datas = [
                            "id"       => "line_warning_alr_" . $_line->_guid,
                            "datetime" => $sejour->entree,
                            "content"  => sprintf(
                                "<div class='small-warning'>%s</div>",
                                CAppUI::tr(
                                    "CPrescription-Beginning of locoregional anesthesia not indicated"
                                )
                            ),
                            "user_id"  => $current_user->_id,
                            "start"    => CMbDT::toTimestamp($sejour->entree),
                            "end"      => CMbDT::toTimestamp($sejour->sortie),
                            "style"    => "background-color: #ffd; z-index: 5;",
                        ];

                        $items[] = self::getBackgroundItem($_subitem, $item_datas);
                    } elseif ($_line->jour_decalage === "AG" && !$interv->debut_ag) {
                        $_item             = $_subitem;
                        $_item["id"]       = "line_warning_ag_" . $_line->_guid;
                        $_item["datetime"] = $sejour->entree;
                        $_item["content"]  = sprintf(
                            "<div class='small-warning'>%s</div>",
                            CAppUI::tr(
                                "CPrescription-Start of general anesthesia not indicated"
                            )
                        );
                        $_item["user_id"]  = $current_user->_id;
                        $_item["start"]    = CMbDT::toTimestamp($sejour->entree);
                        $_item["end"]      = CMbDT::toTimestamp($sejour->sortie);
                        $_item["type"]     = "background";
                        $_item["style"]    = "background-color: #ffd; z-index: 5;";
                        $items[]           = $_item;

                        $item_datas = [
                            "id"       => "line_warning_item_" . $_line->_guid,
                            "datetime" => $sejour->entree,
                            "content"  => sprintf(
                                "<div class='small-warning'>%s</div>",
                                CAppUI::tr(
                                    "CPrescription-Beginning of locoregional anesthesia not indicated"
                                )
                            ),
                            "user_id"  => $current_user->_id,
                            "start"    => CMbDT::toTimestamp($sejour->entree),
                            "end"      => CMbDT::toTimestamp($sejour->sortie),
                            "style"    => "background-color: #ffd; z-index: 5;",
                        ];

                        $items[] = self::getBackgroundItem($_subitem, $item_datas);
                    } elseif ($_line instanceof CPrescriptionLineMedicament && $_line->_ref_produit->isMv()) {
                        $item_datas = [
                            "id"       => "line_warning_mv_" . $_line->_guid,
                            "datetime" => $sejour->entree,
                            "content"  => sprintf(
                                "<div class='small-warning'>%s</div>",
                                CAppUI::tr(
                                    "CPrescription-Prescription in DCI, a specialty must be selected by the pharmacy to allow administration"
                                )
                            ),
                            "user_id"  => $current_user->_id,
                            "start"    => CMbDT::toTimestamp($sejour->entree),
                            "end"      => CMbDT::toTimestamp($sejour->sortie),
                            "style"    => "background-color: #ffd; z-index: 5;",
                        ];

                        $items[] = self::getBackgroundItem($_subitem, $item_datas);
                    } elseif ($_line instanceof CPrescriptionLineMedicament && $_line->bdm == "vidal" && !$_line->_ref_produit->code_cis) {
                        $item_datas = [
                            "id"       => "line_warning_vidal_" . $_line->_guid,
                            "datetime" => $sejour->entree,
                            "content"  => sprintf(
                                "<div class='small-warning'>%s</div>",
                                CAppUI::tr(
                                    "CPrescription-The product is missing from the drug database"
                                )
                            ),
                            "user_id"  => $current_user->_id,
                            "start"    => CMbDT::toTimestamp($sejour->entree),
                            "end"      => CMbDT::toTimestamp($sejour->sortie),
                            "style"    => "background-color: #ffd; z-index: 5;",
                        ];

                        $items[] = self::getBackgroundItem($_subitem, $item_datas);
                    } else {
                        // Chevrons
                        if (!$_line->conditionnel) {
                            $_item             = $_subitem;
                            $_item["id"]       = "line_deb_" . $_line->_guid;
                            $_item["datetime"] = ($_line instanceof CPrescriptionLineMix) ? $_line->_debut : $_line->debut;
                            $_item["content"]  = "";
                            $_item["user_id"]  = $current_user->_id;
                            $_item["start"]    = CMbDT::toTimestamp($_line->_debut_reel);
                            $_item["end"]      = CMbDT::toTimestamp($_line->_fin_reelle);
                            $_item["type"]     = "background";
                            $_item["style"]    = "background-color: rgba(2, 219, 255, 0.26); z-index: 1; opacity: 0.8; font-size:0.8em;";
                            $items[]           = $_item;
                        }
                    }

                    if ($_line instanceof CPrescriptionLineMix) {
                        $_line->loadRefsSegments();
                        $_line->loadActiveDates();
                        $_variations = $_line->calculVariationPerop();

                        // Calcul des prises prevues
                        $_line->calculQuantiteTotal();

                        if ($_line->continuite == "discontinue") {
                            $_variations = [
                                0 => 0,
                                1 => [],
                            ];
                            $_line->calculVariations();

                            $_save_date_time = null;
                            $_save_date_time_fin = null;

                            foreach ($_line->_variations as $_datetime => $_variations_line) {
                                foreach ($_variations_line as $_time => $_variation) {
                                    // On tient compte uniquement des débits non nuls.
                                    // L'absence de débit est gérée directement grâce à la durée de la variation
                                    // exprimée par le pourcentage
                                    if (!$_variation['debit']) {
                                        continue;
                                    }

                                    $date_time = CMbDT::date($_datetime) . " {$_time}";

                                    $datetime_fin = CMbDT::dateTime(
                                        "+" . (intval(round($_variation['pourcentage'] * 60 / 100))) . " minutes",
                                        $date_time
                                    );

                                    if ($_save_date_time_fin) {
                                        // Si le calcul précédent d'heure de de fin dépasse sur la case suivante,
                                        // on copie la fin sur le début de la case courante
                                        if (
                                            (CMbDT::date($_datetime) === CMbDT::date($_save_date_time_fin))
                                            && (CMbDT::time($_save_date_time_fin) > $_time)
                                        ) {
                                            $_save_date_time_fin = CMbDT::date($_save_date_time_fin) . " {$_time}";
                                            $_variations[1][$_save_date_time]['fin'] = $_time;
                                        }
                                        $_variations[1][$_save_date_time_fin] = [
                                            'debit'        => 0,
                                            'variation_id' => null,
                                            'fin'          => $date_time
                                        ];
                                    }

                                    $_variations[1][$date_time] = [
                                        "debit"        => intval($_variation['debit']),
                                        "variation_id" => $_variation['variation_id'],
                                        "fin"          => $datetime_fin,
                                    ];

                                    $_save_date_time     = $date_time;
                                    $_save_date_time_fin = $datetime_fin;
                                }
                            }

                            if (count($_variations[1])) {
                                $_variations[0] = max(CMbArray::pluck($_variations[1], "debit"));
                            }

                            // Remove the first key if the debit equal 0 (To avoid a shift on the graph)
                            $counter_variation = 0;
                            foreach ($_variations[1] as $_variation_datetime => $_variation) {
                                if (!$counter_variation && $_variation["debit"] === 0) {
                                    unset($_variations[1][$_variation_datetime]);
                                }

                                $counter_variation++;
                            }
                        } elseif (!$_line->_last_variation->debit) {
                            $_line->_last_variation->debit = $_line->_ref_variations ? end(
                                $_line->_ref_variations
                            )->debit : $_line->_debit;
                        }

                        $data     = [];
                        $duration = CMbDT::toTimestamp($_line->_fin_reelle) - CMbDT::toTimestamp($_line->_debut_reel);

                        // ligne en 1 seul fois
                        /*if ($_line->ponctual) {
                            $line_mix = reset($_line->_ref_lines);
                            $data[]   = [
                                "guid"       => $_line->_guid,
                                "value"      => 1,
                                "ponctual"   => 1,
                                "unit"       => $line_mix->_unite_reference_libelle,
                                "voie_label" => $_line->_libelle_voie,
                            ];

                            $_item              = $_subitem;
                            $_item["id"]        = $_line->_guid;
                            $_item["line_type"] = "perf";
                            $_item["start"]     = CMbDT::toTimestamp($_line->_debut_reel);
                            $_item["editable"]  = false;
                            $_item["template"]  = "perf";
                            $_item["type"]      = "point";
                            $_item["data"]      = $data;
                            $_item["line_id"]   = $_line->_id;
                            $_item["className"] = "planif_success";
                            $_item["style"]     = "z-index: 2;";

                            $items[] = $_item;
                        }*/

                        if ($_variations) {
                            // Initial debit
                            $initial_height = 100;

                            $debut_line = $_line->_debut_reel;

                            [$_perf_max, $_perfs] = $_variations;

                            $_perfs = CPrescriptionLineMix::adjustVariationsForPerop($_line, $_perfs);

                            foreach ($_perfs as $_datetime => &$_perf) {
                                // Cas de la planification qui est avant le début de ligne (dans le cas des prises à heure fixe)
                                // Exemple : début de la ligne à 14h20, prise à 14h00
                                if ($_datetime < $debut_line) {
                                    $debut_line = $_datetime;
                                }
                                $_height = $_perf_max ? (floatval($_perf["debit"]) / floatval($_perf_max) * 100) : 0;

                                $debit_reel = number_format(
                                    (floatval($_perf["debit"]) * CMbDT::minutesRelative(
                                            $_datetime,
                                            $_perf["fin"]
                                        )) / 60,
                                    2
                                );

                                $debit = $_perf["debit"] ?: 0;

                                $_label = "Début: " . CMbDT::format(
                                        $_datetime,
                                        CAppUI::conf("datetime")
                                    ) . " - Fin: " . CMbDT::format(
                                        $_perf["fin"],
                                        CAppUI::conf("datetime")
                                    ) . " - [" . $debit . " ml]";

                                $debit_total += floatval($debit_reel);

                                if ($_perf["debit"] === $_line->_debit) {
                                    $initial_height -= $_height;
                                }

                                $data[] = [
                                    "guid"       => $_line->_guid,
                                    "value"      => $debit,
                                    "value_real" => $debit_reel,
                                    "height"     => $_height,
                                    "title"      => $_label,
                                    "ponctual"   => 0,
                                    "width"      => $duration ? (CMbDT::toTimestamp($_perf["fin"]) - CMbDT::toTimestamp(
                                                $_datetime
                                            )) / $duration : 0,
                                ];
                            }

                            if (count($data)) {
                                $_item              = $_subitem;
                                $_item["id"]        = $_line->_guid;
                                $_item["line_type"] = "perf";
                                $_item["start"]     = CMbDT::toTimestamp($debut_line);

                                if (!$_line->ponctual) {
                                    $_item["end"] = CMbDT::toTimestamp($_line->_fin_reelle);
                                }

                                $_item["editable"]       = false;
                                $_item["template"]       = "perf";
                                $_item["data"]           = $data;
                                $_item["line_id"]        = $_line->_id;
                                $_item["initial_debit"]  = $_line->_debit ?: 0;
                                $_item["initial_height"] = $initial_height;
                                $_item["style"]          = "z-index: 2;";
                                $_item["subgroup"]       = "variation";
                                $_item['limitSize']      = false;

                                $items[] = $_item;
                            }
                        }

                        $total_quantity_administration      = [];
                        $total_quantity_administration_unit = null;
                        $total_cumul_massique               = [];

                        $counter_adm_mix        = 0;
                        $quantity_total         = ["cumul" => [], "cumul_massique" => [], "volume_administre" => []];
                        $counter_line_mix_items = 0;

                        foreach ($_line->_ref_lines as $_line_mix_item) {
                            $total_quantity_administration_unit = $_line_mix_item->_unite_reference_libelle;

                            $is_B05 = ($_line_mix_item instanceof CPrescriptionLineMixItem && $_line_mix_item->atc) ? preg_match(
                                "/^B05/",
                                $_line_mix_item->atc
                            ) : false;

                            if (!$_line_mix_item->solvant) {
                                $counter_line_mix_items++;
                            }

                            $_line_mix_item->loadRefsAdministrations();
                            $produit = $_line_mix_item->loadRefProduit();

                            $produit->updateRatioMassique();
                            $ratio = null;
                            if (
                                $_line->_pousse_seringue
                                && $_line_mix_item->_ratio_unite_bolus
                                && $_line_mix_item->_unite_bolus
                            ) {
                                $_line_mix_item->_unite_massique = $_line_mix_item->_unite_bolus;
                                $ratio                           = $_line_mix_item->_ratio_unite_bolus;
                            } elseif (
                                ($_line_mix_item->_libelle_unite_prescription === 'g'
                                    || $produit->_ratio_microg
                                    || $produit->_ratio_mg
                                    || $produit->_ratio_UI)
                                && !$is_B05
                            ) {
                                if ($_line_mix_item->_libelle_unite_prescription === 'g') {
                                    $_line_mix_item->_unite_massique = 'g';
                                    $ratio                           = 1;
                                } elseif ($produit->_ratio_microg) {
                                    $_line_mix_item->_unite_massique = "µg";
                                    $ratio                           = $produit->_ratio_microg;
                                } elseif ($produit->_ratio_mg) {
                                    $_line_mix_item->_unite_massique = "mg";
                                    $ratio                           = $produit->_ratio_mg;
                                } else {
                                    $_line_mix_item->_unite_massique = "UI";
                                    $ratio                           = $produit->_ratio_UI;
                                }
                                $_line_mix_item->_ref_produit->_ratio_massique = $ratio;
                            }

                            if (!isset($total_cumul['mix'][$_line->_id])) {
                                $total_cumul['mix'][$_line->_id]                = 0;
                                $total_cumul['mix']['total_cumul'][$_line->_id] = 0;
                                $total_cumul['counter'][$_line->_id]            = 0;
                                $total_cumul['volume_administre'][$_line->_id]  = 0;
                            }

                            $total_cumul['volume_administre'][$_line->_id] += $_line_mix_item->_volume_administre;

                            if ($ratio && !$_line_mix_item->solvant) {
                                if (!isset($total_cumul_massique[$_line_mix_item->_id])) {
                                    $total_cumul_massique[$_line_mix_item->_id] = [
                                        'unit'     => $_line_mix_item->_unite_massique,
                                        'qte'      => 0,
                                        'ucd_view' => $_line_mix_item->_composant_produit,
                                    ];
                                }

                                $total_cumul_massique[$_line_mix_item->_id]['qte'] +=
                                    $_line->computeCumulMassique($_line_mix_item, $_variations);
                            }

                            $_items_adms = [];

                            // Calculate the cumul
                            foreach ($_line_mix_item->_ref_administrations as $_adm) {
                                $period_ok = CPrescriptionLine::canAdmLinePerop(
                                    $_line,
                                    $sejour,
                                    $_adm->dateTime,
                                    $datetime_min,
                                    $datetime_max_adm
                                );

                                $administrateur = $_adm->loadRefAdministrateur();
                                $_adm->loadTargetObject();
                                $_adm->_ref_object->loadRefsFwd();

                                $quantite = 0;

                                // On exprime la quantité en ml pour le cumul
                                $produit_mix = $_line_mix_item->_ref_produit;
                                $id_ml       = $produit_mix->getIdML();
                                $unite_adm   = $_line_mix_item->_unite_administration;

                                // Directement en ml
                                if ($unite_adm === $id_ml) {
                                    $quantite = $_adm->quantite;
                                    $unit = 'ml';
                                } elseif (
                                    isset(
                                        $produit_mix->rapport_unite_prise[$id_ml][$unite_adm]
                                    )
                                ) {
                                    // Conversion possible
                                    $quantite = $_adm->quantite / $produit_mix->rapport_unite_prise[$id_ml][$unite_adm];
                                }

                                if ($ratio) {
                                    $_adm->_quantite_massique = CMbMath::roundSig($_adm->quantite / $ratio, 2);
                                }

                                $_adm->_cumul          += CMbMath::roundSig($quantite, 2);
                                $_adm->_cumul_massique += CMbMath::roundSig($_adm->_quantite_massique, 2);

                                if ($_adm->_cumul_massique && !$_line_mix_item->solvant) {
                                    $total_cumul_massique[$_line_mix_item->_id]['qte'] += $_adm->_cumul_massique;
                                }

                                if (!isset($quantity_total["cumul"][$_line->_id])) {
                                    $quantity_total["cumul"][$_line->_id] = 0;
                                }
                                if (!isset($quantity_total["cumul_massique"][$_line->_id])) {
                                    $quantity_total["cumul_massique"][$_line->_id] = 0;
                                }
                                $quantity_total["cumul"][$_line->_id]          += $quantite;
                                $quantity_total["cumul_massique"][$_line->_id] += $_adm->_cumul_massique;

                                $counter_adm_mix++;

                                $total_cumul['mix'][$_line->_id]                = $quantity_total["cumul"][$_line->_id] . ' ml';
                                $total_cumul['mix']['total_cumul'][$_line->_id] = $quantity_total["cumul"][$_line->_id];
                                $total_cumul['counter'][$_line->_id]            = $counter_adm_mix;

                                if ($_adm->_cumul_massique && !$_line_mix_item->solvant) {
                                    if (!isset($total_cumul['mix']['cumul_massique'][$_line->_id])) {
                                        $total_cumul['mix']['cumul_massique'][$_line->_id] = [];
                                    }

                                    if (!isset($total_cumul['mix']['cumul_massique'][$_line->_id][$_line_mix_item->_id])) {
                                        $total_cumul['mix']['cumul_massique'][$_line->_id][$_line_mix_item->_id] = [
                                            'ucd_view' => $_line_mix_item->_ucd_view,
                                            'qte' => 0,
                                            'unite' => $_line_mix_item->_unite_massique,
                                        ];
                                    }

                                    $total_cumul['mix']['cumul_massique'][$_line->_id][$_line_mix_item->_id]['qte'] +=
                                        $_adm->_cumul_massique;
                                }

                                [$unite_lt, $qte_lt] = CPrescriptionLineMedicament::computeQteUnitLTPerop(
                                    $_line_mix_item,
                                    $quantite
                                );

                                if ($unite_lt && $qte_lt) {
                                    $unit     = $unite_lt;
                                    $quantite = $qte_lt;
                                }

                                $qte_unit_view = ($_adm->_cumul_massique && !$qte_lt && !$unite_lt) ?
                                    ($_adm->_cumul_massique . " " . $_line_mix_item->_unite_massique) :
                                    ($quantite . " " . $unit);

                                // Show in the charts
                                if ($readonly) {
                                    $_content = "<strong>$qte_unit_view</strong>";
                                } else {
                                    $_content = sprintf(
                                        '<strong><span onmouseover="ObjectTooltip.createEx(this, \'%s\', null, null, {duration: 1});">%s</span></strong>',
                                        $_adm->_guid,
                                        $qte_unit_view
                                    );
                                }

                                // Add the name of the product
                                $_content .= ' <strong>(' . $_line_mix_item->_composant_produit . ')</strong>';

                                if ($_line->voie && ($_adm->quantite > 0)) {
                                    $voie_label = CMedicamentProduit::getLibelleVoie($_line->voie);
                                    $_content   .= " <strong>($voie_label)</strong>";
                                }

                                $class_planif_color = "planif_success";

                                if ($_line->_pousse_seringue) {
                                    $class_planif_color .= ' pousse-seringue';
                                }

                                if ($_adm->quantite) {
                                    $_item                        = $_subitem;
                                    $_item["id"]                  = $_adm->_guid;
                                    $_item["content"]             = $_content;
                                    $_item["content_light"]       = "$quantite $unite";
                                    $_item["start"]               = CMbDT::toTimestamp($_adm->dateTime);
                                    $_item["type"]                = "point";
                                    $_item["editable"]            = (($current_user->_id == $administrateur->_id) && !$readonly && $_adm->quantite && $period_ok) ? true : false;
                                    $_item["datetime"]            = $_adm->dateTime;
                                    $_item["period_ok"]           = $period_ok;
                                    $_item["period_datetime_min"] = $datetime_min;
                                    $_item["period_datetime_max"] = $datetime_max_adm;
                                    $_item["line_id"]             = $_line->_id;
                                    $_item["quantite"]            = $_adm->quantite;
                                    $_item["conditionnel"]        = $_line->conditionnel;
                                    $_item["prise_id"]            = $_adm->prise_id;
                                    $_item["user_id"]             = $_adm->administrateur_id;
                                    $_item["administrateur"]      = $administrateur;
                                    $_item["className"]           = "$class_planif_color";
                                    $_item["style"]               = "z-index: 2;";
                                    $_item["subgroup"]            = "administration";
                                    $items[]                      = $_item;
                                }

                                // Stockage des items d'administration
                                $_items_adms[$_adm->dateTime][$_adm->prise_id] = true;
                            }

                            $total_cumul['adms'][$_line->_id][$_line_mix_item->_id] = $_line_mix_item->_ref_administrations;

                            if (!$print) {
                                // Ratio vers l'unité de référence pour l'administration de bolus
                                if ($_line->_pousse_seringue) {
                                    $_line_mix_item->_ratio_prescription = $_line_mix_item->_ratio_unite_bolus;
                                }

                                // Planifications
                                $where_planif_mix = [
                                    "object_class" => " = '$_line_mix_item->_class'",
                                    "object_id"    => " = '$_line_mix_item->_id'",
                                ];

                                if (!CAppUI::pref('show_all_datas_surveillance_timeline') || $print) {
                                    $where_planif_mix["dateTime"] = "BETWEEN '$datetime_min' AND '$datetime_max'";
                                }

                                // Suppression des planif systeme qui ont une planif manuelle associée
                                $where = [
                                    "object_class"      => " = '$_line_mix_item->_class'",
                                    "object_id"         => " = '$_line_mix_item->_id'",
                                    "planification"     => " = '1'",
                                    "original_dateTime" => "IS NOT NULL",
                                ];

                                $_administration              = new CAdministration();
                                $_planification_mix_manuelles = $_administration->loadList($where);

                                $orig_dates_mix = array_flip(
                                    CMbArray::pluck($_planification_mix_manuelles, "original_dateTime")
                                );

                                /** @var CPlanificationSysteme[] $_planifications_mix */
                                $_planifications_mix = $_line_mix_item->loadBackRefs(
                                    "planifications",
                                    null,
                                    null,
                                    null,
                                    null,
                                    null,
                                    "",
                                    $where_planif_mix
                                );

                                foreach ($_planifications_mix as $_planification) {
                                    $datetimes_to_check = [$_planification->dateTime];

                                    $datetimes_to_check[] = CMbDT::transform(
                                        null,
                                        $_planification->dateTime,
                                        '%Y-%m-%d %H:%M:00'
                                    );

                                    foreach ($datetimes_to_check as $datetime_to_check) {
                                        if ((array_key_exists($datetime_to_check, $_items_adms) && array_keys(
                                                    $_items_adms[$datetime_to_check]
                                                )[0] == $_planification->prise_id) || $_line->continuite === "continue") {
                                            unset($_planifications_mix[$_planification->_id]);
                                        }

                                        if (isset($orig_dates_mix[$datetime_to_check])) {
                                            $_planif_manuelle =
                                                $_planification_mix_manuelles[$orig_dates_mix[$datetime_to_check]];

                                            $_planification->dateTime           = $_planif_manuelle->dateTime;
                                            $_planification->_administration_id = $_planif_manuelle->_id;
                                            $_planification->_original_dateTime = $_planif_manuelle->original_dateTime;
                                        }
                                    }
                                }

                                foreach ($_planifications_mix as $_planification) {
                                    if (array_key_exists($_planification->dateTime, $_items_adms) && array_keys(
                                            $_items_adms[$_planification->dateTime]
                                        )[0] == $_planification->prise_id) {
                                        continue;
                                    }
                                    $_planification->loadTargetObject();
                                    $quantite      = $_planification->getQuantiteAdministrable();
                                    $quantite_view = $quantite;

                                    $unite    = $_planification->_ref_object->_unite_reference_libelle;
                                    $code_cis = $_planification->_ref_object->code_cis;

                                    // Pour un principe actif, on exprime en unité de prescription en première intention
                                    if (!$_line_mix_item->solvant) {
                                        $quantite_view = $_line_mix_item->quantite;
                                        $unite    = $_line_mix_item->_libelle_unite_prescription;
                                        $unite    = preg_replace('/(.*) \(.*\)$/', "$1", $unite);
                                    }

                                    [$unite_lt, $qte_lt] = CPrescriptionLineMedicament::computeQteUnitLTPerop(
                                        $_line_mix_item,
                                        $quantite
                                    );

                                    if ($unite_lt && $qte_lt) {
                                        $unite         = $unite_lt;
                                        $quantite_view = $qte_lt;
                                    }

                                    $class_planif_color = "planif_ok";

                                    if ($_planification->dateTime == CMbDT::dateTime()) {
                                        $class_planif_color = "planif_success";
                                    } elseif ($_planification->dateTime < CMbDT::dateTime()) {
                                        $class_planif_color = "planif_late";
                                    }

                                    $store_administration = CPrescriptionLine::canAdmLinePerop(
                                        $_line,
                                        $sejour,
                                        $_planification->dateTime,
                                        $datetime_min,
                                        $datetime_max_adm
                                    );

                                    $period_ok = CPrescriptionLine::canAdmLinePerop(
                                        $_line,
                                        $sejour,
                                        $_planification->dateTime,
                                        $datetime_min,
                                        $datetime_max
                                    );

                                    $content = "$quantite_view $unite";

                                    if ($store_administration && !$print && !$readonly && $period_ok) {
                                        $button_id = "admin_$_line_mix_item->_guid";
                                        $content   .= sprintf(
                                            '<button id="%s" type="button" class="tick me-tertiary oneclick me-small"
                              onclick="SurveillancePerop.storeAdministration(\'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\');" 
                              title="%s"></button>',
                                            $button_id,
                                            $_planification->dateTime,
                                            $quantite,
                                            $_planification->prise_id,
                                            $current_user->_id,
                                            $_line_mix_item->_guid,
                                            $type,
                                            CAppUI::tr('CPrescription-action-Administer')
                                        );
                                    }

                                    $_item                           = $_subitem;
                                    $_item["id"]                     = $_planification->_guid;
                                    $_item["datetime"]               = $_planification->dateTime;
                                    $_item["prise_id"]               = $_planification->prise_id;
                                    $_item["administration_id"]      = $_planification->_administration_id;
                                    $_item["user_id"]                = $current_user->_id;
                                    $_item["code_cis"]               = $code_cis;
                                    $_item["quantite"]               = $quantite;
                                    $_item["content"]                = $content;
                                    $_item["start"]                  = CMbDT::toTimestamp($_planification->dateTime);
                                    $_item["type"]                   = "point";
                                    $_item["className"]              = "planif_systeme $class_planif_color";
                                    $_item["period_ok"]              = $period_ok;
                                    $_item["editable"]["updateTime"] = $period_ok && !$readonly;
                                    $_item["editable"]["remove"]     = false;
                                    $_item["editable"]               = $period_ok && !$readonly;
                                    $_item["conditionnel"]           = $_line->conditionnel;
                                    $_item["object_type"]            = "CPlanificationSysteme";
                                    $_item["subgroup"]               = "planification";
                                    $_item["line_id"]                = $_line->_id;
                                    $_item["line_mix_item_guid"]     = $_line_mix_item->_guid;
                                    $_item["original_dateTime"]      = $_planification->_original_dateTime;
                                    $_item["style"]                  = "z-index: 2;";
                                    $items[]                         = $_item;
                                }
                            }
                        }
                    }

                    $class_bg_conditionnelle = "";

                    if ($_line->conditionnel) {
                        if (!$_line->_current_active) {
                            $class_bg_conditionnelle = "hatching opacity-60";
                        }

                        $counter = count($_line->_ref_segments);

                        foreach ($_line->_ref_segments as $_segment) {
                            $_item             = $_subitem;
                            $_item["id"]       = $_segment->_guid;
                            $_item["datetime"] = $_segment->debut;
                            $_item["content"]  = "Segment $counter";
                            $_item["user_id"]  = $current_user->_id;
                            $_item["start"]    = CMbDT::toTimestamp($_segment->debut);
                            $_item["end"]      = CMbDT::toTimestamp($_segment->fin ?: $sejour->sortie);
                            $_item["type"]     = "background";
                            $_item["style"]    = "background-color: #f5f0e7; z-index: 1;";
                            $items[]           = $_item;

                            $counter--;
                        }
                    }

                    if (!isset($groups[$_subkey])) {
                        $_total_view = "";
                        $is_B05      = ($_line instanceof CPrescriptionLineMedicament && $_line->atc) ? preg_match(
                            "/^B05/",
                            $_line->atc
                        ) : false;

                        if ($_total_num > 0) {
                            $_total_view = $_total;
                        }

                        if ($_line instanceof CPrescriptionLineMix) {
                            if ($readonly) {
                                $_content = sprintf(
                                    '<img src="%s" style="float: left; margin-right: 2px;"/>%s',
                                    CPrescription::$images[$_line->_chapitre],
                                    $_view
                                );
                            } else {
                                $tpl_name = "inc_timeline_group_CPrescriptionLineMix";

                                if (self::$limited_view_datas) {
                                    $tpl_name .= "_light";
                                }

                                $smarty = new CSmartyDP("modules/dPsalleOp");
                                $smarty->assign("icon", CPrescription::$images[$_line->_chapitre]);
                                $smarty->assign("line", $_line);
                                $smarty->assign("type", $type);
                                $smarty->assign("view", $_view);
                                $smarty->assign("print", $print);
                                $smarty->assign("debit_total", $debit_total);
                                $smarty->assign("total_cumul", $total_cumul);
                                $smarty->assign("total_quantity_administration", $total_quantity_administration);
                                $smarty->assign('total_cumul_massique', $total_cumul_massique);
                                $smarty->assign(
                                    "total_quantity_administration_unit",
                                    $total_quantity_administration_unit
                                );
                                $smarty->assign("interv", $interv);
                                $smarty->assign("readonly", $readonly);
                                $smarty->assign('can_adm', CPrescription::canAdmPerop());
                                $smarty->assign('counter_line_mix_items', $counter_line_mix_items);
                                $_content = $smarty->fetch(
                                    "perop/{$tpl_name}.tpl",
                                    '',
                                    '',
                                    0
                                );
                            }
                        } else {
                            $_items_adms         = [];
                            $total_quantity      = 0;
                            $total_quantity_unit = null;

                            foreach ($_line_array["administrations"] as $_adms) {
                                $_adms = CModelObject::naturalSort($_adms, ["dateTime"]);
                                CStoredObject::massLoadFwdRef($_adms, "administrateur_id");


                                foreach ($_adms as $_adm) {
                                    $period_ok = CPrescriptionLine::canAdmLinePerop(
                                        $_line,
                                        $sejour,
                                        $_adm->dateTime,
                                        $datetime_min,
                                        $datetime_max_adm
                                    );

                                    $administrateur = $_adm->loadRefAdministrateur();

                                    $unite    = "";
                                    $quantite = $_adm->quantite;
                                    if ($_line instanceof CPrescriptionLineMedicament) {
                                        $_line->_ref_produit->updateRatioMassique();

                                        [$unite_lt, $qte_lt] = CPrescriptionLineMedicament::computeQteUnitLTPerop(
                                            $_line,
                                            $quantite
                                        );

                                        if ($unite_lt && $qte_lt) {
                                            $unite    = $unite_lt;
                                            $quantite = $qte_lt;
                                        } elseif ($_line->_ref_produit->_ratio_mg && !$is_B05) {
                                            $unite    = "mg";
                                            $quantite = round($quantite / $_line->_ref_produit->_ratio_mg, 2);
                                        } elseif ($_line->_ref_produit->_ratio_microg && !$is_B05) {
                                            $unite    = "µg";
                                            $quantite = round($quantite / $_line->_ref_produit->_ratio_microg, 2);
                                        } elseif ($_line->_ref_produit->_ratio_UI && !$is_B05) {
                                            $unite    = "UI";
                                            $quantite = round($quantite / $_line->_ref_produit->_ratio_UI, 2);
                                        } else {
                                            $unite = $_line->_unite_reference_libelle;
                                        }
                                    } elseif ($_line instanceof CPrescriptionLineElement) {
                                        $unite = $_line->_unite_prise;
                                    }

                                    if ($_line instanceof CPrescriptionLineElement && !$quantite) {
                                        // No posologie for an CPrescriptionLineElement (quantity = 1)
                                        $quantite = 1;
                                    }

                                    if ($readonly || ($_adm->quantite == 0)) {
                                        $_content = "<strong>$quantite $unite</strong>";
                                        $_content .= ($_adm->quantite == 0) ? "<br /> <span style='background-color: red;'>(" . CAppUI::tr(
                                                "CAdministration-Administration canceled"
                                            ) . ")</span>" : "";
                                    } else {
                                        $_content = sprintf(
                                            '<strong><span onmouseover="ObjectTooltip.createEx(this, \'%s\');">%s</span></strong>',
                                            $_adm->_guid,
                                            $quantite . " " . $unite
                                        );
                                    }

                                    if ($_line instanceof CPrescriptionLineMedicament && $_line->voie && $_adm->quantite > 0) {
                                        $voie_label = CMedicamentProduit::getLibelleVoie($_line->voie);
                                        $_content   .= " <strong>($voie_label)</strong>";
                                    }

                                    $class_planif_color = "planif_success";
                                    $content_light      = "$quantite $unite";

                                    if ($_adm->quantite == 0) {
                                        $class_planif_color = "planif_cancel";
                                        $content_light      .= " <span style='background-color: red;'>(" . CAppUI::tr(
                                                "CAdministration-Administration canceled"
                                            ) . ")</span>";
                                    }

                                    $where_segment   = [];
                                    $where_segment[] = "(debut <= '$_adm->dateTime' && fin >= '$_adm->dateTime') OR (debut <= '$_adm->dateTime' && fin IS NULL)";

                                    $segments     = $_line->loadRefsSegments($where_segment);
                                    $main_segment = reset($segments);

                                    if ($_adm->quantite) {
                                        $_item                        = $_subitem;
                                        $_item["id"]                  = $_adm->_guid;
                                        $_item["content"]             = $_content;
                                        $_item["content_light"]       = $content_light;
                                        $_item["start"]               = CMbDT::toTimestamp($_adm->dateTime);
                                        $_item["type"]                = "point";
                                        $_item["editable"]            = (($current_user->_id == $administrateur->_id) && !$readonly && $period_ok) ? true : false;
                                        $_item["datetime"]            = $_adm->dateTime;
                                        $_item["period_ok"]           = $period_ok;
                                        $_item["period_datetime_min"] = $datetime_min;
                                        $_item["period_datetime_max"] = $datetime_max_adm;
                                        $_item["line_id"]             = $_line->_id;
                                        $_item["conditionnel"]        = $_line->conditionnel;
                                        $_item["debut_seg"]           = $main_segment && $main_segment->debut ? $main_segment->debut : null;
                                        $_item["fin_seg"]             = $main_segment && $main_segment->fin ? $main_segment->fin : null;
                                        $_item["quantite"]            = $_adm->quantite;
                                        $_item["prise_id"]            = $_adm->prise_id;
                                        $_item["user_id"]             = $_adm->administrateur_id;
                                        $_item["administrateur"]      = $administrateur;
                                        $_item["className"]           = "$class_planif_color";
                                        $_item["style"]               = "z-index: 2;";
                                        $_item['subgroup']            = 'administration';
                                        $items[]                      = $_item;
                                    }

                                    // Stockage des items d'administration
                                    $_items_adms[$_adm->dateTime][$_adm->prise_id] = true;

                                    $total_quantity += $quantite;
                                }

                                if ($total_quantity) {
                                    $total_quantity_unit = $total_quantity . " " . $unite;
                                }
                            }

                            // Detail tooltip for total
                            $detail_total = [];
                            foreach ($items as $_item) {
                                if (isset($_item["line_id"]) && $_item["line_id"] == $_line->_id) {
                                    $detail_total[$_line->_id][] = $_item;
                                }
                            }

                            $where_planif = [];
                            if (!CAppUI::pref('show_all_datas_surveillance_timeline') || $print) {
                                $where_planif["dateTime"] = "BETWEEN '$datetime_min' AND '$datetime_max'";
                            }

                            $planif_color = [];

                            // Suppression des planif systeme qui ont une planif manuelle associée
                            $where = [
                                "object_class"      => " = '$_line->_class'",
                                "object_id"         => " = '$_line->_id'",
                                "planification"     => " = '1'",
                                "original_dateTime" => "IS NOT NULL",
                            ];

                            $_administration          = new CAdministration();
                            $_planification_manuelles = $_administration->loadList($where);

                            /** @var CPlanificationSysteme[] $_planifications */
                            $_planifications = $_line->loadBackRefs(
                                "planifications",
                                null,
                                null,
                                null,
                                null,
                                null,
                                "",
                                $where_planif
                            );

                            if ($pack->planif_display_mode === "token"
                                && ($_line->signee || (!$signature_perop_obligatoire && $current_user->isIADE()))
                                && !$print
                            ) {
                                foreach ($_planifications as $_planif_id => $_planif) {
                                    if (array_key_exists($_planif->dateTime, $_items_adms) && array_keys(
                                            $_items_adms[$_planif->dateTime]
                                        )[0] == $_planif->prise_id) {
                                        unset($_planifications[$_planif_id]);
                                    }

                                    if (isset($orig_dates[$_planif->dateTime])) {
                                        unset($_planifications[$_planif_id]);
                                    }

                                    $planif_color[$_planif_id] = "planif_ok";

                                    if ($_planif->dateTime == CMbDT::dateTime()) {
                                        $planif_color[$_planif_id] = "planif_success";
                                    } elseif ($_planif->dateTime < CMbDT::dateTime()) {
                                        $planif_color[$_planif_id] = "planif_late";
                                    }
                                }

                                $_planifications                = array_slice($_planifications, 0, 10);
                                $_line->_back["planifications"] = $_planifications;
                            }

                            if ($pack->planif_display_mode === "in_place"
                                && CPrescription::canAdmPerop()
                                && ($_line->signee || (!$signature_perop_obligatoire && $current_user->isIADE()))
                                && !$print
                            ) {
                                $orig_dates = array_flip(
                                    CMbArray::pluck($_planification_manuelles, "original_dateTime")
                                );

                                foreach ($_planifications as $_planif_id => $_planif) {
                                    if (array_key_exists($_planif->dateTime, $_items_adms) && array_keys(
                                            $_items_adms[$_planif->dateTime]
                                        )[0] == $_planif->prise_id) {
                                        unset($_planifications[$_planif_id]);
                                    }

                                    if (isset($orig_dates[$_planif->dateTime])) {
                                        $_planif_manuelle = $_planification_manuelles[$orig_dates[$_planif->dateTime]];

                                        $_planif->dateTime           = $_planif_manuelle->dateTime;
                                        $_planif->_administration_id = $_planif_manuelle->_id;
                                        $_planif->_original_dateTime = $_planif_manuelle->original_dateTime;
                                    }
                                }

                                /** @var CPlanificationSysteme[] $_planifications */
                                foreach ($_planifications as $_planification) {
                                    if (array_key_exists($_planification->dateTime, $_items_adms) && array_keys(
                                            $_items_adms[$_planification->dateTime]
                                        )[0] == $_planification->prise_id) {
                                        continue;
                                    }

                                    $period_ok = CPrescriptionLine::canAdmLinePerop(
                                        $_line,
                                        $sejour,
                                        $_planification->dateTime,
                                        $datetime_min,
                                        $datetime_max_adm
                                    );

                                    $_planification->loadTargetObject();
                                    $unite         = "";
                                    $code_cis      = "";
                                    $quantite      = $_planification->getQuantiteAdministrable();
                                    $quantite_view = $quantite;

                                    if ($_line instanceof CPrescriptionLineMedicament) {
                                        $_line->_ref_produit->updateRatioMassique();
                                        $unite = $_line->_unite_reference_libelle;

                                        [$unite_lt, $qte_lt] = CPrescriptionLineMedicament::computeQteUnitLTPerop(
                                            $_line,
                                            $quantite
                                        );

                                        if ($unite_lt && $qte_lt) {
                                            $unite         = $unite_lt;
                                            $quantite_view = $qte_lt;
                                        } elseif ($_line->_ref_produit->_ratio_mg && !$is_B05) {
                                            $unite         = "mg";
                                            $quantite_view = round($quantite / $_line->_ref_produit->_ratio_mg, 2);
                                        } elseif ($_line->_ref_produit->_ratio_UI && !$is_B05) {
                                            $unite         = "UI";
                                            $quantite_view = round($quantite / $_line->_ref_produit->_ratio_UI, 2);
                                        }

                                        $code_cis = $_planification->_ref_object->code_cis;
                                    }
                                    if ($_line instanceof CPrescriptionLineMedicament && !$quantite_view) {
                                        $unite         = $_planification->_ref_object->_unite_reference_libelle;
                                        $code_cis      = $_planification->_ref_object->code_cis;
                                        $quantite_view = $quantite;
                                    } elseif ($_line instanceof CPrescriptionLineElement) {
                                        $unite = $_line->_unite_prise;
                                    }

                                    $class_planif_color = "planif_ok";

                                    if ($_planification->dateTime == CMbDT::dateTime()) {
                                        $class_planif_color = "planif_success";
                                    } elseif ($_planification->dateTime < CMbDT::dateTime()) {
                                        $class_planif_color = "planif_late";
                                    }

                                    $store_administration = CPrescriptionLine::canAdmLinePerop(
                                        $_line,
                                        $sejour,
                                        $_planification->dateTime,
                                        $datetime_min,
                                        $datetime_max_adm
                                    );

                                    $content = "$quantite_view $unite";

                                    if ($store_administration && !$readonly && $period_ok && !$print) {
                                        $button_id = "admin_$_line->_guid";
                                        $content   .= sprintf(
                                            ' <button id="%s" type="button" class="tick me-tertiary oneclick me-small" 
                                onclick="SurveillancePerop.storeAdministration(\'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\');" 
                                title="%s"></button>',
                                            $button_id,
                                            $_planification->dateTime,
                                            $quantite,
                                            $_planification->prise_id,
                                            $current_user->_id,
                                            $_line->_guid,
                                            $type,
                                            CAppUI::tr('CPrescription-action-Administer')
                                        );
                                    }

                                    $_item                           = $_subitem;
                                    $_item["id"]                     = $_planification->_guid;
                                    $_item["datetime"]               = $_planification->dateTime;
                                    $_item["period_ok"]              = $period_ok;
                                    $_item["period_datetime_min"]    = $datetime_min;
                                    $_item["period_datetime_max"]    = $datetime_max_adm;
                                    $_item["prise_id"]               = $_planification->prise_id;
                                    $_item["administration_id"]      = $_planification->_administration_id;
                                    $_item["user_id"]                = $current_user->_id;
                                    $_item["code_cis"]               = $code_cis;
                                    $_item["quantite"]               = $quantite;
                                    $_item["content"]                = $content;
                                    $_item["start"]                  = CMbDT::toTimestamp($_planification->dateTime);
                                    $_item["type"]                   = "point";
                                    $_item["className"]              = "planif_systeme $class_planif_color";
                                    $_item["editable"]["updateTime"] = !$readonly && $period_ok ? true : false;
                                    $_item["editable"]["remove"]     = true;
                                    $_item["editable"]               = !$readonly && $period_ok ? true : false;
                                    $_item["object_type"]            = "CPlanificationSysteme";
                                    $_item["line_id"]                = $_line->_id;
                                    $_item["original_dateTime"]      = $_planification->_original_dateTime;
                                    $_item["style"]                  = "z-index: 2;";
                                    if ($_line instanceof CPrescriptionLineElement) {
                                        $_item["unite"]     = $_line->_chapitre;
                                        $_item["line_guid"] = $_line->_guid;
                                    }
                                    $items[] = $_item;
                                }
                            }

                            if ($_line->conditionnel) {
                                if (!$_line->_current_active) {
                                    $class_bg_conditionnelle = "hatching opacity-60";
                                }
                                $counter = count($_line->_ref_segments);

                                foreach ($_line->loadRefsSegments() as $_segment) {
                                    $_item             = $_subitem;
                                    $_item["id"]       = $_segment->_guid;
                                    $_item["datetime"] = $_segment->debut;
                                    $_item["content"]  = "Segment $counter";
                                    $_item["user_id"]  = $current_user->_id;
                                    $_item["start"]    = CMbDT::toTimestamp($_segment->debut);
                                    $_item["end"]      = CMbDT::toTimestamp($_segment->fin ?: $sejour->sortie);
                                    $_item["type"]     = "background";
                                    $_item["style"]    = "background-color: #f5f0e7; z-index: 1;";
                                    $items[]           = $_item;

                                    $counter--;
                                }
                            }

                            $tpl_name = "inc_timeline_group_{$_line->_class}";

                            if (self::$limited_view_datas) {
                                $tpl_name .= "_light";
                            }

                            $smarty = new CSmartyDP("modules/dPsalleOp");
                            $smarty->assign("icon", CPrescription::$images[$_line->_chapitre]);
                            $smarty->assign("line", $_line);
                            $smarty->assign("type", $type);
                            $smarty->assign("view", $_view);
                            $smarty->assign("print", $print);
                            $smarty->assign("display_mode", $pack->planif_display_mode);
                            $smarty->assign("total_view", $total_quantity_unit);
                            $smarty->assign("detail_total", $detail_total);
                            $smarty->assign("interv", $interv);
                            $smarty->assign("datetime", CMbDT::dateTime());
                            $smarty->assign("planif_color", $planif_color);
                            $smarty->assign("can_adm", CPrescription::canAdmPerop());
                            $smarty->assign("readonly", $readonly);
                            $smarty->assign('limited_view_datas', self::$limited_view_datas);
                            $_content = $smarty->fetch("perop/{$tpl_name}.tpl", '', '', 0);
                        }

                        $class_bg_chevron = null;
                        if (!$class_bg_conditionnelle) {
                            $class_bg_chevron = "bg_chevron";
                        }

                        // ClassName for status line
                        $status_line = "";

                        if ($_line->premedication) {
                            $status_line = "premedication";
                        }
                        if ($_line->highlight) {
                            $status_line = "highlight_red";
                        }

                        $line_finished = '';

                        if ($_line->_fin_reelle < CMbDT::dateTime()) {
                            $line_finished = 'hatching';
                        }

                        $classname_sup = "$class_bg_conditionnelle $class_bg_chevron $status_line $line_finished";

                        $groups[$_subkey] = [
                            "id"            => $_subkey,
                            "className"     => "timeline-$_line->_class timeline-$_line->_guid $classname_sup",
                            "content"       => $_content,
                            "code_cis"      => $code_cis,
                            "line_guid"     => $_line->_guid,
                            "order"         => $order_i++,
                            'subgroupStack' => true,
                        ];
                    }
                }
            }
        }

        return [
            array_values($groups),
            $items,
        ];
    }

    /**
     * Get the best min and max timings for a context
     *
     * @param COperation $interv
     * @param string     $type
     * @param string     $print
     *
     * @return array
     */
    static function getTimingsByType(COperation $interv, $type, $print = false)
    {
        switch ($type) {
            case "preop":
                $datetime_min = CValue::first(
                    $interv->entree_bloc,
                    $interv->debut_prepa_preop,
                    CMbDT::dateTime(!$print ? "-1 HOUR" : null, $interv->_datetime)
                );
                $datetime_max = CValue::first(
                    $interv->entree_salle,
                    $interv->fin_prepa_preop,
                    $print ? CMbDT::dateTime("+1 HOUR", $datetime_min) : $interv->_ref_sejour->sortie
                );
                break;
            case "sspi":
                $datetime_min = $interv->entree_reveil ?: CMbDT::dateTime(
                    !$print ? "-1 HOUR" : null,
                    $interv->_datetime
                );
                $datetime_max = CValue::first(
                    $interv->sortie_reveil_reel,
                    $interv->sortie_reveil_possible,
                    $print ? CMbDT::dateTime("+8 HOUR", $datetime_min) : $interv->_ref_sejour->sortie
                );
                break;
            default:
                $datetime_min = $interv->entree_salle ?: CMbDT::dateTime(
                    !$print ? "-1 HOUR" : null,
                    $interv->_datetime
                );
                $datetime_max = CValue::first(
                    $interv->sortie_sans_sspi,
                    $interv->sortie_salle,
                    $interv->fin_op,
                    $print ? CMbDT::dateTime(
                        "+4 HOUR",
                        CMbDT::addDateTime($interv->temp_operation, $datetime_min)
                    ) : $interv->_ref_sejour->sortie
                );
                break;
        }

        // Cas du partogramme
        $sejour = $interv->loadRefSejour();

        if ($sejour->grossesse_id) {
            $grossesse = $sejour->loadRefGrossesse();

            if ($type === "sspi") {
                // Début
                $datetime_min = CValue::first(
                    $grossesse->datetime_debut_surv_post_partum,
                    $grossesse->datetime_accouchement,
                    CMbDT::dateTime(!$print ? "-1 HOUR" : null, $interv->_datetime)
                );

                // Fin
                if ($grossesse->datetime_fin_surv_post_partum) {
                    $datetime_max = $grossesse->datetime_fin_surv_post_partum;
                } else {
                    $datetime_max = max(CMbDT::dateTime(), CMbDT::dateTime("+1 HOUR", $datetime_min));
                }
            } else {
                // Début
                if ($grossesse->datetime_debut_travail) {
                    $datetime_min = $grossesse->datetime_debut_travail;
                } else {
                    $min_fields   = [
                        "entree_salle",
                        "induction_debut",
                        "debut_op",
                        "_datetime_best",
                    ];
                    $datetime_min = self::getMinTiming(
                        $interv,
                        $min_fields,
                        CMbDT::dateTime(!$print ? "-1 HOUR" : null, $interv->_datetime),
                        $type
                    );
                }

                // Fin
                if ($grossesse->datetime_accouchement) {
                    $datetime_max = $print ? CMbDT::dateTime(
                        "+1 HOUR",
                        $grossesse->datetime_accouchement
                    ) : $grossesse->datetime_accouchement;
                } else {
                    $datetime_max = max(
                        CMbDT::dateTime("+4 HOUR", $interv->_datetime),
                        CMbDT::dateTime("+4 HOUR", $datetime_min)
                    );
                }
            }
        }

        return [$datetime_min, $datetime_max];
    }

    /**
     * Get the background item for timeline vis.js
     *
     * @param array $subitem    Sub item
     * @param array $item_datas Item datas
     *
     * @return array
     */
    static function getBackgroundItem($subitem, $item_datas)
    {
        $_item             = $subitem;
        $_item["id"]       = $item_datas["id"];
        $_item["datetime"] = $item_datas["datetime"];
        $_item["content"]  = $item_datas["content"];
        $_item["user_id"]  = $item_datas["user_id"];
        $_item["start"]    = $item_datas["start"];
        $_item["end"]      = $item_datas["end"];
        $_item["type"]     = "background";
        $_item["style"]    = $item_datas["style"];

        return $_item;
    }
}
