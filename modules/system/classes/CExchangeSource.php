<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\Chronometer;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbSecurity;
use Ox\Core\Contracts\Client\ClientInterface;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Interop\Eai\CExchangeTransportLayer;
use Ox\Interop\Eai\Resilience\ClientContext;
use Ox\Interop\Ftp\CircuitBreakerException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Throwable;

/**
 * Exchange Source
 */
class CExchangeSource extends CMbObject
{
    /** @var string Source type */
    public const TYPE = '';

    /** @var string redefine this constant for declared the default client */
    protected const DEFAULT_CLIENT = '';

    /** @var array redefine this constant for declared mapping between client classes and name in bd */
    protected const CLIENT_MAPPING = [];

    /** @var int */
    public const CONNEXION_STATUS_SUCCESS = 1;

    /** @var int */
    public const CONNEXION_STATUS_FAILED = 2;

    /** @var string */
    public $retry_strategy;

    /** @var string */
    public $first_call_date;

    public static $typeToClass = [
        "sftp"        => "CSourceSFTP",
        "ftp"         => "CSourceFTP",
        "soap"        => "CSourceSOAP",
        "smtp"        => "CSourceSMTP",
        "pop"         => "CSourcePOP",
        "file_system" => "CSourceFileSystem",
        "http"        => "CSourceHTTP",
        "syslog"      => "CSyslogSource",
    ];

    //multi instance sources (more than one can run at the same time)
    /** @var string[] */
    public static $multi_instance = [
        "CSourcePOP",
        "CSourceSMTP",
    ];

    // DB Fields
    public static $call_traces = [];
    public        $name;
    public        $role;
    public        $host;
    public        $user;
    public        $password;
    public        $iv;
    public        $type_echange;
    public        $active;
    public        $loggable;

    /** @var string */
    public $client_name;

    // Behaviour Fields
    public $libelle;

    /** @var EventDispatcher */
    public $_dispatcher;

    public $_client;
    public $_data;
    public $_args_list    = false;
    public $_allowed_instances;
    public $_wanted_type;
    public $_incompatible = false;
    public $_reachable;
    public $_message;
    public $_response_time;
    public $_all_source   = [];
    public $_receive_filename;
    public $_acquittement;
    public $_count_exchange;
    public $_target_object;

    /** @var CExchangeDataFormat $_exchange_data_format */
    public $_exchange_data_format;

    /** @var CExchangeTransportLayer */
    public $_current_echange;

    /** @var Chronometer */
    public $_current_chronometer;

    /**
     * Return all objects for exchange
     *
     * @param bool $only_active Seulement les sources actives
     *
     * @return array
     */
    static function getAllObjects($only_active = true)
    {
        $exchange_objects = [];
        $classes          = self::getExchangeClasses();
        unset($classes["syslog"]);
        unset($classes["smtp"]);
        unset($classes["pop"]);

        foreach ($classes as $_class) {
            /** @var CExchangeSource $object */
            $object = new $_class;

            if (!$object->_ref_module) {
                continue;
            }
            $where = [];
            if ($only_active) {
                $where["active"] = "= '1'";
            }
            $where["role"] = " = '" . CAppUI::conf("instance_role") . "'";

            $objects = $object->loadList($where);
            if (!$objects) {
                continue;
            }

            $exchange_objects[$_class] = $objects;
        }

        return $exchange_objects;
    }

    /**
     * Return the array of exchange classes
     *
     * @return array
     */
    static function getExchangeClasses()
    {
        self::addExternalSources();

        return self::$typeToClass;
    }

    /**
     * Extend classes
     *
     * @return void
     */
    static function addExternalSources()
    {
        if (CModule::getActive("hl7")) {
            self::$typeToClass["mllp"] = "CSourceMLLP";
        }

        if (CModule::getActive("dicom")) {
            self::$typeToClass["dicom"] = "CSourceDicom";
        }

        if (CModule::getActive("mssante")) {
            self::$typeToClass["mssante"] = "CSourceMSSante";
            self::$multi_instance[]       = "CSourceMSSante";
        }

        if (CModule::getActive('oxPyxvital')) {
            self::$typeToClass['pyxvital'] = 'CPyxvitalSOAPSource';
        }
    }

