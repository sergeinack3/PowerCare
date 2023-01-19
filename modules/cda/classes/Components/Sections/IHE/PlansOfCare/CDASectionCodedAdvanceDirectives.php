<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\IHE\PlansOfCare;

use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Sections\CDASection;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;

/**
 * Class CDASectionCodedAdvanceDirectives
 * @package Ox\Interop\Cda\Components\Section\Ihe\PlansOfCare
 */
class CDASectionCodedAdvanceDirectives extends CDASection
{
    /** @var string */
    public const TEMPLATE_ID = '1.3.6.1.4.1.19376.1.5.3.1.3.35';

    /**
     * CDASectionCodedAdvanceDirectives constructor.
     *
     * @param CCDAFactory                 $factory
     */
    public function __construct(CCDAFactory $factory)
    {
        parent::__construct($factory);

        // Advance Directives
        $this->addTemplateIds('1.3.6.1.4.1.19376.1.5.3.1.3.34');

        // CCD Advance Directives
        $this->addTemplateIds("2.16.840.1.113883.10.20.1.1");
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntries(CCDAPOCD_MT000040_Section $section): void
    {
        // IHE Immunizations Entry [0..1]
        $this->buildDirectives($section);
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildDirectives(CCDAPOCD_MT000040_Section $section)
    {
        // not implemented
    }
}
