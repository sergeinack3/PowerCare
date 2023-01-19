<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Board;

use Exception;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CExamComp;
use Ox\Mediboard\Mediusers\CMediusers;

class ExamCompFinder
{
    private ?string $date_min;

    private ?string $date_intervention;

    private CMediusers $user;

    private CSQLDataSource $ds;

    private array $examens_complementaires = [];

    /**
     * @param CMediusers  $user
     * @param string|null $date_min
     * @param string|null $date_intervention
     *
     * @throws CMbException
     */
    public function __construct(CMediusers $user, ?string $date_min = null, ?string $date_intervention = null)
    {
        $this->ds = CSQLDataSource::get("std");
        if (!$date_min && !$date_intervention) {
            throw new CMbException("Date is mandatory");
        }
        $this->date_min          = $date_min;
        $this->date_intervention = $date_intervention;
        if (!$user->_id) {
            throw new CMbException("User not found");
        }
        $this->user = $user;
    }

    /**
     * @throws Exception
     */
    public function loadExamensComplementaires(): void
    {
        $ljoin   = [
            "consultation"        => "consultation.consultation_id = exams_comp.consultation_id",
            "plageconsult"        => "consultation.plageconsult_id = plageconsult.plageconsult_id",
            "consultation_anesth" => "consultation_anesth.consultation_id = consultation.consultation_id",
        ];
        $ljoin[] = "sejour AS sejour_consult ON sejour_consult.sejour_id = consultation.sejour_id";
        $ljoin[] = "sejour AS sejour_anesth ON sejour_anesth.sejour_id = consultation_anesth.sejour_id";

        if ($this->date_intervention && !$this->date_min) {
            $ljoin[] = "operations AS op_anesth ON op_anesth.sejour_id = sejour_anesth.sejour_id";
            $ljoin[] = "operations AS op_consult ON op_consult.sejour_id = sejour_consult.sejour_id";
        }

        $where   = [
            "plageconsult.chir_id" => $this->ds->prepare("= ?", $this->user->_id),
        ];
        $whereOr = [];
        if ($this->date_min) {
            $whereOr = [
                "sejour_consult.entree " . $this->ds->prepareBetween(
                    "$this->date_min 00:00:00",
                    "$this->date_min 23:59:00"
                ),
                "sejour_anesth.entree " . $this->ds->prepareBetween(
                    "$this->date_min 00:00:00",
                    "$this->date_min 23:59:00"
                ),
            ];
        } elseif ($this->date_intervention) {
            $whereOr = [
                "op_consult.date " . $this->ds->prepare("= ?", $this->date_intervention),
                "op_anesth.date " . $this->ds->prepare("= ?", $this->date_intervention),
            ];
        }
        if (count($whereOr)) {
            $where[] = implode(" OR ", $whereOr);
        }

        $this->examens_complementaires = (new CExamComp())->loadList($where, null, null, "exam_id", $ljoin);

        if (count($this->examens_complementaires)) {
            $this->loadExamensComplReferences();
        }
    }

    /**
     * @return array
     */
    public function getExamensComplementaires(): array
    {
        return $this->examens_complementaires;
    }

    /**
     * @throws Exception
     */
    private function loadExamensComplReferences(): void
    {
        $consultations = CStoredObject::massLoadFwdRef($this->examens_complementaires, "consultation_id");
        CStoredObject::massLoadFwdRef($consultations, "patient_id");
        $consults_anesth = CStoredObject::massLoadBackRefs($consultations, "consult_anesth");
        $sejours = CStoredObject::massLoadFwdRef($consults_anesth, "sejour_id");
        CStoredObject::massLoadFwdRef($sejours, "praticien_id");
        CStoredObject::massLoadFwdRef($consultations, "sejour_id");
        $plages = CStoredObject::massLoadFwdRef($consultations, "plageconsult_id");
        CStoredObject::massLoadFwdRef($plages, "chir_id");

        foreach ($this->examens_complementaires as $_exam) {
            /* @var CExamComp $_exam */
            $consult = $_exam->loadRefConsult();
            $consult->loadRefPlageConsult()->loadRefChir()->loadRefFunction();
            $consult->loadRefPatient();
            $consult->loadRefConsultAnesth()->loadRefSejour()->loadRefPraticien();
            $dossiers                     = $consult->loadRefsDossiersAnesth();
            foreach ($dossiers as $_dossier) {
                $_dossier->loadRefOperation();
                if (
                    (
                        $this->date_min &&
                        $_dossier->_ref_sejour->entree >= CMbDT::dateTime("$this->date_min 00:00:00") &&
                        $_dossier->_ref_sejour->entree <= CMbDT::dateTime("$this->date_min 23:59:00")
                    ) ||
                    (
                        $this->date_intervention &&
                        $_dossier->_ref_operation->date == $this->date_intervention
                    )
                ) {
                    $_dossier->loadRefSejour();
                    $consult->_ref_consult_anesth = $_dossier;
                }
            }

            if (
                (
                    !$consult->_ref_consult_anesth->_id ||
                    !$consult->_ref_consult_anesth->sejour_id
                ) &&
                $consult->sejour_id
            ) {
                $consult->loadRefSejour()->loadRefPraticien()->loadRefFunction();
            }
        }
    }
}
