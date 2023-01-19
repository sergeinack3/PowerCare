<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\IHE\Medications;

use Ox\Interop\Cda\Components\Sections\CDASection;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;

/**
 * Class CDASectionHospitalDischargeMedications
 */
class CDASectionHospitalDischargeMedications extends CDASection
{
    /** @var string */
    public const TEMPLATE_ID = '1.3.6.1.4.1.19376.1.5.3.1.3.22';

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntries(CCDAPOCD_MT000040_Section $section): void
    {
        // Entries - IHE Medications Entry [1..*]
        $this->buildEntriesMedications($section);
    }

    /**
     * IHE Medications Entry [1..*]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntriesMedications(CCDAPOCD_MT000040_Section $section): void
    {
        // not implemented
    }
}
