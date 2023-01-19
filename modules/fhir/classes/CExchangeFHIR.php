<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir;

use Exception;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Core\CMbFieldSpecFact;
use Ox\Interop\Eai\CExchangeBinary;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CInteropNorm;
use Ox\Interop\Eai\CMessageSupported;
use Ox\Interop\Fhir\Actors\CFHIRConfig;
use Ox\Interop\Fhir\ClassMap\FHIRClassMap;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectInterface;
use Ox\Interop\Fhir\Contracts\Profiles\ProfileResource;
use Ox\Interop\Fhir\Contracts\Resources\ResourceInterface;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Ihe\CIHEFHIR;
use Ox\Mediboard\System\CContentAny;

/**
 * Class CExchangeFHIR
 * Exchange FHIR
 */
class CExchangeFHIR extends CExchangeBinary
{
    /** @var string[] */
    public const DELEGATED_OBJECTS = [
        self::DELEGATED_OBJECT_HANDLE,
        self::DELEGATED_OBJECT_MAPPER,
        self::DELEGATED_OBJECT_SEARCHER,
    ];

    /** @var string */
    public const DELEGATED_OBJECT_MAPPER = 'delegated_mapper';
    /** @var string */
    public const DELEGATED_OBJECT_SEARCHER = 'delegated_searcher';
    /** @var string */
    public const DELEGATED_OBJECT_HANDLE = 'delegated_handle';

    /** @var array */
    static $messages = [
        "PDQm"          => "CPDQm",
        "PIXm"          => "CPIXm",
        "MHD"           => "CMHD",
        "FHIR"          => "CFHIR",
        "InteropSante"  => "CFHIRInteropSante",
        "ANS"           => "CFHIRANS",
        "AnnuaireSante" => "CFHIRAnnuaireSante",
    ];

    /** @var string */
    public $exchange_fhir_id;

    /** @var string */
    public $format;

    public $_exchange_fhir;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->loggable = false;
        $spec->table    = 'exchange_fhir';
        $spec->key      = 'exchange_fhir_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                            = parent::getProps();
        $props["group_id"]                .= " back|echanges_fhir";
        $props["sender_class"]            = "enum list|CSenderHTTP show|0";
        $props["sender_id"]               .= " back|expediteur_fhir";
        $props["message_content_id"]      = "ref class|CContentAny show|0 cascade back|messages_fhir";
        $props["acquittement_content_id"] = "ref class|CContentAny show|0 cascade back|acquittements_fhir";
        $props["receiver_id"]             = "ref class|CReceiverFHIR autocomplete|nom back|echanges";
        $props["object_class"]            = "str class show|0";
        $props["object_id"]               .= " back|exchanges_fhir";
        $props["format"]                  = "str";

        $props["_message"]      = "str";
        $props["_acquittement"] = "str";

