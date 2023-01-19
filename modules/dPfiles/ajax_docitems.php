<?php 
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkEdit();

$docitems_guid = CView::get("docitems_guid", "str");
$context_class = CView::get("context_class", "str");

CView::checkin();

$docitems = array(
  "CCompteRendu" => array(),
  "CFile"        => array(),
);

if ($docitems_guid) {
  foreach (explode(",", $docitems_guid) as $_docitem_guid) {
    $docitem = CMbObject::loadFromGuid($_docitem_guid);

    $docitems[$docitem->_class][] = $docitem;
  }
}

$smarty = new CSmartyDP();

$smarty->assign("docitems"     , $docitems);
$smarty->assign("context_class", $context_class);

$smarty->display("inc_docitems");