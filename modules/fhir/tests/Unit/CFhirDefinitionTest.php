<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Tests\Unit;

use Ox\Core\CMbDT;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDate;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeId;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeHumanName;
use Ox\Interop\Fhir\Resources\R4\CFHIRDefinition;
use Ox\Interop\Fhir\Resources\R4\Patient\CFHIRResourcePatient;
use Ox\Interop\Fhir\Utilities\CFHIRTools;
use Ox\Tests\OxUnitTestCase;

class CFhirDefinitionTest extends OxUnitTestCase
{
    public function providerFields(): array
    {
        $fields_summaries = [
            'id', 'identifier', 'active', 'name', 'telecom', 'gender', 'birthDate', 'deceased',
            'address','maritalStatus', 'multipleBirth', 'photo', 'contact', 'communication', 'generalPractitioner', 'managingOrganization', 'link'
        ];

        $data = [];
        foreach ($fields_summaries as $field) {
            $title = "'$field' should be field";
            $data[$title] = ['field' => $field];
        }

        return $data;
    }

    /**
     * @dataProvider providerFields
     */
    public function testDefinitionFields(string $field): void
    {
        $this->assertContains($field,CFHIRDefinition::getFields(CFHIRResourcePatient::class));
    }

    public function providerFieldSummaries(): array
    {
        $fields_summaries = [
            'identifier', 'active', 'name', 'telecom', 'gender', 'birthDate', 'deceased',
            'address', 'managingOrganization', 'link'
        ];

        $data = [];
        foreach ($fields_summaries as $field) {
            $title = "'$field' should be summary field";
            $data[$title] = ['field' => $field];
        }

        return $data;
    }

    /**
     * @dataProvider providerFieldSummaries
     */
    public function testDefinitionSummaries(string $field): void
    {
        $this->assertContains($field,CFHIRDefinition::getSummariesFields(CFHIRResourcePatient::class));
    }

    /**
     * // todo move in serializer test
     */
    public function testDefinitionFieldsNonEmpty(): void
    {
        $resource = new CFHIRResourcePatient();
        $resource->setId(new CFHIRDataTypeId('1'));
        $resource->setName((new CFHIRDataTypeHumanName())->setFamily('foo'));
        $resource->setDeceased(new CFHIRDataTypeBoolean(false));
        $resource->setBirthDate(new CFHIRDataTypeDate(CMbDT::date()));

        $expected = ['id', 'name', 'birthDate', 'deceasedBoolean'];
        $keys = array_keys(CFHIRTools::getNonEmptyFields($resource));

        $this->assertEquals($expected, $keys);
    }
}
