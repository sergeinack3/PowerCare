<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Exception;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CStoredObject;
use Ox\Interop\Eai\Transformations\CLinkActorSequence;
use Ox\Interop\Hl7\CHEvent;
use Ox\Interop\Smp\CSmp;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Doctolib\CReceiverHL7v2Doctolib;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CChambre;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Hospi\CMovement;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\CExchangeSource;

/**
 * Class CInteropActor
 * Interoperability Actor
 */
class CInteropActor extends CMbObject
{
    /** @var string */
    public const ACTOR_APPFINE = 'AppFine';

    /** @var string */
    public const ACTOR_DOCTOLIB = 'Doctolib';

    /** @var string */
    public const ACTOR_TAMM = 'Tamm';

    /** @var string */
    public const ACTOR_MEDIBOARD = 'Mediboard';

    /** @var string */
    public const ACTOR_GALAXIE = 'Galaxie';

    /** @var string */
    public const ACTOR_DMP = 'DMP';

    /** @var string */
    public const ACTOR_ZEPRA = 'ZEPRA';

    /** @var string */
    public const ACTOR_ASIP = 'ASIP';

    /** @var string */
    public const ACTOR_STANDARD = '';

    /** @var string[] */
    public const ACTORS_MANAGED = [];

    /** @var string */
    public const ACTOR_TYPE = '';

    /** @var array  */
    public static $actors_configs = ['CSenderSOAP', 'CSenderFileSystem', 'CSenderFTP', 'CSenderSFTP',
        'CSenderHTTP', 'CSenderMLLP', 'CDicomSender', 'CReceiverHL7v2', 'CReceiverHL7v3', 'CReceiverHprimSante',
        'CPhastDestinataire', 'CReceiverFHIR'
    ];

    // DB Fields
    /** @var string */
    public $nom;

    /** @var string */
    public $type;

    /** @var string */
    public $libelle;

    /** @var string */
    public $group_id;

    /** @var int */
    public $actif;

    /** @var string */
    public $role;

    /** @var int */
    public $exchange_format_delayed;

    // Form fields
    /** @var int */
    public $_reachable;

    /** @var string */
    public $_parent_class;

    /** @var string */
    public $_tag_patient;

    /** @var string */
    public $_tag_sejour;

    /** @var string */
    public $_tag_mediuser;

    /** @var string */
    public $_tag_service;

    /** @var string */
    public $_tag_chambre;

    /** @var string */
    public $_tag_lit;

    /** @var string */
    public $_tag_movement;

    /** @var string */
    public $_tag_visit_number;

    /** @var string */
    public $_tag_consultation;

    /** @var string */
    public $_self_tag;

    /** @var array */
    public $_tags = []; // All tags

    // Forward references
    /** @var CGroups */
    public $_ref_group;
    /** @var CDomain */
    public $_ref_domain;

    /** @var CExchangeSource[] */
    public $_ref_exchanges_sources;

    /** @var CExchangeDataFormat */
    public $_ref_last_exchange;

    /** @var int */
    public $_last_exchange_time;

    /** @var CMessageSupported[] */
    public $_ref_messages_supported;

    /** @var int */
    public $_count_messages_supported;

    /** @var array */
    public $_ref_msg_supported_family = [];

    /** @var CLinkActorSequence[] */
    public $_ref_eai_transformations;

    /** @var bool */
    public $_content_altered = false;

    /** @var string */
    public $_type_echange;

    /** @var CExchangeDataFormat */
    public $_data_format;

    /** @var bool */
    public $_is_ins_compatible = false;

    /**
     * Get objects
     *
     * @return array CInteropReceiver/CInteropSender collection
     * @throws Exception
     */
    public static function getObjects(): array
    {
        $receiver = new CInteropReceiver();
        $sender   = new CInteropSender();

        return [
            "CInteropReceiver" => $receiver->getObjects(),
            "CInteropSender"   => $sender->getObjects(),
        ];
    }

    /**
     * Count objects
     *
     * @return array CInteropReceiver/CInteropSender
     * @throws Exception
     */
    public static function countObjects(): array
    {
        $receiver = new CInteropReceiver();
        $sender   = new CInteropSender();

        return [
            "CInteropReceiver" => $receiver->countObjects(),
            "CInteropSender"   => $sender->countObjects(),
        ];
    }

