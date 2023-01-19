<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Stats;

use Ox\Core\CMbDT;
use Ox\Core\CModelObject;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Bloc\CBlocage;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CBlocStatistics
 */
class CBlocStatistics extends CModelObject {
  /** @var integer CGroups ID */
  public $group_id;

  /** @var string Minimal search date */
  public $date_min;

  /** @var string Maximal search date */
  public $date_max;

  /** @var integer CMediusers ID */
  public $prat_id;

  /** @var integer CDiscipline ID */
  public $discipline_id;

  /** @var integer CBlocOperatoire ID */
  public $bloc_id;

  /** @var integer CSalle ID */
  public $salle_id;

  /** @var string COperations to display (all, all except emergencies, just emergencies) */
  public $operations_to_display;

  /** @var string CPlageOps to display (all, single or double) */
  public $plages_to_display;

  /** @var string Results group mod */
  public $grouping;

  /** @var CSQLDataSource */
  public $_ds;

  /** @var string Date format, used for grouping purposes, etc. */
  public $_date_format;

  /** @var string Used context (prat, discipline, bloc, salle) */
  public $_context;

  /** @var string The class of the CMbObject corresponding to the context
   * (CMediusers, CFunctions, CDiscipline, CBlocOperatoire, CSalle) */
  public $_context_class;

  /** @var array An aray containing the ids of all the selected practitioner */
  public $_praticien_ids;

  public $_blocs_ids;
  public $_prats_ids;
  public $_disciplines_ids;
  public $_salles_ids;

  /**
   * Initialisation method, setting and checking parameters
   *
   * @param array $data The data
   */
  function __construct($data = array()) {
    parent::__construct();

    foreach ($data as $_field => $_value) {
      if (property_exists($this, $_field)) {
        if (($_field == 'operations_to_display' || $_field == 'plages_to_display') && $_value == 'all') {
          $_value = null;
        }
        elseif (in_array($_field, array('_salles_ids', '_blocs_ids', '_disciplines_ids'))) {
          $_value        = $_value ? explode(',', $_value) : null;
          $this->$_field = $_value;
          continue;
        }
        elseif (in_array($_field, array('_prats_ids'))) {
          $_value        = $_value ? explode('|', $_value) : null;
          $this->$_field = $_value;
          continue;
        }
        $this->$_field = trim($_value);
      }
    }
    if ($this->_blocs_ids && !count($this->_blocs_ids)) {
      $this->_blocs_ids = null;
    }
    if ($this->_prats_ids && !count($this->_prats_ids)) {
      $this->_prats_ids = null;
    }
    if ($this->_disciplines_ids && !count($this->_disciplines_ids)) {
      $this->_disciplines_ids = null;
    }
    if ($this->_salles_ids && !count($this->_salles_ids)) {
      $this->_salles_ids = null;
    }
    $this->group_id = CGroups::get()->_id;
    $this->setDates();

    $this->_ds = CSQLDataSource::get('std');
  }

  /**
   * Sets date parameters according to interval parameter
   *
   * @return void
   */
  function setDates() {
    $this->date_max = ($this->date_max) ?: CMbDT::date('first day of this month');
    $this->date_min = ($this->date_min) ?: CMbDT::date('first day of last month', $this->date_max);

    $this->_date_format = '%Y-%m-%d';
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props                          = parent::getProps();
    $props['date_min']              = 'date';
    $props['date_max']              = 'date';
    $props['prat_id']               = 'ref class|CMediusers';
    $props['function_id']           = 'ref class|CFunctions';
    $props['discipline_id']         = 'ref class|CDiscipline';
    $props['bloc_id']               = 'ref class|CBlocOperatoire';
    $props['salle_id']              = 'ref class|CSalle';
    $props['operations_to_display'] = 'enum list|all|except_emergencies|emergencies default|all';
    $props['plages_to_display']     = 'enum list|all|single|double default|all';
    $props['grouping']              = 'enum list|prat|discipline|bloc|salle';

    return $props;
  }

