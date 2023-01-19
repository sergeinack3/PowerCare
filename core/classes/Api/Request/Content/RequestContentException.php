<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Api\Request\Content;

use Ox\Core\Api\Exceptions\ApiException;

class RequestContentException extends ApiException
{
    public static function unableToDecodeContent(): self
    {
        return new self('Unable to decode json from request body');
    }

    public static function dataNodeIsMandatory(): self
    {
        return new self('The body content must have a data node at the top level');
    }

    public static function itemKeyIsMandatory(string $key): self
    {
        return new self("The key {$key} must be present in the item");
    }

    public static function contentIsNotJsonApi(): self
    {
        return new self('The body content is not a valid JsonApi or the header Content-Type is not valid');
    }

    public static function requestedClassIsNotModelObject(string $class): self
    {
        return new self("The requested class {$class} is not a child of CModelObject");
    }

    public static function idMustNotBeProvidedForCreation(): self
    {
        return new self('It is not possible to create an object with a fixed ID');
    }

    public static function tooManyObjects(int $limit): self
    {
        return new self("There is too many objects in the request body, the limit is {$limit}");
    }

    public static function requestedClassTypeIsNotTheSameAsResourceType(
        string $actual_type,
        string $expected_type
    ): self {
        return new self(
            "The requested class have a type {$expected_type} when the actual resource type is {$actual_type}"
        );
    }
}
