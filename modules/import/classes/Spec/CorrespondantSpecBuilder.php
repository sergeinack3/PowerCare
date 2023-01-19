<?php
/**
 * @package Mediboard\Import
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Spec;

use Ox\Core\Specification\AndX;
use Ox\Core\Specification\NotNull;
use Ox\Core\Specification\SpecificationInterface;

/**
 * Description
 */
class CorrespondantSpecBuilder
{
    private const FIELD_ID      = 'external_id';
    private const FIELD_PATIENT = 'patient_id';
    private const FIELD_MEDECIN = 'medecin_id';

    public function build(): SpecificationInterface
    {
        return new AndX(
            ...[
                   NotNull::is(self::FIELD_ID),
                   NotNull::is(self::FIELD_PATIENT),
                   NotNull::is(self::FIELD_MEDECIN),
               ]
        );
    }
}
