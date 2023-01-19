<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Elastic\IndexLifeManagement\Phases;

use Ox\Core\Units\TimeUnitEnum;

/**
 *  This abstract class define the base of an ILM phase with the minimum age.
 */
abstract class AbstractElasticPhase
{
    protected int          $min_age;
    protected TimeUnitEnum $min_age_unit;

    public function __construct(int $min_age, TimeUnitEnum $min_age_unit)
    {
        $this->min_age      = $min_age;
        $this->min_age_unit = $min_age_unit;
    }

    public function build(): array
    {
        return [
            "min_age" => $this->min_age . $this->min_age_unit,
        ];
    }
}
