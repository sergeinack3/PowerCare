<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\ANS\OtherConditionHistories;

use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\ANS\OtherConditionHistories\CDAEntryFRListeProblemes;
use Ox\Interop\Cda\Components\Sections\IHE\OtherConditionHistories\CDASectionHistoryOfPastIllness;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;
use Ox\Mediboard\Patients\CAntecedent;

class CDASectionFRAntecedentsMedicaux extends CDASectionHistoryOfPastIllness
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.2.134';

    /** @var CAntecedent[] */
    protected $antecedents = [];

    /**
     * CDASectionFRAntecedentsMedicaux constructor.
     *
     * @param CCDAFactory $factory
     *
     * @throws \Exception
     */
    public function __construct(CCDAFactory $factory)
    {
        parent::__construct($factory);

        $patient         = $this->factory->patient;
        $dossier_medical = $patient->loadRefDossierMedical();
        if (!$dossier_medical || !$dossier_medical->_id) {
            return;
        }

        /** @var CAntecedent[] $antecedents */
        $antecedents       = $dossier_medical->loadRefsAntecedentsOfType("med");
        $this->antecedents = array_filter(
            $antecedents,
            function ($antecedent) {
                $antecedent->loadBackRefs("atcd_snomed");

                return (bool)$antecedent->loadRefsCodesSnomed();
            }
        );
    }

    /**
     * Entries - FR Liste des problemes [1..*]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntryProblemConcern(CCDAPOCD_MT000040_Section $section): void
    {
        // Entries - FR Liste des problemes [1..*]
        $entry = (new CDAEntryFRListeProblemes($this->factory, $this->antecedents, CDAEntryFRListeProblemes::TYPE_PROBLEMS_ANTECEDENTS_MED))->buildEntry();

        $section->appendEntry($entry);
    }

    public static function check(CCDAFactory $factory): bool
    {
        $dossier_medical = $factory->patient->loadRefDossierMedical();
        if (!$dossier_medical || !$dossier_medical->_id) {
            return false;
        }

        /** @var CAntecedent $_antecedent */
        $antecedents = $dossier_medical->loadRefsAntecedentsOfType("med");
        foreach ($antecedents as $_antecedent) {
            // Récupération des codes Snomed sur l'antécédent (on prend le premier code Snomed) => si on en a pas, next
            $_antecedent->loadBackRefs("atcd_snomed");
            if (!$_antecedent->loadRefsCodesSnomed()) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    public function setCode(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setCodeLoinc($section, '11348-0');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    public function setTitle(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setTitle($section, 'Antécédents médicaux');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    public function setText(CCDAPOCD_MT000040_Section $section): void
    {
        $content = $this->fetchSmarty(
            'Components/Sections/ANS/OtherConditionHistories/fr_antecendent_medicaux',
            [
                'antecedents' => $this->antecedents,
            ]
        );

        CCDADocTools::setText($section, $content);
    }
}

