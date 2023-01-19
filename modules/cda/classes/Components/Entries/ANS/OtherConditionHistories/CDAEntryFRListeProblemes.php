<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Entries\ANS\OtherConditionHistories;

use Exception;
use Ox\Interop\Cda\CCDAClasseCda;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\IHE\OtherConditionHistories\CDAEntryProblemConcern;
use Ox\Interop\Cda\Exception\CCDAException;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Act;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_EntryRelationship;

/**
 * Class CDAEntryFRListeProblemes
 *
 * @package Ox\Interop\Cda\Components\Entries\ANS\OtherConditionHistories
 */
class CDAEntryFRListeProblemes extends CDAEntryProblemConcern
{
    use CDAEntryFREtatCliniqueTrait;

    /** @var string */
    public const TYPE_PROBLEMS_ANTECEDENTS_MED = 'antecedents_med';
    /** @var string */
    public const TYPE_PROBLEMS_PATHOLOGIES = 'pathologies';

    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.3.39';

    /** @var array */
    protected $list;

    /** @var string */
    protected $type;

    /**
     * CDAEntryFRListeProblemes constructor.
     *
     * @param CCDAFactory $factory
     * @param array       $list
     * @param string      $type
     *
     * @throws CCDAException
     */
    public function __construct(CCDAFactory $factory, array $list, string $type)
    {
        parent::__construct($factory);

        $this->type = $type;

        $this->list = $list;
    }

    /**
     * @param CCDAPOCD_MT000040_Act $act
     *
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

    /**
     * @param CCDAPOCD_MT000040_Act $act
     *
     * @throws Exception
     */
    protected function setRelationshipPathoAll(CCDAPOCD_MT000040_Act $act): void
    {
        foreach ($this->list as $object) {
            switch ($this->type) {
                case self::TYPE_PROBLEMS_ANTECEDENTS_MED:
                    $observation = (new CDAEntryFRProblemeAntecedentMed($this->factory, $object))->build();
                    break;

                case self::TYPE_PROBLEMS_PATHOLOGIES:
                    $observation = (new CDAEntryFRProblemePathology($this->factory, $object))->build();
                    break;
                default:
                    throw CCDAException::invalidType();
            }

            $entry_relationship = new CCDAPOCD_MT000040_EntryRelationship();
            $entry_relationship->setObservation($observation);
            $entry_relationship->setTypeCode("SUBJ");
            $entry_relationship->setNegationInd("false");
            $entry_relationship->setInversionInd("false");

            $act->appendEntryRelationship($entry_relationship);
        }
    }
}
