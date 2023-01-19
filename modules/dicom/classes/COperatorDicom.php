<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Dicom;

use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSQLDataSource;
use Ox\Interop\Dicom\Network\Messages\CDicomPDV;
use Ox\Interop\Dicom\Network\Pdu\CDicomPDUFactory;
use Ox\Interop\Eai\CEAIOperator;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CObjectToInteropSender;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * The COperatorDicom class
 */
class COperatorDicom extends CEAIOperator {

  /**
   * Handle a message, and return the response
   * 
   * @param CExchangeDataFormat $data_format The dicom exchange
   * 
   * @return array
   * @throws \Exception
   */
  function event(CExchangeDataFormat $data_format) {
    $dicom_exchange = new CExchangeDicom();
    if ($data_format->_exchange_id) {
      $old_requests = $data_format->_requests;
      $pres_contexts = $data_format->_presentation_contexts;
      $dicom_exchange->load($data_format->_exchange_id);
      $dicom_exchange->decodeContent();
      $dicom_exchange->_presentation_contexts = $pres_contexts;
      $dicom_exchange->_requests = array_merge($dicom_exchange->_requests, $old_requests);
    }
    else {
      $dicom_exchange = $data_format;
      $dicom_exchange->date_production = CMbDT::dateTime();
      $dicom_exchange->send_datetime   = CMbDT::dateTime();
    }

    $last_pdvs = $dicom_exchange->_requests[count($dicom_exchange->_requests) - 1]->getPDVs();

    $response = array();
    $time_deb_pdv = microtime(true);

    foreach ($last_pdvs as $last_pdv) {
      if ($last_pdv->getMessageControlHeader() == 0 || $last_pdv->getMessageControlHeader() == 2) {
        $find_rq_pdv = null;

        if (count($last_pdvs) > 1) {
          $find_rq_pdv = $last_pdvs[0];
        }
        else {
          for ($i = count($dicom_exchange->_requests) - 2; $i >= 0; $i--) {
            $_pdv = $dicom_exchange->_requests[$i]->getPDV(0);
            if ($_pdv->getMessage()->type == "C-Find-RQ") {
              $find_rq_pdv = $_pdv;
              break;
            }
          }
        }

        if (!$find_rq_pdv) {
          return null;
        }
        $response = self::handleCDatas($find_rq_pdv, $last_pdv, $dicom_exchange);
      }
      else {
        // The PDV contain a header
        $msg = $last_pdv->getMessage();
        switch ($msg->getCommandField()->getValue()) {
          case 0x0030:
            $response = self::handleCEchoRQ($last_pdv, $dicom_exchange);
            break;
          case 0x8030:
            $response = self::handleCEchoRSP();
            break;
          case 0x0020:
            $response = self::handleCFindRQ($last_pdv, $dicom_exchange);
            break;
          case 0x8020:
            $response = self::handleCFindRSP();
            break;
          case 0x0FFF:
            $response = self::handleCCancelFindRQ();
            break;
          default:
        }
      }
    }

    $time_aft_pdv = microtime(true);
    $dicom_exchange->response_datetime = CMbDT::dateTime();
    $dicom_exchange->store();
    $response["exchange_id"] = $dicom_exchange->_id;

    return $response;
  }

  /**
   * Handle a C-Echo-RQ message, and return the answer
   * 
   * @param CDicomPDV      $pdv            The PDV who contains the C-Echo-RQ message
   * @param CExchangeDicom $dicom_exchange The Exchange Dicom
   * 
   * @return array
   */
  protected static function handleCEchoRQ($pdv, $dicom_exchange) {
    $msg = $pdv->getMessage();
    $datas = array(
      "PDVs" => array(
        array(
          "pres_context_id"        => $pdv->getPresContextId(),
          "message_control_header" => 0x03,
          "message"                => array(
            "type"  => 0x8030,
            "datas" => array(
              "affected_sop_class" => $msg->getAffectedSopClass()->getValue(),
              "command_field"      => 0x8030,
              "message_id_request" => $msg->getMessageId()->getValue(),
              "command_data_set"   => 0x0101,
              "status"             => 0x0000,
            ),
          ),
        ),
      ),
    );

    $pdu = CDicomPDUFactory::encodePDU("04", $datas, $dicom_exchange->_presentation_contexts);

    if (!$dicom_exchange->_responses) {
      $dicom_exchange->_responses = array();
    }

    $dicom_exchange->_responses[] = $pdu;
    $dicom_exchange->acquittement_valide = 1;
    $dicom_exchange->type = "Echo";

    return array("event" => "PDataTF_Prepared", "datas" => $pdu);
  }
  
