<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\ANS\OtherConditionHistories;

use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\ANS\OtherConditionHistories\CDAEntryFRListeProblemes;
use Ox\Interop\Cda\Components\Sections\IHE\OtherConditionHistories\CDASectionActiveProblems;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;
use Ox\Mediboard\Patients\CPathologie;

class CDASectionFRProblemesActifs extends CDASectionActiveProblems
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.2.132';

    /** @var CPathologie[] */
    protected $pathologies = [];

    /**
     * CDASectionFRProblemesActifs constructor.
     *
     * @param CCDAFactory $factory
     */
    public function __construct(CCDAFactory $factory)
    {
        parent::__construct($factory);

        $patient         = $this->factory->patient;
        $dossier_medical = $patient->loadRefDossierMedical();
        if (!$dossier_medical || !$dossier_medical->_id) {
            return;
        }

        /** @var CPathologie[] $pathologies */
        $pathologies       = $dossier_medical->loadRefsPathologies();
        $this->pathologies = array_filter(
            $pathologies,
            function ($pathology) {
                // Pas de code CIM10 => on prend pas
                return $pathology->code_cim10;
            }
        );
    }

    /**
     * Entries - FR Liste des problemes [1..*]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntriesProblemConcern(CCDAPOCD_MT000040_Section $section): void
    {
        // Entries - FR Liste des problemes [1..*]
        // Construction de l'entry (1 entry avec plusieurs entryRelationShip)
        $entry = (new CDAEntryFRListeProblemes(
            $this->factory,
            $this->pathologies,
            CDAEntryFRListeProblemes::TYPE_PROBLEMS_PATHOLOGIES
        ))->buildEntry();

        $section->appendEntry($entry);
    }

    /**
     * @param CCDAFactory $factory
     *
     * @return bool
     */
    public static function check(CCDAFactory $factory): bool
    {
        $dossier_medical = $factory->patient->loadRefDossierMedical();
        if (!$dossier_medical || !$dossier_medical->_id) {
            return false;
        }

        $pathologies = $dossier_medical->loadRefsPathologies();
        /** @var CPathologie $_pathology */
        foreach ($pathologies as $_pathology) {
            // Pas de code CIM10 => on prend pas
            if (!$_pathology->code_cim10) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setCode(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setCodeLoinc($section, '11450-4');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setTitle(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setTitle($section, 'Pathologies actives');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setText(CCDAPOCD_MT000040_Section $section): void
    {
        $content = $this->fetchSmarty(
            'Components/Sections/ANS/OtherConditionHistories/fr_problemes_actifs',
            [
                'pathologies' => $this->pathologies,
            ]
        );

        CCDADocTools::setText($section, $content);
    }
}
