<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries\IHE\OtherConditionHistories;

use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\CDAEntryAct;
use Ox\Interop\Cda\Rim\CCDARIMAct;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Observation;
use Ox\Mediboard\Patients\CPathologie;

/**
 * Class CDAEntryProblem
 *
 * @package Ox\Interop\Cda\Components\Entries\IHE\OtherConditionHistories
 */
class CDAEntryProblem extends CDAEntryAct
{
    /** @var string */
    public const TEMPLATE_ID = '1.3.6.1.4.1.19376.1.5.3.1.4.5';

    /**
     * CDAEntryProblem constructor.
     *
     * @param CCDAFactory $factory
     * @param CPathologie $pathology
     */
    public function __construct(CCDAFactory $factory)
    {
        parent::__construct($factory);

        // Conformity CCD
        $this->addTemplateIds('2.16.840.1.113883.10.20.1.28');

        $this->entry_content = new CCDAPOCD_MT000040_Observation();
    }

    /**
     * @param CCDARIMAct $entry_content
     */
    protected function buildContent(CCDAClasseCda $entry_content): void
    {
        // not implemented
    }
}
