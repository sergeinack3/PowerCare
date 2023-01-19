<?php

/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanSoins\CPlanificationSysteme;

$service_id = CView::get("service_id", "ref class|CService", true);
$nb_unites  = CView::get("nb_unites", "num default|1", true);
$show_cost  = CView::get("show_cost", "num default|1", true);
$datetime   = CView::get("datetime", "dateTime default|now");
$period     = CView::get("period", "enum list|day|hour|week default|day");
$nb_periods = CView::get("nb_periods", "num default|14 max|30");

CView::checkin();

$datetime  = CMbDT::dirac($period, $datetime);
$datetimes = [];
for ($i = 0; $i < $nb_periods; $i++) {
    $_datetime             = CMbDT::dateTime("+$i $period", $datetime);
    $datetimes[$_datetime] = $_datetime;
}
$tomorrow = CMbDT::dateTime("tomorrow 00:00:00");
$service  = new CService();
$where    = [
    "cancelled" => "= '0'",
];
$services = $service->loadGroupList($where);
$ds       = CSQLDataSource::get('std');

// Chargement des sejours pour le service selectionné
if ($service_id !== null) {
    $sejours     = [];
    $affectation = new CAffectation();

    $datetime_min = CMbDT::dateTime($datetime);
    $datetime_max = CMbDT::dateTime("+$nb_periods $period", $datetime);

    $where = [
        "sortie"                 => ">= '$datetime_min'",
        "entree"                 => "<= '$datetime_max'",
        "affectation.service_id" => $ds->prepare(" = ?", $service_id),
    ];

    $affectations   = $affectation->loadList($where);
    $planifications = [];
    $ressources     = [];


    CMbObject::massLoadFwdRef($affectations, "sejour_id");

    foreach ($affectations as $_affectation) {
        // Chargement du séjour
        $sejour = $_affectation->loadRefSejour(1);
        $sejour->loadRefPatient();
        $sejour->entree        = CMbDT::dirac($period, $sejour->entree);
        $sejour->sortie        = CMbDT::dirac($period, $sejour->sortie);
        $sejours[$sejour->_id] = $sejour;

        // Chargement des planification système
        $planif  = new CPlanificationSysteme();
        $ljoin   = [
            "affectation" => "affectation.sejour_id = planification_systeme.sejour_id",
        ];
        $where   = [
            "planification_systeme.sejour_id" => $ds->prepare(" = ?", $sejour->_id),
            "dateTime"                        => " BETWEEN '$datetime_min' AND '$datetime_max'",
            "affectation.service_id"          => $ds->prepare(" = ?", $service_id),
            "object_class"                    => " = 'CPrescriptionLineElement'",
        ];
        $planifs = $planif->loadList($where, null, null, 'planification_systeme_id', $ljoin);

        // Classement par séjour
        if (!isset($planifications[$sejour->_id])) {
            $planifications[$sejour->_id] = [];
        }

        $planifications[$sejour->_id] += $planifs;
    }
    // Massload line de prescription, éléments de prescription, et indices de coût
    $prescription_line_elements = CStoredObject::massLoadFwdRef($planifs, "object_id");
    $elements_prescription      = CStoredObject::massLoadFwdRef($prescription_line_elements, "element_prescription_id");
    CStoredObject::massLoadBackRefs($elements_prescription, "indices_cout");

    $total_sejour   = [];
    $total_datetime = [];
    $total          = [];
    $charge         = [];

    $charge_realisee         = [];
    $total_sejour_realisee   = [];
    $total_datetime_realisee = [];
    $total_realisee          = [];

    foreach ($datetimes as $_datetime) {
        $total_datetime[$_datetime]          = [];
        $total_datetime_realisee[$_datetime] = [];
    }
    // Parcours des planifications et calcul de la charge
    foreach ($planifications as $_sejour_id => &$_planifs) {
        foreach ($_planifs as &$_planif) {
            $line_element         = $_planif->loadTargetObject();
            $element_prescription = $line_element->_ref_element_prescription;
            $element_prescription->loadRefsIndicesCout();
            $line_element->loadRefsAdministrations();

            if (!count($element_prescription->_ref_indices_cout)) {
                continue;
            }
            if (!isset($charge[$_planif->sejour_id])) {
                // On initialise les objets avec l'id du séjour et la date, prévu et réalisé
                foreach ($datetimes as $_datetime) {
                    $charge[$_planif->sejour_id][$_datetime]          = [];
                    $charge_realisee[$_planif->sejour_id][$_datetime] = [];
                    $total_sejour[$_planif->sejour_id]                = [];
                    $total_sejour_realisee[$_planif->sejour_id]       = [];
                }
            }

            foreach ($element_prescription->_ref_indices_cout as $_indice_cout) {
                $ressource = $_indice_cout->loadRefRessourceSoin();

                $planif_date_time = CMbDT::dirac($period, $_planif->dateTime);

                $ressources[$ressource->_id] = $ressource;
                @$charge[$_planif->sejour_id][$planif_date_time][$ressource->_id] += $_indice_cout->nb;

                @$total_sejour[$_planif->sejour_id][$ressource->_id] += $_indice_cout->nb;
                @$total_datetime[$planif_date_time][$ressource->_id] += $_indice_cout->nb;
                @$total[$ressource->_id] += $_indice_cout->nb;

                //Préparation des éléments pour la charge réalisée
                if (is_array($line_element->_ref_administrations) && ($tomorrow > $planif_date_time)) {
                    if (!isset($charge_realisee[$_planif->sejour_id][$planif_date_time][$ressource->_id])) {
                        $charge_realisee[$_planif->sejour_id][$planif_date_time][$ressource->_id] = 0;
                    }
                    if (!isset($total_sejour_realisee[$_planif->sejour_id][$ressource->_id])) {
                        $total_sejour_realisee[$_planif->sejour_id][$ressource->_id] = 0;
                    }
                    if (!isset($total_datetime_realisee[$planif_date_time][$ressource->_id])) {
                        $total_datetime_realisee[$planif_date_time][$ressource->_id] = 0;
                    }
                    if (!isset($total_realisee[$ressource->_id])) {
                        $total_realisee[$ressource->_id] = 0;
                    }
                }
            }
        }
        // Calcul de la charge réalisée
        if ($_planifs === []) {
            continue;
        }
        $sejour = CSejour::findOrFail($_sejour_id);
        if ($sejour instanceof CSejour && $sejour->_id) {
            $sejour_line_elements = $sejour->loadRefPrescriptionSejour()->loadRefsLinesElement();
            foreach ($sejour_line_elements as $line_element) {
                $line_element->calculAdministrations($datetimes);
                if (!count($line_element->_administrations)) {
                    continue;
                }
                foreach ($line_element->_administrations as $_adm) {
                    foreach ($_adm as $_date => $_hours) {
                        $planif_date_time = CMbDT::dateTime("00:00:00", $_date);
                        if ($tomorrow < $planif_date_time) {
                            continue;
                        }
                        foreach ($_hours as $_hour => $infos) {
                            if ($_hour === "list") {
                                continue;
                            }
                            foreach ($infos['administrations'] as $_admin_hour) {
                                if ($_admin_hour->quantite === "0") {
                                    // On ne prends pas en compte les administration annulées
                                    continue;
                                }
                                $_admin_hour->_ref_object->_ref_element_prescription->loadRefsIndicesCout();
                                $indices_cout = $_admin_hour->_ref_object->_ref_element_prescription->_ref_indices_cout;
                                foreach ($indices_cout as $_indice_cout) {
                                    $_indice_cout->loadRefRessourceSoin();
                                    $ressource = $_indice_cout->_ref_ressource_soin;

                                    $ressources[$ressource->_id] = $ressource;
                                    if (!isset($charge_realisee[$_sejour_id][$planif_date_time][$ressource->_id])) {
                                        $charge_realisee[$_sejour_id][$planif_date_time][$ressource->_id] = 0;
                                    }
                                    if (!isset($total_sejour_realisee[$_sejour_id][$ressource->_id])) {
                                        $total_sejour_realisee[$_sejour_id][$ressource->_id] = 0;
                                    }
                                    if (!isset($total_datetime_realisee[$planif_date_time][$ressource->_id])) {
                                        $total_datetime_realisee[$planif_date_time][$ressource->_id] = 0;
                                    }
                                    if (!isset($total_realisee[$ressource->_id])) {
                                        $total_realisee[$ressource->_id] = 0;
                                    } else {
                                        $charge_realisee[$_sejour_id][$planif_date_time][$ressource->_id] +=
                                            $_indice_cout->nb;

                                        $total_sejour_realisee[$_sejour_id][$ressource->_id] += $_indice_cout->nb;

                                        $total_datetime_realisee[$planif_date_time][$ressource->_id] +=
                                            $_indice_cout->nb;

                                        $total_realisee[$ressource->_id] += $_indice_cout->nb;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

$bank_holidays = CMbDT::getHolidays();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("service_id", $service_id);
$smarty->assign("services", $services);
$smarty->assign("bank_holidays", $bank_holidays);
$smarty->assign("datetimes", $datetimes);
$smarty->assign("datetime", $datetime);
$smarty->assign("nb_periods", $nb_periods);
$smarty->assign("period", $period);
$smarty->assign("nb_unites", $nb_unites);
$smarty->assign("show_cost", $show_cost);
if ($service_id !== null) {
    $smarty->assign("sejours", $sejours);
    $smarty->assign("planifications", $planifications);
    $smarty->assign("ressources", $ressources);
    $smarty->assign("charge", $charge);
    $smarty->assign("total_datetime", $total_datetime);
    $smarty->assign("total_sejour", $total_sejour);
    $smarty->assign("total", $total);
    $smarty->assign("charge_realisee", $charge_realisee);
    $smarty->assign("total_datetime_realisee", $total_datetime_realisee);
    $smarty->assign("total_sejour_realisee", $total_sejour_realisee);
    $smarty->assign("total_realisee", $total_realisee);
}
$smarty->display('vw_ressources_soins');
