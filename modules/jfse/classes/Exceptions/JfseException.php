<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Exceptions;

use Ox\Core\CAppUI;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Ox\Mediboard\Jfse\Utils;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Class JfseException
 *
 * @todo    Generate a user log when an exception is thrown
 *
 * @package Ox\Mediboard\Jfse\Exceptions
 */
abstract class JfseException extends RuntimeException
{
    /** @var string The locales */
    protected $locale;

    /** @var mixed Optional arguments for the localization */
    protected $locale_args;

    /**
     * JfseException constructor.
     *
     * @param string    $name        The name of the exception
     * @param string    $locale      The locales to translate the error message
     * @param array     $locale_args [optional] Additional arguments for the localization
     * @param int       $code        [optional] The Exception code.
     * @param Throwable $previous    [optional] The previous throwable used for the exception chaining.
     */
    public function __construct(
        string $name,
        string $locale,
        array $locale_args = [],
        int $code = 0,
        Throwable $previous = null
    ) {
        $this->locale      = $locale;
        $this->locale_args = $locale_args;

        parent::__construct($name, $code, $previous);
    }

    /**
     * Returns a SmartyResponse that will display the error message, or a JsonResponse that will contain the message
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        if (Utils::isJsonResponseExpected()) {
            $response = new JsonResponse(['error' => utf8_encode($this->getLocalizedMessage())]);
        } else {
            $response = new SmartyResponse(
                'inc_message',
                ['message' => $this->getLocalizedMessage(), 'type' => 'error']
            );
        }

        return $response;
    }

    /**
     * Returns the translation of the message
     *
     * @return string
     */
    public function getLocalizedMessage(): string
    {
        return CAppUI::tr($this->locale, $this->locale_args);
    }
}
