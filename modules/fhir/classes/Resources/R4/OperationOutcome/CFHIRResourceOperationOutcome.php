<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\OperationOutcome;

use Ox\Interop\Fhir\Contracts\Mapping\R4\OperationOutcomeMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourceOperationOutcomeInterface;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\OperationOutcome\CFHIRDataTypeOperationOutcomeIssue;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;

/**
 * FIHR patient resource
 */
class CFHIRResourceOperationOutcome extends CFHIRDomainResource implements ResourceOperationOutcomeInterface
{
    // constants
    /** @var string */
    public const RESOURCE_TYPE = "OperationOutcome";

    // attributes
    /** @var CFHIRDataTypeOperationOutcomeIssue[] */
    protected array $issue = [];

    /** @var OperationOutcomeMappingInterface */
    protected $object_mapping;

    /**
     * @param CFHIRDataTypeOperationOutcomeIssue ...$issue
     *
     * @return $this
     */
    public function addIssue(CFHIRDataTypeOperationOutcomeIssue ...$issue): self
    {
        $this->issue = array_merge($this->issue, $issue);

        return $this;
    }

    /**
     * @param CFHIRDataTypeOperationOutcomeIssue[] $issue
     *
     * @return CFHIRResourceOperationOutcome
     */
    public function setIssue(CFHIRDataTypeOperationOutcomeIssue ...$issue): CFHIRResourceOperationOutcome
    {
        $this->issue = $issue;

        return $this;
    }

    /**
     * @return CFHIRDataTypeOperationOutcomeIssue[]
     */
    public function getIssue(): array
    {
        return $this->issue;
    }

    protected function mapIssue(): void
    {
        $this->issue = $this->object_mapping->mapIssue();
    }
}
