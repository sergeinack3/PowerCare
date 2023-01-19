<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

CCanDo::checkRead();

$year = CValue::get('year');

if ($year) {
  $date = "{$year}-01-01";
}
else {
  $date = CMbDT::date();
  $year = CMbDT::format($date, "%Y");
}

/**
 * COUNTRY CODE: REGION CODES
 */
$countries = array(
  '1' => array(),           // France
  '2' => array('10', '12'), // Switzerland, 10: Vaud, 12: Geneva
  '3' => array()            // Belgium
);

$holidays_by_country = array();
foreach ($countries as $_country => $_regions) {
  $holidays_by_country[$_country] = array(
    'holidays'    => CMbDT::getHolidays($date, false, null, $_country),
    'cp_holidays' => array()
  );

  foreach ($_regions as $_region) {
    $holidays_by_country[$_country]['cp_holidays'][$_region] = CMbDT::getCpHolidays($date, null, $_country, $_region);
  }
}

$smarty = new CSmartyDP();
$smarty->assign("date", $date);
$smarty->assign("year", $year);
$smarty->assign("countries", $countries);
$smarty->assign("holidays_by_country", $holidays_by_country);
$smarty->display("holidays_tester.tpl");