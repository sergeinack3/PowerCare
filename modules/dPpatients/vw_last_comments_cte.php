<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Patients\CConstantComment;

CCanDo::checkRead();
$constant      = CValue::get("constant");
$context_class = CValue::get("context_class");
$context_id    = CValue::get("context_id");

$ljoin                                       = array();
$ljoin["constantes_medicales"]               = "constantes_medicales.constantes_medicales_id = constant_comments.constant_id";
$where                                       = array();
$where["constantes_medicales.context_class"] = " = '$context_class'";
$where["constantes_medicales.context_id"]    = " = '$context_id'";
$where["constant_comments.constant"]         = " = '$constant'";

$comment      = new CConstantComment();
$commentaires = $comment->loadList($where, "constantes_medicales.datetime DESC", 10, "constant_comments.constant_comment_id", $ljoin);

foreach ($commentaires as $_comment) {
  $_comment->loadRefConstantes();
}

$smarty = new CSmartyDP();

$smarty->assign("commentaires", $commentaires);
$smarty->assign("constant", $constant);

$smarty->display("vw_last_comments_cte.tpl");