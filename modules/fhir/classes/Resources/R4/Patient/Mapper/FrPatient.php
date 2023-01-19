<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Patient\Mapper;

use Exception;
use Ox\Interop\Eai\CDomain;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDate;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Patient\CFHIRDataTypePatientContact;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAddress;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeExtension;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeHumanName;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\Organization\Profiles\InteropSante\CFHIRResourceOrganizationFR;
use Ox\Interop\Fhir\Resources\R4\Patient\Profiles\InteropSante\CFHIRResourcePatientFR;
use Ox\Interop\Fhir\Resources\R4\Practitioner\Profiles\InteropSante\CFHIRResourcePractitionerFR;
use Ox\Interop\Fhir\Resources\R4\RelatedPerson\Profiles\InteropSante\CFHIRResourceRelatedPersonFR;
use Ox\Interop\Fhir\Utilities\Helper\PatientHelper;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CIdentityProofType;
use Ox\Mediboard\Patients\CINSPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientINSNIR;
use Ox\Mediboard\Patients\CSourceIdentite;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

/**
 * Description
 */
class FrPatient extends Patient
{
    /** @var CPatient */
    protected $object;

    /** @var CFHIRResourcePatientFR */
    protected CFHIRResource $resource;


    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function mapExtension(): array
    {
        $extensions = [];

        $extensions[] = CFHIRDataTypeExtension::addExtension(
            'http://hl7.org/fhir/StructureDefinition/patient-nationality',
            [
                'value' => [],
            ]
        );

        $extensions[] = CFHIRDataTypeExtension::addExtension(
            'http://interopsante.org/fhir/StructureDefinition/FrPatientIdentReliability',
            [
                'value' => [],
            ]
        );

        $extensions[] = CFHIRDataTypeExtension::addExtension(
            'http://interopsante.org/fhir/StructureDefinition/FrPatientDeathPlace',
            [
                'value' => [],
            ]
        );

        $extensions[] = CFHIRDataTypeExtension::addExtension(
            'http://interopsante.org/fhir/StructureDefinition/FrPatientIdentityMethodCollection',
            [
                'value' => [],
            ]
        );

        $extensions[] = CFHIRDataTypeExtension::addExtension(
            'http://interopsante.org/fhir/StructureDefinition/FrPatientBirthdateUpdateIndicator',
            [
                'value' => [],
            ]
        );

        if ($this->object->cp_naissance) {
            $extensions[] = CFHIRDataTypeExtension::addExtension(
                'http://hl7.org/fhir/StructureDefinition/patient-birthPlace',
                [
                    'valueAddress' => CFHIRDataTypeAddress::build(
                        [
                            'district' => $this->object->cp_naissance,
                        ]
                    ),
                ]
            );
        }

        // todo gestion des extension null

        $source_identite_active = $this->object->loadRefSourceIdentite();
        if ($source_identite_active && $source_identite_active->_id) {
            $sub_extensions = [];
            if ($code_identity = $this->getCodeSourceIdentity()) {
                $sub_extensions[] = CFHIRDataTypeExtension::addExtension(
                    'identityReliability',
                    [
                        'valueCoding' => CFHIRDataTypeCoding::fromValues(
                            $code_identity
                        ),
                    ]
                );

                $sub_extensions[] = CFHIRDataTypeExtension::addExtension(
                    'validationDate',
                    [
                        "valueDate" => new CFHIRDataTypeDate($source_identite_active->debut),
                    ]
                );
            }

            if ($mode_identity = $this->getModeSourceIdentity($source_identite_active)) {
                $sub_extensions[] = CFHIRDataTypeExtension::addExtension(
                    'validationMode',
                    [
                        'valueCoding' => CFHIRDataTypeCoding::fromValues(
                            $mode_identity
                        ),
                    ]
                );
            }

            if ($sub_extensions) {
                $extension_reliability = CFHIRDataTypeExtension::addExtension(
                    'http://interopsante.org/fhir/StructureDefinition/FrPatientIdentReliability',
                    [
                        'extension' => $sub_extensions,
                    ]
                );

                $extensions[] = $extension_reliability;
            }
        }

        return $extensions;
    }

