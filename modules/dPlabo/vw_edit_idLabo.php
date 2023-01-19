<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkAdmin();

// Last update
$today = CMbDT::dateTime();

// Chargement des praticiens de l'établissement
$praticien = new CMediusers();
$praticiens = $praticien->loadPraticiens();
$listPraticiens = array();

foreach ($praticiens as $key => $praticien) {
  $listPraticiens[$key]["prat"] = $praticien;
  $praticien->loadLastId400("labo code4");
  $listPraticiens[$key]["code4"]= $praticien->_ref_last_id400;
  $praticien->loadLastId400("labo code9");
  $listPraticiens[$key]["code9"]= $praticien->_ref_last_id400;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("today"         , $today);
$smarty->assign("listPraticiens", $listPraticiens);

$smarty->display("vw_edit_idLabo");
