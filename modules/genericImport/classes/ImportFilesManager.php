<?php
/**
 * @package Mediboard\GenericImport
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Import\GenericImport;

use DirectoryIterator;
use Exception;
use finfo;
use Ox\Core\CMbDT;
use Ox\Core\CMbPath;
use Ox\Core\CMbString;
use Ox\Import\Framework\Entity\CImportCampaign;
use Ox\Import\GenericImport\Exception\GenericImportException;
use Ox\Mediboard\Files\CFile;

/**
 * Description
 */
class ImportFilesManager
{
    private const DIRECTORY = 'import';

    private const MIME_TYPE_CSV = 'text/plain';

    private const MIME_TYPE_ZIP = 'application/zip';

    private const MIME_TYPE_OCTET_STREAM = 'application/octet-stream';

    private const ALLOWED_MIME_TYPES = [
        self::MIME_TYPE_CSV,
        self::MIME_TYPE_ZIP,
        self::MIME_TYPE_OCTET_STREAM,
    ];

    /** @var array */
    private $files = [];

    /** @var array */
    private $upload_results = [];

    /** @var CImportCampaign */
    private $campaign;

    /** @var string */
    private $directory;

    /** @var bool */
    private $mapped = false;

    public function __construct(CImportCampaign $campaign)
    {
        $this->campaign = $campaign;

        $this->directory = CFile::getDirectory() . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . self::DIRECTORY
            . DIRECTORY_SEPARATOR . $this->campaign->_guid;
    }

    public function uploadFiles(array $files, bool $empty_import_dir = false): void
    {
        if (empty($files)) {
            return;
        }

        $this->initUploadDirectory($empty_import_dir);

        $this->extractUploadedFilesInfos($files);

        $this->checkAndMoveFiles();
    }

    public function listImportFiles(): array
    {
        if (!is_dir($this->directory)) {
            return [];
        }

        $files = [];

        $import_files = $this->campaign->loadBackRefs('import_files', 'file_name');

        /** @var CImportFile $import_file */
        foreach ($import_files as $import_file) {
            $file_path = $this->directory . DIRECTORY_SEPARATOR . $import_file->file_name;

            $files[] = [
                'import_file' => $import_file,
                'file_size'   => (file_exists($file_path)) ? CMbString::toDecaSI(filesize($file_path)) : null,
                'file_time'
                              => (file_exists($file_path)) ? CMbDT::dateTimeFromTimestamp(
                    'NOW',
                    filemtime($file_path)
                ) : null,
            ];
        }

        return $files;
    }

    public function getUploadResults(): array
    {
        return $this->upload_results;
    }

    public function getUploadedFilePath(string $file_name): ?string
    {
        $file_path = $this->directory . DIRECTORY_SEPARATOR . $file_name;

        if (file_exists($file_path)) {
            return $file_path;
        }

        return null;
    }

    private function initUploadDirectory(bool $empty_import_dir): void
    {
        // TODO Handle errors
        if (is_dir($this->directory)) {
            if ($empty_import_dir) {
                CMbPath::emptyDir($this->directory);
                $this->deleteImportFile();
            }

            return;
        }

        CMbPath::forceDir($this->directory);
    }

    private function extractUploadedFilesInfos(array $files): void
    {
        for ($i = 0; $i < count($files['name']); $i++) {
            $this->files[] = [
                'file_name' => $files['name'][$i],
                'path'      => $files['tmp_name'][$i],
                'size'      => $files['size'][$i],
            ];
        }
    }

    private function checkAndMoveFiles(): void
    {
        foreach ($this->files as $file) {
            $mime_type = $this->getMimeType($file['path']);

            if (!in_array($mime_type, self::ALLOWED_MIME_TYPES, true) || filesize($file['path']) !== $file['size']) {
                $this->upload_results[] = [
                    'ImportFileManager-Error-Mime type is not autorized for file',
                    UI_MSG_WARNING,
                    $mime_type,
                    $file['file_name'],
                ];
                continue;
            }

            if ($mime_type === self::MIME_TYPE_CSV || $mime_type === self::MIME_TYPE_OCTET_STREAM) {
                if ($this->moveFile($file)) {
                    try {
                        $this->saveImportFile($file['file_name']);
                        $mapped                 = $this->mapped ? '-Mapped' : '';
                        $this->upload_results[] = [
                            'ImportFileManager-Msg-Uploaded' . $mapped,
                            UI_MSG_OK,
                            $file['file_name'],
                        ];
                    } catch (GenericImportException $e) {
                        $this->upload_results[] = [$e->getMessage(), UI_MSG_WARNING, $file['file_name']];
                    }
                } else {
                    $this->upload_results[] = [
                        'ImportFileManager-Error-An error occured during the upload',
                        UI_MSG_WARNING,
                        $file['file_name'],
                    ];
                }
            } elseif ($mime_type === self::MIME_TYPE_ZIP) {
                if (($count = $this->extractFilesFromZip($file)) > 0) {
                    $this->upload_results[] = [
                        'ImportFileManager-Msg-Exctracted from',
                        UI_MSG_OK,
                        $count,
                        $file['file_name'],
                    ];
                } else {
                    $this->upload_results[] = [
                        'ImportFileManager-Error-An error occured during the extraction',
                        UI_MSG_WARNING,
                        $file['file_name'],
                    ];
                }
            }
        }
    }

    private function moveFile(array $file_infos): bool
    {
        return move_uploaded_file(
            $file_infos['path'],
            $this->directory . DIRECTORY_SEPARATOR . $file_infos['file_name']
        );
    }

    private function extractFilesFromZip(array $file): int
    {
        $tmp_dir = $this->directory . DIRECTORY_SEPARATOR . uniqid();
        CMbPath::forceDir($tmp_dir);
        $extracted = CMbPath::extract($file['path'], $tmp_dir, 'zip');

        if ($extracted === 0 || $extracted === false) {
            CMbPath::remove($tmp_dir);

            return 0;
        }

        $dir_it = new DirectoryIterator($tmp_dir);
        /** @var DirectoryIterator $it */
        foreach ($dir_it as $it) {
            if ($it->isDot()) {
                continue;
            }

            if ($this->getMimeType($it->getPathname()) === self::MIME_TYPE_CSV) {
                if (copy($it->getPathname(), $this->directory . DIRECTORY_SEPARATOR . $it->getFilename())) {
                    try {
                        $this->saveImportFile($it->getFilename());
                    } catch (GenericImportException $e) {
                        // Do nothing
                    }
                }
            }
        }

        CMbPath::remove($tmp_dir);

        return $extracted;
    }

    private function getMimeType(string $file_path): string
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE, '');

        return $finfo->file($file_path);
    }

    private function saveImportFile(string $file): void
    {
        $import_file                     = new CImportFile();
        $import_file->import_campaign_id = $this->campaign->_id;
        $import_file->file_name          = $file;
        $import_file->loadMatchingObjectEsc();
        $this->mapped = false;

        if ($key = array_search(basename($import_file->file_name, '.csv'), GenericImport::AVAILABLE_TYPES)) {
            $import_file->entity_type = GenericImport::AVAILABLE_TYPES[$key];
            $this->mapped             = true;
        }

        if ($msg = $import_file->store()) {
            throw new GenericImportException($msg);
        }
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    /**
     * Delete import file
     *
     * @return void
     * @throws Exception
     */
    private function deleteImportFile(): void
    {
        $import_file                     = new CImportFile();
        $import_file->import_campaign_id = $this->campaign->_id;
        $import_files                    = $import_file->loadMatchingListEsc();

        foreach ($import_files as $_import_file) {
            $_import_file->delete();
        }
    }
}
