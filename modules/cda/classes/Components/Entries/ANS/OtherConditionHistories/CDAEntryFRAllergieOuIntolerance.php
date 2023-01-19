<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries\ANS\OtherConditionHistories;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\IHE\OtherConditionHistories\CDAEntryAllergiesAndIntolerances;
use Ox\Interop\Cda\Rim\CCDARIMAct;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Observation;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Mediboard\Patients\CAntecedent;

class CDAEntryFRAllergieOuIntolerance extends CDAEntryAllergiesAndIntolerances
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.3.41';

    /** @var CAntecedent */
    protected $allergy;

    /**
     * CDAEntryFRAllergieOuIntolerance constructor.
     *
     * @param CCDAFactory $factory
     * @param CAntecedent $antecedent
     */
    public function __construct(CCDAFactory $factory, ?CAntecedent $allergy)
    {
        parent::__construct($factory);

        $this->allergy = $allergy;
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     *
     * @return void
     * @throws Exception
     */
    protected function buildContent(CCDAClasseCda $observation): void
    {
        $observation->setClassCode("OBS"); // observation d'un problème
        $observation->setMoodCode("EVN"); // observation ayant eu lieu
        $observation->setNegationInd("false"); // Signifie que l'élément observé a eu lieu

        // EffectiveTime
        $this->setEffectiveTime($observation);

        // Value
        $this->setValue($observation);

        // Entry-relationship - Sévérité (FR-Severite)
        $this->setRelationshipSeverity($observation);

        // Entry-relationship - Statut du problème (FR-Statut-du-probleme)
        $this->setRelationshipProblemStatus($observation);

        // Entry-relationship - Statut clinique du patient (FR-Statut-clinique-du-patient)
        $this->setRelationshipCliniqueStatus($observation);

        // Entry-relationship - Commentaire (FR-Commentaire-ER)
        $this->setRelationshipComment($observation);
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     *
     * @throws Exception
     */
    public function setCode(CCDARIMAct $observation): void
    {
        $code_info = CANSValueSet::loadEntries("observationIntoleranceType", "ALG");
        if ($code_info) {
            CCDADocTools::setCodeCD(
                $observation,
                CMbArray::get($code_info, "code"),
                CMbArray::get($code_info, "codeSystem"),
                CMbArray::get($code_info, "displayName"),
                "ObservationIntoleranceType"
            );
        }
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    public function setText(CCDARIMAct $observation): void
    {
        if ($this->allergy) {
            CCDADocTools::setTextWithReference($observation, "#" . $this->allergy->_guid);
        } else {
            CCDADocTools::setTextWithReference($observation, "#" . CCDAFactory::NONE_ALLERGY);
        }
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    public function setStatusCode(CCDARIMAct $observation): void
    {
        CCDADocTools::setStatusCode($observation, "completed");
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    public function setEffectiveTime(CCDAPOCD_MT000040_Observation $observation): void
    {
        if (!$this->allergy) {
            CCDADocTools::setLowTime($observation, CMbDT::date());

            return;
        }

        $allergy = $this->allergy;
        if ($allergy->date_fin && $allergy->date_fin > $allergy->date) {
            CCDADocTools::setLowAndHighTime($observation, $allergy->date, $allergy->date_fin);
        } else {
            CCDADocTools::setLowTime($observation, $allergy->date);
        }
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    public function setValue(CCDAPOCD_MT000040_Observation $observation): void
    {
        if ($this->allergy) {
            CCDADocTools::addValueOriginalText($observation, "#". $this->allergy->_guid);

            return;
        }

        // none allergy
        CCDADocTools::addValueOriginalText(
            $observation,
            "#" . CCDAFactory::NONE_ALLERGY,
            'MED-274',
            'Aucune allergie, intolérance, ni réaction adverse',
            '1.2.250.1.213.1.1.4.322',
            'TA-ASIP'
        );
    }


    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    public function setRelationshipSeverity(CCDAPOCD_MT000040_Observation $observation): void
    {
        // not used
    }


    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    public function setRelationshipProblemStatus(CCDAPOCD_MT000040_Observation $observation): void
    {
        // not used
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    public function setRelationshipCliniqueStatus(CCDAPOCD_MT000040_Observation $observation): void
    {
        // not used
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    public function setRelationshipComment(CCDAPOCD_MT000040_Observation $observation): void
    {
        // not used
    }
}
