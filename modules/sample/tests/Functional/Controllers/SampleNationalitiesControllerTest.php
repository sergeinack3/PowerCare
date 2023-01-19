<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Tests\Functional\Controllers;

use Ox\Tests\JsonApi\Item;
use Ox\Tests\OxWebTestCase;

class SampleNationalitiesControllerTest extends OxWebTestCase
{
    public function testListNationalities(): void
    {
        $client = self::createClient();
        $client->request('GET', '/api/sample/nationalities', ['limit' => '3']);

        $this->assertResponseStatusCodeSame(200);

        $collection = $this->getJsonApiCollection($client);

        $this->assertTrue(3 >= $collection->getMeta('count'));

        $this->assertTrue($collection->hasLink('self'));
        $this->assertTrue($collection->hasLink('first'));
        $this->assertTrue($collection->hasLink('last'));

        /** @var Item $item */
        foreach ($collection as $item) {
            $this->assertEquals('sample_nationality', $item->getType());
            $this->assertNotNull($item->getId());
        }
    }
}