  public function getANAPStatistics() {
    $this->getContext();

    if (!$this->_context) {
      $this->_context       = 'group_id';
      $this->_context_class = 'CGroups';
    }

    $context_user = $this->getContextUser();

    $context_place = $this->getContextPlace($context_user);

    $results = array(
      'vacations' => array()
    );

    if ($context_place) {
      $results['context_place'] = $context_place;
    }
    if ($context_user) {
      $results['context_user'] = $context_user;
    }

    $vacation_ids = $this->checkVacations();
    $plage_ids    = array();
    if ($vacation_ids && is_array($vacation_ids)) {
      foreach ($vacation_ids as $_plages) {
        $plage_ids = array_merge($plage_ids, explode('-', $_plages));
      }

      $plage_ids = array_unique($plage_ids);
    }

    $days = CMbDT::getDays($this->date_min, $this->date_max);

    $line = array(
      'plage' => null,
      'tvo'   => 0, // Temps de vacation offert
      'tpos'  => 0, // Temps Programmé d'occupation de la salle
      'tros'  => 0, // Temps réel d'occupation de la salle
      'trov'  => 0, // Temps réel d'occupation des vacations
      'txoc'  => 0, // Taux d'occupation
      'pot'   => 0, // Potentiel salle(s)
      'deb'   => 0, // Débordement
      'txdeb' => 0, // Taux de débordement
      'txper' => 0, // Taux de performance
      'txpot' => 0, // Taux d'utilisation du potentiel des salles
      'evtvo' => 0, // Evaluation du TVO
      'beg'   => 0, // Démarrages tardifs
      'txbeg' => 0, // Taux de démarrages tardifs
      'end'   => 0, // Fins précoces
      'txend' => 0, // Taux de fins précoces
      'nbop'  => 0, // Nombre d'opérations
      'urg'   => 0, // Nombre d'urgences
      'txurg' => 0  // Taux d'urgences
    );

    /* Compute the TROSjour */
    $plages = $this->loadPlagesOperatoires($days, $vacation_ids, $plage_ids);

    foreach ($plages as $_plage) {
      $where = array(
        'annulee'    => "= '0'",
        'plageop_id' => " = '{$_plage->_id}'",
        'entree_salle IS NOT NULL OR entree_bloc IS NOT NULL OR debut_op IS NOT NULL'
      );
      $order = 'entree_salle ASC, entree_bloc ASC, debut_op ASC';

      $_operation = new COperation();
      /** @var COperation[] $_operations */
      $_operations = $_operation->loadList($where, $order);
      $_plage->loadRefSalle();
      $_plage->_ref_salle->loadRefBloc();
      $_plage->loadRefChir();
      $_plage->_ref_chir->loadRefFunction();
      $_plage->_ref_chir->loadRefDiscipline();

      $_end_plage    = strtotime(CMbDT::date() . " $_plage->fin_reference");
      $_early_end    = strtotime(CMbDT::date() . ' ' . CMbDT::time('-15 MINUTES', $_plage->fin_reference));
      $_begin_plage  = strtotime(CMbDT::date() . " $_plage->debut_reference");
      $_late_begin   = strtotime(CMbDT::date() . ' ' . CMbDT::time('+15 MINUTES', $_plage->debut_reference));
      $_cleanup_time = strtotime(CMbDT::date() . " $_plage->temps_inter_op") - strtotime(CMbDT::date() . " 00:00:00");
      $_pause = CMbDT::minutesRelative("00:00:00", $_plage->pause) * 60;

      $_tvo_plage         = $_end_plage - strtotime(CMbDT::date() . " $_plage->debut_reference");
      $_tvo_plage -= $_pause;
      $_tros_plage        = 0;
      $_trov_plage        = 0;
      $_debordement_plage = 0;
      $_nb_urgences       = 0;
      $_late_beginings    = 0;
      $_early_endings     = 0;

      /* Temps programmé d'occupation des salles */
      $_tpos_plage = 0;

      $op_rank = 0;
      foreach ($_operations as $_operation) {
        $op_rank++;
        if ($_operation->temp_operation) {
          $_tpos_plage += self::timeToSeconds($_operation->temp_operation);
        }

        $_begin_hour = $_operation->entree_salle ? $_operation->entree_salle
          : ($_operation->entree_bloc ? $_operation->entree_bloc : $_operation->debut_op);
        $_begin_hour = CMbDT::time($_begin_hour);
        /* If there is no begin hour, the operation is not used for computing the stats */
        if (!$_begin_hour) {
          continue;
        }
        $_end_hour = $_operation->sortie_salle ?
          CMbDT::time($_operation->sortie_salle) :
          CMbDT::addTime($_operation->temp_operation, $_begin_hour);
        $_begin    = strtotime(CMbDT::date() . ' ' . $_begin_hour);
        $_end      = strtotime(CMbDT::date() . ' ' . $_end_hour);

        $_tros_plage += $_end - $_begin;

        if ($_end >= $_end_plage && $_begin < $_end_plage) {
          $_debordement_plage += $_end - $_end_plage;
        }
        elseif ($_begin >= $_end_plage && $_end >= $_end_plage) {
          $_debordement_plage += $_end - $_begin;
        }

        if ($_operation->urgence) {
          $_nb_urgences++;
        }

        if ($_operation->plageop_id && $op_rank == 1 && $_begin > $_late_begin) {
          $_late_beginings += ($_begin - $_begin_plage);
        }
        elseif ($_operation->plageop_id && $op_rank == count($_operations) && $_early_end > $_end) {
          $_early_endings += ($_end_plage - $_end);
        }
      }

      if (count($_operations)) {
        $_tros_plage += $_cleanup_time * (count($_operations) - 1);
        $_tpos_plage += $_cleanup_time * (count($_operations) - 1);
        $_trov_plage += $_tros_plage - $_debordement_plage;
      }

      if (!array_key_exists($_plage->_id, $results['vacations'])) {
        $results['vacations'][$_plage->_id] = $line;
      }

      switch (CMbDT::strftime('%w', strtotime("$_plage->date 00:00:00"))) {
        case 0:
          $_pot = 0;
          break;
        case '6':
          $_pot = 14400;
          break;
        default:
          $_pot = 36000;
      }

      $_line          = $results['vacations'][$_plage->_id];
      $_line['tvo']   += $_tvo_plage;
      $_line['tpos']  += $_tpos_plage;
      $_line['tros']  += $_tros_plage;
      $_line['trov']  += $_trov_plage;
      $_line['deb']   += $_debordement_plage;
      $_line['pot']   += $_pot;
      $_line['beg']   += $_late_beginings;
      $_line['end']   += $_early_endings;
      $_line['nbop']  += count($_operations);
      $_line['urg']   += $_nb_urgences;
      $_line['plage'] = $_plage;

      $results['vacations'][$_plage->_id] = $_line;
    }

    if ($this->grouping) {
      $this->groupANAPStats($results);

      foreach ($results['groupings'] as $_element_guid => $_line) {
        $this->formatANAPResult($_line);
        $results['groupings'][$_element_guid] = $_line;

        foreach ($results['vacations'][$_element_guid] as $_plage => $_line_plage) {
          $this->formatANAPResult($_line_plage);
          $results['vacations'][$_element_guid][$_plage] = $_line_plage;
        }
      }
    }
    else {
      foreach ($results['vacations'] as $_plage => $_line) {
        $this->formatANAPResult($_line);
        $results['vacations'][$_plage] = $_line;
      }
    }

    return $results;
  }


