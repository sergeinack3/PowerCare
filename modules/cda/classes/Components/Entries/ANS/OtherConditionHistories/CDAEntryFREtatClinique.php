<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries\ANS\OtherConditionHistories;

use Exception;
use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\Components\Entries\IHE\OtherConditionHistories\CDAEntryConcern;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Act;

/**
 * Class CDAEntryFREtatClinique
 *
 * @package Ox\Interop\Cda\Components\Entries\ANS\OtherConditionHistories
 */
abstract class CDAEntryFREtatClinique extends CDAEntryConcern
{
    use CDAEntryFREtatCliniqueTrait;

    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.3.38';

    /**
     * @param CCDAPOCD_MT000040_Act $act
     * @return void
     * @throws Exception
     */
    protected function buildContent(CCDAClasseCda $act): void
    {
        $act->setClassCode("ACT");
        $act->setMoodCode("EVN");

        // EffectiveTime
        $this->setEffectiveTime($act);

        // Entry Relationship - Pathologie - Allergie
        $this->setRelationshipPathoAll($act);

        // Entry Relationship - Autre précision sur l'état clinique
        $this->setRelationshipOther($act);
    }
}
