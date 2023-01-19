<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Utilities\SearchParameters;

use Ox\Core\Api\Request\RequestFilter;
use Ox\Interop\Fhir\Exception\CFHIRException;

abstract class AbstractSearchParameter implements ISearchParameter
{
    // modifiers
    /** @var string */
    public const MODIFIER_CONTAINS = 'contains';

    /** @var string */
    public const MODIFIER_EXACT = 'exact';

    /** @var string  */
    public const MODIFIER_MISSING = 'missing';

    /** @var string[] */
    protected const ACCEPTED_MODIFIERS = [
        self::MODIFIER_MISSING
    ];

    // prefix
    /** @var string */
    public const PREFIX_EQUAL = 'eq';

    /** @var string */
    public const PREFIX_NOT_EQUAL = 'ne';

    /** @var string */
    public const PREFIX_GREATER_THAN = 'gt';

    /** @var string */
    public const PREFIX_LESS_THAN = 'lt';

    /** @var string */
    public const PREFIX_GREATER_OR_EQUAL = 'ge';

    /** @var string */
    public const PREFIX_LESS_OR_EQUAL = 'le';

    /** @var string */
    public const PREFIX_START_AFTER = 'sa';

    /** @var string */
    public const PREFIX_END_BEFORE = 'be';

    /** @var string */
    public const PREFIX_APPROXIMATE = 'ap';

    /** @var string[] */
    protected const ACCEPTED_PREFIXES = [

    ];

    /** @var string[] */
    public const ALL_PREXFIX = [
        self::PREFIX_EQUAL,
        self::PREFIX_NOT_EQUAL,
        self::PREFIX_GREATER_THAN,
        self::PREFIX_LESS_THAN,
        self::PREFIX_GREATER_OR_EQUAL,
        self::PREFIX_LESS_OR_EQUAL,
        self::PREFIX_START_AFTER,
        self::PREFIX_END_BEFORE,
        self::PREFIX_APPROXIMATE,
    ];

    // attributes
    /** @var string[]  */
    private $modifiers;

    /** @var string */
    private $parameter_name;

    /** @var string[] */
    private $prefixes;

    /**
     * @return string
     */
    public function getParameterName(): string
    {
        return $this->parameter_name;
    }

    /**
     * AbstractSearchParameter constructor.
     *
     * @param string               $parameter_name
     * @param string|string[]|null $modifiers
     * @param null                 $prefixes
     */
    public function __construct(string $parameter_name, $modifiers = null, $prefixes = null)
    {
        $this->parameter_name = $parameter_name;

        $this->setModifiers($modifiers);
        $this->setPrefixes($prefixes);
    }

    /**
     * @param string|null $modifier
     * @param mixed       $value
     * @param string|null $prefixValue
     *
     * @return string
     */
    public function prepareOperator(?string $modifier, $value, ?string $prefixValue): string
    {
        if ($modifier === self::MODIFIER_MISSING) {
            return $value ? RequestFilter::FILTER_IS_NULL : RequestFilter::FILTER_IS_NOT_NULL;
        }

        return RequestFilter::FILTER_EQUAL;
    }

    /**
     * @param string $value
     *
     * @return string|null
     */
    public function extractPrefixValue(string $value): ?string
    {
        return null;
    }


    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function extractValue($value)
    {
        return $value;
    }

    /**
     * @param string[]|string|null $modifiers
     */
    public function setModifiers($modifiers): void
    {
        if ($modifiers === null) {
            $modifiers = $this::ACCEPTED_MODIFIERS;
        }

        if (!is_array($modifiers)) {
            $modifiers = [$modifiers];
        }

        foreach ($modifiers as $modifier) {
            if (!in_array($modifier, $this::ACCEPTED_MODIFIERS)) {
                throw new CFHIRException("The modifier '$modifier' is not accepted for " . get_class($this));
            }
        }

        $this->modifiers = $modifiers;
    }

    /**
     * @param string $modifier
     *
     * @return bool
     */
    public function isSupportedModifier(string $modifier): bool
    {
        return in_array($modifier, $this->modifiers);
    }

    /**
     * @param string $prefix
     *
     * @return bool
     */
    public function isSupportedPrefix(string $prefix): bool
    {
        return in_array($prefix, $this->prefixes);
    }

    /**
     * @param string|string[]|null $prefixes
     */
    public function setPrefixes($prefixes): void
    {
        if ($prefixes === null) {
            $prefixes = $this::ACCEPTED_PREFIXES;
        }

        if (!is_array($prefixes)) {
            $prefixes = [$prefixes];
        }

        foreach ($prefixes as $prefix) {
            if (!in_array($prefix, $this::ACCEPTED_PREFIXES)) {
                throw new CFHIRException("The prefix '$prefix' is not accepted for " . get_class($this));
            }
        }

        $this->prefixes = $prefixes;
    }

    /**
     * @param string      $key
     * @param             $value
     * @param string|null $modifier
     * @param string|null $prefix
     *
     * @return SearchParameter
     */
    public static function make(string $key, $value, ?string $prefix = null, ?string $modifier = null): SearchParameter
    {
        $type = static::class;
        if ($type === self::class) {
            $type = SearchParameterString::class;
        }

        return new SearchParameter(new $type($key), $value, $modifier, $prefix);
    }
}