        return $props;
    }

    /**
     * Handle exchange
     *
     * @return null|string|void
     */
    function handle()
    {
    }

    /**
     * Check if data is well formed
     *
     * @param string        $data  Data
     * @param CInteropActor $actor Actor
     *
     * @return bool
     */
    function isWellFormed($data, CInteropActor $actor = null)
    {
        return false;
    }

    /**
     * Check if data is understood
     *
     * @param string        $data  Data
     * @param CInteropActor $actor Actor
     *
     * @return bool
     */
    public function understand(string $data, CInteropActor $actor = null): bool
    {
        /** @todo à faire */
        return false;
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
     * @see parent::loadContent()
     */
    function loadContent()
    {
        $this->_ref_message_content = $this->loadFwdRef("message_content_id", true);
        $this->_message             = $this->_ref_message_content->content;

        $this->_ref_acquittement_content = $this->loadFwdRef("acquittement_content_id", true);
        $this->_acquittement             = $this->_ref_acquittement_content->content;
    }

    /**
     * @see parent::guessDataType()
     */
    function guessDataType()
    {
        $data_types = [
            "<?xml" => "xml",
            "{"     => "text",
        ];

        foreach ($data_types as $check => $spec) {
            if (strpos($this->_message, $check) === 0) {
                $this->_props["_message"] = $spec;
                $this->_specs["_message"] = CMbFieldSpecFact::getSpec($this, "_message", $spec);
            }

            if (strpos($this->_acquittement, $check) === 0) {
                $this->_props["_acquittement"] = $spec;
                $this->_specs["_acquittement"] = CMbFieldSpecFact::getSpec($this, "_acquittement", $spec);
            }
        }
    }

    /**
     * @inheritdoc
     */
    function updatePlainFields()
    {
        if ($this->_message !== null) {
            /** @var CContentAny $content */
            $content          = $this->loadFwdRef("message_content_id", true);
            $content->content = $this->_message;
            if ($msg = $content->store()) {
                return $msg;
            }
            if (!$this->message_content_id) {
                $this->message_content_id = $content->_id;
            }
        }

        if ($this->_acquittement !== null) {
            /** @var CContentAny $content */
            $content          = $this->loadFwdRef("acquittement_content_id", true);
            $content->content = (string)$this->_acquittement;
            if ($msg = $content->store()) {
                return $msg;
            }
            if (!$this->acquittement_content_id) {
                $this->acquittement_content_id = $content->_id;
            }
        }
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
        $map = new FHIRClassMap();

        // find messages
        [$object_class, $object_id] = explode("-", $actor_guid);
        $family = [];
        foreach ($this->getFamily() as $_message => $_root_class) {
            /** @var CInteropNorm $root */
            $root = new $_root_class();

            $canonicals = array_keys($root->_categories);
            foreach ($canonicals as $canonical) {
                $resource = $map->resource->getResource($canonical);
                $version  = null;

                $resources = [];
                if ($root instanceof CIHEFHIR) {
                    $resources = $map->resource->listResources($resource::RESOURCE_TYPE);
                }

                $evenements = $root->getEvenements();
                foreach ($resource->getInteractions() as $_evt) {
                    $_evt_class = $evenements[$_evt] ?? null;
                    if (($evenement && ($evenement != $_evt)) || $_evt_class === null) {
                        continue;
                    }

                    $message_supported = new CMessageSupported();
                    $ds                = $message_supported->getDS();

                    $where = [
                        "object_class" => $ds->prepare('= ?', $object_class),
                        "object_id"    => $ds->prepare('= ?', $object_id),
                        "message"      => $ds->prepare('= ?', $_evt_class),
                        "profil"       => $ds->prepare('= ?', $_root_class),
                    ];

                    if ($root instanceof CIHEFHIR) {
                        $resource_canonicals  = array_map(
                            function (CFHIRResource $resource) {
                                return $resource->getCanonical();
                            },
                            $resources
                        );
                        $where['transaction'] = $ds->prepareIn($resource_canonicals);
                    } else {
                        $where['transaction'] = $ds->prepare('= ?', $resource->getCanonical());
                    }

                    if ($show_actif) {
                        $where['active'] = $ds->prepare('= ?', $show_actif);
                    }
                    $message_supported->loadObject($where);
                    if (!$message_supported->_id && !$all) {
                        continue;
                    }

                    if (!$message_supported->_id) {
                        $message_supported->object_class = $object_class;
                        $message_supported->object_id    = $object_id;
                        $message_supported->message      = $_evt_class;
                        $message_supported->profil       = $_root_class;
                        $message_supported->transaction  = $canonical;
                    }

                    if ($version === null && $message_supported->version) {
                        $version = $message_supported->version;
                    }

                    $message_supported->loadEventByName();
                    $message_supported->_data_format = $this;

                    $this->_messages_supported_class[] = $message_supported->message;

                    $family[$_root_class][] = $message_supported;
                }

                // propagation version on each messages
                /** @var CMessageSupported $message */
                foreach (CMbArray::get($family, $_root_class, []) as $message) {
                    if ($message->_id) {
                        continue;
                    }

                    if (!$version) {
                        $supported_versions = $map->version->getSupportedFhirVersions($message->transaction);
                        $version            = reset($supported_versions);
                    }

                    $message->version = $version;
                }
            }
        }

        return $family;
    }

    /**
     * Get exchange FHIR families
     *
     * @return array Families
     */
    function getFamily()
    {
        /** @var ProfileResource[] $profiles */
        $class_map = CClassMap::getInstance();
        $profiles  = $class_map->getClassChildren(ProfileResource::class, false, true);

        $family = [];
        foreach ($profiles as $profile) {
            $family[$profile::getProfileName()] = $class_map->getShortName($profile);
        }

        return $family;
    }

    /**
     * @param string $actor_guid
     *
     * @return CFHIRConfig
     * @throws Exception
     */
    public function getConfigs($actor_guid)
    {
        [$sender_class, $sender_id] = explode("-", $actor_guid);

        $sender_fhir_config               = new CFHIRConfig();
        $sender_fhir_config->sender_class = $sender_class;
        $sender_fhir_config->sender_id    = $sender_id;
        $sender_fhir_config->loadMatchingObject();

        return $this->_configs_format = $sender_fhir_config;
    }

    /**
     * @param string      $canonical
     * @param string|null $version
     *
     * @return ResourceInterface
     */
    public static function getResourceFromCanonical(string $canonical, ?string $version = null): ResourceInterface
    {
        return (new FHIRClassMap())->resource->getResource($canonical);
    }

    /**
     * @param CMessageSupported $message_supported
     * @param string            $type
     *
     * @return DelegatedObjectInterface
     * @throws Exception
     */
    public static function getDelegatedObject(CMessageSupported $message_supported, string $type): array
    {
        $map = new FHIRClassMap();
        try {
            if (!$message_supported->transaction || !$map->resource->getResource($message_supported->transaction)) {
                return [];
            }
        } catch (CFHIRException $e) {
            return [];
        }

        switch ($type) {
            case self::DELEGATED_OBJECT_HANDLE:
                $delegated_objects = $map->delegated->listDelegatedHandle($message_supported->transaction);
                break;
            case self::DELEGATED_OBJECT_SEARCHER:
                $delegated_objects = $map->delegated->listDelegatedSearcher($message_supported->transaction);
                break;
            case self::DELEGATED_OBJECT_MAPPER:
                $delegated_objects = $map->delegated->listDelegatedMapper($message_supported->transaction);
                break;
            default:
                $delegated_objects = [];
        }

        return array_filter(
            $delegated_objects,
            function (DelegatedObjectInterface $delegated) use ($message_supported) {
                $class    = CClassMap::getInstance()->getAliasByShortName($message_supported->profil);
                $profiles = $delegated->onlyProfiles();

                return $profiles === null || !empty($profiles) || in_array($class, $profiles);
            }
        );
    }
}
