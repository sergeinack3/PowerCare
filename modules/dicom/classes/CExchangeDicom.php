<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Dicom;

use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Interop\Dicom\Data\CDicomPresentationContext;
use Ox\Interop\Dicom\Network\Pdu\CDicomPDU;
use Ox\Interop\Dicom\Network\Pdu\CDicomPDUFactory;
use Ox\Interop\Eai\CExchangeBinary;
use Ox\Interop\Eai\CInteropActor;

/**
 * A Dicom exchange
 */
class CExchangeDicom extends CExchangeBinary
{

    static $messages = [
        "Echo" => "CEcho",
        "Find" => "CFind",
    ];

    /**
     * Table Key
     *
     * @var integer
     */
    public $dicom_exchange_id;

    /**
     * The request
     * If there is several messages, they are separated by "|"
     *
     * @var string
     */
    public $requests;

    /**
     * The response
     * If there is several messages, they are separated by "|"
     *
     * @var string
     */
    public $responses;

    /**
     * The presentation contexts, in string
     *
     * @var string
     */
    public $presentation_contexts;

    /**
     * The request
     *
     * @var CDicomPDU[]
     */
    public $_requests;

    /**
     * The response
     *
     * @var CDicomPDU[]
     */
    public $_responses;

    /**
     * The presentation contexts
     *
     * @var CDicomPresentationContext[]
     */
    public $_presentation_contexts;

    /**
     * @inheritDoc
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->loggable = false;
        $spec->table    = "dicom_exchange";
        $spec->key      = "dicom_exchange_id";

        return $spec;
    }

    /**
     * Get the properties of our class as string
     *
     * @return array
     */
    function getProps()
    {
        $props                          = parent::getProps();
        $props["group_id"]              .= " back|echanges_dicom";
        $props["requests"]              = "text";
        $props["responses"]             = "text";
        $props["presentation_contexts"] = "str show|0";
        $props["sender_id"]             .= " back|exchange_dicom";
        $props["sender_class"]          = "enum list|CDicomSender show|0";
        $props["object_class"]          = "str class show|0";
        $props["receiver_id"]           .= " back|echanges_dicom";
        $props["object_id"]             .= " back|exchanges_dicom";

        return $props;
    }

    /**
     * Decode the messages
     *
     * @return null
     */
    function decodeContent()
    {
        if ($this->presentation_contexts && !$this->_presentation_contexts) {
            $pres_contexts_array          = explode('|', $this->presentation_contexts);
            $this->_presentation_contexts = [];

            foreach ($pres_contexts_array as $_pres_context) {
                $_pres_context = explode('/', $_pres_context);

                if (array_key_exists(0, $_pres_context) && array_key_exists(1, $_pres_context) && array_key_exists(
                        2,
                        $_pres_context
                    )) {
                    $this->_presentation_contexts[] = new CDicomPresentationContext(
                        $_pres_context[0],
                        $_pres_context[1],
                        $_pres_context[2]
                    );
                }
            }
        }

        if ($this->requests && !$this->_requests && $this->_presentation_contexts) {
            $request_msgs    = explode("|", $this->requests);
            $this->_requests = [];

            foreach ($request_msgs as $msg) {
                $msg               = base64_decode($msg);
                $pdu               = CDicomPDUFactory::decodePDU($msg, $this->_presentation_contexts);
                $this->_requests[] = $pdu;
            }
        }

        if ($this->responses && !$this->_responses && $this->_presentation_contexts) {
            $response_msgs    = explode("|", $this->responses);
            $this->_responses = [];

            foreach ($response_msgs as $msg) {
                $pdu                = CDicomPDUFactory::decodePDU(base64_decode($msg), $this->_presentation_contexts);
                $this->_responses[] = $pdu;
            }
        }
    }

    /**
     * Update the fields tored in database
     *
     * @return null
     */
    function updatePlainFields()
    {
        parent::updatePlainFields();

        if ($this->_presentation_contexts && !$this->presentation_contexts) {
            foreach ($this->_presentation_contexts as $_pres_context) {
                if (!$this->presentation_contexts) {
                    $this->presentation_contexts = "$_pres_context->id/$_pres_context->abstract_syntax/$_pres_context->transfer_syntax";
                } else {
                    $this->presentation_contexts .= "|$_pres_context->id/$_pres_context->abstract_syntax/$_pres_context->transfer_syntax";
                }
            }
        }

        if ($this->_requests) {
            $this->requests = null;
            foreach ($this->_requests as $_request) {
                if (!$this->requests) {
                    $this->requests = base64_encode($_request->getPacket());
                } else {
                    $this->requests .= "|" . base64_encode($_request->getPacket());
                }
            }
        }

        if ($this->_responses) {
            $this->responses = null;
            foreach ($this->_responses as $_response) {
                if (!$this->responses) {
                    $this->responses = base64_encode($_response->getPacket());
                } else {
                    $this->responses .= "|" . base64_encode($_response->getPacket());
                }
            }
        }
    }