  /**
   * Handle a C-Echo-RQ message, and return the answer
   * 
   * @return array
   */
  protected static function handleCEchoRSP() {
    return array("event" => "AReleaseRQ_Prepared", "datas" => null);
  }
  
  /**
   * Handle a C-Find-RQ message, and return the answer
   * 
   * @param CDicomPDV      $pdv            The PDV who contains the C-Echo-RQ message
   * @param CExchangeDicom $dicom_exchange The Exchange Dicom
   * 
   * @return array
   */
  protected static function handleCFindRQ($pdv, $dicom_exchange) {
    $msg = $pdv->getMessage();
    
    // if the message does not contains datas :
    if ($msg->getCommandDataSet()->getValue() == 0x0101 && $msg->getAffectedSopClass()->getValue() == '1.2.840.10008.5.1.4.31') {
      return array("event" => "AAbort_Prepared", "datas" => 5);
    }

    return array();
  }
  
  /**
   * Handle a C-Find-RSP message, and return the answer
   * 
   * @return array
   */
  protected static function handleCFindRSP() {
    return array("event" => "AAbort_Prepared", "datas" => 5);
  }
  
  /**
   * Handle a C-Cancel-Find-RQ message, and return the answer
   * 
   * @return array
   */
  protected static function handleCCancelFindRQ() {
    return array("event" => "AAbort_Prepared", "datas" => 5);
  }
  
