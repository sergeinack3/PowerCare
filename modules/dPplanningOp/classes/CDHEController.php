<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\PlanningOp;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Hospi\CAffectation;

/**
 * The controller for the new DHE, that can modify or delete several objects
 */
class CDHEController implements IShortNameAutoloadable {

  /** @var CSejour The sejour */
  public $sejour;

  /** @var COperation The operation */
  public $operation;

  /** @var CConsultation The consultation */
  public $consultation;

  /** @var array The data */
  public $data;

  /**
   * CDHEController constructor.
   *
   * @param array $data The data from the DHE
   */
  public function __construct($data = array()) {
    $this->sejour = new CSejour();
    $this->operation = new COperation();
    $this->consultation = new CConsultation();
    $this->data = $data;
  }

  /**
   * Do the given action
   *
   * @param string $action The action to perform
   *
   * @return void
   */
  public function doIt($action) {
    switch ($action) {
      case 'cancel':
        $this->doCancel();
        break;
      case 'delete':
        $this->doDelete();
        break;
      case 'save':
      case 'store':
        $this->doStore();
        break;
      default:
    }

    $this->doRedirect($action);
  }

  /**
   * Store the DHE objects
   *
   * @return void
   */
  public function doStore() {
    if (array_key_exists('sejour', $this->data) && $this->data['sejour']) {
      if ($msg = $this->doStoreSejour($this->data['sejour'])) {
        CApp::rip();
      }
    }

    if (array_key_exists('operation', $this->data) && $this->data['operation']) {
      if ($msg = $this->doStoreOperation($this->data['operation'])) {
        CApp::rip();
      }
    }

    if (array_key_exists('consultation', $this->data) && $this->data['consultation']) {
      if ($msg = $this->doStoreConsultation($this->data['consultation'])) {
        CApp::rip();
      }
    }
  }

  /**
   * Delete the DHE objects
   *
   * @return null|string Null if the delete is successful, the error message otherwise
   */
  public function doDelete() {
    foreach ($this->data['object_guid'] as $_guid) {
      $_object  = CMbObject::loadFromGuid($_guid);

      if ($_object->_id) {
        if ($msg = $_object->delete()) {
          CAppUI::setMsg($msg, UI_MSG_ERROR);
          return $msg;
        }
        else {
          CAppUI::setMsg("$_object->_class-msg-delete", UI_MSG_OK);
        }
      }
    }

    return null;
  }

  /**
   * Cancel the DHE objects
   *
   * @return null|string Null if the store is successful, the error message otherwise
   */
  public function doCancel() {
    foreach ($this->data['object_guid'] as $_guid) {
      $_object  = CMbObject::loadFromGuid($_guid);

      if ($_object->_id) {
        switch ($_object->_class) {
          case 'COperation':
            $_object->annulee = '1';
            break;
          case 'CConsultation':
          case 'CSejour':
            $_object->annule = '1';
            break;
          default:
        }

        if ($msg = $_object->store()) {
          CAppUI::setMsg($msg, UI_MSG_ERROR);
          return $msg;
        }
        else {
          CAppUI::setMsg("$_object->_class-msg-modify", UI_MSG_OK);
        }
      }
    }

    return null;
  }

  /**
   * Store the sejour
   *
   * @param array $data The data of the sejour
   *
   * @return null|string Null if the store is successful, the error message otherwise
   */
  public function doStoreSejour($data = array()) {
    $sejour = $this->sejour;
    $this->setObjectFromData($sejour, $data);

    $old = $sejour->loadOldObject();
    $rpu = $sejour->loadRefRPU();
    $sejour->loadRefCurrAffectation();

    if (CAppUI::conf('urgences create_affectation') && $sejour->mode_sortie
        && $rpu->_id && (($sejour->_unique_lit_id && $sejour->_ref_curr_affectation->lit_id != $sejour->_unique_lit_id)
        && ($sejour->service_sortie_id && $sejour->service_id != $sejour->_ref_curr_affectation))
    ) {
      $affectation = new CAffectation();
      $affectation->entree = CMbDT::dateTime();
      $affectation->lit_id     = $sejour->_unique_lit_id;
      $affectation->service_id = $sejour->service_sortie_id;

      // Mutation en provenance des urgences
      $affectation->_mutation_urg = true;

      $sejour->forceAffectation($affectation);

    }

    if ($msg = $sejour->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
    }
    else {
      CAppUI::setMsg($old->_id ? 'CSejour-msg-modify' : 'CSejour-msg-create', UI_MSG_OK);
    }

    return $msg;
  }

  /**
   * Store the operation
   *
   * @param array $data The data of the operation
   *
   * @return null|string Null if the store is successful, the error message otherwise
   */
  public function doStoreOperation($data = array()) {
    $operation = $this->operation;
    $this->setObjectFromData($operation, $data);

    /** @var COperation $old */
    $old = $operation->loadOldObject();
    if ($operation->_id) {
      if ($operation->plageop_id && $operation->plageop_id != $old->plageop_id) {
        $operation->rank = 0;

        $plage = new CPlageOp();
        $plage->load($old->plageop_id);
        $plage->spec_id = '';

        if ($msg = $plage->store()) {
          return $msg;
        }
      }
    }
    elseif (!$operation->sejour_id) {
      $operation->sejour_id = $this->sejour->_id;
    }

    $operation->_time_op = $data['_time_op'];
    $operation->_time_urgence = $data['_time_urgence'];

    if ($msg = $operation->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
    }
    else {
      CAppUI::setMsg($old->_id ? 'COperation-msg-modify' : 'COperation-msg-create', UI_MSG_OK);
    }

    return $msg;
  }

