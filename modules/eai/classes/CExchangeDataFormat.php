<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Exception;
use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\Contracts\Client\FileClientInterface;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Core\Contracts\Client\MLLPClientInterface;
use Ox\Core\Contracts\Client\SOAPClientInterface;
use Ox\Interop\Ftp\CSenderFTP;
use Ox\Interop\Hl7\CExchangeHL7v2;
use Ox\Interop\Hl7\CHL7;
use Ox\Interop\Hl7\CHL7v2Error;
use Ox\Interop\Hl7\CReceiverHL7v2;
use Ox\Interop\Hprim21\CEchangeHprim21;
use Ox\Interop\Hprim21\CHPrim21Acknowledgment;
use Ox\Interop\Hprimxml\CDestinataireHprim;
use Ox\Interop\Hprimxml\CEchangeHprim;
use Ox\Interop\Hprimxml\CHPrimXMLAcquittements;
use Ox\Interop\Hprimxml\Event\CHPrimXMLEventPatient;
use Ox\Interop\Hprimxml\Event\CHPrimXMLEventServeurActivitePmsi;
use Ox\Interop\Ihe\CIHE;
use Ox\Interop\Phast\CPhastDestinataire;
use Ox\Interop\Phast\Event\CPhastEventPN13;
use Ox\Mediboard\Doctolib\CReceiverHL7v2Doctolib;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\System\CContentAny;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSenderFileSystem;

/**
 * Class CExchangeDataFormat
 * Exchange Data Format
 */
class CExchangeDataFormat extends CMbObject
{
    // DB Fields
    public $group_id;
    public $date_production;
    public $sender_id;
    public $sender_class;
    public $receiver_id;
    public $type;
    public $sous_type;
    public $send_datetime;
    public $response_datetime;
    public $message_content_id;
    public $acquittement_content_id;
    public $statut_acquittement;
    public $message_valide;
    public $acquittement_valide;
    public $id_permanent;
    public $object_id;
    public $object_class;
    public $reprocess;
    public $master_idex_missing;
    public $emptied;

    // Meta
    public $_ref_object;

    // Filter fields
    public $_date_min;
    public $_date_max;

    // Form fields
    public $_self_sender;
    public $_self_receiver;
    public $_message;
    public $_acquittement;
    public $_count_exchanges;
    public $_count_msg_invalide;
    public $_count_ack_invalide;
    public $_observations             = [];
    public $_doc_errors_msg           = [];
    public $_doc_warnings_msg         = [];
    public $_doc_errors_ack           = [];
    public $_doc_warnings_ack         = [];
    public $_load_content             = true;
    public $_messages_supported_class = [];
    public $_to_treatment             = true;
    public $_event_message;
    public $_events_message_by_family;
    public $_configs_format;
    public $_delayed;
    public $_exchange_id;

    // Délai envoi (production - send)
    public $_friendly_delay_send;
    // Durée envoi (send - response)
    public $_friendly_duration_send;

    /** @var array */
    public $_mysql_infos;

    /** @var CGroups */
    public $_ref_group;

    /** @var CInteropSender */
    public $_ref_sender;

    /** @var CInteropReceiver */
    public $_ref_receiver;

    /** @var  CContentAny */
    public $_ref_message_content;
    /** @var  CContentAny */
    public $_ref_message_initial;
    /** @var  CContentAny */
    public $_ref_acquittement_content;

