<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectationUfSecondaire;
use Ox\Mediboard\Hospi\CAffectationUniteFonctionnelle;
use Ox\Mediboard\Hospi\CChambre;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CProtocole;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkEdit();
// Récupération des paramètres
$object_guid = CView::get("object_guid", "str");
CView::checkin();

/** @var CMediusers|CFunctions|CSejour|CProtocole|CService|CLit|CChambre $object */
$object = CMbObject::loadFromGuid($object_guid);

/** @var CAffectationUniteFonctionnelle[] $affectations_uf */
$affectations_uf = $object->loadBackRefs("ufs");

CStoredObject::massLoadFwdRef($affectations_uf, "uf_id");

$ufs_selected = [
    "medicale"    => false,
    "hebergement" => false,
    "soins"       => false,
];

foreach ($affectations_uf as $key => $_affectation_uf) {
    $uf = $_affectation_uf->loadRefUniteFonctionnelle();
    $uf->loadRefUm();
    //Utilisation d'un seul type d'uf par service/chambre/lit?
    $ufs_selected[$_affectation_uf->_ref_uf->_id]  = true;
    $ufs_selected[$_affectation_uf->_ref_uf->type] = true;
}

$order_type        = CMbArray::pluck($affectations_uf, "_ref_uf", "type");
$order_sejour_type = CMbArray::pluck($affectations_uf, "_ref_uf", "type_sejour");
array_multisort($order_type, SORT_ASC, $order_sejour_type, SORT_ASC, $affectations_uf);

$ufs = CUniteFonctionnelle::getUFs($object);

if ($object instanceof CMediusers || $object instanceof CFunctions) {
    /** @var CAffectationUniteFonctionnelle[] $affectations_secondaire_uf */
    $affectations_secondaire_uf = $object->loadBackRefs("ufs_secondaires");

    CStoredObject::massLoadFwdRef($affectations_secondaire_uf, "uf_id");

    $ufs_secondaire_selected = ["medicale" => false];

    foreach ($affectations_secondaire_uf as $key => $_affectation_uf) {
        $uf = $_affectation_uf->loadRefUniteFonctionnelle();
        $uf->loadRefUm();
        //Utilisation d'un seul type d'uf par service/chambre/lit?
        $ufs_secondaire_selected[$_affectation_uf->_ref_uf->_id]  = true;
        $ufs_secondaire_selected[$_affectation_uf->_ref_uf->type] = true;
    }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("object", $object);
$smarty->assign("ufs", $ufs);
$smarty->assign("affectations_uf", $affectations_uf);
$smarty->assign("ufs_selected", $ufs_selected);

if ($object instanceof CMediusers || $object instanceof CFunctions) {
    $smarty->assign("ufs_secondaire_selected", $ufs_secondaire_selected);
    $smarty->assign("affectations_secondaire_uf", $affectations_secondaire_uf);
    $smarty->assign("affectation_uf_second", new CAffectationUfSecondaire());
}

$smarty->display("inc_affectation_uf");
