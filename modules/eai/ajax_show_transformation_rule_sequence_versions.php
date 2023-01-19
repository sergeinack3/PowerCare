<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Eai\Transformations\CTransformationRuleSequence;

/**
 * View versions for transformation rule
 */
CCanDo::checkAdmin();

$transformation_rule_sequence_id = CValue::get("transformation_rule_sequence_id");
$standard_name                   = CValue::get("standard_name");
$profil_name            = CValue::get("profil_name");

$versions = array();

if ($standard_name) {
  $standard = new $standard_name;
  $versions = $standard->getVersions();
}

$versions_profil = array();
if ($profil_name && $profil_name != "none") {
  $profil_name = str_replace("_", "", $profil_name);

  $classname= "C$profil_name";

  $profil = new $classname;
  $versions = $profil->getVersions();
}

$transformation_rule_sequence = new CTransformationRuleSequence();
$transformation_rule_sequence->load($transformation_rule_sequence_id);

if (empty($versions) && $transformation_rule_sequence->version) {
  $versions = array ($transformation_rule_sequence->version);
}


$smarty = new CSmartyDP();
$smarty->assign("versions"           , $versions);
$smarty->assign("transformation_rule_sequence", $transformation_rule_sequence);
$smarty->display("inc_select_enum_versions.tpl");
