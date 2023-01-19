<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Board;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CPlageHoraire;
use Ox\Core\CSQLDataSource;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPlageConge;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\System\CPlanningEvent;
use Ox\Mediboard\System\CPlanningWeek;
use Ox\Mediboard\System\CSourcePOP;
use Ox\Mediboard\Tel3333\C3333TelTools;

class TdbCalendarView
{
    private array $list_functions = [];

    private array $list_praticiens = [];

    private CSQLDataSource $ds;

    private const DEFAULT_NB_DAYS  = 7;
    private const DEFAULT_HOUR_MIN = "07";
    private const DEFAULT_HOUR_MAX = "20";
    private const DEFAULT_PAUSES   = ["07", "12", "19"];

    public function __construct()
    {
        $this->ds = CSQLDataSource::get("std");
    }

    private static function prepareLibelle(string $libelle, string $tr): string
    {
        return '<h3 style="text-align: center">
      ' . CAppUI::tr($tr) . '</h3>
      <p style="text-align: center">' . $libelle . '</p>';
    }


    /**
     * @param CMediusers $praticien
     *
     * @return CSourcePOP|null
     * @throws Exception
     */
    public function loadPraticienAlternativeAccount(CMediusers $praticien): ?CSourcePOP
    {
        $account               = new CSourcePOP();
        $account->object_class = "CMediusers";
        $account->object_id    = $praticien->_id;
        $account->loadMatchingObject();

        return $account->_id ? $account : null;
    }

    /**
     * @param CMediusers $praticien
     * @param CMediusers $user
     * @param string     $perm_fonct
     * @param bool       $use_group
     *
     * @return void
     */
    public function prepareMonthView(
        CMediusers $praticien,
        CMediusers $user,
        string $perm_fonct = "",
        bool $use_group = true
    ): void {
        if ($perm_fonct == "same_function") {
            $this->list_functions[$user->function_id] = $user->loadRefFunction();
        } elseif ($perm_fonct == "write_right") {
            $this->list_functions = CMediusers::loadFonctions(PERM_EDIT);
        } elseif ($perm_fonct != 'only_me') {
            $this->list_functions = CMediusers::loadFonctions();
        }

        if ($perm_fonct == 'only_me') {
            $this->list_praticiens[$user->_id] = $user;
        } elseif ($perm_fonct == "same_function") {
            $this->list_praticiens = $praticien->loadProfessionnelDeSante(
                PERM_READ,
                $user->function_id,
                null,
                false,
                true,
                $use_group
            );
        } elseif ($perm_fonct == "write_right") {
            $this->list_praticiens = $praticien->loadProfessionnelDeSante(
                PERM_EDIT,
                null,
                null,
                false,
                true,
                $use_group
            );
        } else {
            $this->list_praticiens = $praticien->loadProfessionnelDeSante(
                PERM_READ,
                null,
                null,
                false,
                true,
                $use_group
            );
        }

        usort($this->list_praticiens, function ($a, $b) {
            return strcmp($a->_user_last_name, $b->_user_last_name);
        });
    }

    /**
     * @return array
     */
    public function getListPraticiens(): array
    {
        return $this->list_praticiens;
    }

    /**
     * @return array
     */
    public function getListFunctions(): array
    {
        return $this->list_functions;
    }

    /**
     * @throws Exception
     */
    public function createPlanningWeek(
        string $date,
        string $debut,
        string $fin,
        CMediusers $praticien,
        CMediusers $mediuser
    ): CPlanningWeek {
        $nbjours = $this->computeNbJours($praticien, $fin);

        $planning = new CPlanningWeek($date, $debut, $fin, $nbjours, false, null, null, true);

        $this->loadPlanningElements($planning, $praticien, $mediuser);

        return $planning;
    }

    /**
     * @throws Exception
     */
    public function loadPlanningElements(CPlanningWeek $planning, CMediusers $praticien, CMediusers $mediuser): void
    {
        $planning->title    = $praticien->_view;
        $planning->guid     = $mediuser->_guid;
        $planning->hour_min = self::DEFAULT_HOUR_MIN;
        $planning->hour_max = self::DEFAULT_HOUR_MAX;
        $planning->pauses   = self::DEFAULT_PAUSES;

        $this->loadChirPlageConsult($planning, $praticien);
        $this->loadChirPlageOp($planning, $praticien);
        $this->loadChirPlageConges($planning, $praticien);
        $this->loadChirHorsPlages($planning, $praticien);
    }

    /**
     * @throws Exception
     */
    public function computeNbJours(CMediusers $praticien, string $fin): int
    {
        $nbjours          = self::DEFAULT_NB_DAYS;
        $listPlageConsult = new CPlageconsult();
        $listPlageOp      = new CPlageOp();
        $whereChir        = $praticien->getUserSQLClause();

        $where = [
            "date"    => $this->ds->prepare("= ?", $fin),
            "chir_id" => $whereChir,
        ];

        $operation = new COperation();

        if (
            !$listPlageConsult->countList($where) &&
            !$listPlageOp->countList($where) &&
            !$operation->countList($where)
        ) {
            $nbjours--;
            // Aucune plage le dimanche, on peut donc tester le samedi.
            $dateArr         = CMbDT::date("-1 day", $fin);
            $where["date"]   = $this->ds->prepare("= ?", $dateArr);
            $operation->date = $dateArr;
            if (
                !$listPlageConsult->countList($where) &&
                !$listPlageOp->countList($where) &&
                !$operation->countMatchingList()
            ) {
                $nbjours--;
            }
        }

        return $nbjours;
    }

