<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\IHE\OtherConditionHistories;

use Ox\Interop\Cda\Components\Sections\CDASection;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;

class CDASectionHistoryOfPastIllness extends CDASection
{
    /** @var string */
    public const TEMPLATE_ID = '1.3.6.1.4.1.19376.1.5.3.1.3.8';

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntries(CCDAPOCD_MT000040_Section $section): void
    {
        // Entry - IHE Problem Concern [1..1]
        $this->buildEntryProblemConcern($section);
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntryProblemConcern(CCDAPOCD_MT000040_Section $section): void
    {
        // not implemented
    }
}
