<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Export;

use DirectoryIterator;
use Exception;
use FilesystemIterator;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CMbPath;
use Ox\Core\CStoredObject;
use Ox\Core\Import\CMbObjectExport;
use Ox\Core\Logger\LoggerLevels;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\Export\Description\CCSVPatientExportDescriptionWriter;
use Ox\Mediboard\Patients\Export\Description\CXMLPatientExportInfosGenerator;
use PharData;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use ZipArchive;

/**
 * Class used to generate a XML export for a set of CPatients.
 */
class CXMLPatientExport
{
    public const OPTION_START                  = 'start';
    public const OPTION_STEP                   = 'step';
    public const OPTION_PRATICIENS             = 'praticien_id';
    public const OPTION_PATIENT                = 'patient_id';
    public const OPTION_DATE_MIN               = 'date_min';
    public const OPTION_DATE_MAX               = 'date_max';
    public const OPTION_IGNORE_CONST_WITH_TAGS = 'ignore_consult_tag';
    public const OPTION_ARCHIVE_TYPE           = 'archive_type';

    public const ARCHIVE_TYPE_NONE = 'none';
    public const ARCHIVE_TYPE_TAR  = 'tar';
    public const ARCHIVE_TYPE_ZIP  = 'zip';

    public const ARCHIVE_TYPES = [self::ARCHIVE_TYPE_NONE, self::ARCHIVE_TYPE_TAR, self::ARCHIVE_TYPE_ZIP];

    public const OPTIONS = [
        self::OPTION_START                  => 0,
        self::OPTION_STEP                   => 10,
        self::OPTION_PRATICIENS             => null,
        self::OPTION_PATIENT                => null,
        self::OPTION_DATE_MIN               => null,
        self::OPTION_DATE_MAX               => null,
        self::OPTION_IGNORE_CONST_WITH_TAGS => false,
        self::OPTION_ARCHIVE_TYPE           => self::ARCHIVE_TYPE_NONE,
    ];

    public const PATIENT_ORDER = 'patient_id ASC';

    public const CLASSES_DESCRIPTION_FILE_NAME = 'classes_description.csv';
    public const EXPORT_DESCRIPTION_FILE_NAME  = 'export_description.md';

    /** @var string */
    private string $directory;

    /** @var array */
    private array $options;

    /** @var int */
    private int $total = 0;

    /** @var array */
    private array $fw_tree = [];

    /** @var array */
    private array $back_tree = [];

    /** @var string */
    private $current_dir;

    public function __construct(string $directory, array $options = [])
    {
        $this->directory = $directory;
        $this->options   = array_merge(self::OPTIONS, $options);
    }

    /**
     * Do the export using the options passed to the constructor.
     * Log errors using CApp::log.
     *
     * @throws Exception
     */
    public function export(): int
    {
        try {
            $patients = $this->getPatientsToExport();
        } catch (CMbModelNotFoundException $e) {
            CApp::log($e->getMessage(), null, LoggerLevels::LEVEL_WARNING);

            $patients = [];
        }

        $this->back_tree = $this->buildBackRefsTree();
        $this->fw_tree   = $this->buildFwRefsTree();

        foreach ($patients as $patient) {
            try {
                $this->exportPatient($patient);
            } catch (CMbException $e) {
                CApp::log($e->getMessage(), null, LoggerLevels::LEVEL_WARNING);
            }
        }

        // Build description file if not exists
        $this->writeFieldsDescriptionFile();

        $this->writeExportDescriptionFile();

        return count($patients);
    }

    /**
     * Write the export description file to the export directory if it's not already present.
     *
     * @throws Exception
     */
    protected function writeFieldsDescriptionFile(): void
    {
        $file_name = $this->directory . DIRECTORY_SEPARATOR . self::CLASSES_DESCRIPTION_FILE_NAME;

        if (!file_exists($file_name)) {
            try {
                $descriptions = (new CXMLPatientExportInfosGenerator($this->fw_tree, $this->back_tree))->generateInfos(
                    new CPatient()
                );
                (new CCSVPatientExportDescriptionWriter($file_name))->writeDescriptions($descriptions);
            } catch (CMbException $e) {
                CApp::log($e->getMessage(), null, LoggerLevels::LEVEL_WARNING);
            }
        }
    }

