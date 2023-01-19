<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFileTraceability;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourcePOP;

/**
 * Class CInteropReceiver
 * Interoperability Receiver
 */
class CInteropReceiver extends CInteropActor
{
    /** @var array Sources supportées par un destinataire */
    public static $supported_sources = [];

    public $OID = null;

    /** @var boolean */
    public $synchronous;
    /** @var boolean */
    public $monitor_sources;
    /** @var boolean */
    public $use_specific_handler;

    // Form fields
    public $_type_echange;
    public $_exchanges_sources_save = 0;

    /** @var CFileTraceability */
    public $_ref_file_traceability;

    /**
     * count objects
     *
     * @param int $curent_group Current group
     *
     * @return array
     * @throws Exception
     */
    public static function countObjects(int $curent_group = null): array
    {
        if($curent_group == null ){
            $curent_group = CGroups::loadCurrent()->_id;
        }

        $objects = [];
        foreach (self::getChildReceivers() as $_interop_receiver) {
            /** @var CInteropReceiver $receiver */
            $receiver = new $_interop_receiver();

            // No show ?
            if (isset($receiver->_spec->_show) && ($receiver->_spec->_show === false)) {
                continue;
            }

            $where = [];

            if ($curent_group) {
                $where["group_id"] = " = '$curent_group' or group_id is null";
            }

            $objects[$_interop_receiver]['total']       = $receiver->countList($where);
            $where["actif"]                             = " = '1'";
            $where["role"]                              = " = '" . CAppUI::conf('instance_role') . "'";
            $objects[$_interop_receiver]['total_actif'] = $receiver->countList($where);
        }

        return $objects;
    }

    /**
     * Get child receivers
     *
     * @return array CInteropReceiver collection
     * @throws Exception
     */
    static function getChildReceivers(bool $only_true_actor = false)
    {
        $actors = CApp::getChildClasses(CInteropReceiver::class, true, true);

        if ($only_true_actor) {
            foreach ($actors as $key => $actor) {
                if (isset($actor->_spec->_show) && ($actor->_spec->_show === false)) {
                    unset($key);
                }
            }
        }

        return $actors;
    }

    /**
     * Get objects
     *
     * @param bool $only_active     Active
     * @param int  $group_id        Group ID
     * @param bool $put_all_sources Put all sources
     *
     * @return array CInteropReceiver collection
     * @throws Exception
     */
    public static function getObjects(
        bool $only_active = false,
        int $group_id = null,
        bool $put_all_sources = false
    ): array {
        $objects = [];
        foreach (self::getChildReceivers() as $_interop_receiver) {
            /** @var CInteropReceiver $receiver */
            $receiver = new $_interop_receiver();

            // No show ?
            if (isset($receiver->_spec->_show) && ($receiver->_spec->_show === false)) {
                continue;
            }

            $where = [];
            if ($only_active) {
                $where["actif"] = " = '1'";
            }

            if ($group_id) {
                $where["group_id"] = " = '$group_id'";
            }

            $order = "group_id ASC, libelle ASC, nom ASC";

            $objects[$_interop_receiver] = $receiver->loadList($where, $order);
            if (!is_array($objects[$_interop_receiver])) {
                continue;
            }
            foreach ($objects[$_interop_receiver] as $_receiver) {
                /** @var CInteropReceiver $_receiver */
                $_receiver->loadRefGroup();
                $_receiver->isReachable($put_all_sources);
            }
        }

        return $objects;
    }

    /**
     * Get the receivers by oid
     *
     * @param String $oid oid
     *
     * @return CInteropReceiver[]
     * @throws Exception
     */
    static function getObjectsByOID($oid, $receivers = [])
    {
        $objects = [];
        if (!$oid) {
            return $objects;
        }

        $receivers = $receivers ?: self::getChildReceivers();

        foreach ($receivers as $_interop_receiver) {
            /** @var CInteropReceiver $receiver */
            $receiver        = new $_interop_receiver();
            $receiver->OID   = $oid;
            $receiver->role  = CAppUI::conf("instance_role");
            $receiver->actif = "1";
            foreach ($receiver->loadMatchingList() as $_receiver) {
                $objects[$_receiver->_guid] = $_receiver;
            }
        }

        return $objects;
    }

    /**
     * Get the receivers by class name
     *
     * @param string $class
     * @param bool   $only_active
     * @param bool   $only_instance
     * @param int    $current_group
     *
     * @return array
     * @throws Exception
     */
    public static function getObjectsByClass(
        string $class,
        bool $only_active = true,
        bool $only_instance = true,
        int $current_group = null
    ): array {
        /** @var CInteropReceiver $receiver */
        $receiver = new $class();
        $objects  = [];
        
        // No show ?
        if (isset($receiver->_spec->_show) && ($receiver->_spec->_show === false)) {
            return $objects;
        }

        $where = [];
        if ($only_active) {
            $where["actif"] = " = '1'";
        }

        if ($only_instance) {
            $where["role"] = " = '" . CAppUI::conf("instance_role") . "'";
        }
        
        if ($current_group) {
            $where["group_id"] = " = '$current_group'";
        }

        $order = "group_id ASC, libelle ASC, nom ASC";

        $objects = $receiver->loadList($where, $order);
        if (!is_array($objects)) {
            return $objects;
        }
        foreach ($objects as $_receiver) {
            /** @var CInteropReceiver $_receiver */
            $_receiver->loadRefGroup();
            $_receiver->loadRefsExchangesSources();
            $_receiver->lastMessage();
            $_receiver->isINSCompatible();
        }

        return $objects;
    }

