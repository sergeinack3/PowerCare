<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Fixtures;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CModelObjectException;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Mpm\CPrescriptionLineMedicament;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CPathologie;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CTraitement;
use Ox\Mediboard\Prescription\CCategoryPrescription;
use Ox\Mediboard\Prescription\CElementPrescription;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\FixturesSkippedException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * Create simple dossier medical with pathology link
 */
class DossierMedicalFixtures extends Fixtures implements GroupFixturesInterface
{
    public const DOSSIER_MEDICAL                             = 'dossier_medical';
    public const DOSSIER_MEDICAL_LINE_MEDICAMENT_END_AFTER   = 'dossier_medical_line_medicament_end_after';
    public const DOSSIER_MEDICAL_LINE_MEDICAMENT_WITHOUT_END = 'dossier_medical_line_medicament_without_end';
    public const DOSSIER_MEDICAL_LINE_ELEMENT_END_AFTER      = 'dossier_medical_line_element_end_after';
    public const DOSSIER_MEDICAL_LINE_ELEMENT_WITHOUT_END    = 'dossier_medical_line_element_without_end';
    public const DOSSIER_MEDICAL_TRAITEMENT_END_AFTER        = 'dossier_medical_traitement_end_after';
    public const DOSSIER_MEDICAL_TRAITEMENT_WITHOUT_END      = 'dossier_medical_traitement_without_end';

    /**
     * @throws Exception
     * @throws FixturesException
     * @throws CModelObjectException
     */
    public function load(): void
    {
        try {
            CSQLDatasource::get(CAppUI::conf("bcb CBcbObject dsn"));
        } catch (Exception $e) {
            throw new FixturesSkippedException('BCB datasource is needed but could not be found');
        }

        /** @var CPatient $patient */
        $patient               = CPatient::getSampleObject();
        $patient->naissance    = CMbDT::getRandomDate('1850-01-01', CMbDT::date(), 'Y-m-d');
        $patient->cp           = 17000;
        $patient->cp_naissance = 17000;
        $this->store($patient, self::DOSSIER_MEDICAL);

        $dossier = $this->createDossierMedical($patient);

        try {
            CSQLDatasource::get(CAppUI::conf("bcb CBcbObject dsn"));
        } catch (Exception $e) {
            throw new FixturesSkippedException('BCB datasource is needed but could not be found');
        }

        $this->createTraitementPersonnel($dossier);
        //$this->createPathologie($dossier);
    }

    private function createTraitementPersonnel(CDossierMedical $dossier_medical)
    {
        $prescription               = new CPrescription();
        $prescription->object_class = "CDossierMedical";
        $prescription->object_id    = $dossier_medical->_id;
        $prescription->type         = "traitement";
        $this->store($prescription);

        $line_med_1                  = new CPrescriptionLineMedicament();
        $line_med_1->prescription_id = $prescription->_id;
        $line_med_1->creator_id      = $this->getUser(false)->_id;
        $line_med_1->fin             = CMbDT::date("+3 days");
        $this->store($line_med_1, self::DOSSIER_MEDICAL_LINE_MEDICAMENT_END_AFTER);

        $line_med_2                  = new CPrescriptionLineMedicament();
        $line_med_2->prescription_id = $prescription->_id;
        $line_med_2->creator_id      = $this->getUser(false)->_id;
        $this->store($line_med_2, self::DOSSIER_MEDICAL_LINE_MEDICAMENT_WITHOUT_END);

        $line_med_3                  = new CPrescriptionLineMedicament();
        $line_med_3->prescription_id = $prescription->_id;
        $line_med_3->creator_id      = $this->getUser(false)->_id;
        $line_med_3->fin             = CMbDT::date("-3 days");
        $this->store($line_med_3);

        $category           = new CCategoryPrescription();
        $category->chapitre = "biologie";
        $category->nom      = "fixture dossier medical";
        $this->store($category);

        $element_prescription                           = new CElementPrescription();
        $element_prescription->category_prescription_id = $category->_id;
        $element_prescription->libelle                  = "fixture dossier medical";
        $this->store($element_prescription);

        $line_element_1                          = new CPrescriptionLineElement();
        $line_element_1->prescription_id         = $prescription->_id;
        $line_element_1->creator_id              = $this->getUser(false)->_id;
        $line_element_1->element_prescription_id = $element_prescription->_id;
        $line_element_1->fin                     = CMbDT::date("+3 days");
        $this->store($line_element_1, self::DOSSIER_MEDICAL_LINE_ELEMENT_END_AFTER);

        $line_element_2                          = new CPrescriptionLineElement();
        $line_element_2->prescription_id         = $prescription->_id;
        $line_element_2->creator_id              = $this->getUser(false)->_id;
        $line_element_2->element_prescription_id = $element_prescription->_id;
        $this->store($line_element_2, self::DOSSIER_MEDICAL_LINE_ELEMENT_WITHOUT_END);

        $line_element_3                          = new CPrescriptionLineElement();
        $line_element_3->prescription_id         = $prescription->_id;
        $line_element_3->creator_id              = $this->getUser(false)->_id;
        $line_element_3->fin                     = CMbDT::date("-3 days");
        $line_element_3->element_prescription_id = $element_prescription->_id;
        $this->store($line_element_3);

        $traitement_1                     = new CTraitement();
        $traitement_1->dossier_medical_id = $dossier_medical->_id;
        $traitement_1->fin                = CMbDT::date("+3 days");
        $this->store($traitement_1, self::DOSSIER_MEDICAL_TRAITEMENT_END_AFTER);

        $traitement_2                     = new CTraitement();
        $traitement_2->dossier_medical_id = $dossier_medical->_id;
        $this->store($traitement_2, self::DOSSIER_MEDICAL_TRAITEMENT_WITHOUT_END);

        $traitement_3                     = new CTraitement();
        $traitement_3->dossier_medical_id = $dossier_medical->_id;
        $traitement_3->fin                = CMbDT::date("-3 days");
        $this->store($traitement_3);

        $traitement_4                     = new CTraitement();
        $traitement_4->dossier_medical_id = $dossier_medical->_id;
        $traitement_4->annule             = true;
        $this->store($traitement_4);
    }

    /**
     * @param CPatient $patient
     *
     * @return void
     * @throws FixturesException
     */
    public function createDossierMedical(CPatient $patient): CDossierMedical
    {
        $dossier               = new CDossierMedical();
        $dossier->object_id    = $patient->_id;
        $dossier->object_class = "CPatient";
        $this->store($dossier, self::DOSSIER_MEDICAL);

        return $dossier;
    }

    /**
     * @param CPatient        $patient
     * @param CDossierMedical $dossier
     *
     * @return void
     */
    public function createPathologie(CDossierMedical $dossier): void
    {
        $pathologie                     = new CPathologie();
        $pathologie->dossier_medical_id = $dossier->_id;
        $pathologie->creation_date      = CMbDT::dateTime("10/10/2021");
        $pathologie->pathologie         = "pathologie";
        $pathologie->type               = "pathologie";
        $this->store($pathologie, self::DOSSIER_MEDICAL);
    }

    public static function getGroup(): array
    {
        return ['dossier_medical'];
    }
}
