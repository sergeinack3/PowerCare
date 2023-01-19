<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\ProofAmo;

use Cassandra\Date;
use DateTime;

/**
 * Class ProofAmo
 *
 * @package Ox\Mediboard\Jfse\Domain\ProofAmo
 */
class ProofAmo extends \Ox\Mediboard\Jfse\Domain\AbstractEntity
{
    public const NO_PROOF_AMO      = 0;
    public const DOCUMENT          = 1;
    public const TELEMATIC_CONSULT = 2;
    public const VITAL_CARD        = 4;

    /** @var int */
    protected $nature;

    /** @var DateTime */
    protected $date;

    /** @var int */
    protected $origin;

    public function getNature(): ?int
    {
        return $this->nature;
    }

    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    public function getOrigin(): ?int
    {
        return $this->origin;
    }
}