    /**
     * Handle the message
     *
     * @return array
     */
    function handle()
    {
        $operator_dicom = new COperatorDicom();

        return $operator_dicom->event($this);
    }

    /**
     * Check if we can understand the message
     *
     * @param string        $msg           The message
     *
     * @param CInteropActor $actor         The actor
     *
     * @param array         $pres_contexts The presentation contexts
     *
     * @return boolean
     * @throws \Exception
     */
    public function understand(string $data, CInteropActor $actor = null, $pres_contexts = null): bool
    {
        $this->_presentation_contexts = $pres_contexts;
        if (!$this->isWellFormed($data)) {
            return false;
        }

        $pdu  = CDicomPDUFactory::decodePDU($data, $this->_presentation_contexts);
        $pdvs = $pdu->getPDVs();

        $msg_types   = [];
        $msg_classes = [];

        foreach ($pdvs as $pdv) {
            $msg           = $pdv->getMessage();
            $msg_types[]   = $msg->type;
            $msg_classes[] = CClassMap::getSN($msg);
        }


        if ($msg_types[0] == "C-Find-RQ" || $msg_types[0] == "C-Echo-RQ") {
            if (!$this->_requests) {
                $this->_requests = [];
            }
            $this->_requests[] = $pdu;
        } elseif ($msg_types[0] == "C-Echo-RSP" || $msg_types[0] == "C-Find-RSP") {
            if (!$this->_responses) {
                $this->_responses = [];
            }
            $this->_responses[] = $pdu;
        } elseif ($msg_types[0] == "Datas") {
            if ($this->_responses) {
                $this->_responses[] = $pdu;
            } else {
                $this->_requests[] = $pdu;
            }
        }

        foreach ($this->getFamily() as $_family) {
            $family_class = new $_family();
            $events       = $family_class->getEvenements();
            if (array_key_exists($msg_types[0], $events)) {
                $this->_events_message_by_family[$_family][] = $msg_classes[0];
                $this->message_valide                        = 1;

                return true;
            }
        }

        return false;
    }

    /**
     * Check if the message is well formed
     *
     * @param string        $msg   The message
     *
     * @param CInteropActor $actor The actor who sent the message
     *
     * @return boolean
     */
    function isWellFormed($msg, CInteropActor $actor = null)
    {
        $stream = fopen("php://temp", 'w+');
        fwrite($stream, $msg);

        $stream_reader = new CDicomStreamReader($stream);
        $stream_reader->rewind();
        $type = $stream_reader->readHexByte();

        if ($type != "04") {
            $stream_reader->close();

            return false;
        }

        $stream_reader->skip(1);
        $length = $stream_reader->readUInt32();
        $stream_reader->close();

        if (strlen($msg) != $length + 6) {
            return false;
        }

        return true;
    }

    /**
     * Return the family
     *
     * @return array
     */
    function getFamily()
    {
        return self::$messages;
    }

    /**
     * Get the Dicom configs for the given actor
     *
     * @param string $actor_guid Actor GUID
     *
     * @return CDicomConfig|void
     */
    function getConfigs($actor_guid = null)
    {
        if ($actor_guid) {
            [$sender_class, $sender_id] = explode('-', $actor_guid);
        } else {
            $sender_class = $this->sender_class;
            $sender_id    = $this->sender_id;
        }

        $sender_dicom_config               = new CDicomConfig();
        $sender_dicom_config->sender_class = $sender_class;
        $sender_dicom_config->sender_id    = $sender_id;
        $sender_dicom_config->loadMatchingObject();

        return $this->_configs_format = $sender_dicom_config;
    }

    /**
     * @inheritdoc
     */
    function purgeEmptySome()
    {
        $purge_empty_threshold = CAppUI::conf('eai CExchangeDataFormat purge_empty_threshold');

        $date  = CMbDT::dateTime("- {$purge_empty_threshold} days");
        $limit = CAppUI::conf("eai CExchangeDataFormat purge_probability") * 10;

        $where                    = [];
        $where["emptied"]         = " = '0'";
        $where["date_production"] = " < '$date'";

        $order = "date_production ASC";

        $exchange_ids    = $this->loadIds($where, $order, $limit);
        $in_exchange_ids = CSQLDataSource::prepareIn($exchange_ids);

        // Marquage des échanges
        $ds    = $this->getDS();
        $query = "UPDATE `{$this->_spec->table}` SET
                `requests` = NULL,
                `responses` = NULL,
                `emptied` = '1'
              WHERE `{$this->_spec->key}` $in_exchange_ids";
        $ds->exec($query);
    }
}
