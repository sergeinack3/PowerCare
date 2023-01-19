<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Board\Controllers\Legacy;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Board\TdbCalendarView;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

class TdbCalendarViewLegacyController extends CLegacyController
{
    private CMediusers $user;
    private CMediusers $praticien;
    private CFunctions $function;

    private string $perm_fonct;

    /**
     * @throws Exception
     */
    public function incBoard(): void
    {
        $prat_selected     = CView::get("praticien_id", "ref class|CMediusers", true);
        $function_selected = CView::get("function_id", "ref class|CFunctions");

        // Chargement de l'utilisateur courant
        $user       = CMediusers::get();
        $perm_fonct = CAppUI::pref("allow_other_users_board");

        if (!$user->isProfessionnelDeSante() && !$user->isSecretaire()) {
            CAppUI::accessDenied();
        }

        $prat     = new CMediusers();
        $function = new CFunctions();

        if ($prat_selected) {
            $function_selected = null;
            $prat->load($prat_selected);
        } elseif ($user->isProfessionnelDeSante() && !$function_selected) {
            $prat = $user;
        }

        if ($function_selected) {
            $function->load($function_selected);
        }
        $this->user       = $user;
        $this->praticien  = $prat;
        $this->function   = $function;
        $this->perm_fonct = $perm_fonct;
    }

    private function renderBoard(): void
    {
        $this->renderSmarty(
            "inc_board",
            [
                "user"       => $this->user,
                "prat"       => $this->praticien,
                "function"   => $this->function,
                "perm_fonct" => $this->perm_fonct,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function viewWeek(): void
    {
        $this->checkPermRead();

        $this->incBoard();

        $date  = CView::get("date", "date default|now");
        $prec  = CMbDT::date("-1 week", $date);
        $suiv  = CMbDT::date("+1 week", $date);
        $today = CMbDT::date();

        //Planning au format  CPlanningWeek
        $debut = CMbDT::date("-1 week", $date);
        $debut = CMbDT::date("next monday", $debut);
        $fin   = CMbDT::date("next sunday", $debut);

        CView::checkin();

        $calendarview = new TdbCalendarView();

        // Instanciation du planning
        $planning = $calendarview->createPlanningWeek($date, $debut, $fin, $this->praticien, $this->user);
        $this->renderBoard();
        $this->renderSmarty(
            "vw_week",
            [
                "date"     => $date,
                "today"    => $today,
                "debut"    => $debut,
                "fin"      => $fin,
                "prec"     => $prec,
                "suiv"     => $suiv,
                "chirSel"  => $this->praticien->_id,
                "planning" => $planning,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function viewMonth(): void
    {
        $this->checkPermRead();

        $this->incBoard();

        $date = CView::get('date', "date default|now", true);

        CView::checkin();

        $user = CMediusers::get();

        $user->isSecretaire();
        $user->isProfessionnelDeSante();

        $calendarview = new TdbCalendarView();

        $praticien = $this->praticien;

        $pref = CAppUI::pref("allow_other_users_board");

        $calendarview->prepareMonthView($praticien, $user, $pref);

        $listPrat = $calendarview->getListPraticiens();
        $listFunc = $calendarview->getListFunctions();

        $this->renderSmarty(
            "vw_month",
            [
                "date"        => $date,
                "prev"        => $date,
                "next"        => $date,
                "prat"        => $praticien,
                "listPrat"    => $listPrat,
                "user"        => $user,
                "listFunc"    => $listFunc,
                "function_id" => $this->function,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function viewDay(): void
    {
        $this->checkPermRead();

        $this->incBoard();

        $date = CView::get("date", "date default|now", true);
        $vue  = CView::get("vue2", "bool default|" . CAppUI::pref("AFFCONSULT", 0), true);

        CView::checkin();

        $prat = $this->praticien;
        $this->renderBoard();

        $params = [
            "date"     => $date,
            "prec"     => CMbDT::date("-1 day", $date),
            "suiv"    => CMbDT::date("+1 day", $date),
            "user_id"  => CMediusers::get()->_id,
            "vue"      => $vue,
            "prat"     => $prat,
            "function" => $this->function,
        ];

        if (!CAppUI::pref("alternative_display")) {
            $this->renderSmarty(
                "vw_day",
                $params
            );
        } else {
            $calendar_view = new TdbCalendarView();

            $account = $calendar_view->loadPraticienAlternativeAccount($prat);

            $params["account"] = $account;
            $this->renderSmarty(
                "vw_day_alternative",
                $params,
            );
        }
    }
}
