<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Soins\CObjectifSoin;

$objectif_soin_id = CView::get("objectif_soin_id", "ref class|CObjectifSoin");
CView::checkin();

$objectif_soin = new CObjectifSoin();
$objectif_soin->load($objectif_soin_id);
$objectif_soin->loadRefsReevaluations();

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign("objectif_soin", $objectif_soin);
$smarty->display("vw_list_reeval_objectif");
