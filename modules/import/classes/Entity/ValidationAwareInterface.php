<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Entity;

use Ox\Core\Specification\SpecificationViolation;
use Ox\Import\Framework\Validator\ValidatorVisitorInterface;

/**
 * Object aware of validation specs
 */
interface ValidationAwareInterface
{
    /**
     * @param ValidatorVisitorInterface $validator
     *
     * @return SpecificationViolation|null
     */
    public function validate(ValidatorVisitorInterface $validator): ?SpecificationViolation;
}
