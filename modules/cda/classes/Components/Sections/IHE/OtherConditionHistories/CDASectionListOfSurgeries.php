<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\IHE\OtherConditionHistories;

use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Sections\CDASection;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;

class CDASectionListOfSurgeries extends CDASection
{
    /** @var string */
    public const TEMPLATE_ID = '1.3.6.1.4.1.19376.1.5.3.1.3.11';

    /**
     * CDASectionListOfSurgeries constructor.
     *
     * @param CCDAFactory $factory
     */
    public function __construct(CCDAFactory $factory)
    {
        parent::__construct($factory);

        // Conformity CDD Procedures section
        $this->addTemplateIds('2.16.840.1.113883.10.20.1.12');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntries(CCDAPOCD_MT000040_Section $section): void
    {
        // Entries Procedure : Procedure activity (Act) | Procedure activity (observation) | Procedure activity (procedure) [1..*]
        $this->buildEntriesProcedure($section);

        // Entries Comment [0..*]
        $this->buildComments($section);
    }

    /**
     * Entries Procedure [1..*] :
     * Procedure activity (Act)
     * Procedure activity (observation)
     * Procedure activity (procedure)
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntriesProcedure(CCDAPOCD_MT000040_Section $section): void
    {
        // not implemented
    }

    /**
     * Entries Comment [0..*]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildComments(CCDAPOCD_MT000040_Section $section): void
    {
        // not implemented
    }
}
