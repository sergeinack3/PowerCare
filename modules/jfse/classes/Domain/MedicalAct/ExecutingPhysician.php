<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Domain\MedicalAct;

use Ox\Mediboard\Jfse\Domain\PrescribingPhysician\Physician;

class ExecutingPhysician extends Physician
{
    /** @var string */
    protected $id;

    /** @var int */
    protected $convention;

    /** @var int */
    protected $pricing_zone;

    /** @var string */
    protected $practice_condition;

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getConvention(): ?int
    {
        return $this->convention;
    }

    /**
     * @return int
     */
    public function getPricingZone(): ?int
    {
        return $this->pricing_zone;
    }

    /**
     * @return string
     */
    public function getPracticeCondition(): ?string
    {
        return $this->practice_condition;
    }

    /**
     * Checks if the given convention code is valid
     *
     * @param int $code
     *
     * @return bool
     */
    public static function checkConvention(int $code): bool
    {
        return $code >= 0 && $code <= 3;
    }

    /**
     * Checks if the given pricing zone code is valid (ie a 2 digit string, different from 00)
     *
     * @param string $code
     *
     * @return bool
     */
    public static function checkPricingZone(string $code): bool
    {
        return strlen($code) === 2 && intval($code);
    }

    /**
     * Checks if the given practice condition code is valid (a 2 digit numerical string)
     *
     * @param string $code
     *
     * @return bool
     */
    public static function checkPracticeCondition(string $code): bool
    {
        return strlen($code) === 2 && is_numeric($code);
    }
}
