<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$sejour = new CSejour();

$date           = CValue::get('date');
$type           = CValue::get('type');
$services_ids   = CValue::getOrSession('services_ids', 0);
$filter_service = CValue::getOrSession('active_filter_services', 0);
$prat_id        = CValue::get('prat_id');
$period         = CValue::get('period');
$type_pec       = CValue::get('type_pec', $sejour->_specs['type_pec']->_list);
$filterFunction = CValue::get('filterFunction');
$view           = CValue::get('view', 'inputs');
$reglement_dh   = CValue::get('reglement_dh');

if (!is_array($services_ids)) {
  $services_ids = explode('|', $services_ids);
}
CMbArray::removeValue('', $services_ids);

$date_min = CMbDT::dateTime('00:00:00', $date);
$date_max = CMbDT::dateTime('23:59:59', $date);

if ($period) {
  $hour = CAppUI::gconf('dPadmissions General hour_matin_soir');
  if ($period == 'matin') {
    // Matin
    $date_max = CMbDT::dateTime($hour, $date);
  }
  else {
    // Soir
    $date_min = CMbDT::dateTime($hour, $date);
  }
}

$group = CGroups::loadCurrent();

$operation = new COperation();
$where = array();
$ljoin = array(
  'sejour' => 'sejour.sejour_id = operations.sejour_id'
);

if (count($services_ids) && $filter_service) {
  $ljoin['affectation']            = 'affectation.sejour_id = sejour.sejour_id AND affectation.entree = sejour.entree';
  $where['affectation.service_id'] = CSQLDataSource::prepareIn($services_ids);
}

// Filtre sur le type du séjour
if ($type == 'ambucomp') {
  $where['sejour.type'] = CSQLDataSource::prepareIn(array('ambu', 'comp'));
}
elseif ($type == "ambucompssr") {
  $where['sejour.type'] = CSQLDataSource::prepareIn(array('ambu', 'comp', 'ssr'));
}
elseif ($type) {
  if ($type !== 'tous') {
    $where['sejour.type'] = " = '$type'";
  }
}
else {
  $where['sejour.type'] = CSQLDataSource::prepareNotIn(array_merge(CSejour::getTypesSejoursUrgence(), ['seances']));
}

// Filtre sur le praticien
if ($prat_id) {
  $user = CMediusers::get($prat_id);

  if ($user->isAnesth()) {
    $ljoin['plagesop'] = 'plagesop.plageop_id = operations.plageop_id';
    $where[] = " operations.anesth_id = '$prat_id' OR plagesop.anesth_id = '$prat_id' OR sejour.praticien_id = '$prat_id'";
  }
  else {
    $where['sejour.praticien_id'] = " = '$prat_id'";
  }
}

$where['sejour.group_id'] = "= '$group->_id'";

if ($view == 'inputs') {
  $where['sejour.entree'] = "BETWEEN '$date_min' AND '$date_max'";
}
else {
  $where['sejour.sortie'] = "BETWEEN '$date_min' AND '$date_max'";
}
$where['sejour.annule']   = "= '0'";
$where['sejour.type_pec'] = CSQLDataSource::prepareIn($type_pec);

if ($reglement_dh && $reglement_dh != 'all') {
  if ($reglement_dh == 'payed') {
    $where[] = "((operations.depassement > 0 AND operations.reglement_dh_chir != 'non_regle') 
        OR operations.depassement = 0 OR operations.depassement IS NULL)
      AND ((operations.depassement_anesth > 0 AND operations.reglement_dh_anesth != 'non_regle') 
        OR operations.depassement_anesth = 0 OR operations.depassement_anesth IS NULL)
      AND (operations.depassement > 0 OR operations.depassement_anesth > 0)";
  }
  else {
    $where[] = "(operations.depassement > 0 AND operations.reglement_dh_chir = 'non_regle')
      OR (operations.depassement_anesth > 0 AND operations.reglement_dh_anesth = 'non_regle')";
  }
}

$order = 'operations.chir_id';
$group_by = 'operations.operation_id';

