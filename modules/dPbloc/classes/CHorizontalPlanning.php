<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Bloc;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbRange;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CItemLiaison;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\System\CPreferences;

/**
 * Description
 */
class CHorizontalPlanning implements IShortNameAutoloadable {
  /** @var array The periods */
  protected $periods = array();

  /** @var integer The first hour */
  protected $first_hour;

  /** @var integer The last hour */
  protected $last_hour;

  /** @var string The date */
  protected $date;

  /** @var string The current datetime */
  protected $current_datetime;

  /** @var boolean True if the selected date is the current day */
  protected $current_day = false;

  /** @var CBlocOperatoire[] A list of the selected blocs */
  protected $blocs;

  /** @var array A list of selected operation rooms */
  protected $rooms = array();

  /** @var integer[] A list of selected operation room's ids */
  protected $rooms_ids = array();

  /** @var COperation[] The list of operations */
  protected $operations = array();

  /** @var bool Indicate if the functional permission shabla must be checked */
  protected $check_planning_visibility = false;

  /** @var array The functional perms of the different users */
  protected $permissions = array();

  /** @var integer The window width */
  protected $window_width;

  /** @var CGroups The group */
  protected $group;

  /**
   * CHorizontalPlanning constructor.
   *
   * @param string  $date         The date for which the planning will be made
   * @param array   $blocs_ids    The list of the selected CBlocOperatoire
   * @param array   $rooms_ids    The list of the selected CSalle
   * @param integer $window_width The width of the window
   */
  public function __construct($date, $blocs_ids = array(), $rooms_ids = array(), $window_width = 1800) {
    $this->date = $date;
    $this->current_datetime = CMbDT::dateTime();
    $this->current_day = $this->date == CMbDT::date($this->current_datetime);
    $this->group = CGroups::get();

    $user = CMediusers::get();
    if ($user->isChirurgien() || $user->isMedecin() || $user->isDentiste()) {
      $this->check_planning_visibility = true;
    }

    $this->window_width = max(1000, $window_width);

    $this->setPeriods();

    $periods = array();
    foreach ($this->periods as $key => $period) {
      $periods[$key] = array();
    }

    $salle = new CSalle();
    foreach ($blocs_ids as $id) {
      /** @var CBlocOperatoire $bloc */
      $bloc = CBlocOperatoire::loadFromGuid("CBlocOperatoire-$id");

      if ($bloc->group_id == $this->group->_id) {
        $where = ['bloc_id'  => " = '$id'"];

        if (count($rooms_ids)) {
          $where['salle_id'] = CSQLDataSource::prepareIn($rooms_ids);
          $bloc->_ref_salles = $salle->loadListWithPerms(PERM_READ, $where, 'nom');

          foreach ($bloc->_ref_salles as $room) {
            $this->rooms[$room->_id] = array(
              'salle'   => $room,
              'periods' => $periods,
              'height'  => 1,
            );
          }
          $this->rooms_ids = array_merge($this->rooms_ids, array_keys($bloc->_ref_salles));
        }

        $this->blocs[$id] = $bloc;
      }
    }
  }

