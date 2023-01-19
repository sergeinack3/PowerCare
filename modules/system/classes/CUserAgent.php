<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CMbString;
use Ox\Core\CValue;
use Ox\Core\Logger\LoggerLevels;
use OxBrowscap\BrowscapFactory;

/**
 * User agent
 */
class CUserAgent extends CMbObject
{
    public const SUPPORTED_BROWSERS = [
        "Firefox" => ['78.0', '95.0'],
        "Chrome"  => ['88.0', '96.0'],
        "Edge"    => ['86.0', "96.0"],
    ];

    public const BROWSER_NAMES = [
        "Firefox",
        "Chrome",
        "Edge",
        "IE",
        "Opera",
        "Opera Mini",
        "Safari",
        "Android",
        "Konqueror",
        "SeaMonkey",
        "Iceweasel",
    ];

    public const BROWSER_CODE_NAMES = [
        "Firefox" => "firefox",
        "Chrome"  => "chrome",
        "Edge"    => "edge",
        "IE"      => "msie",
        "Opera"   => "opera",
        "Safari"  => "safari",
    ];

    public const PLATFORM_NAMES = [
        "WinNT",
        "Win2000",
        "WinXP",
        "WinVista",
        "Win7",
        "Win8",
        "Win8.1",
        "Linux",
        "MacOSX",
        "iOS",
        "Android",
        "ChromeOS",
        "unknown",
    ];

    public const DEVICE_NAMES = [
        "PC",
        "Android",
        "iPhone",
        "iPad",
        "Nexus 4",
        "Blackberry",
        "general Mobile Device",
        "unknown",
    ];

    public const DEVICE_MAKERS = [
        "Various",
        "Apple",
        "Samsung",
        "HTC",
        "LG",
        "SonyEricsson",
        "RIM",
        "Google",
        "Microsoft",
        "unknown",
    ];

    public $user_agent_id;

    public $user_agent_string;

    public $browser_name;
    public $browser_version;

    public $platform_name;
    public $platform_version;

    public $device_name;
    public $device_maker;
    public $device_type; // Mobile Device, Mobile Phone, Desktop, Tablet
    public $pointing_method; // mouse, unknown, touchscreen

    public $_obsolete;
    public $_too_recent;
    public $_badly_detected;

    /** @var  CUserAuthentication[] */
    public $_ref_user_authentications;

    /**
     * Executed prior to any serialization of the object
     *
     * @return array Array of field names to be serialized
     */
    public function __sleep(): array
    {
        return [
            $this->_spec->key,
            "user_agent_string",

            "browser_name",
            "browser_version",

            "platform_name",
            "platform_version",

            "device_name",
            "device_type",
            "device_maker",
            "pointing_method",
        ];
    }

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = "user_agent";
        $spec->key   = "user_agent_id";

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props                      = parent::getProps();
        $props["user_agent_string"] = "str notNull";

        $props["browser_name"]    = "str";
        $props["browser_version"] = "str";

        $props["platform_name"]    = "str";
        $props["platform_version"] = "str";

        $props["device_name"]     = "str";
        $props["device_type"]     = "enum notNull list|desktop|mobile|tablet|unknown default|unknown";
        $props["device_maker"]    = "str";
        $props["pointing_method"] = "enum notNull list|mouse|touchscreen|unknown default|unknown";

        $props["_obsolete"] = "bool";

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        $this->_view = "$this->browser_name $this->browser_version / $this->platform_name $this->platform_version";

        $this->isObsolete();
        $this->isTooRecent();
    }

    /**
     * Tells if the browser is obsolete
     *
     * @return bool
     */
    public function isObsolete(): ?bool
    {
        $this->isBadlyDetected();

        if (!$this->_badly_detected && $this->isSupportedBrowser()) {
            $min_version = self::SUPPORTED_BROWSERS[$this->browser_name][0];

            $this->_obsolete = $min_version > $this->browser_version;
        }

        return $this->_obsolete;
    }

    public function isTooRecent(): ?bool
    {
        $this->isBadlyDetected();

        if (!$this->_badly_detected && $this->isSupportedBrowser()) {
            $max_version = self::SUPPORTED_BROWSERS[$this->browser_name][1];

            $this->_too_recent = $max_version < $this->browser_version;
        }

        return $this->_too_recent;
    }

    private function isSupportedBrowser(): bool
    {
        return isset(self::SUPPORTED_BROWSERS[$this->browser_name]);
    }

    private function isBadlyDetected(): bool
    {
        return $this->_badly_detected = $this->browser_version === "0.0";
    }

    /**
     * Get code name
     *
     * @return string
     */
    public function getCodeName(): ?string
    {
        return self::BROWSER_CODE_NAMES[$this->browser_name] ?? CMbString::lower($this->browser_name);
    }

    /**
     * Get major version number
     *
     * @return int
     */
    public function getMajorVersion(): int
    {
        return (int)$this->browser_version;
    }

    /**
     * @return CUserAuthentication[]
     */
    public function loadRefUserAuthentication(): ?array
    {
        return $this->_ref_user_authentications = $this->loadBackRefs("user_authentications");
    }

    /**
     * User agent detection
     *
     * @param string|null $ua_string UA string
     *
     *
     * @throws Exception
     */
    public static function detect(?string $ua_string = null)
    {
        try {
            $browscap = BrowscapFactory::create();
            $browser  = $browscap->getBrowser($ua_string);
        } catch (Exception $e) {
            CApp::log($e->getMessage(), null, LoggerLevels::LEVEL_ERROR);
            $browser = false;
        }

        return $browser;
    }


    /**
     * Create a User agent entry from a US string
     *
     * @param bool $store Store the new UA object
     *
     * @return self
     */
    public static function create(bool $store = true): self
    {
        $user_agent = new self();

        $ua_string = CValue::read($_SERVER, "HTTP_USER_AGENT");
        if (!$ua_string) {
            return $user_agent;
        }

        if (!$user_agent->isInstalled()) {
            return $user_agent;
        }

        $user_agent->user_agent_string = substr($ua_string, 0, 255);

        if (!$user_agent->loadMatchingObjectEsc()) {
            if ($browser = self::detect($ua_string)) {
                $user_agent->browser_name    = $browser->browser;
                $user_agent->browser_version = $browser->version;

                $user_agent->platform_name    = $browser->platform;
                $user_agent->platform_version = $browser->platform_version;

                $user_agent->device_name     = $browser->device_name;
                $user_agent->device_maker    = $browser->device_maker;
                $user_agent->pointing_method = $browser->device_pointing_method;

                $user_agent->device_type = self::mapDeviceType($browser->device_type);
            }

            if ($store) {
                $user_agent->store();
            }
        }

        return $user_agent;
    }

    public static function mapDeviceType($device_type): string
    {
        $device_map = [
            "Mobile Device" => "mobile",
            "Mobile Phone"  => "mobile",
            "Desktop"       => "desktop",
            "Tablet"        => "tablet",
        ];

        return CValue::read($device_map, $device_type, "unknown");
    }
}
