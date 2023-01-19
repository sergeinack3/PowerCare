<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients\Import\Xml;

use DirectoryIterator;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CMbPath;
use Ox\Core\CStoredObject;
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Patients\CPatientXMLImport;
use Ox\Mediboard\Patients\Export\CXMLPatientExport;
use PharData;

class PatientImportManager
{
    public const OPTION_FILES_DIRECTORY           = 'files_directory';
    public const OPTION_UPDATE_DATA               = 'update_data';
    public const OPTION_PATIENT_ID                = 'patient_id';
    public const OPTION_LINK_FILES_TO_OP          = 'link_files_to_op';
    public const OPTION_CORRECT_FILES             = 'correct_files';
    public const OPTION_HANDLERS                  = 'handlers';
    public const OPTION_PATIENTS_ONLY             = 'patients_only';
    public const OPTION_DATE_MIN                  = 'date_min';
    public const OPTION_DATE_MAX                  = 'date_max';
    public const OPTION_UF_REPLACE                = 'uf_replace';
    public const OPTION_KEEP_SYNC                 = 'keep_sync';
    public const OPTION_IGNORE_CLASSES            = 'ignore_classes';
    public const OPTION_NO_UPDATE_PATIENTS_EXISTS = 'no_update_patients_exists';
    public const OPTION_IPP_TAG                   = 'ipp_tag';
    public const OPTION_IMPORT_PRESC              = 'import_presc';
    public const OPTION_EXCLUDE_DUPLICATE         = 'exclude_duplicate';

    public const MAX_STEP = 1000;

    public const DEFAULT_OPTIONS = [
        self::OPTION_FILES_DIRECTORY           => null,
        self::OPTION_UPDATE_DATA               => false,
        self::OPTION_PATIENT_ID                => null,
        self::OPTION_LINK_FILES_TO_OP          => false,
        self::OPTION_CORRECT_FILES             => false,
        self::OPTION_HANDLERS                  => false,
        self::OPTION_PATIENTS_ONLY             => false,
        self::OPTION_DATE_MIN                  => null,
        self::OPTION_DATE_MAX                  => null,
        self::OPTION_UF_REPLACE                => null,
        self::OPTION_KEEP_SYNC                 => false,
        self::OPTION_IGNORE_CLASSES            => '',
        self::OPTION_NO_UPDATE_PATIENTS_EXISTS => false,
        self::OPTION_IPP_TAG                   => null,
        self::OPTION_IMPORT_PRESC              => false,
        self::OPTION_EXCLUDE_DUPLICATE         => false,
    ];

    private string $directory;
    private int    $step;
    private int    $start;
    private array  $options;

    public function __construct(string $directory, int $start, int $step, array $options)
    {
        $this->directory = $directory;
        $this->start     = $start;
        $this->step      = min($step, self::MAX_STEP);
        $this->options   = array_merge(self::DEFAULT_OPTIONS, $options);
    }

    public function import(): int
    {
        $this->prepareCacheAndHandlers();

        if ($this->options[self::OPTION_KEEP_SYNC]) {
            $this->enableSync();
        }

        $this->addIgnoredClasses();

        // Cast to int to avoid invalid chars
        if ($patient_id = (int)$this->options[self::OPTION_PATIENT_ID]) {
            if (!$this->importSinglePatient(
                $this->directory . '/CPatient-' . $patient_id,
                (bool)$this->options[self::OPTION_UPDATE_DATA],
                $this->options,
                $this->options[self::OPTION_FILES_DIRECTORY]
            )) {
                throw new CMbException('CPatientXMLImport-Error-Id is not valid', $patient_id);
            }

            return 1;
        }

        return $this->importPatients();
    }

    protected function importPatients(): int
    {
        $iterator = new DirectoryIterator($this->directory);
        $count    = $i = 0;

        /** @var DirectoryIterator $file_info */
        foreach ($iterator as $file_info) {
            if ($file_info->isDot()) {
                continue;
            }

            if (strpos($file_info->getFilename(), "CPatient-") === 0) {
                $i++;
                if ($i <= $this->start) {
                    continue;
                }

                if ($i > $this->start + $this->step) {
                    break;
                }

                $count++;

                $this->importSinglePatient(
                    $file_info->getRealPath(),
                    (bool)$this->options[self::OPTION_UPDATE_DATA],
                    $this->options,
                    $this->options[self::OPTION_FILES_DIRECTORY]
                );
            }
        }

        return $count;
    }

