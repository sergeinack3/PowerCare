<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries\IHE\OtherConditionHistories;

use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\Rim\CCDARIMAct;

class CDAEntryAllergyAndIntoleranceConcern extends CDAEntryConcern
{
    /** @var string */
    public const TEMPLATE_ID = '1.3.6.1.4.1.19376.1.5.3.1.4.5.3';

    /**
     * @param CCDARIMAct $entry_content
     */
    protected function buildContent(CCDAClasseCda $entry_content): void
    {
        // not implemented
    }
}
