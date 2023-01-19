<?php

/**
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();
$date         = CValue::getOrSession("date", CMbDT::date());
$operation_id = CValue::getOrSession("operation_id");
$salle_id     = CValue::getOrSession("salle");

// Chargement des praticiens
$listAnesths = new CMediusers();
$listAnesths = $listAnesths->loadAnesthesistes(PERM_READ);

// Liste des blocs
$listBlocs      = new CBlocOperatoire();
$where          = [];
$where["actif"] = "= '1'";
$listBlocs      = $listBlocs->loadGroupList($where);

// Selection des plages opératoires de la journée
$salle = new CSalle();
if ($salle->load($salle_id)) {
    $salle->loadRefsForDay($date);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("vueReduite", false);
$smarty->assign("salle", $salle);
$smarty->assign("praticien_id", null);
$smarty->assign("listBlocs", $listBlocs);
$smarty->assign("listAnesths", $listAnesths);
$smarty->assign("date", $date);
$smarty->assign("operation_id", $operation_id);

$smarty->display("inc_liste_plages.tpl");