  /**
   * Group the statistic MEAH
   *
   * @param array $stats The statistic
   *
   * @return void
   */
  public function groupANAPStats(&$stats) {
    $groupings = array();
    $vacations = array();

    foreach ($stats['vacations'] as $_key => $_vacation) {
      /** @var CPlageOp $_plage */
      $_plage = $_vacation['plage'];

      switch ($this->grouping) {
        case 'bloc':
          $_element = $_plage->_ref_salle->_ref_bloc;
          break;
        case 'salle':
          $_element = $_plage->_ref_salle;
          break;
        case 'discipline':
          $_element = $_plage->_ref_chir->_ref_discipline;
          break;
        case 'prat':
        default:
          $_element = $_plage->_ref_chir;
      }

      if (!array_key_exists($_element->_guid, $groupings)) {
        $groupings[$_element->_guid] = array(
          'element' => $_element,
          'tvo'     => 0, // Temps de vacation offert
          'tpos'    => 0, // Temps programmé d'occupation de la salle
          'tros'    => 0, // Temps réel d'occupation de la salle
          'trov'    => 0, // Temps réel d'occupation des vacations
          'txoc'    => 0, // Taux d'occupation
          'pot'     => 0, // Potentiel salle(s)
          'deb'     => 0, // Débordement
          'txdeb'   => 0, // Taux de débordement
          'txper'   => 0, // Taux de performance
          'txpot'   => 0, // Taux d'utilisation du potentiel des salles
          'evtvo'   => 0, // Evaluation du TVO
          'beg'     => 0, // Démarrages tardifs
          'txbeg'   => 0, // Taux de démarrages tardifs
          'end'     => 0, // Fins précoces
          'txend'   => 0, // Taux de fins précoces
          'nbop'    => 0, // Nombre d'opérations
          'urg'     => 0, // Nombre d'urgences
          'txurg'   => 0  // Taux d'urgences
        );

        $vacations[$_element->_guid] = array();
      }

      $groupings[$_element->_guid]['tvo']  += $_vacation['tvo'];
      $groupings[$_element->_guid]['tpos'] += $_vacation['tpos'];
      $groupings[$_element->_guid]['tros'] += $_vacation['tros'];
      $groupings[$_element->_guid]['trov'] += $_vacation['trov'];
      $groupings[$_element->_guid]['deb']  += $_vacation['deb'];
      $groupings[$_element->_guid]['pot']  += $_vacation['pot'];
      $groupings[$_element->_guid]['beg']  += $_vacation['beg'];
      $groupings[$_element->_guid]['end']  += $_vacation['end'];
      $groupings[$_element->_guid]['nbop'] += $_vacation['nbop'];
      $groupings[$_element->_guid]['urg']  += $_vacation['urg'];

      $vacations[$_element->_guid][$_key] = $_vacation;
    }

    /* Compute the potential for the bloc and salle groupings */
    if ($this->grouping == 'bloc' || $this->grouping == 'salle') {
      foreach ($groupings as $_grouping) {
        $_element = $_grouping['element'];
        if ($_element->_class == 'CBlocOperatoire') {
          $groupings[$_element->_guid]['pot'] = self::getBlocPotential($_element, $this->date_min, $this->date_max);
        }
        elseif ($_element->_class == 'CSalle') {
          $groupings[$_element->_guid]['pot'] = self::getRoomPotential($_element, $this->date_min, $this->date_max);
        }
      }
    }

    $stats['groupings'] = $groupings;
    $stats['vacations'] = $vacations;
  }

