<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries\ANS\OtherConditionHistories;

use Exception;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Sections\ANS\SousSections\CDASectionFRFacteursDeRisques;
use Ox\Interop\Cda\Rim\CCDARIMAct;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Observation;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Snomed\CSnomed;

/**
 * Class CDAEntryFRProblemeAntecedentFam
 *
 * @package Ox\Interop\Cda\Components\Entries\ANS\OtherConditionHistories
 */
class CDAEntryFRAntecedentFam extends CDAEntryFRProbleme
{
    public const TEMPLATE_ID = '1.3.6.1.4.1.19376.1.5.3.1.3.15';

    /** @var CAntecedent */
    protected $antecedent;

    /**
     * CDAEntryFRProblemeAntecedentFam constructor.
     *
     * @param CCDAFactory $factory
     * @param CAntecedent $antecedent
     */
    public function __construct(CCDAFactory $factory, CAntecedent $antecedent)
    {
        parent::__construct($factory);

        $this->antecedent = $antecedent;
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     *
     * @throws Exception
     */
    protected function setCode(CCDARIMAct $observation): void
    {
        CCDADocTools::setCodeCD($observation, "G-1009", "1.2.250.1.213.2.12", "Diagnostic", "SNOMED 3.5");
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setText(CCDARIMAct $observation): void
    {
        CCDADocTools::setTextWithReference($observation, "#" . $this->antecedent->_guid);
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setEffectiveTime(CCDAPOCD_MT000040_Observation $observation): void
    {
        $antecedent = $this->antecedent;
        if ($antecedent->date) {
            $antecedent->date_fin ? CCDADocTools::setLowAndHighTime($observation, $antecedent->date, $antecedent->date_fin) : CCDADocTools::setLowTime($observation, $antecedent->date);
        } else {
            CCDADocTools::setLowTime($observation, null, 'UNK');
        }
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setValue(CCDAPOCD_MT000040_Observation $observation): void
    {
        // Ajout du code Snomed (il en faut qu'un => on prend le premier)
        /** @var CSnomed $code_snomed */
        $code_snomed = reset($this->antecedent->_ref_codes_snomed);
        if ($code_snomed && $code_snomed->_id) {
            CCDADocTools::addCodeSnomed(
                $observation,
                $code_snomed->code,
                CSnomed::$oid_snomed,
                $code_snomed->libelle,
                "#" . $this->antecedent->_guid
            );
        }
    }
}
