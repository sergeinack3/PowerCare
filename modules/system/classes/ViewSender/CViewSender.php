<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\ViewSender;

use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\Chronometer;
use Ox\Core\CHTTPClient;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbPath;
use Ox\Core\CMbString;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Interop\Ftp\CSourceSFTP;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceFileSystem;
use Ox\Mediboard\System\Sources\CSourceFile;
use Throwable;
use ZipArchive;

/**
 * View sender class.
 * - FTP source
 * - cron table-like period + offset planning
 * - rotation on destination
 */
class CViewSender extends CMbObject
{
    public const MINUTES = 60;
    public const HOURS   = 24;

    public const RESOURCE_TYPE = "viewSender";

    public const RELATION_LAST_EXECUTION = 'lastExecution';

    public const FIELDSET_CONFIGURATION = 'configuration';

    public const FIELDSET_LAST_EXECUTION = 'lastExecution';

    private const REMAINING_FILES_CACHE_PREFIX = 'remaining_files';

    private const MAX_FILE_TIME = 300;

    /** @var string */
    private static $unique_id;

    // DB Table key
    public $sender_id;

    // DB fields
    public $name;
    public $description;
    public $params;
    public $period;
    public $offset;
    public $every;
    public $day; // Passage sur la branche de février pour backport plus tard
    public $active;
    public $max_archives;
    public $last_duration;
    public $last_size;
    public $multipart;
    public $last_datetime;
    public $last_status;
    public $last_http_code;
    public $last_error_datetime;

    // Form fields
    public $_params;
    public $_when;
    public $_active;
    public $_url;
    public $_file;
    public $_file_compressed;
    public $_files_list = [];
    public $_full_period;
    public $_last_age;

    // Distant properties
    public $_hour_plan;

    // Object references
    public $_ref_senders_source;

    private $_file_extension = 'html';

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec                  = parent::getSpec();
        $spec->table           = "view_sender";
        $spec->key             = "sender_id";
        $spec->uniques["name"] = ["name"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                        = parent::getProps();
        $props["name"]                = "str notNull fieldset|default";
        $props["description"]         = "text fieldset|default";
        $props["params"]              = "text notNull fieldset|default";
        $props["period"]              = "enum list|1|2|3|4|5|6|10|15|20|30|60 notNull default|30 fieldset|"
            . self::FIELDSET_CONFIGURATION;
        $props["every"]               = "enum list|1|2|3|4|6|8|12|24 notNull default|1 fieldset|"
            . self::FIELDSET_CONFIGURATION;
        $props["offset"]              = "num min|0 notNull default|0 fieldset|" . self::FIELDSET_CONFIGURATION;
        $props["day"]                 = "enum list|"
            . implode('|', range(1, 28))
            . " fieldset|" . self::FIELDSET_CONFIGURATION;
        $props["active"]              = "bool notNull default|0 fieldset|" . self::FIELDSET_CONFIGURATION;
        $props["max_archives"]        = "num min|1 notNull default|10 fieldset|" . self::FIELDSET_LAST_EXECUTION;
        $props["last_duration"]       = "float loggable|0 fieldset|" . self::FIELDSET_LAST_EXECUTION;
        $props["last_size"]           = "num loggable|0 fieldset|" . self::FIELDSET_LAST_EXECUTION;
        $props["multipart"]           = "bool notNull default|0 fieldset|" . self::FIELDSET_CONFIGURATION;
        $props["last_datetime"]       = "dateTime loggable|0 fieldset|" . self::FIELDSET_LAST_EXECUTION;
        $props["last_status"]         = "enum list|triggered|producted loggable|0 fieldset|"
            . self::FIELDSET_LAST_EXECUTION;
        $props["last_http_code"]      = "num loggable|0 fieldset|" . self::FIELDSET_LAST_EXECUTION;
        $props["last_error_datetime"] = "dateTime loggable|0 fieldset|" . self::FIELDSET_LAST_EXECUTION;

        $props["_url"]         = "str";
        $props["_file"]        = "str";
        $props["_full_period"] = "num min|0";
        $props["_last_age"]    = "num";

        return $props;
    }

