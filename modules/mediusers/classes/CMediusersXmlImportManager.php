<?php

/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers;

use DirectoryIterator;
use finfo;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbPath;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use SplFileInfo;
use ZipArchive;

/**
 * Description
 */
class CMediusersXmlImportManager
{
    private const DEFAULT_PREFS_FILENAME = 'Default_prefs.xml';

    private const ALLOWED_FILE_TYPES = ['xml' => 'text/xml', 'zip' => 'application/zip'];

    private const IMPORT_DIRECTORY = 'tmp/import_mediusers';

    /** @var DirectoryIterator */
    private $iterator;

    /** @var string */
    private $upload_path;

    /** @var array */
    private $options;

    /** @var array */
    private $errors = [];

    public function __construct()
    {
        $this->initUploadPath();
    }

    public function importMediusers(array $options, string $tmp_path, string $file_name): void
    {
        $this->options = $options;

        $mime = $this->getMimeType($tmp_path, $file_name);
        // Disable cache for import
        CStoredObject::$useObjectCache = false;

        CMediusersXMLImport::setIgnoredClasses($this->options);

        if ($mime === self::ALLOWED_FILE_TYPES['xml']) {
            try {
                $this->importFile($tmp_path);
            } catch (CMbException $e) {
                $this->errors[] = [$e->getMessage(), UI_MSG_WARNING];
            }
        } else {
            $this->createDir();
            $directory = $this->unzipFiles($tmp_path, $file_name);

            $this->initIterator($directory);

            /** @var SplFileInfo $_it */
            foreach ($this->iterator as $_it) {
                if ($this->isValidFile($_it)) {
                    try {
                        $this->importFile($_it->getPathname());
                    } catch (CMbException $e) {
                        $this->errors[] = [$e->getMessage(), UI_MSG_WARNING];
                    }
                }
            }

            // Import default prefs
            if (CMbArray::get($this->options, 'default_prefs')) {
                try {
                    $xmlfile = rtrim($directory, '/\\') . self::DEFAULT_PREFS_FILENAME;
                    $this->importFile($xmlfile);
                } catch (CMbException $e) {
                    $this->errors[] = [$e->getMessage(), UI_MSG_WARNING];
                }
            }

            $this->removeFiles();
        }
    }

    public function getImportUsers(string $tmp_path, string $file_name): array
    {
        $this->createDir();
        $directory = $this->handleFiles($tmp_path, $file_name);

        $this->initIterator($directory);

        $import_users = [];
        /** @var SplFileInfo $_it */
        foreach ($this->iterator as $_it) {
            if ($this->isValidFile($_it)) {
                try {
                    $filename = $_it->getFilename();
                    if ($filename === self::DEFAULT_PREFS_FILENAME) {
                        continue;
                    }

                    $import_users[utf8_decode($filename)] = $this->extractUserInfosFromXml($_it->getPathname());
                } catch (CMbException $e) {
                    $errors[] = [$e->getMessage(), UI_MSG_WARNING];
                }
            }
        }

        return $import_users;
    }

    public function compare(string $user_guid): array
    {
        $this->initUploadPath();

        $this->checkDir($this->upload_path);

        $file_path = $this->upload_path . '/' . utf8_encode($user_guid);

        $this->checkFile($file_path);

        $import = new CMediusersXMLImport($file_path);

        return $import->compareProfileFromXML();
    }

    public function importNewProfile(string $file_name, array $options): void
    {
        $this->initUploadPath();

        $this->checkDir($this->upload_path);

        $file_path = rtrim($this->upload_path, '/\\') . '/' . utf8_encode($file_name);

        $this->checkFile($file_path);

        $group = CGroups::loadCurrent();

        $import = new CMediusersXMLImport($file_path);
        $import->setGroupId($group->_id);
        $import->setDirectory(dirname($file_path));
        $import->import([], $options);
    }

