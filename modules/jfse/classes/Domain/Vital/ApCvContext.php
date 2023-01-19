<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Vital;

use DateTimeImmutable;
use JsonSerializable;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use ReturnTypeWillChange;

class ApCvContext extends AbstractEntity implements JsonSerializable
{
    /** @var string */
    protected $identifier;

    /** @var string */
    protected $token;

    /** @var DateTimeImmutable */
    protected $expiration_date;

    /**
     * @return string
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getExpirationDate(): ?DateTimeImmutable
    {
        return $this->expiration_date;
    }

    /**
     * @return int
     */
    public function getLifeExpectancy(): int
    {
        $duration = 1800;
        if ($this->expiration_date && $this->expiration_date instanceof DateTimeImmutable) {
            $duration = $this->expiration_date->getTimestamp() - (new DateTimeImmutable())->getTimestamp();
        }

        return $duration;
    }

    public function isExpired(): bool
    {
        return new DateTimeImmutable() > $this->expiration_date;
    }

    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'identifier'      => $this->identifier,
            'token'           => $this->token,
            'expiration_date' => $this->expiration_date->format('Y-m-d H:i:s'),
        ];
    }
}
