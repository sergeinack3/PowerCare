<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\IHE\Medications;

use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Sections\CDASection;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;

class CDASectionImmunizations extends CDASection
{
    /** @var string  */
    public const TEMPLATE_ID = '1.3.6.1.4.1.19376.1.5.3.1.3.23';

    /**
     * CDASectionMedications constructor.
     *
     * @param CCDAFactory $factory
     */
    public function __construct(CCDAFactory $factory)
    {
        parent::__construct($factory);

        // Conformity CDD Medications Section
        $this->addTemplateIds('2.16.840.1.113883.10.20.1.6');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntries(CCDAPOCD_MT000040_Section $section): void
    {
        // IHE Immunizations Entry [1..*]
        $this->buildEntriesVaccinations($section);
    }

    /**
     * IHE Immunizations Entry [1..*]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntriesVaccinations(CCDAPOCD_MT000040_Section $section): void
    {
        // not implemented
    }
}
