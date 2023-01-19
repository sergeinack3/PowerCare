<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\ANS\OtherConditionHistories;

use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\Components\Sections\IHE\OtherConditionHistories\CDASectionCodedEventOutcomes;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;

class CDASectionFRResultatsEvenements extends CDASectionCodedEventOutcomes
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.2.163';

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntries(CCDAPOCD_MT000040_Section $section): void
    {
        // Entries - FR Simple Observation [1..*]
        $this->buildEntriesSimpleObservations($section);
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntriesSimpleObservations(CCDAPOCD_MT000040_Section $section): void
    {
        // Entry - FR Simple Observation [1..*]
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setCode(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setCodeLoinc($section, '42545-4');
    }
}
