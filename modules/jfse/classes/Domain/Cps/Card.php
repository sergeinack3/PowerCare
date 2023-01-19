<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\Cps;

use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Domain\AbstractEntity;
use Ox\Mediboard\Jfse\Domain\Substitute\Substitute;
use Ox\Mediboard\Jfse\Exceptions\Cps\CpsException;

/**
 * Represents the CPS card
 *
 * @package Ox\Mediboard\Jfse\Domain\Cps
 */
final class Card extends AbstractEntity
{
    protected const CARD_TYPE_CPS = 0;

    /** @var int The code of the type of card */
    protected $type_code;

    /** @var string The label of the type of card */
    protected $type_label;

    /** @var int The code of the type of national identification number */
    protected $national_identification_type_code;

    /** @var string The label of the type of national identification number */
    protected $national_identification_type_label;

    /** @var string The nation identification number */
    protected $national_identification_number;

    /** @var string The nation identification key */
    protected $national_identification_key;

    /** @var int The civility code */
    protected $civility_code;

    /** @var string The civility label */
    protected $civility_label;

    /** @var string The last name */
    protected $last_name;

    /** @var string The first name */
    protected $first_name;

    /** @var Situation[] The list of situation */
    protected $situations;

    /** @var int[] The list of unauthorized speciality numbers */
    protected static $unauthorized_specialities = [30, 39, 40, 50, 51, 60, 61, 62, 63, 64, 65, 66, 67, 68];

    /**
     * @return int
     */
    public function getTypeCode(): int
    {
        return $this->type_code;
    }

    /**
     * @return string
     */
    public function getTypeLabel(): string
    {
        return $this->type_label;
    }

    /**
     * @return int
     */
    public function getNationalIdentificationTypeCode(): int
    {
        return $this->national_identification_type_code;
    }

    /**
     * @return string
     */
    public function getNationalIdentificationTypeLabel(): string
    {
        return $this->national_identification_type_label;
    }

    /**
     * @return string
     */
    public function getNationalIdentificationNumber(): string
    {
        return $this->national_identification_number;
    }

    /**
     * @return string
     */
    public function getNationalIdentificationKey(): string
    {
        return $this->national_identification_key;
    }

    /**
     * @return int
     */
    public function getCivilityCode(): int
    {
        return $this->civility_code;
    }

    /**
     * @return string
     */
    public function getCivilityLabel(): string
    {
        return $this->civility_label;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->last_name;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->first_name;
    }

    /**
     * @return Situation[]
     */
    public function getSituations(): array
    {
        return $this->situations;
    }

    /**
     * @return bool
     */
    public function hasSituations(): bool
    {
        return !empty($this->situations);
    }

    /**
     * @return int
     */
    public function countSituations(): int
    {
        return count($this->situations);
    }

    /**
     * @param int $id The id of the situation
     *
     * @return bool
     */
    public function hasSituationWithId(int $id): bool
    {
        return array_key_exists($id, $this->situations);
    }

    /**
     * Select the situation with the given id (by deleting from the array of situations all the other situations)
     *
     * @param int $id The id of the situation
     *
     * @return void
     */
    public function selectSituation(int $id): void
    {
        if (!$this->hasSituationWithId($id)) {
            throw CpsException::unknownSituationId($id);
        }

        $this->situations = [$id => $this->situations[$id]];
    }

    /**
     * Checks if the speciality of the situation is authorized
     *
     * @return bool
     */
    public function isSpecialityAuthorized(): bool
    {
        if ($this->countSituations()) {
            $situation = reset($this->situations);
            if (in_array((int)$situation->getSpecialityCode(), self::$unauthorized_specialities)) {
                return false;
            }
        }

        return true;
    }

    public function hasSubstitutionSession(): bool
    {
        $has_session = false;
        foreach ($this->situations as $situation) {
            if ($situation->getUser() && $situation->getUser()->getSubstitutionSession()) {
                $has_session = true;
            }
        }

        return $has_session;
    }

    public function getFirstSubstituteNationalId(): ?string
    {
        $substitute_national_id = null;
        foreach ($this->situations as $situation) {
            if ($situation->getUser() && $situation->getUser()->getSubstitutionSession()) {
                $substitute_national_id = $situation->getUser()->getSubstituteRppsNumber();
            }
        }

        return $substitute_national_id;
    }
}
