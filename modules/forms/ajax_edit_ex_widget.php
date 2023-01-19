<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\Forms\CExClassWidget;

CCanDo::checkEdit();

$ex_widget_id = CView::get("ex_widget_id", "ref notNull class|CExClassWidget");

CView::checkin();

$widget = new CExClassWidget();

if ($widget->load($ex_widget_id)) {
  $widget->loadRefsNotes();
}

$widget->loadRefExGroup()->loadRefExClass();
$widget->loadRefPredicate()->loadView();

$smarty = new CSmartyDP();
$smarty->assign("widget", $widget);
$smarty->display("inc_edit_ex_widget.tpl");