<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CValue;
use Ox\Mediboard\Sante400\CIncrementer;

$reset      = CValue::post("_reset");
$extra_data = CValue::post("extra_data");


$do = new CDoObjectAddEdit("CIncrementer");

if (!$reset) {
  $do->doIt();

  CApp::rip();
}

$do->doBind();

/** @var CIncrementer $old */
$old = $do->_old;

/** @var CIncrementer $obj */
$obj = $do->_obj;

if ($old->_id && $old->extra_data && ($old->extra_data == $extra_data)) {
  CAppUI::stepAjax("CIncrementer-msg-Extra value must be updated", UI_MSG_ERROR);
}

// Remise à zéro
$obj->value = $old->reset_value;

$do->doStore();
$do->doRedirect();