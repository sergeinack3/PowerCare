<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries\ANS\OtherConditionHistories;

use Ox\Core\CStoredObject;
use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\IHE\OtherConditionHistories\CDAEntryProcedure;
use Ox\Interop\Cda\Datatypes\Base\CCDACE;
use Ox\Interop\Cda\Datatypes\Base\CCDAED;
use Ox\Interop\Cda\Datatypes\Base\CCDATEL;
use Ox\Interop\Cda\Exception\CCDAException;
use Ox\Interop\Cda\Rim\CCDARIMAct;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_EntryRelationship;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Procedure;
use Ox\Mediboard\Ccam\CDatedCodeCCAM;
use Ox\Mediboard\Patients\CAntecedent;

/**
 * Class CDAEntryFRActe
 *
 * @package Ox\Interop\Cda\Components\Entries\ANS\OtherConditionHistories
 */
class CDAEntryFRActe extends CDAEntryProcedure
{
    /** @var string */
    public const TYPE_ACTE_CHIRURGICAL = 'acte_chirurgical';

    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.3.62';

    /** @var CAntecedent */
    protected $antecedent;

    /**
     * CDAEntryFRActe constructor.
     *
     * @param CCDAFactory   $factory
     * @param CStoredObject $object
     * @param string        $type
     */
    public function __construct(CCDAFactory $factory, CStoredObject $object, string $type)
    {
        parent::__construct($factory);

        // si acte réalisé
        $this->addTemplateIds('2.16.840.1.113883.10.20.1.29');

        switch ($type) {
            case self::TYPE_ACTE_CHIRURGICAL:
                $this->antecedent = $object;
                break;
            default:
                CCDAException::invalidType();
        }

        // si complexité avec plusieurs gestion d'actes différents prendre exemple sur le système CDAEntryProbleme
    }

    /**
     * @param CCDAPOCD_MT000040_Procedure $procedure
     *
     * @return void
     */
    protected function buildContent(CCDAClasseCda $procedure): void
    {
        $procedure->setClassCode("PROC");
        $procedure->setMoodCode("EVN");

        // EffectiveTime
        $this->setEffectiveTime($procedure);

        // Entry relationship - Circonstances ayant décidé de l'acte (FR-Reference-interne)
        $this->setEntryRelationshipCirconstancies($procedure);

        // Entry relationship - Motif de l'acte (FR-Reference-interne)
        $this->setEntryRelationshipMotif($procedure);

        // Entry relationship - Réference interne à un DM (FR-Reference-interne)
        $this->setEntryRelationshipRefDM($procedure);

        // Entry relationship - Difficulté (FR-Simple-Observation)
        $this->setEntryRelationshipDifficulties($procedure);

        // Entry relationship - Scores (FR-Simple-Observation)
        $this->setEntryRelationshipScores($procedure);
    }

    /**
     * @param CCDAPOCD_MT000040_Procedure $procedure
     */
    public function setCode(CCDARIMAct $procedure): void
    {
        $procedure->setCode($this->getCodeCCAM($this->antecedent->_ref_code_ccam));
    }

    /**
     * @param CCDAPOCD_MT000040_Procedure $procedure
     */
    public function setText(CCDARIMAct $procedure): void
    {
        CCDADocTools::setTextWithReference($procedure, "#" . $this->antecedent->_guid);
    }

    /**
     * @param CCDAPOCD_MT000040_Procedure $procedure
     */
    public function setStatusCode(CCDARIMAct $procedure): void
    {
        CCDADocTools::setStatusCode($procedure, $this->antecedent->annule ? "cancelled" : "completed");
    }

    /**
     * @param CCDAPOCD_MT000040_Procedure $procedure
     */
    public function setEffectiveTime(CCDAPOCD_MT000040_Procedure $procedure): void
    {
        $date_start_antecedent = $this->antecedent->date;
        $date_end_antecedent   = $this->antecedent->date_fin;
        if ($date_end_antecedent && $date_end_antecedent > $date_start_antecedent) {
            CCDADocTools::setLowAndHighTime($procedure, $date_start_antecedent, $date_end_antecedent);
        }
        else {
            CCDADocTools::setLowTime($procedure, $date_start_antecedent);
        }
    }

    /**
     * @param CCDAPOCD_MT000040_Procedure $procedure
     */
    public function setEntryRelationshipMotif(CCDAPOCD_MT000040_Procedure $procedure): void
    {
        $code = $this->getCodeCCAM($this->antecedent->_ref_code_ccam, "#" . $this->antecedent->_guid);
        $act = (new CDAEntryFRReferenceInterne($this->factory, $code, $this->id))->build();
        $act->setClassCode("ACT");
        $act->setMoodCode("EVN");

        $entry_relationship = new CCDAPOCD_MT000040_EntryRelationship();
        $entry_relationship->setTypeCode("RSON");
        $entry_relationship->setInversionInd("false");
        $entry_relationship->setAct($act);

        $procedure->appendEntryRelationship($entry_relationship);
    }

    /**
     * @param CCDAPOCD_MT000040_Procedure $procedure
     */
    public function setEntryRelationshipCirconstancies(CCDAPOCD_MT000040_Procedure $procedure): void
    {
        // not used
    }

    /**
     * @param CCDAPOCD_MT000040_Procedure $procedure
     */
    public function setEntryRelationshipRefDM(CCDAPOCD_MT000040_Procedure $procedure): void
    {
        // not used
    }

    /**
     * @param CCDAPOCD_MT000040_Procedure $procedure
     */
    public function setEntryRelationshipDifficulties(CCDAPOCD_MT000040_Procedure $procedure): void
    {
        // not used
    }

    /**
     * @param CCDAPOCD_MT000040_Procedure $procedure
     */
    public function setEntryRelationshipScores(CCDAPOCD_MT000040_Procedure $procedure): void
    {
        // not used
    }

    /**
     * Add code CCAM on element
     *
     * @param object         $element               element
     * @param CDatedCodeCCAM $code_ccam             code ccam
     * @param string         $content_original_text content original text
     *
     * @return CCDACE
     */
    private function getCodeCCAM(CDatedCodeCCAM $code_ccam, $content_original_text = null): CCDACE
    {
        $code = new CCDACE();
        $code->setCode($code_ccam->code);
        $code->setCodeSystem("1.2.250.1.213.2.5");
        $code->setDisplayName($code_ccam->libelleLong);
        $code->setCodeSystemName("CCAM");

        if ($content_original_text) {
            $text_observation           = new CCDAED();
            $text_reference_observation = new CCDATEL();
            $text_reference_observation->setValue($content_original_text);
            $text_observation->setReference($text_reference_observation);
            $code->setOriginalText($text_observation);
        }

        return $code;
    }
}
