<?php

/**
 * @package Mediboard\Admin\Tests
 * @author  SARL OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Mediboard\Board\Tests\Fixtures;

use Ox\Core\CMbDT;
use Ox\Core\CModelObjectException;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CExamComp;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CStatutCompteRendu;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CObservationMedicale;
use Ox\Mediboard\Hospi\CTransmissionMedicale;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Personnel\CPlageConge;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\SalleOp\CActeCCAM;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;

class TdbFixtures extends Fixtures
{
    public const REF_TDB_CHIR      = "TDB_Chir";
    public const REF_PATIENT       = "TDB_Patient";
    public const REF_TDB_OP        = "TDB_Operation";
    public const REF_TDB_HP        = "TDB_Operation_HP";
    public const REF_TDB_USER      = "TDB_User";
    public const REF_TDB_FUNCTION  = "TDB_Function";
    public const REF_PLAGE_CONSULT = "TDB_PLAGECONSULT";
    public const REF_TDB_CONSULT   = "TDB_Consult";
    public const REF_TDB_SEJOUR    = "TDB_Sejour";
    public const REF_TDB_SEANCE    = "TDB_Seance";

    private const STATUS_ATTENTE_VALIDATION = "attente_validation_praticien";
    private const STATUS_BROUILLON          = "brouillon";
    private const STATUS_ENVOYE             = "envoye";
    private const STATUS_A_ENVOYER          = "a_envoyer";

    private CSejour $sejour;

    private CGroups $group;

    private CConsultation $consult;

    private CConsultAnesth $consult_anesth;

    private COperation $operation;

    private CPatient $patient;

    private const LIST_STATUS_CONTEXT_DOCUMENT_CHIR = [
        [
            "status"  => self::STATUS_ATTENTE_VALIDATION,
            "context" => CConsultation::class,
        ],
        [
            "status"  => self::STATUS_BROUILLON,
            "context" => CSejour::class,
        ],
        [
            "status"  => self::STATUS_ENVOYE,
            "context" => CConsultAnesth::class,
        ],
        [
            "status"  => self::STATUS_A_ENVOYER,
            "context" => COperation::class,
        ],
        [
            "status"  => self::STATUS_A_ENVOYER,
            "context" => CPatient::class,
        ],
    ];

    /**
     * @inheritDoc
     * @throws FixturesException
     * @throws CModelObjectException
     */
    public function load()
    {
        $chir = $this->createTdbChir();
        $this->createContextElements($chir);
        foreach (self::LIST_STATUS_CONTEXT_DOCUMENT_CHIR as $status) {
            $this->createDocumentForChirWithStatus($chir, $status);
        }

        $user = $this->getUser(false);
        $this->store($user, self::REF_TDB_USER);
    }

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    private function createTdbChir(): CMediusers
    {
        $group = CGroups::getSampleObject(CGroups::class);
        $this->store($group);

        $this->group = $group;

        /** @var CFunctions $function */
        $function           = CStoredObject::getSampleObject(CFunctions::class);
        $function->group_id = $group->_id;

        $this->store($function, self::REF_TDB_FUNCTION);

        $praticien = $this->getUser(false);

        $praticien->_user_type  = 3;
        $praticien->actif       = 1;
        $praticien->function_id = $function->_id;

        $this->store($praticien, self::REF_TDB_CHIR);

        // On crée un praticien ayant la même fonction
        $praticien_same_fnc              = $this->getUser(false);
        $praticien_same_fnc->_user_type  = 3;
        $praticien_same_fnc->actif       = 1;
        $praticien_same_fnc->function_id = $function->_id;

        $this->store($praticien_same_fnc);

        return $praticien;
    }

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     */
    private function createDocumentForChirWithStatus(CMediusers $user, array $status): void
    {
        /** @var CConsultation|CSejour|CConsultAnesth|COperation $context */
        switch ($status["context"]) {
            case CConsultation::class:
                $context = $this->consult;
                break;
            case CConsultAnesth::class:
                $context = $this->consult_anesth;
                break;
            case COperation::class:
                $context = $this->operation;
                break;
            case CSejour::class:
                $context = $this->sejour;
                break;
            default:
                $context = $this->patient;
                break;
        }

        /** @var CCompteRendu $cr */
        $cr                      = CStoredObject::getSampleObject(CCompteRendu::class);
        $cr->author_id           = $user->_id;
        $cr->signataire_id       = $user->_id;
        $cr->object_class        = $context->_class;
        $cr->object_id           = $context->_id;
        $cr->page_height         = 1;
        $cr->page_width          = 1;
        $cr->signature_mandatory = 1;
        $this->store($cr);

        if ($status["context"] !== CPatient::class) {
            /** @var CStatutCompteRendu $status_cr */
            $status_cr                  = CStoredObject::getSampleObject(CStatutCompteRendu::class);
            $status_cr->statut          = $status["status"];
            $status_cr->user_id         = $user->_id;
            $status_cr->compte_rendu_id = $cr->_id;
            $status_cr->datetime        = CMbDT::dateTime("+1 min");
            $this->store($status_cr);
        }
    }

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     */
    private function createContextElements(CMediusers $user): void
    {
        /** @var $patient CPatient */
        $patient = CStoredObject::getSampleObject(CPatient::class);
        $this->store($patient, self::REF_PATIENT);
        $this->patient = $patient;

        /** @var $sejour CSejour */
        $sejour                = CStoredObject::getSampleObject(CSejour::class);
        $sejour->type          = "comp";
        $sejour->patient_id    = $patient->_id;
        $sejour->annule        = 0;
        $sejour->praticien_id  = $user->_id;
        $sejour->codes_ccam    = "ABCA002";
        $sejour->entree        = CMbDT::dateTime("00:00:00");
        $sejour->entree_prevue = CMbDT::dateTime("00:00:00");
        $sejour->sortie        = CMbDT::dateTime("+8 days 23:59:59");
        $sejour->sortie_prevue = CMbDT::dateTime("+8 days 23:59:59");
        $sejour->group_id      = CGroups::getCurrent()->_id;
        $this->store($sejour, self::REF_TDB_SEJOUR);
        $this->sejour = $sejour;

        $seance = new CSejour();
        $seance->cloneFrom($sejour);
        $seance->type          = "seances";
        $seance->sortie_prevue = $sejour->entree;
        $seance->sortie        = $sejour->entree;
        $this->store($seance, self::REF_TDB_SEANCE);

        /** @var CActeCCAM $acte_ccam_sejour */
        $acte_ccam_sejour                = CStoredObject::getSampleObject(CActeCCAM::class);
        $acte_ccam_sejour->object_class  = $sejour->_class;
        $acte_ccam_sejour->object_id     = $sejour->_id;
        $acte_ccam_sejour->executant_id  = $user->_id;
        $acte_ccam_sejour->execution     = CMbDT::dateTime("next monday 12:00:00");
        $acte_ccam_sejour->code_acte     = $sejour->codes_ccam;
        $acte_ccam_sejour->code_activite = "1";
        $acte_ccam_sejour->code_phase    = "0";
        $acte_ccam_sejour->modificateurs = null;
        $this->store($acte_ccam_sejour);

        $obs_med            = new CObservationMedicale();
        $obs_med->sejour_id = $sejour->_id;
        $obs_med->user_id   = $user->_id;
        $obs_med->degre     = "low";
        $obs_med->date      = CMbDT::dateTime();
        $this->store($obs_med);

        $trans_med            = new CTransmissionMedicale();
        $trans_med->sejour_id = $sejour->_id;
        $trans_med->user_id   = $user->_id;
        $trans_med->date      = CMbDT::dateTime();
        $this->store($trans_med);

        /** @var CPlageconsult $plage */
        $plage             = CStoredObject::getSampleObject(CPlageconsult::class);
        $plage->chir_id    = $user->_id;
        $plage->date       = CMbDT::date();
        $plage->debut      = CMbDT::time("10:00:00");
        $plage->fin        = CMbDT::time("10:30:00");
        $plage->freq       = CMbDT::time("00:10:00");
        $plage->libelle    = "Lorem Ipsum";
        $plage->locked     = 0;
        $plage->pour_tiers = 1;
        $this->store($plage, self::REF_PLAGE_CONSULT);

        /** @var $consult CConsultation */
        $consult                  = CStoredObject::getSampleObject(CConsultation::class);
        $consult->plageconsult_id = $plage->_id;
        $consult->owner_id        = $user->_id;
        $consult->sejour_id       = $sejour->_id;
        $consult->patient_id      = $patient->_id;
        $consult->heure           = CMbDT::time("10:00:00");
        $consult->creation_date   = CMbDT::dateTime("-1 DAY");
        $consult->annule          = 0;
        $consult->codes_ccam      = "ABCA002";
        $this->store($consult, self::REF_TDB_CONSULT);
        $this->consult = $consult;

        /** @var $consult_anesth CConsultAnesth */
        $consult_anesth                  = CStoredObject::getSampleObject(CConsultAnesth::class);
        $consult_anesth->consultation_id = $consult->_id;
        $consult_anesth->sejour_id       = $sejour->_id;
        $this->store($consult_anesth);

        $this->consult_anesth = $consult_anesth;

        $exam_comp                  = new CExamComp();
        $exam_comp->realisation     = "avant";
        $exam_comp->consultation_id = $this->consult->_id;
        $exam_comp->date_bilan      = $plage->date;
        $this->store($exam_comp);

        /** @var COperation $operationhp */
        $operationhp             = CStoredObject::getSampleObject(COperation::class);
        $operationhp->sejour_id  = $sejour->_id;
        $operationhp->chir_id    = $user->_id;
        $operationhp->codes_ccam = "ABCA002";
        $operationhp->date       = CMbDT::date();

        $this->store($operationhp, self::REF_TDB_HP);

        /** @var CBlocOperatoire $bloc */
        $bloc           = CStoredObject::getSampleObject(CBlocOperatoire::class);
        $bloc->group_id = $this->group->_id;
        $bloc->nom      = "Bloc Lorem";
        $this->store($bloc);

        /** @var CSalle $salle */
        $salle          = CStoredObject::getSampleObject(CSalle::class);
        $salle->bloc_id = $bloc->_id;
        $salle->nom     = "Salle Lorem";
        $salle->stats   = 0;
        $this->store($salle);

        /** @var CPlageOp $plage_op */
        $plage_op                  = CStoredObject::getSampleObject(CPlageOp::class);
        $plage_op->date            = CMbDT::date("+1 DAY");
        $plage_op->debut           = "08:00:00";
        $plage_op->debut_reference = "08:00:00";
        $plage_op->fin             = "16:00:00";
        $plage_op->fin_reference   = "16:00:00";
        $plage_op->salle_id        = $salle->_id;
        $plage_op->chir_id         = $user->_id;

        $this->store($plage_op);

        /** @var COperation $operation */
        $operation             = CStoredObject::getSampleObject(COperation::class);
        $operation->sejour_id  = $sejour->_id;
        $operation->chir_id    = $user->_id;
        $operation->date       = $plage_op->date;
        $operation->codes_ccam = "ABCA002";
        $operation->plageop_id = $plage_op->_id;

        $this->store($operation, self::REF_TDB_OP);

        /** @var CActeCCAM $acte_ccam_operation */
        $acte_ccam_operation                = CStoredObject::getSampleObject(CActeCCAM::class);
        $acte_ccam_operation->object_class  = $operation->_class;
        $acte_ccam_operation->object_id     = $operation->_id;
        $acte_ccam_operation->executant_id  = $user->_id;
        $acte_ccam_operation->execution     = CMbDT::dateTime("12:00:00", CMbDT::getNextWorkingDay($operation->date));
        $acte_ccam_operation->code_acte     = $operation->codes_ccam;
        $acte_ccam_operation->code_activite = "1";
        $acte_ccam_operation->code_phase    = "0";
        $this->store($acte_ccam_operation);

        $this->operation = $operation;

        /** @var CPlageConge $plage_conge */
        $plage_conge             = CStoredObject::getSampleObject(CPlageConge::class);
        $plage_conge->user_id    = $user->_id;
        $plage_conge->libelle    = "Conge Lorem";
        $plage_conge->date_debut = CMbDT::dateTime("08:00:00");
        $plage_conge->date_fin   = CMbDT::dateTime("16:00:00");

        $this->store($plage_conge);
    }
}
