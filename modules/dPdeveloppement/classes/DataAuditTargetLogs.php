<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use Ox\Core\CMbException;
use Ox\Mediboard\System\CObjectClass;
use Ox\Mediboard\System\CUserAction;
use Ox\Mediboard\System\CUserLog;
use PDO;

/**
 * Description
 */
class DataAuditTargetLogs
{
    /** @var array */
    private $logs = [];

    /**
     * @param PDO    $connection
     * @param string $start_date
     * @param string $end_date
     *
     * @return void
     * @throws CMbException
     */
    public function parse(PDO $connection, string $start_date, string $end_date)
    {
        if (!$start_date) {
            throw new CMbException('common-error-Missing parameter: %s', 'start_date');
        }

        if (!$end_date) {
            throw new CMbException('common-error-Missing parameter: %s', 'end_date');
        }

        $user_log       = new CUserLog();
        $user_log_table = $user_log->getSpec()->table;
        $user_log_key   = $user_log->getSpec()->key;

        $user_action       = new CUserAction();
        $user_action_table = $user_action->getSpec()->table;
        $user_action_key   = $user_action->getSpec()->key;

        $object_class       = new CObjectClass();
        $object_class_table = $object_class->getSpec()->table;
        $object_class_key   = $object_class->getSpec()->key;

        // Todo: Use framework ORM
        $stmt = $connection->prepare(
            "SELECT `{$user_log_key}`, `date`, `object_class`, `object_id` 
                 FROM `{$user_log_table}` 
                 WHERE `date` BETWEEN ? AND ?;"
        );

        $stmt->execute([$start_date, $end_date]);
        $user_log_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $user_logs = [];
        foreach ($user_log_result as $_result) {
            $user_logs[$_result[$user_log_key]] = [
                'id'           => $_result[$user_log_key],
                'date'         => $_result['date'],
                'object_class' => $_result['object_class'],
                'object_id'    => $_result['object_id'],
            ];
        }

        // Todo: Use framework ORM
        $stmt = $connection->prepare(
            "SELECT `ua`.`{$user_action_key}`, `ua`.`date`, `oc`.`object_class`, `ua`.`object_id` 
                 FROM `{$user_action_table}` AS `ua`, `{$object_class_table}` AS `oc`
                 WHERE `ua`.`object_class_id` = `oc`.`{$object_class_key}`
                   AND `ua`.`date` BETWEEN ? AND ?;"
        );

        $stmt->execute([$start_date, $end_date]);
        $user_action_result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $user_actions = [];
        foreach ($user_action_result as $_result) {
            $user_actions[$_result[$user_action_key]] = [
                'id'           => $_result[$user_action_key],
                'date'         => $_result['date'],
                'object_class' => $_result['object_class'],
                'object_id'    => $_result['object_id'],
            ];
        }

        $logs = [
            'user_log'    => [
                'count' => count($user_logs),
                'logs'  => $user_logs,
            ],
            'user_action' => [
                'count' => count($user_actions),
                'logs'  => $user_actions,
            ],
        ];

        $this->logs = $logs;
    }

    /**
     * @return array
     */
    public function getUserLogsIDs(): array
    {
        return array_keys($this->logs['user_log']['logs']);
    }

    /**
     * @return array
     */
    public function getUserActionsIDs(): array
    {
        return array_keys($this->logs['user_action']['logs']);
    }

    public function getUserLog(int $log_id): ?array
    {
        return ($this->logs['user_log']['logs'][$log_id]) ?? null;
    }

    public function getUserAction(int $log_id): ?array
    {
        return ($this->logs['user_action']['logs'][$log_id]) ?? null;
    }

    private function getUserLogField(int $log_id, string $field)
    {
        $log = $this->getUserLog($log_id);

        if ($log && isset($log[$field])) {
            return $log[$field];
        }

        return null;
    }

    private function getUserActionField(int $log_id, string $field)
    {
        $log = $this->getUserAction($log_id);

        if ($log && isset($log[$field])) {
            return $log[$field];
        }

        return null;
    }

    public function getUserLogDate(int $log_id): ?string
    {
        return $this->getUserLogField($log_id, 'date');
    }

    public function getUserActionDate(int $log_id): ?string
    {
        return $this->getUserActionField($log_id, 'date');
    }

    public function getUserLogObjectClass(int $log_id): ?string
    {
        return $this->getUserLogField($log_id, 'object_class');
    }

    public function getUserActionObjectClass(int $log_id): ?string
    {
        return $this->getUserActionField($log_id, 'object_class');
    }

    public function getLogFromType(string $type, int $log_id): ?array
    {
        switch ($type) {
            case 'user_log':
                return $this->getUserLog($log_id);

            case 'user_action':
                return $this->getUserAction($log_id);

            default:
        }

        return null;
    }

    public function getLogDateFromType(string $type, int $log_id): ?string
    {
        switch ($type) {
            case 'user_log':
                return $this->getUserLogDate($log_id);

            case 'user_action':
                return $this->getUserActionDate($log_id);

            default:
        }

        return null;
    }

    public function getLogObjectClassFromType(string $type, int $log_id): ?string
    {
        switch ($type) {
            case 'user_log':
                return $this->getUserLogObjectClass($log_id);

            case 'user_action':
                return $this->getUserActionObjectClass($log_id);

            default:
        }

        return null;
    }
}
