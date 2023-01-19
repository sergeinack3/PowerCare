<?php

/**
 * @author  SARL OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Mediboard\System\Tests\Functional\Controllers;

use Exception;
use Ox\Core\CMbException;
use Ox\Mediboard\System\ViewSender\CViewSender;
use Ox\Tests\OxWebTestCase;
use Ox\Tests\TestsException;

class ViewSendersControllerTest extends OxWebTestCase
{
    /**
     * @throws TestsException
     * @throws CMbException
     */
    public function testListViewSenders(): void
    {
        $this->createView();

        $client = static::createClient();
        $client->request(
            'GET',
            "/api/system/viewSenders",
        );

        $this->assertResponseIsSuccessful();
        $collection = $this->getJsonApiCollection($client);

        $this->assertEquals(CViewSender::RESOURCE_TYPE, $collection->getFirstItem()->getType());
        $this->assertTrue($collection->hasLink('self'));
        $this->assertTrue($collection->hasLink('first'));
        $this->assertTrue($collection->hasLink('last'));
    }

    /**
     * @throws CMbException
     * @throws Exception
     */
    private function createView(): void
    {
        $view         = new CViewSender();
        $view->params = implode(
            "\n",
            [
                'm=sample',
                'raw=lorem_ipsum',
                'query_id=1',
            ]
        );
        $view->period = 60;
        $view->active = true;

        $view->name = $view->getUniqueName('lorem_ipsum');

        if ($msg = $view->store()) {
            throw new CMbException($msg);
        }
    }
}
