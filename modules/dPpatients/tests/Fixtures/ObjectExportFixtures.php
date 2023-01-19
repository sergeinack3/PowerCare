<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Tests\Fixtures;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Ccam\CDevisCodage;
use Ox\Mediboard\Facturation\CFactureEtablissement;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Tests\Fixtures\Fixtures;

/**
 * Generate data that allow the use of export functionnalities.
 */
class ObjectExportFixtures extends Fixtures
{
    public const EXPORT_TAG                   = 'export';
    public const EXPORT_TAG_PATIENT_FILE      = 'export_patient_file';
    public const EXPORT_TAG_PRESCRIPTION_FILE = 'export_prescription_file';
    public const EXPORT_TAG_FACTURE_FILE      = 'export_facture_file';
    public const EXPORT_TAG_DEVIS_FILE        = 'export_devis_file';

    public function load()
    {
        $prat = $this->getUser();

        /** @var CPatient $patient_with_data */
        $patient_with_data = CPatient::getSampleObject();
        if (CAppUI::isGroup()) {
            $patient_with_data->group_id = $prat->loadRefFunction()->group_id;
        } elseif (CAppUI::isCabinet()) {
            $patient_with_data->function_id = $prat->loadRefFunction()->_id;
        }

        $this->store($patient_with_data);

        $file = $this->buildFile($patient_with_data);
        $this->store($file, self::EXPORT_TAG_PATIENT_FILE);

        $presc = $this->buildPrescription($patient_with_data);
        $this->store($presc);

        $file = $this->buildFile($presc);
        $this->store($file, self::EXPORT_TAG_PRESCRIPTION_FILE);

        $facture = $this->buildFacture($patient_with_data);
        $this->store($facture);

        $file = $this->buildFile($facture);
        $this->store($file, self::EXPORT_TAG_FACTURE_FILE);

        $devis = $this->buildDevis($patient_with_data, $prat);
        $this->store($devis);

        $file = $this->buildFile($devis);
        $this->store($file, self::EXPORT_TAG_DEVIS_FILE);

        /** @var CPatient $empty_patient */
        $empty_patient = CPatient::getSampleObject();
        $this->store($empty_patient);

        for ($i = 0; $i < 5; $i++) {
            $consult = $this->buildConsultation($patient_with_data, $prat);
            $this->store($consult);
        }
    }

    private function buildFile(CStoredObject $target): CFile
    {
        $file               = new CFile();
        $file->file_name    = uniqid();
        $file->object_class = $target->_class;
        $file->object_id    = $target->_id;
        $file->setContent('data');
        $file->fillFields();

        return $file;
    }

    private function buildPrescription(CPatient $patient): CPrescription
    {
        $prat = $this->getUser();

        $sejour                = new CSejour();
        $sejour->patient_id    = $patient->_id;
        $sejour->praticien_id  = $prat->_id;
        $sejour->group_id      = $prat->loadRefFunction()->loadRefGroup()->_id;
        $sejour->entree_prevue = CMbDT::getRandomDate(CMbDT::dateTime('-1 YEAR'), CMbDT::dateTime());
        $sejour->sortie_prevue = CMbDT::dateTime('+1 DAY', $sejour->entree_prevue);
        $sejour->libelle       = uniqid();
        $this->store($sejour);

        $prescription               = new CPrescription();
        $prescription->object_class = $sejour->_class;
        $prescription->object_id    = $sejour->_id;
        $prescription->type         = 'sejour';
        $prescription->libelle      = uniqid();

        return $prescription;
    }

    private function buildFacture(CPatient $patient): CFactureEtablissement
    {
        $facture             = new CFactureEtablissement();
        $facture->patient_id = $patient->_id;
        $facture->ouverture  = CMbDT::dateTime();

        return $facture;
    }

    private function buildDevis(CPatient $patient, CMediusers $prat): CDevisCodage
    {
        $consult = $this->buildConsultation($patient, $prat);
        $this->store($consult);

        $devis                = new CDevisCodage();
        $devis->codable_class = $consult->_class;
        $devis->codable_id    = $consult->_id;
        $devis->patient_id    = $patient->_id;
        $devis->praticien_id  = $consult->loadRefPlageConsult()->chir_id;
        $devis->creation_date = CMbDT::dateTime();
        $devis->loadMatchingObjectEsc();

        return $devis;
    }

    private function buildConsultation(CPatient $patient, CMediusers $prat): CConsultation
    {
        $plage                    = $this->createPlageConsult($prat);
        $consult                  = new CConsultation();
        $consult->plageconsult_id = $plage->_id;
        $consult->patient_id      = $patient->_id;
        $consult->heure           = '08:00:00';
        $consult->chrono          = 16;
        $consult->motif           = uniqid();

        return $consult;
    }

    private function createPlageConsult(CMediusers $mediusers): CPlageconsult
    {
        $plage          = new CPlageconsult();
        $plage->chir_id = $mediusers->_id;
        $plage->date    = CMbDT::date();
        $plage->debut   = '08:00:00';
        $plage->fin     = '20:00:00';
        $plage->freq    = '00:15:00';
        $plage->loadMatchingObjectEsc();
        $this->store($plage);

        return $plage;
    }
}
