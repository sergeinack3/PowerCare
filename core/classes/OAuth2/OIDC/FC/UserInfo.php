<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\OAuth2\OIDC\FC;

/**
 * FC UserInfo container.
 */
class UserInfo
{
    /** @var string|null */
    private $sub;

    /** @var string|null */
    private $given_name;

    /** @var string|null */
    private $family_name;

    /** @var string|null */
    private $birthdate;

    /** @var string|null */
    private $gender;

    /** @var string|null */
    private $birthplace;

    /** @var string|null */
    private $birthcountry;

    /** @var string|null */
    private $preferred_username;

    /** @var string|null */
    private $email;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->sub                = $data['sub'] ?? null;
        $this->given_name         = $data['given_name'] ?? null;
        $this->family_name        = $data['family_name'] ?? null;
        $this->birthdate          = $data['birthdate'] ?? null;
        $this->birthplace         = $data['birthplace'] ?? null;
        $this->gender             = $data['gender'] ?? null;
        $this->birthcountry       = $data['birthcountry'] ?? null;
        $this->preferred_username = $data['preferred_username'] ?? null;
        $this->email              = $data['email'] ?? null;
    }

    public function getSub(): ?string
    {
        return $this->sub;
    }

    public function getName(): ?string
    {
        $name = trim("{$this->family_name} {$this->given_name}");

        return ($name === '') ? null : $name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}
