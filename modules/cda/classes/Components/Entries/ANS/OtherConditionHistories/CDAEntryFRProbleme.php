<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries\ANS\OtherConditionHistories;

use Exception;
use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\IHE\OtherConditionHistories\CDAEntryProblem;
use Ox\Interop\Cda\Rim\CCDARIMAct;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Observation;

/**
 * Class CDAEntryFRProbleme
 *
 * @package Ox\Interop\Cda\Components\Entries\ANS\OtherConditionHistories
 */
abstract class CDAEntryFRProbleme extends CDAEntryProblem
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.3.37';

    /**
     * CDAEntryFRProbleme constructor.
     *
     * @param CCDAFactory $factory
     */
    public function __construct(CCDAFactory $factory)
    {
        parent::__construct($factory);
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     *
     * @return void
     * @throws Exception
     */
    public function buildContent(CCDAClasseCda $observation): void
    {
        $observation->setClassCode("OBS"); // observation d'un problème
        $observation->setMoodCode("EVN"); // observation ayant eu lieu
        $observation->setNegationInd("false"); // Signifie que l'élément observé a eu lieu

        // StatusCode
        $this->setStatusCode($observation);

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
    protected function setCode(CCDARIMAct $observation): void
    {
        // implemented in sub classes
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setText(CCDARIMAct $observation): void
    {
        // implemented in sub classes
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setStatusCode(CCDARIMAct $observation): void
    {
        CCDADocTools::setStatusCode($observation, "completed");
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    abstract protected function setEffectiveTime(CCDAPOCD_MT000040_Observation $observation): void;

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    abstract protected function setValue(CCDAPOCD_MT000040_Observation $observation): void;

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setRelationshipSeverity(CCDAPOCD_MT000040_Observation $observation): void
    {
        // not used
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setRelationshipProblemStatus(CCDAPOCD_MT000040_Observation $observation): void
    {
        // not used
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setRelationshipCliniqueStatus(CCDAPOCD_MT000040_Observation $observation): void
    {
        // not used
    }

    /**
     * @param CCDAPOCD_MT000040_Observation $observation
     */
    protected function setRelationshipComment(CCDAPOCD_MT000040_Observation $observation): void
    {
        // not used
    }
}
