<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Dicom;

use DateTime;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbDT;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;
use Ox\Interop\Dicom\Data\CDicomDictionary;
use Ox\Interop\Dicom\Data\CDicomPresentationContext;
use Ox\Interop\Dicom\Network\Pdu\CDicomPDU;
use Ox\Interop\Dicom\Network\Pdu\CDicomPDUAAssociateRQ;
use Ox\Interop\Dicom\Network\Pdu\CDicomPDUFactory;
use Ox\Interop\Eai\CEAIDispatcher;
use Ox\Interop\Eai\CInteropActor;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * The Dicom session
 * 
 * @todo Modifier la facon dont le presentation context est stocké : faire un mix entre le reply et le request
 */
class CDicomSession extends CMbObject {
  
  const STA1 = "Sta1";
  
  const STA2 = "Sta2";
  
  const STA3 = "Sta3";
  
  const STA4 = "Sta4";
  
  const STA5 = "Sta5";
  
  const STA6 = "Sta6";
  
  const STA7 = "Sta7";
  
  const STA8 = "Sta8";
  
  const STA9 = "Sta9";
  
  const STA10 = "Sta10";
  
  const STA11 = "Sta11";
  
  const STA12 = "Sta12";
  
  const STA13 = "Sta13";
  
  /**
   * The id of the session
   * 
   * @var integer
   */
  public $dicom_session_id = null;
  
  /**
   * The address of the actor who receive the association request.
   * If null, the receiver is Mediboard.
   * 
   * @var string
   */
  public $receiver = null;
  
  /**
   * The address of the actor who initiate the session.
   * If null, the sender is Mediboard.
   * 
   * @var string
   */
  public $sender = null;
  
  /**
   * The begin datetime of the session
   * 
   * @var datetime
   */
  public $begin_date = null;
  
  /**
   * The end datetime of the session
   * 
   * @var datetime
   */
  public $end_date = null;
  
  /**
   * The messages send and received during the session.
   * The structure is : 'type/message|type/message|...'
   * @var string
   */
  public $messages = null;
  
  /**
   * The status of the session
   * 
   * @var string
   */
  public $status = null;
  
  /**
   * The id of the Dicom exchange, containing only the data pdus
   * 
   * @var integer
   */
  public $dicom_exchange_id = null;
  
  /**
   * The sender id
   * 
   * @var integer
   */
  public $sender_id = null;
  
  /**
   * The receiver id
   * 
   * @var integer
   */
  public $receiver_id = null;
  
  /**
   * The group
   * 
   * @var integer
   */
  public $group_id = null;
  
  /**
   * The current state of the DICOM UL State machine
   * 
   * @var string
   */
  public $state = null;
  
  /**
   * The duration of the session, in milliseconds
   * 
   * @var integer
   */
  public $_duration = null;
  
  /**
   * The messages
   * The key is the type, and the value is the message.
   * 
   * @var array
   */
  public $_messages = null;
  
  /**
   * The presentation contexts, in string
   * 
   * @var string
   */
  public $presentation_contexts = null;
  
  /**
   * The ARTIM timer
   * 
   * @var
   */
  protected $_artim_timer = null;
  
  /**
   * The presentation contexts
   * 
   * @var array
   */
  protected $_presentation_contexts = null;
  
  /**
   * The last PDU received
   * 
   * @var CDicomPDU
   */
  protected $_last_PDU_received = null;
  
  /**
   * The Dicom exchange, containing only the data pdus
   * 
   * @var CExchangeDicom
   */
  public $_ref_dicom_exchange = null;
  
  /**
   * The actor, the sender or the receiver
   * 
   * @var CInteropActor
   */
  public $_ref_actor = null;
  
  /**
   * Used for the filters
   * 
   * @var dateTime
   */
  public $_date_min = null;
  
  /**
   * Used for the filters
   * 
   * @var dateTime
   */
  public $_date_max = null;

  /**
   * @var CGroups
   */
  public $_ref_group;
  