    public function updateProfile(string $file_name, array $permissions, array $options): void
    {
        $this->initUploadPath();

        $this->checkDir($this->upload_path);

        $file_path = rtrim($this->upload_path, '/\\') . '/' . utf8_encode($file_name);

        $this->checkFile($file_path);

        $import = new CMediusersXMLImport($file_path);
        $import->setDirectory(dirname($file_path));
        $import->updateProfile($permissions, $options);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function getMimeType(string $tmp_path, ?string $file_name = null): string
    {
        if (!file_exists($tmp_path)) {
            throw new CMbException('CFile-not-exists', $file_name ?? $tmp_path);
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE, '');
        $mime  = $finfo->file($tmp_path);

        if (!in_array($mime, self::ALLOWED_FILE_TYPES)) {
            throw new CMbException(
                'CMediusersImportLegacyController-Error-file must be in list. Type provided',
                implode(',', self::ALLOWED_FILE_TYPES),
                $mime
            );
        }

        return $mime;
    }

    /**
     * @throws CMbException
     */
    protected function importFile(string $file_path, ?CGroups $group = null): void
    {
        if (file_exists($file_path)) {
            $importer = new CMediusersXMLImport($file_path);
            $importer->setGroupId(($group !== null) ? $group->_id : null);
            $importer->setDirectory(dirname($file_path));
            $importer->import([], $this->options);
        }
    }

    private function initUploadPath(): void
    {
        if (!$this->upload_path) {
            $this->upload_path = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . self::IMPORT_DIRECTORY;
        }
    }

    protected function createDir(): void
    {
        if (is_dir($this->upload_path)) {
            CMbPath::emptyDir($this->upload_path);
        } else {
            CMbPath::forceDir($this->upload_path);
        }
    }

    protected function unzipFiles(string $tmp_path, string $zip_name): string
    {
        $zip_path = $this->upload_path . DIRECTORY_SEPARATOR . $zip_name;
        $this->moveUploadedFile($tmp_path, $zip_path);

        $zip = new ZipArchive();
        $zip->open($zip_path);
        if (!$zip->extractTo($this->upload_path)) {
            throw new CMbException('CMediusersImportLegacyController-Error-Cannot extract archive');
        }

        return $this->upload_path;
    }

    protected function moveUploadedFile(string $tmp_path, string $file_name): void
    {
        if (!move_uploaded_file($tmp_path, $file_name)) {
            throw new CMbException('CMediusersImportLegacyController-Error-Cannot move uploaded file');
        }
    }

    private function initIterator(string $directory): void
    {
        $this->iterator = new DirectoryIterator($directory);
    }

    private function isValidFile(SplFileInfo $it): bool
    {
        return (!$it->isDot() && !$it->isDir() && $it->getExtension() === 'xml');
    }

    private function removeFiles(): void
    {
        if (is_dir($this->upload_path)) {
            CMbPath::emptyDir($this->upload_path);
        }
    }

    private function handleFiles(string $tmp_path, string $file_name): string
    {
        $mime = $this->getMimeType($tmp_path, $file_name);
        if ($mime === self::ALLOWED_FILE_TYPES['xml']) {
            $file_path = $this->upload_path . DIRECTORY_SEPARATOR . $file_name;

            $this->moveUploadedFile($tmp_path, $file_path);

            $directory = $this->upload_path;
        } else {
            $directory = $this->unzipFiles($tmp_path, $file_name);
        }

        return $directory;
    }

    private function extractUserInfosFromXml(string $xmlfile): array
    {
        $this->checkFile($xmlfile);

        $import = new CMediusersXMLImport($xmlfile);
        $user   = $import->getProfileFromXML();

        if (!$user) {
            throw new CMbException('CUser-import-no-user', $xmlfile);
        }

        return [
            'new'               => !(bool)$user->_id,
            'user'              => $user,
            'hash_perm_obj'     => ($user->_id) ? $user->getPermObjectHash() : '',
            'hash_perm_mod'     => ($user->_id) ? $user->getPermModulesHash() : '',
            'hash_prefs'        => ($user->_id) ? $user->getPrefsHash() : '',
            'new_hash_perm_mod' => $import->getHashFromXML('CPermModule'),
            'new_hash_perm_obj' => $import->getHashFromXML('CPermObject'),
            'new_hash_prefs'    => $import->getHashFromXML('CPreferences'),
            'nb_perms_obj'      => $import->getCount('CPermObject'),
            'nb_perms_mod'      => $import->getCount('CPermModule'),
            'nb_prefs'          => $import->getCount('CPreferences'),
        ];
    }

    private function checkFile(string $file): void
    {
        if (!file_exists($file)) {
            throw new CMbException('CFile-not-exists', $file);
        }
    }

    private function checkDir(string $dir): void
    {
        if (!is_dir($dir)) {
            throw new CMbException(CAppUI::tr('mod-dPpatients-directory-unavailable', $dir));
        }
    }

    public function getUploadPath(): string
    {
        return $this->upload_path;
    }
}
