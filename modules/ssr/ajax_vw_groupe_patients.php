<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CElementPrescription;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Mediboard\Ssr\CEvenementSSR;
use Ox\Mediboard\Ssr\CExtensionDocumentaireCsARR;
use Ox\Mediboard\Ssr\CPlageGroupePatient;

CCanDo::checkRead();
$plage_groupe_patient_id = CView::get("plage_groupe_patient_id", "ref class|CPlageGroupePatient", true);
$order_way               = CView::get("order_way", "enum list|ASC|DESC default|ASC", true);
$order_col               = CView::get("order_col", "enum list|patient_id|entree default|patient_id", true);
$filter_element_id       = CView::get("filter_element_id", "ref class|CElementPrescription");
$plage_date              = CView::get("plage_date", "date");
CView::checkin();

$plage_groupe_patient = CPlageGroupePatient::findOrFail($plage_groupe_patient_id);

$date                  = CMbDT::date("$plage_groupe_patient->groupe_day this week", $plage_date);
$elements_prescription = $plage_groupe_patient->loadRefElementsPresciption();

$plage_groupe_patient->_date = $date;

$plage_debut = $date . " " . $plage_groupe_patient->heure_debut;
$plage_fin   = $date . " " . $plage_groupe_patient->heure_fin;

$ljoin                              = [];
$ljoin["prescription"]              = "prescription.object_id = sejour.sejour_id AND prescription.object_class = 'CSejour'";
$ljoin["prescription_line_element"] = "prescription_line_element.prescription_id = prescription.prescription_id";

$where                                                      = [];
$where["prescription.type"]                                 = " = 'sejour'";
$where["prescription_line_element.element_prescription_id"] = CSQLDataSource::prepareIn(
    array_keys($elements_prescription)
);
$where["sejour.entree"]                                     = " <= '$date 23:59:59'";
$where["sejour.sortie"]                                     = " >= '$date 00:00:00'";

$sejour  = new CSejour();
$sejours = $sejour->loadGroupList($where, "sejour.entree asc", null, "sejour.sejour_id", $ljoin);

// Événements déjà planifiés sur cette plage
$where                              = [];
$where[]                            = "sejour_id " . CSQLDataSource::prepareIn(array_keys($sejours));
$where[]                            = "DATE(evenement_ssr.debut) >= '" . CMbDT::date($date) . "'";
$where[]                            = "DAYNAME(evenement_ssr.debut) = '" . $plage_groupe_patient->groupe_day . "'";
$where[]                            = "evenement_ssr.plage_groupe_patient_id <> '$plage_groupe_patient->_id' OR evenement_ssr.plage_groupe_patient_id IS NULL";
$where["evenement_ssr.type_seance"] = " <> 'collective'";

$evenement  = new CEvenementSSR();
$evenements = $evenement->loadList($where);

$heure_debut = $plage_groupe_patient->heure_debut;
$heure_fin   = $plage_groupe_patient->heure_fin;

$sejours_associes = $plage_groupe_patient->loadRefSejoursAssocies($date);

$where_plage_groupe = ["plage_groupe_patient_id" => " = '$plage_groupe_patient->_id'"];

$evenements_ssr = CStoredObject::massLoadBackRefs(
    $sejours_associes,
    "evenements_ssr",
    null,
    $where_plage_groupe
);
CStoredObject::massLoadBackRefs($evenements_ssr, "actes_csarr");

foreach ($sejours_associes as $_sejour) {
    $events_ssr = $_sejour->loadRefsEvtsSSRSejour($where_plage_groupe);

    foreach ($events_ssr as $_event) {
        $_event->loadRefsActesCsARR();
    }
}

$sejours_collisions = [];

$lines_elements = CStoredObject::massLoadFwdRef($evenements, "prescription_line_element_id");
CStoredObject::massLoadFwdRef($lines_elements, 'element_prescription_id');

CPrescriptionLineElement::$_load_extra_lite = true;
//Retrait des séjours ayant des événements déjà planifiés sur cette plage
foreach ($evenements as $_evenement) {
    $line_elt = $_evenement->loadRefPrescriptionLineElement();
    $line_elt->loadRefElement();

    if ($_evenement->_heure_deb < $heure_fin && $_evenement->_heure_fin > $heure_debut) {
        $_evenement_fin = CMbDT::dateTime("+$_evenement->_duree minutes", $_evenement->debut);

        if ($_evenement_fin > $plage_debut && $_evenement->debut < $plage_fin) {
            $sejours_collisions[$_evenement->sejour_id] = $_evenement->sejour_id;
        }
    }
}
CPrescriptionLineElement::$_load_extra_lite = false;

// Suppression des séjours qui ne peuvent pas ajouter d'événements SSR dans cette plage
$now               = CMbDT::date();
$first_day_of_week = CMbDT::date("$plage_groupe_patient->groupe_day this week");

if ($now > $first_day_of_week) {
    $first_day_of_week = CMbDT::date("+1 week", $first_day_of_week);
}

