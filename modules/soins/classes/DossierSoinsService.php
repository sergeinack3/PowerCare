<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Soins;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CSQLDataSource;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Admin\CBrisDeGlace;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientSignature;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\System\Forms\CExClassEvent;

/**
 *
 */
class DossierSoinsService implements IShortNameAutoloadable
{
    /** @var CSejour */
    private $sejour;

    /** @var CPatient */
    private $patient;

    /** @var COperation */
    private $operation;

    /** @var CModule|CModule[]|null */
    public $isPrescriptionInstalled;

    /** @var string */
    public $date;

    /** @var array */
    public $form_tabs;

    /**
     * @throws CMbException
     * @throws Exception
     */
    public function __construct(string $sejour_id, string $date, ?string $operation_id = null)
    {
        $this->sejour = (new CSejour())->load($sejour_id);

        if (!$this->sejour) {
            throw new CMbException("CSejour-not found");
        } else {
            $this->patient = $this->sejour->loadRefPatient();
        }

        $this->isPrescriptionInstalled = CModule::getActive("dPprescription");

        if ($operation_id) {
            $operation = (new COperation())->load($operation_id);

            $this->operation = $operation->_id ? $operation : null;
        }

        $this->date = $date;

        $this->loadDossierSoinsReferences();
    }

    /**
     * Getter sejour
     *
     * @return CSejour
     */
    public function getSejour(): CSejour
    {
        return $this->sejour;
    }

    /**
     * Getter patient
     *
     * @return CPatient
     */
    public function getPatient(): CPatient
    {
        return $this->patient;
    }

    /**
     * Getter date of care plan
     *
     * @return string
     */
    public function getDatePlanSoins(): string
    {
        if ($this->sejour->sortie_reelle && $this->date > CMbDT::date($this->sejour->sortie_reelle)) {
            return CMbDT::date($this->sejour->entree);
        }

        return $this->date;
    }

    /**
     * Getter Operation
     *
     * @return COperation
     */
    public function getOperation(): ?COperation
    {
        return $this->operation;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function loadDossierSoins(): void
    {
        CAccessMedicalData::logAccess($this->getSejour());

        $this->getSejour()->loadNDA(); // Needed in the head banner

        if (CBrisDeGlace::isBrisDeGlaceRequired() && !CAccessMedicalData::checkForSejour($this->getSejour())) {
            CAppUI::accessDenied();
        }
        CAccessMedicalData::logAccess($this->getSejour());

        $this->getSejour()->countAlertsNotHandled("medium", "observation");
    }

    /**
     * @throws Exception
     */
    private function loadDossierSoinsReferences(): void
    {
        $patient = $this->getPatient();
        $sejour  = $this->getSejour();
        if ($this->isPrescriptionInstalled) {
            CPrescription::$_load_lite = true;
        }

        // A faire avant le traitement de la prescription car elle est écrasée au chargement
        if (CModule::getActive("forms")) {
            $objects = [
                ["tab_dossier_soins_obs_entree", $this->getSejour()->loadRefObsEntree()],
                ["tab_dossier_soins", $this->getSejour()],
            ];

            $this->form_tabs = CExClassEvent::getTabEvents($objects);
        }

        $this->getSejour()->loadRefPraticien();
        $this->getSejour()->loadJourOp($this->date);
        if ($this->isPrescriptionInstalled) {
            $prescription_sejour = $this->getSejour()->loadRefPrescriptionSejour();
            $prescription_sejour->loadJourOp($this->date);
            $prescription_sejour->loadRefCurrentPraticien();
            $prescription_sejour->loadLinesElementImportant();
        }
        $this->getSejour()->_ref_prescription_sejour->loadRefsLinesElement();

        $patient->countINS();
        $patient->loadRefsNotes();
        $patient->_homonyme = count($patient->getPhoning($this->date));
        $sejour->loadRefsOperations();
        $sejour->loadRefsActesCCAM();
        $sejour->loadRefsActesNGAP();
        $sejour->loadPatientBanner($this->getSejour());
        $sejour->loadRefsRDVExternes();

        if (CModule::getActive("maternite")) {
            $sejour->loadRefGrossesse()->loadRefDossierPerinat();
            $naissance = $sejour->loadRefNaissance();
            $naissance->loadRefGrossesse();
            $naissance->loadRefSejourMaman();
            $patient->loadLastGrossesse();

            if ($patient->civilite == "enf") {
                $sejour->_ref_naissance->_ref_sejour_maman->loadRefPatient()->loadLastGrossesse();
            }
        }

        if ($this->getOperation()) {
            CAccessMedicalData::logAccess($this->getOperation());

            $this->getOperation()->loadRefPlageOp();
            $this->getOperation()->_ref_anesth->loadRefFunction();
        }

        if ($this->isPrescriptionInstalled) {
            CPrescription::$_load_lite = false;
        }
    }

    /**
     * @param string $sejour_id
     *
     * @return array
     * @throws Exception
     */
    public function getLateObjectifsSoins(): array
    {
        $ds = CSQLDataSource::get("std");
        //On récupère le nombre d'objectifs de soin dont l'échéance est dépassée
        $where = [
            "delai"     => $ds->prepare("< ?", CMbDT::date()),
            "sejour_id" => $ds->prepare("= ?", $this->getSejour()->_id),
            "statut"    => $ds->prepare("= ?", 'ouvert'),
        ];

        return (new CObjectifSoin())->loadList($where);
    }
}
