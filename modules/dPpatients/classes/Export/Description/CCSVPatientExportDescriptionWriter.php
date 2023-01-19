<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Export\Description;

use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\FileUtil\CCSVFile;

/**
 * Write an array of CXMLPatientExportInstanceDescription to a file.
 */
class CCSVPatientExportDescriptionWriter
{
    public const HEADER_CLASS_TR         = 'class_tr';
    public const HEADER_SHORT_CLASS_NAME = 'short_class_name';
    public const HEADER_FIELD_NAME       = 'field_name';
    public const HEADER_FIELD_TR         = 'field_tr';
    public const HEADER_FIELD_DESC       = 'field_desc';
    public const HEADER_FIELD_PROP       = 'field_prop';
    public const HEADER_FIELD_SQL_PROP   = 'field_sql_prop';
    public const HEADER_FIELD_PATH       = 'field_path';

    public const HEADERS = [
        self::HEADER_CLASS_TR,
        self::HEADER_SHORT_CLASS_NAME,
        self::HEADER_FIELD_NAME,
        self::HEADER_FIELD_TR,
        self::HEADER_FIELD_DESC,
        self::HEADER_FIELD_PROP,
        self::HEADER_FIELD_SQL_PROP,
        self::HEADER_FIELD_PATH,
    ];

    /** @var CCSVFile */
    private $csv;

    /**
     * @throws CMbException
     */
    public function __construct(string $file_path)
    {
        $this->initCsvFile($file_path);
    }

    public function writeDescriptions(array $descriptions): void
    {
        $this->write($this->getHeaders());

        /** @var CXMLPatientExportInstanceDescription $description */
        foreach ($descriptions as $description) {
            $this->writeDataForDescription($description);
        }

        $this->close();
    }

    private function writeDataForDescription(CXMLPatientExportInstanceDescription $description): void
    {
        $root_path        = $description->getPath();
        $short_class_name = $description->getShortClassName();
        $class_tr         = CAppUI::tr($short_class_name);

        /** @var CXMLPatientExportFieldDescription $field */
        foreach ($description as $field) {
            $this->write($this->getDataForField($field, $class_tr, $short_class_name, $root_path));
        }
    }

    private function getDataForField(
        CXMLPatientExportFieldDescription $field,
        string $class_tr,
        string $short_class_name,
        string $root_path
    ): array {
        return [
            self::HEADER_CLASS_TR         => $class_tr,
            self::HEADER_SHORT_CLASS_NAME => $short_class_name,
            self::HEADER_FIELD_NAME       => $field->getName(),
            self::HEADER_FIELD_TR         => $field->getTr(),
            self::HEADER_FIELD_DESC       => $field->getDesc(),
            self::HEADER_FIELD_PROP       => $field->getProp(),
            self::HEADER_FIELD_SQL_PROP   => $field->getSqlProp(),
            self::HEADER_FIELD_PATH       => $root_path . $field->getPath(),
        ];
    }

    private function getHeaders(): array
    {
        $header_tr = [];

        foreach (self::HEADERS as $header) {
            $header_tr[] = CAppUI::tr('CCSVPatientExportDescriptionWriter.headers.' . $header);
        }

        return $header_tr;
    }

    /**
     * @throws CMbException
     */
    protected function initCsvFile(string $file_path): void
    {
        $fp = fopen($file_path, 'w+');

        if (!$fp) {
            throw new CMbException('CCSVPatientExportDescriptionWritter-Error-Cannot write file', $file_path);
        }

        $this->csv = new CCSVFile($fp);
        $this->csv->setColumnNames(self::HEADERS);
    }

    protected function write(array $data): void
    {
        $this->csv->writeLine($data);
    }

    protected function close(): void
    {
        $this->csv->close();
    }
}
