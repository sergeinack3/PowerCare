<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Levels\Level1;

use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Interop\InteropResources\valueset\CValueSet;

class CCDADmp extends CCDALevel1
{
    /** @var string */
    public const TYPE = self::TYPE_DMP;

    public function extractData()
    {
        // parent call
        parent::extractData();

        // elements should be declared after
        //Conformité CI-SIS
        $this->templateId[] = $this->createTemplateID("1.2.250.1.213.1.1.1.1", "CI-SIS");
    }

    /**
     * @return string
     */
    protected function prepareIdCDA(): string
    {
        $docItem = $this->mbObject;
        if (!$docItem->loadLastId400("DMP")->_id) {
            return parent::prepareIdCDA();
        }

        return $this->id_cda_lot = $docItem->_ref_last_id400->id400;
    }

    /**
     * @return CANSValueSet
     */
    protected function getFactoryValueSet(): CValueSet
    {
        return new CANSValueSet();
    }
}