    /**
     * Get child exchanges
     *
     * @param string $class       Classname
     * @param bool   $short_names [optional] If true, return short_names instead of namespaced names
     *
     * @return string[] Data format classes collection
     * @throws Exception
     */
    static function getAll($class = CExchangeDataFormat::class, $short_names = true)
    {
        return CApp::getChildClasses($class, true, $short_names);
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props = parent::getProps();

        $props["date_production"]     = "dateTime notNull";
        $props["sender_id"]           = "ref class|CInteropSender meta|sender_class autocomplete|nom";
        $props["sender_class"]        = "enum list|CSenderFTP|CSenderSOAP|CSenderFileSystem show|0";
        $props["receiver_id"]         = "ref class|CInteropReceiver";
        $props["group_id"]            = "ref notNull class|CGroups autocomplete|text";
        $props["type"]                = "str";
        $props["sous_type"]           = "str";
        $props["send_datetime"]       = "dateTime";
        $props["response_datetime"]   = "dateTime";
        $props["statut_acquittement"] = "str show|0";
        $props["message_valide"]      = "bool show|0";
        $props["acquittement_valide"] = "bool show|0";
        $props["id_permanent"]        = "str";
        $props["object_id"]           = "ref class|CMbObject meta|object_class unlink";
        $props["object_class"]        = "str notNull class show|0";
        $props["reprocess"]           = "num min|0 max|" . CAppUI::conf("eai max_reprocess_retries") . " default|0";
        $props["master_idex_missing"] = "bool show|0";
        $props["emptied"]             = "bool show|0";

        $props["_self_sender"]        = "bool";
        $props["_self_receiver"]      = "bool notNull";
        $props["_date_min"]           = "dateTime";
        $props["_date_max"]           = "dateTime";
        $props["_count_exchanges"]    = "num";
        $props["_count_msg_invalide"] = "num";
        $props["_count_ack_invalide"] = "num";
        $props["_observations"]       = "str";
        $props["_doc_errors_msg"]     = "str";
        $props["_doc_errors_ack"]     = "str";

        $props["_friendly_delay_send"]    = "str";
        $props["_friendly_duration_send"] = "str";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        // Chargement des contents
        if ($this->_load_content) {
            $this->loadContent();
        }

        $this->_self_sender   = $this->sender_id === null;
        $this->_self_receiver = $this->receiver_id === null;

        if ($this->send_datetime > CMbDT::dateTime(
                "+ " . CAppUI::conf("eai exchange_format_delayed") . " minutes",
                $this->date_production
            )) {
            $this->_delayed = CMbDT::minutesRelative($this->date_production, $this->send_datetime);
        }

        if ($this->send_datetime && $this->date_production) {
            $this->_friendly_delay_send = CMbDT::relativeDuration($this->date_production, $this->send_datetime);
        }

        if ($this->send_datetime && $this->response_datetime) {
            $this->_friendly_duration_send = CMbDT::relativeDuration($this->send_datetime, $this->response_datetime);
        }
    }

    /**
     * Load content
     *
     * @return void
     */
    function loadContent()
    {
    }

    /**
     * Purge the CExchangeDataFormat older than the configured threshold
     *
     * @return bool|resource|void
     */
    function purgeAllSome()
    {
        $this->purgeEmptySome();
        $this->purgeDeleteSome();
    }

    /**
     * Purge the CExchangeDataFormat older than the configured threshold
     *
     * @return bool|resource|void
     */
    function purgeEmptySome()
    {
        $purge_empty_threshold = CAppUI::conf('eai CExchangeDataFormat purge_empty_threshold');

        $date  = CMbDT::dateTime("- {$purge_empty_threshold} days");
        $limit = CAppUI::conf("eai CExchangeDataFormat purge_probability") * 10;
        if (!$limit) {
            return null;
        }

        $where                    = [];
        $where["emptied"]         = " = '0'";
        $where["date_production"] = " < '$date'";

        $order = "date_production ASC";

        $exchanges = $this->loadList($where, $order, $limit);

        $content_ids = array_merge(
            CMbArray::pluck($exchanges, "message_content_id"),
            CMbArray::pluck($exchanges, "acquittement_content_id")
        );

        $content_spec = $this->_specs["message_content_id"];
        /** @var CStoredObject $content */
        $content = new $content_spec->class;

        // Suppression des contents
        $content->deleteAll($content_ids);

        // Marquage des échanges
        $ds = $this->getDS();

        $exchange_ids    = CMbArray::pluck($exchanges, "_id");
        $in_exchange_ids = CSQLDataSource::prepareIn($exchange_ids);
        $query           = "UPDATE `{$this->_spec->table}` SET
                `message_content_id` = NULL,
                `acquittement_content_id` = NULL,
                `emptied` = '1'
              WHERE `{$this->_spec->key}` $in_exchange_ids";

        $ds->exec($query);
    }

