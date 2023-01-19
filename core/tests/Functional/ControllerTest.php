<?php
/**
 * @package Core\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Functional;

use Ox\Core\Api\Resources\Item;
use Ox\Core\CModelObjectCollection;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\System\Controllers\SystemController;
use Ox\Tests\OxWebTestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ControllerTest extends OxWebTestCase
{

    public function testRenderApiResponse(): void
    {
        $controller = static::getContainer()->get(SystemController::class);
        $resource   = new Item(new CUser());
        $reponse    = $controller->renderApiResponse($resource);
        $this->assertInstanceOf(JsonResponse::class, $reponse);
        $this->assertNotNull($reponse->getEtag());
    }

    public function testStoreObjectAndRenderApiResponse(): void
    {
        $controller = static::getContainer()->get(SystemController::class);

        /** @var CUser $user */
        $user                = $this->getObjectFromFixturesReference(
            CUser::class,
            UsersFixtures::REF_USER_LOREM_IPSUM
        );
        $user->user_address1 = uniqid('address');

        /** @var Response $response */
        $response = $controller->storeObjectAndRenderApiResponse($user);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertStringContainsString(
            '"user_username": "' . UsersFixtures::REF_USER_LOREM_IPSUM . '"',
            $response->getContent()
        );
    }

    public function testStoreCollectionAndRenderApiResponse(): void
    {
        $controller = static::getContainer()->get(SystemController::class);
        $collection = new CModelObjectCollection();

        /** @var CUser $user */
        $user                = $this->getObjectFromFixturesReference(
            CUser::class,
            UsersFixtures::REF_USER_LOREM_IPSUM
        );
        $user->user_address1 = uniqid('address');
        $collection->add($user);

        $user2  = new CUser();
        $uniqid = uniqid();
        $user2->cloneFrom($user);
        $user2->user_username = $uniqid;
        $collection->add($user2);

        /** @var Response $response */
        $response = $controller->storeCollectionAndRenderApiResponse($collection);

        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertStringContainsString(
            '"user_username": "' . UsersFixtures::REF_USER_LOREM_IPSUM . '"',
            $response->getContent()
        );
        $this->assertStringContainsString(
            '"user_username": "' . $uniqid . '"',
            $response->getContent()
        );
    }
}
