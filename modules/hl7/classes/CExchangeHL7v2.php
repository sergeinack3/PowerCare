<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Interop\Eai\CDomain;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CExchangeTabular;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Hl7\Events\CHL7Event;

/**
 * Class CExchangeHL7v2
 * Exchange HL7v2
 */
class CExchangeHL7v2 extends CExchangeTabular
{
    /** @var array */
    public static $messages = [
        // IHE
        'PAM'     => 'CPAM',
        'PAM_FRA' => 'CPAMFRA',
        'DEC'     => 'CDEC',
        'SWF'     => 'CSWF',
        'PDQ'     => 'CPDQ',
        'PIX'     => 'CPIX',
        'SINR'    => 'CSINR',
        'LTW'     => 'CLTW',
        'ILW_FRA' => 'CILWFRA',
        'DRPT'    => 'CDRPT',

        // HL7
        'ADT'     => 'CHL7ADT',
        'MDM'     => 'CHL7MDM',
        'MFN'     => 'CHL7MFN',
        'ORU'     => 'CHL7ORU',
    ];

    // DB Table key

    /** @var string */
    public $exchange_hl7v2_id;

    /** @var string */
    public $code;

    /** @var int */
    public $altered_content_id;

    /** @var CHL7v2Message */
    public $_message_object;
    public $_exchange_hl7v2;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->loggable = false;
        $spec->table    = 'exchange_hl7v2';
        $spec->key      = 'exchange_hl7v2_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                            = parent::getProps();
        $props["sender_class"]            = "enum list|CSenderSFTP|CSenderFTP|CSenderSOAP|CSenderMLLP|CSenderFileSystem|CSenderHTTP show|0";
        $props["sender_id"]               .= " back|expediteur_hl7v2";
        $props["message_content_id"]      .= " back|messages_hl7v2";
        $props["altered_content_id"]      = "ref class|CContentTabular show|0 cascade back|messages_altered_hl7v2";
        $props["acquittement_content_id"] .= " back|acquittements_hl7v2";
        $props["receiver_id"]             = "ref class|CReceiverHL7v2 autocomplete|nom back|echanges";
        $props["object_class"]            = "enum list|CSejour|CPatient|COperation|CConsultation|CCompteRendu|CFile|CAffectation|CAppFineClientOrderItem|CEvenementMedical|CEvenementPatient|CPrescriptionLineElement|CStockMouvement show|0";
        $props["object_id"]               .= " back|exchanges_hl7v2";
        $props["group_id"]                .= " back|exchanges_hl7v2";
        $props["code"]                    = "str";

        $props["_message"]      = "er7";
        $props["_acquittement"] = "er7";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function handle()
    {
        $operator_hl7v2 = new COperatorHL7v2();

        return $operator_hl7v2->event($this);
    }

    /**
     * Check if data is understood
     *
     * @param string        $data  Data
     * @param CInteropActor $actor Actor
     *
     * @return bool
     * @throws CHL7v2Exception
     */
    public function understand(string $data, CInteropActor $actor = null): bool
    {
        if (!$this->isWellFormed($data, $actor)) {
            return false;
        }

        $hl7_message = $this->parseMessage($data, false, $actor);

        $hl7_message_evt = "CHL7Event$hl7_message->event_name";

        if ($hl7_message->i18n_code) {
            $hl7_message_evt = $hl7_message_evt . "_" . $hl7_message->i18n_code;
        }

        // Cas spécifique d'un acquittement reçu
        if ($hl7_message_evt === 'CHL7EventACK') {
            $this->_events_message_by_family[$hl7_message_evt][] = CHL7Event::getEventVersion(
                $hl7_message->version,
                "ACK"
            );

            return true;
        }

        foreach ($this->getFamily() as $_message) {
            $message_class = new $_message();
            $evenements    = $message_class->getEvenements();
            if (in_array($hl7_message_evt, $evenements)) {
                if (!$hl7_message->i18n_code) {
                    $this->_events_message_by_family[$_message][] = CHL7Event::getEventVersion(
                        $hl7_message->version,
                        $hl7_message->event_name,
                        $message_class,
                    );
                } else {
                    $this->_events_message_by_family[$_message][] = CHL7Event::getEventVersion(
                        $hl7_message->version,
                        $hl7_message->getI18NEventName(),
                        $message_class
                    );
                }
            }
        }

        if ($this->_events_message_by_family) {
            return true;
        }

        return false;
    }

