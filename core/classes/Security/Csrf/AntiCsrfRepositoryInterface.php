<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Security\Csrf;

use Ox\Core\Security\Csrf\Exceptions\CouldNotGetCsrfToken;
use Ox\Core\Security\Csrf\Exceptions\CouldNotUseCsrf;

/**
 * Description
 */
interface AntiCsrfRepositoryInterface
{
    /**
     * @param string $identifier
     * @param string $secret
     *
     * @throws CouldNotUseCsrf
     */
    public function init(string $identifier, string $secret): void;

    /**
     * @param string $identifier
     *
     * @return string
     * @throws CouldNotUseCsrf
     */
    public function getSecret(string $identifier): string;

    /**
     * @param string        $identifier
     * @param AntiCsrfToken $token
     * @param int           $ttl
     *
     * @throws CouldNotUseCsrf
     */
    public function persistToken(string $identifier, AntiCsrfToken $token, int $ttl): void;

    /**
     * @param string $identifier
     * @param string $candidate
     *
     * @return AntiCsrfToken
     * @throws CouldNotUseCsrf
     * @throws CouldNotGetCsrfToken
     */
    public function retrieveToken(string $identifier, string $candidate): AntiCsrfToken;

    /**
     * @param string        $identifier
     * @param AntiCsrfToken $token
     *
     * @return bool
     * @throws CouldNotUseCsrf
     */
    public function invalidateToken(string $identifier, AntiCsrfToken $token): bool;
}
