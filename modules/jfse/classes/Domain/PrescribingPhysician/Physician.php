<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\PrescribingPhysician;

use JsonSerializable;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class Physician extends AbstractEntity
{
    /** @var string|null */
    protected $id;

    /** @var string */
    protected $last_name;

    /** @var string */
    protected $first_name;

    /** @var string */
    protected $invoicing_number;

    /** @var int */
    protected $speciality;

    /** @var int */
    protected $type;

    /** @var string|null */
    protected $national_id;

    /** @var string|null */
    protected $structure_id;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function getInvoicingNumber(): ?string
    {
        return $this->invoicing_number;
    }

    public function getSpeciality(): ?int
    {
        return $this->speciality;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function getNationalId(): ?string
    {
        return $this->national_id;
    }

    public function getStructureId(): ?string
    {
        return $this->structure_id;
    }
}