    /**
     * @inheritdoc
     */
    function check()
    {
        $this->completeField("every", "period");
        if ($this->every != "1" && $this->period != self::MINUTES) {
            return "$this->_class-failed-every-period-constraint";
        }

        return parent::check();
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view        = $this->name;
        $this->_when        = "$this->period mn + $this->offset";
        $this->_full_period = intval($this->every) > 1 ? self::MINUTES * $this->every : $this->period;

        if ($this->day && $this->day != CMbDT::format(CMbDT::date(), "%d")) {
            $day_count = CMbDT::daysRelative($this->last_datetime, CMbDT::date("+1 MONTH", $this->last_datetime));

            $this->_full_period += (self::MINUTES * self::HOURS * $day_count);
        }

        // Parse parameters
        $params = strtr($this->params, ["\r\n" => "&", "\n" => "&", " " => ""]);
        parse_str($params, $params_array);

        // Add brackets for arrays
        $this->_params = [];
        foreach ($params_array as $_key => $_param) {
            $this->_params[$_key] = $_param;
        }
    }

    /**
     * Check if a view is active at this time
     *
     * @param int $minute Minute
     * @param int $hour   Hour
     * @param int $day    Day of the month between 1 and 28
     *
     * @return bool
     */
    public function getActive(int $minute, ?int $hour = null, ?int $day = null)
    {
        $hour = intval($hour);
        $day  = intval($day);

        $minute_active = (($minute % $this->period) == $this->offset);
        $hour_active   = ($hour % $this->every == 0);

        $day_active = true;
        if ($this->day) {
            $day_active = (intval($this->day) == $day);
        }

        return $this->_active = $minute_active && $hour_active && $day_active;
    }

    /**
     * Build the view sender hour plan based on last duration
     *
     * @param string $plan_mode One of production|sending
     *
     * @return array
     */
    function makeHourPlan($plan_mode = "production")
    {
        $period = intval($this->period);
        $offset = intval($this->offset);
        $every  = intval($this->every);

        // Plan mode on production or sending duration
        $duration = 0;
        if ($plan_mode == "production") {
            $duration = $this->last_duration;
        }
        if ($plan_mode == "sending") {
            $senders_source = $this->loadRefSendersSource();
            $duration       = array_sum(CMbArray::pluck($senders_source, "last_duration"));
        }

        // Microplan on several minutes in case duration is more than 60s
        $microplan = [];
        while ($duration > 0) {
            $microplan[] = min($duration, self::MINUTES);
            $duration    -= self::MINUTES;
        }

        // Hour plan
        $hour_plan = array_fill(0, self::MINUTES, 0);
        foreach (range(0, 59) as $_min) {
            if ($_min % $period == $offset) {
                foreach ($microplan as $_offset => $_duration) {
                    $hour_plan[($_min + $_offset) % self::MINUTES] += $_duration / self::MINUTES / $every;
                }
            }
        }

        return $this->_hour_plan = $hour_plan;
    }

    /**
     * Get sender sources
     *
     * @return CSourceToViewSender[]
     */
    function loadRefSendersSource()
    {
        /** @var CSourceToViewSender[] $senders_source */
        $senders_source = $this->loadBackRefs("sources_link");
        foreach ($senders_source as $_sender_source) {
            $_sender_source->loadRefSenderSource()->loadRefSource();
        }

        return $this->_ref_senders_source = $senders_source;
    }

    /**
     * Make the URL from the user and the params
     *
     * @param CUser $user The user
     *
     * @return string
     */
    private function makeUrl()
    {
        // Todo: External URL?
        $base = CAppUI::conf("base_url");

        if ($this->multipart) {
            $this->_params["suppressHeaders"] = "1";
            $this->_params["multipart"]       = "1";
        } elseif (strpos($this->params, 'raw=') !== false) {
            // Case of raw export
            $this->_file_extension = 'csv';
        } else {
            $this->_params["dialog"] = "1";
            $this->_params["_aio"]   = "1";
        }

        $query = CMbString::toQuery($this->_params);
        $url   = "$base/?$query";

        return $this->_url = $url;
    }

    /**
     * Set the _last_age field
     *
     * @return void
     */
    function getLastAge()
    {
        $this->_last_age = CMbDT::minutesRelative($this->last_datetime, CMbDT::dateTime());
    }