    /**
     * Get objects by events
     *
     * @param array            $events             Events name
     * @param CInteropReceiver $receiver           Receiver
     * @param bool             $only_current_group Load only receivers of the current group
     * @param string           $profil             Profil
     *
     * @return array Receivers supported
     * @throws Exception
     */
    static function getObjectsBySupportedEvents(
        $events = [],
        CInteropReceiver $receiver = null,
        $only_current_group = false,
        $profil = null
    ) {
        $receivers = [];
        $group_id  = CGroups::loadCurrent()->_id;

        foreach ($events as $_event) {
            $msg_supported       = new CMessageSupported();
            $msg_supported_table = $msg_supported->_spec->table;

            $where                                 = [];
            $where["$msg_supported_table.message"] = " = '$_event'";
            if ($profil) {
                $where["$msg_supported_table.profil"] = " = '$profil'";
            }
            $where["$msg_supported_table.active"] = " = '1'";

            $ljoin = [];
            if ($receiver) {
                $table         = $receiver->_spec->table;
                $key           = $receiver->_spec->key;
                $ljoin[$table] = "$table.$key = message_supported.object_id";

                $where["$msg_supported_table.object_class"] = " = '$receiver->_class'";
                if ($receiver->_id) {
                    $where["$msg_supported_table.object_id"] = " = '$receiver->_id'";
                }

                if ($only_current_group) {
                    $where["$table.group_id"] = "= '$group_id'";
                }

                $where["$table.actif"] = " = '1'";
            }

            if (!$msg_supported->loadObject($where, null, null, $ljoin)) {
                $receivers[$_event] = null;

                return $receivers;
            }

            $messages = $msg_supported->loadList($where, "object_class", null, null, $ljoin);

            foreach ($messages as $_message) {
                /** @var CInteropReceiver $receiver_found */
                $receiver_found = CMbObject::loadFromGuid("$_message->object_class-$_message->object_id");
                if (!$receiver_found->actif || $only_current_group && $receiver_found->group_id != $group_id) {
                    continue;
                }

                $receiver_found->loadRefGroup();
                $receiver_found->isReachable();

                $receivers[$_event][] = $receiver_found;
            }
        }

        return $receivers;
    }

    /**
     * Load group forward reference
     *
     * @return CGroups
     * @throws Exception
     */
    function loadRefGroup()
    {
        return $this->_ref_group = $this->loadFwdRef("group_id", 1);
    }

    /**
     * Sender is reachable ?
     *
     * @param bool $put_all_sources Put all sources
     *
     * @return void reachable
     */
    public function isReachable(bool $put_all_sources = false): void
    {
        if (!$this->_ref_exchanges_sources) {
            $this->loadRefsExchangesSources($put_all_sources);
        }
    }

    /**
     * Get exchanges sources
     *
     * @param bool $put_all_sources Put all sources
     *
     * @return array|CExchangeSource[]
     */
    public function loadRefsExchangesSources(bool $put_all_sources = false): array
    {
        return [];
    }

    /**
     * Load receiver
     *
     * @param int $group_id Group ID
     *
     * @return CReceiverHL7v2Doctolib
     * @throws Exception
     */
    public static function get(?int $group_id = null): self
    {
        $receiver           = new static();
        $receiver->type     = self::ACTOR_TYPE ?: null;
        $receiver->group_id = $group_id;
        $receiver->actif    = 1;
        $receiver->role     = CAppUI::conf("instance_role");
        $receiver->loadMatchingObject();

        return $receiver;
    }

    /**
     * @inheritdoc
     */
    public function loadMatchingObject($order = null, $group = null, $ljoin = null, $index = null, bool $strict = true)
    {
        if (!$this->isStandardClass() && $this::ACTOR_TYPE) {
            $this->type = $this::ACTOR_TYPE;
        }

        return parent::loadMatchingObject($order, $group, $ljoin, $index, $strict);
    }