  /**
   * Handle a C-Data message, and return the answer
   *
   * @param CDicomPDV      $find_rq_pdv    The PDV who contains the C-Find-RQ message
   * @param CDicomPDV      $find_data_pdv  The PDV who contains the C-Find-Data message
   * @param CExchangeDicom $dicom_exchange The Exchange Dicom
   *
   * @todo Le code est spécifique à la Source, il faudrait le rendre générique
   *
   * @return array
   */
  protected static function handleCDatas($find_rq_pdv, $find_data_pdv, $dicom_exchange) {
    $msg_rq = $find_rq_pdv->getMessage();
    $msg_data = $find_data_pdv->getMessage();

    /* If the message is a request : */
    if (is_null($dicom_exchange->_responses)) {
      $requested_datas = $msg_data->getRequestedDatas();
      $sender = $dicom_exchange->loadRefSender();

      if (!$sender->_id) {
        return array("event" => "AAbort_Prepared", "datas" => 5);
      }

      /* Loading the objects linked to the Dicom sender */
      $linked_objects = CObjectToInteropSender::loadAllObjectsFor($sender->_id);

      $find_rsp_pending_datas = array(
        "PDVs" => array(
          array(
            "pres_context_id"        => $find_data_pdv->getPresContextId(),
            "message_control_header" => 0x03,
            "message"                => array(
              "type"  => 0x8020,
              "datas" => array(
                "affected_sop_class" => $msg_rq->getAffectedSopClass()->getValue(),
                "command_field"      => 0x8020,
                "message_id_request" => $msg_rq->getMessageId()->getValue(),
                "command_data_set"   => 0xfefe,
                "status"             => 0xff00,
              ),
            ),
          ),
        ),
      );

      /* Encoding the PDU that separate the patients data */
      $find_rsp_pending = CDicomPDUFactory::encodePDU(0x04, $find_rsp_pending_datas, $dicom_exchange->_presentation_contexts);

      $calling_ae_title = self::getRequestedAETitle($requested_datas);
      $modality = self::getRequestedModality($requested_datas, $dicom_exchange);

      /* Getting the requested encoding */
      $encoding = null;
      $rq_datasets = $find_data_pdv->getMessage()->getDatasets();

      if (isset($rq_datasets[0x0008][0x0005])) {
        $encoding_dataset = $rq_datasets[0x0008][0x0005];
        $encoding = $encoding_dataset->getValue();
      }

      $responses = array();

      /* Loading the objects (CSejour or COperation) from the linked objects */
      $objects = self::loadObjectsFromLinkedObjects($linked_objects);

      if ($objects) {
        foreach ($objects as $_object) {
          $responses[] = $find_rsp_pending;

          $find_rsp_datas = array(
            "PDVs" => array(
              array(
                "pres_context_id"        => $find_data_pdv->getPresContextId(),
                "message_control_header" => 0x02,
                "message"                => array(
                  "type"  => "data",
                  "datas" => self::getDataFromObject($_object, $encoding, $modality, $calling_ae_title, $dicom_exchange->getConfigs()),
                )
              ),
            )
          );

          $responses[] = CDicomPDUFactory::encodePDU(0x04, $find_rsp_datas, $dicom_exchange->_presentation_contexts);
        }
      }

      $find_rsp_success_datas = array(
        "PDVs" => array(
          array(
            "pres_context_id"        => $find_data_pdv->getPresContextId(),
            "message_control_header" => 0x03,
            "message"                => array(
              "type"  => 0x8020,
              "datas" => array(
                "affected_sop_class" => $msg_rq->getAffectedSopClass()->getValue(),
                "command_field"      => 0x8020,
                "message_id_request" => $msg_rq->getMessageId()->getValue(),
                "command_data_set"   => 0x0101,
                "status"             => 0x0000,
              ),
            ),
          ),
        ),
      );

      $responses[] = CDicomPDUFactory::encodePDU(0x04, $find_rsp_success_datas, $dicom_exchange->_presentation_contexts);

      if (!$dicom_exchange->_responses) {
        $dicom_exchange->_responses = $responses;
      }
      else {
        array_merge($dicom_exchange->_responses, $responses);
      }

      return array("event" => "PDataTF_Prepared", "datas" => $responses);
    }
    else {
      /* The message is a response : */
      return array("event" => "AAbort_Prepared", "datas" => 5);
    }
  }

  /**
   * Load the objects to send from the linked objects
   *
   * @param CMbObject[] $linked_objects The linked objects
   *
   * @return array|CMbObject[]
   */
  protected static function loadObjectsFromLinkedObjects($linked_objects) {
    $linked_object = reset($linked_objects);

    switch ($linked_object->object_class) {
      case 'CSalle':
        $objects = self::loadOperationsFromLinkedObjects($linked_objects);
        break;
      case 'CService':
        $objects = self::loadSejoursFromLinkedObjects($linked_objects);
        break;
      default:
        $objects = array();
    }

    return $objects;
  }

  /**
   * Load the COperation to send from the linked CSalle objects
   *
   * @param CSalle[] $linked_objects The linked objects
   *
   * @return COperation[]
   * @throws \Exception
   */
  protected static function loadOperationsFromLinkedObjects($linked_objects) {
    $salles = array();

    foreach ($linked_objects as $_linked_object) {
      $salles[] = $_linked_object->object_id;
    }

    $operation = new COperation;
    $where = array(
      'salle_id'    => CSQLDataSource::prepareIn($salles),
      'date'        => " = '" . CMbDT::date() . "'",
      'plageop_id'  => ' IS NULL',
      'annulee'     => " = '0'"
    );

    /* Loading the unplanned operations */
    $unplanned_operations = $operation->loadList($where);

    $ljoin = array(
      "plagesop" => "plagesop.plageop_id = operations.plageop_id"
    );

    $where = array(
      "plagesop.salle_id"   => CSQLDataSource::prepareIn($salles),
      "plagesop.date"       => " = '" . CMbDT::date() . "'",
      "operations.salle_id" => CSQLDataSource::prepareIn($salles),
      'operations.annulee'  => " = '0'"
    );

    /* Loading the planned operations */
    $operations = $operation->loadList($where, null, null, null, $ljoin);

    $operations = array_merge($operations, $unplanned_operations);

    CMbObject::massLoadFwdRef($operations, 'plageop_id');
    CMbObject::massLoadFwdRef($operations, 'chir_id');
    CMbObject::massLoadFwdRef($operations, 'sejour_id');

    return $operations;
  }