    /**
     * Retrieves the file from the URL, via HTTP GET
     *
     * @return string
     * @throws CMbException
     */
    public function makeFile()
    {
        $file = tempnam("", "view");

        if ($file !== false) {
            $this->addTempFile($file);
        }

        CApp::$chrono->stop();
        $chrono = new Chronometer();
        $chrono->start();

        $httpclient = new CHTTPClient($this->_url);
        $httpclient->setCookie(session_name() . "=" . session_id());

        // On ne vérifie pas le certificat, car c'est potentiellement sur localhost
        $httpclient->setOption(CURLOPT_SSL_VERIFYHOST, false);
        $httpclient->setOption(CURLOPT_SSL_VERIFYPEER, false);

        // On récupère et écrit les données dans le fichier temporaire
        $contents = $httpclient->get();

        $chrono->stop();
        CApp::$chrono->start();

        $this->last_http_code = (isset($httpclient->last_information['http_code'])) ? $httpclient->last_information['http_code'] : null;
        $this->last_duration  = $chrono->total;

        if ($this->last_http_code >= 400 || file_put_contents($file, $contents) === false) {
            $this->clearTempFiles();

            // Set duration, error datetime, content size and status

            $this->last_error_datetime = CMbDT::dateTime();
            $this->last_size           = strlen($contents);
            $this->last_status         = 'triggered';
            $this->store();

            if ($this->last_http_code >= 400) {
                trigger_error(
                    CAppUI::tr('CViewSender-error-http return code', $this->name, $this->last_http_code),
                    E_USER_WARNING
                );
            } else {
                trigger_error(CAppUI::tr("CViewSender-ko-file_put_contents"), E_USER_WARNING);
            }

            return null;
        }

        $this->last_size     = filesize($file);
        $this->last_datetime = CMbDT::dateTime();
        $this->last_status   = ($httpclient->last_information['size_download'] > 0) ? 'producted' : 'triggered';
        $this->store();

        $this->_files_list = [];
        if ($this->multipart) {
            /*
             * Fichiers:
             *   $this->name/[datetime]/XXX.html
             *   $this->name/[datetime]/YYY.html
             *
             * Archive:
             *   $this->name/archive/[datetime]/XXX.html
             *   $this->name/archive/[datetime]/YYY.html
             *
             */
            $parts = json_decode($contents, true);

            foreach ($parts as $_part) {
                $_file = tempnam("", "view");

                if ($_file !== false) {
                    $this->addTempFile($_file);
                }

                if (file_put_contents($_file, base64_decode($_part["content"])) === false) {
                    $chrono->stop();
                    CApp::$chrono->start();

                    $this->clearTempFiles();

                    throw new CMbException("CViewSender-ko-file_put_contents");
                }

                $this->_files_list[] = [
                    "name_raw"  => $_file,
                    "name_zip"  => null,
                    "title"     => base64_decode($_part["title"]),
                    "extension" => $_part["extension"],
                ];
            }
        } else {
            $this->_files_list[] = [
                "name_raw"  => $file,
                "name_zip"  => null,
                "title"     => null,
                "extension" => $this->_file_extension,
            ];
        }

        return $this->_file = $file;
    }

    /**
     * Send the file
     *
     * @return void
     */
    private function sendFile()
    {
        $senders_sources = $this->loadRefSendersSource();
        foreach ($senders_sources as $_source_to_view_sender) {
            $_source_to_view_sender->resetValues();
        }

        // On transmet aux sources le fichier
        foreach ($senders_sources as $_source_to_view_sender) {
            $_source_to_view_sender->last_datetime = CMbDT::dateTime();
            $_source_to_view_sender->last_status   = "triggered";
            $_source_to_view_sender->last_duration = null;
            $_source_to_view_sender->last_size     = null;

            // Store the object to have stats if a fail occure
            $_source_to_view_sender->store();

            $chrono = new Chronometer();
            $chrono->start();

            $_sender_source = $_source_to_view_sender->_ref_sender_source;
            $source         = $_sender_source->_ref_source;

            if ($source->_id && $source->active && $_sender_source->actif) {
                try {
                    if ($source->role != CAppUI::conf("instance_role")) {
                        throw new CMbException(
                            "CViewSenderSource-msg-View sender source incompatible %s with the instance role %s",
                            $source->role,
                            CAppUI::conf("instance_role")
                        );
                    }

                    switch (get_class($source)) {
                        case CSourceFTP::class:
                        case CSourceSFTP::class:
                        case CSourceFileSystem::class:
                            $this->send($_source_to_view_sender);
                            break;

                        default:
                            throw new CMbException(
                                "CViewSenderSource-msg-View sender source incompatible %s with the instance role %s"
                            );
                    }
                } catch (Throwable $e) {
                    // Seulement en warning pour continuer la boucle sur les sources suivantes
                    $e->stepAjax(UI_MSG_WARNING);
                }
            }

            $chrono->stop();
            $_source_to_view_sender->last_duration = $chrono->total;
            $_source_to_view_sender->store();
        }

        $this->clearTempFiles();
    }