    /**
     * Get child exchanges
     *
     * @return string[] Data format classes collection
     * @throws Exception
     */
    static function getAll()
    {
        $sources = CApp::getChildClasses(CExchangeSource::class, true, true, true);

        return array_filter(
            $sources,
            function ($v) {
                $s = new $v();

                return ($s->_spec->key);
            }
        );
    }

    /**
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->_dispatcher = new EventDispatcher();
    }

    /**
     * Initialize & start call_traces chronometer
     *
     * @return void
     */
    public function startCallTrace(): void
    {
        $_key_trace = static::class;
        if (array_key_exists($_key_trace, static::$call_traces) === false) {
            self::$call_traces[$_key_trace] = new Chronometer();
        }
        $this->_current_chronometer = self::$call_traces[$_key_trace];
        self::$call_traces[$_key_trace]->start();
    }

    /**
     * Stop call_traces chronometer
     *
     * @return void
     */
    public function stopCallTrace(): void
    {
        if ($this->_current_chronometer->step !== 0) {
            $_key_trace = static::class;
            static::$call_traces[$_key_trace]->stop();
        }
    }

    /**
     * @inheritdoc
     */
    public function getProps()
    {
        $props                 = parent::getProps();
        $props["name"]         = "str notNull";
        $props["role"]         = "enum list|prod|qualif default|qualif notNull";
        $props["host"]         = "text notNull autocomplete";
        $props["user"]         = "str";
        $props["password"]     = "password randomizable show|0 loggable|0";
        $props["iv"]           = "str show|0 loggable|0";
        $props["type_echange"] = "str protected";
        $props["active"]       = "bool default|1 notNull";
        $props["loggable"]     = "bool default|0 notNull";
        $props["libelle"]      = "str";

        // client
        $client_type          = $this::CLIENT_MAPPING ? "enum list|" .
            implode('|', array_keys($this::CLIENT_MAPPING)) : 'str';
        $default_client       = $this::DEFAULT_CLIENT ? ' default|' . $this::DEFAULT_CLIENT : '';
        $props['client_name'] = $client_type . $default_client;

        $props["_incompatible"]  = "bool";
        $props["_reachable"]     = "enum list|0|1|2 default|0";
        $props["_response_time"] = "float";

        return $props;
    }

    /**
     * @return ClientInterface
     * @throws CMbException
     */
    public function getClient(): ClientInterface
    {
        throw new CMbException('CExchangeSource-client.none');
    }

    /**
     * @param ClientContext $context
     *
     * @return void
     */
    public function onBeforeRequest(ClientContext $context)
    {
        // do nothing
    }


    /**
     * @param ClientContext $context
     *
     * @return void
     */
    public function onAfterRequest(ClientContext $context)
    {
        // do nothing
    }

    /**
     * @param ClientContext $context
     *
     * @return void
     */
    public function onException(ClientContext $context)
    {
        // do nothing
    }

    /**
     * event start logs
     *
     * @param ClientContext $context
     *
     * @return void
     */
    public function startLog(ClientContext $context)
    {
        // do nothing
    }

    /**
     * event stop logs
     *
     * @param ClientContext $context
     *
     * @return void
     */
    public function stopLog(ClientContext $context)
    {
        // do nothing
    }

    /**
     * event exception logs
     *
     * @param ClientContext $context
     *
     * @return void
     */
    public function exceptionLog(ClientContext $context)
    {
        // do nothing
    }

    /**
     * @inheritDoc
     */
    function initialize()
    {
        parent::initialize();

        self::addExternalSources();
    }