foreach ($sejours as $_sejour) {
    if (in_array($_sejour->_id, array_keys($sejours_associes))) {
        continue;
    }

    $date_sortie         = CMbDT::date($_sejour->sortie);
    $date_entree         = CMbDT::date($_sejour->entree);
    $first_day_of_sejour = $first_day_of_week > $date_entree ? $first_day_of_week : $date_entree;
    $days                = [];

    for ($day = $first_day_of_sejour; $day < $date_sortie; $day = CMbDT::date("+1 week", $day)) {
        $days[$day] = $day;
    }

    $unset_id = count($days) > 0;

    foreach ($days as $_day) {
        $evt_collision        = new CEvenementSSR();
        $evt_collision->debut = $_day . " " . $heure_debut;
        $evt_collision->duree = $plage_groupe_patient->_duree;
        if (count($evt_collision->getCollectivesCollisions(null, null, $_sejour->_id, "<", false)) === 0) {
            $unset_id = false;
            break;
        }
    }

    if ($unset_id) {
        unset($sejours[$_sejour->_id]);
    }
}

CPrescription::$_load_lite = true;
CSejour::massLoadRefPrescriptionSejour($sejours);
CPrescription::$_load_lite       = false;
$where_acte_csarr                = [];
$where_acte_csarr["type_seance"] = "";
$prescriptions_period            = [];

$cache_elements_prescription = [];

CStoredObject::massLoadFwdRef($sejours, "patient_id");

foreach ($sejours as $_sejour) {
    $_sejour->loadRefPatient();
    $prescription = $_sejour->_ref_prescription_sejour;
    $prescription->loadRefsLinesElement();

    $counter_element = 0;

    foreach ($prescription->_ref_prescription_lines_element as $_line_element) {
        $element_prescription = $_line_element->_ref_element_prescription;

        if (!isset($cache_elements_prescription[$_line_element->element_prescription_id])) {
            $cache_elements_prescription[$_line_element->element_prescription_id] = $element_prescription;
        }

        if (in_array($element_prescription->_id, array_keys($elements_prescription))) {
            $counter_element++;

            if (($_line_element->_debut_reel < $plage_debut && $_line_element->_fin_reelle < $plage_debut) || ($_line_element->_debut_reel > $plage_fin && $_line_element->_fin_reelle > $plage_fin)) {
                unset($prescription->_ref_prescription_lines_element[$_line_element->_id]);
                $counter_element--;
                continue;
            }
        }
    }

    if ($counter_element < 1) {
        unset($sejours[$_sejour->_id]);
    }
}

$executants_cats = [];
$where_csarr     = ["type_seance <> 'collective' OR type_seance IS NULL"];

CStoredObject::massLoadBackRefs($cache_elements_prescription, "csarrs", null, $where_csarr);

foreach ($cache_elements_prescription as $_element_prescription) {
    if (!isset($executants_cats[$_element_prescription->category_prescription_id])) {
        $executants_cats[$_element_prescription->category_prescription_id] =
            $_element_prescription->loadRefUsersFromFunctionsCategory();

        CStoredObject::massLoadFwdRef(
            $executants_cats[$_element_prescription->category_prescription_id],
            "function_id"
        );

        foreach ($executants_cats[$_element_prescription->category_prescription_id] as $_executant) {
            $_executant->loadRefFunction();
        }
    }

    $actes_csarr = $_element_prescription->loadRefsCsarrs($where_csarr);

    $acte_heure_debut = "";
    $acte_heure_fin   = "";

    foreach ($actes_csarr as $_acte) {
        if (!$acte_heure_debut) {
            $acte_heure_debut = $plage_groupe_patient->heure_debut;
        }

        $acte_heure_fin = CMbDT::addTime($acte_heure_debut, "00:{$_acte->duree}:00");

        $activite = $_acte->loadRefActiviteCsarr();
        $activite->loadRefsModulateurs();

        $_acte->_heure_debut = $acte_heure_debut;
        $_acte->_heure_fin   = $acte_heure_fin;

        $acte_heure_debut = $acte_heure_fin;
    }
}

if ($order_col == "patient_id") {
    $order_nom = CMbArray::pluck($sejours, "_ref_patient", "nom");
    $order_prenom = CMbArray::pluck($sejours, "_ref_patient", "prenom");
    array_multisort(
        $order_nom,
        constant("SORT_$order_way"),
        $order_prenom,
        constant("SORT_$order_way"),
        $sejours
    );
} else {
    $order_entree = CMbArray::pluck($sejours, "entree");
    array_multisort($order_entree, constant("SORT_$order_way"), $sejours);
}

$extensions_doc = CExtensionDocumentaireCsARR::getList();

$element = CElementPrescription::findOrNew($filter_element_id);

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("order_way", $order_way);
$smarty->assign("order_col", $order_col);
$smarty->assign("sejours", $sejours);
$smarty->assign("sejours_collisions", $sejours_collisions);
$smarty->assign("evenements", $evenements);
$smarty->assign("plage_groupe_patient", $plage_groupe_patient);
$smarty->assign("extensions_doc", $extensions_doc);
$smarty->assign("element", $element);
$smarty->assign("plage_date", $plage_date);
$smarty->assign('executants_cats', $executants_cats);
$smarty->display("inc_vw_groupe_patients");