  /**
   * Format a result line for the MEAH statistic
   *
   * @param array $line The line to format
   *
   * @return void
   */
  public function formatANAPResult(&$line) {
    $_tvo  = $line['tvo'];
    $_tros = $line['tros'];
    $_trov = $line['trov'];
    $_pot  = $line['pot'];
    $_deb  = $line['deb'];
    $_beg  = $line['beg'];
    $_end  = $line['end'];

    if ($_tvo > 0) {
      $line['txoc']  = round($_tros / $_tvo, 2) * 100;
      $line['txdeb'] = round($_deb / $_tvo, 2) * 100;
      $line['txper'] = round(($_trov) / $_tvo, 2) * 100;
    }
    if ($_pot > 0) {
      $line['txpot'] = round($_tros / $_pot, 2) * 100;
      $line['evtvo'] = round($_tvo / $_pot, 2) * 100;
    }
    if ($line['nbop'] > 0) {
      $line['txurg'] = round($line['urg'] / $line['nbop'], 2) * 100;
    }
    if ($_beg > 0) {
      $line['txbeg'] = round($_beg / $_tvo, 2) * 100;
    }
    if ($_end > 0) {
      $line['txend'] = round($_end / $_tvo, 2) * 100;
    }

    $line['tvo']  = self::formatDuration($_tvo);
    $line['tpos'] = self::formatDuration($line['tpos']);
    $line['tros'] = self::formatDuration($_tros);
    $line['trov'] = self::formatDuration($_trov);
    $line['pot']  = self::formatDuration($_pot);
    $line['beg']  = self::formatDuration($_beg);
    $line['end']  = self::formatDuration($_end);
    $line['deb']  = self::formatDuration($_deb);
  }

