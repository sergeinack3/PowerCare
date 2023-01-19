<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Vital;

use JsonSerializable;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;

class Patient extends AbstractEntity implements JsonSerializable
{
    /** @var string */
    protected $last_name;

    /** @var string */
    protected $birth_name;

    /** @var string */
    protected $birth_date;

    /** @var string */
    protected $first_name;

    /** @var string */
    protected $address;

    /** @var string */
    protected $zip_code;

    /** @var string */
    protected $city;

    /** @var string */
    protected $birth_rank;

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function getBirthDate(): ?string
    {
        return $this->birth_date;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function getBirthRank(): ?string
    {
        return $this->birth_rank ?? "1";
    }

    public function getBirthName(): ?string
    {
        return $this->birth_name;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getZipCode(): ?string
    {
        return $this->zip_code;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function jsonSerialize(): array
    {
        // Sometimes, the birth date has the format Y-m-d and sometime Ymd
        $birth_date = str_replace('-', '', $this->birth_date);
        $year       = substr($birth_date, 0, 4);
        $month      = substr($birth_date, 4, 2);
        $day        = substr($birth_date, 6, 2);

        $data = [
            "first_name" => utf8_encode($this->first_name),
            "last_name"  => utf8_encode($this->last_name),
            "birth_name" => utf8_encode($this->birth_name),
            "birth_date" => $year . '-' . $month . '-' . $day,
            "birth_rank" => $this->birth_rank,
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
