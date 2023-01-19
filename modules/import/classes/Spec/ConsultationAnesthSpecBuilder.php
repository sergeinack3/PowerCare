<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Spec;

use Ox\Core\Specification\IsNull;
use Ox\Core\Specification\SpecificationInterface;

/**
 * External consultation spec builder
 */
class ConsultationAnesthSpecBuilder
{
    /**
     * @return SpecificationInterface|null
     */
    public function build(): ?SpecificationInterface
    {
        return IsNull::is('external_id')->not();
    }
}
