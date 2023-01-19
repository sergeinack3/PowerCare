<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Kernel\Resolver;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CController;
use Ox\Core\CStoredObject;
use Ox\Core\Kernel\Exception\ControllerException;
use Ox\Core\Kernel\Exception\HttpException;
use Ox\Core\Kernel\Routing\RequestHelperTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Throwable;

/**
 * Responsible for resolving the value of an argument based on its metadata.
 */
final class CStoredObjectAttributeValueResolver implements ArgumentValueResolverInterface
{
    use RequestHelperTrait;

    private $object_class;
    private $object_id;
    private $controller;

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        // Support argument
        $this->object_class = $argument->getType();

        if ($this->object_class === CStoredObject::class) {
            $this->object_class = $request->attributes->get('object_class');
        }

        if (!is_subclass_of(
            $this->object_class,
            CStoredObject::class
        )) {
            return false;
        }

        // Support controller
        $controller = $this->getController($request);
        if (!class_exists($controller) || !is_subclass_of($controller, CController::class)) {
            return false;
        }

        try {
            $this->controller = new $controller();
        } catch (Throwable $e) {
            throw new ControllerException(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                CAppUI::tr(
                    'ControllerException-Error-Cannot-resolve-attributes-for-controllers-with-arguments-in-their-constructor'
                )
            );
        }


        /** @var CStoredObject $object */
        $object             = new $this->object_class();
        $object_primary_key = $object->getPrimaryKey();

        if (!$this->object_id = $request->attributes->getInt($object_primary_key)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        /** @var CStoredObject $object */
        $object = $this->object_class::findOrFail($this->object_id);

        // check perm
        switch ($request->getMethod()) {
            case Request::METHOD_GET:
            case Request::METHOD_HEAD:
            default:
                // check read
                $perm = $this->controller->checkPermRead($object, $request);
                break;

            case Request::METHOD_POST:
            case Request::METHOD_PUT:
            case Request::METHOD_DELETE:
            case Request::METHOD_PATCH:
                // check edit
                $perm = $this->controller->checkPermEdit($object, $request);
                break;
        }

        if (!$perm) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Permission denied');
        }

        yield $object;
    }
}