  /**
   * Load the CSejour to send from the linked CService objects
   *
   * @param CService[] $linked_objects The linked CService objects
   *
   * @return CSejour[]
   * @throws \Exception
   */
  protected static function loadSejoursFromLinkedObjects($linked_objects) {
    $services = array();

    foreach ($linked_objects as $_linked_object) {
      $services[] = $_linked_object->object_id;
    }

    $sejour = new CSejour();

    $ljoin = array(
      'affectation' => 'affectation.sejour_id = sejour.sejour_id'
    );

    $date = CMbDT::date();
    $where = array(
      'affectation.entree' => " <= '$date 23:59:59'",
      'affectation.sortie' => " >= '$date 00:00:00'",
      'affectation.service_id' => CSQLDataSource::prepareIn($services),
      'affectation.effectue' =>  " = '0'",
      'sejour.type' => " != 'exte'",
      'sejour.annule' => " = '0'"
    );

    $sejours = $sejour->loadList($where, null, null, array('sejour.sejour_id'), $ljoin);

    $ljoin = array(
      'affectation' => 'sejour.sejour_id = affectation.sejour_id'
    );

    $where = array(
      'sejour.group_id' => " = '" . CGroups::loadCurrent()->_id . "'",
      'sejour.entree_prevue' => " <= '$date 23:59:59'",
      'sejour.sortie_prevue' => " >= '$date 00:00:00'",
      'sejour.type' => " != 'exte'",
      'sejour.annule' => " = '0'",
      'affectation.affectation_id' => ' IS NULL'
    );

    $sejours = array_merge($sejours, $sejour->loadList($where, null, null, array('sejour.sejour_id'), $ljoin));

    CMbObject::massLoadFwdRef($sejours, 'patient_id');
    CMbObject::massLoadFwdRef($sejours, 'praticien_id');

    return $sejours;
  }

  /**
   * Prepare the data to send from the given object
   *
   * @param CMbObject    $object           The object
   * @param string       $encoding         The encoding
   * @param string       $modality         The target modality
   * @param string       $calling_ae_title The AE title who requested the worklist
   * @param CDicomConfig $dicom_config     The Exchange Dicom
   *
   * @return array
   */
  protected static function getDataFromObject($object, $encoding, $modality, $calling_ae_title, $dicom_config) {
    switch ($object->_class) {
      case 'COperation':
        $data = self::getDataFromOperation($object, $encoding, $modality, $calling_ae_title, $dicom_config);
        break;
      case 'CSejour':
        $data = self::getDataFromSejour($object, $encoding, $modality, $calling_ae_title, $dicom_config);
        break;
      default:
        $data = array();
    }

    return $data;
  }

