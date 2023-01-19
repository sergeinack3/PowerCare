<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$date         = CValue::getOrSession("date", CMbDT::date());
$praticien_id = CValue::getOrSession("praticien_id", CAppUI::$instance->user_id);

// Chargement de la liste des praticiens
$praticien  = new CMediusers();
$praticiens = $praticien->loadPraticiens();

// Chargement du praticien selectionné
$praticien->load($praticien_id);

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("praticiens", $praticiens);
$smarty->assign("date", $date);
$smarty->assign("praticien", $praticien);
$smarty->display("vw_aed_replacement");
