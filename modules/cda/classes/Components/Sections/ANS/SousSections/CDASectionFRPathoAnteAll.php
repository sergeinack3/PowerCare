<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\ANS\SousSections;

use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Sections\ANS\OtherConditionHistories\CDASectionFRAllergiesEtIntolerances;
use Ox\Interop\Cda\Components\Sections\ANS\OtherConditionHistories\CDASectionFRAntecedentsChirurgicaux;
use Ox\Interop\Cda\Components\Sections\ANS\OtherConditionHistories\CDASectionFRAntecedentsMedicaux;
use Ox\Interop\Cda\Components\Sections\ANS\OtherConditionHistories\CDASectionFRProblemesActifs;
use Ox\Interop\Cda\Components\Sections\CDASection;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Component5;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;

class CDASectionFRPathoAnteAll extends CDASection
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.2.30';

    protected $dossier_medical;

    /** @var bool */
    protected $has_antecedents_chir = false;
    /** @var bool */
    protected $has_antecedents_med = false;
    /** @var bool */
    protected $has_phatologie = false;
    /** @var bool */
    protected $has_allergies = false;

    /**
     * CDASectionFRPathoAnteAll constructor.
     *
     * @param CCDAFactory $factory
     */
    public function __construct(CCDAFactory $factory)
    {
        parent::__construct($factory);

        $this->dossier_medical = $this->factory->patient->loadRefDossierMedical();

        $this->has_allergies        = CDASectionFRAllergiesEtIntolerances::check($this->factory);
        $this->has_phatologie       = CDASectionFRProblemesActifs::check($this->factory);
        $this->has_antecedents_chir = CDASectionFRAntecedentsChirurgicaux::check($this->factory);
        $this->has_antecedents_med  = CDASectionFRAntecedentsMedicaux::check($this->factory);
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    public function buildEntries(CCDAPOCD_MT000040_Section $section): void
    {
        // Entry - FR Problemes actifs [0..1]
        $this->buildComponentProblemeActifs($section);

        // Entry - FR Antecedents medicaux [0..1]
        $this->buildComponentAntecedentsMedicaux($section);

        // Entry - FR Antecedents chirurgicaux [0..1]
        $this->buildComponentAntecedentsChirurgicaux($section);

        // Entry FR Allergies et intolerances [0..1]
        $this->buildComponentAllergiesIntolerances($section);
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setCode(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setCodeLoinc($section, '34117-2');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setTitle(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setTitle($section, 'Pathologie en cours, antécédents et allergies');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setText(CCDAPOCD_MT000040_Section $section): void
    {
        // Text section is needed ?
        if (!$this->checkNecessaryTextForSection()) {
            return;
        }

        CCDADocTools::setText($section, 'Aucune pathologie, aucun antécédent et aucune allergie');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildComponentProblemeActifs(CCDAPOCD_MT000040_Section $section): void
    {
        $component = new CCDAPOCD_MT000040_Component5();
        if ($this->has_phatologie) {
            $sub_section = (new CDASectionFRProblemesActifs($this->factory))->build();
            $component->setSection($sub_section);
        }

        $section->appendComponent($component);
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildComponentAntecedentsMedicaux(CCDAPOCD_MT000040_Section $section): void
    {
        $component = new CCDAPOCD_MT000040_Component5();

        if ($this->has_antecedents_med) {
            $sub_section = (new CDASectionFRAntecedentsMedicaux($this->factory))->build();
            $component->setSection($sub_section);
        }

        $section->appendComponent($component);
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildComponentAntecedentsChirurgicaux(CCDAPOCD_MT000040_Section $section): void
    {
        $component = new CCDAPOCD_MT000040_Component5();

        if ($this->has_antecedents_chir) {
            $sub_section = (new CDASectionFRAntecedentsChirurgicaux($this->factory))->build();
            $component->setSection($sub_section);
        }

        $section->appendComponent($component);
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildComponentAllergiesIntolerances(CCDAPOCD_MT000040_Section $section): void
    {
        $component = new CCDAPOCD_MT000040_Component5();

        if ($this->has_allergies) {
            $sub_section = (new CDASectionFRAllergiesEtIntolerances($this->factory))->build();
            $component->setSection($sub_section);
        }

        $section->appendComponent($component);
    }

    /**
     * Check if necessary to put balise <text> or not
     *
     * @param CCDAPOCD_MT000040_Section $section
     * @param CCDAFactory               $factory
     *
     * @return bool
     */
    private function checkNecessaryTextForSection()
    {
        // Section avec texte obligatoire si aucune des 4 sous sections fille n'est présente
        // Vérification patho active, ATCD médical, ATDC chir, allergie

        return !($this->has_antecedents_chir
            || $this->has_antecedents_med
            || $this->has_phatologie
            || $this->has_allergies);
    }
}