    protected function prepareCacheAndHandlers(): void
    {
        if ($this->options[self::OPTION_HANDLERS]) {
            // Disable cache anyway to avoid bugs
            CStoredObject::$useObjectCache = false;
        } else {
            CApp::disableCacheAndHandlers();
        }
    }

    protected function enableSync(): void
    {
        HandlerManager::enableObjectHandler('CSyncHandler');
    }

    private function addIgnoredClasses(): void
    {
        if ($ignore_classes = $this->options[self::OPTION_IGNORE_CLASSES]) {
            CPatientXMLImport::$_ignored_classes = array_merge(explode('|', $ignore_classes), CPatientXMLImport::$_ignored_classes);
        }

        if (!CModule::getActive("dPprescription") || !$this->options[self::OPTION_IMPORT_PRESC]) {
            CPatientXMLImport::$_ignored_classes = array_merge(CPatientXMLImport::$_prescription_classes, CPatientXMLImport::$_ignored_classes);
        }
    }

    /**
     * Import a single patient from the given directory.
     *
     * @throws CMbException
     */
    private function importSinglePatient(
        string $directory_path,
        bool $update_data,
        array $options,
        ?string $files_directory
    ): bool {
        $extracted = false;
        if (!is_dir($directory_path)) {
            if (is_file("$directory_path." . CXMLPatientExport::ARCHIVE_TYPE_TAR)) {
                $directory_path .= ('.' . CXMLPatientExport::ARCHIVE_TYPE_TAR);
            } elseif (is_file("$directory_path." . CXMLPatientExport::ARCHIVE_TYPE_ZIP)) {
                $directory_path .= ('.' . CXMLPatientExport::ARCHIVE_TYPE_ZIP);
            }

            if (is_file($directory_path)) {
                $this->extractPatientData($directory_path);
                $path_info = pathinfo($directory_path);
                $directory_path = $path_info['dirname'] . '/' . $path_info['filename'];
                $extracted      = true;
            }
        }

        $xmlfile    = "$directory_path/export.xml";
        $xml_exists = false;
        try {
            if (file_exists($xmlfile)) {
                $xml_exists = true;
                $xmlfile    = realpath($xmlfile);

                $importer = $this->getImporter($xmlfile);
                $importer->setUpdateData($update_data);
                $importer->setDirectory(dirname($xmlfile));

                if ($files_directory) {
                    $importer->setFilesDirectory($files_directory);
                }

                $importer->import([], $options);
            }
        } finally {
            // Must always clean directory to avoid having extracted directories that will be parsed
            if ($extracted) {
                CMbPath::remove($directory_path);
            }
        }

        return $xml_exists;
    }

    protected function getImporter(string $xmlfile): CPatientXMLImport
    {
        return new CPatientXMLImport($xmlfile);
    }


    /**
     * Extract $directory_path to $directory_path without extension if it's a tar or a zip.
     * Remove previously existing directory.
     *
     * @throws CMbException
     */
    private function extractPatientData(string $directory_path): void
    {
        $path_info = pathinfo($directory_path);
        $ext = $path_info['extension'];
        switch ($ext) {
            case CXMLPatientExport::ARCHIVE_TYPE_TAR:
            case CXMLPatientExport::ARCHIVE_TYPE_ZIP:
                $extract_dir = $path_info['dirname'] . '/' . $path_info['filename'];
                if (is_dir($extract_dir)) {
                    CMbPath::remove($extract_dir);
                }

                $phar = new PharData($directory_path);
                $phar->extractTo($extract_dir);
                break;
            default:
                throw new CMbException(
                    'CXMLPatientExport-Error-Type is not valid use one of',
                    $ext,
                    CXMLPatientExport::ARCHIVE_TYPE_TAR . ', ' . CXMLPatientExport::ARCHIVE_TYPE_ZIP
                );
        }
    }
}
