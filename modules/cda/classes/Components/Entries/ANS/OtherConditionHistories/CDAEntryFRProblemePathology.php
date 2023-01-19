<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries\ANS\OtherConditionHistories;

use Exception;
use Ox\Core\CMbArray;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Rim\CCDARIMAct;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Observation;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\Patients\CPathologie;

/**
 * Class CDAEntryFRProblemePathology
 *
 * @package Ox\Interop\Cda\Components\Entries\ANS\OtherConditionHistories
 */
class CDAEntryFRProblemePathology extends CDAEntryFRProbleme
{
    /** @var CPathologie */
    protected $pathology;

    /**
     * CDAEntryFRProblemePathology constructor.
     *
     * @param CCDAFactory $factory
     * @param CPathologie $pathology
     */
    public function __construct(CCDAFactory $factory, CPathologie $pathology)
    {
        parent::__construct($factory);

        $this->pathology = $pathology;
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     *
     * @throws Exception
     */
    protected function setCode(CCDARIMAct $observation): void
    {
        $code = $this->pathology->type == "pathologie" ? "G-1009" : "F-01000";
        $data_code = $this->factory->valueset_factory->getProblemCode($code);
        CCDADocTools::setCodeSnomed($observation, $code, CMbArray::get($data_code, "codeSystem"), CMbArray::get($data_code, "displayName"));
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setText(CCDARIMAct $observation): void
    {
        CCDADocTools::setTextWithReference($observation, "#" . $this->pathology->_guid);
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setEffectiveTime(CCDAPOCD_MT000040_Observation $observation): void
    {
        CCDADocTools::setLowTime($observation, $this->pathology->debut, $this->pathology->debut ? null : 'UNK');
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setValue(CCDAPOCD_MT000040_Observation $observation): void
    {
        $pathology   = $this->pathology;
        $code_cim_10 = CCodeCIM10::get($pathology->code_cim10);
        CCDADocTools::addValueCodeCDCIM10(
            $observation,
            $pathology->code_cim10,
            CCodeCIM10::$OID,
            $code_cim_10->libelle,
            "#$pathology->_guid"
        );
    }
}