  /**
   * Prepare the data to send from the given operation
   *
   * @param COperation   $operation        The operation
   * @param string       $encoding         The encoding
   * @param string       $modality         The target modality
   * @param string       $calling_ae_title The AE title who requested the worklist
   * @param CDicomConfig $dicom_config     The Exchange Dicom
   *
   * @return array
   */
  protected static function getDataFromOperation($operation, $encoding, $modality, $calling_ae_title, $dicom_config) {
    $patient = $operation->loadRefPatient();
    $operation->updateFormFields();
    $operation->loadRefPlageOp();
    $sejour = $operation->loadRefSejour();
    $chir   = $operation->loadRefChir();
    $patient->loadIPP();
    $sejour->loadNDA();

    $libelle = "";
    if ($operation->libelle) {
      $libelle = substr($operation->libelle, 0, 64);
    }
    else {
      $libelle = "Pas de libellé";
    }

    $date = "";
    if ($operation->date) {
      $date = str_replace("-", "", $operation->date);
    }
    else {
      $date = str_replace("-", "", $operation->_ref_plageop->date);
    }

    $time = "";
    if ($operation->time_operation) {
      $time = str_replace(":", "", $operation->time_operation);
      $time .= ".000000";
    }
    else {
      $time = str_replace(':', '', CMbDT::time()) . '.000000';
    }

    $sejour_id = $sejour->_id;
    if ($sejour->_NDA) {
      $sejour_id = $sejour->_NDA;
    }

    $sejour_uid = self::generateSejourUID($chir->_id, $sejour_id);

    $patient_id = $patient->_id;
    if ($patient->_IPP) {
      $patient_id = $patient->_IPP;
    }

    $age = intval(substr(trim($patient->_age), 0, 2));

    $separator = ($dicom_config && $dicom_config->physician_separator) ? $dicom_config->physician_separator : ' ';
    $chir_name = "$chir->_user_last_name$separator$chir->_user_first_name";

    $data = array(
      0x0008 => array (
        0x0020 => self::encodeValue($date, $encoding),
        0x0050 => self::encodeValue($sejour_id, $encoding)
      ),
      0x0010 => array(
        0x0010 => self::encodeValue("$patient->nom^$patient->prenom", $encoding),
        0x0020 => self::encodeValue("$patient_id", $encoding),
        0x0030 => self::encodeValue(str_replace("-", "", $patient->naissance), $encoding),
        0x0040 => self::encodeValue(strtoupper($patient->sexe), $encoding)
      ),
      0x0020 => array(),
      0x0038 => array (
        0x0010 => self::encodeValue($sejour_id, $encoding)
      ),
      0x0040 => array(
        0x0100 => array(
          array(
            array("group_number" => 0x0008, "element_number" => 0x0060, "value" => self::encodeValue($modality, $encoding)),
            array("group_number" => 0x0040, "element_number" => 0x0001, "value" => self::encodeValue($calling_ae_title, $encoding)),
            array("group_number" => 0x0040, "element_number" => 0x0002, "value" => self::encodeValue($date, $encoding)),
            array("group_number" => 0x0040, "element_number" => 0x0003, "value" => self::encodeValue($time, $encoding)),
            array("group_number" => 0x0040, "element_number" => 0x0006, "value" => self::encodeValue($chir_name, $encoding)),
            array("group_number" => 0x0040, "element_number" => 0x0007, "value" => self::encodeValue($libelle, $encoding)),
            array("group_number" => 0x0040, "element_number" => 0x0009, "value" => self::encodeValue($sejour_id, $encoding)),
          ),
        ),
        0x1001 => self::encodeValue($sejour_id, $encoding)
      ),
    );

    if ($dicom_config->uid_0020_000d) {
      $data[0x0020] = array(
        0x0010 => self::encodeValue($sejour_id, $encoding),
        0x000D => self::encodeValue($sejour_uid, $encoding)
      );
    }
    else {
      $data[0x0020] = array(
        0x000D => self::encodeValue($sejour_id, $encoding)
      );
    }

    /* We had the field 0x0032,0x1032 if it's configured */
    if ($dicom_config->send_0032_1032) {
      if (!array_key_exists(0x0032, $data)) {
        $data[0x0032] = array();
      }

      $data[0x0032][0x1032] = self::encodeValue($chir_name, $encoding);

      /* Add the field into the sequence of item 0x0040,0x0100 */
      /*$data[0x0040][0x0100][0][] = array(
        "group_number" => 0x0032,
        "element_number" => 0x1032, "value" => self::encodeValue($chir_name, $encoding)
      );*/
    }

    return $data;
  }