    /**
     * Purge the CExchangeDataFormat older than the configured threshold
     *
     * @return bool|resource|void
     */
    function purgeDeleteSome()
    {
        $purge_delete_threshold = CAppUI::conf('eai CExchangeDataFormat purge_delete_threshold');

        $date  = CMbDT::dateTime("- {$purge_delete_threshold} days");
        $limit = CAppUI::conf("eai CExchangeDataFormat purge_probability") * 10;
        if (!$limit) {
            return null;
        }

        $ds    = $this->getDS();
        $query = new CRequest();
        $query->addTable($this->_spec->table);
        $query->addWhereClause("date_production", "< '$date'");
        $query->addWhereClause("emptied", "= '1'");
        $query->setLimit($limit);
        $ds->exec($query->makeDelete());
    }

    /**
     * Load Groups
     *
     * @return CGroups
     */
    function loadRefGroups()
    {
        $this->_ref_group = $this->loadFwdRef("group_id", true);
    }

    /**
     * Get observations
     *
     * @return array
     */
    function getObservations()
    {
    }

    /**
     * Get errors
     *
     * @return array
     */
    function getErrors()
    {
    }

    /**
     * Get encoding
     *
     * @return string
     */
    function getEncoding()
    {
    }

    /**
     * Is well formed ?
     *
     * @param string $data Data
     *
     * @return bool
     */
    function isWellFormed($data)
    {
    }

    /**
     * Understand ?
     *
     * @param string        $data  Data
     * @param CInteropActor $actor Actor
     *
     * @return bool
     */
    public function understand(string $data, CInteropActor $actor = null): bool
    {
        return false;
    }

    /**
     * Handle exchange
     *
     * @return string|null
     */
    function handle()
    {
    }

    /**
     * Get configs
     *
     * @param string $actor_guid Actor
     *
     * @return array
     */
    function getConfigs($actor_guid)
    {
    }

    /**
     * Count exchanges, make totals by format
     *
     * @return int The absolute total
     */
    function countExchangesDF()
    {
        // Total des échanges
        $this->_count_exchanges = $this->countList();

        // Total des messages invalides
        $where                   = [];
        $where['message_valide'] = " = '0'";
        if (isset($this->_specs["message_content_id"])) {
            $where['message_content_id'] = "IS NOT NULL";
        }
        $this->_count_msg_invalide = $this->countList($where);

        // Total des acquittements invalides
        $where                        = [];
        $where['acquittement_valide'] = " = '0'";

        if (isset($this->_specs["acquittement_content_id"])) {
            $where['acquittement_content_id'] = "IS NOT NULL";
        }
        $this->_count_ack_invalide = $this->countList($where);
    }

    /**
     * Get messages supported
     *
     * @param string $actor_guid   Actor guid
     * @param bool   $all          All messages
     * @param string $evenement    Event name
     * @param bool   $show_actif   Show only active
     * @param string $profil_class CInteropNorm
     *
     * @return array
     */
    public function getMessagesSupported(
        string $actor_guid,
        bool $all = true,
        string $evenement = null,
        bool $show_actif = false,
        string $profil_class = null
    ): array {
        $args = func_get_args();

        $cache = new Cache("{$this->_class}.getMessagesSupported", $args, Cache::INNER);
        if ($cache->exists()) {
            $family = $cache->get();
            foreach ($family as $_root_class => $messages_supported) {
                foreach ($messages_supported as $_message_supported) {
                    $this->_messages_supported_class[] = $_message_supported->message;
                }
            }

            return $family;
        }

        $family = $this->_getMessagesSupported($actor_guid, $all, $evenement, $show_actif, $profil_class);

        return $cache->put($family);
    }

