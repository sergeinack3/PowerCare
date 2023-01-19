<?php

/**
 * @package Mediboard\System\Elastic
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Elastic;

use Ox\Core\CAppUI;
use Ox\Core\CMbString;
use Ox\Core\Elastic\{ElasticObject,
    ElasticObjectMappings,
    ElasticObjectSettings,
    Encoding,
    Exceptions\ElasticMappingException};
use Ox\Core\Elastic\IndexLifeManagement\ElasticIndexLifeManager;
use Ox\Core\Elastic\IndexLifeManagement\Phases\{ElasticDeletePhase, ElasticHotPhase, ElasticWarmPhase,};
use Ox\Core\Logger\LoggableElasticObjectInterface;
use Ox\Core\Logger\LoggerLevels;
use Ox\Core\Units\TimeUnitEnum;
use Ox\Mediboard\Admin\CUser;
use Psr\Log\InvalidArgumentException;

/**
 * Class to structure application logs aka Mediboard Logs
 */
class ApplicationLog extends ElasticObject implements LoggableElasticObjectInterface
{
    public const DATASOURCE_NAME = "application-log";

    protected string  $message;
    protected string  $context;
    protected string  $log_level;
    protected ?string $user_id;
    protected ?string $username;
    protected ?string $server_ip;
    protected string  $session_id;

    /**
     * Construct log
     *
     * @param string $message
     * @param array  $context
     * @param int    $log_level
     */
    public function __construct(string $message = null, array $context = [], int $log_level = LoggerLevels::LEVEL_INFO)
    {
        parent::__construct();
        if ($message != null) {
            $this->message = $message;
        }
        $this->context = json_encode($context);
        $this->setLogLevel($log_level);

        // Get current context for log (user, server, session)
        $this->user_id    = (CAppUI::$user) ? CAppUI::$user->user_id : null;
        $this->username   = (CAppUI::$user->_user_username) ?? null;
        $this->server_ip  = $_SERVER["SERVER_ADDR"] ?? null;
        $this->session_id = CMbString::truncate(session_id(), 15);
    }

    /**
     * Defines settings (Index, Datasource)
     * Uses default configuration for (shards, replicas)
     * @return ElasticObjectSettings
     */
    public function setSettings(): ElasticObjectSettings
    {
        $settings = new ElasticObjectSettings(self::DATASOURCE_NAME);

        $ilm = new ElasticIndexLifeManager($settings->getILMName());
        $ilm->setHotPhase((new ElasticHotPhase())->setRolloverOnMaxAge(1, TimeUnitEnum::DAYS()))
            ->setWarmPhase((new ElasticWarmPhase(15, TimeUnitEnum::DAYS())))
            ->setDeletePhase((new ElasticDeletePhase(60, TimeUnitEnum::DAYS())));

        $settings->addIndexLifeManagement($ilm);

        return $settings;
    }

    /**
     * Mappings fields that we want to store in Elasticsearch
     *
     * @return ElasticObjectMappings
     * @throws ElasticMappingException
     */
    public function setMappings(): ElasticObjectMappings
    {
        $mappings = new ElasticObjectMappings();
        $mappings->addStringField("message", Encoding::ISO_8859_1)
            ->addStringField("context", Encoding::ISO_8859_1)
            ->addStringField("log_level", Encoding::ISO_8859_1)
            ->addRefField("user_id", CUser::class, false)
            ->addStringField("username", Encoding::ISO_8859_1)
            ->addStringField("server_ip", Encoding::ISO_8859_1)
            ->addStringField("session_id", Encoding::ISO_8859_1);

        return $mappings;
    }

    /**
     * Organize fields for rendering
     * @return array
     */
    public function prepareToRender(): array
    {
        $extra         = json_encode($this->getExtra(), true);
        $short_context = strlen($this->context) > 2 ? "[context:" . strlen($this->context) . "]" : "";
        if (array_key_exists("context", $this->highlight)) {
            $short_context = '<highlight style="background-color: yellow; display: inline;">' . $short_context . '</highlight>';
        }
        $log_data          = [
            'date'         => "[" . $this->date->format("Y-m-d H:i:s.u") . "]",
            'level'        => "[" . $this->log_level . "]",
            'color'        => LoggerLevels::getLevelColor($this->log_level),
            'message'      => $this->highlight["message"] ?? $this->message,
            'context'      => $short_context,
            'context_json' => $this->context,
            'extra'        => strlen($extra) > 2 ? "[extra:" . strlen($extra) . "]" : "",
            'extra_json'   => $extra,
        ];
        $log_data["infos"] = urlencode(serialize($log_data));

        return $log_data;
    }

    /**
     * @return array
     */
    private function getExtra(): array
    {
        return [
            "user_id"    => $this->user_id,
            "username"   => $this->username,
            "server_ip"  => $this->server_ip,
            "session_id" => $this->session_id,
        ];
    }

    public function setExtra(string $extra_data): void
    {
        $extra            = json_decode($extra_data, true);
        $this->user_id    = $extra["user_id"] ?? "";
        $this->username   = $extra["username"] ?? "";
        $this->server_ip  = $extra["server_ip"] ?? "";
        $this->session_id = $extra["session_id"] ?? "";
    }

    public function toLogFile(): string
    {
        return sprintf(
            "[%s] [%s] %s [context:%s] [extra:%s]",
            $this->date->format("Y-m-d H:i:s.u"),
            $this->log_level,
            $this->message,
            $this->context,
            json_encode($this->getExtra())
        );
    }

    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return string|null
     */
    public function getContext(): ?string
    {
        return $this->context;
    }

    /**
     * @param string $context
     */
    public function setContext(string $context): void
    {
        $this->context = $context;
    }

    /**
     * @return string|null
     */
    public function getLogLevel(): ?string
    {
        return $this->log_level;
    }

    /**
     * @param int|string $log_level
     */
    public function setLogLevel($log_level): void
    {
        try {
            $this->log_level = is_string($log_level) ? $log_level : LoggerLevels::getLevelName((int)$log_level);
        } catch (InvalidArgumentException $e) {
            $this->log_level = LoggerLevels::getLevelName(LoggerLevels::LEVEL_INFO);
        }
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    /**
     * @param string|null $user_id
     */
    public function setUserId(?string $user_id): void
    {
        $this->user_id = $user_id;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string|null
     */
    public function getServerIp(): ?string
    {
        return $this->server_ip;
    }

    /**
     * @param string|null $server_ip
     */
    public function setServerIp(?string $server_ip): void
    {
        $this->server_ip = $server_ip;
    }

    /**
     * @return string|null
     */
    public function getSessionId(): ?string
    {
        return $this->session_id;
    }

    /**
     * @param string $session_id
     */
    public function setSessionId(string $session_id): void
    {
        $this->session_id = $session_id;
    }

    public function buildFromLogRecord(array $record): self
    {
        $obj = clone $this;

        $obj->date       = $record["datetime"];
        $obj->message    = $record["message"];
        $obj->context    = json_encode($record["context"]);
        $obj->log_level  = $record["level_name"];
        $obj->user_id    = $record["extra"]["user_id"] ?? "";
        $obj->server_ip  = $record["extra"]["server_ip"] ?? "";
        $obj->session_id = $record["extra"]["session_id"] ?? "";

        return $obj;
    }
}