  /**
   * The DICOM UL state machine
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   * 
   * @var array
   */
  protected static $_state_machine = array(
    "Sta1" => array(
      "AAssociateRQ_Prepared" => "AE1",
      "TCP_Open"              => "AE5",
      "TCP_Closed"            => "AA5",
    ),
    "Sta2" => array(
      "AAssociateAC_Received" => "AA1",
      "AAssociateRJ_Received" => "AA1",
      "AAssociateRQ_Received" => "AE6",
      "PDataTF_Received"      => "AA1",
      "AReleaseRQ_Received"   => "AA1",
      "AReleaseRP_Received"   => "AA1",
      "AAbort_Received"       => "AA2",
      "TCP_Closed"            => "AA5",
      "ARTIMTimeOut"          => "AA2",
      "InvalidPDU"            => "AA1",
    ),
    "Sta3" => array(
      "AAssociateAC_Received" => "AA8",
      "AAssociateRJ_Received" => "AA8",
      "AAssociateRQ_Received" => "AA8",
      "AAssociateAC_Prepared" => "AE7",
      "AAssociateRJ_Prepared" => "AE8",
      "PDataTF_Received"      => "AA8",
      "AReleaseRQ_Received"   => "AA8",
      "AReleaseRP_Received"   => "AA8",
      "AAbort_Prepared"       => "AA1",
      "AAbort_Received"       => "AA3",
      "TCP_Closed"            => "AA4",
      "InvalidPDU"            => "AA8",
    ),
    "Sta4" => array(
      "TCP_Indication"        => "AE2",
      "AAbort_Prepared"       => "AA2",
      "TCP_Closed"            => "AA4",
    ),
    "Sta5" => array(
      "AAssociateAC_Received" => "AE3",
      "AAssociateRJ_Received" => "AE4",
      "AAssociateRQ_Received" => "AA8",
      "PDataTF_Received"      => "AA8",
      "AReleaseRQ_Received"   => "AA8",
      "AReleaseRP_Received"   => "AA8",
      "AAbort_Prepared"       => "AA1",
      "AAbort_Received"       => "AA3",
      "TCP_Closed"            => "AA5",
      "InvalidPDU"            => "AA8",
    ),
    "Sta6" => array(
      "AAssociateAC_Received" => "AA8",
      "AAssociateRJ_Received" => "AA8",
      "AAssociateRQ_Received" => "AA8",
      "PDataTF_Prepared"      => "DT1",
      "PDataTF_Received"      => "DT2",
      "AReleaseRQ_Prepared"   => "AR1",
      "AReleaseRQ_Received"   => "AR2",
      "AReleaseRP_Received"   => "AA8",
      "AAbort_Prepared"       => "AA1",
      "AAbort_Received"       => "AA3",
      "TCP_Closed"            => "AA5",
      "InvalidPDU"            => "AA8",
    ),
    "Sta7" => array(
      "AAssociateAC_Received" => "AA8",
      "AAssociateRJ_Received" => "AA8",
      "AAssociateRQ_Received" => "AA8",
      "PDataTF_Received"      => "AR6",
      "AReleaseRQ_Received"   => "AR8",
      "AReleaseRP_Received"   => "AR3",
      "AAbort_Prepared"       => "AA1",
      "AAbort_Received"       => "AA3",
      "TCP_Closed"            => "AA4",
      "InvalidPDU"            => "AA8",
    ),
    "Sta8" => array(
      "AAssociateAC_Received" => "AA8",
      "AAssociateRJ_Received" => "AA8",
      "AAssociateRQ_Received" => "AA8",
      "PDataTF_Prepared"      => "AR7",
      "PDataTF_Received"      => "AA8",
      "AReleaseRQ_Received"   => "AA8",
      "AReleaseRP_Received"   => "AA8",
      "AReleaseRP_Prepared"   => "AR4",
      "AAbort_Prepared"       => "AA1",
      "AAbort_Received"       => "AA3",
      "TCP_Closed"            => "AA4",
      "InvalidPDU"            => "AA8",
    ),
    "Sta9" => array(
      "AAssociateAC_Received" => "AA8",
      "AAssociateRJ_Received" => "AA8",
      "AAssociateRQ_Received" => "AA8",
      "PDataTF_Received"      => "AA8",
      "AReleaseRQ_Received"   => "AA8",
      "AReleaseRP_Received"   => "AA8",
      "AReleaseRP_Prepared"   => "AR9",
      "AAbort_Prepared"       => "AA1",
      "AAbort_Received"       => "AA3",
      "TCP_Closed"            => "AA4",
      "InvalidPDU"            => "AA8",
    ),
    "Sta10" => array(
      "AAssociateAC_Received" => "AA8",
      "AAssociateRJ_Received" => "AA8",
      "AAssociateRQ_Received" => "AA8",
      "PDataTF_Received"      => "AA8",
      "AReleaseRQ_Received"   => "AA8",
      "AReleaseRP_Received"   => "AR10",
      "AAbort_Prepared"       => "AA1",
      "AAbort_Received"       => "AA3",
      "TCP_Closed"            => "AA4",
      "InvalidPDU"            => "AA8",
    ),
    "Sta11" => array(
      "AAssociateAC_Received" => "AA8",
      "AAssociateRJ_Received" => "AA8",
      "AAssociateRQ_Received" => "AA8",
      "PDataTF_Received"      => "AA8",
      "AReleaseRQ_Received"   => "AA8",
      "AReleaseRP_Received"   => "AR3",
      "AAbort_Prepared"       => "AA1",
      "AAbort_Received"       => "AA3",
      "TCP_Closed"            => "AA4",
      "InvalidPDU"            => "AA8",
    ),
    "Sta12" => array(
      "AAssociateAC_Received" => "AA8",
      "AAssociateRJ_Received" => "AA8",
      "AAssociateRQ_Received" => "AA8",
      "PDataTF_Received"      => "AA8",
      "AReleaseRQ_Received"   => "AA8",
      "AReleaseRP_Received"   => "AA8",
      "AReleaseRP_Prepared"   => "AR4",
      "AAbort_Prepared"       => "AA1",
      "AAbort_Received"       => "AA3",
      "TCP_Closed"            => "AA4",
      "InvalidPDU"            => "AA8",
    ),
    "Sta13" => array(
      "AAssociateAC_Received" => "AA6",
      "AAssociateRJ_Received" => "AA6",
      "AAssociateRQ_Received" => "AA7",
      "PDataTF_Received"      => "AA6",
      "AReleaseRQ_Received"   => "AA6",
      "AReleaseRP_Received"   => "AA6",
      "AAbort_Received"       => "AA2",
      "TCP_Closed"            => "AR5",
      "ARTIM_TimeOut"         => "AA2",
      "InvalidPDU"            => "AA7",
    ),
  );

