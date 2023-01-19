<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

// Current user and current function
$mediuser = CMediusers::get();
$function = $mediuser->loadRefFunction();

// Filter
$filter = new CPlageconsult();
$filter->_function_id       = CValue::get("_function_id", $function->type == "cabinet" ? $function->_id : null);
$filter->_date_min          = CValue::get("_date_min", CMbDT::date("last month"));
$filter->_date_max          = CValue::get("_date_max", CMbDT::date());
$filter->_user_id           = CValue::get("_user_id", null);
$compute_mode               = CValue::get("compute_mode");
$csv                        = CValue::get("csv");
$page                       = (int)CValue::get("page", 0);

$limit = 1000;
if ($csv) {
  $limit = 10000;
}

$group_id = CGroups::loadCurrent()->_id;

$ds = $filter->getDS();
$list = array();

if ($compute_mode == "adresse_par") {
  $ljoin = array("plageconsult"    => "plageconsult.plageconsult_id = consultation.plageconsult_id",
                 "users_mediboard" => "users_mediboard.user_id = plageconsult.chir_id",
                 "functions_mediboard" => "functions_mediboard.function_id = users_mediboard.function_id");

  $where = array("consultation.adresse_par_prat_id" => "is not null",
                 "functions_mediboard.group_id"     => "= $group_id",
                 "plageconsult.date"                => "between '".$filter->_date_min."' and '".$filter->_date_max."'");

  if ($filter->_user_id) {
    $where["users_mediboard.user_id"] = "= ".$filter->_user_id;
  }
  if ($filter->_function_id) {
    $where["users_mediboard.function_id"] = "= ".$filter->_function_id;
  }

  $group_by = array("consultation.adresse_par_prat_id");
  $order_by = array("total desc");
  $limit    = "$page, $limit";

  $request = new CRequest();
  $request->addSelect("consultation.adresse_par_prat_id, COUNT(consultation.consultation_id) AS total");
  $request->addTable("consultation");
  $request->addLJoin($ljoin);
  $request->addWhere($where);
  $request->addGroup($group_by);
  $request->addOrder($order_by);
  $request->setLimit($limit);

  $query = $request->makeSelect();

  $list = $ds->loadHashList($query);
}
elseif ($compute_mode == "correspondants") {
  $tag = CPatient::getTagIPP();

  $ljoin = array("plageconsult"    => "plageconsult.plageconsult_id = consultation.plageconsult_id",
                 "correspondant"   => "correspondant.patient_id = consultation.patient_id",
                 "medecin"         => "medecin.medecin_id = correspondant.medecin_id",
                 "id_sante400"     => "id_sante400.object_id = consultation.patient_id AND id_sante400.object_class = 'CPatient'",
                 "users_mediboard" => "users_mediboard.user_id = plageconsult.chir_id");

  $where = array();

  if ($filter->_function_id) {
    $where["users_mediboard.function_id"] = "= $filter->_function_id";
  }
  if ($filter->_user_id) {
    $where["plageconsult.chir_id"] = "= $filter->_user_id";
  }
  if ($tag) {
    $where["id_sante400.tag"] = "= '$tag'";
  }

  // Correspondent
  $group_by = array("medecin.medecin_id");
  $limit = "$page, $limit";

  $request = new CRequest();
  $request->addSelect("medecin.medecin_id, COUNT(correspondant.patient_id) AS total");
  $request->addTable("consultation");
  $request->addLJoin($ljoin);
  $request->addWhere($where);
  $request->addGroup($group_by);
  $request->setLimit($limit);

  $query_corresp = $request->makeSelect();

  // GP Request query
  $ljoin["patients"] = "patients.patient_id = consultation.patient_id";
  $where["patients.medecin_traitant"] = "is not null";
  $group_by = array("patients.medecin_traitant");

  $request = new CRequest();
  $request->addSelect('patients.medecin_traitant, COUNT(DISTINCT(patients.patient_id)) AS total');
  $request->addTable("consultation");
  $request->addLJoin($ljoin);
  $request->addWhere($where);
  $request->addGroup($group_by);
  $request->setLimit($limit);

  $query_traitant = $request->makeSelect();

  $list_corresp  = $ds->loadHashList($query_corresp);
  $list_traitant = $ds->loadHashList($query_traitant);

  foreach ($list_traitant as $_medecin_id => $_count) {
    if (array_key_exists($_medecin_id, $list_corresp)) {
      $list_corresp[$_medecin_id] += $_count;
    }
    else {
      $list_corresp[$_medecin_id] = $_count;
    }
  }

  arsort($list_corresp);
  $list = $list_corresp;
}

$where = array(
  "medecin_id" => $ds->prepareIn(array_keys($list))
);
$medecin = new CMedecin();
/** @var CMedecin[] $medecins */
$medecins = $medecin->loadList($where);

if ($csv) {
  $csvfile = new CCSVFile();
  $titles = array(
    "Total",
    CAppUI::tr("CMedecin-nom"),
    CAppUI::tr("CMedecin-prenom"),
    CAppUI::tr("CMedecin-type"),
    CAppUI::tr("CMedecin-tel"),
    CAppUI::tr("CMedecin-fax"),
    CAppUI::tr("CMedecin-email"),
    CAppUI::tr("CMedecin-adresse"),
    CAppUI::tr("CMedecin-cp"),
    CAppUI::tr("CMedecin-adeli"),
    CAppUI::tr("CMedecin-rpps"),
  );
  $csvfile->writeLine($titles);
  
  foreach ($list as $_medecin_id => $_count) {
    $_medecin = $medecins[$_medecin_id];

    if (!$_medecin) {
      continue;
    }

    $_line = array(
      $_count,
      $_medecin->nom,
      $_medecin->prenom,
      $_medecin->type,
      $_medecin->tel,
      $_medecin->fax,
      $_medecin->email,
      $_medecin->adresse,
      $_medecin->cp,
      $_medecin->adeli,
      $_medecin->rpps,
    );

    $csvfile->writeLine($_line);
  }

  $csvfile->stream("Médecins correspondants");
}
else {
  $smarty = new CSmartyDP();
  $smarty->assign("medecins", $medecins);
  $smarty->assign("counts", $list);
  $smarty->display("inc_stats_medecins.tpl");
}
