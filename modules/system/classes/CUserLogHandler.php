<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;
use Ox\Core\CApp;
use Ox\Core\CMbObject;
use Ox\Core\Handlers\ObjectHandler;
use Ox\Core\CStoredObject;
use Ox\Core\Logger\LoggerLevels;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Class CUserLogHandler
 */
class CUserLogHandler extends ObjectHandler {
  static $function_id;
  static $patient_id;

  static $handled_classes = array(
    'CPatient',
    'CConsultation',
    'CFile',
    'CConstantesMedicales',
    'CDossierMedical',
    'CEvenementPatient',
    'CPathologie',
    'CAntecedent',
    'CTraitement',
    'CInclusionProgramme',
    'CPrescription',
    'CPrescriptionLine',
    'CPrescriptionLineMix',
    'CPrescriptionLineElement',
    'CPrescriptionLineMedicament',
    'CPrescriptionLineComment',
    'CPrisePosologie',
  );

  static $handled_contexts = array(
    'CFile' => array(
      'CPatient',
      'CSejour',
      'CPrescription',
      'CConsultation',
    ),
    'CDossierMedical' => array(
      'CPatient',
    ),
    'CPrescription' => array(
      'CConsultation',
      'CDossierMedical',
      'CSejour',
    ),
    'CPrisePosologie' => array(
      'CPrescriptionLineMedicament',
      'CPrescriptionLineElement',
      'CPrescriptionLineMix',
    )
  );

  /**
   * Gets handled classes
   *
   * @return array
   */
  static function getHandled() {
    return self::$handled_classes;
  }

  /**
   * Gets handled contexts
   *
   * @param string $context Context (array index)
   *
   * @return bool|array
   */
  static function getHandledContexts($context) {
    if (!$context || !isset(self::$handled_contexts[$context])) {
      return false;
    }

    return self::$handled_contexts[$context];
  }

  /**
   * @inheritdoc
   */
  static function isHandled(CStoredObject $object) {
    if (!parent::isHandled($object)
        || !($object instanceof CUserLog)
        || !in_array($object->object_class, self::getHandled())
    ) {
      return false;
    }

    return true;
  }

  /**
   * @inheritdoc
   */
  function onBeforeStore(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }

    $ref_object = $object->loadTargetObject();
    if (!self::getFunction($ref_object)) {
        CApp::log('Cannot get function id', $ref_object, LoggerLevels::LEVEL_DEBUG);

      return false;
    }

    return true;
  }

  /**
   * @inheritdoc
   */
  function onAfterStore(CStoredObject $object) {
    if (!$this->isHandled($object)) {
      return false;
    }

    if (!self::$function_id || !self::$patient_id) {
      return false;
    }

    $user = CMediusers::get();
    if (self::$function_id !== $user->function_id) {
      $patient_log = new CPatientLog();
      $patient_log->user_log_id = $object->_id;
      $patient_log->patient_id = self::$patient_id;

      if ($msg = $patient_log->store()) {
          CApp::log($msg, $object, LoggerLevels::LEVEL_DEBUG);

        return false;
      }
    }

    return true;
  }

  /**
   * Gets function id
   *
   * @param CMbObject $object Object
   *
   * @return bool
   */
  static function getFunction($object) {
    switch ($object->_class) {
      case 'CPatient':
        return self::getCPatientFunction($object);
      case 'CConsultation':
      case 'CConstantesMedicales':
      case 'CInclusionProgramme':
        return self::getCPatientFwdRefFunction($object);
      case 'CFile':
      case 'CDossierMedical':
        return self::getMetaObjectFunction($object);
      case 'CEvenementPatient':
      case 'CPathologie':
      case 'CAntecedent':
      case 'CTraitement':
        return self::getCDossierMedicalFwdRefFunction($object);
      case 'CPrescription':
      case 'CPrisePosologie':
        return self::getCPrescriptionFunction($object);
      case 'CPrescriptionLine':
      case 'CPrescriptionLineMix':
      case 'CPrescriptionLineMedicament':
      case 'CPrescriptionLineComment':
        return self::getCPrescriptionFwdRefFunction($object);
      default:
        return false;
    }
  }

  /**
   * Gets function id
   *
   * @param CMbObject $object Object
   *
   * @return bool
   */
  static function getCPatientFunction($object) {
    self::$function_id = $object->function_id;
    self::$patient_id = $object->_id;

    return true;
  }

  /**
   * Gets function id
   *
   * @param CMbObject $object Object
   *
   * @return bool
   */
  static function getCPatientFwdRefFunction($object) {
    $patient = $object->loadRefPatient();

    return self::getFunction($patient);
  }

  /**
   * Gets function id
   *
   * @param CMbObject $object Object
   *
   * @return bool
   */
  static function getMetaObjectFunction($object) {
    $context = method_exists($object, 'loadTargetObject') ? $object->loadTargetObject() : false;

    if (!$context || !$context->_id || !in_array($context->_class, self::getHandledContexts($object->_class))) {
      return false;
    }

    return self::getFunction($context);
  }

  /**
   * Gets function id
   *
   * @param CMbObject $object Object
   *
   * @return bool
   */
  static function getCDossierMedicalFwdRefFunction($object) {
    $dossier = $object->loadRefDossierMedical();

    return self::getFunction($dossier);
  }

  /**
   * Gets function id
   *
   * @param CMbObject $object Object
   *
   * @return bool
   */
  static function getCPrescriptionFunction($object) {
    $context = CStoredObject::loadFromGuid("$object->object_class-$object->object_id");

    if (!$context || !$context->_id || !in_array($context->_class, self::getHandledContexts($object->_class))) {
      return false;
    }

    return self::getFunction($context);
  }

  /**
   * Gets function id
   *
   * @param CMbObject $object Object
   *
   * @return bool
   */
  static function getCPrescriptionFwdRefFunction($object) {
    $prescription = $object->loadRefPrescription();

    if (!$prescription || !$prescription->_id) {
      return false;
    }

    return self::getFunction($prescription);
  }

}
