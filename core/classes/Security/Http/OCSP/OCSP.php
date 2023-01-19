<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Security\Http\OCSP;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbSecurity;
use Ox\Core\Security\Http\OCSP\Exceptions\CannotCheckCertificate;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * OCSP Class.
 * Checks whether the certificate is revoked or not.
 */
class OCSP
{
    /** @var CacheInterface $cache Caching method */
    private CacheInterface $cache;

    /**
     * @param CacheInterface $cache Caching method to use.
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Check revocation certificate for a specific url.
     *
     * @param string $url Url to be checked.
     *
     * @return bool
     * @throws CMbException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function check(string $url): bool
    {
        if ($url === '') {
            throw new CMbException('OCSP-Error-Url is empty');
        }

        // Create a key based on url to be checked.
        $key        = CMbSecurity::hash(CMbSecurity::SHA1, $url);
        $is_revoked = $this->cache->get($key);

        if ($is_revoked === null) {
            $is_revoked = $this->checkIsRevoked($url);

            $this->cache->set($key, $is_revoked, CMbDT::SECS_PER_HOUR * 6);
        }

        return $is_revoked;
    }

    /**
     * Use OCSPChecker to verify if certificate is revoked or not.
     *
     * @param string $url Url to be checked.
     *
     * @return bool
     * @throws Exception
     */
    private function checkIsRevoked(string $url): bool
    {
        try {
            // Set verifyPeer to false.
            OCSPChecker::setOption(CURLOPT_SSL_VERIFYPEER, false);

            // Get OCSP Response from an url.
            $ocsp_response = (OCSPChecker::fromURL($url))->check();

            // Return revoked response.
            return ($ocsp_response->isRevoked() === true);
        } catch (CannotCheckCertificate $e) {
            // If it is not possible to verify the certificate for a technical reason, allows access.
            CApp::log($e->getMessage());
            return false;
        } catch (Exception $e) {
            // Other exceptions are denied access.
            return true;
        }
    }
}