    private function addEventToPlanning(
        CPlanningWeek &$planning,
        CPlageHoraire $_plage,
        string $date,
        string $libelle = "",
        string $color = "#BCE",
        string $type = "operation",
        string $class = null
    ): void {
        $debute           = "$date $_plage->debut";
        $event            = new CPlanningEvent(
            $_plage->_guid,
            $debute,
            CMbDT::minutesRelative($_plage->debut, $_plage->fin),
            $libelle,
            $color,
            true,
            $class,
            null
        );
        $event->resizable = true;

        //Paramètres de la plage de consultation
        $event->type = $type;
        /** @var $_plage CPlageconsult|CPlageOp */
        $pct = $_plage->_fill_rate;
        if ($pct > "100") {
            $pct = "100";
        }
        if ($pct == "") {
            $pct = 0;
        }

        $event->plage["id"]  = $_plage->_id;
        $event->plage["pct"] = $pct;
        if ($type == "consultation") {
            $event->plage["locked"]       = $_plage->locked;
            $event->plage["_affected"]    = $_plage->_affected;
            $event->plage["_nb_patients"] = $_plage->_nb_patients;
            $event->plage["_total"]       = $_plage->_total;
        } else {
            /** @var $_plage CPlageOp */
            $event->plage["locked"]            = 0;
            $event->plage["_count_operations"] = $_plage->_count_operations;
        }
        $event->plage["list_class"] = $_plage->_guid;
        $event->plage["add_class"]  = $date;
        $event->plage["list_title"] = $date;
        $event->plage["add_title"]  = $type;

        //Ajout de l'évènement au planning
        $planning->addEvent($event);
    }

    /**
     * @throws Exception
     */
    private function loadChirPlageConsult(CPlanningWeek $planning, CMediusers $praticien): void
    {
        $last_day      = CMbDT::date("+7 day", $planning->date_min);
        $plageConsult  = new CPlageconsult();
        $plagesConsult = $plageConsult->loadForDays($praticien->_id, $planning->date_min, $last_day);
        $color         = CAppUI::isMediboardExtDark() ? "#61a53c" : "#BFB";
        foreach ($plagesConsult as $plage_consult) {
            $plage_consult->loadFillRate();
            $plage_consult->countPatients();
            $plage_consult->loadRefChir();
            $class = null;
            if ($plage_consult->pour_tiers) {
                $class = "pour_tiers";
            }
            if (CModule::getActive("3333tel")) {
                C3333TelTools::checkPlagesConsult($plage_consult, $plage_consult->_ref_chir->function_id);
            }
            $this->addEventToPlanning(
                $planning,
                $plage_consult,
                $plage_consult->date,
                $plage_consult->libelle,
                $color,
                "consultation",
                $class
            );
        }
    }

    private function loadChirPlageOp(CPlanningWeek $planning, CMediusers $praticien): void
    {
        $plageOp  = new CPlageOp();
        $plagesOp = $plageOp->loadForDays(
            $praticien->_id,
            $planning->date_min,
            CMbDT::date(
                "+7 day",
                $planning->date_min
            )
        );
        foreach ($plagesOp as $_op) {
            $_op->loadRefSalle();
            $_op->multicountOperations();
            $color = CAppUI::isMediboardExtDark() ? "#6a79d2" : "#BCE";

            //to check if group is present group
            $g = CGroups::loadCurrent();
            $_op->loadRefSalle();
            $_op->_ref_salle->loadRefBloc();
            if ($_op->_ref_salle->_ref_bloc->group_id != $g->_id) {
                $color = "#748dee";
            }

            $this->addEventToPlanning($planning, $_op, $_op->date, $_op->_ref_salle->nom, $color, "operation");
        }
    }

    /**
     * @throws Exception
     */
    private function loadChirPlageConges(CPlanningWeek $planning, CMediusers $praticien): void
    {
        if (CModule::getActive("dPpersonnel")) {
            $conge       = new CPlageConge();
            $where_conge = [
                "date_debut" => $this->ds->prepare("<= ?", CMbDT::dateTime("00:00:00", $planning->date_max)),
                "date_fin"   => $this->ds->prepare(">= ?", CMbDT::dateTime("23:59:59", $planning->date_min)),
                "user_id"    => $praticien->getUserSQLClause(),
            ];

            /** @var CPlageConge[] $conges */
            $conges = $conge->loadList($where_conge);
            foreach ($conges as $_conge) {
                $libelle = self::prepareLibelle($_conge->libelle, "CPlageConge|pl");
                $_date   = $_conge->date_debut;
                while ($_date < $_conge->date_fin) {
                    $length       = CMbDT::minutesRelative($_date, $_conge->date_fin);
                    $event        = new CPlanningEvent(
                        $_conge->_guid . $_date,
                        $_date,
                        $length,
                        $libelle,
                        "#ddd",
                        true,
                        "hatching",
                        null,
                        false
                    );
                    $event->below = 1;
                    $planning->addEvent($event);
                    $_date = CMbDT::dateTime("+1 DAY", CMbDT::date($_date));
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    private function loadChirHorsPlages(CPlanningWeek $planning, CMediusers $praticien): void
    {
        $operation = new COperation();
        $where     = [];
        $whereChir = $praticien->getUserSQLClause();
        for ($i = 0; $i < self::DEFAULT_NB_DAYS; $i++) {
            $date                = CMbDT::date("+$i day", $planning->date_min);
            $where["date"]       = $this->ds->prepare("= ?", $date);
            $where["annulee"]    = $this->ds->prepare(" = '0'");
            $where["plageop_id"] = "IS NULL";
            $where[]             = "chir_id $whereChir OR anesth_id $whereChir";
            $nb_hors_plages      = $operation->countList($where);
            if ($nb_hors_plages) {
                $onclick = "viewList('$date', null, 'CPlageOp')";
                $planning->addDayLabel($date, "$nb_hors_plages intervention(s) hors-plage", null, "#ffd700", $onclick);
            }
        }
    }
}
