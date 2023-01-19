<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\OAuth2\OIDC\PSC;

/**
 * PSC UserInfo container.
 *
 * Contains:
 * - Sub
 * - Id (RPPS or Adeli)
 * - Family name
 * - Given name
 * - Other ids (custom)
 */
class UserInfo
{
    /** @var string|null */
    private $sub;

    /** @var string|null */
    private $id;

    /** @var string|null */
    private $family_name;

    /** @var string|null */
    private $given_name;

    /** @var array */
    private $other_ids;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->sub         = $data['sub'] ?? null;
        $this->id          = $data['SubjectNameID'] ?? null;
        $this->family_name = $data['family_name'] ?? null;
        $this->given_name  = $data['given_name'] ?? null;
        $this->other_ids   = $data['otherIds'] ?? [];
    }

    public function getSub(): ?string
    {
        return $this->sub;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        $name = trim("{$this->family_name} {$this->given_name}");

        return ($name === '') ? null : $name;
    }

    /**
     * @return bool
     */
    public function isIdRpps(): bool
    {
        if ($this->id === null) {
            return false;
        }

        foreach ($this->other_ids as $_info_id) {
            if ($this->id === $_info_id['identifiant']) {
                return $_info_id['origine'] === 'RPPS';
            }
        }

        return false;
    }

    public function getRppsOrAdeli(): ?string
    {
        if ($this->id === null) {
            return null;
        }

        return ltrim($this->id, '8');
    }
}
