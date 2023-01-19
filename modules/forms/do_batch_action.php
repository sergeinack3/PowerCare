<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\System\Forms\CExClassField;
use Ox\Mediboard\System\Forms\CExConcept;

CCanDo::checkAdmin();

$action = CValue::get("action");

switch ($action) {
  case "bool_defaul_reset":
    $ex_class_field = new CExClassField();
    $ds             = $ex_class_field->_spec->ds;

    $query = "
    UPDATE `ex_class_field`
    SET
    `prop` = REPLACE(`prop`, ' default|0', ''),
    `prop` = REPLACE(`prop`, ' default|1', ''),
    `prop` = REPLACE(`prop`, ' default|', '')
    WHERE `prop` LIKE 'bool %';";

    if (!$ds->query($query)) {
      CAppUI::setMsg("Erreur lors de la remise � z�ro des champs bool�ens (" . $ds->error() . ")", UI_MSG_WARNING);
    }
    else {
      CAppUI::setMsg($ds->affectedRows() . " champs mis � jour", UI_MSG_OK);
    }

    $query = "
    UPDATE `ex_concept`
    SET
    `prop` = REPLACE(`prop`, ' default|0', ''),
    `prop` = REPLACE(`prop`, ' default|1', ''),
    `prop` = REPLACE(`prop`, ' default|', '')
    WHERE `prop` LIKE 'bool %';";

    if (!$ds->query($query)) {
      CAppUI::setMsg("Erreur lors de la remise � z�ro des concepts bool�ens (" . $ds->error() . ")", UI_MSG_WARNING);
    }
    else {
      CAppUI::setMsg($ds->affectedRows() . " concepts mis � jour", UI_MSG_OK);
    }
    break;

  case "str_to_text":
    $where = array(
      "prop" => "LIKE 'str%'",
    );

    $concept      = new CExConcept();
    $str_concepts = $concept->loadList($where);

    foreach ($str_concepts as $_concept) {
      $_concept->prop = preg_replace("/^(str)/", "text", $_concept->prop);

      if ($msg = $_concept->store()) {
        CAppUI::setMsg($msg, UI_MSG_WARNING);
      }
      else {
        CAppUI::setMsg("Concept modifi�", UI_MSG_OK);
      }
    }

    if (count($str_concepts) == 0) {
      CAppUI::setMsg("Aucun concept modifi�", UI_MSG_OK);
    }

    $ex_field   = new CExClassField;
    $str_fields = $ex_field->loadList($where);

    foreach ($str_fields as $_field) {
      $_field->prop = preg_replace("/^(str)/", "text", $_field->prop);

      if ($msg = $_field->store()) {
        CAppUI::setMsg($msg, UI_MSG_WARNING);
      }
      else {
        CAppUI::setMsg("Champ modifi�", UI_MSG_OK);
      }
    }

    if (count($str_fields) == 0) {
      CAppUI::setMsg("Aucun champ modifi�", UI_MSG_OK);
    }
    break;
}

echo CAppUI::getMsg();
