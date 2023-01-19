<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions;

use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Api\Message;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Ox\Mediboard\Jfse\Utils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ApiMessageException extends JfseException
{
    /** @var Message[] */
    private $messages;
    /**
     * JfseException constructor.
     *
     * @param Message[] $messages        The name of the exception
     * @param int       $code        [optional] The Exception code.
     * @param Throwable $previous    [optional] The previous throwable used for the exception chaining.
     */
    public function __construct(
        array $messages,
        int $code = 0,
        Throwable $previous = null
    ) {
        $this->messages = $messages;

        parent::__construct('ApiMessages', '', [], $code, $previous);
    }

    /**
     * Returns a SmartyResponse that will display the error message, or a JsonResponse that will contain the message
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        $messages = [];

        foreach ($this->messages as $message) {
            $messages[] = ['type' => $message->getType(), 'text' => $message->getDescription()];
        }

        if (Utils::isJsonResponseExpected()) {
            $response = new JsonResponse(['messages' => CMbArray::mapRecursive('utf8_encode', $messages)]);
        } else {
            $response = new SmartyResponse('inc_messages', ['messages' => $messages]);
        }

        return $response;
    }
}
