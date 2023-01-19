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
use Ox\Core\Specification\Not;
use Ox\Core\Specification\NotNull;
use Ox\Core\Specification\OrX;
use Ox\Core\Specification\SpecificationInterface;

/**
 * Description
 */
class ActeCCAMSpecBuilder
{
    private const FIELD_ID = 'external_id';

    private const FIELD_EXECUTANT_ID    = 'executant_id';
    private const FIELD_CONSULTATION_ID = 'consultation_id';
    private const FIELD_CODE_ACTE       = 'code_acte';
    private const FIELD_DATE_EXECUTION  = 'date_execution';
    private const FIELD_CODE_ACTIVITE   = 'code_activite';
    private const FIELD_CODE_PHASE      = 'code_phase';


    public function build(): ?SpecificationInterface
    {
        return new AndX(
            ...[
                   NotNull::is(self::FIELD_ID),
                   NotNull::is(self::FIELD_EXECUTANT_ID),
                   NotNull::is(self::FIELD_CONSULTATION_ID),
                   NotNull::is(self::FIELD_CODE_ACTE),
                   NotNull::is(self::FIELD_DATE_EXECUTION),
                   InstanceOfX::is(self::FIELD_DATE_EXECUTION, DateTime::class),
                   NotNull::is(self::FIELD_CODE_ACTIVITE),
                   NotNull::is(self::FIELD_CODE_PHASE),
               ]
        );
    }
}
