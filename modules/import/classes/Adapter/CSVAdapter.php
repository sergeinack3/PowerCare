<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\Framework\Adapter;

use Generator;
use Ox\Core\CMbString;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Import\Framework\Configuration\ConfigurableInterface;
use Ox\Import\Framework\Configuration\ConfigurationTrait;

/**
 * Description
 */
class CSVAdapter implements AdapterInterface, ConfigurableInterface
{
    use ConfigurationTrait;

    /** @var string */
    private $file_path;

    /** @var resource */
    private $fp;

    /** @var CCSVFile */
    private $csv_reader;

    /** @var string */
    private $id_prefix = '';

    /** @var string */
    private $escape_string = '\\';

    public function initFile(string $file_path): void
    {
        $this->file_path = $file_path;
        $this->fp = fopen($this->file_path, 'r');
        $this->csv_reader = new CCSVFile($this->fp);

        if ($this->configuration) {
            if ($delimiter = $this->configuration->offsetGet('field_delimiter')) {
                $this->csv_reader->delimiter = $delimiter;
            }

            if ($enclosure = $this->configuration->offsetGet('field_enclosure')) {
                $this->csv_reader->enclosure = $enclosure;
            }

            if ($escape = $this->configuration->offsetGet('escape_string')) {
                $this->csv_reader->escape_string = $escape;
            }
        }

        $columns_names = $this->readAndSanitizeLine(false);
        $columns_names = array_map([CMbString::class, 'lower'], $columns_names);
        $this->csv_reader->setColumnNames($columns_names);
    }

    private function skipLines(int $offset = 0, array $conditions = []): void
    {
        if ($offset > 0) {
            if ($conditions) {
                $i = 0;
                while ($line = $this->readAndSanitizeLine()) {
                    if (!$this->checkConditions($conditions, $line)) {
                        continue;
                    }

                    $i++;
                    if ($i >= $offset) {
                        break;
                    }
                }
            } else {
                for ($i = 0; $i < $offset; $i++) {
                    $this->csv_reader->readLine();
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function retrieve(
        string $collection,
        string $identifier,
        $id,
        array $conditions = [],
        array $select = []
    ): ?array {
        $this->initFile($collection);

        if ($this->id_prefix) {
            $id = str_replace($this->id_prefix, '', $id);
        }

        while ($line = $this->readAndSanitizeLine()) {
            if (isset($line[$identifier]) && $line[$identifier] == $id) {
                return $line;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function get(
        string $collection,
        int $count = 1,
        int $offset = 0,
        ?array $conditions = [],
        ?array $select = []
    ): ?Generator {
        $this->initFile($collection);
        $this->skipLines($offset, $conditions);
        $i = 0;
        while ($line = $this->readAndSanitizeLine()) {
            if ($conditions && !$this->checkConditions($conditions, $line)) {
                continue;
            }

            if ($i++ >= $count) {
                break;
            }

            yield $line;
        }
    }

    private function checkConditions(array $conditions, array $line): bool
    {
        $conditions_ok = true;
        foreach ($conditions as $_column_name => $_value) {
            if (!isset($line[$_column_name]) || $line[$_column_name] != $_value) {
                $conditions_ok = false;
                break;
            }
        }

        return $conditions_ok;
    }

    public function count(string $collection, ?array $conditions = []): int
    {
        $this->initFile($collection);

        $i = 0;
        while ($line = $this->readAndSanitizeLine()) {
            if ($conditions && !$this->checkConditions($conditions, $line)) {
                continue;
            }

            $i++;
        }

        return $i;
    }

    private function readAndSanitizeLine(bool $assoc = true): ?array
    {
        $line = $this->csv_reader->readLine($assoc, true);
        if ($line) {
            $line = $this->sanitizeLine($line);
        }

        return ($line) ?: null;
    }

    private function sanitizeLine(array $line): array
    {
        $line = array_map('trim', $line);

        $sanitize = ($this->configuration) ? $this->configuration['sanitize_functions'] : null;
        if ($sanitize) {
            foreach ($sanitize as $_func) {
                $line = array_map($_func, $line);
            }
        }

        return $line;
    }

    public function setPrefix(string $prefix): void
    {
        $this->id_prefix = $prefix;
    }
}
