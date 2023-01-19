<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Client\Response;

use Ox\Interop\Fhir\Resources\CFHIRResource;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class Envelope
 * @package Ox\Interop\Fhir\Client\Response
 */
class Envelope
{
    /** @var Response */
    private $response;

    /** @var array */
    private $data = [];

    /** @var CFHIRResource */
    private $resource;

    /**
     * Envelope constructor.
     *
     * @param CFHIRResource $resource
     * @param array         $objects
     */
    public function __construct(Response $response, array $objects = [])
    {
        $this->response = $response;

        foreach ($objects as $object) {
            if (!is_object($object)) {
                continue;
            }

            $this->data[get_class($object)][] = $object;
        }
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * @return CFHIRResource
     */
    public function getResource(): ?CFHIRResource
    {
        return $this->resource;
    }

    /**
     * @param string $type
     *
     * @return mixed|null
     */
    public function last(string $type)
    {
        if (isset($this->data[$type]) && ($data = $this->data[$type])) {
            return end($data) ?: null;
        }

        return null;
    }

    /**
     * @param object $object
     *
     * @return Envelope
     */
    public function with(object $object): self
    {
        $cloned = clone $this;

        if (!$this->resource && $object instanceof CFHIRResource) {
            $cloned->resource = $object;
        } else {
            $cloned->data[get_class($object)][] = $object;
        }

        return $cloned;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function withoutAll(string $type): self
    {
        $cloned = clone $this;

        unset($cloned->data[$type]);

        return $cloned;
    }


    /**
     * @param string $type
     *
     * @return $this
     */
    public function withoutType(string $type): self
    {
        $cloned = clone $this;

        foreach ($cloned->data as $class => $object) {
            if ($class === $type || is_subclass_of($class, $type)) {
                unset($cloned->data[$class]);
            }
        }

        return $cloned;
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function all(string $type): array
    {
        return $this->data[$type] ?? [];
    }
}
