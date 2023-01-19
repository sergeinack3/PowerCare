<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\ANS\Medications;

use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\ANS\Medications\CDAEntryFRTraitement;
use Ox\Interop\Cda\Components\Sections\IHE\Medications\CDASectionAdmissionMedicationHistory;
use Ox\Interop\Cda\Exception\CCDAException;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;

class CDASectionFRTraitementsAdmission extends CDASectionAdmissionMedicationHistory
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.2.144';

    /** @var CPrescriptionLineMedicament[] */
    protected $prescription_lines = [];

    public function __construct(CCDAFactory $factory)
    {
        parent::__construct($factory);

        $dossier_medical = $factory->patient->loadRefDossierMedical();
        if (!$dossier_medical || !$dossier_medical->_id) {
            return;
        }

        $prescription             = $dossier_medical->loadRefPrescription();
        $this->prescription_lines = array_filter(
            $prescription->_ref_prescription_lines,
            function ($prescription_line) {
                $prescription_line->loadRefsPrises();

                return $prescription_line->long_cours;
            }
        );
    }

    /**
     * Entries - FR Traitement [1..*]
     *
     * @param CCDAPOCD_MT000040_Section $section
     *
     * @throws CCDAException
     */
    protected function buildEntriesMedications(CCDAPOCD_MT000040_Section $section): void
    {
        // empty prescription line
        $prescription_lines = $this->prescription_lines;
        if (empty($prescription_lines)) {
            $prescription_lines[] = null;
        }

        // Entries - Fr Traitement [1..*]
        foreach ($this->prescription_lines as $prescription_line) {
            $entry = (new CDAEntryFRTraitement($this->factory, $prescription_line))->buildEntry();
            $section->appendEntry($entry);
        }
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    public function setCode(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setCodeLoinc($section, '42346-7');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    public function setTitle(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setTitle($section, "Traitements à l'admission");
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    public function setText(CCDAPOCD_MT000040_Section $section): void
    {
        $content = $this->fetchSmarty(
            'Components/Sections/ANS/Medications/fr_traitements_admission',
            [
                'prescription_lines' => $this->prescription_lines,
            ]
        );

        CCDADocTools::setText($section, $content);
    }
}

