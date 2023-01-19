<?php

/**
 * @package Mediboard\fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Description
 */

namespace Ox\Interop\Fhir\Tests\Unit;

use Exception;
use Ox\Core\CStoredObject;
use Ox\Interop\Fhir\ClassMap\FHIRClassMap;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Profiles\ProfileResource;
use Ox\Interop\Fhir\Datatypes\CFHIRDataType;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeChoice;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Profiles\CFHIRAnnuaireSante;
use Ox\Interop\Fhir\Profiles\CFHIRCDL;
use Ox\Interop\Fhir\Profiles\CFHIRInteropSante;
use Ox\Interop\Fhir\Profiles\CFHIRMES;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\AllergyIntolerance\CFHIRResourceAllergyIntolerance;
use Ox\Interop\Fhir\Resources\R4\Appointment\CFHIRResourceAppointment;
use Ox\Interop\Fhir\Resources\R4\Appointment\Profiles\InteropSante\CFHIRResourceAppointmentFR;
use Ox\Interop\Fhir\Resources\R4\Binary\CFHIRResourceBinary;
use Ox\Interop\Fhir\Resources\R4\Bundle\CFHIRResourceBundle;
use Ox\Interop\Fhir\Resources\R4\Bundle\Profiles\ANS\CDL\CFHIRResourceBundleCreationNoteCdL;
use Ox\Interop\Fhir\Resources\R4\CapabilityStatement\CFHIRResourceCapabilityStatement;
use Ox\Interop\Fhir\Resources\R4\CFHIRDefinition;
use Ox\Interop\Fhir\Resources\R4\CodeSystem\CFHIRResourceCodeSystem;
use Ox\Interop\Fhir\Resources\R4\ConceptMap\CFHIRResourceConceptMap;
use Ox\Interop\Fhir\Resources\R4\Device\CFHIRResourceDevice;
use Ox\Interop\Fhir\Resources\R4\Device\Profiles\PHD\CFHIRResourceDevicePHD;
use Ox\Interop\Fhir\Resources\R4\DocumentManifest\CFHIRResourceDocumentManifest;
use Ox\Interop\Fhir\Resources\R4\DocumentReference\CFHIRResourceDocumentReference;
use Ox\Interop\Fhir\Resources\R4\DocumentReference\Profiles\ANS\CFHIRResourceDocumentReferenceCdL;
use Ox\Interop\Fhir\Resources\R4\Encounter\CFHIRResourceEncounter;
use Ox\Interop\Fhir\Resources\R4\Location\CFHIRResourceLocation;
use Ox\Interop\Fhir\Resources\R4\Location\Profiles\InteropSante\CFHIRResourceLocationFR;
use Ox\Interop\Fhir\Resources\R4\Location\Profiles\MES\CFHIRResourceLocationENS;
use Ox\Interop\Fhir\Resources\R4\Observation\CFHIRResourceObservation;
use Ox\Interop\Fhir\Resources\R4\Observation\Profiles\ANS\CFHIRResourceObservationBMIENS;
use Ox\Interop\Fhir\Resources\R4\Observation\Profiles\ANS\CFHIRResourceObservationBodyHeightENS;
use Ox\Interop\Fhir\Resources\R4\Observation\Profiles\ANS\CFHIRResourceObservationBodyTemperatureENS;
use Ox\Interop\Fhir\Resources\R4\Observation\Profiles\ANS\CFHIRResourceObservationBodyWeightENS;
use Ox\Interop\Fhir\Resources\R4\Observation\Profiles\ANS\CFHIRResourceObservationBPENS;
use Ox\Interop\Fhir\Resources\R4\Observation\Profiles\ANS\CFHIRResourceObservationGlucoseENS;
use Ox\Interop\Fhir\Resources\R4\Observation\Profiles\ANS\CFHIRResourceObservationHeartrateENS;
use Ox\Interop\Fhir\Resources\R4\Observation\Profiles\ANS\CFHIRResourceObservationPainSeverityENS;
use Ox\Interop\Fhir\Resources\R4\Observation\Profiles\ANS\CFHIRResourceObservationStepsByDayENS;
use Ox\Interop\Fhir\Resources\R4\Observation\Profiles\ANS\CFHIRResourceObservationWaistCircumferenceENS;
use Ox\Interop\Fhir\Resources\R4\OperationOutcome\CFHIRResourceOperationOutcome;
use Ox\Interop\Fhir\Resources\R4\Organization\CFHIRResourceOrganization;
use Ox\Interop\Fhir\Resources\R4\Organization\Profiles\InteropSante\CFHIRResourceOrganizationFR;
use Ox\Interop\Fhir\Resources\R4\Parameters\CFHIRResourceParameters;
use Ox\Interop\Fhir\Resources\R4\Patient\CFHIRResourcePatient;
use Ox\Interop\Fhir\Resources\R4\Patient\Profiles\InteropSante\CFHIRResourcePatientFR;
use Ox\Interop\Fhir\Resources\R4\Practitioner\CFHIRResourcePractitioner;
use Ox\Interop\Fhir\Resources\R4\Practitioner\Profiles\ANS\CFHIRResourcePractitionerRASS;
use Ox\Interop\Fhir\Resources\R4\Practitioner\Profiles\InteropSante\CFHIRResourcePractitionerFR;
use Ox\Interop\Fhir\Resources\R4\PractitionerRole\CFHIRResourcePractitionerRole;
use Ox\Interop\Fhir\Resources\R4\PractitionerRole\Profiles\AnnuaireSante\CFHIRResourcePractitionerRoleOrganizationalRass;
use Ox\Interop\Fhir\Resources\R4\PractitionerRole\Profiles\AnnuaireSante\CFHIRResourcePractitionerRoleProfessionalRass;
use Ox\Interop\Fhir\Resources\R4\RelatedPerson\CFHIRResourceRelatedPerson;
use Ox\Interop\Fhir\Resources\R4\RelatedPerson\Profiles\InteropSante\CFHIRResourceRelatedPersonFR;
use Ox\Interop\Fhir\Resources\R4\Schedule\CFHIRResourceSchedule;
use Ox\Interop\Fhir\Resources\R4\Schedule\Profiles\InteropSante\CFHIRResourceScheduleFR;
use Ox\Interop\Fhir\Resources\R4\Slot\CFHIRResourceSlot;
use Ox\Interop\Fhir\Resources\R4\Slot\Profiles\InteropSante\CFHIRResourceSlotFR;
use Ox\Interop\Fhir\Resources\R4\StructureDefinition\CFHIRResourceStructureDefinition;
use Ox\Interop\Fhir\Resources\R4\ValueSet\CFHIRResourceValueSet;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameter;
use Ox\Interop\Fhir\Utilities\SearchParameters\SearchParameterString;
use Ox\Interop\Ihe\CMHD;
use Ox\Interop\Ihe\CPDQm;
use Ox\Interop\Ihe\CPHD;
use Ox\Interop\Ihe\CPIXm;
use Ox\Tests\OxUnitTestCase;
use PHPUnit\Framework\MockObject\MockClass;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Description
 */
