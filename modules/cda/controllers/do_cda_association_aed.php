<?php
/**
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Sante400\CIdSante400;

$group_type = CValue::post("group_type");
$group_id   = CValue::post("group_id");
$idex_tag   = CValue::post("idex_tag", "cda_association_code");

$group      = new CGroups();
$group->load($group_id);

$idex = new CIdSante400();
$idex->tag = $idex_tag;
$idex->setObject($group);
$idex->loadMatchingObject();
$idex->id400 = $group_type;

if ($group_type && $msg = $idex->store()) {
  CAppUI::stepAjax($msg, UI_MSG_ERROR);
}

CAppUI::stepAjax("Configuration effectuée");