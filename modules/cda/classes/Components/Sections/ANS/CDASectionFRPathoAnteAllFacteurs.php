<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\ANS;

use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\Components\Sections\ANS\SousSections\CDASectionFRFacteursDeRisques;
use Ox\Interop\Cda\Components\Sections\ANS\SousSections\CDASectionFRPathoAnteAll;
use Ox\Interop\Cda\Components\Sections\CDASection;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Component5;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;

/**
 * Class CDASectionFRPathoAnteAllFacteurs
 *
 * Pathologies - Antecedents - Allergies - Facteurs de risques
 *
 * @package Ox\Interop\Cda\Components\Sections\ANS
 */
class CDASectionFRPathoAnteAllFacteurs extends CDASection
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.2.29';

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    public function buildEntries(CCDAPOCD_MT000040_Section $section): void
    {
        // Entry - FR Pathologies Antecedents Allergies [1..1]
        $this->buildComponentPAA($section);

        // Entry - FR Facteurs de risques [1..1]
        $this->buildCompononentFacteursRisques($section);
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setCode(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setCodeLoinc($section, '46612-8');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setTitle(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setTitle($section, 'Pathologies en cours, antécédents, allergies et facteurs de risque');
    }

    /**
     * Entry - FR Pathologies Antecedents Allergies [1..1]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildComponentPAA(CCDAPOCD_MT000040_Section $section): void
    {
        /** @var CCDAPOCD_MT000040_Section $sub_section */
        $component   = new CCDAPOCD_MT000040_Component5();
        $sub_section = (new CDASectionFRPathoAnteAll($this->factory))->build();
        $component->setSection($sub_section);
        $section->appendComponent($component);
    }

    /**
     * Entry - FR Facteurs de risques [1..1]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildCompononentFacteursRisques(CCDAPOCD_MT000040_Section $section): void
    {
        /** @var CCDAPOCD_MT000040_Section $sub_section */
        $component   = new CCDAPOCD_MT000040_Component5();
        $sub_section = (new CDASectionFRFacteursDeRisques($this->factory))->build();
        $component->setSection($sub_section);
        $section->appendComponent($component);
    }
}
