<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CChambre;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkRead();

$view_current = CValue::get("view_current", 0);

CView::enforceSlave();

$smarty = new CSmartyDP();

// Pour l'établissement courant
if ($view_current) {
  $etab          = CGroups::loadCurrent();
  $current_group = $etab->_id;

  $res_current_etab = array();
  $where            = array();
  $ljoin            = array();
  
  // - Nombre de séjours
  $tag_NDA               = CSejour::getTagNDA($current_group);
  $where["tag"]          = "= '$tag_NDA'";
  $where["object_class"] = "= 'CSejour'";

  $idex = new CIdSante400();
  $res_current_etab["CSejour-_NDA"] = $idex->countList($where);
  
  // - Patients IPP
  $tag_ipp               = CPatient::getTagIPP($current_group);
  $where["tag"]          = "= '$tag_ipp'";
  $where["object_class"] = "= 'CPatient'";

  $idex = new CIdSante400;
  $res_current_etab["CPatient-_IPP"] = $idex->countList($where);
  
  // - Nombre de consultations
  $where        = array();
  $consultation = new CConsultation();

  $ljoin["plageconsult"]        = "consultation.plageconsult_id = plageconsult.plageconsult_id";
  $ljoin["users_mediboard"]     = "plageconsult.chir_id = users_mediboard.user_id";
  $ljoin["functions_mediboard"] = "users_mediboard.function_id = functions_mediboard.function_id";

  $where["functions_mediboard.group_id"] = " = '$current_group'";
  $res_current_etab["CConsultation"]     = $consultation->countList($where, null, $ljoin);
  
  // - Lits
  $ljoin = array();
  $where = array();
  $lit   = new CLit();

  $ljoin["chambre"] = "lit.chambre_id = chambre.chambre_id";
  $ljoin["service"] = "chambre.service_id = service.service_id";

  $where["service.group_id"] = "= '$current_group'";

  $res_current_etab["CLit"] = $lit->countList($where, null, $ljoin);
  
  // - Chambres
  $ljoin   = array();
  $where   = array();
  $chambre = new CChambre();

  $ljoin["service"]             = "chambre.service_id = service.service_id";
  $where["service.group_id"]    = "= '$current_group'";

  $res_current_etab["CChambre"] = $chambre->countList($where, null, $ljoin);
  
  // - Utilisateurs
  $ljoin    = array();
  $where    = array();
  $mediuser = new CMediusers();

  $ljoin["functions_mediboard"]          = "users_mediboard.function_id = functions_mediboard.function_id";
  $where["functions_mediboard.group_id"] = "= '$current_group'";

  $res_current_etab["CMediusers"]        = $mediuser->countList($where, null, $ljoin);

  $smarty->assign("res_current_etab", $res_current_etab);
  $smarty->display("inc_metrique_current_etab.tpl");
}
// Vue générale
else {
  $ds     = CSQLDataSource::get("std");
  $etab   = CGroups::loadCurrent();
  $result = array();

  $listeClasses = CApp::getInstalledClasses(array(), true);
  $mapping_table_classe = array();

  foreach ($listeClasses as $class) {
    $obj = new $class;

    $spec = $obj->getSpec();

    if ($spec->measureable) {
      $mapping_table_classe[$spec->table] = $class;
    }
  }

  if (count($mapping_table_classe)) {
    $sql = "SHOW TABLE STATUS WHERE Name " . CSQLDataSource::prepareIn(array_keys($mapping_table_classe));
    $statusTables = $ds->loadList($sql);

    foreach ($statusTables as $_statusTable) {
      $class = $mapping_table_classe[$_statusTable["Name"]];
      $result[$class] = $_statusTable;
    }
  }

  ksort($result);

  $smarty->assign("result",   $result);
  $smarty->assign("etab",     $etab);
  $smarty->assign("nb_etabs", $etab->countList());
  $smarty->display("view_metrique.tpl");
}
