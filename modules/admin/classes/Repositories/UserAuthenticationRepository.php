<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin\Repositories;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CUserAuthentication;

/**
 * User Authentication Repository.
 */
class UserAuthenticationRepository
{
    /** @var CUserAuthentication */
    private CUserAuthentication $model;

    /** @var CSQLDataSource */
    private CSQLDataSource $ds;

    public function __construct()
    {
        $this->model = new CUserAuthentication();
        $this->ds    = $this->model->getDS();
    }

    /**
     * Build aggregated stats for a period
     *
     * @param string $start                    Start date time
     * @param string $end                      End date time
     * @param string $interval                 Interval
     * @param bool   $exclude_current_function Excludes results within the current user's function
     *
     * @return array
     * @throws Exception
     */
    public function getAuthenticationDataOnPeriod(
        string $start,
        string $end,
        string $interval,
        string $domain,
        bool $exclude_current_function = false
    ): array {
        $auth = clone $this->model;

        switch ($interval) {
            default:
            case "eight-weeks":
                $period_format = "%d/%m";
                break;
            case "one-year":
                $period_format = "%Y S%U";
                break;
            case "four-years":
                $period_format = "%m/%Y";
                break;
            case "twenty-years":
                $period_format = "%Y";
                break;
        }

        // Convert date format from PHP to MySQL
        $period_format = str_replace("%M", "%i", $period_format);

        $request = new CRequest();
        $request->addSelect(
            'COUNT(*) AS `auths_count`,
            COUNT(DISTINCT(`user_id`)) AS `users_count`,
            `datetime_login` AS `datetime`,
            DATE_FORMAT(`datetime_login`, "' . $period_format . '") AS `period`'
        );
        $request->addTable($auth->_spec->table);
        $request->addWhere(
            [
                'datetime_login' => $this->ds->prepareBetween($start, $end),
            ]
        );

        $user = new CMediusers();
        switch ($domain) {
            case 'all':
                $users = $user->loadUsers(PERM_READ, null, null, true, false);
                break;
            case 'function':
                $users = $user->loadUsers(PERM_READ, CFunctions::getCurrent()->_id);
                $exclude_current_function = false;
                break;
            case 'group':
            default:
                $users = $user->loadUsers();
                break;
        }

        $user_ids = CMbArray::pluck($users, 'user_id');

        if (true === $exclude_current_function) {
            $user_ids = array_diff($user_ids, $this->getCurrentFunctionUsersId());
            if (empty($user_ids)) {
                return [];
            }
        }

        $request->addWhere(
            [
                'user_id' => $this->ds->prepareIn($user_ids),
            ]
        );
        $request->addGroup('`period`');
        $rows = $this->ds->exec($request->makeSelect());

        $items = [];
        while ($row = $this->ds->fetchAssoc($rows)) {
            $items[] = $row;
        }

        return $items;
    }

    /**
     * @param string $start                    Start date time
     * @param string $end                      End date time
     * @param string $interval                 Interval
     * @param bool   $exclude_current_function Excludes results within the current user's function
     *
     * @throws Exception
     */
    public function getAuthenticationsCountGraphData(
        string $startx,
        string $endx,
        string $interval,
        string $domain,
        bool $exclude_current_function = false
    ): array {

        switch ($interval) {
            default:
            case "eight-weeks":
                $step          = "+1 DAY";
                $period_format = "%d/%m";
                $ticks_modulo  = 3;
                break;

            case "one-year":
                $step          = "+1 WEEK";
                $period_format = "%Y S%U";
                $ticks_modulo  = 3;
                break;

            case "four-years":
                $step          = "+1 MONTH";
                $period_format = "%m/%Y";
                $ticks_modulo  = 2;
                break;

            case "twenty-years":
                $step          = "+1 YEAR";
                $period_format = "%Y";
                $ticks_modulo  = 1;
                break;
        }
        $datax = [];
        $i     = 0;
        for ($d = $startx; $d <= $endx; $d = CMbDT::dateTime($step, $d)) {
            $datax[] = [$i, CMbDT::format($d, $period_format)];
            $i++;
        }

        $entries = $this->getAuthenticationDataOnPeriod($startx, $endx, $interval, $domain, $exclude_current_function);

        $auths_count = [];
        $users_count = [];
        $datetime_by_index = [];

        foreach ($datax as $x) {
            $auths_count[$x[0]] = [$x[0], 0];
            $users_count[$x[0]] = [$x[0], 0];
            foreach ($entries as $entry) {
                if ($x[1] === CMbDT::format($entry['datetime'], $period_format)) {
                    $auths_count[$x[0]] = [$x[0], $entry['auths_count']];
                    $users_count[$x[0]] = [$x[0], $entry['users_count']];
                    $datetime_by_index[$x[0]] = $entry['period'];
                }
            }
        }

        // Removing some xaxis ticks
        foreach ($datax as $i => &$x) {
            if ($i % $ticks_modulo) {
                $x[1] = '';
            }
        }

        $title = '';

        $subtitle = CMbDT::format($endx, CAppUI::conf("longdate"));

        $series = [
            [
                'label' => CAppui::tr("CUserAuthentication|pl"),
                'data'  => $auths_count,
                'bars' => [
                    'show' => true,
                ],
            ],
            [
                'label' => CAppui::tr("CUser|pl"),
                'data'  => $users_count,
                'lines' => [
                    'show' => true,
                ],
                'color' => 'royalblue',
                'yaxis' => 2,
            ]
        ];

        $options = [
            'title'       => $title,
            'subtitle'    => $subtitle,
            'xaxis'       => [
                'labelsAngle' => 45,
                'min'         => 0,
                'ticks'       => $datax,
            ],
            'yaxis'       => [
                'min'             => 0,
                'title'           => CAppUI::tr('CUserAuthentication|pl'),
                'autoscaleMargin' => 1,
            ],
            'y2axis'      => [
                'min'             => 0,
                'title'           => CAppUI::tr('CUser|pl'),
                'autoscaleMargin' => 1,
            ],
            'grid'        => [
                'verticalLines' => false,
            ],
            'HtmlText'    => false,
            'spreadsheet' => [
                'show'             => true,
                'csvFileSeparator' => ';',
                'decimalSeparator' => ',',
            ],
        ];

        return [
            'series'            => $series,
            'options'           => $options,
            'datetime_by_index' => $datetime_by_index,
        ];
    }

    /**
     * @throws Exception
     */
    private function getCurrentFunctionUsersId(): array
    {
        $user = new CMediusers();
        $ds   = $user->getDS();

        return $user->loadColumn(
            $user->_spec->key,
            [
                'functions_mediboard.function_id' => $ds->prepare('= ?', CFunctions::getCurrent()->_id),
            ],
            [
                'functions_mediboard' => 'functions_mediboard.function_id = users_mediboard.function_id',
            ]
        );
    }
}
