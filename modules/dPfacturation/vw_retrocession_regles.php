<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();
$prat_id = CView::get("prat_id", "ref class|CMediusers", 1);
CView::checkin();

$mediuser = new CMediusers();
$listPrat = $mediuser->loadPraticiens();

// Chargement du praticien
$praticien = new CMediusers();
$praticien->load($prat_id);
$praticien->loadRefsRetrocessions();

// Creation du template
$smarty = new CSmartyDP();

$smarty->assign("listPrat",   $listPrat);
$smarty->assign("praticien",  $praticien);

$smarty->display("vw_retrocession_regles");