    protected function writeExportDescriptionFile(): void
    {
        $file_name = $this->directory . DIRECTORY_SEPARATOR . self::EXPORT_DESCRIPTION_FILE_NAME;
        if (!file_exists($file_name)) {
            copy(dirname(__DIR__) . '/../resources/Export/export_format.md', $file_name);
        }
    }

    /**
     * Get the patients to export depending on the context.
     * If a patient_id is given in the options the export will be only for this patient.
     * If no patient_id is given, the export will use the start and step option to get the patients to export.
     *
     * @return CPatient[]
     *
     * @throws CMbModelNotFoundException
     */
    private function getPatientsToExport(): array
    {
        if ($patient_id = $this->options[self::OPTION_PATIENT]) {
            $patient     = CPatient::findOrFail($patient_id);
            $this->total = 1;

            return [$patient];
        }

        if (CAppUI::isGroup() || CAppUI::isCabinet()) {
            [$patients, $this->total] = CMbObjectExport::getPatientToExportFunction(
                $this->options[self::OPTION_PRATICIENS],
                $this->options[self::OPTION_START],
                $this->options[self::OPTION_STEP]
            );
        } else {
            [$patients, $this->total] = CMbObjectExport::getPatientsToExport(
                $this->options[self::OPTION_PRATICIENS],
                $this->options[self::OPTION_DATE_MIN],
                $this->options[self::OPTION_DATE_MAX],
                $this->options[self::OPTION_START],
                $this->options[self::OPTION_STEP],
                self::PATIENT_ORDER
            );
        }


        return $patients;
    }

    /**
     * Build a tree of backrefs that must be exported for each object.
     */
    private function buildBackRefsTree(): array
    {
        $back_tree = CMbObjectExport::DEFAULT_BACKREFS_TREE;

        if (CModule::getInstalled('notifications')) {
            $back_tree = array_merge($back_tree, CMbObjectExport::NOTIF_BACK_TREE);
        }

        return $back_tree;
    }

    /**
     * Build a tree of forward refs that must be exported for each object.
     */
    private function buildFwRefsTree(): array
    {
        $fw_tree = CMbObjectExport::DEFAULT_FWREFS_TREE;

        if (CModule::getInstalled('notifications')) {
            $fw_tree = array_merge($fw_tree, CMbObjectExport::NOTIF_FW_TREE);
        }

        return $fw_tree;
    }

    /**
     * Export a single patient to an XML file with all it's fields, fw_refs and back_refs.
     *
     * @throws CMbException
     */
    private function exportPatient(CPatient $patient): void
    {
        $this->current_dir = $this->directory . DIRECTORY_SEPARATOR . $patient->_guid;

        if (!$this->createDir()) {
            throw new CMbException('CXMLPatientExport-Error-Unable to create directory', $this->current_dir);
        }

        $export = $this->buildObjectExporter($patient);

        $xml = $export->toDOM()->saveXML();
        if (!$this->writeXmlFile($xml)) {
            throw new CMbException('CXMLPatientExport-Error-Unable to write file', $this->current_dir . '/export.xml');
        }

        if ($this->options[self::OPTION_ARCHIVE_TYPE] !== self::ARCHIVE_TYPE_NONE) {
            $this->createArchive($this->current_dir);
        }
    }

    /**
     * @throws CMbException
     */
    private function buildObjectExporter(CPatient $patient): CMbObjectExport
    {
        $export = new CMbObjectExport($patient, $this->back_tree);
        $export->setForwardRefsTree($this->fw_tree);
        $export->setFilterCallback($this->getFilterCallback());
        $export->setObjectCallback($this->getObjectCallback());

        return $export;
    }

    protected function createDir(): bool
    {
        return CMbPath::forceDir($this->current_dir);
    }

