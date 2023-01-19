<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries\IHE\PlansOfCare;

use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\CDAEntryAct;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Observation;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;

/**
 * Class CDAEntryFRDirectivesAnticipees
 *
 * @package Ox\Interop\Cda\Components\Sections\ANS\PlansOfCare
 */
class CDAEntryAdvancedDirectiveObservation extends CDAEntryAct
{
    /** @var string */
    public const TEMPLATE_ID = '1.3.6.1.4.1.19376.1.5.3.1.4.13.7';

    /**
     * CDAEntryFRTraitement constructor.
     *
     * @param CCDAFactory                 $factory
     * @param CPrescriptionLineMedicament $prescription_line
     */
    public function __construct(CCDAFactory $factory)
    {
        parent::__construct($factory);

        // Déclaration de conformité Advanced Directive Observation (CCD)
        $this->addTemplateIds('2.16.840.1.113883.10.20.1.17');

        $this->entry_content = new CCDAPOCD_MT000040_Observation();
    }

    /**
     * @param CCDAClasseCda $entry_content
     */
    protected function buildContent(CCDAClasseCda $entry_content): void
    {
        // not implemented
    }
}
