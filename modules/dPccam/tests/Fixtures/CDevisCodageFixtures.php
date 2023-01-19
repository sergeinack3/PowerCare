<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam\Tests\Fixtures;

use Ox\Core\CMbDT;
use Ox\Core\CModelObjectException;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Ccam\CDevisCodage;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Patients\CPatient;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;

class CDevisCodageFixtures extends Fixtures
{
    public const TAG_DEVIS_CODAGE = 'devis_codage';

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    public function load(): void
    {
        $consultation = $this->generateConsultation();
        $this->generateDevisCodage($consultation, self::TAG_DEVIS_CODAGE);
        $this->generateCategory();
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    private function generateConsultation(): CConsultation
    {
        $praticien = $this->getUser();

        $patient = CPatient::getSampleObject();
        $this->store($patient);

        /** @var CPlageconsult $plage */
        $plage          = new CPlageconsult();
        $plage->chir_id = $praticien->_id;
        $plage->date    = 'now';
        $plage->debut   = CMbDT::time("08:00:00");
        $plage->fin     = CMbDT::time("18:00:00");
        $plage->freq    = CMbDT::time("00:30:00");
        $this->store($plage);

        $consultation = CConsultation::getSampleObject();
        $consultation->patient_id = $patient->_id;
        $consultation->plageconsult_id = $plage->_id;
        $consultation->heure = $plage->debut;
        $this->store($consultation);

        return $consultation;
    }

    /**
     * @throws FixturesException
     */
    private function generateDevisCodage(CConsultation $consultation, string $tag = null): void
    {
        $devis_codage = new CDevisCodage();
        $devis_codage->praticien_id  = $consultation->loadRefPraticien()->_id;
        $devis_codage->codable_class = $consultation->_class;
        $devis_codage->codable_id    = $consultation->_id;
        $devis_codage->patient_id    = $consultation->patient_id;
        $devis_codage->date          = 'now';
        $devis_codage->creation_date = "now";

        $this->store($devis_codage, $tag);
    }

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     */
    private function generateCategory(): void
    {
        $file_category        = CFilesCategory::getSampleObject();
        $file_category->class = 'CDevisCodage';
        $this->store($file_category);
    }
}