class FHIRConformanceTest extends OxUnitTestCase
{
    /** @var string[] */
    private const RESOURCES_HL7_DEFINED = [
        CFHIRResourceAllergyIntolerance::class  => 'AllergyIntolerance',
        CFHIRResourceAppointment::class         => 'Appointment',
        CFHIRResourceBinary::class              => 'Binary',
        CFHIRResourceBundle::class              => 'Bundle',
        CFHIRResourceCapabilityStatement::class => 'CapabilityStatement',
        CFHIRResourceCodeSystem::class          => 'CodeSystem',
        CFHIRResourceConceptMap::class          => 'ConceptMap',
        CFHIRResourceDevice::class              => 'Device',
        CFHIRResourceDocumentManifest::class    => 'DocumentManifest',
        CFHIRResourceDocumentReference::class   => 'DocumentReference',
        CFHIRResourceEncounter::class           => 'Encounter',
        CFHIRResourceLocation::class            => 'Location',
        CFHIRResourceObservation::class         => 'Observation',
        CFHIRResourceOperationOutcome::class    => 'OperationOutcome',
        CFHIRResourceOrganization::class        => 'Organization',
        CFHIRResourceParameters::class          => 'Parameters',
        CFHIRResourcePatient::class             => 'Patient',
        CFHIRResourcePractitioner::class        => 'Practitioner',
        CFHIRResourcePractitionerRole::class    => 'PractitionerRole',
        CFHIRResourceSchedule::class            => 'Schedule',
        CFHIRResourceSlot::class                => 'Slot',
        CFHIRResourceStructureDefinition::class => 'StructureDefinition',
        CFHIRResourceRelatedPerson::class       => 'RelatedPerson',
        CFHIRResourceValueSet::class            => 'ValueSet',
    ];

