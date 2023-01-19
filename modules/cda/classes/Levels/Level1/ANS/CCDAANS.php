<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Levels\Level1\ANS;

use Ox\Core\CMbArray;
use Ox\Interop\Cda\Levels\Level1\CCDALevel1;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Interop\InteropResources\valueset\CValueSet;
use Ox\Interop\Xds\Factory\CXDSFactory;

/**
 * Class CCDAANS
 *
 * @package Ox\Interop\Cda\Levels\Level3\ANS
 */
class CCDAANS extends CCDALevel1
{
    public const TYPE = self::TYPE_ANS_L1;

    public function extractData()
    {
        // parent call
        parent::extractData();

        // elements should be declared after

        //Conformité CI-SIS
        $this->templateId[] = $this->createTemplateID("1.2.250.1.213.1.1.1.1", "CI-SIS");
    }

    /**
     * @return CANSValueSet
     */
    protected function getFactoryValueSet(): CValueSet
    {
        return new CANSValueSet();
    }

    /**
     * @param CXDSFactory $xds
     */
    public function initializeXDS(CXDSFactory $xds): void
    {
        parent::initializeXDS($xds);
        $data_valueset         = CANSValueSet::loadEntries(
            "formatCode",
            $this::CODE_JDV,
        );
        $xds->entry_media_type = [
            "codingScheme" => CMbArray::get($data_valueset, "codeSystem"),
            "name"         => CMbArray::get($data_valueset, "displayName"),
            "formatCode"   => CMbArray::get($data_valueset, "code"),
        ];
    }
}
