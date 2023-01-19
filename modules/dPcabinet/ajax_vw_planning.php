<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Cabinet\PlanningConsultService;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Notifications\CNotificationEvent;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Personnel\CPlageConge;
use Ox\Mediboard\System\CPlanningEvent;
use Ox\Mediboard\System\CPlanningRange;
use Ox\Mediboard\System\CPlanningWeek;

CCanDo::checkRead();
$chirSel     = CValue::getOrSession("chirSel");
$function_id = CValue::get("function_id");
$today       = CMbDT::date();

$show_free      = CValue::get("show_free");
$show_cancelled = CView::get("show_cancelled", "bool default|0", true);
$facturated     = CValue::get("facturated");
$status         = CValue::get("status");
$actes          = CValue::get("actes");
$hide_in_conge  = CValue::get("hide_in_conge", 0);
$type_view      = CValue::getOrSession("type_view", null);
$print          = CValue::get("print", 0);
$scroll_top     = CView::get("scroll_top", "num default|0");
$debut          = CValue::getOrSession("debut");
CView::checkin();

$min_hour = 23;

// gathering prat ids
$ids      = [];
$function = new CFunctions();
$function->load($function_id);
if ($function->_id) {
    $function->loadRefsUsers();
    foreach ($function->_ref_users as $_user) {
        $ids[] = $_user->_id;
    }
}

if (!$function_id && $chirSel) {
    $ids[] = $chirSel;
}

// Nombre de visites à domicile
$nb_visite_domicile = 0;
$chir               = CMediusers::get($chirSel);

// Liste des consultations a avancer si desistement
//TODO a vérifier comportement
$count_si_desistement = CConsultation::countDesistementsForDay($ids, $today);

$is_draggable = true;

// Période
$debut = CMbDT::date("last sunday", $debut);
$fin   = CMbDT::date("next sunday", $debut);
$debut = CMbDT::date("+1 day", $debut);

$prev = CMbDT::date("-1 week", $debut);
$next = CMbDT::date("+1 week", $debut);

$dateArr   = CMbDT::date("+6 day", $debut);
$nbDays    = 7;
$listPlage = new CPlageconsult();

$where         = [];
$where["date"] = "= '$dateArr'";
$whereChir     = $chir->getUserSQLClause();
$where[]       = "chir_id $whereChir OR remplacant_id $whereChir";

if (!$listPlage->countList($where)) {
    $nbDays--;
    // Aucune plage le dimanche, on peut donc tester le samedi.
    $dateArr       = CMbDT::date("+5 day", $debut);
    $where["date"] = "= '$dateArr'";
    if (!$listPlage->countList($where)) {
        $nbDays--;
    }
}

$bank_holidays = array_merge(CMbDT::getHolidays($debut), CMbDT::getHolidays($fin));

// Planning Week
$planning = new CPlanningWeek($debut, $debut, $fin, $nbDays, false, $print ? "1000" : "auto");

$user = new CMediusers();
$user->load($chirSel);
$see_notification = CModule::getActive("smsProviders") && $chirSel
    && CNotificationEvent::userHasNotificationEvents($user);
if ($user->_id) {
    $user->loadRefFunction();
    $planning->title = $user->_view;
} else {
    $planning->title = "";
}

$can_edit = CCanDo::edit();

$planning->guid               = $user->_guid;
$planning->hour_min           = "07";
$planning->hour_max           = "20";
$planning->pauses             = ["07", "12", "19"];
$planning->dragndrop          = $planning->resizable = $can_edit ? 1 : 0;
$planning->hour_divider       = 60 / CAppUI::gconf('dPcabinet CPlageconsult minutes_interval');
$planning->no_dates           = 0;
$planning->reduce_empty_lines = 1;

$plage = new CPlageconsult();

$users      = [];
$conges_day = [];
if ($user->_id) {
    $muser = new CMediusers();
    $users = $muser->loadUsers(PERM_READ, $user->function_id);
}

$planning_consult_service = new PlanningConsultService($debut, $fin, $chirSel, $status, $facturated, $actes, $show_cancelled);
$contents_by_date = $planning_consult_service->getContentsByDate();

