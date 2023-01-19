<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Interop\Eai\CDomain;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Refresh list domains EAI
 */
CCanDo::checkAdmin();

$domain_id = CView::get("domain_id", "ref class|CDomain", true);

CView::checkin();

// Liste des domaines
$domain  = new CDomain();

    $group = CGroups::loadCurrent()->_id;
    if ($group != null) {
        $ljoin = "group_domain ON domain.domain_id = group_domain.domain_id";
        $where['group_id'] = "= $group or group_id is null";
    }
if($group != null){
    $domains = $domain->loadList($where,null,null,null,$ljoin);
}else{
    $domain = $domain->loadList();
}
CStoredObject::massLoadFwdRef($domains, "actor_id");
CStoredObject::massLoadFwdRef($domains, "incrementer_id");
CStoredObject::massLoadBackRefs($domains, "group_domains");

/** @var CDomain $_domain */
foreach ($domains as $_domain) {
  $_domain->loadRefActor();
  $_domain->loadRefIncrementer()->loadView();
  $_domain->loadRefsGroupDomains();
  foreach ($_domain->_ref_group_domains as $_group_domain) {
    $_group_domain->loadRefGroup();
  }
  $_domain->isMaster();
  if ($_domain->_id == $domain_id) {
    $domain = $_domain;
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("domains"     , $domains);
$smarty->assign("domain"      , $domain);
$smarty->display("inc_list_domains.tpl");