    private const RESOURCES_PROFILED_DEFINED = [
        CFHIRResourcePatientFR::class                          => 'FrPatient',
        CFHIRResourceAppointmentFR::class                      => 'FrAppointment',
        CFHIRResourceOrganizationFR::class                     => 'FrOrganization',
        CFHIRResourceSlotFR::class                             => 'FrSlot',
        CFHIRResourceScheduleFR::class                         => 'FrSchedule',
        CFHIRResourcePractitionerFR::class                     => 'FrPractitioner',
        CFHIRResourcePractitionerRASS::class                   => 'practitioner-rass',
        CFHIRResourcePractitionerRoleProfessionalRass::class   => 'practitionerRole-professionalRole-rass',
        CFHIRResourcePractitionerRoleOrganizationalRass::class => 'practitionerRole-organizationalRole-rass',
        CFHIRResourceBundleCreationNoteCdL::class              => 'CdL_BundleCreationNoteCdL',
        CFHIRResourceDocumentReferenceCdL::class               => 'CdL_DocumentReferenceCdL',
        CFHIRResourceRelatedPersonFR::class                    => 'FrRelatedPerson',
        CFHIRResourceObservationBodyTemperatureENS::class      => 'ENS_FrObservationBodyTemperature',
        CFHIRResourceObservationBMIENS::class                  => 'ENS_FrObservationBmi',
        CFHIRResourceObservationHeartrateENS::class            => 'ENS_FrObservationHeartrate',
        CFHIRResourceObservationBodyHeightENS::class           => 'ENS_FrObservationBodyHeight',
        CFHIRResourceObservationBodyWeightENS::class           => 'ENS_FrObservationBodyWeight',
        CFHIRResourceObservationStepsByDayENS::class           => 'ENS_ObservationStepsByDay',
        CFHIRResourceObservationBPENS::class                   => 'ENS_FrObservationBp',
        CFHIRResourceObservationWaistCircumferenceENS::class   => 'ENS_FrObservationWaistCircumference',
        CFHIRResourceObservationPainSeverityENS::class         => 'ENS_FrObservationPainSeverity',
        CFHIRResourceObservationGlucoseENS::class              => 'ENS_FrObservationGlucose',
        CFHIRResourceLocationFR::class                         => 'FrLocation',
        CFHIRResourceLocationENS::class                        => 'ENS_FrLocation',
        CFHIRResourceDevicePHD::class                          => 'PhdDevice',
    ];

    /** @var string[] */
    private const PROFILE_DEFINED = [
        CFHIR::class              => 'http://hl7.org/fhir/StructureDefinition/',
        CFHIRMES::class           => 'http://esante.gouv.fr/ci-sis/fhir/StructureDefinition/',
        CFHIRCDL::class           => 'http://esante.gouv.fr/ci-sis/fhir/StructureDefinition/',
        CFHIRAnnuaireSante::class => 'https://apifhir.annuaire.sante.fr/ws-sync/exposed/structuredefinition/',
        CFHIRInteropSante::class  => 'http://interopsante.org/fhir/StructureDefinition/',
        CPHD::class               => 'http://hl7.org/fhir/uv/phd/StructureDefinition/',
        CMHD::class               => 'https://profiles.ihe.net/ITI/MHD/StructureDefinition',
        CPDQm::class              => 'https://profiles.ihe.net/ITI/PDQm/StructureDefinition',
        CPIXm::class              => 'https://profiles.ihe.net/ITI/PIXm/StructureDefinition',
    ];

    /**
     * @param $PROFILE_DEFINED
     *
     * @return array
     */
    public function providerBaseUrlProfile(): array
    {
        $data = [];
        foreach (self::PROFILE_DEFINED as $class => $value) {
            $data[$class] = ['class' => $class, 'expected' => $value];
        }

        return $data;
    }

    /**
     * @param string $class
     * @param string $expected
     *
     * @dataProvider providerBaseUrlProfile
     */
    public function testBaseURLProfile(string $class, string $expected): void
    {
        /** @var $class CFHIR */
        $this->assertEquals($expected, $class::BASE_PROFILE);
    }

