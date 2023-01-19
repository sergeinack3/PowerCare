<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Api;

use Ox\Mediboard\Jfse\Exceptions\ApiException;
use Ox\Mediboard\Jfse\Utils;

/**
 * Class Request
 *
 * @package Ox\Mediboard\Jfse
 */
final class Request
{
    /** @var string The value for the JSON return type */
    public const RETURN_TYPE_JSON = '';
    /** @var string The value for the PDF return type */
    public const RETURN_TYPE_PDF = 'PDF';

    /** @var string The name of the API method */
    private $method;
    /** @var array The content of the request */
    private $content;

    /** @var bool */
    private $force_object = true;

    /**
     * Request constructor.
     */
    private function __construct()
    {
        $this->content = [
            'integrator' => [
                'name'          => Utils::getEditorName(),
                'key'           => Utils::getEditorKey(),
                'etablissement' => Utils::getGroupName(),
                'os'            => Utils::getOperatingSystem(),
            ],
            'method'     => [],
            'cardReader' => [
                'id'        => Utils::getResidentUid(),
                'protocol'  => 'PCSC',
                'channel'   => '0',
                'reader'    => '0'
            ],
            'returnMode' => [
                'mode' => 1,
                'URL'  => ''
            ],
            'idJfse'     => Utils::getJfseUserId(),
        ];
    }

    /**
     * @param string $method        The method's name
     * @param array  $parameters    The parameters of the method call
     * @param bool   $service       Flag that indicate if the method is called as a service or must return an HTML
     *                              response
     * @param string $return_type   The type of return. JSON by default, but can return a PDF (depending on the called
     *                              method)
     * @param bool   $cancel        A flag to indicate that we are in the context of a cancellation (of a FSE for
     *                              example)
     * @param bool   $asynchronous  A flag that indicate if the method must be executed asynchronously
     *
     * @return $this
     */
    private function setMethodData(
        string $method,
        array $parameters = [],
        bool $service = true,
        ?string $return_type = self::RETURN_TYPE_JSON,
        bool $cancel = false,
        bool $asynchronous = false
    ): self {
        $this->method = $method;

        $this->content['method'] = [
            'name'       => $method,
            'service'    => $service,
            'parameters' => array_map_recursive(function ($value) {
                return is_string($value) ? utf8_encode($value) : $value;
            }, $parameters)
        ];

        if ($return_type) {
            $this->content['method']['returnType'] =
                ($return_type !== self::RETURN_TYPE_JSON && $return_type !== self::RETURN_TYPE_PDF)
                    ? self::RETURN_TYPE_JSON : $return_type;
        }

        if ($cancel) {
            $this->content['method']['cancel'] = $cancel;
        }

        if ($asynchronous) {
            $this->content['method']['asynchronous'] = $asynchronous;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    public function setForceObject(bool $force_object): self
    {
        $this->force_object = $force_object;

        return $this;
    }

    /**
     * Encode the content in JSON and returns it
     *
     * @return false|string
     */
    public function getContent()
    {
        if ($this->force_object) {
            $content = @json_encode($this->content, JSON_FORCE_OBJECT);
        } else {
            $content = @json_encode($this->content);
        }


        if (!$content) {
            throw ApiException::requestForgeError(json_last_error_msg(), $this->content);
        }

        return $content;
    }

    /**
     * @param string $method        The jFSE method's name
     * @param array  $parameters    The parameters of the method call
     * @param bool   $service       Flag that indicate if the method is called as a service or must return an HTML
     *                              response
     * @param string $return_type   The type of return. JSON by default, but can return a PDF (depending on the called
     *                              method)
     * @param bool   $cancel        A flag to indicate that we are in the context of a cancellation (of a FSE for
     *                              example)
     * @param bool   $asynchronous  A flag that indicate if the method must be executed asynchronously
     *
     * @return Request
     */
    public static function forge(
        string $method,
        array $parameters = [],
        bool $service = true,
        ?string $return_type = null,
        bool $cancel = false,
        bool $asynchronous = false
    ): self {
        return (new self())->setMethodData($method, $parameters, $service, $return_type, $cancel, $asynchronous);
    }
}
