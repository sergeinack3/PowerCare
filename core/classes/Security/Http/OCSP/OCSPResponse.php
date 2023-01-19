<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Security\Http\OCSP;

use DateTimeImmutable;
use Ocsp\Response;

/**
 * OCSPChecker Class.
 * Response to an OCSP call.
 */
class OCSPResponse
{
    /** @var Response */
    private $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Is the certificate revoked (NULL: unknown)?
     *
     * @return bool|null
     */
    public function isRevoked(): ?bool
    {
        return $this->response->isRevoked();
    }

    /**
     * Get the revocation date/time (not null only if the certificate is revoked).
     *
     * @return DateTimeImmutable|null
     */
    public function getRevokedOn(): ?DateTimeImmutable
    {
        return $this->response->getRevokedOn();
    }

    /**
     * Get the revocation reason (if revoked).
     *
     * @return int|null
     */
    public function getRevocationReason(): ?int
    {
        return $this->response->getRevocationReason();
    }

    /**
     * Get the serial number of the certificate.
     *
     * @return string
     */
    public function getCertificateSerialNumber(): string
    {
        return $this->response->getCertificateSerialNumber();
    }

    /**
     * Get the most recent time at which the status being indicated is known by the responder to have been correct.
     *
     * @return DateTimeImmutable
     */
    public function getValidatedDatetime(): DateTimeImmutable
    {
        return $this->response->getThisUpdate();
    }
}
