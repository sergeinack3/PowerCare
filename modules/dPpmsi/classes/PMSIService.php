<?php

/**
 * @package Mediboard\Ameli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Pmsi;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Fse\CFseFactory;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

class PMSIService implements IShortNameAutoloadable
{
    /**
     * Get a PSMI Patient
     * @param int $patient_id
     *
     * @return CPatient
     * @throws CMbModelNotFoundException
     */
    private function getPatientPMSI(int $patient_id): CPatient
    {
        $patient = CPatient::findOrFail($patient_id);
        $patient->loadIPP();
        $patient->loadRefsCorrespondants();
        $patient->loadRefPhotoIdentite();
        $patient->loadPatientLinks();
        $patient->countINS();
        $patient->updateBMRBHReStatus();

        if (CModule::getActive("fse")) {
            $cv = CFseFactory::createCV();
            if ($cv) {
                $cv->loadIdVitale($patient);
            }
        }

        return $patient;
    }

    /**
     * Get a PMSI Stay
     * @param CSejour  $sejour
     * @param CPatient $patient
     *
     * @return CSejour
     * @throws Exception
     */
    private function getStayPMSI(CSejour $sejour, CPatient $patient): CSejour
    {
        $sejour->canDo();
        $sejour->loadNDA();
        $sejour->loadExtDiagnostics();
        $affectations = $sejour->loadRefsAffectations();
        CAffectation::massUpdateView($affectations);
        $cibles = $last_trans_cible = $users = $functions = [];
        $sejour->loadSuiviMedical(null, null, $cibles, $last_trans_cible, null, $users,null, $functions, 1);
        $sejour->loadRefRPU();
        $sejour->_ref_patient = $patient;

        foreach ($sejour->loadRefsOperations() as $_op) {
            $_op->loadRefChirs();
            $_op->loadRefPlageOp();
            $_op->loadRefAnesth();
            $_op->loadRefsConsultAnesth();
            $_op->loadRefBrancardage();
        }

        $sejour->loadRefsConsultAnesth();

        foreach ($sejour->loadRefsActesCCAM() as $_acte) {
            $_acte->loadRefExecutant();
        }

        return $sejour;
    }

    /**
     * Get a Maternity Stay
     * @param CNaissance $naissance_enf
     *
     * @return CNaissance
     * @throws Exception
     */
    private function getMaternityStayPMSI(CNaissance $naissance_enf): CNaissance
    {
        $naissance_enf->canDo();
        $naissance_enf->loadRefGrossesse();

        $sejour_enf = $naissance_enf->loadRefSejourEnfant();
        $sejour_enf->loadRelPatient();
        $sejour_enf->loadRefUFHebergement();
        $sejour_enf->loadRefUFMedicale();
        $sejour_enf->loadRefUFSoins();
        $sejour_enf->loadRefService();
        $sejour_enf->loadRefsNotes();

        return $naissance_enf;
    }

    /**
     * Get a Mother stay
     * @param CSejour   $sejour_maman
     * @param bool|null $maternity
     *
     * @return CSejour
     * @throws Exception
     */
    private function getMotherStayPMSI(CSejour $sejour_maman, ?bool $maternity = false): CSejour
    {
        $sejour_maman->canDo();
        $sejour_maman->loadRefUFHebergement();
        $sejour_maman->loadRefUFMedicale();
        $sejour_maman->loadRefUFSoins();
        $sejour_maman->loadRefService();
        $sejour_maman->loadRefsNotes();
        $sejour_maman->loadRefGrossesse();
        $sejour_maman->_ref_grossesse->canDo();

        $grossesse = $sejour_maman->_ref_grossesse;
        $grossesse->loadLastAllaitement();
        $grossesse->loadFwdRef("group_id");

        foreach ($grossesse->loadRefsNaissances() as $_naissance) {
            $_naissance->loadRefSejourEnfant();
            $_naissance->_ref_sejour_enfant->loadRelPatient();
        }

        if ($maternity) {
            $sejour_maman->loadRefRPU();
            $sejour_maman->_ref_patient = $grossesse->loadRefParturiente();
        }

        return $sejour_maman;
    }

    /**
     * Get PMSI Stay dossier
     * @param int  $patient_id
     * @param int  $sejour_id
     * @param bool $log
     *
     * @return array
     * @throws CMbModelNotFoundException
     * @throws Exception
     */
    public function getStayDossierPMSI(int $patient_id, int $sejour_id, bool $log = true): array
    {
        $patient = $this->getPatientPMSI($patient_id);

        $sejour = CSejour::findOrNew($sejour_id);

        if ($log === true) {
            CAccessMedicalData::logAccess($sejour);
        }

        if ($sejour->patient_id == $patient->_id) {
            $sejour = $this->getStayPMSI($sejour, $patient);
            $naissance_enf = $sejour->loadUniqueBackRef("naissance");
            if ($naissance_enf && $naissance_enf->_id) {
                $naissance_enf = $this->getMaternityStayPMSI($naissance_enf);
                $sejour_maman = $naissance_enf->loadRefSejourMaman();
                if ($sejour_maman && $sejour_maman->_id) {
                    $sejour_maman = $this->getMotherStayPMSI($sejour_maman, true);
                }
            }

            if ($sejour->grossesse_id) {
                $sejour = $this->getMotherStayPMSI($sejour);
            }
        }

        return [
            "patient"       => $patient,
            "sejour"        => $sejour,
            "sejour_maman"  => isset($sejour_maman->_id) ? $sejour_maman : null,
            "naissance_enf" => isset($naissance_enf->_id) ? $naissance_enf : null,
        ];
    }
}
