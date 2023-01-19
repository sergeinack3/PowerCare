<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CConstantComment;

CCanDo::checkEdit();

$constant    = CView::get('constant', 'str');
$constant_id = CView::get('constant_id', 'ref class|CConstantesMedicales');
$unique_id   = CView::get('unique_id', 'str');

CView::checkin();

$comment              = new CConstantComment();
$comment->constant    = $constant;
$comment->constant_id = $constant_id;
$comment->loadMatchingObject();
$comment->loadRefConstantes();

$smarty = new CSmartyDP();
$smarty->assign('comment', $comment);
$smarty->assign('unique_id', $unique_id);
$smarty->display('inc_edit_constant_comment.tpl');