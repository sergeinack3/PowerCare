<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Astreintes;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CPerson;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Load Astreintes data for the calendar builder
 */
class AstreinteManager
{
    public const MODE_DAY     = "day";
    public const MODE_WEEK    = "week";
    public const MODE_MONTH   = "month";
    public const MODE_YEAR    = "year";
    public const MODE_OFFLINE = "offline";

    public const DEFAULT_ASTREINTE_ORDER = "start DESC,end";

    /**
     * Charge les plages d'astreinte selon les filtres
     *
     * @param string $date
     * @param string $mode
     * @param array  $type_names
     * @param int    $category
     *
     * @return array
     * @throws Exception
     */
    public function loadAstreintes(string $date, string $mode, array $type_names, ?string $category): array
    {
        $group = CGroups::loadCurrent();

        $astreinte = new CPlageAstreinte();
        $where     = [
            "group_id" => $astreinte->getDS()->prepare("= ? ", $group->_id),
        ];

        if ($type_names && reset($type_names) != "all") {
            $where["type"] = CSQLDataSource::prepareIn($type_names);
        }

        $category = ($category || $category == "0") ? $category : CAppUI::pref("categorie");
        if ($category != 0) {
            $where["categorie"] = $astreinte->getDS()->prepare("= ?", $category);
        }

        switch ($mode) {
            case self::MODE_YEAR:
                $year_first     = CMbDT::transform(null, $date, "%Y-01-01");
                $year_last      = CMbDT::transform(null, $date, "%Y-12-31");
                $where["start"] = $astreinte->getDS()->prepare("< ?", CMbDT::dateTime("$year_last 23:59:00"));
                $where["end"]   = $astreinte->getDS()->prepare("> ?", CMbDT::dateTime("$year_first 00:00:00"));
                break;

            case self::MODE_MONTH:
                $month_monday   = CMbDT::date("first day of this month", $date);
                $month_sunday   = CMbDT::date("last day of this month", $month_monday);
                $where["start"] = $astreinte->getDS()->prepare("< ?", CMbDT::dateTime("$month_sunday 23:59:00"));
                $where["end"]   = $astreinte->getDS()->prepare("> ?", CMbDT::dateTime("$month_monday 00:00:00"));
                break;

            case self::MODE_WEEK:
                $week_monday    = CMbDT::date("this week", $date);
                $week_sunday    = CMbDT::date("next sunday", $week_monday);
                $where["start"] = $astreinte->getDS()->prepare("<= ?", CMbDT::dateTime("$week_sunday 23:59:00"));
                $where["end"]   = $astreinte->getDS()->prepare(">= ?", CMbDT::dateTime("$week_monday 00:00:00"));
                break;
            case self::MODE_DAY:
            default:
                $where["start"] = $astreinte->getDS()->prepare("< ?", CMbDT::dateTime("$date 23:59:00"));
                $where["end"]   = $astreinte->getDS()->prepare("> ?", CMbDT::dateTime("$date 00:00:00"));
                break;
        }

        $astreintes = $astreinte->loadList($where, self::DEFAULT_ASTREINTE_ORDER);

        foreach ($astreintes as $_astreinte) {
            $_astreinte->loadRefUser();
            $_astreinte->loadRefColor();
            $_astreinte->getCollisions();
            $_astreinte->loadRefCategory();
        }

        return $astreintes;
    }

    /**
     * Compute next and prev date for the interface
     *
     * @param string $date
     * @param string $mode
     *
     * @return array
     */
    public function computeNextPrev(string $date, string $mode): array
    {
        switch ($mode) {
            case self::MODE_OFFLINE:
                $date_prev = CMbDT::date("first day of this month", $date);
                $date_next = CMbDT::date("last day of this month", $date_prev);
                break;
            case self::MODE_YEAR:
                $date_prev = CMbDT::date("-1 YEAR", $date);
                $date_next = CMbDT::date("+1 YEAR", $date);
                break;
            case self::MODE_MONTH:
                $month_monday = CMbDT::date("first day of this month", $date);
                $date_prev    = CMbDT::date("-1 MONTH", $month_monday);
                $date_next    = CMbDT::date("+1 MONTH", $month_monday);
                break;
            case self::MODE_WEEK:
                $date_prev = CMbDT::date("-1 WEEK", $date);
                $date_next = CMbDT::date("+1 WEEK", $date);
                break;
            case self::MODE_DAY:
            default:
                $date_prev = CMbDT::date("-1 DAY", $date);
                $date_next = CMbDT::date("+1 DAY", $date);
                break;
        }

        return [$date_prev, $date_next];
    }

