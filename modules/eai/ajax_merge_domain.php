<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Eai\CDomain;

CCanDo::checkAdmin();

$domains_id = CValue::get("domains_id");
if (!is_array($domains_id)) {
  $domains_id = explode("-", $domains_id);
}

CMbArray::removeValue("", $domains_id);

$domains    = array();
$checkMerge = array();
if (count($domains_id) != 2) {
  $checkMerge[] = CAppUI::tr("mergeTooFewObjects");
}

foreach ($domains_id as $domain_id) {
  $domain = new CDomain();
  
  // the CMbObject is loaded
  if (!$domain->load($domain_id)){
    CAppUI::setMsg("Chargement impossible de l'objet [$domain_id]", UI_MSG_ERROR);
    continue;
  }
  
  $domain->loadRefIncrementer();
  $domain->loadRefActor();
  
  $domains[] = $domain;
}

$domain1 = $domains[0];
$domain2 = $domains[1];

if (($domain1->incrementer_id && $domain2->actor_id) || ($domain2->incrementer_id && $domain1->actor_id)) {
  $checkMerge[] = CAppUI::tr("CDomain-merge_incompatible-incrementer_actor");
}

/*if (($domain1->derived_from_idex && !$domain2->derived_from_idex) || ($domain2->derived_from_idex && !$domain1->derived_from_idex)) {
  $checkMerge[] = CAppUI::tr("CDomain-merge_incompatible-derived_from_idex");
}*/

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("domains", $domains);
$smarty->assign("checkMerge", $checkMerge);
$smarty->display("inc_domains_merge.tpl");