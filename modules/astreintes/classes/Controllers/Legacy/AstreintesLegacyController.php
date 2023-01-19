<?php

/**
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Astreintes\Controllers\Legacy;

use DateTimeImmutable;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Astreintes\AstreinteCalendarBuilder;
use Ox\Mediboard\Astreintes\AstreinteCalendarFactory;
use Ox\Mediboard\Astreintes\AstreinteManager;
use Ox\Mediboard\Astreintes\CCategorieAstreinte;
use Ox\Mediboard\Astreintes\CPlageAstreinte;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Controller for Astreintes
 */
class AstreintesLegacyController extends CLegacyController
{
    /**
     * @throws Exception
     */
    public function calendrierAstreinte(): void
    {
        $this->checkPermRead();

        $date        = CView::get("date", "date default|now", true);
        $mode        = CView::get("mode", "enum list|day|week|month|year default|week", true);
        $category_id = CView::get(
            "category",
            "ref class|CCategorieAstreinte default|" . CAppUI::pref("categorie-astreintes")
        );

        CView::checkin();

        $calendar_builder = new AstreinteCalendarBuilder(new DateTimeImmutable($date), $mode, $category_id);

        $calendar = $calendar_builder->buildAstreinteCalendar(new AstreinteCalendarFactory());

        [$date_prev, $date_next] = (new AstreinteManager())->computeNextPrev($date, $mode);

        $this->renderSmarty(
            "vw_calendar",
            [
                "date"                      => $date,
                "next"                      => $date_next,
                "prev"                      => $date_prev,
                "planning"                  => $calendar,
                "height_planning_astreinte" => CAppUI::pref("planning_resa_height", 1500),
                "mode"                      => $mode,
                "categories"                => CCategorieAstreinte::loadListCategories(),
                "current_category_id"       => $category_id,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function listAstreintes(): void
    {
        $this->checkPermRead();

        $date        = CView::get("date", "date default|now", true);
        $mode        = CView::get("mode", "enum list|day|week|month|year default|day", true);
        $type        = CView::get("type_names", "str default|all");
        $category_id = CView::get(
            "category",
            "ref class|CCategorieAstreinte default|" . CAppUI::pref("categorie-astreintes")
        );

        CView::checkin();

        $astreinteManager = new AstreinteManager();

        $type_names = is_array($type) ? $type : explode(",", $type);

        $astreintes = $astreinteManager->loadAstreintes($date, $mode, $type_names, $category_id);
        [$date_prev, $date_next] = $astreinteManager->computeNextPrev($date, $mode);

        $this->renderSmarty("vw_list_astreintes", [
            "astreinte"           => new CPlageAstreinte(),
            "astreintes"          => $astreintes,
            "today"               => $date,
            "date_prev"           => $date_prev,
            "date_next"           => $date_next,
            "mode"                => $mode,
            "type_names"          => $type_names,
            "categories"          => CCategorieAstreinte::loadListCategories(),
            "current_category_id" => $category_id,

        ]);
    }

    /**
     * @throws Exception
     */
    public function listAstreintesDay(): void
    {
        $this->checkPermRead();

        $date = CView::get("date", "date default|now");
        $time = CView::get("time", "time default|now");

        CView::checkin();

        $plages_astreinte = (new AstreinteManager())->loadAstreintesForDays($date, $time);

        $title = CAppUI::tr("CPlageAstreinte.For") . " "
            . htmlentities(
                CMbDT::format($date, CAppUI::conf("longdate")),
                ENT_COMPAT
            );

        $this->renderSmarty(
            "vw_list_day_astreinte",
            [
                "plages_astreinte" => $plages_astreinte,
                "categories"       => CCategorieAstreinte::loadListCategories(),
                "title"            => $title,
                "date"             => $date,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function offlineListAstreintes(): void
    {
        $this->checkPermRead();

        $date       = CView::get("date", "date default|now", true);
        $mode       = CView::get("mode", "enum list|day|week|month|year default|day", true);
        $type_names = CView::get("type_names", "str default|all", true);
        $category_id = CView::get("category", "ref class|CCategorieAstreinte");

        CView::checkin();

        $astreinteManager = new AstreinteManager();

        $type_names = is_array($type_names) ? $type_names : explode(",", $type_names);
        $astreintes = $astreinteManager->loadAstreintes($date, $mode, $type_names, $category_id ?: null);
        [$start, $end] = $astreinteManager->computeNextPrev($date, AstreinteManager::MODE_OFFLINE);

        $this->renderSmarty("offline_list_astreintes", [
            "astreintes"   => $astreintes,
            "start_period" => $start,
            "end_period"   => $end,
        ]);
    }

    /**
     * @throws Exception
     */
    public function listPhonesFromUser(): void
    {
        $this->checkPermRead();

        $user_id = CView::get("user_id", "ref class|CMediusers");

        CView::checkin();

        $user = CUser::findOrNew($user_id);

        $phones = (new AstreinteManager())->getPhonesFromUser($user);

        $this->renderSmarty("inc_list_phones", [
            "phones" => $phones,
            "user"   => $user,
        ]);
    }

    /**
     * @throws Exception
     */
    public function loadAstreintesUser(): void
    {
        $this->checkPermRead();

        $user_id  = CView::get("user_id", "ref notNull class|CMediusers");
        $plage_id = CView::get("plage_id", "ref class|CPlageAstreinte");

        CView::checkin();

        $user = CMediusers::findOrFail($user_id);

        $plages_astreinte = (new AstreinteManager())->loadAstreintesForUser($user);

        $this->renderSmarty("inc_liste_plages_astreinte", [
            "user"             => $user,
            "plages_astreinte" => $plages_astreinte,
            "plage_id"         => $plage_id,
            "today"            => CMbDT::dateTime(),
        ]);
    }

    /**
     * @throws Exception
     */
    public function editPlageAstreinte(): void
    {
        $this->checkPermEdit();

        $plage_id      = CView::get("plage_id", "str");
        $plage_date    = CView::get("date", "str");
        $plage_hour    = CView::get("hour", "str");
        $plage_minutes = CView::get("minutes", "str");
        $user_id       = CView::get("user_id", "str");

        CView::checkin();

        $astreinte_manager = new AstreinteManager();

        $users = $astreinte_manager->getUsersForPlageAstreinte();

        $user = CMediusers::findOrNew($user_id);

        $plageastreinte = $astreinte_manager->getPlageAstreintes(
            $user,
            $users,
            $plage_id,
            $plage_date,
            $plage_hour,
            $plage_minutes
        );
        $this->renderSmarty(
            "inc_edit_plage_astreinte",
            [
                "users"          => $users,
                "user"           => CMediusers::get(),
                "group"          => CGroups::get(),
                "plageastreinte" => $plageastreinte,
                "categories"     => CCategorieAstreinte::loadListCategories(),
            ]
        );
    }
}