  /**
   * Store the consultation
   *
   * @param array $data The data of the consultation
   *
   * @return null|string Null if the store is successful, the error message otherwise
   */
  public function doStoreConsultation($data = array()) {
    $consult = $this->consultation;
    $this->setObjectFromData($consult, $data);
    $consult->heure = CMbDT::time($consult->_datetime);

    $old = $consult->loadOldObject();
    /* In case of an immediate consultation, we set the CPlageConsult */
    if ($data['_type'] == 'immediate' && $consult->_datetime) {
      $time = CMbDT::time($consult->_datetime);
      $hour = CMbDT::format($consult->_datetime, '%H:0:0');
      $duration = $consult->duree * CPlageconsult::$minutes_interval;
      $hour_end = CMbDT::time('+ ' . (intval($duration / 60) + 1) . ' HOUR', $hour);

      $where = array(
        'chir_id' => " = {$data['chir_id']}",
        'date'    => " = '" . CMbDT::date($consult->_datetime) . "'",
        'debut'   => " <= '$time'",
        'fin'     => " > '$time'"
      );

      $plage = new CPlageconsult();
      $plage->loadObject($where);

      if (!$plage->_id) {
        $plage_before = new CPlageconsult();
        $where['debut'] = " <= '$hour'";
        $where['fin']   = " >= '$hour'";
        $plage_before->loadObject($where);

        $plage_after = new CPlageconsult();
        $where['debut'] = " <= '$hour_end'";
        $where['fin']   = " >= '$time'";
        $plage_after->loadObject($where);

        if ($plage_before->_id) {
          if ($plage_after->_id) {
            $plage_before->fin = $plage_after->debut;
          }
          else {
            $plage_before->fin = max($plage_before->fin, $hour_end);
          }

          $plage = $plage_before;
        }
        elseif ($plage_after->_id) {
          $plage_after->debut = min($plage_after->debut, $hour);
          $plage = $plage_after;
        }
        else {
          $duration = $consult->duree * CPlageconsult::$minutes_interval;
          $plage->chir_id = $data['chir_id'];
          $plage->date    = CMbDT::date($consult->_datetime);
          $plage->freq    = CMbDT::time("+ $duration min", '00:00:00');
          $plage->debut   = $hour;
          $plage->fin     = $hour_end;
          $plage->libelle = 'automatique';
          $plage->_immediate_plage = 1;
        }
      }

      $plage->updateFormFields();
      if ($msg = $plage->store()) {
        return $msg;
      }

      $consult->plageconsult_id = $plage->_id;
    }

    if (!$consult->_id && !$consult->sejour_id) {
      $consult->sejour_id = $this->sejour->_id;
    }

    $consult->updateFormFields();

    if ($msg = $consult->store()) {
      CAppUI::setMsg($msg, UI_MSG_ERROR);
    }
    else {
      CAppUI::setMsg($old->_id ? 'CConsultation-msg-modify' : 'CConsultation-msg-create', UI_MSG_OK);
    }

    return $msg;
  }

  /**
   * Redirect the user
   *
   * @param string $action The action performed
   *
   * @return void
   */
  public function doRedirect($action) {
    $redirect = 'm=planningOp&tab=vw_dhe';
    switch ($action) {
      case 'save':
      case 'store':
        if (array_key_exists('operation', $this->data) && $this->data['operation']) {
          $redirect .= "&operation_id={$this->operation->_id}";
        }
        elseif (array_key_exists('consultation', $this->data) && $this->data['consultation']) {
          $redirect .= "&consultation_id={$this->consultation->_id}";
        }
        elseif (array_key_exists('sejour', $this->data) && $this->data['sejour']) {
          $redirect .= "&sejour_id={$this->sejour->_id}";
        }
        break;
      default:
        if (array_key_exists('operation', $this->data) && $this->data['operation']) {
          $redirect .= "&operation_id=";
        }
        elseif (array_key_exists('consultation', $this->data) && $this->data['consultation']) {
          $redirect .= "&consultation_id=";
        }
        else {
          $redirect .= "&sejour_id=";
        }
    }

    CAppUI::redirect($redirect);
  }

  /**
   * Set the properties of the given object from the given data
   *
   * @param CMbObject $object The object
   * @param array     $data   The data
   *
   * @return void
   */
  public function setObjectFromData($object, $data) {
    $object->load($data[$object->_spec->key]);

    foreach ($data as $_property => $_value) {
      if (property_exists($object, $_property) && $_property != $object->_spec->key) {
        $object->$_property = $_value;
      }
    }

    $object->updateFormFields();
  }
}