    /**
     * @throws Exception
     */
    public function loadAstreintesForDays(string $date, string $time): array
    {
        $plage_astreinte  = new CPlageAstreinte();
        $where            = [
            "start"    => $plage_astreinte->getDs()->prepare("< ?", "$date $time"),
            "end"      => $plage_astreinte->getDs()->prepare("> ?", "$date 00:00:00'"),
            "group_id" => $plage_astreinte->getDs()->prepare(" = ?", CGroups::loadCurrent()->_id),
        ];
        $plages_astreinte = $plage_astreinte->loadList($where);

        /** @var CPlageAstreinte[] $plages_astreinte */
        foreach ($plages_astreinte as $key_plage => $_plage) {
            if ($_plage->end < CMbDT::dateTime()) {
                unset($plages_astreinte[$key_plage]);
            }

            $_plage->loadRefUser();
            $_plage->loadRefColor();
        }

        return $plages_astreinte;
    }

    /**
     * @throws CMbModelNotFoundException
     */
    public function getPhonesFromUser(?CPerson $user): array
    {
        $phones = [];
        if (!$user->_id) {
            return $phones;
        }

        $muser = CMediusers::findOrFail($user->_id);
        $user  = $muser->loadRefUser();
        $muser->loadRefFunction()->loadRefGroup();

        $this->addPhone("CUser-user_astreinte", $user->user_astreinte, $phones);
        $this->addPhone("CUser-user_astreinte_autre", $user->user_astreinte_autre, $phones);
        $this->addPhone("CUser-user_mobile", $user->user_mobile, $phones);
        $this->addPhone("CUser-user_phone", $user->user_phone, $phones);
        $this->addPhone("CFunctions-tel", $muser->_ref_function->tel, $phones);

        return $phones;
    }

    /**
     * Helper pour ajouter les numeros d'astreinte
     */
    private function addPhone(string $field_str, string $field = null, array &$phones = null): void
    {
        if ($field && !in_array($field, $phones)) {
            $phones[$field_str] = $field;
        }
    }

    /**
     * @throws Exception
     */
    public function loadAstreintesForUser(CMediusers $user): array
    {
        $plage_astreinte          = new CPlageAstreinte();
        $plage_astreinte->user_id = $user->_id;

        $astreintes = $plage_astreinte->loadMatchingList(self::DEFAULT_ASTREINTE_ORDER, 100);

        foreach ($astreintes as $_astreinte) {
            /** @var $_astreinte CPlageAstreinte */
            $_astreinte->loadRefUser();
            $_astreinte->loadRefColor();
            $_astreinte->getCollisions();
            $_astreinte->loadRefCategory();
        }

        return $astreintes;
    }

    /**
     * @throws Exception
     */
    public function getPlageAstreintes(
        CMediusers $user,
        array $users,
        ?string $plage_id,
        ?string $plage_date,
        ?string $plage_hour,
        ?string $plage_minutes
    ): CPlageAstreinte {
        $plageastreinte = new CPlageAstreinte();

        // edition
        if ($plage_id) {
            $plageastreinte->load($plage_id);
            $plageastreinte->loadRefsNotes();
            $plageastreinte->countDuplicatedPlages();
        }

        // creation
        if (!$plageastreinte->_id) {
            // phone
            $plageastreinte->phone_astreinte = $user->_user_astreinte;

            $plageastreinte->group_id = CGroups::loadCurrent()->_id;

            // date & hour
            if ($plage_date && $plage_hour) {
                $plageastreinte->start = "$plage_date $plage_hour:$plage_minutes:00";
            }

            // user
            if (in_array($user->_id, array_keys($users))) {
                $plageastreinte->user_id = $user->_id;
            }
        }

        $plageastreinte->loadRefGroup();

        return $plageastreinte;
    }

    /**
     * @throws Exception
     */
    public function getUsersForPlageAstreinte(): array
    {
        $user = new CMediusers();

        $ljoin = [
            "users"               => "users.user_id = users_mediboard.user_id",
            "functions_mediboard" => "functions_mediboard.function_id = users_mediboard.function_id",
        ];
        $where = [
            "users_mediboard.actif" => "= '1' ",
            "group_id"              =>
                $user->getDS()->prepare(
                    " = ?",
                    CGroups::loadCurrent()->_id
                ) . "or group_id is null ",
        ];

        $where_count              = $where;
        $where_count["astreinte"] = "= '1'";
        $count_oncall_configured  = $user->countList($where_count, null, $ljoin);

        if ($count_oncall_configured > 0) {
            $where["astreinte"] = "= '1'";
        }

        return $user->loadListWithPerms(PERM_EDIT, $where, "users.user_last_name", null, null, $ljoin);
    }
}
