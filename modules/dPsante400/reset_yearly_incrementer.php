<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Mediboard\Sante400\CIncrementer;

CCanDo::checkAdmin();

$year = date('y');

$incrementer = new CIncrementer();
$where       = array(
  "extra_data"  => "IS NOT NULL",
  "reset_value" => "IS NOT NULL"
);
/** @var CIncrementer[] $incrementers */
$incrementers = $incrementer->loadList($where);

foreach ($incrementers as $_incrementer) {
  if ($year <= $_incrementer->extra_data) {
    CAppUI::stepAjax("CIncrementer-msg-Extra value must be updated", UI_MSG_ERROR);
  }

  $_incrementer->extra_data = $year;
  $_incrementer->value      = $_incrementer->reset_value;

  $_incrementer->store();

  CAppUI::stepAjax("CIncrementer-msg-Incrementer '%d' resetted to value '%d' and year '%d'",
    UI_MSG_OK, $_incrementer->_id, $_incrementer->reset_value, $year
  );
}


