<?php
/**
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Search;

use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Files\CFile;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\Handlers\ObjectHandler;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * Class CSearchObjectHandler
 * Attention celui ci n'est pas actif par défaut dans le module Administration.
 */
class CSearchObjectHandler extends ObjectHandler {
  /**
   * @var array
   */
  static $handled = array();


  /**
   * Check the types which are handled.
   *
   * @param CMbObject $object the object
   *
   * @return bool
   */
  static function checkHandled($object) {
    if (!CAppUI::$user || !CAppUI::$user->_id) {
      return false;
    }

    $group = self::loadRefGroup($object);
    if(!$group->_id){
      $group = CGroups::loadCurrent();
    }

    if ($group && $group->_id && $handled = CAppUI::gconf("search active_handler active_handler_search_types", $group->_id)) {
      self::$handled = explode("|", $handled);
    }

    return true;
  }

  /**
   * @inheritdoc
   */
  static function isHandled(CStoredObject $object) {
    return in_array($object->_class, self::$handled);
  }

  /**
   * @inheritdoc
   */
  function onAfterStore(CStoredObject $object) {
    $this->checkHandled($object);
    if (!$this->isHandled($object)) {
      return false;
    }

    return self::requesthandler($object);
  }

  /**
   * @inheritdoc
   */
  function onBeforeDelete(CStoredObject $object) {
    $this->checkHandled($object);
    if (!$this->isHandled($object)) {
      return false;
    }
    $object->_save_id = $object->_id;

    return true;
  }

  /**
   * @inheritdoc
   */
  function onAfterDelete(CStoredObject $object) {
    $this->checkHandled($object);

    if (!$this->isHandled($object)) {
      return false;
    }

    return self::requesthandler($object, 'delete');
  }

  /**
   * Function to call in static way in class.
   *
   * @param CMbObject $object the object you want to handled
   * @param string    $type   [optionnal] the action to do
   *
   * @return bool
   */
  static function requesthandler(CMbObject $object, $type = null) {
    self::checkHandled($object);
    if ($object instanceof CCompteRendu && !$object->object_id) {
      // si c'est un modèle de document
      return false;
    }
    if ($object instanceof CFile
        && !in_array($object->object_class, array('CSejour', 'CConsultation', 'CConsultAnesth', 'COperation'))
    ) {
      return false;
    }
    if ($object instanceof CPrescriptionLineMedicament && $object->loadRefPrescription()->loadRefObject() instanceof CDossierMedical) {
      return false;
    }

    if (!$type) {
      if (!$object->_ref_current_log) {
        $type = "create";
      }
      else {
        $type = $object->_ref_current_log->type;
      }
    }

    $search_indexing               = new CSearchIndexing();
    $search_indexing->type         = $type;
    $search_indexing->date         = CMbDT::dateTime();
    $search_indexing->object_class = $object->_class;
    $search_indexing->object_id    = ($object->_id) ? $object->_id : $object->_save_id;// save_id dans le cas du delete

    $group = self::loadRefGroup($object);
    if(!$group->_id){
      $group = CGroups::loadCurrent();
    }
    if (!CAppUI::gconf("search active_handler active_handler_search", $group->_id)) {
      return false;
    }
    $search_indexing->store();

    return true;
  }

  /**
   * Load Group from CMbObject
   *
   * @param CMbObject $object CMbObject
   *
   * @return CGroups
   */
  static function loadRefGroup($object) {
    switch ($object->_class) {
      case 'CCompteRendu':
        /** @var CCompteRendu $object */
        $object->completeField("author_id");
        $object->loadRefAuthor();
        $group = $object->_ref_author->loadRefFunction()->loadRefGroup();
        break;
      case 'CConsultAnesth':
      case 'COperation':
        /** @var CConsultAnesth $object */
        $object->loadRefChir();
        $group = $object->_ref_chir->loadRefFunction()->loadRefGroup();
        break;
      case 'CConsultation':
      case 'CPrescriptionLineMedicament':
      case 'CPrescriptionLineMix':
      case 'CPrescriptionLineElement':
        $object->loadRefPraticien();
        $group = $object->_ref_praticien->loadRefFunction()->loadRefGroup();
        break;
      case 'CObservationMedicale':
      case 'CTransmissionMedicale':
        $object->completeField("user_id");
        $object->loadRefUser();
        $group = $object->_ref_user->loadRefFunction()->loadRefGroup();
        break;
      case 'CFile':
        /** @var CFile $object */
        $object->completeField("author_id");
        $object->loadRefAuthor();
        $group = $object->_ref_author->loadRefFunction()->loadRefGroup();
        break;
      default:
        if ($object->_class instanceof CExObject) {
          $group = $object->loadRefGroup();
        }
        else {
          return new CGroups();
        }
    }

    return $group;
  }
}
