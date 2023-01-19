<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Matcher;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Import\Framework\Configuration\ConfigurableInterface;
use Ox\Import\Framework\Configuration\ConfigurationTrait;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\Cabinet\Vaccination\CVaccination;
use Ox\Mediboard\Ccam\CActe;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Mediusers\CMediusers;
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
 * Matcher for import objects which will found if an object already exist in database
 */
class DefaultMatcher implements MatcherVisitorInterface, ConfigurableInterface
{
    use ConfigurationTrait;

    /**
     * @inheritDoc
     */
    public function matchUser(CUser $user): CUser
    {
        $safe_user                = new CUser();
        $safe_user->user_username = $user->user_username;
        $safe_user->loadMatchingObjectEsc();

        if ($safe_user && $safe_user->_id) {
            $user = $safe_user;
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function matchPatient(CPatient $patient): CPatient
    {
        // TODO Améliorer le matching de patient en prennant en compte les possibles espaces multiples
        // Todo: Tester aussi les cas avec prénoms composés et diacritiques
        $patient->loadMatchingPatient();

        return $patient;
    }

    /**
     * @inheritDoc
     */
    public function matchMedecin(CMedecin $medecin): CMedecin
    {
        // Gestion du cloisonnement utilisateur
        $current_user = CMediusers::get();
        $function_id  = null;
        $group_id     = null;

        if (CAppUI::isCabinet()) {
            $function_id = $current_user->function_id;
        } elseif (CAppUI::isGroup()) {
            $group_id = CGroups::loadCurrent()->_id;
        }

        if ($medecin->rpps) {
            $medecin->loadFromRPPS($medecin->rpps, $function_id, $group_id);
        }

        if (!$medecin->_id && $medecin->adeli) {
            $medecin->loadByAdeli($medecin->adeli, $function_id);
        }

        if (!$medecin->_id) {
            $ds = $medecin->getDS();

            // Todo: Care! Is property is NULL, then $ds->prepare performs  = '' which probably does not exist in DB
            $where = [
                'nom'         => $ds->prepare('= ?', $medecin->nom),
                'prenom'      => ($medecin->prenom) ? $ds->prepare('= ?', $medecin->prenom) : 'IS NULL',
                'cp'          => ($medecin->cp) ? $ds->prepare('= ?', $medecin->cp) : 'IS NULL',
                'function_id' => ($function_id) ? $ds->prepare('= ?', $function_id) : 'IS NULL',
                'group_id'    => ($group_id) ? $ds->prepare('= ?', $group_id) : 'IS NULL',
            ];

            if (!$medecin->loadObject($where) && $medecin->cp) {
                $short_cp = substr($medecin->cp, 0, 2);

                $where['cp'] = $ds->prepareLike("{$short_cp}___");

                $medecin->loadObject($where);
            }
        }

        return $medecin;
    }

    /**
     * @inheritDoc
     */
    public function matchPlageConsult(CPlageconsult $plage_consult): CPlageconsult
    {
        $ds = $plage_consult->getDS();

        $where = [
            'chir_id' => $ds->prepare('= ?', $plage_consult->chir_id),
            'date'    => $ds->prepare('= ?', $plage_consult->date),
        ];

        $plage_consult->loadObject($where);

        return $plage_consult;
    }

    /**
     * @inheritDoc
     */
    public function matchConsultation(CConsultation $consultation): CConsultation
    {
        $ds = $consultation->getDS();

        $where = [
            'patient_id'      => $ds->prepare('= ?', $consultation->patient_id),
            'plageconsult_id' => $ds->prepare('= ?', $consultation->plageconsult_id),
        ];

        $consultation->loadObject($where);

        return $consultation;
    }

    /**
     * @inheritDoc
     */
    public function matchConsultationAnesth(CConsultAnesth $consultation): CConsultAnesth
    {
        // TODO: Implement matchConsultationAnesth() method.
        return $consultation;
    }

    /**
     * @inheritDoc
     */
    public function matchSejour(CSejour $sejour): CSejour
    {
        $ds = $sejour->getDS();

        $entree = $sejour->entree_reelle ?: $sejour->entree_prevue;

        $where = [
            'patient_id' => $ds->prepare('= ?', $sejour->patient_id),
            'group_id'   => $ds->prepare('= ?', $sejour->group_id),
            'entree'     => $ds->prepare(
                'BETWEEN ?1 AND ?2',
                CMbDT::dateTime('-1 DAY', $entree),
                CMbDT::dateTime('+1 DAY', $entree)
            ),
        ];

        $sejour->loadObject($where);

        return $sejour;
    }

    /**
     * @inheritDoc
     */
    public function matchFile(CFile $file): CFile
    {
        if ($this->configuration['find_existing_files']) {
            $ds = $file->getDS();

            $date = CMbDT::date($file->file_date);

            $where = [
                'object_class' => $ds->prepare('= ?', $file->object_class),
                'object_id'    => $ds->prepare('= ?', $file->object_id),
                'file_name'    => $ds->prepare('= ?', $file->file_name),
                'file_date'    => $ds->prepareLike("$date%"),
            ];

            $file->loadObject($where);
        }

        return $file;
    }

    public function matchAntecedent(CAntecedent $antecedent): CAntecedent
    {
        $ds = $antecedent->getDS();

        $where = [
            'dossier_medical_id' => $ds->prepare('= ?', $antecedent->dossier_medical_id),
            'rques'              => $ds->prepare('= ?', $antecedent->rques),
            'date'               => $ds->prepare('= ?', $antecedent->date),
        ];

        if ($antecedent->type) {
            $where['type'] = $ds->prepare('= ?', $antecedent->type);
        }

        $antecedent->loadObject($where);

        return $antecedent;
    }

    public function matchTraitement(CTraitement $trt): CTraitement
    {
        $ds = $trt->getDS();

        $where = [
            'dossier_medical_id' => $ds->prepare('= ?', $trt->dossier_medical_id),
            'traitement'         => $ds->prepare('= ?', $trt->traitement),
        ];

        $trt->loadObject($where);

        return $trt;
    }

    public function matchCorrespondant(CCorrespondant $correspondant): CCorrespondant
    {
        $ds = $correspondant->getDS();

        $where = [
            'patient_id' => $ds->prepare('= ?', $correspondant->patient_id),
            'medecin_id' => $ds->prepare('= ?', $correspondant->medecin_id),
        ];

        $correspondant->loadObject($where);

        return $correspondant;
    }

    public function matchEvenementPatient(CEvenementPatient $evenement_patient): CEvenementPatient
    {
        $ds = $evenement_patient->getDS();

        $where = [
            'dossier_medical_id' => $ds->prepare('= ?', $evenement_patient->dossier_medical_id),
            'praticien_id'       => $ds->prepare('= ?', $evenement_patient->praticien_id),
            'date'               => $ds->prepare('= ?', $evenement_patient->date),
            'type'               => $ds->prepare('= ?', $evenement_patient->type),
        ];

        $evenement_patient->loadObject($where);

        return $evenement_patient;
    }

    public function matchInjection(CInjection $injection): CInjection
    {
        $ds = $injection->getDS();

        $where = [
            'patient_id'     => $ds->prepare('= ?', $injection->patient_id),
            'injection_date' => $ds->prepare('= ?', $injection->injection_date),
            'speciality'     => $ds->prepareLike("%$injection->speciality%"),
        ];

        $injection->loadObject($where);

        return $injection;
    }

    public function matchActeCCAM(CActeCCAM $acte_ccam): CActeCCAM
    {
        $ds = $acte_ccam->getDS();

        $where              = $this->getWhereActe($acte_ccam);
        $where['code_acte'] = $ds->prepare('= ?', $acte_ccam->code_acte);

        $acte_ccam->loadObject($where);

        return $acte_ccam;
    }

    public function matchActeNGAP(CActeNGAP $acte_ngap): CActeNGAP
    {
        $ds = $acte_ngap->getDS();

        $where         = $this->getWhereActe($acte_ngap);
        $where['code'] = $ds->prepare('= ?', $acte_ngap->code);

        $acte_ngap->loadObject($where);

        return $acte_ngap;
    }

    public function matchConstante(CConstantesMedicales $constantes_medicales): CConstantesMedicales
    {
        $ds = $constantes_medicales->getDS();

        $where = [
            'patient_id' => $ds->prepare('= ?', $constantes_medicales->patient_id),
            'datetime'   => $ds->prepare('= ?', $constantes_medicales->datetime),
        ];

        $constantes_medicales->loadObject($where);

        return $constantes_medicales;
    }

    public function matchDossierMedical(CDossierMedical $dossier_medical): CDossierMedical
    {
        $ds = $dossier_medical->getDS();

        $where = [
            'object_class' => '= "CPatient"',
            'object_id'    => $ds->prepare('= ?', $dossier_medical->object_id),
        ];

        $dossier_medical->loadObject($where);

        return $dossier_medical;
    }

    public function matchAffectation(CAffectation $affectation): CAffectation
    {
        $ds = $affectation->getDS();

        $where = [
            'sejour_id' => $ds->prepare('= ?', $affectation->sejour_id),
            'entree'    => $ds->prepare('= ?', $affectation->entree),
            'sortie'    => $ds->prepare('= ?', $affectation->sortie),
        ];

        $affectation->loadObject($where);

        return $affectation;
    }

    public function matchOperation(COperation $operation): COperation
    {
        $ds = $operation->getDS();

        $where = [
            'sejour_id'      => $ds->prepare('= ?', $operation->sejour_id),
            'chir_id'        => $ds->prepare('= ?', $operation->chir_id),
            'date'           => $ds->prepare('= ?', $operation->date),
            'time_operation' => $ds->prepare('= ?', $operation->time_operation),
        ];

        $operation->loadObject($where);

        return $operation;
    }

    /**
     * @inheritDoc
     */
    public function matchObservationResult(CObservationResult $observation_result): CObservationResult
    {
        // Define in OxLaboTransformer
    }

    /**
     * @inheritDoc
     */
    public function matchObservationIdentifier(CObservationIdentifier $observation_identifier): CObservationIdentifier
    {
        // Define in OxLaboTransformer
    }

    /**
     * @inheritDoc
     */
    public function matchObservationResultValue(
        CObservationResultValue $observation_result_value
    ): CObservationResultValue {
        // Define in OxLaboMatcher
    }

    /**
     * @inheritDoc
     */
    public function matchObservationResultSet(
        CObservationResultSet $observation_result_set
    ): CObservationResultSet {
        // Define in OxLaboMatcher
    }

    public function matchObservationAbnormalFlag(CObservationAbnormalFlag $observation_flag): CObservationAbnormalFlag
    {
        // Define in OxLaboMatcher
    }

    public function matchObservationValueUnit(CObservationValueUnit $observation_value_unit): CObservationValueUnit
    {
        // Define in OxLaboMatcher
    }

    public function matchObservationFile(CFile $file): CFile
    {
        // Define in OxLaboMatcher
    }

    public function matchObservationResponsible(
        CObservationResponsibleObserver $observation_responsible_observer
    ): CObservationResponsibleObserver {
        // Define in OxLaboMatcher
    }

    public function matchObservationExam(
        CObservationResultExamen $observation_result_examen
    ): CObservationResultExamen {
        // Define in OxLaboMatcher
    }

    public function matchObservationPatient(
        CPatient $patient
    ): CPatient {
        // Define in OxLaboMatcher
    }

    private function getWhereActe(CActe $acte): array
    {
        $ds = $acte->getDS();

        return [
            'object_class' => $ds->prepare('= ?', $acte->object_class),
            'object_id'    => $ds->prepare('= ?', $acte->object_id),
            'executant_id' => $ds->prepare('= ?', $acte->executant_id),
        ];
    }
}
