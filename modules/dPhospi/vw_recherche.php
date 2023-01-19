<?php

/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$group = CGroups::loadCurrent();

// Récupération des paramètres
$typeVue        = CView::get("typeVue", "num default|0", true);
$selPrat        = CView::get('selPrat', 'ref class|CMediusers', true);
$services_ids   = CView::get("services_ids", "str", true);
$date_recherche = CView::get("date_recherche", "dateTime default|now", true);

// Détection du changement d'établissement
$services_ids = CService::getServicesIdsPref($services_ids);
CView::checkin();

// Liste des chirurgiens
$listPrat = new CMediusers();
$listPrat = $listPrat->loadPraticiens(PERM_READ);

// Liste des services
$services           = new CService();
$where              = [];
$where["group_id"]  = "= '$group->_id'";
$where["cancelled"] = "= '0'";
$order              = "nom";
$services           = $services->loadListWithPerms(PERM_READ, $where, $order);

$listAff            = null;
$libre              = null;
$autre_sexe_chambre = [];
$occupes            = [];

$patients = [];
$services = [];

$ds = CSQLDataSource::get("std");

//
// Cas de l'affichage des lits libres
//
if ($typeVue == 0) {
    // Recherche de tous les lits disponibles
    $sql = "SELECT lit.lit_id
          FROM affectation
          LEFT JOIN lit ON lit.lit_id = affectation.lit_id
          LEFT JOIN chambre ON lit.chambre_id = chambre.chambre_id
          WHERE '$date_recherche' BETWEEN affectation.entree AND affectation.sortie
          AND chambre.annule = '0'
          AND lit.annule = '0'
          AND affectation.effectue = '0'
          GROUP BY lit.lit_id";

    $occupes = $ds->loadlist($sql);
    $arrayIn = [];
    foreach ($occupes as $key => $occupe) {
        $arrayIn[] = $occupe["lit_id"];
    }
    $notIn = count($arrayIn) > 0 ? implode(', ', $arrayIn) : 0;
    $libre = [];

    if (is_array($services_ids) && count($services_ids)) {
        $sql   = "SELECT lit.chambre_id, lit.lit_id, lit.nom AS lit, chambre.nom AS chambre, 
            chambre.caracteristiques as caracteristiques, 
            service.nom AS service, MIN(affectation.entree) AS limite, service.service_id
            FROM lit
            LEFT JOIN affectation ON affectation.lit_id = lit.lit_id
            AND (affectation.entree > '$date_recherche' OR affectation.entree IS NULL)
            LEFT JOIN chambre ON chambre.chambre_id = lit.chambre_id
            LEFT JOIN service ON service.service_id = chambre.service_id
            WHERE lit.lit_id NOT IN($notIn)
            AND chambre.is_waiting_room = '0'
            AND chambre.annule = '0'
            AND lit.annule = '0'
            AND service.group_id = '$group->_id'
            AND service.service_id " . CSQLDataSource::prepareIn($services_ids) . "
            GROUP BY lit.lit_id
            ORDER BY service.nom, chambre.nom, lit.nom, limite DESC";
        $libre = $ds->loadList($sql);

        $sql = "SELECT lit.chambre_id, patients.sexe, patients.patient_id, lit.nom AS lit, 
            chambre.nom AS chambre, service.nom AS service
            FROM affectation
            LEFT JOIN lit ON lit.lit_id = affectation.lit_id
            LEFT JOIN chambre ON chambre.chambre_id = lit.chambre_id
            LEFT JOIN service ON chambre.service_id = chambre.service_id
            LEFT JOIN sejour ON sejour.sejour_id = affectation.sejour_id
            LEFT JOIN patients ON patients.patient_id = sejour.patient_id
            WHERE '$date_recherche' BETWEEN affectation.entree AND affectation.sortie
            AND affectation.lit_id IS NOT NULL
            AND affectation.sejour_id IS NOT NULL
            AND lit.chambre_id " . CSQLDataSource::prepareIn(CMbArray::pluck($libre, "chambre_id")) .
            " AND lit.lit_id " . CSQLDataSource::prepareNotIn(CMbArray::pluck($libre, "lit_id")) . "
           GROUP BY lit.lit_id";

        $where    = [
            "service_id" => CSQLDataSource::prepareIn($services_ids),
        ];
        $service  = new CService();
        $services = $service->loadList($where);

        $autre_sexe_chambre = $ds->loadList($sql);
        $where              = [
            "patient_id" => CSQLDataSource::prepareIn(CMbArray::pluck($autre_sexe_chambre, "patient_id")),
        ];
        $patient            = new CPatient();
        $patients           = $patient->loadList($where);
        foreach ($autre_sexe_chambre as $key => $_autre) {
            $autre_sexe_chambre[$_autre["chambre_id"]] = $_autre;
            if ($_autre["patient_id"]) {
                $autre_sexe_chambre[$_autre["chambre_id"]]["patient"] = $patients[$_autre["patient_id"]];
            }
        }
    }
} elseif ($typeVue == 1) {
    //
    // Cas de l'affichage des lits d'un praticien
    //

    // Recherche des patients du praticien
    // Qui ont une affectation
    $listAff = [
        "Aff"    => [],
        "NotAff" => [],
    ];

    if (is_array($services_ids) && count($services_ids)) {
        $affectation    = new CAffectation();
        $ljoin          = [
            "lit"     => "affectation.lit_id = lit.lit_id",
            "chambre" => "chambre.chambre_id = lit.chambre_id",
            "service" => "service.service_id = chambre.service_id",
            "sejour"  => "sejour.sejour_id   = affectation.sejour_id",
        ];
        $where          = [
            "affectation.entree"  => "< '$date_recherche'",
            "affectation.sortie"  => "> '$date_recherche'",
            "service.service_id"  => CSQLDataSource::prepareIn($services_ids),
            "sejour.praticien_id" => CSQLDataSource::prepareIn(array_keys($listPrat), $selPrat),
            "sejour.group_id"     => "= '$group->_id'",
        ];
        $order          = "service.nom, chambre.nom, lit.nom";
        $listAff["Aff"] = $affectation->loadList($where, $order, null, null, $ljoin);
        CAffectation::massUpdateView($listAff["Aff"]);
        foreach ($listAff["Aff"] as $_aff) {
            /** @var CAffectation $_aff */
            $_aff->loadView();
            $_aff->loadRefSejour();
            $_aff->_ref_sejour->loadRefPatient();
            $_aff->_ref_sejour->_ref_praticien = $listPrat[$_aff->_ref_sejour->praticien_id];

            $_aff->loadRefLit();
            $_aff->_ref_lit->loadCompleteView();
            foreach ($_aff->_ref_sejour->_ref_operations as $_operation) {
                $_operation->loadExtCodesCCAM();
            }
        }
    } else {
        // Qui n'ont pas d'affectation
        $sejour            = new CSejour();
        $where             = [
            "sejour.entree"       => "< '$date_recherche'",
            "sejour.sortie"       => "> '$date_recherche'",
            "sejour.praticien_id" => CSQLDataSource::prepareIn(array_keys($listPrat), $selPrat),
            "sejour.group_id"     => "= '$group->_id'",
        ];
        $order             = "sejour.entree, sejour.sortie, sejour.praticien_id";
        $listAff["NotAff"] = $sejour->loadList($where, $order);
        foreach ($listAff["NotAff"] as $_sejour) {
            /** @var CSejour $_sejour */
            $_sejour->loadRefPatient();
            $_sejour->_ref_praticien = $listPrat[$_sejour->praticien_id];
        }
    }
} elseif ($typeVue == 2) {
    // Recherche de tous les lits bloques pour urgences
    $ljoin            = [];
    $ljoin["lit"]     = "lit.lit_id = affectation.lit_id";
    $ljoin["chambre"] = "lit.chambre_id = chambre.chambre_id";
    $ljoin["service"] = "service.service_id = chambre.service_id";

    $where                            = [];
    $where["chambre.annule"]          = " = '0'";
    $where["lit.annule"]              = " = '0'";
    $where["affectation.effectue"]    = " = '0'";
    $where["affectation.sejour_id"]   = " IS NULL";
    $where['affectation.function_id'] = 'IS NOT NULL';
    $where["service.group_id"]        = "= '$group->_id'";
    $where[]                          = " '$date_recherche' BETWEEN affectation.entree AND affectation.sortie";

    if (is_array($services_ids) && count($services_ids)) {
        $where['affectation.service_id'] = CSQLDataSource::prepareIn($services_ids);
    }

    $affectation = new CAffectation();
    $occupes     = $affectation->loadList($where, null, null, null, $ljoin);
    CAffectation::massUpdateView($occupes);
    foreach ($occupes as $_affectation) {
        /* @var CAffectation $_affectation */
        $_affectation->loadRefLit()->loadRefChambre()->loadRefService();
    }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("date_recherche", $date_recherche);
$smarty->assign("occupes", $occupes);
$smarty->assign("libre", $libre);
$smarty->assign("typeVue", $typeVue);
$smarty->assign("selPrat", $selPrat);
$smarty->assign("listPrat", $listPrat);
$smarty->assign("listAff", $listAff);
$smarty->assign("autre_sexe_chambre", $autre_sexe_chambre);
$smarty->assign("canPlanningOp", CModule::getCanDo("dPplanningOp"));
$smarty->assign("services", $services);

$smarty->display("vw_recherche.tpl");
