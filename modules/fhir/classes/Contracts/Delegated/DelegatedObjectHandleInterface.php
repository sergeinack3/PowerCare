<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Delegated;

use Ox\Interop\Fhir\Resources\CFHIRResource;

/**
 * Description
 */
interface DelegatedObjectHandleInterface extends DelegatedObjectInterface
{
    /**
     * Intégration de la ressource dans un objet Mediboard
     *
     * @param CFHIRResource $resource
     *
     * @return CFHIRResource|null
     */
    public function handle(CFHIRResource $resource): ?CFHIRResource;

    /**
     * Know if this delegated can support the resource and object or data given
     *
     * @param CFHIRResource      $resource
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource): bool;
}
