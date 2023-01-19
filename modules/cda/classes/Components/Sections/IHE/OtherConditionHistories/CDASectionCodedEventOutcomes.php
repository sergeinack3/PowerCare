<?php

namespace Ox\Interop\Cda\Components\Sections\IHE\OtherConditionHistories;

use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

class CDASectionCodedEventOutcomes extends CDASectionEventOutcomes
{
    /** @var string */
    public const TEMPLATE_ID = '1.3.6.1.4.1.19376.1.7.3.1.1.13.7';

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntries(CCDAPOCD_MT000040_Section $section): void
    {
        // Entries - IHE Simple Observation [1..*]
        $this->buildEntriesSimpleObservations($section);

        // Entries - IHE Patient Transfer [0..*]
        $this->setEntriesPatientTransfer($section);

        // Entries - IHE Problem Entry [0..*]
        $this->setEntriesProblems($section);
    }

    /**
     * Entries - IHE Simple Observation [1..*]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntriesSimpleObservations(CCDAPOCD_MT000040_Section $section): void
    {
        // not implemented
    }

    /**
     * Entries - IHE Patient Transfer [0..*]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setEntriesPatientTransfer(CCDAPOCD_MT000040_Section $section): void
    {
        // not implemented
    }

    /**
     * Entries - IHE Problem Entry [0..*]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setEntriesProblems(CCDAPOCD_MT000040_Section $section): void
    {
        // not implemented
    }
}
