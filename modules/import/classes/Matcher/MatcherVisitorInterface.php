<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Matcher;

use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\ObservationResult\CObservationAbnormalFlag;
use Ox\Mediboard\ObservationResult\CObservationIdentifier;
use Ox\Mediboard\ObservationResult\CObservationResponsibleObserver;
use Ox\Mediboard\ObservationResult\CObservationResult;
use Ox\Mediboard\ObservationResult\CObservationResultExamen;
use Ox\Mediboard\ObservationResult\CObservationResultSet;
use Ox\Mediboard\ObservationResult\CObservationResultValue;
use Ox\Mediboard\ObservationResult\CObservationValueUnit;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CCorrespondant;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CTraitement;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\SalleOp\CActeCCAM;

/**
 * Interface for object's matcher functions
 */
interface MatcherVisitorInterface
{
    public function matchUser(CUser $user): CUser;

    public function matchPatient(CPatient $patient): CPatient;

    public function matchMedecin(CMedecin $medecin): CMedecin;

    public function matchPlageConsult(CPlageconsult $plage_consult): CPlageconsult;

    public function matchConsultation(CConsultation $consultation): CConsultation;

    public function matchConsultationAnesth(CConsultAnesth $consultation): CConsultAnesth;

    public function matchSejour(CSejour $sejour): CSejour;

    public function matchFile(CFile $file): CFile;

    public function matchAffectation(CAffectation $affectation): CAffectation;

    public function matchAntecedent(CAntecedent $antecedent): CAntecedent;

    public function matchTraitement(CTraitement $trt): CTraitement;

    public function matchCorrespondant(CCorrespondant $correspondant): CCorrespondant;

    public function matchEvenementPatient(CEvenementPatient $evenement_patient): CEvenementPatient;

    public function matchInjection(CInjection $injection): CInjection;

    public function matchActeCCAM(CActeCCAM $acte_ccam): CActeCCAM;

    public function matchActeNGAP(CActeNGAP $acte_ngap): CActeNGAP;

    public function matchConstante(CConstantesMedicales $constantes_medicales): CConstantesMedicales;

    public function matchDossierMedical(CDossierMedical $dossier_medical): CDossierMedical;

    public function matchOperation(COperation $operation): COperation;

    public function matchObservationResult(CObservationResult $observation_result): CObservationResult;

    public function matchObservationIdentifier(CObservationIdentifier $observation_identifier): CObservationIdentifier;

    public function matchObservationResultValue(
        CObservationResultValue $observation_result_value
    ): CObservationResultValue;

    public function matchObservationResultSet(CObservationResultSet $observation_result_set): CObservationResultSet;

    public function matchObservationAbnormalFlag(CObservationAbnormalFlag $observation_flag): CObservationAbnormalFlag;

    public function matchObservationValueUnit(CObservationValueUnit $observation_value_unit): CObservationValueUnit;

    public function matchObservationFile(CFile $file): CFile;

    public function matchObservationResponsible(
        CObservationResponsibleObserver $observation_responsible_observer
    ): CObservationResponsibleObserver;

    public function matchObservationExam(
        CObservationResultExamen $observation_result_examen
    ): CObservationResultExamen;

    public function matchObservationPatient(
        CPatient $patient
    ): CPatient;
}
