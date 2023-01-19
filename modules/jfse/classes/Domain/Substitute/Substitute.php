<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Substitute;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

/**
 * Represents a substitute practitioner
 *
 * @package Ox\Mediboard\Jfse\Domain\CPS
 */
final class Substitute extends AbstractEntity
{
    /** @var int */
    protected $id;

    /** @var int */
    protected $user_id;

    /** @var string */
    protected $last_name;

    /** @var string */
    protected $first_name;

    /** @var string */
    protected $invoicing_number;

    /** @var string */
    protected $national_id;

    /** @var int */
    protected $situation_id;

    /** @var Session[] */
    protected $sessions;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    /**
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    /**
     * @return string
     */
    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    /**
     * @return string
     */
    public function getInvoicingNumber(): ?string
    {
        return $this->invoicing_number;
    }

    /**
     * @return string
     */
    public function getNationalId(): ?string
    {
        return $this->national_id;
    }

    /**
     * @return int
     */
    public function getSituationId(): ?int
    {
        return $this->situation_id;
    }

    /**
     * @return Session[]
     */
    public function getSessions(): ?array
    {
        return $this->sessions;
    }
}
