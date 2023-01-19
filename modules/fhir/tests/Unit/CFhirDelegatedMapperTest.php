<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Tests\Unit;

use DOMDocument;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbPath;
use Ox\Core\CMbXPath;
use Ox\Core\Module\CModule;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Serializers\CFHIRSerializer;
use Ox\Interop\Fhir\Tests\Fixtures\Resources\Appointment\AppointmentFhirResourcesFixtures;
use Ox\Interop\Fhir\Tests\Fixtures\Resources\Location\LocationFhirResourcesFixtures;
use Ox\Interop\Fhir\Tests\Fixtures\Resources\Observation\ObservationFhirResourcesFixtures;
use Ox\Interop\Fhir\Tests\Fixtures\Resources\Organization\OrganizationFhirResourcesFixtures;
use Ox\Interop\Fhir\Tests\Fixtures\Resources\Patient\PatientFhirResourcesFixtures;
use Ox\Interop\Fhir\Tests\Fixtures\Resources\Practitioner\PractitionerFhirResourcesFixtures;
use Ox\Interop\Fhir\Tests\Fixtures\Resources\PractitionerRole\PractitionerRoleFhirResourcesFixtures;
use Ox\Interop\Fhir\Tests\Fixtures\Resources\Schedule\ScheduleFhirResourcesFixtures;
use Ox\Interop\Fhir\Tests\Fixtures\Resources\Slot\SlotFhirResourcesFixtures;
use Ox\Tests\OxUnitTestCase;
use Ox\Tests\TestsException;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Class CFhirDelegatedMapperTest
 * @package Ox\Interop\Fhir\Tests\Unit
 *
 * @group   schedules
 *
 */
class CFhirDelegatedMapperTest extends OxUnitTestCase
{
    /** @var bool|null */
    private static ?bool $is_java_installed = null;

    /** @var string|null */
    private static ?string $path_validator = null;

    /** @var null|string */
    private static ?string $output_path = null;

    /* @var array|array[] */
    private static array $issues = [];

    /** @var array */
    private static array $resources = [
        AppointmentFhirResourcesFixtures::OBJECT_RESOURCE_COUPLE,
        LocationFhirResourcesFixtures::OBJECT_RESOURCE_COUPLE,
        ObservationFhirResourcesFixtures::OBJECT_RESOURCE_COUPLE,
        OrganizationFhirResourcesFixtures::OBJECT_RESOURCE_COUPLE,
        PatientFhirResourcesFixtures::OBJECT_RESOURCE_COUPLE,
        PractitionerFhirResourcesFixtures::OBJECT_RESOURCE_COUPLE,
        PractitionerRoleFhirResourcesFixtures::OBJECT_RESOURCE_COUPLE,
        ScheduleFhirResourcesFixtures::OBJECT_RESOURCE_COUPLE,
        SlotFhirResourcesFixtures::OBJECT_RESOURCE_COUPLE,
    ];

    /**
     * @throws TestsException
     * @throws Exception
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$is_java_installed = self::isJavaInstalled();

        if (!CModule::getActive("sourceCode") && $module = CModule::getInstalled('sourceCode')) {
            self::toogleActiveModule($module);
        }

        if (self::$path_validator = CAppUI::conf('sourceCode fhir fhir_validator_path')) {
            self::$path_validator = rtrim(self::$path_validator, '/') . '/validator_cli.jar';
            if (!file_exists(self::$path_validator)) {
                self::$path_validator = null;
            }
        }

        if (!self::hasValidator()) {
            self::markTestSkipped('FHIR validator cli is not configured');
        }

        if (!self::getResources()) {
            self::markTestSkipped('No fhir resource available');
        }
    }

    /**
     * @return array|CFHIRResource[]
     * @throws TestsException
     */
    private static function getResources(): array
    {
        if (self::$resources) {
            return self::$resources;
        }

        // setUpBeforeClass was not called (launch test without testsuite)
        if (self::$is_java_installed === null) {
            self::setUpBeforeClass();

            return self::$resources;
        }

        return [];
    }

    /**
     * @return array
     * @throws TestsException
     */
    public function providerScenariosFHIR(): array
    {
        $resources = [];
        /** @var array $resource */
        foreach (self::getResources() as $resource) {
            $resources = array_merge($resources, $resource);
        }

        return $resources;
    }

    /**
     * @param string $fhir_resource
     * @param string $object_class
     * @param string $fixture_ref
     *
     * @throws Exception|InvalidArgumentException
     * @dataProvider providerScenariosFHIR
     */
    public function testFhirResourcesXMLFormat(string $fhir_resource, string $object_class, string $fixture_ref): void
    {
        $resource = new $fhir_resource();

        $object_to_map = $this->getObjectFromFixturesReference(
            $object_class,
            $fixture_ref
        );

        $resource->mapFrom($object_to_map);

        $is_valid = $this->validate($resource, 'xml');

        $this->assertTrue($is_valid, $this->getFailedMessage($resource, $fhir_resource, 'xml'));
    }

