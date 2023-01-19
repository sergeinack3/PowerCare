<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Delegated;

use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterList;

/**
 * Description
 */
interface DelegatedObjectSearcherInterface extends DelegatedObjectInterface
{
    /**
     * Register all supported parameters
     *
     * @param SearchParameterList $parameter_list
     *
     * @return void
     */
    public function registerSupportedParameters(SearchParameterList $parameter_list): void;

    /**
     * Get all data which respond to the request
     *
     * @param CFHIRResource $resource
     * @param string        $limit
     *
     * @return array
     */
    public function search(CFHIRResource $resource, string $limit): array;

    /**
     * Get the count of total object which match with request
     *
     * @return int
     */
    public function getTotal(): int;
}
