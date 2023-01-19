<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\ANS\SousSections;

use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Sections\ANS\OtherConditionHistories\CDASectionFRAntecedentsFamiliaux;
use Ox\Interop\Cda\Components\Sections\CDASection;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Component5;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;

class CDASectionFRFacteursDeRisques extends CDASection
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.2.31';

    /** @var bool */
    protected $has_atcd_familiaux = false;

    protected $dossier_medical;

    /**
     * CDASectionFRPathoAnteAll constructor.
     *
     * @param CCDAFactory $factory
     */
    public function __construct(CCDAFactory $factory)
    {
        parent::__construct($factory);

        $this->dossier_medical = $this->factory->patient->loadRefDossierMedical();

        $this->has_atcd_familiaux = CDASectionFRAntecedentsFamiliaux::check($this->factory);
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    public function buildEntries(CCDAPOCD_MT000040_Section $section): void
    {
        // Component[Section] - FR Habitus mode de vie [0..1]
        $this->buildComponentHabitus($section);

        // Component[Section] - FR Facteurs de risque professionnels non code [0..1]
        $this->buildComponentFacteurPro($section);

        // Component[Section] - FR Antecedents familiaux [0..1]
        $this->buildComponentAntecedentFamilliaux($section);
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildComponentHabitus(CCDAPOCD_MT000040_Section $section): void
    {
        // not implemented
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildComponentFacteurPro(CCDAPOCD_MT000040_Section $section): void
    {
        // not implemented
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildComponentAntecedentFamilliaux(CCDAPOCD_MT000040_Section $section): void
    {
        $component = new CCDAPOCD_MT000040_Component5();
        if ($this->has_atcd_familiaux) {
            $sub_section = (new CDASectionFRAntecedentsFamiliaux($this->factory))->build();
            $component->setSection($sub_section);
        }

        $section->appendComponent($component);
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setCode(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setCodeLoinc($section, '57207-3');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setTitle(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setTitle($section, 'Facteurs de risque');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setText(CCDAPOCD_MT000040_Section $section): void
    {
        $patient = $this->factory->patient;
        $dossier_medical = $patient->loadRefDossierMedical();

        $content = $this->fetchSmarty(
            'Components/Sections/ANS/Sous_Sections/fr_facteurs_de_risques',
            [
                'dossier_medical' => $dossier_medical,
            ]
        );

        CCDADocTools::setText($section, $content);
    }
}
