<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\ANS\Medications;

use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\Components\Entries\ANS\Medications\CDAEntryFRTraitement;
use Ox\Interop\Cda\Components\Sections\IHE\Medications\CDASectionMedications;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;

/**
 * Class CDASectionFRTraitements
 *
 * @package Ox\Interop\Cda\Components\Sections\HL7\Medications
 */
class CDASectionFRTraitements extends CDASectionMedications
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.2.143';

    /**
     * Entries - FR-Traitement [1..*]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntriesMedications(CCDAPOCD_MT000040_Section $section): void
    {
        $dossier_medical = $this->factory->patient->loadRefDossierMedical();
        $prescription = $dossier_medical->loadRefPrescription();

        /** @var CPrescriptionLineMedicament $_prescription_line */
        // Entries - FR-Traitement [1..*]
        foreach ($prescription->_ref_prescription_lines as $prescription_line) {
            if (!$prescription_line->debut && !$prescription_line->fin) {
                continue;
            }
            $entry = (new CDAEntryFRTraitement($this->factory, $prescription_line))->buildEntry();
            $section->appendEntry($entry);
        }
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setTitle(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setTitle($section, 'Médications');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected  function setCode(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setCodeLoinc($section, "10160-0", "Traitements");
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setText(CCDAPOCD_MT000040_Section $section): void
    {
        $dossier_medical = $this->factory->patient->loadRefDossierMedical();
        $prescription    = $dossier_medical->loadRefPrescription();

        foreach ($prescription->_ref_prescription_lines as $_prescription_line) {
            /** @var CPrescriptionLineMedicament $_prescription_line */
            $_prescription_line->loadRefsPrises();
        }

        $content = $this->fetchSmarty(
            'Components/Sections/ANS/Medications/fr_traitements',
            [
                'prescription_lines' => $prescription->_ref_prescription_lines,
            ]
        );

        CCDADocTools::setText($section, $content);
    }
}
