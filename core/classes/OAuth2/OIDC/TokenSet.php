<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\OAuth2\OIDC;

/**
 * Simple class holding OIDC tokens (access, refresh and id).
 */
class TokenSet
{
    public const PSC_TYPE = 'psc';
    public const FC_TYPE  = 'fc';

    private string $type;

    private string $access_token;

    private ?string $refresh_token = null;

    private string $id_token;

    /**
     * @param string      $type
     * @param string      $access_token
     * @param string|null $refresh_token
     * @param string      $id_token
     */
    public function __construct(string $type, string $access_token, ?string $refresh_token, string $id_token)
    {
        $this->type          = $type;
        $this->access_token  = $access_token;
        $this->refresh_token = $refresh_token;
        $this->id_token      = $id_token;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAccessToken(): string
    {
        return $this->access_token;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refresh_token;
    }

    public function getIdToken(): string
    {
        return $this->id_token;
    }

    public function setAccessToken(string $access_token): void
    {
        $this->access_token = $access_token;
    }

    public function setRefreshToken(string $refresh_token): void
    {
        $this->refresh_token = $refresh_token;
    }
}
