<?php
/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Stock\CSociete;

CCanDo::checkEdit();

$societe_id = CView::get("societe_id", "ref class|CSociete", true);

CView::checkin();

// Loads the expected Societe
$societe = new CSociete();
$societe->load($societe_id);
$societe->loadRefsBack();

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign('societe', $societe);
$smarty->display('inc_form_societe.tpl');
