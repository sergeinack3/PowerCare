<?php

namespace Ox\Interop\Cda\Components\Sections\IHE\OtherConditionHistories;

use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Sections\CDASection;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

class CDASectionAllergiesAndOtherAdverseReactions extends CDASection
{
    /** @var string */
    public const TEMPLATE_ID = '1.3.6.1.4.1.19376.1.5.3.1.3.13';

    /**
     * CDASectionAllergiesAndOtherAdverseReactions constructor.
     *
     * @param CCDAFactory $factory
     */
    public function __construct(CCDAFactory $factory)
    {
        parent::__construct($factory);

        // Conformity Alert section
        $this->addTemplateIds('2.16.840.1.113883.10.20.1.2');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntries(CCDAPOCD_MT000040_Section $section): void
    {
        // Entries - IHE Allergy and Intolerance Concern Entry [1..*]
        $this->buildEntriesAllergyIntoleranceConcern($section);
    }

    /**
     * Entries - IHE Allergy and Intolerance Concern Entry [1..*]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntriesAllergyIntoleranceConcern(CCDAPOCD_MT000040_Section $section): void
    {
       // not implemented
    }
}