  /**
   * Set the selected context
   *
   * @return void
   */
  protected function getContext() {
    if ($this->prat_id || $this->_prats_ids) {
      $this->_context       = 'prat_id';
      $this->_context_class = 'CMediusers';
    }
    elseif ($this->discipline_id || $this->_disciplines_ids) {
      $this->_context       = 'discipline_id';
      $this->_context_class = 'CDiscipline';
    }
    elseif ($this->salle_id || $this->_salles_ids) {
      $this->_context       = 'salle_id';
      $this->_context_class = 'CSalle';
    }
    elseif ($this->bloc_id || $this->_blocs_ids) {
      $this->_context       = 'bloc_id';
      $this->_context_class = 'CBlocOperatoire';
    }
  }

  /**
   * Load the CPageOp objects according to the filters
   *
   * @param array $days         The list of the days
   * @param array $vacation_ids The vacation ids
   * @param array $plage_ids    The plageop ids
   *
   * @return CPlageOp[]
   */
  protected function loadPlagesOperatoires($days, $vacation_ids, $plage_ids) {

    $plage = new CPlageOp();

    $where = array(
      'plagesop.date'   => $this->_ds->prepareIn($days),
      'plagesop.status' => "= 'occupied'"
    );
    $ljoin = array();

    if ($vacation_ids) {
      $where['plagesop.plageop_id'] = $this->_ds->prepareIn($plage_ids);
    }

    if ($this->_prats_ids) {
      $where['plagesop.chir_id'] = $this->_ds->prepareIn($this->_prats_ids);
    }
    elseif ($this->_disciplines_ids) {
      $ljoin['users_mediboard']               = 'users_mediboard.user_id = plagesop.chir_id';
      $where['users_mediboard.discipline_id'] = $this->_ds->prepareIn($this->_disciplines_ids);
    }

    if ($this->_salles_ids) {
      $where['plagesop.salle_id'] = $this->_ds->prepareIn($this->_salles_ids);
    }
    elseif ($this->_blocs_ids) {
      $ljoin['sallesbloc']         = 'sallesbloc.salle_id = plagesop.salle_id';
      $where['sallesbloc.bloc_id'] = $this->_ds->prepareIn($this->_blocs_ids);
    }
    elseif (!$this->_prats_ids && !$this->_disciplines_ids) {
      $this->_context                    = 'group_id';
      $ljoin['sallesbloc']               = 'sallesbloc.salle_id = plagesop.salle_id';
      $ljoin['bloc_operatoire']          = 'bloc_operatoire.bloc_operatoire_id = sallesbloc.bloc_id';
      $where['bloc_operatoire.group_id'] = $this->_ds->prepare('= %', $this->group_id);
    }

    switch ($this->operations_to_display) {
      case 'except_emergencies':
        $where['plagesop.urgence'] = "= '0'";
        break;
      case 'emergencies':
        $where['plagesop.urgence'] = "= '1'";
        break;
      default:
    }

    /** @var CPlageOp[] $plages */
    $plages = $plage->loadList($where, 'plagesop.date', null, null, $ljoin);
    CStoredObject::massLoadBackRefs($plages, 'operations', null, array('annulee' => "= '0'"));
    $salles = CStoredObject::massLoadFwdRef($plages, 'salle_id');
    CStoredObject::massLoadFwdRef($salles, 'bloc_id');
    $chirs = CStoredObject::massLoadFwdRef($plages, 'chir_id');
    CStoredObject::massLoadFwdRef($chirs, 'function_id');

    return $plages;
  }

