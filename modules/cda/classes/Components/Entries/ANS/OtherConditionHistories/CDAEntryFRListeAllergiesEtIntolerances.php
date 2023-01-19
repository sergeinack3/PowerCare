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
use Ox\Interop\Cda\Components\Entries\IHE\OtherConditionHistories\CDAEntryAllergyAndIntoleranceConcern;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Act;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_EntryRelationship;
use Ox\Mediboard\Patients\CAntecedent;

/**
 * Class CDAEntryFRListeAllergiesEtIntolerances
 *
 * @package Ox\Interop\Cda\Components\Entries\ANS\OtherConditionHistories
 */
class CDAEntryFRListeAllergiesEtIntolerances extends CDAEntryAllergyAndIntoleranceConcern
{
    use CDAEntryFREtatCliniqueTrait;

    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.3.40';

    /** @var CAntecedent[] */
    protected $allergies = [];

    /** @var null|string */
    protected $last_update_list_allergies = null;

    /**
     * CDAEntryFRListeAllergiesEtIntolerances constructor.
     *
     * @param CCDAFactory   $factory
     * @param CAntecedent[] $allergies
     *
     * @throws Exception
     */
    public function __construct(CCDAFactory $factory, array $allergies)
    {
        parent::__construct($factory);

        $this->allergies = $allergies;

        $last_update_list_allergies = null;
        foreach ($this->allergies as $allergy) {
            $last_update = $allergy->loadLastLog();
            if (!$last_update_list_allergies || ($last_update_list_allergies < $last_update->date)) {
                $last_update_list_allergies = $last_update->date;
            }
        }
        $this->last_update_list_allergies = $last_update_list_allergies;
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
     */
    protected function setEffectiveTime(CCDAPOCD_MT000040_Act $act): void
    {
        if (empty($this->allergies)) {
            $date_start = CMbArray::get($this->factory->service_event, "time_start");
            CCDADocTools::setLowTime($act, $date_start);

            return;
        }

        $last_update_list_allergies = $this->last_update_list_allergies
            ? CMbDT::date($this->last_update_list_allergies)
            : CMbDT::date();

        if ($this->statusCode == "completed" || $this->statusCode == "aborted") {
            CCDADocTools::setLowAndHighTime($act, CMbDT::date());
        } else {
            CCDADocTools::setLowTime($act, $last_update_list_allergies);
        }
    }

    /**
     * @param CCDAPOCD_MT000040_Act $act
     *
     * @throws Exception
     */
    protected function setRelationshipPathoAll(CCDAPOCD_MT000040_Act $act): void
    {
        $allergies = $this->allergies;
        if (empty($allergies)) {
            $allergies[] = null;
            return;
        }

        foreach ($allergies as $allergy) {
            $observation        = (new CDAEntryFRAllergieOuIntolerance($this->factory, $allergy))->build();
            $entry_relationship = new CCDAPOCD_MT000040_EntryRelationship();
            $entry_relationship->setTypeCode("SUBJ");
            $entry_relationship->setInversionInd("false");
            $entry_relationship->setObservation($observation);

            $act->appendEntryRelationship($entry_relationship);
        }
    }
}