    /**
     * Send the file via FTP
     *
     * @param CSourceToViewSender $source_to_view_sender Source to sender view
     *
     * @return void
     * @throws CMbException
     */
    function send(CSourceToViewSender $source_to_view_sender)
    {
        $sender_source = $source_to_view_sender->_ref_sender_source;
        /** @var CSourceSFTP|CSourceFTP|CSourceFileSystem $source */
        $source = $sender_source->_ref_source;

        $source->getClient()->init($source);
        if (!$source->getClient()->isAuthentificate()) {
            return;
        }

        foreach ($this->_files_list as $_i => &$_file) {
            $basename = $this->name;
            if ($this->multipart) {
                $destination_basename = $source->fileprefix . $basename . "/" . $this->getDateTime(
                    ) . "/" . $_file["title"];
            } else {
                $destination_basename = $source->fileprefix . $basename;
            }

            $compressed = $sender_source->archive;
            $extension  = "." . ($source->fileextension ? $source->fileextension : $_file["extension"]);

            $file_name = $destination_basename . ($compressed ? ".zip" : $extension);

            // Création de l'archive si nécessaire
            if ($compressed && !file_exists($_file["name_zip"])) {
                $this->addTempFile($_file['name_raw'] . '.zip');

                $this->_files_list[$_i]["name_zip"] = $_file["name_raw"] . ".zip";
                $_file["name_zip"]                  = $this->_files_list[$_i]["name_zip"];
                $this->_file_compressed             = $this->_file . ".zip";

                $zip = self::isZipEnabled();

                // If no password use ZipArchive to create the zip
                // If there is a password use the command zip because zipArchive don't handle passwords
                if (!$sender_source->password || !$zip) {
                    $archive = new ZipArchive();
                    $archive->open($this->_files_list[$_i]["name_zip"], ZipArchive::CREATE);
                    $archive->addFile($_file["name_raw"], $destination_basename . $extension);
                    $archive->close();
                } else {
                    $file_path     = $_file['name_raw'];
                    $file_path_zip = $this->_files_list[$_i]['name_zip'];

                    // Rename the temporary file to its real name
                    // Do not use zipnote to rename the files in the zip because of a bug in zipnote 3.0 (last version)
                    $destination_basename = basename($destination_basename);
                    $new_name             = dirname($file_path) . "/{$destination_basename}{$extension}";
                    rename($file_path, $new_name);
                    // Replace the name in the array for the clearTempFiles() method
                    $_file['name_raw'] = $new_name;

                    // zip command
                    // -j to junk path (only store the file and don't create the whole path in the zip)
                    // -P password
                    $cmd = sprintf(
                        'zip -j -P %s %s %s',
                        escapeshellarg($sender_source->password),
                        escapeshellarg($file_path_zip),
                        escapeshellarg($_file['name_raw'])
                    );
                    exec($cmd);
                }
            }

            // Envoi du fichier
            $file = $compressed ? $_file["name_zip"] : $_file["name_raw"];

            $source->setData(file_get_contents($file));
            $source->getClient()->send($file_name);
            $source_to_view_sender->last_status = "uploaded";

            // Vérification de la taille du fichier uploadé
            $source_to_view_sender->last_size = $source->getClient()->getSize($file_name);
            if ($source_to_view_sender->last_size == filesize($file)) {
                $source_to_view_sender->last_status = "checked";
            }

            // Enregistrement
            if ($source instanceof CSourceFTP) {
                $source->counter++;
            }
            $source->store();
        }

        // TODO: en mode multipart, gérer la rotation
        if (!$this->multipart) {
            $source_to_view_sender->last_count = $this->archiveFile($source, $basename, $compressed);
        }
    }

    /**
     * Clears temporaray files
     *
     * @return void
     */
    function clearTempFiles()
    {
        if (file_exists($this->_file)) {
            unlink($this->_file);
        }

        foreach ($this->_files_list as $_file) {
            if ($_file["name_raw"] && file_exists($_file["name_raw"])) {
                unlink($_file["name_raw"]);
            }

            if ($_file["name_zip"] && file_exists($_file["name_zip"])) {
                unlink($_file["name_zip"]);
            }
        }
    }

    private function initUniqueId(): void
    {
        // Init only one per query
        if (!self::$unique_id) {
            self::$unique_id = uniqid();
        }
    }

    public function setForceUniqueId(string $unique_id): void
    {
        self::$unique_id = $unique_id;
    }

