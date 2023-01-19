<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\IHE\OtherConditionHistories;

use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Sections\CDASection;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;

class CDASectionActiveProblems extends CDASection
{
  /** @var string */
  public const TEMPLATE_ID = '1.3.6.1.4.1.19376.1.5.3.1.3.6';

    /**
     * CDASectionActiveProblems constructor.
     *
     * @param CCDAFactory $factory
     */
  public function __construct(CCDAFactory $factory)
  {
    parent::__construct($factory);

    // conformity CDD Problem Section
    $this->addTemplateIds('2.16.840.1.113883.10.20.1.11');
  }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntries(CCDAPOCD_MT000040_Section $section): void
    {
        // Entries - IHE Problem Concern Entry [1..*]
        $this->buildEntriesProblemConcern($section);
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntriesProblemConcern(CCDAPOCD_MT000040_Section $section): void
    {
        // not implemented
    }
}
