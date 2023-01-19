<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CMbException;
use ReflectionClass;

/**
 * Class CInteropNorm
 * Interoperability Norme
 */
abstract class CInteropNorm implements IShortNameAutoloadable
{
    /** @var string[] */
    public const DOMAINS_AVAILABLE = [
        // IHE
        self::DOMAIN_ITI,
        self::DOMAIN_PaLM,
        self::DOMAIN_PAM,
        self::DOMAIN_PCD,
        self::DOMAIN_RAD,

        self::DOMAIN_SYSLOG,
        self::DOMAIN_CDA,
        self::DOMAIN_TLSi,
        self::DOMAIN_HL7,
    ];

    /** @var string Pathology and Laboratory Medicine (PaLM) */
    public const DOMAIN_PaLM = 'PaLM';
    /** @var string Patient Administration Management (PAM) */
    public const DOMAIN_PAM = 'PAM';
    /** @var string IT Infrastructure (ITI) */
    public const DOMAIN_ITI = 'ITI';
    /** @var string Patient Care Device (PCD) */
    public const DOMAIN_PCD = 'PCD';
    /** @var string Radiology (RAD) */
    public const DOMAIN_RAD = 'RAD';
    /** @var string Cardiology (CARD) */
    public const DOMAIN_CARD = 'CARD';

    /** @var string */
    public const DOMAIN_SYSLOG = 'Syslog';
    /** @var string */
    public const DOMAIN_CDA = 'CDA';
    /** @var string */
    public const DOMAIN_TLSi = 'TLSi';
    /** @var string HL7 */
    public const DOMAIN_HL7 = 'HL7';

    /** @var string */
    protected const PREFIX_TRANSLATE_VERSION = '';
    /** @var array */
    public static $object_handlers = [];
    /** @var array */
    public static $versions = [];
    /** @var array */
    public static $evenements = [];
    /** @var string */
    public $name;
    /** @var string */
    public $domain;
    /** @var string */
    public $type;
    /** @var array */
    public $_categories = [];

    /** @var array */
    public $_versions_category = [];

    /** @var string */
    public $_prefix_translate_version;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->_prefix_translate_version = $this::PREFIX_TRANSLATE_VERSION;
        $this->_versions_category        = $this->getCategoryVersions();
    }

    /**
     * @return array
     */
    public function getCategoryVersions(): array
    {
        return $this->_versions_category = [];
    }

    /**
     * Retrieve handlers list
     *
     * @return array Handlers list
     */
    public static function getObjectHandlers(): ?array
    {
        return self::$object_handlers;
    }

    /**
     * Retrieve transaction name
     *
     * @param string $code Event code
     *
     * @return string Transaction name
     */
    public static function getTransaction(string $code): ?string
    {
        return null;
    }

    /**
     * Return data format object
     *
     * @param CExchangeDataFormat $exchange Instance of exchange
     *
     * @return object An instance of data format
     * @throws CMbException
     *
     */
    public static function getEvent(CExchangeDataFormat $exchange)
    {
    }

    /**
     * Get tag
     *
     * @param int $group_id group id
     *
     * @return string|null
     */
    public static function getTag(?int $group_id = null): ?string
    {
    }

    /**
     * Get objects
     *
     * @return array CInteropNorm collection
     */
    public function getObjects(): ?array
    {
        $standards = [];
        foreach (CApp::getChildClasses(static::class, false, true) as $_interop_norm) {
            /* We check if the class is instantiable */
            $reflection = new ReflectionClass($_interop_norm);
            if (!$reflection->isInstantiable()) {
                continue;
            }

            /** @var CInteropNorm $norm */
            $norm = new $_interop_norm();
            $norm->getNorm($standards);
        }

        return $standards;
    }

    /**
     * Get norm
     *
     * @param array        $standards
     *
     * @return void
     */
    public function getNorm(array &$standards): void
    {
        if (!$this->name || !$this->type) {
            return;
        }

        $domain_name = $this->getDomain();
        $type        = $this->getType();

        $standards[$this->name][$domain_name][$type] = $this->getEvents();
    }

    /**
     * Retrieve profil
     *
     * @return string
     */
    public function getDomain(): ?string
    {
        return $this->domain ? $this->domain : "none";
    }

    /**
     * Retrieve type
     *
     * @return string
     */
    public function getType(): ?string
    {
        return $this->type ? $this->type : "none";
    }

    /**
     * Retrieve events
     *
     * @return array Events list
     */
    public function getEvents(): ?array
    {
        $events = $this->getEvenements();

        $temp = [];
        foreach ($this->_categories as $_transaction => $_events) {
            foreach ($_events as $_event_name) {
                if (array_key_exists($_event_name, $events)) {
                    $temp[$_transaction][$_event_name] = $events[$_event_name];
                }
            }
        }

        if (empty($temp)) {
            $temp["none"] = $events;
        }

        return $temp;
    }

    /**
     * Retrieve versions list of data format
     *
     * @return array Versions list
     */
    public function getVersions(): ?array
    {
        return self::$versions;
    }

    /**
     * Retrieve document elements
     *
     * @return array
     */
    public function getDocumentElements(): ?array
    {
        return [];
    }

    /**
     * @param CMessageSupported $message_supported
     * @param string            $event_name
     * @param string            $category
     *
     * @return bool
     */
    public function isMessageSupported(CMessageSupported $message_supported, string $event_name, string $category): bool
    {
        return $this->isEventSupported($event_name)
            && $this->isMessageAttributeSupported($message_supported, $event_name)
            && $this->isTransactionSupported($message_supported, $category);
    }

    /**
     * @param string $event_name
     *
     * @return bool
     */
    protected function isEventSupported(string $event_name): bool
    {
        return array_key_exists($event_name, $this->getEvenements());
    }

    /**
     * Retrieve events list of data format
     *
     * @return array Events list
     */
    public function getEvenements(): ?array
    {
        return self::$evenements;
    }

    /**
     * @param CMessageSupported $message_supported
     * @param string            $event_name
     *
     * @return bool
     */
    protected function isMessageAttributeSupported(CMessageSupported $message_supported, string $event_name): bool
    {
        return $message_supported->message === $this->getEvenements()[$event_name];
    }

    /**
     * @param CMessageSupported $message_supported
     * @param string            $category
     *
     * @return bool
     */
    protected function isTransactionSupported(CMessageSupported $message_supported, string $category): bool
    {
        return ($message_supported->transaction === null) || ($message_supported->transaction === "") || $message_supported->transaction === $category;
    }
}
