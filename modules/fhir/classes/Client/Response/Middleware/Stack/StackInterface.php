<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Client\Response\Middleware\Stack;

use Ox\Interop\Fhir\Client\Response\Middleware\MiddlewareInterface;

interface StackInterface
{
    /**
     * @return MiddlewareInterface
     */
    public function next(): MiddlewareInterface;
}
