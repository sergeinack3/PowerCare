<?php
/**
 * @author  SARL OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Mediboard\Admin\Tests\Functional\Controllers;

use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Tests\OxWebTestCase;
use Symfony\Component\Routing\Generator\UrlGenerator;

class PermissionControllerTest extends OxWebTestCase
{
    public function testIdenticate()
    {
        /** @var CUser $user */
        $user = $this->getObjectFromFixturesReference(CUser::class, UsersFixtures::REF_USER_LOREM_IPSUM);

        $client = static::createClient();
        $client->request('GET', 'api/identicate', [
            'login' => $user->user_username,
        ]);

        $this->assertResponseIsSuccessful();

        $item = $this->getJsonApiItem($client);

        $this->assertEquals($item->getType(), 'identicate');

        $this->assertEquals($item->getAttribute('login'), $user->user_username);

        $this->assertTrue($item->getAttribute('is_identicate'));
    }

    public function testIdenticateDoesNotExist()
    {
        $client = static::createClient();
        $client->request('GET', 'api/identicate', [
            'login' => uniqid('user_'),
        ]);

        $this->assertResponseIsSuccessful();

        $item = $this->getJsonApiItem($client);

        $this->assertFalse($item->getAttribute('is_identicate'));
    }

    public function testIdenticateError()
    {
        $client = static::createClient();

        $client->request('GET', 'api/identicate');

        $this->assertResponseStatusCodeSame(500);
    }
}
