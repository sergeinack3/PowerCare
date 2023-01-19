<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir;

use Ox\Core\Module\AbstractModuleCache;
use Ox\Interop\Fhir\ClassMap\FHIRClassMap;
use Ox\Interop\Fhir\Resources\R4\CFHIRDefinition;
use Psr\SimpleCache\InvalidArgumentException;

class CModuleCacheFhir extends AbstractModuleCache
{
    public function getModuleName(): string
    {
        return 'fhir';
    }

    /**
     * @inheritdoc
     * @throws InvalidArgumentException
     */
    public function clearSpecialActions(): void
    {
        parent::clearSpecialActions();

        // Delete cache Definition Fhir
        CFHIRDefinition::clearCache();

        // Delete cache resource map
        (new FHIRClassMap())->clearCache();
    }
}
