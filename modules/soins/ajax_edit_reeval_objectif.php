<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Soins\CObjectifSoinReeval;

$objectif_reeval_id = CView::get("objectif_reeval_id", "ref class|CObjectifSoinReeval");
$objectif_soin_id   = CView::get("objectif_soin_id", "ref class|CObjectifSoin");
CView::checkin();

$reevaluation = new CObjectifSoinReeval();
if (!$reevaluation->load($objectif_reeval_id)) {
  $reevaluation->objectif_soin_id = $objectif_soin_id;
}

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign("reevaluation", $reevaluation);
$smarty->display("vw_edit_reeval_objectif");
