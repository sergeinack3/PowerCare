<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Tests\Functional\Controllers;

use Ox\Mediboard\Sample\Entities\CSampleNationality;
use Ox\Mediboard\Sample\Entities\CSamplePerson;
use Ox\Mediboard\Sample\Tests\Fixtures\SamplePersonFixtures;
use Ox\Mediboard\Sample\Tests\Fixtures\SampleUtilityFixtures;
use Ox\Tests\JsonApi\Item;
use Ox\Tests\OxWebTestCase;

class SamplePersonsControllerTest extends OxWebTestCase
{
    public function testListPersons(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/sample/persons', ['relations' => 'all', 'limit' => 3]);

        $this->assertResponseStatusCodeSame(200);

        $collection = $this->getJsonApiCollection($client);

        $this->assertEquals(3, $collection->getMeta('count'));

        /** @var Item $item */
        foreach ($collection as $item) {
            $this->assertNotNull($item->getId());
            $this->assertEquals('sample_person', $item->getType());

            if ($item->hasRelationship('nationality') && $nationality = $item->getRelationship('nationality')) {
                $this->assertEquals('sample_nationality', $nationality->getType());
                $this->assertNotNull($nationality->getId());
            }

            $this->assertTrue($item->hasRelationship('profilePicture'));
            $this->assertTrue($item->hasRelationship('moviesPlayed'));
        }
    }

    public function testGetPerson(): void
    {
        /** @var CSamplePerson $person */
        $person = $this->getObjectFromFixturesReference(CSamplePerson::class, SamplePersonFixtures::DIRECTOR_TAG);

        $client = self::createClient();
        $client->request('GET', '/api/sample/persons/' . $person->_id);

        $this->assertResponseStatusCodeSame(200);

        $item = $this->getJsonApiItem($client);
        $this->assertEquals($person->_id, $item->getId());
        $this->assertEquals('sample_person', $item->getType());
        $this->assertEquals($person->first_name, $item->getAttribute('first_name'));
        $this->assertEquals($person->last_name, $item->getAttribute('last_name'));
        $this->assertTrue($item->getAttribute('is_director'));

        // Check links
        $this->assertEquals('/api/sample/persons/' . $person->_id, $item->getLink('self'));
        $this->assertEquals('/api/schemas/sample_person', $item->getLink('schema'));
        $this->assertEquals('/api/history/sample_person/' . $person->_id, $item->getLink('history'));
    }

    public function testCreatePersonFailed(): void
    {
        $item = new Item('sample_person');

        $client = self::createClient();
        $client->request('POST', '/api/sample/persons', [], [], [], json_encode($item));

        $this->assertResponseStatusCodeSame(500);

        $error = $this->getJsonApiError($client);
        $this->assertStringContainsString('Personne : Données incorrectes', $error->getMessage());
    }

    public function testCreatePerson(): CSamplePerson
    {
        $nationality = $this->getObjectFromFixturesReference(
            CSampleNationality::class,
            SampleUtilityFixtures::NATIONALITY
        );

        $item = (new Item('sample_person'))
            ->setAttributes(['first_name' => 'test_firstname', 'last_name' => 'test_lastname'])
            ->setRelationships(['nationality' => new Item('sample_nationality', $nationality->_id)]);

        $client = $this->createClient();
        $client->request('POST', '/api/sample/persons?relations=nationality', [], [], [], json_encode($item));

        $this->assertResponseStatusCodeSame(201);

        $collection = $this->getJsonApiCollection($client);
        $this->assertEquals(1, $collection->getMeta('count'));
        $item = $collection->getFirstItem();

        $person_id = $item->getId();
        $this->assertNotNull($person_id);
        $this->assertEquals('sample_person', $item->getType());
        $this->assertEquals('test_firstname', $item->getAttribute('first_name'));
        $this->assertEquals('test_lastname', $item->getAttribute('last_name'));
        $this->assertEquals($nationality->_id, $item->getRelationship('nationality')->getId());

        return CSamplePerson::findOrFail($person_id);
    }

    /**
     * @depends testCreatePerson
     */
    public function testUpdatePerson(CSamplePerson $person): CSamplePerson
    {
        $item = (new Item('sample_person', $person->_id))
            ->setAttributes(['first_name' => 'new_first_name']);

        $client = self::createClient();
        $client->request('PATCH', '/api/sample/persons/' . $person->_id, [], [], [], json_encode($item));

        $this->assertResponseStatusCodeSame(200);

        $item = $this->getJsonApiItem($client);

        $this->assertEquals($person->_id, $item->getId());
        $this->assertEquals('sample_person', $item->getType());
        $this->assertEquals('new_first_name', $item->getAttribute('first_name'));

        return $person;
    }

    /**
     * @depends testCreatePerson
     */
    public function testDeletePerson(CSamplePerson $person): void
    {
        $client = self::createClient();
        $client->request('DELETE', '/api/sample/persons/' . $person->_id);

        $this->assertResponseStatusCodeSame(204);
        $this->assertEmpty($client->getResponse()->getContent());

        $this->assertFalse(CSamplePerson::find($person->_id));
    }
}