  /**
   * Prepare the data to send from the given sejour
   *
   * @param CSejour      $sejour           The sejour
   * @param string       $encoding         The encoding
   * @param string       $modality         The target modality
   * @param string       $calling_ae_title The AE title who requested the worklist
   * @param CDicomConfig $dicom_config     The Exchange Dicom
   *
   * @return array
   */
  protected static function getDataFromSejour($sejour, $encoding, $modality, $calling_ae_title, $dicom_config) {
    $libelle = '';
    $date = '';
    $time = '';

    $patient = $sejour->loadRefPatient();
    $sejour->updateFormFields();
    $operation = $sejour->loadRefCurrOperation(CMbDT::dateTime());
    if ($operation->_id) {
      $operation->updateFormFields();
      $operation->loadRefPlageOp();

      if ($operation->libelle) {
        $libelle = substr($operation->libelle, 0, 64);
      }
      else {
        $libelle = 'Pas de libellé';
      }

      if ($operation->date) {
        $date = str_replace('-', '', $operation->date);
      }
      else {
        $date = str_replace('-', '', $operation->_ref_plageop->date);
      }

      if ($operation->time_operation) {
        $time = str_replace(':', '', $operation->time_operation) . '.000000';
      }
      else {
        $time = str_replace(':', '', CMbDT::time()) . '.000000';
      }
    }
    else {
      $libelle = "Pas de libellé";
      $date = str_replace('-', '', CMbDT::date());
      $time = str_replace(':', '', CMbDT::time()) . '.000000';
    }

    $chir = $sejour->loadRefPraticien();
    $patient->loadIPP();
    $sejour->loadNDA();

    $sejour_id = $sejour->_id;
    if ($sejour->_NDA) {
      $sejour_id = $sejour->_NDA;
    }

    $sejour_uid = self::generateSejourUID($chir->_id, $sejour_id);

    $patient_id = $patient->_id;
    if ($patient->_IPP) {
      $patient_id = $patient->_IPP;
    }

    $age = intval(substr(trim($patient->_age), 0, 2));

    $separator = ($dicom_config && $dicom_config->physician_separator) ? $dicom_config->physician_separator : ' ';
    $chir_name = "$chir->_user_last_name$separator$chir->_user_first_name";

    $data = array(
      0x0008 => array (
        0x0020 => self::encodeValue($date, $encoding),
        0x0050 => self::encodeValue($sejour_id, $encoding)
      ),
      0x0010 => array(
        0x0010 => self::encodeValue("$patient->nom^$patient->prenom", $encoding),
        0x0020 => self::encodeValue("$patient_id", $encoding),
        0x0030 => self::encodeValue(str_replace("-", "", $patient->naissance), $encoding),
        0x0040 => self::encodeValue(strtoupper($patient->sexe), $encoding)
      ),
      0x0020 => array(),
      0x0038 => array (
        0x0010 => self::encodeValue($sejour_id, $encoding)
      ),
      0x0040 => array(
        0x0100 => array(
          array(
            array("group_number" => 0x0008, "element_number" => 0x0060, "value" => self::encodeValue($modality, $encoding)),
            array("group_number" => 0x0040, "element_number" => 0x0001, "value" => self::encodeValue($calling_ae_title, $encoding)),
            array("group_number" => 0x0040, "element_number" => 0x0002, "value" => self::encodeValue($date, $encoding)),
            array("group_number" => 0x0040, "element_number" => 0x0003, "value" => self::encodeValue($time, $encoding)),
            array("group_number" => 0x0040, "element_number" => 0x0006, "value" => self::encodeValue($chir_name, $encoding)),
            array("group_number" => 0x0040, "element_number" => 0x0007, "value" => self::encodeValue($libelle, $encoding)),
            array("group_number" => 0x0040, "element_number" => 0x0009, "value" => self::encodeValue($sejour_id, $encoding)),
          ),
        ),
        0x1001 => self::encodeValue($sejour_id, $encoding)
      ),
    );

    /* Set the values of the group 0020 according to the configuration */
    if ($dicom_config->uid_0020_000d) {
      $data[0x0020] = array(
        0x000D => self::encodeValue($sejour_uid, $encoding),
        0x0010 => self::encodeValue($sejour_id, $encoding)
      );
    }
    else {
      $data[0x0020] = array(
        0x000D => self::encodeValue($sejour_id, $encoding)
      );
    }

    /* We add the field 0x0032,0x1032 if it's configured */
    if ($dicom_config->send_0032_1032) {
      if (!array_key_exists(0x0032, $data)) {
        $data[0x0032] = array();
      }

      $data[0x0032][0x1032] = self::encodeValue($chir_name, $encoding);

      /* Add the field into the sequence of item 0x0040,0x0100 */
      /*$data[0x0040][0x0100][0][] = array(
        "group_number" => 0x0032,
        "element_number" => 0x1032,
        "value" => self::encodeValue($chir_name, $encoding)
      );*/
    }

    return $data;
  }

