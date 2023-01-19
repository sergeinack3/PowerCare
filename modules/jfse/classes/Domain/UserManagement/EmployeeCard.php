<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\UserManagement;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class EmployeeCard extends AbstractEntity
{
    /** @var string */
    protected $id;

    /** @var string */
    protected $establishment_id;

    /** @var string */
    protected $name;

    /** @var string */
    protected $invoicing_number;

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
    public function getEstablishmentId(): ?string
    {
        return $this->establishment_id;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getInvoicingNumber(): ?string
    {
        return $this->invoicing_number;
    }
}