  /**
   * Checks if vacations are single or double
   *
   * @return array|null
   */
  protected function checkVacations() {
    if (!$this->plages_to_display) {
      return false;
    }

    $this->_ds->exec('SET SESSION group_concat_max_len = 100000;');

    $from  = array('plagesop AS p1');
    $where = array();

    if ($this->date_min) {
      $where[] = $this->_ds->prepare('p1.date >= ?', $this->date_min);
    }

    if ($this->date_max) {
      $where[] = $this->_ds->prepare('p1.date <= ?', $this->date_max);
    }

    if ($this->prat_id) {
      $where['p1.chir_id'] = $this->_ds->prepare('= ?', $this->prat_id);
    }
    elseif ($this->_praticien_ids) {
      $where[] = 'p1.chir_id ' . $this->_ds->prepareIn($this->_praticien_ids)
        . 'OR p1.original_owner_id ' . $this->_ds->prepareIn($this->_praticien_ids);
    }

    $where['p1.chir_id'] = 'IS NOT NULL';

    switch ($this->operations_to_display) {
      case 'except_emergencies':
        $where['p1.urgence'] = "= '0'";
        break;
      case 'emergencies':
        $where['p1.urgence'] = "= '1'";
        break;
      default:
    }

    $subrequest = new CRequest();
    $subrequest->addSelect('plageop_id');
    $subrequest->addTable('plagesop as p2');
    $subrequest->addWhere(
      array(
        'p2.date'            => ' = p1.date',
        'p2.chir_id'         => ' = p1.chir_id',
        'p2.debut_reference' => ' < p1.fin_reference',
        'p2.fin_reference'   => ' > p1.debut_reference',
        'p2.salle_id'        => ' != p1.salle_id'
      )
    );

    $where[] = ($this->plages_to_display == 'single' ? 'NOT EXISTS' : 'EXISTS') . '(' . $subrequest->makeSelect() . ')';

    $request = new CRequest();
    $request->addSelect("GROUP_CONCAT(CAST(p1.plageop_id AS CHAR) SEPARATOR '-')");
    $request->addTable($from);
    $request->addWhere($where);

    $request->addGroup('p1.chir_id');

    return $this->_ds->loadColumn($request->makeSelect());
  }

  /**
   * Load all the operations of the given CPlageOp
   *
   * @param integer $plage_id The CPlageOp id
   *
   * @return COperation[]
   */
  public function getVacationDetails($plage_id) {
    $operation = new COperation();

    $where['operations.plageop_id'] = $this->_ds->prepare('= %', $plage_id);

    switch ($this->operations_to_display) {
      case 'except_emergencies':
        $where['operations.urgence'] = "= '0'";
        break;

      case 'emergencies':
        $where['operations.urgence'] = "= '1'";
        break;
      default:
    }

    /** @var COperation[] $operations */
    $operations = $operation->loadList($where, 'operations.date');

    CStoredObject::massLoadFwdRef($operations, 'anesth_id');
    CStoredObject::massLoadFwdRef($operations, 'chir_id');
    /** @var CSejour[] $sejours */
    $sejours = CStoredObject::massLoadFwdRef($operations, 'sejour_id');
    CStoredObject::massLoadFwdRef($sejours, 'patient_id');
    CSejour::massLoadNDA($sejours);

    foreach ($operations as $_operation) {
      $_operation->loadRefPatient();
      $_operation->_ref_patient->loadComplete();
      $_operation->loadRefSejour();
      $_operation->loadRefPlageOp();
      $_operation->_ref_sejour->loadNDA($this->group_id);
      $_operation->loadRefChir();
      $_operation->_ref_chir->loadRefFunction();
      $_operation->loadRefSalle();
      $_operation->_ref_salle->loadRefBloc();
      $_operation->updateFormFields();
    }

    return $operations;
  }