  /**
   * Return the value of the ScheduledStationAETitle (dataset 0x0040,0x0001)
   *
   * @param array $requested_datas The the data of the CFind request
   *
   * @return string
   */
  protected static function getRequestedAETitle($requested_datas) {
    $ae_title = '';

    /* We check if the dataset is in the data */
    if (array_key_exists(0x0040, $requested_datas) && array_key_exists(0x0001, $requested_datas[0x0040])) {
      $ae_title = $requested_datas[0x0040][0x0001]->getValue();
    }
    /* Check if the dataset is in the sequence of the dataset 0x0040,0x0100 */
    elseif (array_key_exists(0x0040, $requested_datas) && array_key_exists(0x0100, $requested_datas[0x0040])
        && $dataset = $requested_datas[0x0040][0x0100]->getSequenceDataSet(0x0040, 0x0001)
    ) {
      $ae_title = $dataset->getValue();
    }

    return $ae_title;
  }

  /**
   * Return the value of the Modality (dataset 0x0008,0x0060)
   *
   * @param array          $requested_datas The the data of the CFind request
   * @param CExchangeDicom $dicom_exchange  The Exchange Dicom
   *
   * @return string
   */
  protected static function getRequestedModality($requested_datas, $dicom_exchange) {
    $modality = '';

    $config = $dicom_exchange->getConfigs();
    /* Check if a value is configured for the modality */
    if (isset($config->value_0008_0060)) {
      $modality = $config->value_0008_0060;
    }
    elseif (array_key_exists(0x0008, $requested_datas) && array_key_exists(0x0060, $requested_datas[0x0008])) {
      /* We check if the dataset is in the data */
      $modality = $requested_datas[0x0008][0x0060]->getValue();
    }
    /* Check if the dataset is in the sequence of the dataset 0x0040,0x0100 */
    elseif (array_key_exists(0x0040, $requested_datas) && array_key_exists(0x0100, $requested_datas[0x0040])
        && $dataset = $requested_datas[0x0040][0x0100]->getSequenceDataSet(0x0008, 0x0060)
    ) {
      $modality = $dataset->getValue();
    }

    return $modality;
  }

  /**
   * Generate an UID from the sejour_id (or NDA) and user_id
   *
   * @param integer $user_id   The id of the user
   * @param string  $sejour_id The sejour's id or NDA
   *
   * @return string The UID of the sejour
   */
  public static function generateSejourUID($user_id, $sejour_id) {
    $org_root = '1.2.250.17425';
    $group = CGroups::loadCurrent();

    return "{$org_root}.{$group->_id}.{$user_id}.{$sejour_id}";
  }

  /**
   * Encode the value if a specific encoding is needed
   *
   * @param string $value    The value to encode
   * @param string $encoding The encoding
   *
   * @return string The encoded value
   */
  protected static function encodeValue($value, $encoding) {
    if ($encoding == "ISO_IR 192") {
      $value = utf8_encode($value);
    }

    return $value;
  }
}