    /**
     * Get messages supported
     *
     * @param string $actor_guid   Actor guid
     * @param bool   $all          All messages
     * @param string $evenement    Event name
     * @param bool   $show_actif   Show only active
     * @param string $profil_class CInteropNorm
     *
     * @return array
     */
    protected function _getMessagesSupported(
        string $actor_guid,
        bool $all = true,
        string $evenement = null,
        bool $show_actif = false,
        string $profil_class = null
    ): array {
        [$object_class, $object_id] = explode("-", $actor_guid);
        $family = [];

        // Récupération de toutes les familles de l'échange ou directement celle récupérée par le dispatcher à la
        // réception d'un message
        $families = $profil_class ? [$profil_class] : $this->getFamily();
        foreach ($families as $_root_class) {
            $root = new $_root_class();

            foreach ($root->getEvenements() as $_evt => $_evt_class) {
                if ($evenement && ($evenement != $_evt)) {
                    continue;
                }

                $message_supported               = new CMessageSupported();
                $message_supported->object_class = $object_class;
                $message_supported->object_id    = $object_id;
                $message_supported->message      = $_evt_class;
                $message_supported->profil       = $_root_class;

                if ($show_actif) {
                    $message_supported->active = $show_actif;
                }

                $message_supported->loadMatchingObject();
                if (!$message_supported->_id && !$all) {
                    continue;
                }

                $message_supported->loadEventByName();
                $message_supported->_data_format = $this;

                $this->_messages_supported_class[] = $message_supported->message;

                $family[$_root_class][] = $message_supported;
            }
        }

        return $family;
    }

    /**
     * Get family
     *
     * @return array
     */
    function getFamily()
    {
        return [];
    }

    /**
     * Set object_id & object_class
     *
     * @param CMbObject $mbObject Object
     *
     * @return void
     */
    function setObjectIdClass(CMbObject $mbObject)
    {
        if ($mbObject) {
            $this->object_id    = $mbObject->_id;
            $this->object_class = $mbObject->_class;
        }
    }

    /**
     * Set permanent identifier
     *
     * @param CMbObject $mbObject Object
     *
     * @return void
     */
    function setIdPermanent(CMbObject $mbObject)
    {
        if ($mbObject instanceof CPatient) {
            if (!$mbObject->_IPP) {
                $mbObject->loadIPP($this->group_id);
            }
            $this->id_permanent = $mbObject->_IPP;
        }

        if ($mbObject instanceof CSejour) {
            if (!$mbObject->_NDA) {
                $mbObject->loadNDA($this->group_id);
            }
            $this->id_permanent = $mbObject->_NDA;
        }
    }

