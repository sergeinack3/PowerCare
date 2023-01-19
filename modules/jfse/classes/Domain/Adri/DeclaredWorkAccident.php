<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Adri;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

final class DeclaredWorkAccident extends AbstractEntity
{
    /** @var int */
    protected $number;

    /** @var string */
    protected $id;

    /** @var string */
    protected $code;

    /** @var string */
    protected $organism;

    /**
     * @return int
     */
    public function getNumber(): ?int
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getOrganism(): ?string
    {
        return $this->organism;
    }
}
