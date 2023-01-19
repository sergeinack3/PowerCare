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
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\DocumentReference\CFHIRDataTypeDocumentReferenceContent;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\DocumentReference\CFHIRDataTypeDocumentReferenceContext;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAttachment;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Profiles\CFHIRCDL;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceDomainTrait;
use Ox\Interop\Fhir\Resources\R4\DocumentReference\Profiles\ANS\CFHIRResourceDocumentReferenceCdL;
use Ox\Interop\Fhir\Resources\R4\Patient\Profiles\InteropSante\CFHIRResourcePatientFR;
use Ox\Interop\Fhir\Resources\R4\PractitionerRole\Profiles\AnnuaireSante\CFHIRResourcePractitionerRoleProfessionalRass;
use Ox\Interop\Fhir\Resources\R4\RelatedPerson\CFHIRResourceRelatedPerson;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\System\CNote;

class DocumentReferenceCdLDocumentItem implements DelegatedObjectMapperInterface, DocumentReferenceMappingInterface
{
    use CStoredObjectResourceDomainTrait;

    /** @var CDocumentItem */
    protected $object;

    /** @var CFHIRResourceDocumentReferenceCdL */
    protected CFHIRResource $resource;

    /**
     * @param CFHIRResource $resource
     * @param mixed         $object
     *
     * @return void
     */
    public function setResource(CFHIRResource $resource, $object): void
    {
        $this->object   = $object;
        $this->resource = $resource;
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
     * @param CFHIRResource $resource
     * @param               $object
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource, $object): bool
    {
        return $object instanceof CDocumentItem && $object->_id;
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

    public function mapType(): ?CFHIRDataTypeCodeableConcept
    {
        $values = CANSValueSet::loadEntries('typeNoteCdL', 'GEN');
        $values['codeSystem'] = 'urn:oid:1.2.250.1.213.1.1.4.334';

        return CFHIRDataTypeCodeableConcept::fromValues($values);
    }

    public function mapCategory(): array
    {
        return [];
    }

    public function mapSubject(): ?CFHIRDataTypeReference
    {
        $patient = $this->object->getIndexablePatient();
        if (!$patient->_id) {
            return null;
        }

        return $this->resource->addReference(CFHIRResourcePatientFR::class, $patient);
    }

    public function mapDate(): ?CFHIRDataTypeInstant
    {
        if ($this->object instanceof CFile) {
            return new CFHIRDataTypeInstant($this->object->file_date);
        }

        return new CFHIRDataTypeInstant($this->object->creation_date);
    }

    public function mapAuthor(): array
    {
        if ($author = $this->object->getFromStore(CCorrespondantPatient::class)) {
            return [$this->resource->addReference(CFHIRResourceRelatedPerson::class, $author)];
        }
        else {
            $author = $this->object->getFromStore(CMedecin::class) ?: $this->object->loadRefAuthor();
            if ($author && $author->_id) {
                return [$this->resource->addReference(CFHIRResourcePractitionerRoleProfessionalRass::class, $author)];
            }
        }

        return [];
    }

    public function mapAuthenticator(): ?CFHIRDataTypeReference
    {
        // not used in this profile
        return null;
    }

    public function mapCustodian(): ?CFHIRDataTypeReference
    {
        // not used in this profile
        return null;
    }

    public function mapRelatesTo(): array
    {
        return [];
    }

    public function mapDescription(): ?CFHIRDataTypeString
    {
        /** @var CNote $note */
        $note = $this->object->loadLastBackRef('notes');
        if ($note && $note->_id) {
            return new CFHIRDataTypeString($note->text ?: $note->libelle);
        }

        return null;
    }

    public function mapSecurityLabel(): array
    {
        $securities = [];
        $system = 'https://mos.esante.gouv.fr/NOS/TRE_A07-StatutVisibiliteDocument/FHIR/TRE-A07-StatutVisibiliteDocument';
        if ($this->object->masquage_patient) {
            $securities[] = CFHIRDataTypeCodeableConcept::fromValues(
                CANSValueSet::loadEntries('visibiliteDoc', 'INVISIBLE_PATIENT')
            );
        }

        if ($this->object->masquage_praticien) {
            $securities[] = CFHIRDataTypeCodeableConcept::fromValues(
                CANSValueSet::loadEntries('visibiliteDoc', 'MASQUE_PS')
            );
        }

        if ($this->object->masquage_representants_legaux) {
            $securities[] = CFHIRDataTypeCodeableConcept::fromValues(
                CANSValueSet::loadEntries('visibiliteDoc', 'INVISIBLE_REPRESENTANTS_LEGAUX')
            );
        }

        foreach ($securities as $security) {
            $security->coding[0]->system = new CFHIRDataTypeUri($system);
        }

        return $securities;
    }

    public function mapContent(): array
    {
        try {
            if (!$content = $this->object->getBinaryContent(true, false)) {
                return [];
            }
        } catch (Exception $e) {
            return [];
        }

        // todo générer le content uniquement lors du read
        // todo pour les search inclure un url gestion (necessite map du binary

        $content_type = $this->object instanceof CFile ? $this->object->file_type : "application/pdf";
        $title = $this->object instanceof CFile ? $this->object->file_name : $this->object->nom;
        $reference_content = CFHIRDataTypeDocumentReferenceContent::build(
            [
                'attachment' => CFHIRDataTypeAttachment::build(
                    [
                        'data'        => $content,
                        'size'        => strlen($content),
                        'hash'        => sha1($content),
                        'title'       => $title,
                        'creation'    => $this->resource->getDate()->getValue(),
                        'contentType' => $content_type,
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

    public function mapDocStatus(): ?CFHIRDataTypeCode
    {
        return null;
    }
}