    /**
     * @return array
     */
    public function providerResourceTypeFHIR(): array
    {
        $data = [];
        foreach (self::RESOURCES_HL7_DEFINED as $class => $value) {
            $data[$class] = ['resource' => $class, 'expected' => $value];
        }

        return $data;
    }

    /**
     * @param string $resource
     * @param string $expected
     *
     * @dataProvider providerResourceTypeFHIR
     */
    public function testResourceTypeFHIR(string $resource, string $expected): void
    {
        /** @var $resource CFHIRResource */
        $this->assertEquals($expected, $resource::RESOURCE_TYPE);
    }

    /**
     * @return array
     */
    public function providerResourceTypeProfile(): array
    {
        $data = [];
        foreach (self::RESOURCES_PROFILED_DEFINED as $class => $value) {
            $data[$class] = ['resource' => $class, 'expected' => $value];
        }

        return $data;
    }

    /**
     * @param string $resource
     * @param string $expected
     *
     * @dataProvider providerResourceTypeProfile
     */
    public function testResourceTypeProfile(string $resource, string $expected): void
    {
        /** @var $resource CFHIRResource */
        $this->assertEquals($expected, $resource::PROFILE_TYPE);
    }

    /**
     * @return array
     */
    public function providerResourceTypeDefined(): array
    {
        $map  = new FHIRClassMap();
        $data = [];

        foreach ($map->resource->listResources(null, CFHIR::class) as $resource) {
            $class        = get_class($resource);
            $data[$class] = ['resource_class' => $class];
        }

        return $data;
    }

    /**
     * @param string $resource_class
     *
     * @dataProvider providerResourceTypeDefined
     */
    public function testResourceTypeDefined(string $resource_class): void
    {
        $resource_classes = array_keys(self::RESOURCES_HL7_DEFINED);
        $this->assertTrue(
            in_array($resource_class, $resource_classes),
            "The resource '$resource_class' is not defined in constant Ox\Mediboard\\fhir\Tests\Unit\CFHIRTest::RESOURCES_HL7_DEFINED"
        );
    }

    /**
     * @return array
     */
    public function providerResourceProfileDefined(): array
    {
        $map  = new FHIRClassMap();
        $data = [];

        foreach ($map->resource->setReturnClass(true)->listProfiled() as $class) {
            $data[$class] = ['resource_class' => $class];
        }

        return $data;
    }

    /**
     * @param string $resource_class
     *
     * @dataProvider providerResourceProfileDefined
     */
    public function testResourceProfileDefined(string $resource_class): void
    {
        $resource_classes = array_keys(self::RESOURCES_PROFILED_DEFINED);

        $this->assertTrue(
            in_array($resource_class, $resource_classes),
            "The resource '$resource_class' is not defined in constant Ox\Mediboard\\fhir\Tests\Unit\CFHIRTest::RESOURCES_PROFILED_DEFINED"
        );
    }

    /**
     * @return array
     */
    public function providerProfileDefined(): array
    {
        $map  = new FHIRClassMap();
        $data = [];

        foreach ($map->profile->setReturnClass(true)->listProfiles() as $class) {
            $data[$class] = ['profile_class' => $class];
        }

        return $data;
    }

    /**
     * @param string $profile_class
     *
     * @dataProvider providerProfileDefined
     */
    public function testProfileDefined(string $profile_class): void
    {
        $profiles_classes = array_keys(self::PROFILE_DEFINED);

        $this->assertTrue(
            in_array($profile_class, $profiles_classes),
            "The profile '$profile_class' is not defined in constant Ox\Mediboard\\fhir\Tests\Unit\CFHIRTest::PROFILE_DEFINED"
        );
    }

    /**
     * @param mixed $expectedResult
     * @param mixed $input
     *
     * @dataProvider providerParameters
     */
    public function testParameters($expectedResult, $input): void
    {
        $this->assertSame($expectedResult, $input);
    }

