<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Utility;

use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestApi;

trait FilterableTrait
{
    /** @var ArrayFilter */
    private $filter;

    /**
     * @param RequestApi $request_api
     * @param array      $array
     *
     * @return array
     * @throws ApiException
     */
    private function applyFilter(RequestApi $request_api, array $array): array
    {
        if ($this->filter === null) {
            $this->filter = new ArrayFilter($request_api);
        }

        if ($this->filter->isEnabled()) {
            $array = $this->filter->apply($array);
        }

        return $array;
    }

    private function isFilterEnabled(): bool
    {
        return ($this->filter && $this->filter->isEnabled());
    }
}