    /**
     * Send exchange
     *
     * @return void
     * @throws CMbException
     *
     */
    function send()
    {
        $this->loadRefsInteropActor();

        if (!$this->message_valide) {
            throw new CMbException("CExchangeDataFormat-msg-Invalid exchange");
        }

        if (!$this->receiver_id) {
            throw new CMbException("CExchangeDataFormat-msg-Unable to Send Message");
        }

        $receiver = $this->_ref_receiver;
        $receiver->loadConfigValues();

        $evenement = null;

        $msg = $this->_message;
        if ($receiver instanceof CReceiverHL7v2) {
            if ($receiver->_configs["encoding"] == "UTF-8" && !CMbString::isUTF8($msg)) {
                $msg = utf8_encode($msg);
            }

            $events    = CMbArray::get($receiver->getSpec()->messages, $this->type);
            $evenement = reset($events);

            try {
                // On va chercher en fonction de l'event IHE
                $data_format = CIHE::getEvent($this);
            } catch (CMbException $e) {
                // Si pas d'event IHE on va remonter à HL7
                $data_format = CHL7::getEvent($this);
            }

            // Cas d'un destinataire doctolib
            if (CModule::getActive('doctolib') && ($receiver->type === CInteropActor::ACTOR_DOCTOLIB)) {
                $receiver = CReceiverHL7v2Doctolib::get($this->group_id);
                // On est obligé de mettre le _class à CReceiverHL7v2 pour le loadMatching des messages supportés
                $receiver->_class = CClassMap::getSN(CReceiverHL7v2::class);
                $receiver->send($evenement, $msg, $this);

                return;
            }
        }

        /*if ($receiver instanceof CReceiverHL7v3) {
        }*/

        if ($receiver instanceof CDestinataireHprim) {
            if ($receiver->_configs["encoding"] == "UTF-8" && !CMbString::isUTF8($msg)) {
                $msg = utf8_encode($msg);
            }

            if ($this->type == "patients") {
                $evenement   = "evenementPatient";
                $data_format = CHPrimXMLEventPatient::getHPrimXMLEvenements($this->_message);
            }

            if ($this->type == "pmsi") {
                $data_format = CHPrimXMLEventServeurActivitePmsi::getHPrimXMLEvenements($this->_message);
                $evenement   = $data_format->sous_type;
            }
        }

        if ($receiver instanceof CPhastDestinataire) {
            $data_format = CPhastEventPN13::getXMLEvenementsPN13($this->_message);
            $evenement   = $data_format->sous_type;
        }

        if (!$evenement) {
            throw new CMbException("CExchangeDataFormat-msg-No events defined");
        }

        $source = CExchangeSource::get("$receiver->_guid-$evenement");

        if (!$source->_id || !$source->active) {
            throw new CMbException("CExchangeDataFormat-msg-No source for this actor");
        }

        // Si on n'a pas d'IPP et NDA
        if ($this->master_idex_missing) {
            throw new CMbException("CExchangeDataFormat-msg-Master idex missing");
        }

        $source->setData($msg, false, $this);
        $this->send_datetime = CMbDT::dateTime();
        $client              = $source->getClient();
        if ($client instanceof FileClientInterface || $client instanceof SOAPClientInterface || $client instanceof MLLPClientInterface) {
            $client->send();
        } else {
            throw new CMbException('CExchangeSource-msg-client not supported', $source->name);
        }
        $this->response_datetime = CMbDT::dateTime();

        // Si on n'a pas d'acquittement
        if (!$ack_data = $source->getACQ()) {
            $this->store();

            return;
        }

        $this->getAcknowledgment($data_format, $ack_data);

        if (CModule::getActive("appFineClient")) {
            $object = $this->loadTargetObject();
            CAppFineClient::generateIdexEventId($receiver, $object, $ack_data);
        }

        $this->_acquittement = $ack_data;
        $this->store();
    }

    /**
     * Load interop actors
     *
     * @return void
     */
    function loadRefsInteropActor()
    {
        $this->loadRefReceiver();
        $this->loadRefSender();
    }

    /**
     * Load interop receiver
     *
     * @return CInteropReceiver
     */
    function loadRefReceiver()
    {
        return $this->_ref_receiver = $this->loadFwdRef("receiver_id", true);
    }

    /**
     * Load interop sender
     *
     * @return CInteropSender
     */
    function loadRefSender()
    {
        return $this->_ref_sender = $this->loadFwdRef("sender_id", true);
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        /* Possible purge when creating a CExchangeDataFormat */
        if (!$this->_id) {
            CApp::doProbably(CAppUI::conf('eai CExchangeDataFormat purge_probability'), [$this, 'purgeAllSome']);
        }

        return parent::store();
    }

    /**
     * Get acknowledgment
     *
     * @param CInteropNorm $data_format Data format
     * @param string       $ack_data    Acknowledgment
     *
     * @return string
     */
    function getAcknowledgment($data_format, $ack_data)
    {
    }

