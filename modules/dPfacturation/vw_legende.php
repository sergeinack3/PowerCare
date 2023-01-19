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
use Ox\Mediboard\Facturation\CFacture;

$classe = CView::get("classe", "str");
CView::checkin();

CCanDo::checkRead();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("classe", $classe);
$smarty->assign("statutes", CFacture::STATUSES);

$smarty->display("vw_legende");

