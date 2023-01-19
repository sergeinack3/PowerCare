<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Profiles;

use DateTime;
use DateTimeZone;
use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbSecurity;
use Ox\Core\CMbString;
use Ox\Interop\Eai\CInteropNorm;
use Ox\Interop\Fhir\ClassMap\FHIRClassMap;
use Ox\Interop\Fhir\Contracts\Profiles\ProfileResource;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * FHIR interop norm class
 */
class CFHIR extends CInteropNorm implements IShortNameAutoloadable, ProfileResource
{
    use ProfileResourceTrait;

    /** @var string */
    public const BASE_PROFILE = "http://hl7.org/fhir/StructureDefinition/";
    /** @var string */
    public const DOMAIN_TYPE = 'FHIR';
    /** @var string */
    public const DOMAIN_NAME = 'FHIR';

    /** @var string|null  */
    public const RESOURCE_META_SOURCE = null;

    public const BLINK1_UNKNOW  = "fhir unknown";
    public const BLINK1_ERROR   = "fhir error";
    public const BLINK1_WARNING = "fhir warning";
    public const BLINK1_OK      = "fhir ok";

    /** @var string */
    protected const PREFIX_TRANSLATE_VERSION = 'FHIR';

    // todo remove this : Used in template inc_list_patients_pdqm && inc_list_result_mhd
    /**
     * @var string[] Relation map
     * @deprecated
     */
    public static $relation_map = [
        "first"    => "fast-backward",
        "previous" => "step-backward",
        //"self"     => "circle-o",
        "next"     => "step-forward",
        "last"     => "fast-forward",
    ];

    /**
     * @see parent::__construct
     */
    public function __construct()
    {
        parent::__construct();

        $this->name = static::DOMAIN_NAME;
        $this->type = static::DOMAIN_TYPE;

        $categories = [];
        $map        = new FHIRClassMap();
        $canonicals = $this->listResourceCanonicals();
        foreach ($canonicals as $canonical) {
            $resource = $map->resource->getResource($canonical);

            $categories[$canonical] = $resource->getInteractions();
        }

        $this->_categories = $categories;
    }

    /**
     * @inheritDoc
     */
    public static function listResourceCanonicals(): array
    {
        /** @var CFHIRResource[] $classes */
        $classes = CClassMap::getInstance()->getClassChildren(CFHIRResource::class, false, true);

        $resource_canonicals = [];
        foreach ($classes as $class) {
            if ($class::PROFILE_CLASS === static::class) {
                $resource_canonicals[] = $class::getCanonical();
            }
        }

        return $resource_canonicals;
    }

    /**
     * @inheritDoc
     */
    public static function getProfileName(): string
    {
        return static::DOMAIN_TYPE;
    }

    /**
     * @throws Exception
     */
    public function getCategoryVersions(): array
    {
        if ($this->_versions_category) {
            return $this->_versions_category;
        }

        $category_versions = [];
        $map               = (new FHIRClassMap());

        $profile_class  = get_class($this);
        $canonicals = $map->profile->getCanonicalsFromProfileClass($profile_class);
        foreach ($canonicals as $canonical) {
            $category_versions[$canonical] = $map->version->getResourceVersions($canonical);
        }

        return $this->_versions_category = $category_versions;
    }

    /**
     * @inheritDoc
     */
    public static function getCanonical(): string
    {
        return self::BASE_PROFILE;
    }

    /**
     * Makes a query string from an array
     *
     * @param array $query The query array
     *
     * @return string
     */
    public static function makeQueryString(?array $query): ?string
    {
        $parts = [];

        foreach ($query as $_key => $_values) {
            foreach ($_values as $_value) {
                $parts[] = urlencode($_key) . "=" . str_replace("+", "%2B", urlencode($_value));
            }
        }

        return implode("&", $parts);
    }

    /**
     * Créer un UUID
     *
     * @return string
     */
    public static function generateUUID(): string
    {
        return CMbSecurity::generateUUID();
    }

    /**
     * Retourne le datetime actuelle au format UTC
     *
     * @param String $date now
     * @param bool   $z    Z
     *
     * @return string
     * @throws Exception
     */
    public static function getTimeUtc(?string $date = "now", bool $z = true): string
    {
        if (!$date) {
            $date = 'now';
        }

        $timezone_local = new DateTimeZone(CAppUI::conf("timezone"));
        $timezone_utc   = new DateTimeZone("UTC");
        $date           = new DateTime($date, $timezone_local);
        $date->setTimezone($timezone_utc);

        return $z ? $date->format("Y-m-d\TH:i:sP") . "Z" : $date->format("Y-m-d\TH:i:sP");
    }

    /**
     * Load idex FHIR
     *
     * @param CDocumentItem $object   object
     * @param string        $group_id Group
     *
     * @return CIdSante400
     */
    public static function loadIdex(CDocumentItem $object, ?int $group_id = null): CIdSante400
    {
        return $object->_ref_fhir_idex = CIdSante400::getMatchFor($object, self::getObjectTag($group_id));
    }

    /**
     * Get object tag
     *
     * @param string $group_id Group
     *
     * @return string|null
     */
    public static function getObjectTag(?int $group_id = null): ?string
    {
        // Recherche de l'établissement
        $group = CGroups::get($group_id);
        if (!$group_id) {
            $group_id = $group->_id;
        }

        // Todo: Take care of LSB here
        $cache = new Cache('CFHIR.getObjectTag', [$group_id], Cache::INNER);

        if ($cache->exists()) {
            return $cache->get();
        }

        $tag = self::getDynamicTag();

        return $cache->put(str_replace('$g', $group_id, $tag));
    }

    /**
     * Get object dynamic tag
     *
     * @return string
     */
    public static function getDynamicTag(): ?string
    {
        return CAppUI::conf("fhir tag_default");
    }

    /**
     * Parses GET parameters, keeping repeating values inside an array
     *
     * @param string $string The query string to parse
     * @param bool   $raw    Get raw results, do not parse modifiers
     *
     * @return array
     */
    public static function parseQueryString(?string $string = null, ?bool $raw = false): array
    {
        if (!$string) {
            $string = $_SERVER['QUERY_STRING'];
        }

        $query  = $string ? explode('&', $string) : [];
        $params = [];

        foreach ($query as $param) {
            if (strpos($param, '=')) {
                [$name, $value] = explode('=', $param, 2);
            } else {
                $name  = $param;
                $value = null;
            }

            // Custom field name to stop query processing
            if ($name === "_fhir_stop") {
                break;
            }

            if ($raw) {
                $params[urldecode($name)][] = urldecode($value);
            } else {
                $field = CFHIR::parseCondition(urldecode($name));

                $params[$field[0]][] = [$field[1], urldecode($value)];
            }
        }

        return $params;
    }

    /**
     *
     *
     * @param $name
     *
     * @return array
     */
    public static function parseCondition(string $name): array
    {
        if (CMbString::endsWith($name, ":exact")) {
            $pos = strrpos($name, ":exact");

            return [substr($name, 0, $pos), "exact"];
        }

        if (CMbString::endsWith($name, ":contains")) {
            $pos = strrpos($name, ":contains");

            return [substr($name, 0, $pos), "contains"];
        }

        if (CMbString::endsWith($name, ":not")) {
            $pos = strrpos($name, ":not");

            return [substr($name, 0, $pos), "!="];
        }

        return [$name, "="];
    }

    /**
     * @see parent::getEvenements
     */
    public function getEvenements(): ?array
    {
        return $this->getEvenementsFromCategories();
    }
}