    /**
     * @param bool $cache
     *
     * @return mixed
     * @throws Exception
     * @deprecated
     * @todo redefine meta raf
     */
    public function loadTargetObject($cache = true)
    {
        return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
    }

    /**
     * Reprocessing exchange
     *
     * @return void
     * @throws CMbException
     *
     */
    function reprocessing()
    {
        if ($this->reprocess >= CAppUI::conf("eai max_reprocess_retries")) {
            throw new CMbException("CExchangeDataFormat-too_many_retries", $this->reprocess);
        }

        if (!$this->sender_id || !$this->sender_class) {
            throw new CMbException("CExchangeDataFormat-msg-Message untreatable");
        }

        $sender = new $this->sender_class;
        $sender->load($this->sender_id);

        // Suppression de l'identifiant dans le cas où l'échange repasse pour éviter un autre échange avec
        // un identifiant forcé
        if ($this instanceof CExchangeAny) {
            $exchange_id = $this->_id;
            $this->_id   = null;
        }

        if (!$ack_data = CEAIDispatcher::dispatch($this->_message, $sender, $this->_id)) {
            // Dans le cas d'un échange générique on le supprime
            if ($this instanceof CExchangeAny) {
                $this->_id = $exchange_id;
                if ($msg = $this->delete()) {
                    throw new CMbException("CMbObject-msg-delete-failed", $msg);
                }
            }
        }

        $this->load($this->_id);

        // Dans le cas d'un échange générique on le supprime
        if ($this instanceof CExchangeAny) {
            $this->_id = $exchange_id;
            if ($msg = $this->delete()) {
                throw new CMbException("CMbObject-msg-delete-failed", $msg);
            }
        }

        if (!$ack_data) {
            return;
        }

        if ($sender instanceof CSenderFileSystem || $sender instanceof CSenderFTP) {
            CEAIDispatcher::createFileACK($ack_data, $sender);
        }

        $ack_valid = 0;
        if ($this instanceof CEchangeHprim) {
            $dom_evt = $sender->_data_format->_event_message->getHPrimXMLEvenements($this->_message);
            $ack     = CHPrimXMLAcquittements::getAcquittementEvenementXML($dom_evt);
            $ack->loadXML($ack_data);
            $ack_valid = $ack->schemaValidate(null, false, false);
            if ($ack_valid) {
                $this->statut_acquittement = $ack->getStatutAcquittement();
            }
        }

        if ($this instanceof CEchangeHprim21) {
            $ack = new CHPrim21Acknowledgment($sender->_data_format->_event_message);
            $ack->handle($ack_data);
            $this->statut_acquittement = $ack->getStatutAcknowledgment();
            $ack_valid                 = $ack->message->isOK(CHL7v2Error::E_ERROR);
        }

        if ($this instanceof CExchangeHL7v2) {
            $evt               = $sender->_data_format->_event_message;
            $evt->_data_format = $sender->_data_format;

            // Récupération des informations du message - CHL7v2MessageXML
            $dom_evt           = $evt->handle($this->_message);
            $dom_evt->_is_i18n = $evt->_is_i18n;

            $ack = $dom_evt->getEventACK($evt);
            $ack->handle($ack_data);

            $this->statut_acquittement = $ack->getStatutAcknowledgment();
            $ack_valid                 = $ack->message->isOK(CHL7v2Error::E_ERROR);
        }

        $this->send_datetime       = CMbDT::dateTime();
        $this->acquittement_valide = $ack_valid ? 1 : 0;
        $this->_acquittement       = $ack_data;
        $this->reprocess++;

        if ($msg = $this->store()) {
            throw new CMbException("CMbObject-msg-store-failed", $msg);
        }
    }