    /**
     * @throws Exception
     */
    public function mapIdentifier(): array
    {
        $identifiers = parent::mapIdentifier();

        if ($patientINSNIR = $this->object->loadRefPatientINSNIR()) {
            if ($patientINSNIR->_is_ins_nir) {
                $type_ins = (new CFHIRDataTypeCodeableConcept())
                    ->setCoding(PatientHelper::getTypeCodingINS());

                $INS_NIR = CFHIRDataTypeIdentifier::makeIdentifier($patientINSNIR->ins_nir, CPatientINSNIR::OID_INS_NIR)
                    ->setType($type_ins)
                    ->setUse(new CFHIRDataTypeCode('official'));

                $identifiers[] = $INS_NIR;
            }

            if ($patientINSNIR->_is_ins_nia) {
                $typeSystem = 'http://interopsante.org/CodeSystem/fr-v2-0203';
                $coding     = (new CFHIRDataTypeCoding())
                    ->setCode('INS-NIA')
                    ->setSystem(CPatientINSNIR::OID_INS_NIA)
                    ->setDisplay('NIA');
                $type_nia   = (new CFHIRDataTypeCodeableConcept())->setCoding($coding);

                $identifiers[] = CFHIRDataTypeIdentifier::makeIdentifier($patientINSNIR->is_nia, $typeSystem)
                    ->setUse(new CFHIRDataTypeCode('temp'))
                    ->setType($type_nia);
            }
        }

        $patientINS             = new CINSPatient();
        $patientINS->patient_id = $this->object->_id;
        $patientINS->type       = 'C';
        if ($patientINS->loadMatchingObject()) {
            $system = 'urn:oid:1.2.250.1.213.1.4.2';
            $coding = (new CFHIRDataTypeCoding())
                ->setSystem('http://interopsante.org/CodeSystem/fr-v2-0203')
                ->setCode('INS-C')
                ->setDisplay('INS calculé');
            $type   = (new CFHIRDataTypeCodeableConcept())->setCoding($coding);

            $identifiers[] = CFHIRDataTypeIdentifier::makeIdentifier($patientINS->type, $system)
                ->setType($type)
                ->setUse(new CFHIRDataTypeCode('secondary'));
        }

        if ($IPP = $this->object->loadIPP()) {
            $master_domain = CDomain::getMasterDomainPatient();
            if ($oid = $master_domain->OID) {
                $type_identifier = CFHIRDataTypeCodeableConcept::addCodeable(PatientHelper::getTypeCodingIPP());

                $identifier = CFHIRDataTypeIdentifier::makeIdentifier($IPP, $oid);
                $identifier->type = $type_identifier;
                $identifier->use = new CFHIRDataTypeCode('usual');
                $identifiers[]   = $identifier;
            }
        }

        return $identifiers;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function mapName(): array
    {
        $assemblyOrder = [
            'url'   => 'http://terminology.hl7.org/CodeSystem/v2-0444',
            'value' => new CFHIRDataTypeCode('F'),
        ];

        $extension = CFHIRDataTypeExtension::addExtension(
            'http://terminology.hl7.org/CodeSystem/v2-0444',
            [
                'valueCoding' => 'F',
            ]
        );

        //TODO Passer en CFHIRDataTypeFrHumanName
        $names = CFHIRDataTypeHumanName::addName(
            $this->object->nom_jeune_fille,
            $this->object->prenom,
            'official',
            $this->object->prenoms
        );

        if ($this->object->prenom_usuel) {
            //TODO Passer en CFHIRDataTypeFrHumanName
            $names = CFHIRDataTypeHumanName::addName(
                $this->object->nom,
                $this->object->prenom_usuel,
                'usual',
                null,
                $names
            );
        }

        return $names;
    }

    public function mapTelecom(): array
    {
        //TODO Passer en CFHIRDataTypeFrAddress
        return [];
    }

    /**
     */
    public function mapAddress(): array
    {
        //TODO Passer en CFHIRDataTypeFrAddress
        return [];
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function mapContact(): array
    {
        $contacts = [];

        /** @var CCorrespondantPatient[] */
        $correspondantsPatient = $this->object->loadRefsCorrespondantsPatient();

        foreach ($correspondantsPatient as $_correspondant) {
            $relationship = $this->getContactRelationship($_correspondant);

            $name = $this->getContactName($_correspondant);

            $telecom = $this->getContactTelecom($_correspondant);

            $address = $this->getContactAddress($_correspondant);

            $gender = new CFHIRDataTypeCode($this->resource->formatGender($_correspondant->sex));

            $organization = null;

            $period = null;

            $contact = CFHIRDataTypePatientContact::build(
                [
                    'relationship' => $relationship,
                    'name'         => $name,
                    'telecom'      => $telecom,
                    'address'      => $address,
                    'gender'       => $gender,
                    'organization' => $organization,
                    'period'       => $period,
                ]
            );

            $contact->extension = $this->getContactExtension($_correspondant);

            $contacts[] = $contact;
        }

        return $contacts;
    }

    public function mapGeneralPractitioner(string $resource_class = CFHIRResourcePractitionerFR::class): array
    {
        return parent::mapGeneralPractitioner($resource_class);
    }

    public function mapManagingOrganization(string $resource_class = CFHIRResourceOrganizationFR::class
    ): ?CFHIRDataTypeReference {
        return parent::mapManagingOrganization($resource_class);
    }

    /**
     * @return CFHIRDataTypeExtension[]
     * @throws ReflectionException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    protected function getContactExtension(CCorrespondantPatient $correspondant_patient): array
    {
        return [
            CFHIRDataTypeExtension::addExtension(
                'http://interopsante.org/fhir/StructureDefinition/FrPatientContactIdentifier',
                [
                    'value' => CFHIRDataTypeIdentifier::build(
                        [
                            "value" => new CFHIRDataTypeString($correspondant_patient->getUuid()),
                        ]
                    ),
                ]
            ),
        ];
    }

    /**
     * @param CCorrespondantPatient $_correspondant_patient
     *
     * @return CFHIRDataTypeCodeableConcept[]
     */
    protected function getContactRelationship(CCorrespondantPatient $_correspondant_patient): array
    {
        $rolePersonSystem  = 'urn:oid:2.16.840.1.113883.5.110';
        $rolePersonCode    = 'ECON';
        $rolePersonDisplay = 'Entit  contacter en cas d\'urgence';

        $relatedPersonSystem = 'urn:oid:2.16.840.1.113883.5.111';

        $relationships = [];

        if ($related_person = CFHIRResourceRelatedPersonFR::getRelatedPerson($_correspondant_patient)) {
            return [$related_person];
        }

        switch ($_correspondant_patient->parente) {
            case 'mere':
                $relatedPersonCode    = 'MTH';
                $relatedPersonDisplay = 'Mre';
                break;

            case 'pere':
                $relatedPersonCode    = 'FTH';
                $relatedPersonDisplay = 'Pre';
                break;

            case 'grand_parent':
                $relatedPersonCode    = 'GRPRN';
                $relatedPersonDisplay = 'Grand-parent';
                break;

            case 'frere':
                $relatedPersonCode    = 'BRO';
                $relatedPersonDisplay = 'Frre';
                break;

            case 'soeur':
                $relatedPersonCode    = 'SIS';
                $relatedPersonDisplay = 'Soeur';
                break;

            case 'petits_enfants':
                $relatedPersonCode    = 'GRNDCHILD';
                $relatedPersonDisplay = 'Petit-enfant';
                break;

            //TODO Vers quel code mapper ? HUSB Epoux, WIFE Epouse, SPS Epoux ou pouse
            case 'epoux':
                $relatedPersonCode    = 'SPS';
                $relatedPersonDisplay = 'Epoux ou pouse';
                break;

            case 'enfant':
                $relatedPersonCode    = 'CHILD';
                $relatedPersonDisplay = 'Enfant';
                break;

            case 'enfant_adoptif':
                $relatedPersonCode    = 'CHLDADOPT';
                $relatedPersonDisplay = 'Enfant adopt';
                break;

            //TODO Vers quel code mapper ? CHLDINLAW Gendre ou belle-fille
            case 'beau_fils':
                $relatedPersonCode    = '';
                $relatedPersonDisplay = '';
                break;

            case 'conjoint':
                $relatedPersonCode    = 'SIGOTHR';
                $relatedPersonDisplay = 'Conjoint';
                break;

            default:
                $relatedPersonCode    = 'FRND';
                $relatedPersonDisplay = 'Autre proche';
                break;
        }


        $relatedPersonCoding = CFHIRDataTypeCoding::addCoding(
            $relatedPersonSystem,
            $relatedPersonCode,
            $relatedPersonDisplay
        );
        $text                = $relatedPersonDisplay;

        $relationships[] = CFHIRDataTypeCodeableConcept::addCodeable($relatedPersonCoding, $text);

        if (!$relationships) {
            $rolePersonCoding = CFHIRDataTypeCoding::addCoding(
                $relatedPersonSystem,
                $relatedPersonCode,
                $relatedPersonDisplay
            );
            $text             = $rolePersonDisplay;

            $relationships[] = CFHIRDataTypeCodeableConcept::addCodeable($rolePersonCoding, $text);
        }

        return $relationships;
    }

    /**
     * @param CCorrespondantPatient $_correspondant_patient
     *
     * @return CFHIRDataTypeContactPoint[]
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    protected function getContactTelecom(CCorrespondantPatient $_correspondant_patient): array
    {
        // TODO Passer à CFHIRDataTypeFrContactPoint
        return parent::getContactTelecom($_correspondant_patient);
    }

    protected function getCodeSourceIdentity(): array
    {
        if ($this->object->status === "QUAL") {
            $code    = 'VALI';
            $display = "Identité validée";
        } else {
            $code    = 'PROV';
            $display = "Identité provisoire";
        }

        return [
            'code'        => $code,
            'codeSystem'  => "http://interopsante.org/fhir/CodeSystem/fr-v2-0445",
            'displayName' => $display,
        ];
    }

    protected function getModeSourceIdentity(CSourceIdentite $source_identite_active): array
    {
        $type   = $source_identite_active->loadRefIdentityProofType();
        $system = '';
        if ($type->code === CIdentityProofType::PROOF_ACTE_NAISSANCE) {
            $code    = 'AN';
            $display = "Extrait d'acte de naissance";
        } elseif ($type->code === CIdentityProofType::PROOF_CARTE_ID) {
            $code    = 'CN';
            $display = "Carte nationale d'identité";
        } elseif ($type->code === CIdentityProofType::PROOF_LIVRET_FAMILLE) {
            $code    = 'LE';
            $display = "Livret de famille";
        } elseif ($type->code === CIdentityProofType::PROOF_PASSEPORT) {
            $code    = 'PA';
            $display = "Passeport";
        } else {
            return [];
        }

        return [
            'code'        => $code,
            'codeSystem'  => $system,
            'displayName' => $display,
        ];
    }
}
