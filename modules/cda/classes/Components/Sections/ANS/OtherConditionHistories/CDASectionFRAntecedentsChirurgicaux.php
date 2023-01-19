<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\ANS\OtherConditionHistories;

use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\ANS\OtherConditionHistories\CDAEntryFRActe;
use Ox\Interop\Cda\Components\Sections\IHE\OtherConditionHistories\CDASectionCodedListOfSurgeries;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;
use Ox\Mediboard\Patients\CAntecedent;

class CDASectionFRAntecedentsChirurgicaux extends CDASectionCodedListOfSurgeries
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.2.136';

    /** @var CAntecedent[] */
    protected $antecedents;

    public function __construct(CCDAFactory $factory)
    {
        parent::__construct($factory);

        $dossier_medical = $factory->patient->loadRefDossierMedical();
        if (!$dossier_medical || !$dossier_medical->_id) {
            return;
        }

        /** @var CAntecedent[] $antecedents */
        $antecedents       = $dossier_medical->loadRefsAntecedentsOfType("chir");
        $this->antecedents = array_filter(
            $antecedents,
            function ($antecedent) {
                // On prend les antécédents qui ont un code CCAM dans leur libellé && qui ont une date
                return $antecedent->date && $antecedent->hasNameCodeCCAM();
            }
        );
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

        $antecedents = $dossier_medical->loadRefsAntecedentsOfType("chir");
        /** @var CAntecedent $_antecedent */
        foreach ($antecedents as $_antecedent) {
            // On ajoute que les antécédents qui ont une date
            if (!$_antecedent->date) {
                continue;
            }

            // On prend les antécédents qui ont un code CCAM dans leur libellé
            if (!$_antecedent->hasNameCodeCCAM()) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Entries - FR Acte
     */
    public function buildEntriesProcedure(CCDAPOCD_MT000040_Section $section): void
    {
        // todo use template for none antecedent ?

        // Entries - FR Acte [1..*]
        foreach ($this->antecedents as $antecedent) {
            $entry = (new CDAEntryFRActe($this->factory, $antecedent, CDAEntryFRActe::TYPE_ACTE_CHIRURGICAL))->buildEntry();
            $section->appendEntry($entry);
        }
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setCode(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setCodeLoinc($section, '47519-4');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setTitle(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setTitle($section, 'Antécédents chirurgicaux');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setText(CCDAPOCD_MT000040_Section $section): void
    {
        $content = $this->fetchSmarty(
            'Components/Sections/ANS/OtherConditionHistories/fr_antecendent_chirurgicaux',
            [
                'antecedents' => $this->antecedents,
            ]
        );

        CCDADocTools::setText($section, $content);
    }
}
