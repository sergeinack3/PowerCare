<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Eai\CDomain;
use Ox\Mediboard\Sante400\CIdSante400;

CApp::setTimeLimit(240);
CApp::setMemoryLimit("512M");

CCanDo::checkAdmin();

$d1_id    = CValue::request("domain_1_id");
$d2_id    = CValue::request("domain_2_id");
$idex_ids = CValue::request("idex_ids", array());

/* Traitement prélable pour passer en "trash" les idexs en erreurs */
foreach ($idex_ids as $_idex => $idex_id) {
  $idex = new CIdSante400();
  $idex->load($idex_id);
  
  $idex->tag = "trash_$idex->tag";
  $idex->store();
}

/* checkMerge */
$domains_id = array(
  $d1_id,
  $d2_id
);

$domains    = array();
$checkMerge = array();
if (count($domains_id) != 2) {
  $checkMerge[] = CAppUI::tr("mergeTooFewObjects");
}

foreach ($domains_id as $domain_id) {
  $domain = new CDomain();
  
  // the CMbObject is loaded
  if (!$domain->load($domain_id)) {
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

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("domains", $domains);
$smarty->assign("checkMerge", $checkMerge);
$smarty->display("inc_domains_merge.tpl");

CApp::rip();
