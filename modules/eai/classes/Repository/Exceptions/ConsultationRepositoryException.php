<?php

/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Repository\Exceptions;

use Ox\Core\CMbException;

/**
 * Description
 */
class ConsultationRepositoryException extends CMbException
{
    /** @var int */
    public const PATIENT_DIVERGENCE_FOUND = 1;

    /** @var int */
    private int $id;
    private ?string $msg;

    /**
     * FileManagerException constructor.
     *
     * @param int    $id      of exception
     * @param string ...$args optional args
     */
    public function __construct(int $id, string ...$args)
    {
        parent::__construct("ConsultationRepositoryException-$id", ...$args);

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
