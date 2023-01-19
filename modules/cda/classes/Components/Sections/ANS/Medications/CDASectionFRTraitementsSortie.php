<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Cda\Components\Sections\ANS\Medications;

use Ox\Core\CMbDT;
use Ox\Interop\Cda\CCDADocTools;
use Ox\Interop\Cda\CCDAFactory;
use Ox\Interop\Cda\Components\Entries\ANS\Medications\CDAEntryFRTraitement;
use Ox\Interop\Cda\Components\Sections\IHE\Medications\CDASectionHospitalDischargeMedications;
use Ox\Interop\Cda\Structure\CCDAPOCD_MT000040_Section;
use Ox\Interop\Eai\CItemReport;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;

class CDASectionFRTraitementsSortie extends CDASectionHospitalDischargeMedications
{
    /** @var string */
    public const TEMPLATE_ID = '1.2.250.1.213.1.1.2.146';

    /** @var CPrescriptionLineMedicament[] */
    protected $prescription_lines = [];

    /**
     * CDASectionFRTraitementsSortie constructor.
     *
     * @param CCDAFactory $factory
     */
    public function __construct(CCDAFactory $factory)
    {
        parent::__construct($factory);

        $patient         = $factory->patient;
        $dossier_medical = $patient->loadRefDossierMedical();
        if (!$dossier_medical || !$dossier_medical->_id) {
            $this->factory->report->addData('CDA-msg-None treatment exit', CItemReport::SEVERITY_ERROR);

            return;
        }

        $prescription = $dossier_medical->loadRefPrescription();


        $this->prescription_lines = array_filter(
            $prescription->_ref_prescription_lines,
            function ($prescription_line) {
                // med start at end of sejour
                $start_at_end = $prescription_line->debut && $prescription_line->debut == CMbDT::format(
                        $this->factory->targetObject->sortie,
                        '%Y-%m-%d'
                    );
                if ($start_at_end) {
                    return true;
                }

                $start_before   = $prescription_line->debut < CMbDT::format(
                        $this->factory->targetObject->entree,
                        '%Y-%m-%d'
                    );
                $continue_after = $prescription_line->fin > CMbDT::format(
                        $this->factory->targetObject->sortie,
                        '%Y-%m-%d'
                    );
                if ($start_before && $continue_after) {
                    return true;
                }

                return false;
            }
        );
    }

    /**
     * Entries - FR-Traitement [1..*]
     *
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function buildEntriesMedications(CCDAPOCD_MT000040_Section $section): void
    {
        $lines = $this->prescription_lines;

        // add empty traitement section
        if (empty($lines)) {
            $lines[] = null;
        }

        // Entries - FR-Traitement [1..*]
        foreach ($lines as $line) {
            $entry = (new CDAEntryFRTraitement($this->factory, $line))->buildEntry();
            $section->appendEntry($entry);
        }
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setCode(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setCodeLoinc($section, '10183-2');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setTitle(CCDAPOCD_MT000040_Section $section): void
    {
        CCDADocTools::setTitle($section, 'Traitements à la sortie');
    }

    /**
     * @param CCDAPOCD_MT000040_Section $section
     */
    protected function setText(CCDAPOCD_MT000040_Section $section): void
    {
        $content = $this->fetchSmarty(
            'Components/Sections/ANS/Medications/fr_traitements_sortie',
            [
                'prescription_lines' => $this->prescription_lines,
            ]
        );

        CCDADocTools::setText($section, $content);
    }
}
