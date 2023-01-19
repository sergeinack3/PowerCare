<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\CMergeLog;

CCanDo::checkAdmin();
$ds   = CSQLDataSource::get("std");
$mode = CView::get("mode", "str default|check");
$page = intval(CView::get('page', 'num default|0'));
CView::checkin();

$request = "SELECT object_class, object_id, COUNT(dossier_medical_id) AS dm_count
                      FROM dossier_medical
                      WHERE object_id <> '0'
                      GROUP BY object_class, object_id
                      HAVING dm_count > 1";

$step       = 35;
$nb_results = count($ds->loadList($request));

$correction = 0;
$erreurs    = array();
if ($mode == "repair" && $nb_results) {
  $resultats = $ds->loadList($request);
  foreach ($resultats as $result) {
    //Dossier de références
    $where                 = array();
    $where["object_class"] = "= '" . $result['object_class'] . "'";
    $where["object_id"]    = "= '" . $result['object_id'] . "'";
    $dossier_ok            = new CDossierMedical();
    $dossier_ok->loadObject($where, "dossier_medical_id ASC");
    $dossier_ok->loadRefsAntecedents();
    $dossier_ok->loadRefsEtatsDents();
    $dossier_ok->loadRefsTraitements();
    $dossier_ok->loadRefsEvenementsPatient();
    $dossier_ok->loadRefsPathologies();

    //Chargement des autres dossiers
    $_dossier                    = new CDossierMedical();
    $where["dossier_medical_id"] = "!= '" . $dossier_ok->_id . "'";
    $dossiers                    = $_dossier->loadList($where);

    //Merge des dossiers
    foreach ($dossiers as $dossier) {
        $merge_log = CMergeLog::logStart(CUser::get()->_id, $dossier_ok, [$dossier->_id => $dossier], false);

      /* @var CDossierMedical $dossier */
        try {
            $dossier_ok->merge(array($dossier->_id => $dossier), false, $merge_log);
            $merge_log->logEnd();
            $correction++;
        } catch (Throwable $t) {
            $merge_log->logFromThrowable($t);
            $erreurs[$result['object_class'] . "-" . $result['object_id']] = $t->getMessage();
        }
    }
  }
  $nb_results = count($ds->loadList($request));
}

$request   = "$request
            LIMIT $page, $step;";
$resultats = $ds->loadList($request);

$objects_with_doublons = array();
foreach ($resultats as $result) {
  $object_id    = $result['object_id'];
  $object_class = $result['object_class'];
  /* @var CPatient|CSejour $object */
  $object = new $object_class;
  $object->load($object_id);
  $where                 = array();
  $where["object_class"] = "= '$object_class'";
  $where["object_id"]    = "= '$object_id'";
  $dossier               = new CDossierMedical();
  $dossiers              = $dossier->loadList($where, "dossier_medical_id ASC");
  foreach ($dossiers as $_dossier) {
    $_dossier->loadRefsAntecedents();
    $_dossier->loadRefsEtatsDents();
    $_dossier->loadRefsTraitements();
  }
  $object->_ref_dossiers_medicaux        = $dossiers;
  $object->_erreurs_correction           = isset($erreurs[$object->_guid]) ? $erreurs[$object->_guid] : array();
  $objects_with_doublons[$object->_guid] = $object;
}

$patients = array();
$smarty   = new CSmartyDP();
$smarty->assign('patients', $patients);
$smarty->assign('resultats', $objects_with_doublons);
$smarty->assign('correction', $correction);
$smarty->assign('mode', $mode);
$smarty->assign("page", $page);
$smarty->assign("step", $step);
$smarty->assign("nb_results", $nb_results);
$smarty->display('vw_check_dossier_medical.tpl');