  /**
   * The constructor
   *
   * @param CInteropActor $actor The actor
   *
   * @return void
   * @throws \Exception
   */
  function __construct($actor = null) {
    parent::__construct();
    if ($actor) {
      $this->setActor($actor);
    }
    $this->group_id = CGroups::loadCurrent()->_id;
    $this->state = self::STA1;
    $this->begin_date = CMbDT::dateTime();
    $this->messages = "";
  }
  
  /**
   * @inheritDoc
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table  = "dicom_session";
    $spec->key    = "dicom_session_id";
    
    return $spec; 
  }
  
  /**
   * Get the properties of our class as string
   * 
   * @return array
   */
  function getProps() {
    $props = parent::getProps();
    $props["receiver"]              = "str notNull";
    $props["sender"]                = "str notNull";
    $props["begin_date"]            = "dateTime notNull";
    $props["end_date"]              = "dateTime";
    $props["messages"]              = "text";
    $props["status"]                = "enum list|null|Rejected|Completed|Aborted";
    $props["dicom_exchange_id"]     = "ref class|CExchangeDicom back|dicom_session";
    $props["group_id"]              = "ref notNull class|CGroups autocomplete|text back|dicom_session";
    $props["sender_id"]             = "ref class|CDicomSender autocomplete|nom back|session_dicom";
    //$props["receiver_id"]           = "ref class|CDicomReceiver autocomplete|nom";
    $props["state"]                 = "str notNull show|0";
    $props["presentation_contexts"] = "str show|0";
    
    $props["_duration"]             = "num";
    $props["_date_min"]             = "dateTime";
    $props["_date_max"]             = "dateTime";
    return $props;
  }

