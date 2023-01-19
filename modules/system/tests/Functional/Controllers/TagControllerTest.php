<?php

/**
 * @author  SARL OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Mediboard\System\Tests\Functional\Controllers;

use Exception;
use Ox\Core\Kernel\Exception\HttpException;
use Ox\Mediboard\Sample\Entities\CSampleMovie;
use Ox\Mediboard\System\CTag;
use Ox\Tests\OxWebTestCase;
use Ox\Tests\TestsException;

class TagControllerTest extends OxWebTestCase
{
    /**
     * @throws TestsException
     * @throws Exception
     */
    public function testShowTag(): void
    {
        $tag = $this->createTag();

        $client = static::createClient();
        $client->request(
            'GET',
            "/api/tag/" . $tag->_id,
        );

        $this->assertResponseIsSuccessful();
        $item = $this->getJsonApiItem($client);

        $this->assertEquals(CTag::RESOURCE_TYPE, $item->getType());
        $this->assertEquals('Tag API', $item->getAttribute('name'));
    }

    /**
     * @throws TestsException
     */
    public function testListTags(): void
    {
        $client = static::createClient();
        $client->request(
            'GET',
            "/api/tag",
        );

        $this->assertResponseIsSuccessful();
        $collection = $this->getJsonApiCollection($client);

        $this->assertEquals(CTag::RESOURCE_TYPE, $collection->getFirstItem()->getType());
        $this->assertTrue($collection->hasLink('self'));
        $this->assertTrue($collection->hasLink('first'));
        $this->assertTrue($collection->hasLink('last'));
    }

    /**
     * @return CTag
     * @throws Exception|HttpException
     */
    private function createTag(): CTag
    {
        $tag               = new CTag();
        $tag->name         = 'Tag API';
        $tag->object_class = (new CSampleMovie())->_class;

        if ($msg = $tag->store()) {
            throw new HttpException($msg);
        }

        return $tag;
    }
}
