<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Tests\Fixtures;

use Ox\Core\CMbDT;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Personnel\CPlageConge;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\Fixtures\Fixtures;

class PlanningConsultServiceFixtures extends Fixtures
{
    public const PLANNING_CONSULT_SERVICE_INTERV_HORS_PLAGE          = "planning_consult_service_interv_hors_plage";
    public const PLANNING_CONSULT_SERVICE_PLAGE_OPERATOIRE           = "planning_consult_service_plage_operatoire";
    public const PLANNING_CONSULT_SERVICE_PLAGE_CONGES               = "planning_consult_service_plage_conges";
    public const PLANNING_CONSULT_SERVICE_PLAGE_CONSULT              = "planning_consult_service_plage_consult";
    public const PLANNING_CONSULT_SERVICE_PLAGE_CONSULT_WITHOUT_SLOT = "planning_consult_service_plage_consult_WITHOUT_SLOT";
    public const PLANNING_CONSULT_SERVICE_CONSULT                    = "planning_consult_service_consult";
    public const PLANNING_CONSULT_SERVICE_CONSULT_ACTE               = "planning_consult_service_consult_acte";

    /**
     * @inheritDoc
     */
    public function load()
    {
        $chir = $this->getUser(false);

        $this->createPlageOperatoire($chir);
        $this->createIntervHorsPlage($chir);
        $this->createPlageConges($chir);
        $plage_consult_id = $this->createPlageConsult($chir);
        $this->createConsults($chir, $plage_consult_id);
    }

    public function createPlageOperatoire(CMediusers $chir): void
    {
        $bloc           = new CBlocOperatoire();
        $bloc->group_id = CGroups::loadCurrent()->_id;
        $bloc->nom      = "planning consult service";
        $this->store($bloc);

        $salle          = new CSalle();
        $salle->bloc_id = $bloc->_id;
        $salle->nom     = "planning consult service";
        $salle->stats   = 0;
        $this->store($salle);

        $plage_op                  = new CPlageOp();
        $plage_op->salle_id        = $salle->_id;
        $plage_op->chir_id         = $chir->_id;
        $plage_op->date            = CMbDT::date();
        $plage_op->debut           = "08:00:00";
        $plage_op->fin             = "18:00:00";
        $plage_op->debut_reference = "08:00:00";
        $plage_op->fin_reference   = "18:00:00";
        $this->store($plage_op, self::PLANNING_CONSULT_SERVICE_PLAGE_OPERATOIRE);
    }

    public function createIntervHorsPlage(CMediusers $chir): void
    {
        $patient                  = new CPatient();
        $patient->prenom          = "planning_consult";
        $patient->nom_jeune_fille = "fixtures";
        $patient->naissance       = "2000-05-23";
        $this->store($patient);

        $sejour                = new CSejour();
        $sejour->patient_id    = $patient->_id;
        $sejour->praticien_id  = $this->getUser(false)->_id;
        $sejour->group_id      = CGroups::loadCurrent()->_id;
        $sejour->entree_prevue = CMbDT::dateTime();
        $sejour->sortie_prevue = CMbDT::dateTime("+2 DAYS");
        $this->store($sejour);

        $interv            = new COperation();
        $interv->sejour_id = $sejour->_id;
        $interv->chir_id   = $chir->_id;
        $interv->date      = CMbDT::date();
        $this->store($interv, self::PLANNING_CONSULT_SERVICE_INTERV_HORS_PLAGE);
    }

    public function createPlageConges(CMediusers $chir): void
    {
        $plage_conges             = new CPlageConge();
        $plage_conges->user_id    = $chir->_id;
        $plage_conges->date_debut = CMbDT::dateTime("+1 DAYS");
        $plage_conges->date_fin   = CMbDT::dateTime("+2 DAYS");
        $plage_conges->libelle    = "planning consult service";
        $this->store($plage_conges, self::PLANNING_CONSULT_SERVICE_PLAGE_CONGES);
    }

    public function createPlageConsult(CMediusers $chir): int
    {
        $plage_consult_without_slot          = new CPlageconsult();
        $plage_consult_without_slot->chir_id = $this->getUser(false)->_id;
        $plage_consult_without_slot->date    = CMbDT::date("2020-12-31");
        $plage_consult_without_slot->freq    = "00:15:00";
        $plage_consult_without_slot->debut   = "09:00:00";
        $plage_consult_without_slot->fin     = "12:00:00";
        $plage_consult_without_slot->libelle = "planning consult service";
        $this->store($plage_consult_without_slot, self::PLANNING_CONSULT_SERVICE_PLAGE_CONSULT_WITHOUT_SLOT);

        $plage_consult          = new CPlageconsult();
        $plage_consult->chir_id = $chir->_id;
        $plage_consult->date    = CMbDT::date();
        $plage_consult->freq    = "00:15:00";
        $plage_consult->debut   = "09:00:00";
        $plage_consult->fin     = "12:00:00";
        $plage_consult->libelle = "planning consult service";
        $this->store($plage_consult, self::PLANNING_CONSULT_SERVICE_PLAGE_CONSULT);

        return $plage_consult->_id;
    }

    public function createConsults(CMediusers $chir, int $plage_consult_id): void
    {
        $consult                  = new CConsultation();
        $consult->plageconsult_id = $plage_consult_id;
        $consult->heure           = "10:00:00";
        $consult->chrono          = "32";
        $consult->owner_id        = $chir->_id;
        $consult->facture         = 1;
        $this->store($consult, self::PLANNING_CONSULT_SERVICE_CONSULT);

        $consult                  = new CConsultation();
        $consult->plageconsult_id = $plage_consult_id;
        $consult->heure           = "10:00:00";
        $consult->chrono          = "32";
        $consult->owner_id        = $chir->_id;
        $consult->facture         = 0;
        $this->store($consult, self::PLANNING_CONSULT_SERVICE_CONSULT_ACTE);

        $acte               = new CActeNGAP();
        $acte->object_id    = $consult->_id;
        $acte->object_class = "CConsultation";
        $acte->code         = "AMK";
        $acte->quantite     = "1";
        $acte->coefficient  = "0.1";
        $acte->executant_id = $chir->_id;
        $acte->execution    = CMbDT::dateTime();
        $this->store($acte);
    }
}
