<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\ANS;

use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Sections\ANS\Medications\CDASectionFRTraitements;
use Ox\Interop\Cda\Components\Sections\CDASection;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Component5;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;

class CDASectionFRPlanDeTraitement extends CDASection
{
    /** @var string  */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.2.32';

    /**
     * CDASectionFRPlanDeTraitement constructor.
     *
     * @param CCDAFactory $factory
     */
    public function __construct(CCDAFactory $factory)
    {
        parent::__construct($factory);
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    public function buildEntries(CCDAPOCD_MT000040_Section $section): void
    {
        // Entry - FR Traitements [1..1]
        $this->buildEntryMedication($section);
    }

    /**
     * Entry - FR Traitements [1..1]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntryMedication(CCDAPOCD_MT000040_Section $section): void
    {
        if (!$this->hasMedications()) {
            return;
        }

        // add Section Fr - Traitements
        $section_fr_traitements = (new CDASectionFRTraitements($this->factory))->build();
        $component = new CCDAPOCD_MT000040_Component5();
        $component->setSection($section_fr_traitements);

        $section->appendComponent($component);
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setCode(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setCodeLoinc($section, "18776-5");
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setTitle(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setTitle($section, "Traitements au long cours");
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setText(CCDAPOCD_MT000040_Section $section): void
    {
        if ($this->hasMedications()) {
            return;
        }

        $content = $this->fetchSmarty(
            'Components/Sections/ANS/fr_plan_de_traitement'
        );

        CCDADocTools::setText($section, $content);
    }

    /**
     * @return bool
     */
    private function hasMedications(): bool
    {
        $dossier_medical = $this->factory->patient->loadRefDossierMedical();
        if (!$dossier_medical || !$dossier_medical->_id) {
            return false;
        }

        $prescription = $dossier_medical->loadRefPrescription();

        return $prescription && $prescription->_id && !empty($prescription->_ref_prescription_lines);
    }
}
