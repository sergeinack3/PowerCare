<?php

/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport;

use Ox\Core\CClassMap;
use Ox\Core\CMbPath;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Import\GenericImport\Exception\FileAccessException;
use ZipArchive;

/**
 * Description
 */
class OxImportPivot
{
    private const TEMP_DIR = 'tmp/import_pivot';

    private const IMPORT_FORMATS_FILENAME = 'import_formats.zip';

    private const FILE_FORMAT_SUFFIX = '_format';

    private const FILE_EXTENSION = '.csv';

    private const FILE_INFOS_HEADER = [
        'Nom du champ',
        'Taille du champ',
        'Type de données',
        'Description',
        'Obligatoire',
    ];

    /** @var string */
    private $tmp_path;

    /** @var AbstractOxPivotImportableObject */
    private $pivot;

    public function __construct(bool $init_dir = true)
    {
        $this->tmp_path = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . self::TEMP_DIR;

        if ($init_dir && !$this->initTempDir()) {
            throw FileAccessException::UnableToInitDirException($this->tmp_path);
        }
    }

    public function getImportableClasses(bool $instanciate = true): array
    {
        return (CClassMap::getInstance())->getClassChildren(GenericPivotObject::class, $instanciate, true);
    }

    public function buildImportFile(AbstractOxPivotImportableObject $pivot): string
    {
        $this->pivot = $pivot;

        return $this->buildCsv();
    }

    public function buildMultipleImportFiles(array $import_classes = []): string
    {
        if (!$import_classes) {
            $import_classes = $this->getImportableClasses();
        }

        $zip_path = $this->tmp_path . DIRECTORY_SEPARATOR . self::IMPORT_FORMATS_FILENAME;

        $zip = new ZipArchive();
        $zip->open($zip_path, ZipArchive::CREATE);

        $paths = [];
        /** @var AbstractOxPivotImportableObject $instance */
        foreach ($import_classes as $instance) {
            $paths[] = $path = $this->buildImportFile($instance);
            $zip->addFile($path, $instance->getFileName() . self::FILE_EXTENSION);

            $paths[] = $info_path = $this->buildImportFileInfos($instance);
            $zip->addFile($info_path, $instance->getFileName() . self::FILE_FORMAT_SUFFIX . self::FILE_EXTENSION);
        }

        $zip->close();

        foreach ($paths as $_path) {
            CMbPath::remove($_path);
        }

        return $zip_path;
    }

    public function buildImportFileInfos(AbstractOxPivotImportableObject $object): string
    {
        $file_path = $this->tmp_path . DIRECTORY_SEPARATOR . $object->getFileName() . self::FILE_FORMAT_SUFFIX
            . self::FILE_EXTENSION;
        $csv       = $this->createCsvFile($file_path);
        $this->writeInfos($object, $csv);
        $csv->close();

        return $file_path;
    }

    private function buildCsv(): string
    {
        $file_path = $this->tmp_path . DIRECTORY_SEPARATOR . $this->pivot->getFileName() . self::FILE_EXTENSION;

        $csv = $this->createCsvFile($file_path);
        $csv->writeLine($this->pivot->getFields());
        $csv->close();

        return $file_path;
    }

    private function writeInfos(AbstractOxPivotImportableObject $object, CCSVFile $csv): void
    {
        $csv->writeLine(self::FILE_INFOS_HEADER);

        /**
         * @var string           $field
         * @var FieldDescription $infos
         */
        foreach ($object->getImportableFields() as $field => $infos) {
            $csv->writeLine(
                [
                    $field,
                    $infos->getSize(),
                    $infos->getType(),
                    $infos->getDescription(),
                    $infos->isMandatory() ? 'Oui' : 'Non',
                ]
            );
        }

        if ($add_infos = $object->getAdditionnalInfos()) {
            $csv->writeLine([]);
            foreach ($add_infos as $_info) {
                $csv->writeLine([$_info]);
            }
        }
    }

    private function initTempDir(): bool
    {
        if (is_dir($this->tmp_path)) {
            return CMbPath::emptyDir($this->tmp_path);
        }

        return CMbPath::forceDir($this->tmp_path);
    }

    private function createCsvFile(string $file_path): CCSVFile
    {
        if (($fp = fopen($file_path, 'w+')) === false) {
            throw FileAccessException::UnableToOpenFileForWriting($file_path);
        }

        return new CCSVFile($fp);
    }
}
