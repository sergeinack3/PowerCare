<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Sante400\CHyperTextLink;

$object_id         = CView::get("object_id", "num");
$object_class      = CView::get("object_class", "str");
$hypertext_link_id = CView::get("hypertext_link_id", "num");
$show_widget       = CView::get("show_widget", "bool default|0");

CView::checkin();

$hypertext_link = new CHyperTextLink();
$hypertext_link->load($hypertext_link_id);

if (!$hypertext_link->_id) {
  $hypertext_link->object_id    = $object_id;
  $hypertext_link->object_class = $object_class;
}

$smarty = new CSmartyDP();

$smarty->assign("hypertext_link", $hypertext_link);
$smarty->assign("show_widget", $show_widget);

$smarty->display("inc_edit_hypertext_link.tpl");