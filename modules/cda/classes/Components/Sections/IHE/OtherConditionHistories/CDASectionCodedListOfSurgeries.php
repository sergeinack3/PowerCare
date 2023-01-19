<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\IHE\OtherConditionHistories;

use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;

class CDASectionCodedListOfSurgeries extends CDASectionListOfSurgeries
{
    /** @var string */
    public const TEMPLATE_ID = '1.3.6.1.4.1.19376.1.5.3.1.3.12';

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntries(CCDAPOCD_MT000040_Section $section): void
    {
        // Entries - IHE Procedure Entry [1..*]
        $this->buildEntriesProcedure($section);

        // Entries - IHE External Reference Entry [0..*]
        $this->buildEntriesExternalReference($section);
    }

    /**
     * Entries - IHE Procedure Entry [1..*]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntriesProcedure(CCDAPOCD_MT000040_Section $section): void
    {
        // not implemented
    }

    /**
     * IHE External Reference Entry [0..*]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntriesExternalReference(CCDAPOCD_MT000040_Section $section): void
    {
        // not implemented
    }
}
