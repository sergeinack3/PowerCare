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

$object_id    = CView::get("object_id", "num");
$object_class = CView::get("object_class", "str");
$show_widget  = CView::get("show_widget", "bool default|0");
$show_only    = CView::get("show_only", "bool default|0", true);
$count_links  = CView::get("count_links", "bool default|0");

CView::checkin();

$hypertext_links = array();

if ($object_id && $object_class) {
  $filter               = new CHyperTextLink();
  $filter->object_id    = $object_id;
  $filter->object_class = $object_class;
  $hypertext_links      = $filter->loadMatchingList();
}

$smarty = new CSmartyDP();

$smarty->assign("hypertext_links", $hypertext_links);
$smarty->assign("object_id", $object_id);
$smarty->assign("object_class", $object_class);
$smarty->assign("show_only", $show_only);
$smarty->assign("count_links", $count_links);

$smarty->display(
  $show_widget ?
    "inc_widget_list_hypertext_links.tpl" :
    "inc_list_hypertext_links.tpl"
);
