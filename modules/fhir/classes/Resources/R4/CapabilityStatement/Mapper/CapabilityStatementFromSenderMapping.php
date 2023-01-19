<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\CapabilityStatement\Mapper;

use Ox\Interop\Fhir\Actors\CSenderFHIR;
use Ox\Interop\Fhir\Contracts\Mapping\R4\CapabilityStatementMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeMarkdown;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CapabilityStatement\CFHIRDataTypeCapabilityStatementSoftware;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeMeta;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeNarrative;
use Ox\Interop\Fhir\Resources\R4\CapabilityStatement\CFHIRResourceCapabilityStatement;

/**
 * FIHR CapabilityStatement resource
 */
class CapabilityStatementFromSenderMapping implements CapabilityStatementMappingInterface
{
    private CFHIRResourceCapabilityStatement $capability;
    private CSenderFHIR $sender_FHIR;

    public function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    public function mapUrl(): CFHIRDataTypeUri
    {
        // TODO: Implement mapUrl() method.
    }

    /**
     * @inheritDoc
     */
    public function mapVersion(): CFHIRDataTypeString
    {
        // TODO: Implement mapVersion() method.
    }

    /**
     * @inheritDoc
     */
    public function mapName(): CFHIRDataTypeString
    {
        // TODO: Implement mapName() method.
    }

    /**
     * @inheritDoc
     */
    public function mapTitle(): CFHIRDataTypeString
    {
        // TODO: Implement mapTitle() method.
    }

    /**
     * @inheritDoc
     */
    public function mapStatus(): ?CFHIRDataTypeCode
    {
        // TODO: Implement mapStatus() method.
    }

    /**
     * @inheritDoc
     */
    public function mapExperimental(): CFHIRDataTypeBoolean
    {
        // TODO: Implement mapExperimental() method.
    }

    /**
     * @inheritDoc
     */
    public function mapDate(): ?CFHIRDataTypeDateTime
    {
        // TODO: Implement mapDate() method.
    }

    /**
     * @inheritDoc
     */
    public function mapPublisher(): CFHIRDataTypeString
    {
        // TODO: Implement mapPublisher() method.
    }

    /**
     * @inheritDoc
     */
    public function mapContact(): array
    {
        // TODO: Implement mapContact() method.
    }

    /**
     * @inheritDoc
     */
    public function mapDescription(): CFHIRDataTypeMarkdown
    {
        // TODO: Implement mapDescription() method.
    }

    /**
     * @inheritDoc
     */
    public function mapUseContext(): array
    {
        // TODO: Implement mapUseContext() method.
    }

    /**
     * @inheritDoc
     */
    public function mapJurisdiction(): array
    {
        // TODO: Implement mapJurisdiction() method.
    }

    /**
     * @inheritDoc
     */
    public function mapPurpose(): CFHIRDataTypeMarkdown
    {
        // TODO: Implement mapPurpose() method.
    }

    /**
     * @inheritDoc
     */
    public function mapCopyright(): CFHIRDataTypeMarkdown
    {
        // TODO: Implement mapCopyright() method.
    }

    /**
     * @inheritDoc
     */
    public function mapKind(): ?CFHIRDataTypeCode
    {
        // TODO: Implement mapKind() method.
    }

    /**
     * @inheritDoc
     */
    public function mapInstantiates(): array
    {
        // TODO: Implement mapInstantiates() method.
    }

    /**
     * @inheritDoc
     */
    public function mapImports(): array
    {
        // TODO: Implement mapImports() method.
    }

    /**
     * @inheritDoc
     */
    public function mapSoftware(): CFHIRDataTypeCapabilityStatementSoftware
    {
        // TODO: Implement mapSoftware() method.
    }

    /**
     * @inheritDoc
     */
    public function mapFhirVersion(): ?CFHIRDataTypeCode
    {
        // TODO: Implement mapFhirVersion() method.
    }

    /**
     * @inheritDoc
     */
    public function mapFormat(): ?array
    {
        // TODO: Implement mapFormat() method.
    }

    /**
     * @inheritDoc
     */
    public function mapPatchFormat(): array
    {
        // TODO: Implement mapPatchFormat() method.
    }

    /**
     * @inheritDoc
     */
    public function mapImplementationGuide(): array
    {
        // TODO: Implement mapImplementationGuide() method.
    }

    /**
     * @inheritDoc
     */
    public function mapRest(): array
    {
        // TODO: Implement mapRest() method.
    }

    /**
     * @inheritDoc
     */
    public function mapText(): ?CFHIRDataTypeNarrative
    {
        // TODO: Implement mapText() method.
    }

    /**
     * @inheritDoc
     */
    public function mapContained(): array
    {
        // TODO: Implement mapContained() method.
    }

    /**
     * @inheritDoc
     */
    public function mapExtension(): array
    {
        // TODO: Implement mapExtension() method.
    }

    /**
     * @inheritDoc
     */
    public function mapModifierExtension(): array
    {
        // TODO: Implement mapModifierExtension() method.
    }

    /**
     * @inheritDoc
     */
    public function mapId(): ?CFHIRDataTypeString
    {
        // TODO: Implement mapId() method.
    }

    /**
     * @inheritDoc
     */
    public function mapMeta(): ?CFHIRDataTypeMeta
    {
        // TODO: Implement mapMeta() method.
    }

    /**
     * @inheritDoc
     */
    public function mapImplicitRules(): ?CFHIRDataTypeUri
    {
        // TODO: Implement mapImplicitRules() method.
    }

    /**
     * @inheritDoc
     */
    public function mapLanguage(): ?CFHIRDataTypeCode
    {
        // TODO: Implement mapLanguage() method.
    }
}