    /**
     * Inject master idex (IPP/NDA) missing
     *
     * @return void
     * @throws CMbException
     *
     */
    function injectMasterIdexMissing()
    {
        $patient = new CPatient();
        $sejour  = new CSejour();
        if ($this->object_class && $this->object_id) {
            $object = CMbObject::loadFromGuid("$this->object_class-$this->object_id");

            if ($object instanceof CPatient) {
                $patient = $object;
                $patient->loadIPP($this->group_id);

                if (!$patient->_IPP) {
                    return;
                }
            }

            if ($object instanceof CSejour) {
                $sejour = $object;
                $sejour->loadNDA($this->group_id);
                $sejour->loadRefPatient()->loadIPP($this->group_id);

                $patient = $sejour->_ref_patient;

                if (!$patient->_IPP || !$sejour->_NDA) {
                    return;
                }
            }

            if ($object instanceof CAffectation) {
                $affectation = $object;
                $sejour      = $affectation->loadRefSejour();

                $sejour->loadNDA($this->group_id);
                $sejour->loadRefPatient()->loadIPP($this->group_id);

                $patient = $sejour->_ref_patient;

                if (!$patient->_IPP || !$sejour->_NDA) {
                    return;
                }
            }
        }

        $pattern = "===IPP_MISSING===";
        if (strpos($this->_message, $pattern) !== false) {
            $this->_message = str_replace("===IPP_MISSING===", $patient->_IPP, $this->_message);
        }

        $pattern = "===NDA_MISSING===";
        if (strpos($this->_message, $pattern) !== false) {
            $this->_message = str_replace("===NDA_MISSING===", $sejour->_NDA, $this->_message);
        }

        $this->master_idex_missing = strpos($this->_message, "===NDA_MISSING===") !== false ||
            strpos($this->_message, "===IPP_MISSING===") !== false;

        if ($msg = $this->store()) {
            throw new CMbException(CAppUI::tr("$this->_class-msg-store-failed") . $msg);
        }
    }

    /**
     * Reject message already received
     *
     * @param int $message_id Message
     *
     * @return bool
     */
    function rejectMessageAlreadyReceived($message_id)
    {
        return false;
    }

    /**
     * Get mysql info
     *
     * @return array Infos
     */
    function getMysqlInfos()
    {
        $ds = $this->getDS();

        $db = CMbArray::get($ds->config, "dbname");

        $query = "SELECT  data_length + index_length AS 'size',
                     data_free
              FROM information_schema.TABLES
              WHERE table_schema = '$db'
              AND table_name = '{$this->_spec->table}';";

        $mysql_infos_exchange = CMbArray::get($ds->loadList($query), 0);

        $content_spec        = CMbArray::get($this->_specs, "message_content_id");
        $mysql_infos_content = [];
        if ($content_spec) {
            /** @var CStoredObject $content */
            $content = new $content_spec->class;
            $query   = "SELECT  data_length + index_length AS 'size',
                     data_free
              FROM information_schema.TABLES
              WHERE table_schema = '$db'
              AND table_name = '{$content->_spec->table}';";

            $mysql_infos_content = CMbArray::get($ds->loadList($query), 0);
        }

        $mysql_infos = [
            "size"      => CMbArray::get($mysql_infos_exchange, "size") + CMbArray::get($mysql_infos_content, "size"),
            "data_free" => CMbArray::get($mysql_infos_exchange, "data_free") + CMbArray::get(
                    $mysql_infos_content,
                    "data_free"
                ),
        ];

        $this->_mysql_infos = $mysql_infos;
    }

    /**
     * @param CStoredObject $object
     *
     * @return void
     * @todo redefine meta raf
     * @deprecated
     */
    public function setObject(CStoredObject $object)
    {
        CMbMetaObjectPolyfill::setObject($this, $object);
    }

    /**
     * @inheritDoc
     * @todo remove
     */
    function loadRefsFwd()
    {
        parent::loadRefsFwd();
        $this->loadTargetObject();
    }
}