  /**
   * Load the dicom exchange
   *
   * @param bool $cache True if the cache is used, false if not
   *
   * @return CExchangeDicom
   * @throws \Exception
   */
  function loadRefDicomExchange($cache = true) {
    if ($this->dicom_exchange_id !== null && $this->_ref_dicom_exchange === null) {
      $this->_ref_dicom_exchange = $this->loadFwdRef("dicom_exchange_id", $cache);
    }

    return $this->_ref_dicom_exchange;
  }
  
  /**
   * Load the group
   * 
   * @return null
   * @throws \Exception
   */
  function loadRefGroups() {
    $this->_ref_group = new CGroups;
    $this->_ref_group->load($this->group_id);
  }
  
  /**
   * Load the actor
   * 
   * @return void
   * @throws \Exception
   */ 
  function loadRefActor() {
    if ($this->sender_id) {
      $this->_ref_actor = new CDicomSender;
      $this->_ref_actor->load($this->sender_id);
    }
  }
  
  /**
   * Set the actor
   * 
   * @param CInteropActor $actor The actor
   * 
   * @return null
   * @throws \Exception
   */
  function setActor(CInteropActor $actor) {
    $source = $actor->getFirstExchangesSources();
    $actor_class = CClassMap::getSN($actor);
    if ($actor_class == "CDicomSender" ) {
      $actor->loadRefsExchangesSources();
      $this->sender = $source->host . ":" . $source->port;
      $this->receiver = "[SELF]";
      $this->sender_id = $actor->_id;
    }

    $this->_ref_actor = $actor;
  }
  
  /**
   * Return the actor
   * 
   * @return CInteropActor
   */
  function getActor() {
    if (!$this->_ref_actor) {
      $this->loadRefActor();
    }
    return $this->_ref_actor;
  }
  
  /**
   * Add a message to the field messages
   * 
   * @param string    $type    The type of the message
   * 
   * @param CDicomPDU $message The message
   * 
   * @return null
   */
  function addMessage($type, $message) {
    if (!$this->_messages) {
      $this->_messages = array($type => $message);
    }
    else {
      $this->_messages[$type] = $message; 
    }
  }
  
  /**
   * Update the form fields
   * 
   * @return void
   */
  function updateFormFields() {
    parent::updateFormFields();
    
    if ($this->presentation_contexts) {
      $pres_contexts_array = explode('|', $this->presentation_contexts);
      $this->_presentation_contexts = array();
      
      foreach ($pres_contexts_array as $_pres_context) {
        $_pres_context = explode('/', $_pres_context);

        if (array_key_exists(0, $_pres_context) && array_key_exists(1, $_pres_context) && array_key_exists(2, $_pres_context)) {
          $this->_presentation_contexts[] = new CDicomPresentationContext($_pres_context[0], $_pres_context[1], $_pres_context[2]);
        }
      }
    }
    
    if ($this->end_date && $this->begin_date) {
      $this->_duration = $this->end_date - $this->begin_date;
    }
  }
  
  /**
   * Update the plin fields
   * 
   * @return null
   */
  function updatePlainFields() {
    parent::updatePlainFields();
    
    if ($this->_presentation_contexts && !$this->presentation_contexts) {
      foreach ($this->_presentation_contexts as $_pres_context) {
        if (!$this->presentation_contexts) {
          $this->presentation_contexts = "$_pres_context->id/$_pres_context->abstract_syntax/$_pres_context->transfer_syntax";
        }
        else {
          $this->presentation_contexts .= "|$_pres_context->id/$_pres_context->abstract_syntax/$_pres_context->transfer_syntax";
        }
      }
    }
    
    if ($this->_messages) {
      foreach ($this->_messages as $type => $msg) {
        if (!$this->messages) {
          $this->messages = "$type\\" . base64_encode($msg->getPacket());
        }
        else {
          $this->messages .= "|$type\\" . base64_encode($msg->getPacket());
        }
      }
    }
  }
  