    protected function writeXmlFile(string $xml): bool
    {
        return (bool)file_put_contents($this->current_dir . '/export.xml', $xml);
    }

    /**
     * Filter function which will be used to tell if an object must be exported or not.
     */
    private function getFilterCallback(): callable
    {
        return function (CStoredObject $object) {
            return CMbObjectExport::exportFilterCallback(
                $object,
                $this->options[self::OPTION_DATE_MIN],
                $this->options[self::OPTION_DATE_MAX],
                $this->options[self::OPTION_PRATICIENS],
                [],
                $this->options[self::OPTION_IGNORE_CONST_WITH_TAGS]
            );
        };
    }

    /**
     * Callback function that allow the modification of XML or actions after an object has been converted to
     * a DOMElement.
     */
    private function getObjectCallback(): callable
    {
        return function (CStoredObject $object) {
            CMbObjectExport::exportCallBack(
                $object,
                $this->current_dir,
                true,
                false,
                true
            );
        };
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    private function createArchive(string $current_dir): void
    {
        switch ($this->options[self::OPTION_ARCHIVE_TYPE]) {
            case self::ARCHIVE_TYPE_TAR:
                $this->createTarArchive($current_dir);
                break;
            case self::ARCHIVE_TYPE_ZIP:
                $this->createZipArchive($current_dir);
                break;
            default:
                throw new CMbException(
                    'CXMLPatientExport-Error-Type is not valid use one of',
                    $this->options[self::OPTION_ARCHIVE_TYPE],
                    implode(', ', self::ARCHIVE_TYPES)
                );
        }

        CMbPath::remove($current_dir);
    }

    public static function checkDirectory(string $directory): array
    {
        if (!is_readable($directory) || !is_dir($directory)) {
            CAppUI::stepAjax("CXMLPatientExport-Error-Directory is not readable", UI_MSG_ERROR);
        }

        $iterator   = new DirectoryIterator($directory);
        $count_dirs = $count_valid_dirs = $count_files = 0;

        /** @var DirectoryIterator $file_info */
        foreach ($iterator as $file_info) {
            if ($file_info->isDot()) {
                continue;
            }

            if ($file_info->isFile()) {
                // Special check for tar and zip files
                if (
                    in_array($file_info->getExtension(), [self::ARCHIVE_TYPE_TAR, self::ARCHIVE_TYPE_ZIP])
                    && strpos($file_info->getFilename(), 'CPatient-') === 0
                ) {
                    $count_dirs++;
                    $count_valid_dirs++;
                    continue;
                }

                $count_files++;
                continue;
            }

            if ($file_info->isDir()) {
                $count_dirs++;

                if (strpos($file_info->getFilename(), "CPatient-") === 0) {
                    $count_valid_dirs++;
                }
            }
        }

        return [
            'count_dirs'       => $count_dirs,
            'count_valid_dirs' => $count_valid_dirs,
            'count_files'      => $count_files,
        ];
    }

    private function createTarArchive(string $current_dir): void
    {
        $tar_path = $current_dir . '.' . self::ARCHIVE_TYPE_TAR;
        if (file_exists($tar_path)) {
            CMbPath::remove($tar_path);
        }

        $phar = new PharData($tar_path);
        $phar->buildFromDirectory($current_dir);
    }

    private function createZipArchive(string $current_dir): void
    {
        $zip_name = $current_dir . '.' . self::ARCHIVE_TYPE_ZIP;

        $zip = new ZipArchive();
        if ($zip->open($zip_name, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new CMbException('CXMLPatientExport-Error-Cannot create zip file', $zip_name);
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($current_dir, FilesystemIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $directory */
        foreach ($iterator as $directory) {
            $file_path = str_replace($current_dir, '', $directory->getPathname());
            $zip->addFile($directory->getPathname(), $file_path);
        }

        if ($zip->close() !== true) {
            throw new CMbException('CXMLPatientExport-Error-Cannot write zip file', $zip_name);
        }
    }
}
