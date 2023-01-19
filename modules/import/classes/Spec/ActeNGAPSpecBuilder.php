<?php

/**
 * @package Mediboard\Import
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Spec;

use DateTime;
use Ox\Core\Specification\AbstractCompositeSpecification;
use Ox\Core\Specification\AndX;
use Ox\Core\Specification\Enum;
use Ox\Core\Specification\InstanceOfX;
use Ox\Core\Specification\IsNull;
use Ox\Core\Specification\NotNull;
use Ox\Core\Specification\OrX;
use Ox\Core\Specification\SpecificationInterface;

class ActeNGAPSpecBuilder
{
    private const FIELD_ID = 'external_id';

    private const FIELD_EXECUTANT_ID     = 'executant_id';
    private const FIELD_CONSULTATION_ID  = 'consultation_id';
    private const FIELD_CODE             = 'code_acte';
    private const FIELD_DATE_EXECUTION   = 'date_execution';
    private const FIELD_CODE_QUANTITE    = 'quantite';
    private const FIELD_CODE_COEFFICIENT = 'coefficient';


    public function build(): ?SpecificationInterface
    {
        return new AndX(
            ...[
                   NotNull::is(self::FIELD_ID),
                   NotNull::is(self::FIELD_EXECUTANT_ID),
                   NotNull::is(self::FIELD_CONSULTATION_ID),
                   NotNull::is(self::FIELD_CODE),
                   NotNull::is(self::FIELD_DATE_EXECUTION),
                   InstanceOfX::is(self::FIELD_DATE_EXECUTION, DateTime::class),
                   NotNull::is(self::FIELD_CODE_QUANTITE),
                   NotNull::is(self::FIELD_CODE_COEFFICIENT),
               ]
        );
    }
}