  /**
   * Decode the messages
   * 
   * @return null
   */
  function loadMessages() {
    $this->loadRefDicomExchange();
    if ($this->_ref_dicom_exchange) {
      $this->_ref_dicom_exchange->decodeContent();
    }  
      
    $this->_messages = array();
    
    $msg_array = explode('|', $this->messages);
    
    foreach ($msg_array as $msg) {
      $msg = explode('\\', $msg);
      $pdu = CDicomPDUFactory::decodePDU(base64_decode($msg[1]));
      $this->_messages[$msg[0]] = $pdu;
      
      if ($msg[0] == "A-Associate-AC" && $this->_ref_dicom_exchange) {
        $i = 1;  
        foreach ($this->_ref_dicom_exchange->_requests as $_request) {
          $name = "$_request->type_str-RQ$i";  
          $this->_messages[$name] = $_request;
          $i++;
        }
        
        $i = 1;  
        foreach ($this->_ref_dicom_exchange->_responses as $_response) {
          $name = "$_response->type_str-RSP$i";  
          $this->_messages[$name] = $_response;
          $i++;
        }
      }
    }
  }
  
  /**
   * Return the action corresponing to the current state and to the event
   * 
   * @param string $event The event
   * 
   * @return string
   */
  protected function getAction($event) {
    return self::$_state_machine[$this->state][$event];
  }
  
  /**
   * Return the presentation context linked to the presentation context
   * 
   * @return string or null
   */
  function getTransferSyntaxByID() {
    foreach ($this->presentation_contexts as $_pres_context) {
      if ($_pres_context->id = $this->pres_context_id) {
        return $_pres_context->transfer_syntax->name;
      }
    }
    return null;
  }
  
  /**
   * Set the presentation contexts, from the A-Associate-RQ and the A-Associate-AC pdus
   * 
   * @return null
   */
  function setPresentationContexts() {
    if (array_key_exists("A-Associate-RQ", $this->_messages) && array_key_exists("A-Associate-AC", $this->_messages)) {
      $_rq_pres_contexts = $this->_messages["A-Associate-RQ"]->presentation_contexts;
      $_ac_pres_contexts = $this->_messages["A-Associate-AC"]->presentation_contexts;
      
      $this->_presentation_contexts = array();
      foreach ($_rq_pres_contexts as $rq_pres_context) {
        foreach ($_ac_pres_contexts as $ac_pres_context) {
          if ($rq_pres_context->id == $ac_pres_context->id) {
            $_pres_context = new CDicomPresentationContext($rq_pres_context->id, $rq_pres_context->abstract_syntax->name);
            $_pres_context->transfer_syntax = $ac_pres_context->transfer_syntax->name;
            $this->_presentation_contexts[] = $_pres_context;
          }
        }
      }
    }
  }
  
  /**
   * Check the validity of the A-Associate-RQ PDU
   * 
   * @param CDicomPDUAAssociateRQ $pdu The A-Associate-RQ PDU
   * 
   * @return boolean
   */
  protected function isAAssociateRQValid(CDicomPDUAAssociateRQ $pdu) {
    if ($pdu->protocol_version != 0x001) {
      return 2;
    }
    
    if (strtolower($pdu->called_AE_title) != "mediboard") {
      return 7;
    }
    
    foreach ($pdu->presentation_contexts as $presentation_context) {
      $is_one_sop_class_supported = false;
      if (CDicomDictionary::isSOPClassSupported($presentation_context->abstract_syntax->name)) {
        $is_one_sop_class_supported = true;
      }
      
      $is_one_transfer_syntax_supported = false;
      foreach ($presentation_context->transfer_syntaxes as $transfer_syntax) {
        if (CDicomDictionary::isTransferSyntaxSupported($transfer_syntax->name)) {
          $is_one_transfer_syntax_supported = true;
        }
      }
      
      if (!$is_one_sop_class_supported || !$is_one_transfer_syntax_supported) {
        return 1;
      }
    }
    
    return true;
  }

