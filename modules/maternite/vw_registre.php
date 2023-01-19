<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Maternite\CNaissance;

CCanDo::checkRead();
$naissance            = new CNaissance();
$date_min_spec        = array(
  "date",
  "default" => CMbDT::date("first day of January")
);

$date_max_spec        = array(
  "date",
  "default" => CMbDT::date("last day of December")
);
$naissance->_date_min = CView::get("date_min", $date_min_spec, true);
$naissance->_date_max = CView::get("date_max", $date_max_spec, true);
CView::checkin();

$smarty = new CSmartyDP();
$smarty->assign("naissance", $naissance);
$smarty->display("vw_registre");