    /**
     * @inheritdoc
     */
    public function check()
    {
        $source = self::get($this->name, null, true);

        if ($source->_id && ($source->_id != $this->_id)) {
            $this->active = 0;
        }

        return parent::check();
    }

    /**
     * Get the exchange source
     *
     * @param string            $name            Nom
     * @param string|array|null $type            Type de la source (FTP, SOAP, ...)
     * @param bool              $override        Charger les autres sources
     * @param string            $type_echange    Type de l'échange
     * @param bool              $only_active     Seulement la source active
     * @param bool              $put_all_sources Charger toutes les sources
     * @param boolean           $use_cache       Utiliser le cache
     *
     * @return CExchangeSource
     */
    static function get(
        $name,
        $type = null,
        $override = false,
        $type_echange = null,
        $only_active = true,
        $put_all_sources = false,
        $use_cache = true
    ) {
        $key = [
            $name,
            (is_array($type) ? implode('-', $type) : $type),
            $override,
            $type_echange,
            $only_active,
            $put_all_sources
        ];

        if ($use_cache) {
            // Todo: Take care of LSB here
            $cache = new Cache('CExchangeSource.get', $key, Cache::INNER);
            if ($cache->exists()) {
                return $cache->get();
            }
        }

        $exchange_classes = self::getExchangeClasses();

        // On passe juste un type de source
        $source_type = null;
        if ($type && !is_array($type)) {
            $source_type = $type;
            $type        = [$type];
        }

        if ($type) {
            $type             = array_fill_keys($type, $type);
            $exchange_classes = array_intersect_key($exchange_classes, $type);

            $wanted_type = join('|', array_keys($type));
        }

        foreach ($exchange_classes as $_class_key => $_class) {
            /** @var CExchangeSource $exchange_source */
            $exchange_source = new $_class();

            if ($only_active && !$put_all_sources) {
                $exchange_source->active = 1;
            }

            $exchange_source->name = $name;
            $exchange_source->loadMatchingObject();

            if ($exchange_source->_id) {
                if ($type) {
                    $exchange_source->_wanted_type = $wanted_type;
                }
                $exchange_source->_allowed_instances = self::getObjects($exchange_source, $exchange_classes);
                if ($exchange_source->role != CAppUI::conf("instance_role")) {
                    if (!$override) {
                        $incompatible_source                = new $exchange_source->_class();
                        $incompatible_source->name          = $exchange_source->name;
                        $incompatible_source->_incompatible = true;
                        if (PHP_SAPI !== 'cli') {
                            CAppUI::displayAjaxMsg("CExchangeSource-_incompatible", UI_MSG_ERROR);
                        }

                        return $incompatible_source;
                    }
                    $exchange_source->_incompatible = true;
                }

                return $use_cache ? $cache->put($exchange_source, false) : $exchange_source;
            }
        }

        $source = new CExchangeSource();
        if ($source_type && isset(self::$typeToClass[$source_type])) {
            $source = new self::$typeToClass[$source_type]();
        }

        $source->name         = $name;
        $source->type_echange = $type_echange;
        if ($type) {
            $source->_wanted_type = $wanted_type;
        }
        $source->_allowed_instances = self::getObjects($source, $exchange_classes);

        return $use_cache ? $cache->put($source, false) : $source;
    }

    /**
     * Return the exchange object
     *
     * @param CExchangeSource $exchange_source Name of the exchange source
     * @param array()         $type            Always null
     *
     * @return array|null
     */
    static function getObjects(CExchangeSource $exchange_source, $type = [])
    {
        if (!$type || (count($type) == 1 && $exchange_source->_class != "CExchangeSource")) {
            return null;
        }

        $name         = $exchange_source->name;
        $type_echange = $exchange_source->type_echange;

        $exchange_objects = [];
        foreach ($type as $_class) {
            /** @var CExchangeSource $object */
            $object = new $_class();

            if (!$object->_ref_module) {
                continue;
            }
            $object->name = $name;
            $object->loadMatchingObject();
            $object->type_echange = $type_echange;

            $exchange_objects[$_class] = $object;
        }

        return $exchange_objects;
    }

