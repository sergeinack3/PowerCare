<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Urgences\CRPUReevalPEC;

CCanDo::checkEdit();
$rpu_reeval_pec_id = CView::get("rpu_reeval_pec_id", "ref class|CRPUReevalPEC");
$rpu_id            = CView::get("rpu_id", "ref class|CRPU");
CView::checkin();
$user = CMediusers::get();

$rpu_reeval_pec = new CRPUReevalPEC();
$rpu_reeval_pec->load($rpu_reeval_pec_id);

if (!$rpu_reeval_pec->_id) {
  $rpu_reeval_pec->rpu_id   = $rpu_id;
  $rpu_reeval_pec->datetime = "now";
  $rpu_reeval_pec->user_id  = $user->_id;
}

$smarty = new CSmartyDP;
$smarty->assign("rpu_reeval_pec", $rpu_reeval_pec);
$smarty->display("inc_edit_reevaluate_pec");