  /**
   * Prepare the datas for creating a A-Associate-AC PDU
   *
   * @param CDicomPDUAAssociateRQ $associate_rq The A-Associate-RQ PDU
   *
   * @return array
   *
   * @todo handle the user info sub items
   */
  protected function prepareAAssociateACPDU(CDicomPDUAAssociateRQ $associate_rq) {
    $datas = array(
      "protocol_version"      => 1,
      "called_AEtitle"       => $associate_rq->called_AE_title,
      "calling_AEtitle"      => $associate_rq->calling_AE_title,
      "application_context"   => array("name" => $associate_rq->application_context->name),
      "presentation_contexts" => array(),
      "user_info"             => array(
        "sub_items" => array(
          "CDicomPDUItemMaximumLength" => array("maximum_length" => 32768),
          "CDicomPDUItemImplementationClassUID" => array("uid" =>CAppUI::conf("dicom implementation_sop_class")),
          "CDicomPDUItemImplementationVersionName" => array("version_name" => CAppUI::conf("dicom implementation_version")),
        ), 
      ),
    );
      
    foreach ($associate_rq->presentation_contexts as $presentation_context) {
      $reason = 0;
      if (!CDicomDictionary::isSOPClassSupported($presentation_context->abstract_syntax->name)) {
        $reason = 3;
      }

      $transfer_syntaxes = array();
      foreach ($presentation_context->transfer_syntaxes as $_transfer_syntax) {
        if (CDicomDictionary::isTransferSyntaxSupported($_transfer_syntax->name)) {
          $transfer_syntaxes[] = $_transfer_syntax->name;
        }
      }
      
      if (in_array("1.2.840.10008.1.2", $transfer_syntaxes)) {
        $transfer_syntax = "1.2.840.10008.1.2";
      }
      else {
        if (count($transfer_syntaxes) == 0) {
          $reason = 4;
        }
        $transfer_syntax = $transfer_syntaxes[0];
      }

      $datas["presentation_contexts"][] = array(
        "id" => $presentation_context->id,
        "reason" => $reason,
        "transfer_syntax" => array("name" => $transfer_syntax),
      );
    }
    return $datas;
  }

  /**
   * Prepare the datas for creating a A-Associate-RJ PDU
   * 
   * @param integer $reason The reason of the reject
   * 
   * @return array
   */
  protected function prepareAAssociateRJPDU($reason) {
    return array(
      "result" => 2,
      "source" => 1,
      "diagnostic" => $reason,
    );
  }
  
  /**
   * Handle an event
   * 
   * @param string $event The name of the event
   * 
   * @param string $datas The datas
   * 
   * @return string
   */
  function handleEvent($event, $datas = null) {
    $action = $this->getAction($event);
    $method = "do$action";
    if (method_exists($this, $method)) {
      return $this->$method($datas);
    }
    else {
      /** @todo Lever une exception **/
    }
  }
  