    /**
     * Get target object from source name
     *
     * @return CMbObject|null
     */
    public function getTargetObject(): ?CStoredObject
    {
        preg_match("/C[\w]+-\d+/", $this->name, $matches);

        $object_guid = CMbArray::get($matches, 0);

        if (!$object_guid) {
            return null;
        }

        $this->_target_object = CMbObject::loadFromGuid($object_guid);
        if ($this->_target_object === false) {
            return null;
        } else {
            return $this->_target_object;
        }
    }

    /**
     * Get password
     *
     * @param string $pwd      Password
     * @param string $iv_field Initialisation vector field
     *
     * @return null|string
     */
    public function getPassword($pwd = null, $iv_field = "iv")
    {
        if (is_null($pwd)) {
            $pwd = $this->password;
            if (!$this->password) {
                return "";
            }
        }

        try {
            $master_key = CApp::getAppMasterKey();

            $iv_to_use = $this->{$iv_field};

            if (!$iv_to_use) {
                $clear = $pwd;
                $this->store();

                return $clear;
            }

            return CMbSecurity::decrypt(CMbSecurity::AES, CMbSecurity::CTR, $master_key, $pwd, $iv_to_use);
        } catch (Throwable $e) {
            return $pwd;
        }
    }

    /**
     * @inheritdoc
     */
    public function store()
    {
        $this->completeField("name");

        if ($this->password === "") {
            $this->password = null;
        } else {
            if ($this->fieldModified("password") || !$this->_id) {
                $this->password = $this->encryptString();
            }
        }

        $this->updateEncryptedFields();

        return parent::store();
    }

    /**
     * Encrypt
     *
     * @param string $pwd      Password
     * @param string $iv_field Initialisation vector field
     *
     * @return null|string
     */
    protected function encryptString($pwd = null, $iv_field = "iv")
    {
        if (is_null($pwd)) {
            $pwd = $this->password;
        }

        try {
            $master_key = CApp::getAppMasterKey();

            $iv                = CMbSecurity::generateIV();
            $this->{$iv_field} = $iv;

            return CMbSecurity::encrypt(CMbSecurity::AES, CMbSecurity::CTR, $master_key, $pwd, $iv);
        } catch (Throwable $e) {
            return $pwd;
        }
    }

    /**
     * Encrypt fields
     *
     * @return void
     */
    protected function updateEncryptedFields()
    {
    }

    /**
     * Set data
     *
     * @param array|string        $data     Data
     * @param bool                $argsList Args list
     * @param CExchangeDataFormat $exchange Exchange
     *
     * @return void
     */
    public function setData($data, $argsList = false, CExchangeDataFormat $exchange = null)
    {
        $this->_args_list            = $argsList;
        $this->_data                 = $data;
        $this->_exchange_data_format = $exchange;
    }

    /**
     * Send
     */
    function send()
    {
    }

    /**
     * Get acknowledgment
     *
     * @return string|array
     */
    function getACQ()
    {
        return $this->_acquittement;
    }

    /**
     * Source is reachable ?
     *
     * @return bool reachable
     * @throws CMbException
     */
    public function isReachable(): bool
    {
        $this->_reachable = 0;
        if (!$this->active) {
            $this->_reachable = 1;
            $this->_message   = CAppUI::tr("CExchangeSource_no-active", $this->host);

            return false;
        }

        $client = $this->getClient();

        try {
            if (!$client->isReachableSource()) {
                return false;
            }

            if (!$client->isAuthentificate()) {
                return false;
            }
        }catch (CircuitBreakerException $e){
            return false;
        }

        $this->_reachable = 2;
        $this->_message   = CAppUI::tr("$this->_class-reachable-source", $this->host);

        return true;
    }
}