    public static function clearRemainingFiles(): void
    {
        $instance = new self();
        $cache    = $instance->getRemainingFilesCache();

        if ($files = $cache->get()) {
            $remainings = [];
            foreach ($files as $file_name => [$file_date, $unique]) {
                if ($instance->canDeleteFile($file_date, $unique)) {
                    if (file_exists($file_name) && !CMbPath::remove($file_name)) {
                        $remainings[$file_name] = [$file_date, $unique];
                    }
                } else {
                    $remainings[$file_name] = [$file_date, $unique];
                }
            }

            if (!empty($remainings)) {
                $cache->put($remainings);

                return;
            }

            $cache->rem();
        }
    }

    public function canDeleteFile(string $file_date, string $unique): bool
    {
        return (
            $unique === self::$unique_id || CMbDT::durationSecond($file_date, CMbDT::dateTime()) > self::MAX_FILE_TIME
        );
    }

    public function getUniqueId(): ?string
    {
        return self::$unique_id;
    }

    private function addTempFile(string $file_path): void
    {
        $this->initUniqueId();

        $cache = $this->getRemainingFilesCache();
        $files = $cache->get() ?: [];

        $files[$file_path] = [CMbDT::dateTime(), self::$unique_id];
        $cache->put($files);
    }

    public function getRemainingFilesCache(): Cache
    {
        return new Cache('CViewSender', self::REMAINING_FILES_CACHE_PREFIX, Cache::INNER_OUTER);
    }

    /**
     * Populate archive directory up to max_archives files
     *
     * @param CSourceFile $source     Exchange source
     * @param string      $basename   Base name for archive directory
     * @param boolean     $compressed True if file is an archive
     *
     * @return int Current archive count
     */
    function archiveFile(CExchangeSource $source, $basename, $compressed)
    {
        try {
            // Répertoire d'archivage
            $directory = $source->fileprefix . $basename;
            $datetime  = $this->getDateTime();
            $source->getClient()->createDirectory($directory);

            // Transmission de la copie
            $archive = "$directory/archive-$datetime" . ($compressed ? ".zip" : ".html");
            $file    = $compressed ? $this->_file_compressed : $this->_file;
            $source->setData(file_get_contents($file));
            $source->getClient()->send($archive);

            // Rotation des fichiers
            $files = $source->getClient()->getListFiles($directory);
            rsort($files);

            $rm_dir = $directory;
            if ($source instanceof CSourceFileSystem) {
                $full_path = $source->getFullPath($source->_path);
                $rm_dir    =
                    rtrim($full_path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . rtrim(
                        $rm_dir,
                        DIRECTORY_SEPARATOR
                    ) . DIRECTORY_SEPARATOR;
            }

            $list_files = array_slice($files, $this->max_archives);

            $source->_destination_file = $rm_dir;
            $client                    = $source->getClient();
            foreach ($list_files as $_file) {
                $client->delFile(basename($_file), $rm_dir);
            }
        } catch (CMbException $e) {
            $e->stepAjax();
        }

        return count($source->getClient()->getListFilesDetails($directory));
    }

    /**
     * Gets current date time
     *
     * @return string
     */
    function getDateTime()
    {
        static $datetime = null;

        if ($datetime === null) {
            $datetime = CMbDT::format(null, "%Y-%m-%d_%H-%M-%S");
        }

        return $datetime;
    }

    public function prepareAndSendFile(): bool
    {
        $this->makeUrl();

        if (($filepath = $this->makeFile()) && filesize($filepath) > 0) {
            $this->sendFile();

            return true;
        }

        return false;
    }

    public function getUniqueName(string $name): string
    {
        $ds = $this->getDS();
        if ($this->countList(['name' => $ds->prepare('= ?', $name)])) {
            $indice = 0;

            $base_name = $name;
            do {
                $indice++;
                $suffixe = sprintf(" %02s", $indice);
                $name    = $base_name . $suffixe;
            } while ($this->countList(['name' => $ds->prepare('= ?', $name)]));
        }

        return $name;
    }

    public static function isZipEnabled(): bool
    {
        exec('zip --version', $zip);

        return (bool)$zip;
    }

    /**
     * @return Collection|array
     * @throws ApiException
     */
    public function getResourceLastExecution()
    {
        if (!$sources = $this->loadRefSendersSource()) {
            return [];
        }

        foreach ($sources as $_source) {
            $_source->loadRefSender();
        }

        return new Collection($sources);
    }
}
