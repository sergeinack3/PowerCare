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
use Ox\Core\Specification\Enum;
use Ox\Core\Specification\InstanceOfX;
use Ox\Core\Specification\IsNull;
use Ox\Core\Specification\MaxLength;
use Ox\Core\Specification\NotNull;
use Ox\Core\Specification\OrX;
use Ox\Core\Specification\SpecificationInterface;
use Ox\Mediboard\Cabinet\Vaccination\CVaccination;

/**
 * External injection spec builder
 */
class InjectionSpecBuilder
{
    private const FIELD_ID             = 'external_id';
    private const FIELD_PATIENT        = 'patient_id';
    private const FIELD_INJECTION_DATE = 'injection_date';
    private const FIELD_TYPE_VACCIN    = '_type_vaccin';

    public function build(): SpecificationInterface
    {
        return new AndX(
            ...[
                   NotNull::is(self::FIELD_ID),
                   NotNull::is(self::FIELD_PATIENT),
                   NotNull::is(self::FIELD_INJECTION_DATE),
                   InstanceOfX::is(self::FIELD_INJECTION_DATE, DateTime::class),
                   $this->getTypeSpec(),
               ]
        );
    }

    private function getTypeSpec(): SpecificationInterface
    {
        return new OrX(
            Enum::is(self::FIELD_TYPE_VACCIN, CVaccination::TYPES_VACCINATIONS),
            MaxLength::is(self::FIELD_TYPE_VACCIN, 80),
            IsNull::is(self::FIELD_TYPE_VACCIN)
        );
    }
}