  /**
   * Load the operations and compute the planning data
   *
   * @return array
   */
  public function getPlanningData() {
    $this->loadOperations();

    $dtnow = CMbDT::dateTime();
    $user = CMediusers::get();

    foreach ($this->operations as $operation) {
      /* Check if the it is needed to check the functional permission bloc_planning_visibility */
      if ($this->check_planning_visibility && array_key_exists($operation->chir_id, $this->permissions)
          && array_key_exists('bloc_planning_visibility', $this->permissions[$operation->chir_id])
      ) {
        $permission = $this->permissions[$operation->chir_id]['bloc_planning_visibility'];
        $chir = $operation->loadRefChir();

        /* Check the visibility conditions depending on the value of the surgeon's function permission,
           if the connected user is a also surgeon */
        if (($permission == 'restricted' && $user->_id != $operation->chir_id)
            || ($permission == 'function' && $user->function_id != $chir->function_id)
        ) {
          continue;
        }
      }

      self::loadOperationReferences($operation);

      $operation->_fin_prevue = CMbDT::addTime(CMbDT::time($operation->temp_operation), $operation->_datetime_best);

      $state = 'not_started';
      if ($operation->sortie_salle || $operation->fin_op) {
        $state = 'ended';
      }
      elseif ($operation->debut_op) {
        $state = 'pending';
      }

      if ($operation->debut_op && $operation->debut_op <= $dtnow) {
        $duree = CMbDT::timeRelative($operation->debut_op, $operation->fin_op ? : $dtnow);

        if ($duree > $operation->temp_operation) {
          $state .= ($operation->fin_op ? ' late_ended' : ' late');
        }
      }

      if ($operation->plageop_id && $operation->salle_id != $operation->_ref_plageop->salle_id) {
        $state .= ' moved';
      }

      $salle_id = $operation->salle_id;

      $begin = $operation->time_operation;
      if ($operation->entree_salle) {
        $begin = CMbDT::time($operation->entree_salle);
      }

      $end = CMbDT::addTime($operation->temp_operation, $begin);
      if ($operation->sortie_salle) {
        $end = CMbDT::time($operation->sortie_salle);
      }

      $h_begin = (int) CMbDT::format($begin, '%H');
      $h_end   = (int) CMbDT::format($end, '%H');

      // Operations on two days
      $date_begin = $date_end = $operation->date;

      if ($h_begin > $h_end) {
        $date_end = CMbDT::date("+1 DAY", $date_end);
      }

      foreach ($this->periods as $key => $data) {
        if (($this->date === $date_begin && in_array($h_begin, $data['hours']))
          || ($this->date === $date_end && in_array($h_end, $data['hours']))
        ) {

          $period = $key;

          $data = array(
            'object'    => $operation,
            'positions' => $this->getOperationPositions($operation, $period),
            'state'     => $state
          );

          if ($operation->salle_id && !$operation->annulee) {
            $this->addDataToRoom($salle_id, $period, $data);
          }
          elseif (!$salle_id && !$operation->annulee) {
            $this->addDataToRoom('unplaced', $period, $data);
          }
          elseif (CAppUI::pref('planning_bloc_show_cancelled_operations')) {
            $this->addDataToRoom('cancelled', $period, $data);
          }
        }
      }
    }

    $this->loadBlocages();
    $this->reorderDatas();

    return $this->rooms;
  }

  /**
   * Return the periods
   *
   * @return array
   */
  public function getPeriods() {
    return $this->periods;
  }

  /**
   * Return the height of the planning (considering collisions)
   *
   * @return int
   */
  public function getHeight() {
    return array_sum(CMbArray::pluck($this->rooms, "height"));
  }

  /**
   * Return the current time position in the planning
   *
   * @return array
   */
  public function getCurrentTimePosition() {
    $hour = CMbDT::format($this->current_datetime, '%H');
    $minutes = (int) CMbDT::format($this->current_datetime, '%M');

    $current_time = array('period' => 0);

    if ($this->current_day) {
      $key        = 0;
      $position   = 0;
      $hour_width = 150;
      foreach ($this->periods as $key => $period) {
        if (in_array($hour, $period['hours'])) {
          if (array_search($hour, $period['hours'])) {
            $hour_width = $period['hour_width'];
            $position   += round(array_search($hour, $period['hours']) * $hour_width);
          }
          break 1;
        }
      }

      /* Computing the position from the minutes */
      if ($minutes) {
        $position += round($minutes / 60 * $hour_width);
      }

      $current_time['period']   = $key;
      $current_time['position'] = $position;
    }

    return $current_time;
  }

  /**
   * Set the periods according to the user preferences
   *
   * @return void
   */
  protected function setPeriods() {
    $period_1 = intval(CAppUI::pref('planning_bloc_period_1', 7));
    $period_2 = intval(CAppUI::pref('planning_bloc_period_2'));
    $period_3 = intval(CAppUI::pref('planning_bloc_period_3'));
    $period_4 = intval(CAppUI::pref('planning_bloc_period_4'));

    $this->periods = array();
    $this->first_hour = $period_1;
    $this->last_hour = $period_1 - 1;

    /* First period */
    $begin = $period_1;
    $end = $period_2;
    if (!$end) {
      $end = $begin;
    }

    $this->periods[] = $this->makePeriod($begin, $end);

    /* Second period */
    if ($period_2) {
      $begin = $period_2;
      $end = $period_3;
      if (!$end) {
        $end = "0";
      }

      $this->periods[] = $this->makePeriod($begin, $end);

      /* Third period */
      if ($period_3) {
        $begin = $period_3;
        $end = $period_4;
        if (!$end) {
          $end = "0";
        }

        $this->periods[] = $this->makePeriod($begin, $end);

        /* Fourth period */
        if ($period_4) {
          $begin = $period_4;
          $end = "0";

          $this->periods[] = $this->makePeriod($begin, $end);
        }
      }
    }
  }