    /**
     * @param string $fhir_resource
     * @param string $object_class
     * @param string $fixture_ref
     *
     * @throws Exception
     * @throws InvalidArgumentException
     * @dataProvider providerScenariosFHIR
     */
    public function testFhirResourcesJSONFormat(string $fhir_resource, string $object_class, string $fixture_ref): void
    {
        $resource = new $fhir_resource();

        $object_to_map = $this->getObjectFromFixturesReference(
            $object_class,
            $fixture_ref
        );

        $resource->mapFrom($object_to_map);

        $is_valid = $this->validate($resource);

        $this->assertTrue($is_valid, $this->getFailedMessage($resource, $fhir_resource, 'json'));
    }


    /**
     * @return bool
     */
    private static function hasValidator(): bool
    {
        if (!self::$is_java_installed) {
            return false;
        }

        return self::$path_validator ? true : false;
    }

    private static function isJavaInstalled(): bool
    {
        exec('java -version 2>&1', $output, $code);

        return $code === 0;
    }

    /**
     * @param CFHIRResource $resource
     * @param string        $format
     *
     * @return bool
     * @throws Exception
     * @throws InvalidArgumentException
     */
    private function validate(CFHIRResource $resource, string $format = 'json'): bool
    {
        $dir               = CAppUI::getTmpPath('fhir_scenario');
        $cli_path          = self::$path_validator;
        $file_name         = uniqid('fhir_resource_scenario', true);
        $resource_path     = $dir . '/' . $file_name . ".$format";
        $package_version   = 'hl7-france-fhir.administrative';
        self::$output_path = $resource_output_path = $dir . '/output_' . $file_name . ".$format";

        $resource_profile = $resource->getProfile();
        $resource_version = $resource->getResourceFHIRVersion();

        // create tmp file with data
        CMbPath::forceDir($dir);

        // Serialize resource
        $serializer = CFHIRSerializer::serialize($resource, $format);
        $data       = $serializer->getResourceSerialized();

        file_put_contents($resource_path, $data);

        // build command
        $cmd = "java -jar $cli_path $resource_path -ig $package_version -version $resource_version -profile $resource_profile -output $resource_output_path -tx n/a";

        // execute validation
        exec($cmd);

        $result_code = $this->parseResponse($format);

        return $result_code === 0;
    }

    /**
     * @param string $format
     *
     * @return int : number of errors
     * @throws Exception
     */
    private function parseResponse(string $format): int
    {
        $issues = [
            'error'       => [],
            'fatal'       => [],
            'warning'     => [],
            'information' => [],
        ];

        if ($format === 'json') {
            $outcome = json_decode(file_get_contents(self::$output_path));

            foreach ($outcome->issue as $issue) {
                $issues[$issue->severity][] = $issue->details->text;
            }
        } elseif ($format === 'xml') {
            $dom = new DOMDocument();
            $dom->loadXML(file_get_contents(self::$output_path));
            $xpath = new CMbXPath($dom);
            $xpath->registerNamespace('f', 'http://hl7.org/fhir');

            $issues_nodes = $xpath->query('//f:issue');
            foreach ($issues_nodes as $node) {
                $severity            = $xpath->queryAttributNode('f:severity', $node, 'value');
                $issues[$severity][] = $xpath->queryAttributNode('f:details/f:text', $node, 'value');
            }
        }

        self::$issues = $issues;

        return count($issues['error']) + count($issues['fatal']);
    }

    /**
     * @param CFHIRResource $resource
     * @param string        $title
     * @param string        $format
     *
     * @return string
     */
    private function getFailedMessage(CFHIRResource $resource, string $title, string $format): string
    {
        $message = "The test '$title' for the resource '" . get_class($resource) . "' in format '$format' has failed";

        if ($errors = $this->getErrors()) {
            $message .= "\n" . $errors;
        }

        if ($warnings = $this->getWarnings()) {
            $message .= "\n" . $warnings;
        }

        return $message;
    }

    /**
     * @return string|null
     */
    private function getErrors(): ?string
    {
        if (!$errors = CMbArray::get(self::$issues, 'error')) {
            return null;
        }

        return $this->formatAnnotation($errors, 'error');
    }


    /**
     * @return string|null
     */
    private function getWarnings(): ?string
    {
        if (!$warnings = CMbArray::get(self::$issues, 'warning')) {
            return null;
        }

        return $this->formatAnnotation($warnings, 'warning');
    }

    /**
     * @param array  $messages
     * @param string $type
     *
     * @return string
     */
    private function formatAnnotation(array $messages, string $type): string
    {
        $count   = count($messages);
        $content = "$type ($count) : ";
        foreach ($messages as $index => $message) {
            $content .= "\n\t[$index] : $message";
        }

        return $content;
    }
}
