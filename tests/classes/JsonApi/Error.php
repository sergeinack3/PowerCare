<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Tests\JsonApi;

use Ox\Tests\TestsException;

/**
 * Representation of a JsonApi error for testing
 */
class Error
{
    public const ERRORS = 'errors';
    public const TYPE = 'type';
    public const CODE = 'code';
    public const MESSAGE = 'message';

    private ?string $type;
    private ?int    $code;
    private ?string $message;

    public function __construct(?string $type, ?int $code, ?string $message)
    {
        $this->type    = $type;
        $this->code    = $code;
        $this->message = $message;
    }

    public static function createFromArray(array $error): self
    {
        if (!isset($error[static::ERRORS])) {
            throw new TestsException('Errors must be the first key for errors.');
        }

        return new static(
            $error[static::ERRORS][static::TYPE] ?? null,
            $error[static::ERRORS][static::CODE] ?? null,
            $error[static::ERRORS][static::MESSAGE] ?? null
        );
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}
