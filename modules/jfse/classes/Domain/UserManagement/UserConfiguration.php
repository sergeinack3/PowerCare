<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\UserManagement;

use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class UserConfiguration extends AbstractEntity
{
    /** @var User */
    protected $user;

    /** @var string  */
    protected $conventions_folder_path;

    /** @var array */
    protected $cerfas_list;

    /** @var UserParameter[] */
    protected $parameters;

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getConventionsFolderPath(): string
    {
        return $this->conventions_folder_path;
    }

    /**
     * @return array
     */
    public function getCerfasList(): array
    {
        return $this->cerfas_list;
    }

    /**
     * @return UserParameter[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
