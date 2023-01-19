<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/* Tâche planifiée permettant de décaler les interventions placée après une intervention en retard */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkAdmin();

CView::checkin();

$shift = CAppUI::gconf('dPbloc other operation_shift_value');

$group = CGroups::loadCurrent();

$date = CMbDT::date();
$datetime = CMbDT::dateTime();

/* Loading the unfinished operations */
$operation = new COperation();

$ljoin = array(
  'sejour' => 'sejour.sejour_id = operations.sejour_id'
);

$where = array(
  'sejour.group_id' => " = {$group->_id}",
  'operations.date'          => " = '{$date}'",
  'operations.rank'          => ' IS NOT NULL',
  'operations.entree_salle'  => " < '{$datetime}'",
  'operations.sortie_salle'  => ' IS NULL',
  "TIMEDIFF('{$datetime}', operations.entree_salle) > operations.temp_operation"
);

/** @var COperation[] $operations */
$operations = $operation->loadList($where, 'entree_salle ASC, operation_id ASC', null, 'operation_id', $ljoin);
$plages = array();

foreach ($operations as $operation) {
  $plage = $operation->loadRefPlageOp();

  if (!in_array($plage->_id, $plages)) {
    $plage->loadRefsOperations(false, 'rank, time_operation', false, null, array('rank' => " > {$operation->rank}"));

    foreach ($plage->_ref_operations as $_operation) {
      $_operation->time_operation = CMbDT::time("+$shift MINUTES", $_operation->time_operation);
      $_operation->_time_urgence  = $_operation->time_operation;

      $_operation->store(false);
    }

    $plages[] = $plage->_id;
  }
}