    /**
     * @return array[]
     */
    public function providerParameters(): array
    {
        $format = new SearchParameter(new SearchParameterString('format'), 'application/fhir+xml');
        $family = new SearchParameter(new SearchParameterString('family'), 'DUPONT');
        $given  = new SearchParameter(new SearchParameterString('given'), 'Jean');
        $sex    = new SearchParameter(new SearchParameterString('sex'), 'm');

        $parameterBag = new ParameterBag();
        $parameterBag->set($format->getParameterName(), $format);
        $parameterBag->set($family->getParameterName(), $family);
        $parameterBag->set($given->getParameterName(), $given);
        $parameterBag->set($sex->getParameterName(), $sex);
        $parameterBag->set('url', 'www.openxtrem.com');

        $resource = new CFHIRResourcePatient();
        $resource->setParameterSearch($parameterBag);

        return [
            'FHIR searchParameters' => [count($resource->getSearchParameters()), 4],
            'FHIR brutParameters'   => [count($resource->getParametersBrut()), 1],
            'FHIR getParameter'     => [$resource->getParameterSearch('family')->getValue(), 'DUPONT'],
            'FHIR getBrutParameter' => [$resource->getParameterBrut('url'), 'www.openxtrem.com'],
            'FHIR getNullParameter' => [$resource->getParameterSearch('birthdate'), null],
        ];
    }


    public function providerUnicityDelegatedShortnameClass(): array
    {
        $fhir_map = new FHIRClassMap();
        $fhir_map->delegated->setReturnClass(true);
        $data            = [];
        $types_delegated = ['mapper', 'searcher', 'handle'];

        foreach ($types_delegated as $type_delegated) {
            $delegated_classes = $fhir_map->delegated->listDelegated($type_delegated);

            foreach ($delegated_classes as $delegated_class) {
                $short_name = substr($delegated_class, strrpos($delegated_class, '\\') + 1, strlen($delegated_class));

                $data["Unicity of $short_name for $type_delegated"] = [
                    $short_name,
                    $delegated_classes,
                    $type_delegated,
                ];
            }
        }

        return $data;
    }

    /**
     * Assert that each delegated object has a unique shortname per type of delegated object
     *
     * @dataProvider providerUnicityDelegatedShortnameClass
     *
     * @param string $shortname
     * @param array  $all_delegated_per_type
     * @param string $delegated_type
     *
     * @return void
     */
    public function testUnicityDelegatedShortnameClass(
        string $shortname,
        array $all_delegated_per_type,
        string $delegated_type
    ): void {
        $classes = array_filter($all_delegated_per_type, function ($class) use ($shortname) {
            return str_ends_with($class, "\\$shortname");
        });

        $nb_classes = count($classes);

        $this->assertCount(
            1,
            $classes,
            "The shortname class '$shortname' is not unique, $nb_classes as the same shortname for $delegated_type delegated object"
        );
    }

    /**
     * @return array
     * @throws Exception
     */
    public function providerProfile(): array
    {
        $data = [];
        foreach (array_keys(self::PROFILE_DEFINED) as $profile_class) {
            $data[$profile_class] = [new $profile_class()];
        }

        return $data;
    }

    /**
     * @dataProvider providerProfile
     *
     * @param ProfileResource $profile_class
     *
     * @return void
     */
    public function testProfileDefinition(ProfileResource $profile_class): void
    {
        $class = get_class($profile_class);
        $this->assertNotEmpty($profile_class::getCanonical(), "The canonical of profile [$class] should not be empty");
        $this->assertNotEmpty($profile_class::getProfileName(), "The name of profile [$class] should not be empty");
    }

    public function testNonProfileDoubloon(): void
    {
        // profile uniqueness == canonical + name of profile.

        /** @var ProfileResource[] $profiles */
        $canonicals = [];
        foreach (array_keys(self::PROFILE_DEFINED) as $profile_class) {
            $profile      = new $profile_class();
            $canonicals[] = sha1($profile::getCanonical() . ' ' . $profile->getProfileName());
        }

        $this->assertEquals(count($canonicals), count(array_unique($canonicals)));
    }

