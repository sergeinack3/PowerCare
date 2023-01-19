<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Search;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Ox\Core\CMbString;
use Ox\Core\FileUtil\CCSVFile;
use Traversable;

class SearchResults implements IteratorAggregate, Countable
{
    /** @var SearchResult[] */
    private $results = [];

    /** @var int */
    private $time;

    /** @var int */
    private $total;

    /** @var CSearchThesaurusEntry */
    private $bookmark;

    public function __construct(int $time, int $total)
    {
        $this->time  = $time;
        $this->total = $total;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function setResults(array $results): void
    {
        $this->results = $results;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getBookmark(): CSearchThesaurusEntry
    {
        return $this->bookmark;
    }

    public function setBookmark(CSearchThesaurusEntry $bookmark): void
    {
        $this->bookmark = $bookmark;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->results);
    }

    public function count(): int
    {
        return count($this->results);
    }

    /**
     * Prepare the CSV
     *
     * @param resource $fp         File manager
     * @param bool     $obfuscated Anonymise data
     *
     * @return CCSVFile
     */
    public function asCsv($fp, bool $obfuscated): CCSVFile
    {
        $csv_writer   = new CCSVFile($fp); // Use PROFILE_EXCEL as default
        $column_names = [
            "Id",
            "Date",
            "Type",
            "Titre",
            "Patient",
            "Author",
            "Document",
        ];
        $csv_writer->setColumnNames($column_names);
        $csv_writer->writeLine($column_names);

        foreach ($this->results as $_result) {
            $line = [
                'Id'       => $_result,
                'Date'     => $_result,
                'Type'     => $_result,
                'Patient'  => $_result->getPatient(),
                'Author'   => $_result->getAuthor(),
                'Document' => CMbString::purifyHTML($_result->getBody($obfuscated)),
            ];
            $csv_writer->writeLine($line);
        }

        return $csv_writer;
    }
}
