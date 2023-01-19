<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Kernel\Event;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestFormats;
use Ox\Core\Api\Resources\Item;
use Ox\Core\Api\Serializers\ErrorSerializer;
use Ox\Core\CApp;
use Ox\Core\CController;
use Ox\Core\Kernel\Routing\RequestHelperTrait;
use Ox\Mediboard\System\Controllers\CSystemController;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

/**
 * Exception listener for V2 calls
 * Keep exception and make api/gui response (or let sf debuger)
 */
class ExceptionListener implements EventSubscriberInterface
{
    use RequestHelperTrait;

    private Environment           $twig;
    private LoggerInterface       $logger;
    private ContainerInterface    $container;

    /**
     * @param LoggerInterface $requestLogger Get the requestLogger because exceptions are on the request channel.
     */
    public function __construct(
        Environment $twig,
        LoggerInterface $requestLogger,
        ContainerInterface $container
    ) {
        $this->twig      = $twig;
        $this->logger    = $requestLogger;
        $this->container = $container;
    }

    /**
     * @inheritdoc
     *
     * Do not put more than -1 on the KernelEvents::EXCEPTION event to let SF log the exception with a 0 priority.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onException', -1],
        ];
    }

    /**
     * On exception check if the event is supported (not in _profiler).
     * And if the app is not in debug make a API/GUI response using the exception.
     *
     * @throws Exception
     */
    public function onException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        $request   = $event->getRequest();

        if (!$this->supports($event)) {
            return;
        }

        // Make Response
        $status_code = $throwable instanceof HttpExceptionInterface ?
            $throwable->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;

        $headers = $throwable instanceof HttpExceptionInterface ?
            $throwable->getHeaders() : [];

        if ($this->isRequestApi($request)) {
            $response = $this->makeApiResponse($event, $status_code, $headers);
        } else {
            $response = $this->makeGuiResponse($event, $status_code, $headers);
        }

        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @param ExceptionEvent $event
     * @param string         $status_code
     * @param array          $headers
     *
     * @return Response
     * @throws ApiException
     */
    protected function makeApiResponse(
        ExceptionEvent $event,
        string $status_code,
        array $headers = []
    ): Response {
        $e      = $event->getThrowable();

        $format = (new RequestFormats($event->getRequest()))->getExpected();
        $datas  = [
            'type'    => $this->encodeType($e),
            'code'    => $e->getCode(),
            'message' => $e->getMessage(),
        ];

        $resource = new Item($datas);
        $resource->setSerializer(ErrorSerializer::class);
        $resource->setFormat($format);

        return (new CController())->renderApiResponse($resource, $status_code, $headers);
    }

    /**
     * Obfuscated exception's class
     *
     * @param object $e
     *
     * @return string
     */
    private function encodeType($e): string
    {
        return base64_encode(get_class($e));
    }

    protected function makeGuiResponse(
        ExceptionEvent $event,
        string $status_code,
        array $headers = []
    ): Response {
        switch ($status_code) {
            case 503:
                $controller = $this->container->get(SystemController::class);

                return $controller->offline($event->getThrowable()->getMessage());
            case 403:
                $body = $this->twig->render(
                    "error403.html.twig",
                    $this->buildErrorPageParameters()
                );
                break;
            case 404:
                $body = $this->twig->render(
                    "error404.html.twig",
                    $this->buildErrorPageParameters()
                );
                break;
            case 500:
            default:
                $body = $this->twig->render(
                    "error.html.twig",
                    $this->buildErrorPageParameters()
                );
        }

        return new Response($body, $status_code, $headers);
    }

    /**
     * Do not use custom exception handler for profiler requests.
     * In profiler AppListener is not supported and CAppUI::conf is not initialized.
     */
    private function supports(KernelEvent $event): bool
    {
        $request = $event->getRequest();

        return !$this->isRequestProfiler($request) && $this->handleResponse($request);
    }

    /**
     * If not APP_DEBUG handle the response and do not let SF do it.
     * If the request is API and an ajax call handle the response to display the JSON error.
     */
    private function handleResponse(Request $request): bool
    {
        if (!$this->isAppDebug($request)) {
            return true;
        }

        if ($this->isRequestApi($request) && $request->isXmlHttpRequest()) {
            return true;
        }

        return false;
    }

    private function buildErrorPageParameters(): array
    {
        $root_dir = dirname(__DIR__, 4);

        $src_logo  = file_exists($root_dir . '/images/pictures/logo_custom.png')
            ? 'images/pictures/logo_custom.png'
            : 'images/pictures/logo.png';
        $bg_custom = $root_dir . '/images/pictures/bg_custom.jpg';

        return [
            'external_url' => rtrim(CApp::getBaseUrl(), '/') . '/',
            'src_logo'     => $src_logo,
            'bg_custom'    => $bg_custom,
            'bg'           => is_file($bg_custom),
        ];
    }
}
