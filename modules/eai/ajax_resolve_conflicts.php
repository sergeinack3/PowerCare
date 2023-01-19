<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Interop\Eai\CDomain;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkAdmin();

CApp::setMemoryLimit("712M");

$domains_id = CValue::get("domains_id");
if (!is_array($domains_id)) {
  $domains_id = explode("-", $domains_id);
}

CMbArray::removeValue("", $domains_id);

$d1_id = $domains_id[0];
$d2_id = $domains_id[1];

$d1 = new CDomain();
$d1->load($d1_id);
$d1->isMaster();

$d2 = new CDomain();
$d2->load($d2_id);
$d2->isMaster();

$ds = $d1->_spec->ds;

$and = null;
if ($d1->_is_master_ipp || $d2->_is_master_ipp) {
  $and .= "AND id1.object_class = 'CPatient' \n AND id2.object_class = 'CPatient'";
}
if ($d1->_is_master_nda || $d2->_is_master_nda) {
  $and .= "AND id1.object_class = 'CSejour' \n AND id2.object_class = 'CSejour'";
}

$intersect_id400s = $ds->loadList("SELECT id1.id400
                                   FROM id_sante400 AS id1 
                                   LEFT JOIN id_sante400 AS id2 ON id2.id400 = id1.id400 
                                   AND id2.object_class = id1.object_class
                                   WHERE id1.tag = '$d1->tag'
                                   AND id2.tag = '$d2->tag'
                                   $and"
                                 );

$intersect_id400s = CMbArray::pluck($intersect_id400s, "id400");

if (!$intersect_id400s) {
  $intersect_id400s = array();
}

$intersect = array();
foreach ($intersect_id400s as $_id400) {
  $idex_d1        = new CIdSante400();
  $idex_d1->tag   = "$d1->tag";
  $idex_d1->id400 = $_id400;
  $idex_d1->loadMatchingObject();
  $idex_d1->loadTargetObject();
    
  $idex_d2        = new CIdSante400();
  $idex_d2->tag   = "$d2->tag";
  $idex_d2->id400 = $_id400;
  $idex_d2->loadMatchingObject();
  $idex_d2->loadTargetObject();
  
  $intersect[$_id400] = array(
    $idex_d1,
    $idex_d2
  );
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("d1_id", $d1_id);
$smarty->assign("d2_id", $d2_id);
$smarty->assign("intersect", $intersect);
$smarty->display("inc_resolve_conflicts.tpl");