  /**
   * The action AE-1
   * 
   * Open a TCP connection with the server, only used in client mode
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAE1($datas) {
    // Open a TCP Connection with the server
    
    $this->begin_date = CMbDT::dateTime();
  }
  
  /**
   * The action AE-2
   * 
   * Send the prepared A-ASSOCIATE-RQ PDU to the server, only used in client mode
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAE2($datas) {
    
  }
  
  /**
   * The action AE-3
   * 
   * Decode a A-ASSOCIATE-AC PDU
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAE3($datas) {
    $associate_ac = CDicomPDUFactory::decodePDU($datas);
    
    $this->setPresentationContexts();
    $this->addMessage($associate_ac->type_str, $associate_ac);
    
    $this->state = self::STA6;
    
    /**
     * @todo envoyer les données
     */
  }
  
  /**
   * The action AE-4
   * 
   * Decode a A-ASSOCIATE-RJ PDU
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAE4($datas) {
    $associate_rj = CDicomPDUFactory::decodePDU($datas);
    
    $this->addMessage($associate_rj->type_str, $associate_rj);
    
    $this->state = self::STA1;
    
    $this->end_date = CMbDT::dateTime();
    $this->status = "Rejected";
  }
  
  /**
   * The action AE-5
   * 
   * Start ARTIM timer, wait for A-ASSOCIATE-RQ
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAE5($datas) {
    // start ARTIM timer
    $this->state = self::STA2;
    
    $this->begin_date = CMbDT::dateTime();
  }
  
  /**
   * The action AE-6
   * 
   * Stop ARTIM timer, and decode the A-ASSOCIATE-RQ PDU
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAE6($datas) {
    // stop ARTIM timer
    $associate_rq = CDicomPDUFactory::decodePDU($datas);
    $this->addMessage($associate_rq->type_str, $associate_rq);
    
    $valid = $this->isAAssociateRQValid($associate_rq);
    $this->state = self::STA3;
    
    if ($valid === true) {
      return $this->handleEvent("AAssociateAC_Prepared", $this->prepareAAssociateACPDU($associate_rq));
    }
    else {
      return $this->handleEvent("AAssociateRJ_Prepared", $this->prepareAAssociateRJPDU($valid));
    }
  }
  
  /**
   * The action AE-7
   * 
   * Send A-ASSOCIATE-AC PDU
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAE7($datas) {
    $pdu = CDicomPDUFactory::encodePDU("02", $datas);
    
    $this->addMessage($pdu->type_str, $pdu);

    $this->setPresentationContexts();
    $this->state = self::STA6;
    return $pdu->getPacket();
  }
  
  /**
   * The action AE-8
   * 
   * Send A-ASSOCIATE-RJ PDU
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAE8($datas) {
    $pdu = CDicomPDUFactory::encodePDU("03", $datas);
    
    $this->addMessage($pdu->type_str, $pdu);
    $this->status = "Rejected";
    
    $this->state = self::STA13;
    return $pdu->getPacket();
  }
  
  /**
   * The action DT-1
   * 
   * Send a P-DATA-TF PDU
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doDT1($datas) {
    $response = "";
    if (is_array($datas)) {
      foreach ($datas as $pdu) {
        $response .= $pdu->getPacket();
      }
    }
    else {
      $pdu = $datas;
      $response = $pdu->getPacket();
    }

    return $response;
  }
  
  /**
   * The action DT-2
   * 
   * Decode the P-DATA-TF PDU
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doDT2($datas) {
    $ack = CEAIDispatcher::dispatch(array("msg" => $datas, "pres_contexts" => $this->_presentation_contexts), $this->_ref_actor, $this->dicom_exchange_id);
    if (is_array($ack) && array_key_exists("exchange_id", $ack)) {
      if (!$this->dicom_exchange_id) {    
        $this->dicom_exchange_id = $ack["exchange_id"];
      }

      if (array_key_exists("event", $ack) && array_key_exists("datas", $ack)) {
        return $this->handleEvent($ack["event"], $ack["datas"]);
      }
    }
    return '';
  }
  
  /**
   * The action AR-1
   * 
   * Send a A-RELEASE-RQ PDU
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAR1($datas = null) {
    $pdu = CDicomPDUFactory::encodePDU("05", $datas);
    
    $this->addMessage($pdu->type_str, $pdu);
    
    $this->state = self::STA7;
    return $pdu->getPacket();
  }
  
  /**
   * The action AR-2
   * 
   * Decode the A-RELEASE-RQ PDU
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAR2($datas) {
    $release_rq = CDicomPDUFactory::decodePDU($datas);
    
    $this->_last_PDU_received = $release_rq;
    $this->addMessage($release_rq->type_str, $release_rq);
    
    $this->state = self::STA8;
    
    return $this->handleEvent("AReleaseRP_Prepared");
  }
  
  /**
   * The action AR-3
   * 
   * Decode the A-RELEASE-RP PDU
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAR3($datas) {
    $release_rp = CDicomPDUFactory::decodePDU($datas);
    
    $this->_last_PDU_received = $release_rp;
    $this->addMessage($release_rp->type_str, $release_rp);
    
    $this->state = self::STA1;
    
    $this->status = "Completed";
    
    $this->end_date = CMbDT::dateTime();
  }
  
  /**
   * The action AR-4
   * 
   * Send a A-RELEASE-RP PDU and start ARTIM timer
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAR4($datas = null) {
    $pdu = CDicomPDUFactory::encodePDU(0x06);
    
    $this->addMessage($pdu->type_str, $pdu);
    
    $this->state = self::STA13;
    /** @todo start ARTIM timer **/
    
    $this->status = "Completed";
    $this->end_date = CMbDT::dateTime();
    
    return $pdu->getPacket();
  }
  
  /**
   * The action AR-5
   * 
   * Stop ARTIM timer
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAR5($datas) {
    /** @todo stop ARTIM timer **/
    $this->state = self::STA1;
  }
  
  /**
   * The action AR-6
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAR6($datas) {
    
  }
  
  /**
   * The action AR-7
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAR7($datas) {
    
  }
  
  /**
   * The action AR-8
   * 
   * Handle the case of A-Release-RQ collision
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAR8($datas) {
    $release_rq = CDicomPDUFactory::decodePDU($datas);
    
    $this->_last_PDU_received = $release_rq;
    $this->addMessage($release_rq->type_str, $release_rq);
    
    if ($this->sender) {
      $this->state = self::STA9;
      return $this->handleEvent("AReleaseRP_Prepared");      
    }
    else {
      $this->state = self::STA9;
    }
  }
  
  /**
   * The action AR-9
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAR9($datas = null) {
    $pdu = CDicomPDUFactory::encodePDU("06");
    
    $this->addMessage($pdu->type_str, $pdu);
    
    $this->state = self::STA11;
    
    return $pdu->getPacket();
  }
  
  /**
   * The action AR-10
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAR10($datas) {
    $release_rp = CDicomPDUFactory::decodePDU($datas);
    
    $this->_last_PDU_received = $release_rp;
    $this->addMessage($release_rp->type_str, $release_rp);
    
    $this->state = self::STA12;
    return $this->handleEvent("AReleaseRP_Prepared");      
  }
  
  /**
   * The action AA-1
   * 
   * Send a A-ABORT PDU and start ARTIM timer
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAA1($datas) {
    $diagnostic = 0;
    if ($datas && is_integer($datas)) {
      $diagnostic = $datas;
    }
    $pdu = CDicomPDUFactory::encodePDU("07", array("source" => 0, "diagnostic" => $diagnostic));
    
    $this->addMessage($pdu->type_str, $pdu);
    $this->status ="Aborted";
    $this->state = self::STA13;
    
    return $pdu->getPacket();
  }
  
  /**
   * The action AA-2
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAA2($datas) {
    /**
     Stop ARTIM timer
     Close connection
     **/
    $this->state = self::STA1;
  }
  
  /**
   * The action AA-3
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAA3($datas) {
     $pdu = CDicomPDUFactory::decodePDU($datas);
    
    $this->addMessage($pdu->type_str, $pdu);
    
    /** close connection **/
    $this->state = self::STA1;
    $this->status ="Aborted";
    $this->end_date = CMbDT::dateTime();
  }
  
  /**
   * The action AA-4
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAA4($datas) {
    $pdu = CDicomPDUFactory::decodePDU($datas);
    
    $this->addMessage($pdu->type_str, $pdu);
    $this->status ="Aborted";
    $this->state = self::STA1;
  }
  
  /**
   * The action AA-5
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAA5($datas) {
    /** Stop ARTIM timer **/  
    $this->state = self::STA1;
  }
  
  /**
   * The action AA-6
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAA6($datas) {
    $this->state = self::STA13;
  }
  
  /**
   * The action AA-7
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAA7($datas) {
    $pdu = CDicomPDUFactory::encodePDU("07", array("source" => 2, "diagnostic" => 0));
    
    $this->addMessage($pdu->type_str, $pdu);
    
    $this->state = self::STA13;
    $this->status ="Aborted";
    return $pdu->getPacket();
  }
  
  /**
   * The action AA-8
   * 
   * @param mixed $datas The datas
   * 
   * @return string
   * 
   * @see DICOM Standard PS 3.8 Section 9.2
   */
  protected function doAA8($datas) {
    $diagnostic = 0;
    if ($datas && is_integer($datas)) {
      $diagnostic = $datas;
    }
    $pdu = CDicomPDUFactory::encodePDU("07", array("source" => 2, "diagnostic" => $diagnostic));
    
    $this->addMessage($pdu->type_str, $pdu);
    $this->status ="Aborted";
    
    $this->state = self::STA13;
    
    return $pdu->getPacket();
  }
}