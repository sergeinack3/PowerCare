<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries\ANS\OtherConditionHistories;

use Exception;
use Ox\Core\CMbArray;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\Rim\CCDARIMAct;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Act;
use Ox\Interop\InteropResources\valueset\CANSValueSet;

trait CDAEntryFREtatCliniqueTrait
{
    /** @var string */
    protected $statusCode;

    /**
     * @param CCDAPOCD_MT000040_Act $act
     */
    protected function setRelationshipPathoAll(CCDAPOCD_MT000040_Act $act): void
    {
        // implemented in class which used
    }

    /**
     * @param CCDAPOCD_MT000040_Act $act
     */
    protected function setRelationshipOther(CCDAPOCD_MT000040_Act $act): void
    {
        // implemented in class which used
    }

    /**
     * @param CCDARIMAct $act
     */
    protected function setCode(CCDARIMAct $act): void
    {
        CCDADocTools::setCodeNullFlavor($act);
    }

    /**
     * @param CCDAPOCD_MT000040_Act $act
     *
     * @throws Exception
     */
    protected function setStatusCode(CCDARIMAct $act): void
    {
        $value_set = new CANSValueSet();
        $this->statusCode = CCDADocTools::getStatusEntranceMB($this->factory->targetObject);
        CCDADocTools::setStatusCode($act, CMbArray::get($value_set->getStatusEntrance($this->statusCode), "code"));
    }

    /**
     * @param CCDAPOCD_MT000040_Act $act
     */
    protected function setEffectiveTime(CCDAPOCD_MT000040_Act $act): void
    {
        // Dans ce cas, on met Low et High
        $date_start = CMbArray::get($this->factory->service_event, "time_start");
        if ($this->statusCode == "completed" || $this->statusCode == "aborted") {
            $date_end = CMbArray::get($this->factory->service_event, "time_stop");
            CCDADocTools::setLowAndHighTime($act, $date_start, $date_end);
        }
        // Dans ce cas, on met que Low
        else {
            CCDADocTools::setLowTime($act, $date_start);
        }
    }

    /**
     * @param CCDARIMAct $entry_content
     */
    protected function setText(CCDARIMAct $entry_content): void
    {
        // not used
    }

    /**
     * @param CCDARIMAct $entry_content
     */
    protected function setTitle(CCDARIMAct $entry_content): void
    {
        // not used
    }
}
