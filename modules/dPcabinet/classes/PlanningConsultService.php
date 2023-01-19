<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPlageConge;
use Ox\Mediboard\PlanningOp\COperation;

/**
 * Permet de construire un tableau par date des consultations, plage de conges, de consult... pour l'affichage du
 * planning
 */
class PlanningConsultService extends PlanningConsultSlotService
{
    /** @var array */
    private $contents_by_date;
    /** @var array */
    private $holidays;
    /** @var CMediusers */
    private $chir;
    /** @var bool */
    private $actes;
    /** @var int|null */
    private $line_element_id;

    /**
     * @param int|string $chir_id
     */
    public function __construct(
        string $date_debut,
        string $date_fin,
               $chir_id,
        string $chrono = null,
        string $facture = null,
        string $actes = null,
        string $consult_cancelled = null,
        string $line_element_id = ""
    ) {
        $this->chir              = CMediusers::get($chir_id);
        $this->chrono            = $chrono == "" ? null : $chrono;
        $this->facture           = $facture == "" ? null : $facture;
        $this->actes             = $actes == "" ? null : $actes;
        $this->consult_cancelled = $consult_cancelled == "" ? null : $consult_cancelled;
        $this->line_element_id   = $line_element_id == "" ? null : $line_element_id;

        $this->holidays         = array_merge(CMbDT::getHolidays($date_debut), CMbDT::getHolidays($date_fin));
        $date                   = $date_debut;
        $this->contents_by_date = [];
        if ($date == $date_fin) {
            $this->createArrayContentsByDate($date);
        } else {
            while ($date <= $date_fin) {
                $this->createArrayContentsByDate($date);

                $date = CMbDT::date("+1 day", $date);
            }
        }

        $this->createContentsByDate();
    }

    private function createArrayContentsByDate(string $date): void
    {
        $this->contents_by_date[$date] = [
            "plage_op"          => [],
            "interv_hors_plage" => [],
            "conges"            => [],
            "plage_consult"     => [],
            "consults"          => [],
        ];
    }

    private function createContentsByDate(): void
    {
        foreach ($this->contents_by_date as $_date => $contents) {
            $this->intervContents($_date);
            $this->congesContents($_date);
            $this->plageConsultContents($_date);
        }
    }

    private function intervContents(string $date): void
    {
        $is_holiday = array_key_exists($date, $this->holidays);
        if (CAppUI::pref("showIntervPlanning") && (!$is_holiday || CAppUI::pref("show_plage_holiday"))) {
            $where         = [];
            $where["date"] = "= '$date'";
            $where[]       = "chir_id {$this->chir->getUserSQLClause()} OR spec_id = '{$this->chir->function_id}'";
            /** @var CPlageOp[] $intervs */
            $interv  = new CPlageOp();
            $intervs = $interv->loadList($where);
            CStoredObject::massLoadFwdRef($intervs, "chir_id");
            foreach ($intervs as $_interv) {
                $this->contents_by_date[$date]["plage_op"][] = $_interv;
            }

            //HORS PLAGE
            $where               = [];
            $where["date"]       = "= '$date'";
            $where["plageop_id"] = " IS NULL";
            $where["chir_id"]    = $this->chir->getUserSQLClause();

            $hors_plage = new COperation();
            /** @var COperation[] $horsPlages */
            $hors_plages = $hors_plage->loadList($where);
            foreach ($hors_plages as $_hors_plage) {
                $this->contents_by_date[$date]["interv_hors_plage"][] = $_hors_plage;
            }
        }
    }

    private function congesContents(string $date): void
    {
        if (CModule::getActive("dPpersonnel")) {
            $where            = [];
            $where[]          = "'$date' BETWEEN DATE(date_debut) AND DATE(date_fin)";
            $where["user_id"] = "= '{$this->chir->_id}'";

            $conge = new CPlageConge();
            /** @var CPlageconge[] $conges */
            $conges = $conge->loadList($where);
            foreach ($conges as $_conge) {
                $_conge->loadRefReplacer();

                $this->contents_by_date[$date]["conges"][] = $_conge;
            }
        }
    }

    private function plageConsultContents(string $date): void
    {
        $where         = [];
        $where["date"] = "= '$date'";
        $where[]       = "chir_id {$this->chir->getUserSQLClause()} OR remplacant_id {$this->chir->getUserSQLClause()}";
        $libelles      = CPlageconsult::getLibellesPref();
        if (count($libelles)) {
            $where["libelle"] = CSQLDataSource::prepareIn($libelles);
        }
        if ($this->line_element_id) {
            $where["pour_tiers"] = "= '1'";
        }
        $plage = new CPlageconsult();
        /** @var CPlageConsult[] $plages */
        $plages = $plage->loadList($where, "date, debut");

        $chirs_plages = CStoredObject::massLoadFwdRef($plages, "chir_id");
        CStoredObject::massLoadFwdRef($chirs_plages, "function_id");
        CStoredObject::massLoadFwdRef($plages, "remplacant_id");
        CStoredObject::massLoadFwdRef($plages, "pour_compte_id");
        CStoredObject::massLoadBackRefs($plages, "slots");

        foreach ($plages as $_plage) {
            if ($_plage->libelle != "automatique_suivi_patient") {
                $_plage->loadRefChir();
                $_plage->loadRefRemplacant();
                $_plage->loadRefPourCompte();
                $_plage->loadRefChir()->loadRefFunction();
                $_plage->loadRefAgendaPraticien();
                $_plage->colorPlanning($this->chir->_id);

                $this->slotContents($_plage, $date);

                $this->contents_by_date[$date]["plage_consult"][] = $_plage;
            }
        }
    }

    /**
     * @throws Exception
     */
    protected function consultContentsByDate(string $date, CConsultation $consult): void
    {
        $consult->loadRefsActes();

        if ($this->actes !== null) {
            if ($this->actes && !$consult->_count_actes) {
                return;
            }
            if (!$this->actes && $consult->_count_actes > 0) {
                return;
            }
        }
        $_consult_anesth = $consult->loadRefConsultAnesth();
        if ($_consult_anesth && $_consult_anesth->_id) {
            $consult->_alert_docs        += $_consult_anesth->_alert_docs;
            $consult->_locked_alert_docs += $_consult_anesth->_locked_alert_docs;
        }

        $consult->loadRefFacture();
        $consult->loadPosition();
        $consult->colorPlanning();
        $consult->loadRefPatient();
        if (CModule::getActive('teleconsultation')) {
            $consult->loadRefRoom();
        }
        $consult->loadRefReservedRessources();
        $consult->loadRefConsultAnesth();
        $consult->checkDHE();
        if (CModule::getActive('notifications')) {
            $consult->loadRefNotification();
        }
        $consult->loadRefAdresseParPraticien();
        $consult->loadRefCategorie();
        $consult->canDo();
        $consult->loadRefReunion();

        $plage_resource_id = CStoredObject::massLoadFwdRef(
            $consult->_ref_reserved_ressources,
            "plage_ressource_cab_id"
        );
        CStoredObject::massLoadFwdRef($plage_resource_id, "ressource_cab_id");
        foreach ($consult->_ref_reserved_ressources as $_reserved) {
            $consult = $_reserved->loadRefPlageRessource()->loadRefRessource();
        }

        $this->contents_by_date[$date]["consults"][] = $consult;
    }

    public function getContentsByDate(): array
    {
        return $this->contents_by_date;
    }

    protected function consultContents(CConsultation $consult, int $plage_id): void
    {
    }
}