    /**
     * Load exchanges sources
     *
     * @param bool $put_all_sources Put all sources
     *
     * @return CExchangeSource[]
     * @throws Exception
     */
    public function loadRefsExchangesSources(bool $put_all_sources = false): array
    {
        if (!$this->_ref_msg_supported_family) {
            $this->getMessagesSupportedByFamily();
        }

        $this->_ref_exchanges_sources = [];
        foreach ($this->_ref_msg_supported_family as $_evenement) {
            $source = CExchangeSource::get(
                "$this->_guid-$_evenement",
                static::$supported_sources,
                true,
                $this->_type_echange,
                true,
                $put_all_sources
            );
            if ($source instanceof CSourcePOP) {
                $source->loadRefMetaObject();
            }

            $this->_ref_exchanges_sources[$_evenement] = $source;
        }

        return $this->_ref_exchanges_sources;
    }

    /**
     * Get the receivers by oid
     *
     * @param string $type     Receiver type
     * @param int    $group_id group id
     *
     * @return self[]
     * @throws Exception
     */
    static function getObjectsByType($type, $group_id = null)
    {
        $objects = [];
        if (!$type) {
            return $objects;
        }

        $receiver           = new static();
        $receiver->group_id = $group_id ? $group_id : CGroups::loadCurrent()->_id;
        $receiver->type     = $type;
        $receiver->role     = CAppUI::conf("instance_role");
        $receiver->actif    = "1";
        foreach ($receiver->loadMatchingList() as $_receiver) {
            $objects[$_receiver->_guid] = $_receiver;
        }

        return $objects;
    }

    /**
     * Initialize object specification
     *
     * @return CMbObjectSpec the spec
     */
    function getSpec()
    {
        $spec = parent::getSpec();

        $spec->messages = [];

        return $spec;
    }

    /**
     * Get properties specifications as strings
     *
     * @return array
     */
    function getProps()
    {
        $props = parent::getProps();

        $props["OID"]                  = "str";
        $props["synchronous"]          = "bool default|1 notNull";
        $props["monitor_sources"]      = "bool default|1 notNull";
        $props["use_specific_handler"] = "bool default|0 notNull";

        $props["_exchanges_sources_save"] = "num";

        return $props;
    }

    /**
     * Update the form (derived) fields plain fields
     *
     * @return void
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $this->_parent_class = "CInteropReceiver";

        if (!$this->libelle) {
            //$this->libelle = $this->nom;
        }
    }

    /**
     * Get object handler
     *
     * @param CEAIObjectHandler $objectHandler Object handler
     *
     * @return mixed
     */
    function getFormatObjectHandler(CEAIObjectHandler $objectHandler)
    {
        return [];
    }

    /**
     * Retourne les messages supportés actifs pour un destinataire trié par catégorie
     *
     * @param CInteropReceiver $receiver receiver
     *
     * @return CMessageSupported[]
     */
    function getMessagesSupportedSort(CInteropReceiver $receiver)
    {
        $exchanges = $receiver->makeBackSpec("echanges");
        $receiver->_backSpecs["echanges"];

        /** @var CExchangeDataFormat $data_format */
        $data_format             = new $exchanges->class;
        $only_messages_supported = $data_format->getMessagesSupported($receiver->_guid, false, null, true);

        $messages_supported = [];
        foreach ($only_messages_supported as $_family => $_messages_supported) {
            $family = new $_family;
            $events = $family->getEvenements();

            $categories = [];
            if (isset($family->_categories) && !empty($family->_categories)) {
                foreach ($family->_categories as $_category => $events_name) {
                    foreach ($events_name as $_event_name) {
                        foreach ($_messages_supported as $_message_supported) {
                            if (!array_key_exists($_event_name, $events)) {
                                continue;
                            }

                            if ($_message_supported->message != $events[$_event_name]) {
                                continue;
                            }

                            $categories[$_category][] = $_message_supported;
                        }
                    }
                }
            } else {
                $categories["none"] = $_messages_supported;
            }
            // On reformate un peu le tableau des catégories
            $family->_categories = $categories;

            $domain = $family->domain ? $family->domain : $family->name;

            $messages_supported[$domain][] = $family;
        }

        return $messages_supported;
    }

    /**
     * @inheritDoc
     */
    public function isINSCompatible(): bool
    {
        return $this->_is_ins_compatible;
    }
}
