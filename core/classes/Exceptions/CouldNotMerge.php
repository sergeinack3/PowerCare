<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Exceptions;

/**
 * Description
 */
class CouldNotMerge extends MergeException
{
    public static function invalidNumberOfObjects(): self
    {
        return new static('mergeTooFewObjects');
    }

    public static function storeFailure(string $message): self
    {
        return new static($message);
    }

    public static function backrefsTransferFailure(string $message): self
    {
        return new static($message);
    }

    public static function deleteFailure(string $message): self
    {
        return new static($message);
    }

    public static function idexStoreFailure(string $message): self
    {
        return new static($message);
    }

    public static function domainMergeImpossible(): self
    {
        return new static('CDomain-merge_impossible');
    }

    public static function groupDomainMergeImpossible(): self
    {
        return new static('CGroupDomain-merge_impossible');
    }

    public static function mergeImpossible(): self
    {
        return new static('CMediusers-merge-impossible');
    }

    public static function baseObjectNotFound(): self
    {
        return new static('common-error-Object not found');
    }

    public static function objectNotFound(): self
    {
        return new static('common-error-Object not found');
    }

    public static function differentType(string $object_class, string $base_class): self
    {
        return new self(
            "An object from type '{$object_class}' can't be merge with an object from type '{$base_class}'"
        );
    }
}
