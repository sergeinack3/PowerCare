<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Kernel\Resolver;

use Ox\Core\CStoredObject;
use Ox\Core\Kernel\Resolver\CStoredObjectAttributeValueResolver;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\Controllers\SystemController;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 *  Test if a request is supported and the creation of the CStoredObject + perm check
 */
class CStoredObjectAttributeValueResolverTest extends OxUnitTestCase
{
    /**
     * @dataProvider notSupportedRequestProvider
     */
    public function testNotSupportedResquest(Request $request, ArgumentMetadata $argument): void
    {
        $resolver = new CStoredObjectAttributeValueResolver();
        $this->assertFalse($resolver->supports($request, $argument));
    }

    public function testIsSupportedRequest(): void
    {
        $request  = new Request([], [], ['_controller' => SystemController::class, 'user_id' => CUser::get()->_id]);
        $argument = new ArgumentMetadata('lorem', CUser::class, false, false, null);

        $resolver = new CStoredObjectAttributeValueResolver();
        $this->assertTrue($resolver->supports($request, $argument));
    }

    public function testResolveObject(): void
    {
        $current_user = CUser::get();

        $request = $this->getValidRequest();

        $argument = new ArgumentMetadata('lorem', CUser::class, false, false, null);

        $resolver = new CStoredObjectAttributeValueResolver();
        $resolver->supports($request, $argument);
        $found = $resolver->resolve($request, $argument)->current();

        $this->assertEquals($current_user->getPlainFields(), $found->getPlainFields());
    }

    public function notSupportedRequestProvider(): array
    {
        return [
            'argument is not a CStoredObject'             => [
                new Request([], [], ['object_class' => 'toto']),
                new ArgumentMetadata('lorem', CStoredObject::class, false, false, null),
            ],
            'controller does not exists'                  => [
                new Request([], [], ['_controller' => 'lorem']),
                new ArgumentMetadata('lorem', CUser::class, false, false, null),
            ],
            'object primary key is not the argument name' => [
                new Request([], [], ['_controller' => SystemController::class, 'not_user_id' => 10]),
                new ArgumentMetadata('lorem', CUser::class, false, false, null),
            ],
        ];
    }

    private function getValidRequest(string $method = 'GET'): Request
    {
        $request = new Request([], [], ['_controller' => SystemController::class, 'user_id' => CUser::get()->_id]);
        $request->setMethod($method);

        return $request;
    }
}