  /**
   * Load the operations
   *
   * @return void
   */
  protected function loadOperations() {
    $where = [
      'date' => CSQLDataSource::prepareIn([CMbDT::date('-1 DAY', $this->date), $this->date]),
      "salle_id IS NULL OR salle_id " . CSQLDataSource::prepareIn($this->rooms_ids),
      'sejour.group_id' => " = '{$this->group->_id}'"
    ];

    $ljoin = ['sejour' => 'operations.sejour_id = sejour.sejour_id'];

    $order = 'salle_id ASC, time_operation ASC, annulee ASC';

    $operation = new COperation();
    $this->operations = $operation->loadList($where, $order, null, null, $ljoin);

    $sejours  = CStoredObject::massLoadFwdRef($this->operations, 'sejour_id');
    $chirs    = CStoredObject::massLoadFwdRef($this->operations, 'chir_id');
    $anesths  = CStoredObject::massLoadFwdRef($this->operations, 'anesth_id');
    $plages   = CStoredObject::massLoadFwdRef($this->operations, 'plageop_id');
    $anesths  = array_merge($anesths, CStoredObject::massLoadFwdRef($plages, 'anesth_id'));
    CStoredObject::massLoadFwdRef($sejours, 'patient_id');
    CStoredObject::massLoadFwdRef(array_merge($chirs, $anesths), 'function_id');
    CStoredObject::massLoadBackRefs($this->operations, "notes");

    /* If needed, we get the permissions and preferences of all the chirs */
    if ($this->check_planning_visibility) {
      $this->permissions = CPreferences::getAllPrefsUsers($chirs);
    }
  }

  /**
   * Load the blocages for each rooms
   *
   * @return void
   */
  protected function loadBlocages() {
    foreach ($this->rooms as $id => $data) {
      if ($data['salle'] instanceof CSalle) {
        $room = $data['salle'];
        $room->_blocage = array();
        $blocages = $room->loadRefsBlocages($this->date);

        $first_hour = str_pad($this->first_hour, 2, '0', STR_PAD_LEFT);
        $last_hour  = str_pad($this->last_hour, 2, '0', STR_PAD_LEFT);

        if (count($blocages)) {
          foreach ($blocages as $blocage) {
            if ($blocage->fin < "{$this->date} {$first_hour}:00:00") {
              continue;
            }

            $begin = CMbDT::time($blocage->deb);
            $end = CMbDT::time($blocage->fin);

            if ($blocage->deb < "{$this->date} {$first_hour}:00:00") {
              $begin = "{$first_hour}:00:00";
            }

            if ($blocage->fin > CMbDT::date('+1 DAY', $this->date) . " {$last_hour}:59:59") {
              $end = "{$last_hour}:59:59";
            }

            foreach ($this->periods as $key => $period) {
              $hour_begin = (int) CMbDT::format($begin, '%H');
              $minutes_begin = (int) CMbDT::format($begin, '%M');
              $hour_end = (int) CMbDT::format($end, '%H');
              $hour_width = $period['hour_width'];
              $position = 0;

              if (in_array($hour_begin, $period['hours'])) {
                if (array_search($hour_begin, $period['hours'])) {
                  $position += round(array_search($hour_begin, $period['hours']) * $hour_width);
                }
                /* Computing the position from the minutes */
                if ($minutes_begin) {
                  $position += round($minutes_begin / 60 * $hour_width);
                }

                $duration = CMbDT::subTime($begin, $end);
                /* If the end of the blocage is superior to the end of the period */
                if (!in_array($hour_end, $period['hours']) && $hour_end) {
                  $_end = str_pad(end($period['hours']), 2, '0', STR_PAD_LEFT) . ':59:59';
                  $duration = CMbDT::subTime($begin, $_end);
                  $begin = str_pad(end($period['hours']) + 1, 2, '0', STR_PAD_LEFT) . ':00:00';
                }

                $width = self::getWidth($duration, $hour_width);

                if (!in_array($key, $room->_blocage)) {
                  $room->_blocage[$key] = array();
                }

                $room->_blocage[$key][] = array(
                  'position' => $position,
                  'width'    => $width,
                  'view'     => $blocage->_view,
                  'id'       => $blocage->_id
                );
              }
            }
          }
        }
      }
    }
  }