  /**
   * Return the context user
   *
   * @return CMediusers|CStoredObject|null
   */
  protected function getContextUser() {
    $context_user = null;

    if ($this->_prats_ids && count($this->_prats_ids) == 1) {
      $context_user = CMediusers::get($this->_prats_ids[0]);
      $context_user->loadRefFunction();
    }
    elseif ($this->_disciplines_ids && count($this->_disciplines_ids) == 1) {
      $context_user = CStoredObject::loadFromGuid("CDiscipline-" . $this->_disciplines_ids[0]);
    }

    return $context_user;
  }

  /**
   * Return the context place
   *
   * @param mixed $context_user The context user
   *
   * @return CGroups|CSalle|CStoredObject|null
   */
  protected function getContextPlace($context_user) {
    $context_place = null;

    if ($this->_salles_ids && count($this->_salles_ids) == 1) {
      /** @var CSalle $context_place */
      $context_place = CStoredObject::loadFromGuid("CSalle-" . $this->_salles_ids[0]);
      $context_place->loadRefBloc();
    }
    elseif ($this->_blocs_ids && count($this->_blocs_ids) == 1) {
      $context_place = CStoredObject::loadFromGuid("CBlocOperatoire-" . $this->_blocs_ids[0]);
    }

    if (is_null($context_place) && is_null($context_user)) {
      $context_place = CGroups::loadCurrent();
    }

    return $context_place;
  }

  /**
   * Return the potential of a bloc (the available time in the room) for the given period
   *
   * @param CBlocOperatoire $bloc     The bloc
   * @param string          $date_min The min date
   * @param string          $date_max The max date
   *
   * @return int
   */
  protected static function getBlocPotential($bloc, $date_min, $date_max) {
    $potential = 0;
    $bloc->loadRefsSalles();
    foreach ($bloc->_ref_salles as $_salle) {
      $potential += self::getRoomPotential($_salle, $date_min, $date_max);
    }

    return $potential;
  }

  /**
   * Return the potential of a room (the available time in the room) for the given period
   *
   * @param CSalle $salle    The salle
   * @param string $date_min The min date
   * @param string $date_max The max date
   *
   * @return int
   */
  protected static function getRoomPotential($salle, $date_min, $date_max) {
    $days      = CMbDT::getDays($date_min, $date_max);
    $potential = 0;

    $where        = array(
      "deb <= '$date_max'",
      "fin >= '$date_min'",
      "salle_id = $salle->_id"
    );
    $blocage      = new CBlocage();
    $blocages     = $blocage->loadList($where, null, null, 'blocage_id');
    $blocked_days = array();
    foreach ($blocages as $_blocage) {
      $blocked_days = array_merge($blocked_days, array_diff(CMbDT::getDays($_blocage->deb, $_blocage->fin), $blocked_days));
    }

    $days = array_diff($days, $blocked_days);

    foreach ($days as $_day) {
      $_day_of_week = CMbDT::format($_day, '%w');

      if ($_day_of_week > 0 && $_day_of_week < 6) {
        $potential += 36000;
      }
      elseif ($_day_of_week == 6) {
        $potential += 14400;
      }
    }

    return $potential;
  }

  /**
   * Format a duration in seconds to a human readable string
   *
   * @param int $secs The seconds
   *
   * @return string
   */
  public static function formatDuration($secs) {
    $seconds = $secs % 60;
    $minutes = floor(($secs % 3600) / 60);
    $hours   = floor($secs / 3600);

    if ($seconds >= 30) {
      $minutes++;
    }
    /* If the previous condition is met, the minutes can go up to 60 */
    if ($minutes >= 60) {
      $hours++;
      $minutes -= 60;
    }

    $minutes = str_pad(abs($minutes), 2, "0", STR_PAD_LEFT);
    $hours   = str_pad($hours, 2, "0", STR_PAD_LEFT) . 'h';

    return "$hours$minutes";
  }

  /**
   * Convert a time to a duration in seconds
   *
   * @param string $time The time
   *
   * @return integer
   */
  public static function timeToSeconds($time) {
    [$hours, $minutes, $seconds] = explode(':', $time);
    $seconds += $minutes * 60 + $hours * 3600;

    return $seconds;
  }
}
