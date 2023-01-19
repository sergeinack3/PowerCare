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
class DossierMedicalSpecBuilder
{
    private const FIELD_ID = 'external_id';

    private const FIELD_PATIENT       = 'patient_id';
    private const FIELD_GROUP_SANGUIN = 'group_sanguin';
    private const FIELD_RHESUS        = 'rhesus';


    public function build(): ?SpecificationInterface
    {
        return new AndX(
            ...[
                   NotNull::is(self::FIELD_ID),
                   NotNull::is(self::FIELD_PATIENT),
                   Enum::is(self::FIELD_GROUP_SANGUIN, ['O', 'A', 'B', 'AB']),
                   Enum::is(self::FIELD_RHESUS, ['POS', 'NEG']),
               ]
        );
    }
}
