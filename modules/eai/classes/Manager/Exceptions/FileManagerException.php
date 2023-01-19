<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */


/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Manager\Exceptions;

use Ox\Core\CMbException;

/**
 * Description
 */
class FileManagerException extends CMbException
{
    /** @var int */
    public const CONTENT_EMPTY = 1;
    /** @var int */
    public const NO_TARGET_OBJECT = 2;
    /** @var int */
    public const INVALID_STORE_FILE = 3;
    /** @var int */
    public const FILE_CONTEXT_DIVERGENCE = 4;
    /** @var int  */
    public const INVALID_STORE_IDEX = 5;

    /** @var int */
    private int    $id;
    private ?string $msg;

    /**
     * FileManagerException constructor.
     *
     * @param int    $id      of exception
     * @param string ...$args optional args
     */
    public function __construct(int $id, string ...$args)
    {
        parent::__construct("FileManagerException-$id", ...$args);

        $this->code = $this->id  = $id;
        $this->msg = $args[0] ?? null;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getMsg(): ?string
    {
        return $this->msg;
    }
}