    /**
     * @return bool
     */
    public function isStandardClass(): bool
    {
        return in_array(get_parent_class($this), [CInteropReceiver::class, CInteropSender::class]);
    }

    /**
     * @inheritDoc
     */
    public function getProps()
    {
        $type = '';
        if ($this::ACTORS_MANAGED) {
            $actors = $this::ACTORS_MANAGED;
            sort($actors);
            $type = 'list|' . implode('|', $actors);
            if ($this::ACTOR_STANDARD) {
                $type .= ' default|' . $this::ACTOR_STANDARD;
            }
        }

        $props                            = parent::getProps();
        $props["nom"]                     = "str notNull seekable|begin index";
        $props["libelle"]                 = "str";
        $props["group_id"]                = "ref notNull class|CGroups autocomplete|text";
        $props["actif"]                   = "bool notNull default|1";
        $props["role"]                    = "enum list|prod|qualif default|prod notNull";
        $props["exchange_format_delayed"] = "num min|0 default|60";
        $props["type"]                    = "enum" . ($type ? " $type" : '');

        $props["_reachable"]    = "bool";
        $props["_parent_class"] = "str";

        $props["_self_tag"]         = "str";
        $props["_tag_patient"]      = "str";
        $props["_tag_sejour"]       = "str";
        $props["_tag_consultation"] = "str";
        $props["_tag_mediuser"]     = "str";
        $props["_tag_service"]      = "str";
        $props["_tag_chambre"]      = "str";
        $props["_tag_lit"]          = "str";
        $props["_tag_movement"]     = "str";
        $props["_tag_visit_number"] = "str";

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function store()
    {
        // Create
        if (!$this->_id && !$this->isStandardClass()) {
            $this->type = $this::ACTOR_TYPE;
        }

        return parent::store();
    }

    /**
     * @inheritDoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view         = $this->libelle ?: $this->nom;
        $this->_type_echange = $this->_class;

        $this->_self_tag = $this->getTag();

        $this->_tag_patient = CPatient::getTagIPP($this->group_id);
        $this->_tag_sejour  = CSejour::getTagNDA($this->group_id);

        $this->_tag_consultation = CConsultation::getObjectTag($this->group_id);
        $this->_tag_mediuser     = CMediusers::getObjectTag($this->group_id);
        $this->_tag_service      = CService::getObjectTag($this->group_id);
        $this->_tag_chambre      = CChambre::getObjectTag($this->group_id);
        $this->_tag_lit          = CLit::getObjectTag($this->group_id);
        $this->_tag_movement     = CMovement::getObjectTag($this->group_id);
        $this->_tag_visit_number = CSmp::getObjectTag($this->group_id);
    }

    /**
     * Get actor tag
     *
     * @param int $group_id Group
     *
     * @return string
     * @throws Exception
     */
    private function getTag(): ?string
    {
        return $this->_guid;
    }

    /**
     * Get actor tags
     *
     * @param bool $cache Cache
     *
     * @return array
     * @throws Exception
     */
    public function getInstanceTags(): array
    {
        $tags = [];

        foreach ($this->getSpecs() as $key => $spec) {
            if (strpos($key, "_tag_") === false) {
                continue;
            }

            $tags[$key] = $this->$key;
        }

        return $this->_tags = $tags;
    }

    /**
     * Get idex
     *
     * @param CMbObject $object Object
     *
     * @return CIdSante400
     * @throws Exception
     */
    public function getIdex(CMbObject $object): CIdSante400
    {
        return CIdSante400::getMatchFor($object, $this->getTag($this->group_id, $this->_class));
    }

    /**
     * Load user forward reference
     *
     * @return CMediusers|CStoredObject
     */
    public function loadRefUser(): ?CMediusers
    {
        return null;
    }

    /**
     * Return the fisrt element of exchangesSources
     *
     * @return mixed|null
     */
    public function getFirstExchangesSources()
    {
        $this->loadRefsExchangesSources();
        if (!$this->_ref_exchanges_sources) {
            return null;
        }

        return reset($this->_ref_exchanges_sources);
    }

    /**
     * Load transformations
     *
     * @param array $where Additional where clauses
     *
     * @return CLinkActorSequence[]|CStoredObject[]
     * @throws Exception
     */
    public function loadRefsEAITransformation(array $where = []): array
    {
        return $this->_ref_eai_transformations = $this->loadBackRefs(
            "actor_transformations",
            null,
            null,
            null,
            null,
            null,
            null,
            $where
        );
    }

    /**
     * Load domain
     *
     * @param array $where Additional where clauses
     *
     * @return CDomain|CStoredObject
     * @throws Exception
     */
    public function loadRefDomain(array $where = []): CDomain
    {
        $where["incrementer_id"] = "IS NULL";

        return $this->_ref_domain = $this->loadUniqueBackRef("domain", null, null, null, null, null, $where);
    }

    /**
     * Last message
     *
     * @return null|CExchangeDataFormat
     * @throws Exception
     */
    public function lastMessage(): ?CExchangeDataFormat
    {
        $last_exchange = null;

        // Dans le cas d'un destinataire on peut charger les échanges par la backref
        if ($this instanceof CInteropReceiver) {
            $last_exchange = $this->loadBackRefs(
                'echanges', "send_datetime DESC", "1", null, null, null, null, ["send_datetime IS NOT NULL"]
            );
            if (!$last_exchange) {
                return null;
            }

            $last_exchange = reset($last_exchange);
        }

        // Pour un expéditeur, il faut parcourir tous les formats
        if ($this instanceof CInteropSender) {
            foreach (CExchangeDataFormat::getAll(CExchangeDataFormat::class, false) as $key => $_exchange_class) {
                foreach (CApp::getChildClasses($_exchange_class, true) as $under_key => $_under_class) {
                    /** @var CExchangeDataFormat $exchange */
                    $exchange               = new $_under_class;
                    $exchange->sender_id    = $this->_id;
                    $exchange->sender_class = $this->_class;

                    $exchange->loadMatchingObject("send_datetime DESC");
                    if ($exchange->_id) {
                        $last_exchange = $exchange;

                        continue 2;
                    }
                }
            }
        }

        if (!$last_exchange) {
            return null;
        }

        $this->_last_exchange_time = CMbDT::minutesRelative($last_exchange->send_datetime, CMbDT::dateTime());

        return $this->_ref_last_exchange = $last_exchange;
    }

    /**
     * Count messages supported back reference collection
     *
     * @param array $where Clause where
     *
     * @return int
     * @throws Exception
     */
    public function countMessagesSupported(array $where = []): array
    {
        return $this->_count_messages_supported = $this->countBackRefs("messages_supported", $where);
    }

    /**
     * Is that the message is supported by this actor
     *
     * @param string      $message     Message
     * @param string|null $profil      Profil
     * @param string|null $transaction Transaction
     *
     * @return bool
     */
    public function isMessageSupported(string $message, ?string $profil = null, ?string $transaction = null): bool
    {
        $cache = new Cache(
            "{$this->_class}.isMessageSupported",
            "$this->_guid-$message-$profil-$transaction",
            Cache::INNER
        );
        if ($cache->exists()) {
            return $cache->get() > 0;
        }

        $msg_supported               = new CMessageSupported();
        $msg_supported->object_class = $this->_class;
        $msg_supported->object_id    = $this->_id;
        $msg_supported->message      = $message;
        if ($profil) {
            $msg_supported->profil = $profil;
        }
        if ($transaction) {
            $msg_supported->transaction = $transaction;
        }
        $msg_supported->active = 1;

        return $cache->put($msg_supported->countMatchingList()) > 0;
    }

    /**
     * Get messages supported by family
     *
     * @return array
     * @throws Exception
     */
    public function getMessagesSupportedByFamily(): array
    {
        $family   = [];
        $backRefs = CClassMap::getInstance()->getClassRef(get_class($this))->back;
        foreach ($backRefs as $_back_ref) {
            if (!str_ends_with($_back_ref, 'receiver_id')) {
                continue;
            }

            $tab                = explode(' ', $_back_ref);
            $_data_format_class = CMbArray::get($tab, 0);

            /** @var CExchangeDataFormat $_data_format */
            $_data_format = new $_data_format_class();
            $temp         = $_data_format->getFamily();
            $family       = array_merge($family, $temp);
        }

        if (empty($family)) {
            return $this->_ref_msg_supported_family;
        }

        $supported = $this->loadRefsMessagesSupported();
        foreach ($family as $_family => $_root_class) {
            /** @var CInteropNorm $root */
            $root = new $_root_class();

            $events = $root->getEvenements();
            foreach ($supported as $_msg_supported) {
                if (!$_msg_supported->active) {
                    continue;
                }

                if (!in_array($_msg_supported->message, $events)) {
                    continue;
                }

                $messages = $this->_spec->messages;
                if (isset($messages[$root->type])) {
                    $this->_ref_msg_supported_family = array_merge(
                        $this->_ref_msg_supported_family,
                        $messages[$root->type]
                    );
                    continue 2;
                }
            }
        }

        return $this->_ref_msg_supported_family;
    }

    /**
     * Load messages supported back reference collection
     *
     * @return CMessageSupported[]|CStoredObject[]
     * @throws Exception
     */
    function loadRefsMessagesSupported()
    {
        return $this->_ref_messages_supported = $this->getMessagesSupported();
    }

    /**
     * Get messages supported
     *
     * @return CMessageSupported[]|CStoredObject[]
     *
     * @throws Exception
     */
    function getMessagesSupported()
    {
        $cache = new Cache("{$this->_class}.getMessagesSupported", $this->_guid, Cache::INNER);
        if ($cache->exists()) {
            return $cache->get();
        }

        $messages_supported = $this->loadBackRefs("messages_supported");

        return $cache->put($messages_supported);
    }

    /**
     * Send event
     *
     * @param CHEvent    $evenement      Event
     * @param CMbObject $object         Objet Mediboard
     * @param String[]  $data           String[]
     * @param array     $headers        array
     * @param bool      $message_return No Send the message
     * @param bool      $soapVar        XML message ?
     *
     * @return bool|CHEvent
     */
    public function sendEvent($evenement, $object, $data = [], $headers = [], $message_return = false, $soapVar = false)
    {
        // Si pas actif
        if (!$this->actif) {
            return false;
        }

        if ($this->role != CAppUI::conf("instance_role")) {
            return false;
        }

        return true;
    }

    /**
     * Get event
     *
     * @return bool
     */
    public static function getEvent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function loadMatchingList(
        $order = null,
        $limit = null,
        $group = null,
        $ljoin = null,
        $index = null,
        bool $strict = true
    ) {
        if (!$this->isStandardClass() && $this::ACTOR_TYPE) {
            $this->type = self::ACTOR_TYPE;
        }

        return parent::loadMatchingList($order, $limit, $group, $ljoin, $index, $strict);
    }

    /**
     * @inheritdoc
     */
    public function loadList(
        $where = null,
        $order = null,
        $limit = null,
        $group = null,
        $ljoin = null,
        $index = null,
        $having = null,
        bool $strict = true,
        ?int $limit_time = null
    ) {
        if (!$this->isStandardClass() && $this::ACTOR_TYPE && !array_key_exists('type', $where)) {
            $ds    = $this->getDS();
            $where = array_merge(
                $where,
                ['type' => $ds->prepare('= ?', $this::ACTOR_TYPE)]
            );
        }

        return parent::loadList($where, $order, $limit, $group, $ljoin, $index, $having, $strict, $limit_time);
    }

    /**
     * @inheritdoc
     */
    public function loadObject(
        $where = null,
        $order = null,
        $group = null,
        $ljoin = null,
        $index = null,
        $having = null,
        bool $strict = true
    ) {
        if (!$this->isStandardClass() && $this::ACTOR_TYPE && !array_key_exists('type', $where)) {
            $ds    = $this->getDS();
            $where = array_merge(
                $where,
                ['type' => $ds->prepare('= ?', self::ACTOR_TYPE)]
            );
        }

        return parent::loadObject($where, $order, $group, $ljoin, $index, $having, $strict);
    }

    /**
     * Is the actor INS compatible?
     *
     * @return bool
     */
    public function isINSCompatible(): bool
    {
        return $this->_is_ins_compatible;
    }
}
