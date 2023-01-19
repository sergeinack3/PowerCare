<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\DocumentReference\Mapper\ANS;

use Exception;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Mapping\R4\DocumentReferenceMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\DocumentReference\CFHIRDataTypeDocumentReferenceContent;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\DocumentReference\CFHIRDataTypeDocumentReferenceContext;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\DocumentReference\CFHIRDataTypeDocumentReferenceRelatesTo;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAttachment;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeExtension;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Profiles\CFHIRCDL;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceDomainTrait;
use Ox\Interop\Fhir\Resources\R4\DocumentReference\Profiles\ANS\CFHIRResourceDocumentReferenceCdL;
use Ox\Interop\Fhir\Resources\R4\Patient\Profiles\InteropSante\CFHIRResourcePatientFR;
use Ox\Interop\Fhir\Resources\R4\Practitioner\Profiles\InteropSante\CFHIRResourcePractitionerFR;
use Ox\Interop\Fhir\Resources\R4\PractitionerRole\Profiles\AnnuaireSante\CFHIRResourcePractitionerRoleProfessionalRass;
use Ox\Interop\Fhir\Resources\R4\RelatedPerson\CFHIRResourceRelatedPerson;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\System\CNote;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

class DocumentReferenceCdLNote implements DelegatedObjectMapperInterface, DocumentReferenceMappingInterface
{
    use CStoredObjectResourceDomainTrait;

    /** @var CNote */
    protected $object;

    /** @var CFHIRResourceDocumentReferenceCdL */
    protected CFHIRResource $resource;

    public function setResource(CFHIRResource $resource, $object): void
    {
        $this->object   = $object;
        $this->resource = $resource;
    }

    /**
     * @param CFHIRResource $resource
     * @param mixed         $object
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource, $object): bool
    {
        return $object instanceof CNote && $object->_id;
    }

    /**
     * @return string[]
     */
    public function onlyProfiles(): array
    {
        return [CFHIRCDL::class];
    }

    /**
     * @return string[]
     */
    public function onlyRessources(): array
    {
        return [CFHIRResourceDocumentReferenceCdL::class];
    }

    /**
     * @throws Exception
     */
    public function mapMasterIdentifier(): ?CFHIRDataTypeIdentifier
    {
        return new CFHIRDataTypeIdentifier($this->object->getUuid());
    }

    public function mapStatus(): ?CFHIRDataTypeCode
    {
        return new CFHIRDataTypeCode('current');
    }

    public function mapDocStatus(): ?CFHIRDataTypeCode
    {
        return null;
    }

    public function mapType(): ?CFHIRDataTypeCodeableConcept
    {
        $values               = CANSValueSet::loadEntries('typeNoteCdL', 'OBS');
        $values['codeSystem'] = 'urn:oid:1.2.250.1.213.1.1.4.334';

        return CFHIRDataTypeCodeableConcept::fromValues($values);
    }

    public function mapCategory(): array
    {
        return [];
    }

    public function mapSubject(): ?CFHIRDataTypeReference
    {
        $target = $this->object->loadTargetObject();
        if (($target instanceof CFile || $target instanceof CNote) && $target->_id) {
            $target = $target->loadTargetObject();
        }
        if (!$target->_id) {
            return null;
        }

        return $target instanceof CPatient
            ? $this->resource->addReference(CFHIRResourcePatientFR::class, $target)
            : null;
    }

    public function mapDate(): ?CFHIRDataTypeInstant
    {
        return new CFHIRDataTypeInstant($this->object->date);
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function mapAuthor(): array
    {
        if ($author = $this->object->getFromStore(CCorrespondantPatient::class)) {
            return [$this->resource->addReference(CFHIRResourceRelatedPerson::class, $author)];
        }

        if ($author = $this->object->getFromStore(CMedecin::class)) {
            return [$this->resource->addReference(CFHIRResourcePractitionerRoleProfessionalRass::class, $author)];
        }

        $user = $this->object->loadRefUser();
        if ($user->_id) {
            return [$this->resource->addReference(CFHIRResourcePractitionerFR::class, $user)];
        }

        return [];
    }

    public function mapAuthenticator(): ?CFHIRDataTypeReference
    {
        // Forbidden in this profile
        return null;
    }

    public function mapCustodian(): ?CFHIRDataTypeReference
    {
        // Forbidden in this profile
        return null;
    }

    /**
     * @return CFHIRDataTypeDocumentReferenceRelatesTo[]
     * @throws \Exception
     */
    public function mapRelatesTo(): array
    {
        $relates = [];
        $target = $this->object->loadTargetObject();
        if (($target instanceof CFile || $target instanceof CNote) && $target->_id) {
            $relates[] =  CFHIRDataTypeDocumentReferenceRelatesTo::build(
                [
                    'code'   => new CFHIRDataTypeCode('appends'),
                    'target' => $this->resource->addReference(CFHIRResourceDocumentReferenceCdL::class, $target),
                ]
            );
        }

        return $relates;
    }

    public function mapDescription(): ?CFHIRDataTypeString
    {
        return $this->object->libelle && $this->object->text ? new CFHIRDataTypeString($this->object->libelle) : null;
    }

    public function mapSecurityLabel(): array
    {
        return [];
    }

    public function mapContent(): array
    {
        $title   = $this->object->libelle;
        $content = $this->object->text ?: $title;

        $reference_content = CFHIRDataTypeDocumentReferenceContent::build(
            [
                'attachment' => CFHIRDataTypeAttachment::build(
                    [
                        'data'        => $content,
                        'size'        => strlen($content),
                        'hash'        => sha1($content),
                        'title'       => $this->object->text ? $title : null,
                        'creation'    => $this->resource->getDate()->getValue(),
                        'contentType' => 'text/plain',
                    ]
                ),
            ]
        );

        return [$reference_content];
    }

    public function mapContext(): ?CFHIRDataTypeDocumentReferenceContext
    {
        return null;
    }

    public function mapExtension(): array
    {
        $extensions = [];
        if ($this->object->degre === 'high') {
            $extensions[] = CFHIRDataTypeExtension::addExtension(
                'http://esante.gouv.fr/ci-sis/fhir/StructureDefinition/isUrgent',
                [
                    'valueBoolean' => new CFHIRDataTypeBoolean(true),
                ]
            );
        }

        return $extensions;
    }
}
