<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Sante400\CIncrementer;

CCanDo::checkAdmin();

$incrementer_id = CValue::getOrSession("incrementer_id");
$domain_id      = CValue::getOrSession("domain_id");

// Récupération due l'incrementeur à ajouter/editer 
$incrementer = new CIncrementer;
$incrementer->load($incrementer_id);
$incrementer->loadMasterDomain($domain_id);
$incrementer->loadView();

if ($incrementer->_object_class) {
  $object = new $incrementer->_object_class;

  $object_vars = array_keys(CIncrementer::getVars($object));
  $object_vars = array_combine($object_vars, $object_vars);
  foreach ($object_vars as &$_var) {
    $_var = "[$_var]";
  }

  $object_vars["VALUE"] = "%06d";
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("incrementer", $incrementer);
$smarty->assign("domain_id", $domain_id);
$smarty->assign("object_vars", $object_vars);
$smarty->display("inc_edit_incrementer.tpl");