<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Vital;

use JsonSerializable;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class Insured extends AbstractEntity implements JsonSerializable
{
    /** @var string */
    protected $nir;

    /** @var string */
    protected $nir_key;

    /** @var string */
    protected $last_name;

    /** @var string */
    protected $first_name;

    /** @var string */
    protected $birth_name;

    /** @var string */
    protected $regime_code;

    /** @var string */
    protected $regime_label;

    /** @var string */
    protected $managing_fund;

    /** @var string */
    protected $managing_center;

    /** @var string */
    protected $managing_code;

    /** @var string */
    protected $situation_code;

    /** @var string */
    protected $address;

    /** @var string */
    protected $zip_code;

    /** @var string */
    protected $city;

    public function getNir(): ?string
    {
        return $this->nir;
    }

    public function getNirKey(): ?string
    {
        return $this->nir_key;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function getBirthName(): ?string
    {
        return $this->birth_name;
    }

    public function getRegimeCode(): ?string
    {
        return $this->regime_code;
    }

    public function getRegimeLabel(): ?string
    {
        return $this->regime_label ?? "";
    }

    public function getManagingFund(): ?string
    {
        return $this->managing_fund;
    }

    public function getManagingCenter(): ?string
    {
        return $this->managing_center;
    }

    public function getManagingCode(): ?string
    {
        return $this->managing_code;
    }

    /**
     * Sets the managing code, and set it to a default value if the given value is null
     *
     * @param string|null $managing_code
     *
     * @return $this
     */
    public function setManagingCode(?string $managing_code): self
    {
        $managing_code = $managing_code ?: '10';

        $this->managing_code = $managing_code;

        return $this;
    }

    public function getSituationCode(): ?string
    {
        return $this->situation_code;
    }

    /**
     * @return string
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getZipCode(): ?string
    {
        return $this->zip_code;
    }

    /**
     * @return string
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    public function jsonSerialize(): array
    {
        $data = [
            'nir'             => $this->nir,
            'nir_key'         => $this->nir_key,
            'last_name'       => utf8_encode($this->last_name ?? ''),
            'first_name'      => utf8_encode($this->first_name ?? ''),
            'birth_name'      => utf8_encode($this->birth_name ?? ''),
            'regime_code'     => $this->regime_code,
            'regime_label'    => utf8_encode($this->regime_label ?? ''),
            'managing_fund'   => $this->managing_fund,
            'managing_center' => $this->managing_center,
            'managing_code'   => $this->managing_code,
            'situation_code'  => $this->situation_code,
        ];

        if ($this->address && trim($this->address) !== '') {
            $data['address'] = utf8_encode(trim($this->address));
        }

        if ($this->zip_code && '' !== $this->zip_code) {
            $data['zip_code'] = $this->zip_code;
        }

        if ($this->city && '' !== $this->city) {
            $data['city'] = utf8_encode($this->city);
        }

        return $data;
    }
}
