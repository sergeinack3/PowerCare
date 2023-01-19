<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\UserManagement;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

/**
 * Represents a user parameter
 *
 * @package Ox\Mediboard\Jfse\Domain\UserManagement
 */
class UserParameter extends AbstractEntity
{
    /** @var int */
    protected $id;

    /** @var string The name of the parameter */
    protected $name;

    /** @var mixed The value of the parameter */
    protected $value;

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