/** @var COperation[] $operations */
$operations = CAppUI::gconf('dPadmissions General use_perms')
                ? $operation->loadListWithPerms(PERM_READ, $where, $order, null, $group_by, $ljoin)
                : $operation->loadList($where, $order, null, $group_by, $ljoin);

$plages     = CStoredObject::massLoadFwdRef($operations, 'plageop_id');
$praticiens = CStoredObject::massLoadFwdRef($operations, 'chir_id');
$praticiens = array_merge(CStoredObject::massLoadFwdRef($operations, 'anesth_id'), $praticiens);
$praticiens = array_merge(CStoredObject::massLoadFwdRef($plages, 'anesth_id'), $praticiens);
$functions  = CStoredObject::massLoadFwdRef($praticiens, 'function_id');
$sejours    = CStoredObject::massLoadFwdRef($operations, 'sejour_id');
$patients   = CStoredObject::massLoadFwdRef($sejours, 'patient_id');

COperation::massLoadActes($operations);

$lines_by_user = array();

foreach ($operations as $_operation) {
  $_chir = $_operation->loadRefChir(true);
  $_operation->_ref_chir->loadRefFunction();
  $_anesth =$_operation->loadRefAnesth(true);
  $_operation->_ref_anesth->loadRefFunction();
  $_patient = $_operation->loadRefPatient(true);
  $_operation->loadRefsActes();
  $_sejour = $_operation->loadRefSejour();
  $_sejour->loadNDA($group->_id);

  $_total_dp = 0;
  $_total_dp_anesth = 0;

  foreach ($_operation->_ref_actes as $_act) {
    if ($_act->montant_depassement) {
      if ($_act->executant_id == $_chir->_id) {
        $_total_dp += $_act->montant_depassement;
      }
      elseif ($_act->executant_id == $_anesth->_id) {
        $_total_dp_anesth += $_act->montant_depassement;
      }
    }
  }

  if (($_total_dp || $_operation->depassement) && (($prat_id && $_chir->_id == $prat_id) || !$prat_id)) {
    if (!array_key_exists($_chir->_id, $lines_by_user)) {
      $lines_by_user[$_chir->_id] = array(
        'user'  => $_chir,
        'lines' => array()
      );
    }

    $_line = array('patient' => $_patient, 'dp' => $_total_dp, 'nda' => false, 'state' => null);

    if ($_sejour->_NDA) {
      $_line['nda'] = $_sejour->_NDA;
    }

    if ($_operation->depassement && $_total_dp != $_operation->depassement) {
      $_line['dp_prevu'] = $_operation->depassement;
    }

    if ($_operation->reglement_dh_chir != 'non_regle') {
      $_line['state'] = $_operation->reglement_dh_chir;
    }

    $lines_by_user[$_chir->_id]['lines'][] = $_line;
  }

  if (($_total_dp_anesth || $_operation->depassement_anesth) && (($prat_id && $_anesth->_id == $prat_id) || !$prat_id)) {
    if (!array_key_exists($_anesth->_id, $lines_by_user)) {
      $lines_by_user[$_anesth->_id] = array(
        'user'  => $_anesth,
        'lines' => array()
      );
    }

    $_line = array('patient' => $_patient, 'dp' => $_total_dp_anesth, 'nda' => false, 'state' => null);

    if ($_sejour->_NDA) {
      $_line['nda'] = $_sejour->_NDA;
    }

    if ($_operation->depassement_anesth && $_total_dp_anesth != $_operation->depassement_anesth) {
      $_line['dp_prevu'] = $_operation->depassement_anesth;
    }

    if ($_operation->reglement_dh_anesth != 'non_regle') {
      $_line['state'] = $_operation->reglement_dh_anesth;
    }

    $lines_by_user[$_anesth->_id]['lines'][] = $_line;
  }
}

$smarty = new CSmartyDP();
$smarty->assign('lines_by_user', $lines_by_user);
$smarty->assign('date', $date);
$smarty->display('print_recouvrement_dp.tpl');
