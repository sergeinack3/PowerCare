<?php

/**
 * @package Mediboard\Core\Elastic
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Elastic;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Ox\Core\Elastic\ElasticObject;
use Ox\Core\Elastic\ElasticObjectMappings;
use Ox\Core\Elastic\ElasticObjectSettings;
use Ox\Core\Elastic\Encoding;
use Ox\Core\Elastic\Exceptions\ElasticMappingException;
use Ox\Core\Elastic\IndexLifeManagement\ElasticIndexLifeManager;
use Ox\Core\Elastic\IndexLifeManagement\Phases\ElasticDeletePhase;
use Ox\Core\Elastic\IndexLifeManagement\Phases\ElasticHotPhase;
use Ox\Core\Elastic\IndexLifeManagement\Phases\ElasticWarmPhase;
use Ox\Core\Logger\ErrorTypes;
use Ox\Core\Logger\LoggableElasticObjectInterface;
use Ox\Core\Units\TimeUnitEnum;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CErrorLog;

class ErrorLog extends ElasticObject implements LoggableElasticObjectInterface
{
    public const DATASOURCE_NAME = "error-log";

    /**
     * @var string
     */
    protected $microtime;
    /**
     * @var int
     */
    protected $user_id;
    /**
     * @var string
     */
    protected $server_ip;
    /**
     * @var string
     */
    protected $request_uid;
    /**
     * @var string
     */
    protected $type;
    /**
     * @var string
     */
    protected $text;
    /**
     * @var string
     */
    protected $file;
    /**
     * @var int
     */
    protected $line;
    /**
     * @var string
     */
    protected $signature_hash;
    /**
     * @var string
     */
    protected $stacktrace;
    /**
     * @var string
     */
    protected $param_GET;
    /**
     * @var string
     */
    protected $param_POST;
    /**
     * @var string
     */
    protected $session_data;

    /** @var int */
    protected $count = 1;

    /** @var array */
    protected $_similar_ids = [];

    /** @var array */
    protected $_similar_user_ids = [];

    /** @var array */
    protected $_similar_server_ips = [];

    /** @var DateTimeImmutable */
    protected $_date_min;

    /** @var DateTimeImmutable */
    protected $_date_max;

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
     * @return ElasticObjectMappings
     * @throws ElasticMappingException
     */
    public function setMappings(): ElasticObjectMappings
    {
        $mappings = new ElasticObjectMappings();
        $mappings->addRefField("user_id", CUser::class, false)
            ->addStringField("server_ip", Encoding::UTF_8)
            ->addStringField("microtime", Encoding::UTF_8)
            ->addStringField("request_uid", Encoding::UTF_8)
            ->addStringField("type", Encoding::UTF_8)
            ->addStringField("text", Encoding::ISO_8859_1, false, true)
            ->addStringField("file", Encoding::UTF_8)
            ->addIntField("line")
            ->addIntField("count")
            ->addStringField("signature_hash", Encoding::UTF_8, false, true)
            ->addStringField("stacktrace", Encoding::UTF_8, false, true)
            ->addStringField("param_GET", Encoding::UTF_8, false, true)
            ->addStringField("param_POST", Encoding::UTF_8, false, true)
            ->addStringField("session_data", Encoding::UTF_8);

        return $mappings;
    }


    public function __construct(array $data = null)
    {
        parent::__construct();
        if ($data != null && $data != []) {
            $this->date           = date_create_immutable_from_format("Y-m-d H:i:s", $data["time"]);
            $this->user_id        = $data["user_id"] ?? "";
            $this->server_ip      = $data["server_ip"] ?? $_SERVER["SERVER_ADDR"] ?? "";
            $this->request_uid    = $data["request_uid"] ?? "";
            $this->type           = $data["type"] ?? "";
            $this->text           = $data["text"] ?? "";
            $this->file           = $data["file"] ?? "";
            $this->line           = $data["line"] ?? "";
            $this->signature_hash = $data["signature_hash"] ?? "";
            $this->count          = $data["count"] ?? 1;
            $this->stacktrace     = json_encode($data["data"]["stacktrace"] ?? []);
            $this->param_GET      = json_encode($data["data"]["param_GET"] ?? []);
            $this->param_POST     = json_encode($data["data"]["param_POST"] ?? []);
            $this->session_data   = json_encode($data["data"]["session_data"] ?? []);
        }
    }

    public function toCErrorLog(): CErrorLog
    {
        $error                      = new CErrorLog();
        $error->_id                 = $this->id;
        $error->user_id             = $this->user_id;
        $error->server_ip           = $this->server_ip;
        $error->datetime            = $this->date->format("Y-m-d H:i:s");
        $error->error_type          = $this->type;
        $error->text                = $this->text;
        $error->file_name           = $this->file ?? "";
        $error->line_number         = $this->line ?? "";
        $error->_stacktrace_output  = json_decode($this->stacktrace, true) ?? [];
        $error->_param_GET          = json_decode($this->param_GET, true) ?? [];
        $error->_param_POST         = json_decode($this->param_POST, true) ?? [];
        $error->_session_data       = json_decode($this->session_data, true) ?? [];
        $error->signature_hash      = $this->signature_hash;
        $error->request_uid         = $this->request_uid;
        $error->count               = $this->count;
        $error->_similar_count      = $this->count;
        $error->_similar_ids        = $this->_similar_ids;
        $error->_similar_user_ids   = $this->_similar_user_ids;
        $error->_similar_server_ips = $this->_similar_server_ips;
        if ($this->_date_min) {
            $error->_datetime_min = $this->_date_min->format("Y-m-d H:i:s");
        }

        if ($this->_date_max) {
            $error->_datetime_max = $this->_date_max->format("Y-m-d H:i:s");
        }

        $_types = array_flip(ErrorTypes::TYPES);
        if (isset($_types[$this->type])) {
            $_num_type        = $_types[$this->type];
            $error->_category = ErrorTypes::CATEGORIES[$_num_type];
        }

        $error->_url = "?" . http_build_query($error->_param_GET, true, "&");

        return $error;
    }

    /**
     * @return string
     */
    public function getMicrotime(): string
    {
        return $this->microtime;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @return string
     */
    public function getServerIp(): string
    {
        return $this->server_ip;
    }

    /**
     * @return string
     */
    public function getRequestUid(): string
    {
        return $this->request_uid;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @return string
     */
    public function getSignatureHash(): string
    {
        return $this->signature_hash;
    }

    /**
     * @return string
     */
    public function getStacktrace(): string
    {
        return $this->stacktrace;
    }

    /**
     * @return string
     */
    public function getParamGET(): string
    {
        return $this->param_GET;
    }

    /**
     * @return string
     */
    public function getParamPOST(): string
    {
        return $this->param_POST;
    }

    /**
     * @return string
     */
    public function getSessionData(): string
    {
        return $this->session_data;
    }

    /**
     * @return array
     */
    public function getSimilarUserIds(): array
    {
        return $this->_similar_user_ids;
    }

    /**
     * @param array $similar_user_ids
     */
    public function addSimilarUserId(string $similar_user_id): void
    {
        $this->_similar_user_ids[] = $similar_user_id;
    }

    /**
     * @return array
     */
    public function getSimilarIds(): array
    {
        return $this->_similar_ids;
    }

    /**
     * @param array $similar_user_ids
     */
    public function addSimilarId(string $similar_user_id): void
    {
        $this->_similar_ids[] = $similar_user_id;
    }

    /**
     * @return array
     */
    public function getSimilarServerIps(): array
    {
        return $this->_similar_server_ips;
    }

    /**
     * @param array $similar_server_ips
     */
    public function addSimilarServerIp(string $similar_server_ip): void
    {
        $this->_similar_server_ips[] = $similar_server_ip;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @param int $count
     */
    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDateMin(): DateTimeImmutable
    {
        return $this->_date_min;
    }

    /**
     * @param DateTimeImmutable $date_min
     */
    public function setDateMin(string $date_min, DateTimeZone $timezone): void
    {
        $this->_date_min = DateTimeImmutable::createFromFormat(
            self::ELASTIC_DATE_TIME_FORMAT_WITHOUT_TIMEZONE,
            $date_min,
            new DateTimeZone("UTC")
        )->setTimezone($timezone);
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDateMax(): DateTimeImmutable
    {
        return $this->_date_max;
    }

    /**
     * @param DateTimeImmutable $date_max
     */
    public function setDateMax(string $date_max, DateTimeZone $timezone): void
    {
        $this->_date_max = DateTimeImmutable::createFromFormat(
            self::ELASTIC_DATE_TIME_FORMAT_WITHOUT_TIMEZONE,
            $date_max,
            new DateTimeZone("UTC")
        )->setTimezone($timezone);
    }

    /**
     * @param array $record
     *
     * @return $this
     */
    public function buildFromLogRecord(array $record): self
    {
        $obj = clone $this;

        /** @var Exception $exception */
        $exception           = $record["context"]["exception"];
        $obj->text           = $exception->getMessage();
        $obj->user_id        = array_key_exists("user_id", $record["extra"]) ? $record["extra"]["user_id"] : "";
        $obj->server_ip      = array_key_exists("server_ip", $record["extra"]) ? $record["extra"]["server_ip"] : "";
        $obj->microtime      = array_key_exists("microtime", $record["extra"]) ? $record["extra"]["microtime"] : "";
        $obj->request_uid    = array_key_exists(
            "request_uuid",
            $record["extra"]
        ) ? $record["extra"]["request_uuid"] : "";
        $obj->type           = $record["extra"]["type"];
        $obj->file           = $record["extra"]["file"];
        $obj->line           = $exception->getLine();
        $obj->count          = $record["extra"]["count"];
        $obj->signature_hash = $record["extra"]["signature_hash"];
        $obj->stacktrace     = json_encode($record["extra"]["data"]["stacktrace"]);
        $obj->param_GET      = json_encode($record["extra"]["data"]["param_GET"]);
        $obj->param_POST     = json_encode($record["extra"]["data"]["param_POST"]);
        $obj->session_data   = json_encode($record["extra"]["data"]["session_data"]);

        return $obj;
    }
}