    /**
     * Check if data is well formed
     *
     * @param string        $data  Data
     * @param CInteropActor $actor Actor
     *
     * @return bool|void
     */
    function isWellFormed($data, CInteropActor $actor = null)
    {
        try {
            $sender = ($actor ?: $this->loadRefSender());
            $strict = $sender ? $this->getConfigs($sender->_guid)->strict_segment_terminator : false;

            return CHL7v2Message::isWellFormed($data, $strict);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get HL7 config for one actor
     *
     * @param string $actor_guid Actor GUID
     *
     * @return CHL7Config|void
     * @throws Exception
     */
    function getConfigs($actor_guid)
    {
        [$sender_class, $sender_id] = explode("-", $actor_guid);

        $sender_hl7_config               = new CHL7Config();
        $sender_hl7_config->sender_class = $sender_class;
        $sender_hl7_config->sender_id    = $sender_id;
        $sender_hl7_config->loadMatchingObject();

        return $this->_configs_format = $sender_hl7_config;
    }

    /**
     * Parse HL7 message
     *
     * @param string $string     Data
     * @param bool   $parse_body Parse only header ?
     * @param null   $actor      Actor
     *
     * @return CHL7v2Message
     * @throws CHL7v2Exception
     */
    function parseMessage($string, $parse_body = true, $actor = null)
    {
        if (!$actor && isset($this->_ref_sender->_id)) {
            $actor = $this->_ref_sender;
        }
        if (!$actor && isset($this->_ref_receiver->_id)) {
            $actor = $this->_ref_receiver;
        }

        $hl7_message = new CHL7v2Message();

        if (!$this->_id && $actor) {
            $this->sender_id    = $actor->_id;
            $this->sender_class = $actor->_class;
        }

        if ($this->sender_id) {
            $this->loadRefSender();
            $this->getConfigs($this->_ref_sender->_guid);
            $hl7_message->strict_segment_terminator = ($this->_configs_format->strict_segment_terminator == 1);
        }

        $hl7_message->parse($string, $parse_body, $actor);

        return $hl7_message;
    }

    /**
     * @inheritdoc
     */
    function getFamily()
    {
        return self::$messages;
    }

    /**
     * Get exchange errors
     *
     * @return bool|void
     */
    function getErrors()
    {
    }

    /**
     * Get Message
     *
     * @return CHL7v2Message|null
     * @throws CHL7v2Exception
     */
    function getMessage()
    {
        if ($this->_message === null) {
            return null;
        }

        $hl7_message = $this->parseMessage($this->_message);

        $this->_doc_errors_msg   = !$hl7_message->isOK(CHL7v2Error::E_ERROR);
        $this->_doc_warnings_msg = !$hl7_message->isOK(CHL7v2Error::E_WARNING);

        $this->_message_object = $hl7_message;

        return $hl7_message;
    }

    /**
     * Get Message
     *
     * @return CHL7v2Message|null
     * @throws CHL7v2Exception
     */
    function getMessageInitial()
    {
        $content_tabular = $this->loadFwdRef("altered_content_id", true);

        return $this->parseMessage($content_tabular->content);
    }

    /**
     * Get HL7 acquittement
     *
     * @return CHL7v2Message|null
     * @throws CHL7v2Exception
     */
    function getACK()
    {
        if ($this->_acquittement === null) {
            return null;
        }

        $actor = null;
        if (isset($this->_ref_sender->_id)) {
            $actor = $this->_ref_sender;
        }
        if (isset($this->_ref_receiver->_id)) {
            $actor = $this->_ref_receiver;
        }

        $hl7_ack = new CHL7v2Message();
        if (!CHL7v2Message::isWellFormed($this->_acquittement)) {
            return $this->_acquittement;
        }

        $hl7_ack->parse($this->_acquittement, true, $actor);

        $this->_doc_errors_ack   = !$hl7_ack->isOK(CHL7v2Error::E_ERROR);
        $this->_doc_warnings_ack = !$hl7_ack->isOK(CHL7v2Error::E_WARNING);

        return $hl7_ack;
    }

    /**
     * Get message encoding
     *
     * @return string
     */
    function getEncoding()
    {
        return $this->_message_object->getEncoding();
    }

    /**
     * Populate exchange
     *
     * @param CExchangeDataFormat $data_format Data format
     * @param CHL7Event           $event       Event HL7
     *
     * @return string|void
     */
    public function populateExchange(CExchangeDataFormat $data_format, CHL7Event $event): void
    {
        $sender = $data_format->_ref_sender;
        $sender->loadRefsExchangesSources();

        $source = (!empty($sender->_ref_exchanges_sources)) ? reset(
            $sender->_ref_exchanges_sources
        ) : null;

        $this->group_id           = $data_format->group_id;
        $this->sender_id          = $data_format->sender_id;
        $this->sender_class       = $data_format->sender_class;
        $this->version            = $event->message->extension ?: $event->message->version;
        $this->nom_fichier        = isset($source->_receive_filename) ?? $source->_receive_filename;
        $this->type               = $event->profil;
        $this->sous_type          = $event->transaction;
        $this->code               = $event->code;
        $this->_message           = $data_format->_message;
        $this->altered_content_id = $event->altered_content_message_id;
        $this->send_datetime      = CMbDT::dateTime();
    }

    /**
     * Populate error exchange
     *
     * @param CHL7Acknowledgment $ack   Acknowledgment
     * @param CHL7Event          $event Event HL7
     *
     * @return string|void
     * @throws Exception
     */
    function populateErrorExchange(CHL7Acknowledgment $ack = null, CHL7Event $event = null)
    {
        if ($ack) {
            $ack->event_ack->msg_hl7;
            $this->_acquittement = $ack->event_ack->msg_hl7;
            /* @todo Comment gérer ces informations ? */
            $this->statut_acquittement = $ack->ack_code;
            $this->acquittement_valide = $ack->event_ack->message->isOK(CHL7v2Error::E_ERROR) ? 1 : 0;
        } else {
            $this->message_valide    = $event->message->isOK(CHL7v2Error::E_ERROR) ? 1 : 0;
            $this->date_production   = CMbDT::dateTime();
            $this->response_datetime = CMbDT::dateTime();
        }

        $this->store();
    }

    /**
     * Generate 'Commit Accept' acknowledgment
     *
     * @param CHL7Acknowledgment $ack            Acknowledgment
     * @param array              $mb_error_codes Mediboard errors codes
     * @param null               $comments       Comments
     * @param CMbObject          $mbObject       Object
     *
     * @return string
     * @throws Exception
     */
    function setAckCA(CHL7Acknowledgment $ack, $mb_error_codes = null, $comments = null, CMbObject $mbObject = null)
    {
        $ack->generateAcknowledgment("CA", $mb_error_codes, "0", "I", $comments, $mbObject);

        return $this->populateExchangeACK($ack, $mbObject);
    }

    /**
     * Populate ACK exchange
     *
     * @param CHL7Acknowledgment $ack      Acknowledgment
     * @param CMbObject          $mbObject Object
     *
     * @return string
     * @throws Exception
     */
    function populateExchangeACK(CHL7Acknowledgment $ack, $mbObject = null)
    {
        $msgAck = $ack->event_ack->msg_hl7;

        $this->statut_acquittement = $ack->ack_code;
        $this->acquittement_valide = $ack->event_ack->message->isOK(CHL7v2Error::E_ERROR) ? 1 : 0;
        $this->_message            = null;

        if ($mbObject && $mbObject->_id) {
            $this->setObjectIdClass($mbObject);
            $this->setIdPermanent($mbObject);
        }

        $this->_acquittement = $msgAck;
        if (!$this->send_datetime) {
            $this->send_datetime = CMbDT::dateTime();
        }
        $this->response_datetime = CMbDT::dateTime();
        $this->store();

        return $msgAck;
    }

    /**
     * Generate 'Application Accept' acknowledgment
     *
     * @param CHL7Acknowledgment $ack            Acknowledgment
     * @param array|string       $mb_error_codes Mediboard errors codes
     * @param null               $comments       Comments
     * @param CMbObject          $mbObject       Object
     *
     * @return string
     * @throws Exception
     */
    function setAckAA(CHL7Acknowledgment $ack, $mb_error_codes = null, $comments = null, CMbObject $mbObject = null)
    {
        $ack->generateAcknowledgment("AA", $mb_error_codes, "0", "I", $comments, $mbObject);

        return $this->populateExchangeACK($ack, $mbObject);
    }

    /**
     * Generate 'Application Error' acknowledgment
     *
     * @param CHL7Acknowledgment $ack            Acknowledgment
     * @param array|string       $mb_error_codes Mediboard errors codes
     * @param string             $comments       Comments
     * @param CMbObject          $mbObject       Object
     *
     * @return string
     * @throws Exception
     */
    function setAckAE(CHL7Acknowledgment $ack, $mb_error_codes, $comments = null, CMbObject $mbObject = null)
    {
        $ack->generateAcknowledgment("AE", $mb_error_codes, "0", "W", $comments, $mbObject);

        return $this->populateExchangeACK($ack, $mbObject);
    }

    /**
     * Generate 'Application Reject' acknowledgment
     *
     * @param CHL7Acknowledgment $ack            Acknowledgment
     * @param array|string       $mb_error_codes Mediboard errors codes
     * @param string             $comments       Comments
     * @param CMbObject          $mbObject       Object
     *
     * @return string
     * @throws Exception
     */
    function setAckAR(CHL7Acknowledgment $ack, $mb_error_codes, $comments = null, CMbObject $mbObject = null)
    {
        $ack->generateAcknowledgment("AR", $mb_error_codes, "207", "E", $comments, $mbObject);

        return $this->populateExchangeACK($ack, $mbObject);
    }

    /**
     * Generate 'Patient Demographics Response' acknowledgment
     *
     * @param CHL7v2PatientDemographicsAndVisitResponse $ack        Acknowledgment
     * @param array                                     $objects    Objects
     * @param string                                    $QPD8_error QPD-8 that contained the unrecognized domain
     * @param CDomain[]                                 $domains    Domains
     *
     * @return string
     * @throws Exception
     */
    function setPDRAA(CHL7v2PatientDemographicsAndVisitResponse $ack, $objects = [], $QPD8_error = null, $domains = [])
    {
        $ack->generateAcknowledgment("AA", "0", "I", $objects, null, $domains);

        return $this->populateExchangeACK($ack);
    }

    /**
     * Generate 'Patient Demographics Response' acknowledgment
     *
     * @param CHL7v2PatientDemographicsAndVisitResponse $ack        Acknowledgment
     * @param array                                     $objects    Objects
     * @param string                                    $QPD8_error QPD-8 that contained the unrecognized domain
     *
     * @return string
     * @throws Exception
     */
    function setPDRAE(CHL7v2PatientDemographicsAndVisitResponse $ack, $objects = null, $QPD8_error = null)
    {
        $ack->generateAcknowledgment("AE", "204", "E", null, $QPD8_error);

        return $this->populateExchangeACK($ack);
    }

    /**
     * Generate ORR 'Success' acknowledgment
     *
     * @param CHL7Acknowledgment $ack            Acknowledgment
     * @param array              $mb_error_codes Mediboard errors codes
     * @param null               $comments       Comments
     * @param CMbObject          $mbObject       Object
     *
     * @return string
     * @throws Exception
     */
    function setORRSuccess(
        CHL7Acknowledgment $ack,
        $mb_error_codes = null,
        $comments = null,
        CMbObject $mbObject = null
    ) {
        $ack->generateAcknowledgment("AA", $mb_error_codes, "0", "I", $comments, $mbObject);

        return $this->populateExchangeACK($ack, $mbObject);
    }

    /**
     * Generate ORR 'Error' acknowledgment
     *
     * @param CHL7Acknowledgment $ack            Acknowledgment
     * @param string|array       $mb_error_codes Mediboard errors codes
     * @param string             $comments       Comments
     * @param CMbObject          $mbObject       Object
     *
     * @return string
     * @throws Exception
     */
    function setORRError(CHL7Acknowledgment $ack, $mb_error_codes, $comments = null, CMbObject $mbObject = null)
    {
        $ack->generateAcknowledgment("AR", $mb_error_codes, "204", "E", $comments, $mbObject);

        return $this->populateExchangeACK($ack);
    }

    /**
     * @inheritdoc
     */
    function rejectMessageAlreadyReceived($message_id)
    {
    }

    /**
     * @inheritdoc
     */
    function loadView()
    {
        parent::loadView();

        $this->getObservations();
    }

    /**
     * @inheritdoc
     */
    function getObservations($display_errors = false)
    {
        if ($this->_acquittement) {
            $acq = $this->_acquittement;

            $this->_observations = [];

            if (strpos($acq, "UNICODE") !== false) {
                $acq = utf8_decode($acq);
            }

            // quick regex
            // ERR|~~~207^0^0^E201||207|E|code^libelle|||commentaire
            $pattern = "/ERR\|[^\|]*\|[^\|]*\|[^\|]*\|([^\|]*)\|([^\^]+)\^([^\|]+)\|[^\|]*\|[^\|]*\|([^\r\n\|]*)/ms";
            if (preg_match_all($pattern, $acq, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    if ($match[1] == "E") {
                        $this->_observations[$match[2]] = [
                            "code"        => $match[2],
                            "libelle"     => $match[3],
                            "commentaire" => strip_tags($match[4]),
                        ];
                    }
                }

                return $this->_observations;
            }
        }
    }

    /**
     * @inheritdoc
     */
    function getAcknowledgment($data_format, $ack_data)
    {
        $ack = new CHL7v2Acknowledgment($data_format);
        $ack->handle($ack_data);
        $this->statut_acquittement = $ack->getStatutAcknowledgment();
        $this->acquittement_valide = $ack->message->isOK(CHL7v2Error::E_ERROR) ? 1 : 0;
    }
}