  /**
   * Load all the needed references of the given operation
   *
   * @param COperation $operation The operation
   *
   * @return void
   */
  protected static function loadOperationReferences($operation) {
    $sejour = $operation->loadRefSejour();
    $operation->loadRefChir();
    $operation->loadRefPlageOp();
    $operation->loadRefAnesth();
    $operation->loadRefsNotes();

    $operation->_ref_chir->loadRefFunction();
    $operation->_ref_anesth->loadRefFunction();

    $sejour->loadNDA();

    $patient = $operation->loadRefPatient();
    $dossier = $patient->loadRefDossierMedical();
    $dossier->countAntecedents();
    $dossier->countAllergies();

    if (CAppUI::gconf('dPplanningOp CSejour use_charge_price_indicator')) {
      $operation->_ref_sejour->loadRefChargePriceIndicator();
    }

    if (CAppUI::gconf('dPhospi prestations systeme_prestations') == 'expert') {
      $where = array('item_realise_id' => 'IS NOT NULL', 'date' => " = '$operation->date'");
      $liaisons = $operation->_ref_sejour->loadBackRefs('items_liaisons', 'date', null, null, null, null, null, $where);
      /** @var CItemLiaison $liaison */
      foreach ($liaisons as $liaison) {
        $liaison->loadRefItemRealise();
      }

      $operation->_ref_sejour->_liaisons_for_prestation = $liaisons;
    }
  }

  /**
   * Return the positions values for the given operation
   *
   * @param COperation $operation The operation
   * @param integer    $period    The period's key
   *
   * @return array
   */
  protected function getOperationPositions($operation, $period) {
    $hour_width = $this->periods[$period]['hour_width'];

    $begin = $operation->time_operation;
    if ($operation->entree_salle) {
      $begin = CMbDT::time($operation->entree_salle);
    }

    $end = CMbDT::addTime($operation->temp_operation, $begin);
    if ($operation->sortie_salle) {
      $end = CMbDT::time($operation->sortie_salle);
    }

    /* Computing the left position */
    $position = 0;

    /* Computing the position from the hours */
    $hour = (int) CMbDT::format($begin, '%H');
    $minutes = (int) CMbDT::format($begin, '%M');

    if (!in_array($hour, $this->periods[$period]['hours'])) {
      $begin = reset($this->periods[$period]['hours']) . ":00:00";
    }

    $hour_end = (int) CMbDT::format($end, '%H');
    if (!in_array($hour_end, $this->periods[$period]['hours'])) {
      $end = end($this->periods[$period]['hours']) . ":00:00";
      $end = CMbDT::time("+1 hour", $end);
    }

    if (in_array($hour, $this->periods[$period]['hours']) && array_search($hour, $this->periods[$period]['hours'])) {
      $position += round(array_search($hour, $this->periods[$period]['hours']) * $hour_width);
    }
    /* Computing the position from the minutes */
    if (in_array($hour, $this->periods[$period]['hours']) && $minutes) {
      $position += round($minutes / 60 * $hour_width);
    }

    /* Computing the width */
    $duration = CMbDT::subTime($begin, $end);
    $width = self::getWidth($duration, $hour_width);

    /* Induction */
    $width_induction = 0;
    $position_induction = 0;
    if ($operation->_duree_induction) {
      $width_induction = self::getWidth($operation->_duree_induction, $hour_width);

      $begin_induction = CMbDT::time($operation->induction_debut);
      if ($begin_induction != $begin) {
        $diff = CMbDT::subTime($begin, $begin_induction);
        $hour = (int) CMbDT::format($diff, '%H');
        $minutes = (int) CMbDT::format($diff, '%M');
        if ($hour) {
          $position_induction += round($hour * $hour_width);
        }
        /* Computing the position from the minutes */
        if ($minutes) {
          $position_induction += round($minutes / 60 * $hour_width);
        }
      }
    }

    /* Preop */
    $position_preop = 0;
    $width_preop = 0;
    if ($operation->presence_preop && $operation->presence_preop != '00:00:00') {
      $width_preop    = self::getWidth($operation->presence_preop, $hour_width);
      $position_preop = $position - $width_preop;
    }

    /* Postop */
    $position_postop = 0;
    $width_postop = 0;
    if ($operation->presence_postop && $operation->presence_postop != '00:00:00') {
      $width_postop    = self::getWidth($operation->presence_postop, $hour_width);
    }

    if ($operation->duree_bio_nettoyage && $operation->duree_bio_nettoyage != '00:00:00') {
      $width_postop    += self::getWidth($operation->duree_bio_nettoyage, $hour_width);
    }

    if ($width_postop) {
      $position_postop = $position + $width;
    }

    return array(
      'position'            => $position,
      'width'               => $width,
      'position_induction'  => $position_induction,
      'width_induction'     => $width_induction,
      'position_preop'      => $position_preop,
      'width_preop'         => $width_preop,
      'position_postop'     => $position_postop,
      'width_postop'        => $width_postop
    );
  }

