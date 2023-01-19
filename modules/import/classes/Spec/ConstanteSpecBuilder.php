<?php

/**
 * @package Mediboard\Import
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Spec;

use DateTime;
use Ox\Core\Specification\AndX;
use Ox\Core\Specification\GreaterThanOrEqual;
use Ox\Core\Specification\InstanceOfX;
use Ox\Core\Specification\IsNull;
use Ox\Core\Specification\LessThanOrEqual;
use Ox\Core\Specification\NotNull;
use Ox\Core\Specification\OrX;
use Ox\Core\Specification\SpecificationInterface;

/**
 * Description
 */
class ConstanteSpecBuilder
{
    private const FIELD_ID = 'external_id';

    private const FIELD_USER_ID                       = 'user_id';
    private const FIELD_PATIENT_ID                    = 'patient_id';
    private const FIELD_DATETIME                      = 'datetime';
    private const FIELD_TAILLE                        = 'taille';
    private const FIELD_POIDS                         = 'poids';
    private const FIELD_PULSE                         = 'pouls';
    private const FIELD_TEMPERATURE                   = 'temperature';
    private const FIELD_BLOOD_PRESSURE_SYSTOLE_RIGHT  = 'ta_droit_systole';
    private const FIELD_BLOOD_PRESSURE_DIASTOLE_RIGHT = 'ta_droit_diastole';
    private const FIELD_BLOOD_PRESSURE_SYSTOLE_LEFT   = 'ta_gauche_systole';
    private const FIELD_BLOOD_PRESSURE_DIASTOLE_LEFT  = 'ta_gauche_diastole';
    private const FIELD_SHOE_SIZE                     = 'pointure';

    public function build(): ?SpecificationInterface
    {
        return new AndX(
            ...[
                   NotNull::is(self::FIELD_ID),
                   NotNull::is(self::FIELD_PATIENT_ID),
                   $this->buildDateTimeSpec(),
                   $this->buildTailleSpec(),
                   $this->buildPoidsSpec(),
                   $this->buildPulseSpec(),
                   $this->buildTemperatureSpec(),
                   $this->buildBloodPressureRightSpec(),
                   $this->buildBloodPressureLeftSpec(),
                   $this->buildShoeSizeSpec()
               ]
        );
    }

    private function buildDateTimeSpec(): SpecificationInterface
    {
        return new OrX(
            ...[
                   IsNull::is(self::FIELD_DATETIME),
                   InstanceOfX::is(self::FIELD_DATETIME, DateTime::class),
               ]
        );
    }

    private function buildTailleSpec(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_TAILLE),
            new AndX(
                GreaterThanOrEqual::is(self::FIELD_TAILLE, 20),
                LessThanOrEqual::is(self::FIELD_TAILLE, 300),
            )
        );
    }

    private function buildPoidsSpec(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_POIDS),
            LessThanOrEqual::is(self::FIELD_POIDS, 500),
        );
    }

    private function buildPulseSpec(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_PULSE),
            new AndX(
                GreaterThanOrEqual::is(self::FIELD_PULSE, 20),
                LessThanOrEqual::is(self::FIELD_PULSE, 400),
            )
        );
    }

    private function buildTemperatureSpec(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_TEMPERATURE),
            new AndX(
                GreaterThanOrEqual::is(self::FIELD_TEMPERATURE, 20),
                LessThanOrEqual::is(self::FIELD_TEMPERATURE, 50),
            )
        );
    }

    private function buildBloodPressureRightSpec(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(
                self::FIELD_BLOOD_PRESSURE_SYSTOLE_RIGHT,
                self::FIELD_BLOOD_PRESSURE_DIASTOLE_RIGHT
            ),
            new AndX(
                LessThanOrEqual::is(self::FIELD_BLOOD_PRESSURE_SYSTOLE_RIGHT, 22),
                LessThanOrEqual::is(self::FIELD_BLOOD_PRESSURE_DIASTOLE_RIGHT, 15)
            )
        );
    }

    private function buildBloodPressureLeftSpec(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(
                self::FIELD_BLOOD_PRESSURE_SYSTOLE_LEFT,
                self::FIELD_BLOOD_PRESSURE_DIASTOLE_LEFT
            ),
            new AndX(
                LessThanOrEqual::is(self::FIELD_BLOOD_PRESSURE_SYSTOLE_LEFT, 22),
                LessThanOrEqual::is(self::FIELD_BLOOD_PRESSURE_DIASTOLE_LEFT, 15)
            )
        );
    }

    public function buildShoeSizeSpec(): SpecificationInterface
    {
        return new OrX(
            IsNull::is(self::FIELD_SHOE_SIZE),
            new AndX(
                GreaterThanOrEqual::is(self::FIELD_SHOE_SIZE, 15),
                LessThanOrEqual::is(self::FIELD_SHOE_SIZE, 57)
            )
        );
    }
}