for ($i = 0; $i < $nbDays; $i++) {
    $jour       = CMbDT::date("+$i day", $debut);
    $is_holiday = array_key_exists($jour, $bank_holidays);

    $planning->addDayLabel($jour, '<span style="font-size: 1.4em">' . CMbDT::format($jour, "%a %d %b") . '</span>');

    // conges dans le header
    if (count($users)) {
        if (CModule::getActive("dPpersonnel")) {
            $_conges = CPlageConge::loadForIdsForDate(array_keys($users), $jour);
            foreach ($_conges as $key => $_conge) {
                $_conge->loadRefUser();
                $conges_day[$i][] = $_conge->_ref_user->_shortview;
            }
        }
    }

    //INTERVENTIONS
    $intervs = $contents_by_date[$jour]["plage_op"];
    foreach ($intervs as $_interv) {
        $range = new CPlanningRange(
            $_interv->_guid,
            $jour . " " . $_interv->debut,
            CMbDT::minutesRelative($_interv->debut, $_interv->fin),
            CAppUI::tr($_interv->_class),
            "bbccee",
            "plageop"
        );
        $planning->addRange($range);
    }

    //HORS PLAGE
    $horsPlages = $contents_by_date[$jour]["interv_hors_plage"];
    foreach ($horsPlages as $_horsplage) {
        $op = new CPlanningRange(
            $_horsplage->_guid,
            $jour . " " . $_horsplage->time_operation,
            (CMBDT::minutesRelative("00:00:00", $_horsplage->temp_operation)),
            $_horsplage->_view,
            "3c75ea",
            "horsplage"
        );
        $planning->addRange($op);
    }

    // PLAGES CONGE
    $is_conge = false;
    $conges   = $contents_by_date[$jour]["conges"];
    foreach ($conges as $_conge) {
        $libelle = '<h3 style="text-align: center">
        ' . CAppUI::tr("CPlageConge|pl") . '</h3>
        <p style="text-align: center">' . $_conge->libelle . '</p>';

        $_date = $_conge->date_debut;

        while ($_date < $_conge->date_fin) {
            $event        = new CPlanningEvent(
                $_conge->_guid . $_date,
                $_date,
                CMbDT::minutesRelative($_date, $_conge->date_fin),
                $libelle,
                "#ddd",
                true,
                "hatching",
                null,
                false
            );
            $event->below = 1;
            $planning->addEvent($event);
            $_date = CMbDT::dateTime("+1 DAY", CMbDT::date($_date));
        }
    }

    //PLAGES CONSULT

    // férié & pref
    if ($is_holiday && !CAppUI::pref("show_plage_holiday")) {
        continue;
    }

    // conges
    if ($is_conge && $hide_in_conge) {
        continue;
    }

    $plages = $contents_by_date[$jour]["plage_consult"];
    /** @var CPlageconsult $_plage */
    foreach ($plages as $_plage) {
        if (CMbDT::format($_plage->debut, "%H") < $min_hour) {
            $min_hour = CMbDT::format($_plage->debut, "%H");
        }

        // Affichage de la plage sur le planning
        $range = new CPlanningRange(
            $_plage->_guid,
            $jour . " " . $_plage->debut,
            CMbDT::minutesRelative($_plage->debut, $_plage->fin),
            $_plage->libelle,
            $_plage->color
        );

        if ($_plage->_ref_agenda_praticien->sync) {
            $range->icon      = "fas fa-sync-alt";
            $range->icon_desc = CAppUI::tr("CAgendaPraticien-sync-desc");
            $is_draggable     = false;
            if (!CMediusers::get()->isAdmin()) {
                $range->disabled = true;
            }
        }

        $range->type = "plageconsult";
        $planning->addRange($range);

        //RdvFree
        if ($show_free) {
            foreach ($_plage->_ref_slots as $_slot) {
                if ($_slot->status != "free") {
                    continue;
                }
                $event              = new CPlanningEvent(
                    "$_slot->start",
                    "$_slot->start",
                    $_plage->_freq,
                    "",
                    $_plage->_color_planning,
                    true,
                    "droppable",
                    null
                );
                $event->type        = "rdvfree$type_view";
                $event->plage["id"] = $_plage->_id;
                if ($_plage->locked == 1) {
                    $event->disabled = true;
                }
                $event->plage["color"] = $_plage->color;
                if ($_plage->_ref_agenda_praticien->sync) {
                    $event->disabled = true;
                }
                $event->datas = ["meeting_id" => "", "pause" => "0"];
                //Ajout de l'évènement au planning
                $planning->addEvent($event);
            }
        }

        //consultations
        $consultations = $contents_by_date[$jour]["consults"];

        /** @var CConsultation $_consultation */
        foreach ($consultations as $_consultation) {
            if ($_consultation->plageconsult_id != $_plage->_id) {
                continue;
            }
            if ($_consultation->heure < $min_hour) {
                $min_hour = CMbDT::format($_consultation->heure, "%H");
            }

            $consult_termine = ($_consultation->chrono == CConsultation::TERMINE) ? "hatching" : "";

            $debute = "$jour $_consultation->heure";
            $motif  = $_consultation->motif;
            if ($_consultation->patient_id) {
                $style = "";
                if ($_consultation->annule) {
                    $style .= "text-decoration:line-through;";
                }

                $title = "";
                if ($_consultation->_consult_sejour_out_of_nb) {
                    $nb    = $_consultation->_consult_sejour_nb;
                    $of    = $_consultation->_consult_sejour_out_of_nb;
                    $title .= "<span style=\"float:right;\">$nb / $of</span>";
                }

                if ($_consultation->visite_domicile) {
                    $title .= "<i class=\"fa fa-home\" style=\"font-size: 1.2em;\" " .
                        "title=\"" . CAppUI::tr("CConsultation-visite_domicile-desc") . "\"></i> ";
                    $nb_visite_domicile++;
                }

                if ($_consultation->_alert_docs) {
                    if ($_consultation->_count["alert_docs"] == $_consultation->_count["locked_alert_docs"]) {
                        $title .= "<i class=\"far fa-file\" " .
                            "style=\"float: right; font-size: 1.3em;color:green;background-color:lightgreen;" . "\" " .
                            "title=\"" . CAppUI::tr("CCompteRendu-alert_locked_docs_object.all") . "\"></i>";
                    } else {
                        $title .= "<i class=\"far fa-file\" style=\"float: right; font-size: 1.3em;" . "\" " .
                            "title=\"" . CAppUI::tr("CCompteRendu-alert_docs_object") . "\"></i>";
                    }
                }

                $title .= "<i class=\"";
                if (
                    ($_consultation->_ref_facture
                    && $_consultation->_ref_facture->_id
                    && !$_consultation->_ref_facture->annule)
                    || $_consultation->facture
                ) {
                    $title .= "texticon texticon-ok\" title = \"" . CAppUI::tr("CConsultation-has_facture");
                } else {
                    $title .= "texticon texticon-gray\" title = \"" . CAppUI::tr("CConsultation-has_no_facture");
                }

                $title .= "\" style=\"float:right\">F</i>";

                if ($_consultation->teleconsultation) {
                    $_room = $_consultation->_ref_room;
                    if ($_room->active) {
                        if ($_consultation->arrivee) {
                            $title_attr = CAppUI::tr("CConsultation.teleconsultation-ENCOURS");
                            $color      = "me-success";
                        } else {
                            $title_attr = CAppUI::tr("CConsultation.teleconsultation-ENCOURS");
                            $color      = "me-warning";
                        }
                    } else {
                        if ($_consultation->arrivee) {
                            if ($_room->fin) {
                                $title_attr = CAppUI::tr("CConsultation.teleconsultation-TERMINEE");
                                $color      = "me-grey";
                            } else {
                                $title_attr = CAppUI::tr("CConsultation.teleconsultation-ATTENTE") . " "
                                    . CMbDT::format($_consultation->arrivee, "%Hh%M");
                                $color      = "me-warning";
                            }
                        } else {
                            $title_attr = CAppUI::tr("CConsultation.teleconsultation-OPEN-i");
                            $color      = "me-grey";
                        }
                    }
                    $title .= "<i class='fas fa-video me-icon " . $color . "' title='" . $title_attr . "' ></i></br>";
                }

                // Display resources
                $res_title = "";
                foreach ($_consultation->_ref_reserved_ressources as $_reserved) {
                    $resource = $_reserved->_ref_plage_ressource->_ref_ressource;

                    $res_title .= '<span class="texticon me-margin-2" style="color: #' . $resource->color . '; ' .
                        'border: 1px solid #' . $resource->color . ';">';
                    $res_title .= $resource->libelle;
                    $res_title .= '</span>';
                }

                //Ajout du cartouche de DHE dans le nouveau semainier
                $consult_anesth = $_consultation->_ref_consult_anesth;
                if ($consult_anesth && $consult_anesth->_id && $_consultation->_etat_dhe_anesth) {
                    if ($_consultation->_etat_dhe_anesth == "associe") {
                        $title .= "<span class=\"texticon texticon-allergies-ok\"";
                        $title .= "title=\"" . CAppUI::tr(
                                "CConsultation-_etat_dhe_anesth-associe"
                            ) . "\" style=\"float: right;\">";
                        $title .= CAppUI::tr("COperation-event-dhe") . "</span>";
                    } elseif ($_consultation->_etat_dhe_anesth == "dhe_exist") {
                        $title .= "<span class=\"texticon texticon-atcd\"";
                        $title .= "title=\"" . CAppUI::tr(
                                "CConsultation-_etat_dhe_anesth-dhe_exist"
                            ) . "\" style=\"float: right;\">";
                        $title .= CAppUI::tr("COperation-event-dhe") . "</span>";
                    } elseif ($_consultation->_etat_dhe_anesth == "non_associe") {
                        $title .= "<span class=\"texticon texticon-stup texticon-stroke\"";
                        $title .= "title=\"" . CAppUI::tr(
                                "CConsultation-_etat_dhe_anesth-non_associe"
                            ) . "\" style=\"float: right;\">";
                        $title .= CAppUI::tr("COperation-event-dhe") . "</span>";
                    }
                }

                if ($see_notification && CNotificationEvent::displayNotificationsForPatient(
                        $_consultation->_ref_patient
                    )) {
                    $title .= $_consultation->smsPlanning();
                }

                $title .= "<span style=\"$style\">";
                $title .= $_consultation->_ref_patient->_view . "\n" . $motif;
                $title .= "</span>";

                if ($_consultation->adresse_par_prat_id) {
                    $medecin = $_consultation->_ref_adresse_par_prat;
                    $title   .= "<span class='compact'>" . "Adressé par Dr $medecin->nom $medecin->prenom" . "</span>";
                }

                $title               .= $res_title;
                $event               = new CPlanningEvent(
                    $_consultation->_guid,
                    $debute,
                    $_consultation->duree * $_plage->_freq,
                    $title,
                    $_consultation->_color_planning,
                    true,
                    "droppable $debute",
                    $_consultation->_guid,
                    false
                );
                $event->border_color = $_consultation->_ref_categorie->couleur;
                $event->border_title = $_consultation->_ref_categorie->nom_categorie;
            } else {
                $title_consult = CAppUI::tr("CConsultation-PAUSE");

                if ($_consultation->groupee && $_consultation->no_patient) {
                    $title_consult = CAppUI::tr("CConsultation-MEETING");
                }

                $title = "[" . $title_consult . "] $motif";

                $event               = new CPlanningEvent(
                    $_consultation->_guid,
                    $debute,
                    $_consultation->duree * $_plage->_freq,
                    $title,
                    $_consultation->_color_planning,
                    true,
                    "droppable $debute",
                    $_consultation->_guid,
                    false
                );
                $event->border_color = $_consultation->_ref_categorie->couleur;
                $event->border_title = $_consultation->_ref_categorie->nom_categorie;
            }
            $event->type                    = "rdvfull$type_view";
            $event->plage["id"]             = $_plage->_id;
            $event->plage["consult_id"]     = $_consultation->_id;
            $event->plage["patient_id"]     = $_consultation->patient_id;
            $event->plage["patient_status"] = $_consultation->loadRefPatient()->status;
            if ($_plage->locked == 1) {
                $event->disabled = true;
            }

            if ($_consultation->categorie_id) {
                $event->icon      = "./modules/dPcabinet/images/categories/" . $_consultation->_ref_categorie->nom_icone;
                $event->icon_desc = $_consultation->_ref_categorie->nom_categorie;
            }
            if ($_consultation->_id && !$print) {
                $can_edit = $_consultation->_can->edit;

                if ($is_draggable) {
                    $event->draggable /*= $event->resizable */ = $can_edit;
                }

                $freq = 1;
                if ($_plage->freq) {
                    $freq = intval(CMbDT::transform($_plage->freq, null, "%H")) * 60 + intval(
                            CMbDT::transform($_plage->freq, null, "%M")
                        );
                }
                $event->hour_divider = 60 / $freq;

                if ($can_edit) {
                    $event->addMenuItem("copy", CAppUI::tr("CConsultation-copy"));
                    $event->addMenuItem("cut", CAppUI::tr("CConsultation-cut"));
                    if ($_consultation->patient_id) {
                        $event->addMenuItem("add", CAppUI::tr("CConsultation-add"));
                        if ($_consultation->chrono == CConsultation::PLANIFIE) {
                            $event->addMenuItem("tick", CAppUI::tr("CConsultation-notify_arrive-court"));
                        }
                        if ($_consultation->chrono == CConsultation::PATIENT_ARRIVE) {
                            $event->addMenuItem("tick_cancel", CAppUI::tr("CConsultation-cancel_arrive"));
                        }
                    }

                    if (
                        $_consultation->chrono != CConsultation::TERMINE
                        && $_consultation->chrono != CConsultation::PATIENT_ARRIVE
                    ) {
                        $event->addMenuItem("cancel", CAppUI::tr("CConsultation-cancel_rdv"));
                    }
                }
            }

            $event->status = $consult_termine;

            $meeting_id   = ($_consultation->_ref_reunion->reunion_id) ? $_consultation->_ref_reunion->reunion_id : '';
            $pause        = ($_consultation->patient_id) ? "0" : "1";
            $event->datas = [
                "meeting_id"     => $meeting_id,
                "pause"          => $pause,
                "patient_id"     => $_consultation->patient_id,
                "patient_status" => $_consultation->_ref_patient->status,
            ];

            //Ajout de l'évènement au planning
            $event->plage["color"] = $_plage->color;
            $event->below          = 0;
            $planning->addEvent($event);
        }
    }
}

