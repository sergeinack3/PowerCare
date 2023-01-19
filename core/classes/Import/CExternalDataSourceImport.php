<?php

/**
 * @package Ox\Core\Import
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 */

namespace Ox\Core\Import;

use Exception;
use Ox\Core\CMbPath;
use Ox\Core\CSQLDataSource;
use Throwable;

use const DIRECTORY_SEPARATOR;

/**
 * Description
 */
class CExternalDataSourceImport
{
    private const TEMP_DIR = 'tmp';

    /** @var array */
    protected $data_files = [];

    /** @var string */
    protected $source_name;

    /** @var string */
    protected $source_dir;

    /** @var string */
    protected $data_dir;

    /** @var string */
    protected $tmp_dir;

    /** @var CSQLDataSource */
    protected $ds;

    /** @var array */
    protected $messages = [];

    public function __construct(?string $name, ?string $path, ?array $data)
    {
        if (!empty($name)) {
            $this->setSourceName($name);
        }
        if (!empty($path)) {
            $this->setDataDirectory($path);
        }
        if (!empty($data)) {
            foreach ($data as $name => $item) {
                $this->addData($name, $item);
            }
        }
    }

    public function setSourceName(string $name): self
    {
        $this->source_name = $name;
        $this->tmp_dir     = self::TEMP_DIR . DIRECTORY_SEPARATOR . $this->source_name . DIRECTORY_SEPARATOR;

        return $this;
    }

    public function getSourceName(): string
    {
        return $this->source_name;
    }

    public function getSourceNameForSQL(): string
    {
        return strtolower(str_replace('-', '_', $this->getSourceName()));
    }

    public function getSource(): ?CSQLDatasource
    {
        return $this->ds;
    }

    public function setSource(): ?CSQLDataSource
    {
        return $this->ds = CSQLDataSource::get($this->source_name);
    }

    public function setDataDirectory(string $path): self
    {
        $this->data_dir   = $path;
        $this->source_dir = $this->getDirectory() . DIRECTORY_SEPARATOR . $this->data_dir . DIRECTORY_SEPARATOR;

        return $this;
    }

    public function addData(string $name, array $items): self
    {
        $this->data_files[$name] = $items;

        return $this;
    }

    /**
     * @throws Exception
     */
    public function importDatabase(?array $types = []): bool
    {
        $return = true;

        $this->setSource();

        if (empty($types) && !empty($this->data_files)) {
            $types = array_keys($this->data_files);
        }

        foreach ($this->data_files as $type => $files) {
            if (!in_array($type, $types)) {
                continue;
            }

            $zip_name = reset($files);

            if (!$this->extractData($zip_name)) {
                $return = false;
                continue;
            }

            array_shift($files);

            foreach ($files as $file) {
                $return = $return && $this->importFile($file);
            }
        }

        return $return;
    }

    protected function extractData(string $zip_name): bool
    {
        try {
            $count = CMbPath::extract($this->source_dir . $zip_name, $this->tmp_dir);
        } catch (Throwable $e) {
            $this->addMessage([$e->getMessage(), UI_MSG_WARNING, $zip_name]);
            return false;
        }

        $this->addMessage([$this->getClassName() . '-Info-Files extracted from', UI_MSG_OK, $count, $zip_name]);

        return true;
    }

    /**
     * @throws Exception
     */
    protected function importFile(string $file_name): bool
    {
        try {
            if (!$this->ds) {
                return true;
            }

            if (($line_count = $this->ds->queryDump($this->tmp_dir . $file_name)) === null) {
                throw new Exception($this->getClassName() . '-Error-File, an error occured in query');
            }

            $this->addMessage([$this->getClassName() . '-Info-File imported, query executed', UI_MSG_OK, $file_name, $line_count]);
        } catch (Exception $e) {
            $this->addMessage([$e->getMessage(), UI_MSG_WARNING, $this->ds->error()]);
            return false;
        }

        return true;
    }

    public function addMessage(array $message): array
    {
        return $this->messages[] = $message;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getTmpDir(): string
    {
        return $this->tmp_dir;
    }

    public function getDataDir(): string
    {
        return $this->getDirectory() . DIRECTORY_SEPARATOR . $this->data_dir;
    }

    protected function getDirectory(): string
    {
        $rc = new \ReflectionClass(get_class($this));
        return dirname($rc->getFileName());
    }

    protected function getClassName(): string
    {
        $rc = new \ReflectionClass(get_class($this));
        return $rc->getShortName();
    }
}