    public function providerGetAndSet(): array
    {
        $data      = [];
        $resources = array_keys(self::RESOURCES_HL7_DEFINED);

        foreach ($resources as $resource) {
            $fields = CFHIRDefinition::getFields($resource);
            foreach ($fields as $field) {
                $definition_field = CFHIRDefinition::getElementDefinition($resource, $field);
                $datatypes        = [];
                if (($definition_field['datatype']['class'] ?? null) === CFHIRDataTypeChoice::class) {
                    foreach ($definition_field['datatype']['sub_types'] as $sub_type) {
                        $datatypes[] = new $sub_type();
                    }
                } else {
                    $datatypes[] = new $definition_field['datatype']['class']();
                }

                foreach ($datatypes as $datatype) {
                    $data["Get and Set for $resource test $field and datatatype " . $datatype::NAME] = [
                        $resource,
                        $field,
                        $datatype,
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * @dataProvider providerGetAndSet
     *
     * @param string        $resource_class
     * @param string        $field
     * @param CFHIRDataType $datatype
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function testGetAndSet(string $resource_class, string $field, CFHIRDataType $datatype): void
    {
        $definition_field = CFHIRDefinition::getElementDefinition($resource_class, $field);
        $is_array         = $definition_field['datatype']['is_array'];

        $resource   = new $resource_class();
        $method_set = 'set' . ucfirst($field);
        if (!method_exists($resource_class, $method_set)) {
            $this->fail("Method $method_set doesn't exist on resource $resource_class");
        }

        if ($is_array) {
            $method_add = 'add' . ucfirst($field);
            if (!method_exists($resource_class, $method_add)) {
                $this->fail("Method $method_add doesn't exist on resource $resource_class");
            }
        }

        $method_get = 'get' . ucfirst($field);
        if (!method_exists($resource_class, $method_get)) {
            $this->fail("Method $method_get doesn't exist on resource $resource_class");
        }

        $method_map = 'map' . ucfirst($field);
        if (!method_exists($resource_class, $method_map)) {
            $this->fail("Method $method_map doesn't exist on resource $resource_class");
        }

        $empty_result = $resource->$method_get();
        if ($is_array) {
            $this->assertIsArray($empty_result);
            $this->assertEmpty($empty_result);
        } else {
            $this->assertNull($empty_result, $field);
        }

        $this->assertInstanceOf($resource_class, $resource->$method_set($datatype), $field);

        if ($is_array) {
            $this->assertEquals([$datatype], $resource->$method_get());

            $this->assertInstanceOf($resource_class, $resource->$method_add($datatype, $datatype));
            $this->assertEquals([$datatype, $datatype, $datatype], $resource->$method_get(), $method_add);

            // empty array for reset prop
            $resource->$method_set();
            $this->assertEmpty($resource->$method_get());
        } else {
            $this->assertEquals($datatype, $resource->$method_get());

            // null is possible to reset prop
            $resource->$method_set(null);
            $this->assertNull($resource->$method_get());
        }
    }

    public function providerFunctionMap(): array
    {
        $resources = array_keys(self::RESOURCES_HL7_DEFINED);

        $data      = [];
        foreach ($resources as $resource_class) {
            $title  = "map for resource $resource_class";
            $fields = CFHIRDefinition::getFields($resource_class);

            $methods_map = array_map(function (string $field) {
                return 'map' . ucfirst($field);
            }, $fields);

            $data[$title] = [
                $resource_class,
                $methods_map,
            ];
        }

        return $data;
    }

    /**
     * @param string $resource_class
     * @param array  $methods_map
     *
     * @return CFHIRResource
     */
    private function getMockResource(string $resource_class, array $methods_map): CFHIRResource
    {
        /** @var CFHIRResource $resource */
        $resource = $this->getMockBuilder($resource_class)
            ->onlyMethods($methods_map)
            ->getMock();

        foreach ($methods_map as $method) {
            $resource->method($method)->willReturnCallback(function () {
                return;
            });
        }

        $mapper_class = $this->getMockBuilder(DelegatedObjectMapperInterface::class)
            ->onlyMethods(['isSupported', 'setResource', 'onlyRessources', 'onlyProfiles', 'getMapping'])
            ->getMock();
        $mapper_class->method('isSupported')->willReturn(true);

        $resource->setMapper($mapper_class);

        return $resource;
    }

    /**
     * @dataProvider providerFunctionMap
     *
     * @param CFHIRResource|MockClass $resource
     * @param string[]                $methods_map
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function testFunctionMap(string $resource_class, array $methods_map): void
    {
        $resource = $this->getMockResource($resource_class, $methods_map);
        foreach ($methods_map as $method) {
            $resource->expects($this->exactly(1))->method($method);
        }

        $object      = new CStoredObject();
        $object->_id = 1;
        $resource->mapFrom($object);
    }
}
