<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Utility;

use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\Filter;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Request\RequestFilter;
use Ox\Core\CMbString;

/**
 * Restricted filter for search on non model objects
 */
class ArrayFilter
{
    public const SEARCH_IN_KEY   = 'key';
    public const SEARCH_IN_VALUE = 'value';

    public const SEARCH_MODES = [
        RequestFilter::FILTER_BEGIN_WITH,
        RequestFilter::FILTER_END_WITH,
        RequestFilter::FILTER_CONTAINS,
        RequestFilter::FILTER_EQUAL,
    ];

    public const SEARCH_IN = [
        self::SEARCH_IN_KEY,
        self::SEARCH_IN_VALUE,
    ];

    /** @var string */
    private $search;
    /** @var string */
    private $search_mode;
    /** @var string */
    private $search_in;

    public function __construct(RequestApi $request)
    {
        if (!$request->isFiltersEmpty()) {
            $filters = $request->getFilters();

            /** @var Filter $filter */
            $filter = reset($filters);

            if (!$filter->isEmpty()) {
                $values = $filter->getValues();

                $this->search      = CMbString::lower($values[0]);
                $this->search_mode = $filter->getOperator();
                $this->search_in   = $filter->getKey();

                if ($this->search_mode && !in_array($this->search_mode, self::SEARCH_MODES, true)) {
                    throw new ApiException(
                        'Search mode candidate \'' . $this->search_mode . '\' is not in '
                        . implode('|', self::SEARCH_MODES)
                    );
                }

                if ($this->search_in && !in_array($this->search_in, self::SEARCH_IN, true)) {
                    throw new ApiException(
                        'Search in candidate \'' . $this->search_in . '\' is not in ' . implode('|', self::SEARCH_IN)
                    );
                }
            }
        }
    }

    public function isEnabled(): bool
    {
        return (bool)$this->search;
    }

    public function apply(array $array): array
    {
        $filtered_locales = [];
        foreach ($array as $_key => $_value) {
            $haystack = ($this->search_in === self::SEARCH_IN_KEY) ? $_key : $_value;

            if ($this->search && !$this->isValidValue($this->search, CMbString::lower($haystack), $this->search_mode)) {
                continue;
            }

            $filtered_locales[$_key] = $_value;
        }

        return $filtered_locales;
    }

    private function isValidValue(string $search_value, string $haystack, string $search_mode): bool
    {
        switch ($search_mode) {
            case RequestFilter::FILTER_CONTAINS:
                return (strpos($haystack, $search_value) !== false);

            case RequestFilter::FILTER_EQUAL:
                return ($search_value === $haystack);

            case RequestFilter::FILTER_BEGIN_WITH:
                return str_starts_with($haystack, $search_value);

            case RequestFilter::FILTER_END_WITH:
                return CMbString::endsWith($haystack, $search_value);

            default:
                throw new ApiException(
                    'Search mode candidate \'' . $search_mode . '\' is not in '
                    . implode('|', self::SEARCH_MODES)
                );
        }
    }
}