  /**
   * Return an array of hours from the begining to the end
   *
   * @param integer $begin The begining of the period. Must be an hour number in 24h format
   * @param integer $end   The end of the period. Must be an hour number in 24h format
   *
   * @return array
   */
  protected function makePeriod($begin, $end) {
    if ($begin >= $end) {
      $end += 24;
    }

    $hours = array();
    $i = 0;
    for ($h = $begin; $h < $end; $h++) {
      $hours[$i] = $h % 24;
      $i++;
    }

    return array('hours' => $hours, 'hour_width' => $this->getHourWidth(count($hours)));
  }

  /**
   * Add the given data to the given rooms
   *
   * @param mixed   $name   The name of the room
   * @param integer $period The key of the period
   * @param array   $data   The data to set
   *
   * @return void
   */
  protected function addDataToRoom($name, $period, $data) {
    if (!array_key_exists($name, $this->rooms)) {
      $this->rooms[$name] = array(
        'salle'       => $name,
        'periods'     => array()
      );

      if (is_string($name)) {
        $this->rooms[$name]['salle'] = CAppUI::tr("COperation-state-{$name}");
      }
    }

    if (!array_key_exists($period, $this->rooms[$name]['periods'])) {
      $this->rooms[$name]['periods'][$period] = array();
    }

    $this->rooms[$name]['periods'][$period][] = $data;
  }

  /**
   * Reorder operations considering collisions
   *
   * @return void
   */
  protected function reorderDatas() {
    foreach ($this->rooms as $_name => $_room) {
      foreach ($_room["periods"] as $_period => $_data_by_period) {
        if (!count($_data_by_period)) {
          continue;
        }
        $save_ops = $_data_by_period;
        $this->rooms[$_name]["periods"][$_period] = array();
        $ops = array();
        foreach ($save_ops as $_key => $_data) {
          $ops[$_key] = array(
            "lower" => $_data["positions"]["position"],
            "upper" => $_data["positions"]["position"] + $_data["positions"]["width"]
          );
        }

        foreach (CMbRange::rearrange2($ops) as $_data_id => $_data) {
          $this->rooms[$_name]["periods"][$_period][$_data["start"]][] = $save_ops[$_data_id];
        }

        if (!isset($this->rooms[$_name]["height"])) {
          $this->rooms[$_name]["height"] = 0;
        }

        $this->rooms[$_name]["height"] = max($this->rooms[$_name]["height"], count($this->rooms[$_name]["periods"][$_period]));
      }
    }
  }

  /**
   * Compute the width of an hour in the planning, based on the number of hours to display in a single period
   *
   * @param integer $hours The number of hours in the period
   *
   * @return integer
   */
  protected function getHourWidth($hours) {
    return $hours < 12 ? round($this->window_width / $hours) : 150;
  }

  /**
   * Compute the width in pixels for the given duration
   *
   * @param string  $duration   The duration
   * @param integer $hour_width The width of an hour (in pixels)
   *
   * @return float
   */
  protected static function getWidth($duration, $hour_width) {
    $hours    = (int) CMbDT::format($duration, '%H');
    $minutes  = (int) CMbDT::format($duration, '%M');

    $width = 0;
    if ($hours) {
      $width += round($hours * $hour_width);
    }
    /* Computing the width from the minutes */
    if ($minutes) {
      $width += round($minutes / 60 * $hour_width);
    }

    return $width;
  }
}