if (CAppUI::pref("show_intervention")) {
    $evenement  = new CEvenementPatient();
    $ds         = $evenement->getDS();
    $whereEvent = [
        "praticien_id"       => $ds->prepare("= ?", $chirSel),
        "type"               => $ds->prepare("= 'intervention'"),
        "date"               => $ds->prepareBetween($debut, $fin),
        "date_fin_operation" => $ds->prepare("IS NOT NULL"),
        "cancel"             => $ds->prepare("= ?", '0'),
    ];
    $evenements = $evenement->loadList($whereEvent);

    foreach ($evenements as $_event) {
        $eventPlanning = new CPlanningEvent(
            $_event->_guid,
            $_event->date,
            CMbDT::minutesRelative($_event->date, $_event->date_fin_operation), //$_event->_duration_min_operation,
            $_event->libelle,
            "#f58c84",
        );
        $_event->loadRefPatient();
        $eventPlanning->type        = "rdvfull$type_view";
        $eventPlanning->plage["id"] = null;
        $eventPlanning->datas       = [
            "meeting_id"     => null,
            "pause"          => false,
            "patient_id"     => $_event->_ref_patient->_id,
            "patient_status" => null,
        ];
        $planning->addEvent($eventPlanning);
    }
}

$planning->hour_min = $min_hour;

// conges
foreach ($conges_day as $key => $_day) {
    $conges_day[$key] = implode(", ", $_day);
}

$planning->rearrange(true);

$smarty = new CSmartyDP();
$smarty->assign("planning", $planning);
$smarty->assign("debut", $debut);
$smarty->assign("fin", $fin);
$smarty->assign("prev", $prev);
$smarty->assign("next", $next);
$smarty->assign("chirSel", $chirSel);
$smarty->assign("conges", $conges_day);
$smarty->assign("function_id", $function_id);
$smarty->assign("user", $user);
$smarty->assign("today", $today);
$smarty->assign("height_calendar", CAppUI::pref("height_calendar", "2000"));
$smarty->assign("bank_holidays", $bank_holidays);
$smarty->assign("count_si_desistement", $count_si_desistement);
$smarty->assign("print", $print);
$smarty->assign("nb_visite_dom", $nb_visite_domicile);
$smarty->assign("scroll_top", $scroll_top);
$smarty->assign("show_cancelled", $show_cancelled);
$smarty->display("inc_vw_planning.tpl");
