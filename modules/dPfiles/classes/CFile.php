<?php

/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Exception;
use FilesystemIterator;
use finfo;
use FPDF;
use FPDI;
use Ox\AppFine\Server\CNatureFile;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Resources\Item;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CFileParser;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbPath;
use Ox\Core\CMbPDFMerger;
use Ox\Core\CMbSecurity;
use Ox\Core\CMbString;
use Ox\Core\CModelObject;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\Mutex\CMbMutex;
use Ox\Core\Sessions\CSessionHandler;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\PersisterVisitorInterface;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\IGroupRelated;
use Ox\Mediboard\Patients\IPatientRelated;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Search\IIndexableObject;
use Ox\Mediboard\System\CObjectEncryption;
use Ox\Mediboard\System\EncryptedObjectTrait;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Fichiers téléversés vers l'application.
 * Egalement :
 *  - pièces jointes d'email
 *  - conversion de fichiers en PDF
 *  - aperçus PDF de documents
 */
class CFile extends CDocumentItem implements IIndexableObject, ImportableInterface
{
    use EncryptedObjectTrait {
        createObjectEncryption as createObjectEncryptionTrait;
    }

    /** @var string */
    public const RESOURCE_TYPE = 'file';

    public const ALLOWED_EXTENSIONS = [
        'apz',
        'bmp',
        'crt',
        'csv',
        'doc',
        'docm', // Word with macro
        'docx',
        'gif',
        'gpg', // interop ror
        'heic',
        'hpm', // Hprim medecin
        'html',
        'jpeg',
        'jpg',
        'mov',
        'mpe',
        'mpeg',
        'mpg',
        'mp3',
        'mp4',
        'ods',
        'odt',
        'ok', // interop ror
        'osoft', // Import from Osoft
        'oxps',
        'pdf',
        'png',
        'rtf',
        'svg',
        'tif',
        'tiff',
        'txt',
        'webm',
        'wmv',
        'xml',
        'xlsx',
        'xps',
        'zip',
    ];

    public const ALLOWED_MIME_TYPES = [
        //'application/json',   // Detected as text/plain
        //'image/jpg',          // Detected as image/jpeg
        //'application/xml',    // Detected as text/xml
        //'application/rtf',    // Detected as text/rtf

        // Audio
        'audio/x-wav',
        'audio/mpeg',

        'application/pgp', // interop
        'application/octet-stream', // fichiers apz (ou chiffrés)

        'application/osoft', // Import Osoft

        // PDF
        'application/pdf',

        // Word + libre office
        'application/CDFV2', // Old Word documents
        'application/msword', // Documents word
        'application/vnd.oasis.opendocument.spreadsheet', // ods
        'application/vnd.oasis.opendocument.text', // odt
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // Excel xlsx
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // Word docx

        'application/x-hl7',
        'application/zip',

        // Images
        'image/bmp',
        'image/gif',
        'image/heic',
        'image/heif',
        'image/jpeg',
        'image/png',
        'image/svg+xml',
        'image/tiff',

        // Texte
        'text/html',
        'text/osoft', // Import Osoft
        'text/plain',
        'text/rtf',
        'text/xml',
        // The Fileinfo extension can return this mime-type for text, like mail sent by doctors
        'text/x-Algol68',

        // Videos
        'video/quicktime',
        'video/mp4',
        'video/mpeg',
        'video/webm',
        'video/x-ms-wmv',

        // Empty files
        'application/x-empty',
    ];

    /** @var ?string */
    private static $directory = null;

    /** @var ?string */
    private static $directory_private = null;

    /** @var bool Tell wether the CFile::registerPrivateDirectory has been called */
    private static $is_directory_registered = false;

    // Whether migration is enabled or not (useful for import)
    /** @var bool */
    public static $migration_enabled = true;

    public const FILENAME_LENGTH = 16;

    public const RELATION_CONTEXT = "context";

    public const RELATION_CATEGORY = 'category';

    public const RELATION_NATURE_FILE = 'natureFile';

    // DB Table key
    public $file_id;

    // DB Fields
    public $file_real_filename;
    public $file_name;
    public $file_type;
    public $file_date;
    public $rotation;
    public $date_rotation;
    public $language;
    public $compression;
    public $author_id;
    public $nature_file_id;

    public $_base64_content;

    private $_content;
    private $_old_content;
    private $_move_path;
    private $_uploaded = false;
    private $_copy     = false;

    // Form fields
    public $_sub_dir;
    public $_absolute_dir;
    public $_file_path;
    public $_nb_pages;
    public $_old_file_path;
    public $_data_uri;
    public $_binary_content;
    public $_file_type;

    public $_ref_read_status;
    public $_file_name_cda;

    // Behavior fields
    public $_rotate;
    public $_rename;
    public $_merge_files;

    public $_ref_order_item;
    public $_ref_object_sent;
    public $_ref_author;
    public $_ref_nature_file;

    public $_quite_store = false;

    // Other fields
    static $rotable_extensions = ["bmp", "gif", "jpg", "jpeg", "png", "pdf"];

    // Files extensions so the pdf conversion is possible
    static $file_types =
        "cgm csv dbf dif doc docm docx dot dotm dotx
    dxf emf eps fodg fodp fods fodt hwp
    lwp met mml odp odg ods otg odf odm odt oth
    otp ots ott pct pict pot potm potx pps ppt pptm
    pptx rtf sgf sgv slk stc std sti stw svg svm sxc
    sxd sxg sxi sxm sxw txt uof uop uos uot wb2 wk1 wks
    wmf wpd wpg wps xlc xlm xls xlsb xlsm xlsx xlt xltm
    xltx xlw";

    static $_files_types = [
        "excel",
        "image",
        "pdf",
        "text",
        "word",
    ];

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec              = parent::getSpec();
        $spec->table       = 'files_mediboard';
        $spec->key         = 'file_id';
        $spec->measureable = true;

        $spec->uniques['file_real_filename'] = ['file_real_filename'];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props                       = parent::getProps();
        $props["file_date"]          = "dateTime notNull fieldset|default";
        $props["file_real_filename"] = "str notNull show|0 fieldset|extra";
        $props["file_type"]          = "str show|0 fieldset|default";
        $props["file_name"]          = "str notNull show|0 fieldset|default";
        $props["rotation"]           = "num default|0 show|0 fieldset|extra";
        $props["date_rotation"]      = "dateTime fieldset|extra";
        $props["language"]           = "enum list|en-EN|es-ES|fr-CH|fr-FR default|fr-FR show|0 fieldset|extra";
        $props["compression"]        = "str show|0 fieldset|extra";
        $props['author_id']          = 'ref class|CMediusers back|owned_files fieldset|default';
        $props['nature_file_id']     = 'num fieldset|default';

        $props["object_id"]        .= ' back|files';
        $props["file_category_id"] .= " back|categorized_files";

        // Form Fields
        $props["_sub_dir"]       = "str show|0";
        $props["_absolute_dir"]  = "str show|0";
        $props["_file_path"]     = "str show|0";
        $props["_old_file_path"] = "str show|0";

        // Behavior fields
        $props["_rotate"]      = "enum list|left|right";
        $props["_rename"]      = "str";
        $props["_merge_files"] = "bool";

        return $props;
    }

    /**
     * Get author
     *
     * @return CMediusers|CStoredObject
     * @throws Exception
     */
    public function loadRefAuthor(): CMediusers
    {
        return $this->_ref_author = $this->loadFwdRef('author_id', true);
    }

    /**
     * Get the number of CFile with a specific name from an object list
     *
     * @param CStoredObject[] $objects the objects
     * @param string          $name    the name
     *
     * @return void
     * @throws Exception
     */
    static function massCountNamed($objects, $name)
    {
        $where = [
            "file_name" => "= '$name'",
        ];
        CStoredObject::massCountBackRefs($objects, "files", $where, [], "named_file_$name");
    }

    /**
     * Load a file with a specific name associated with an object
     *
     * @param CMbObject $object Context object
     * @param string    $name   File name with extension
     *
     * @return CFile
     */
    static function loadNamed(CMbObject $object, $name)
    {
        if (!$object->_id) {
            return new self();
        }

        // Precounting optimization: no need to query when we already know array is empty
        $backname = "named_file_$name";

        if (isset($object->_count[$backname]) && $object->_count[$backname] === 0) {
            return new self();
        }

        $file = new self();
        $file->setObject($object);
        $file->file_name = $name;
        $file->loadMatchingObject();

        return $file;
    }

    /**
     * Force directories creation for file upload
     *
     * @return void
     */
    function forceDir()
    {
        // Check global directory
        if (!CMbPath::forceDir(self::getPrivateDirectory())) {
            trigger_error("Files directory is not writable : " . self::getPrivateDirectory(), E_USER_WARNING);

            return;
        }

        // Checks complete file directory
        CMbPath::forceDir($this->_absolute_dir);
    }

    /**
     * Get the content of the file
     *
     * @return string
     * @throws Exception
     */
    public function getBinaryContent(): ?string
    {
        $content = $this->_file_path ? file_get_contents($this->_file_path) : null;

        if (!$this->object_class) {
            return $this->_binary_content = $content;
        }

        // To avoid decrpyt if no needed
        $classmap = CClassMap::getInstance();
        $classmap_class = $classmap->getClassMap($classmap->getAliasByShortName($this->object_class));

        if (!in_array(ConfidentialObjectInterface::class, $classmap_class->interfaces)) {
            return $this->_binary_content = $content;
        }

        return $this->_binary_content = $this->decrypt($content);
    }

    /**
     * @param string $content Content to write to FS
     *
     * @return void
     */
    function setContent($content)
    {
        $this->_content = $content;
    }

    /**
     * @return mixed
     */
    function getContent()
    {
        return $this->_content;
    }

    /**
     * Write the content of a file to the FS using $this->_content as the content of the file
     *
     * @return string|int String or false on failure, number of byes written on success
     *
     * @throws CMbException
     */
    private function writeFile()
    {
        if ($this->_content === null) {
            return 'CFile-error-Empty content';
        }

        if (!self::isAllowedFileContent($this->_content)) {
            return 'CFile-error-File type is not allowed';
        }

        if (!$this->prepareDir()) {
            return 'CFile-error-Unable to create directory';
        }

        if ($this->_id && is_file($this->_file_path)) {
            $this->_old_content = file_get_contents($this->_file_path);
        }

        $this->_ref_object = $this->loadFwdRef('object_id', true);

        if ($this->_ref_object instanceof ConfidentialObjectInterface) {
            $this->_content = $this->encrypt($this->_content, $this->_ref_object->getKeyName());
        }

        $result = file_put_contents($this->_file_path, $this->_content);

        // Empty file cache to avoid getting a bad doc size
        if ($this->_id) {
            clearstatcache();
        }

        $this->doc_size = filesize($this->_file_path);

        // Reset _content to avoid writing multiple times on FS
        $this->_content = null;

        // No error and at least one byte written
        return (bool)($result !== false && $result > 0);
    }

    /**
     * Prepare the directory for writing
     *
     * @return bool
     */
    private function prepareDir()
    {
        if (!$this->file_real_filename) {
            trigger_error(CAppUI::tr("CFile-error-Empty file_real_filename"), E_USER_WARNING);

            return false;
        }

        $this->updateFormFields();

        if (!static::checkSignatureFile()) {
            trigger_error(
                CAppUI::tr('CFile-error-Signature file is not readable: %s', static::getSignatureFilePath()),
                E_USER_WARNING
            );

            return false;
        }

        if ($this->_absolute_dir && $this->forceDir() === false) {
            trigger_error(CAppUI::tr("CFile-error-Cannot create directory"), E_USER_WARNING);

            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        $this->_file_date = $this->file_date;

        $this->_extensioned = $this->file_name;

        $last_point          = strrpos($this->_extensioned, '.');
        $this->_no_extension = substr($this->_extensioned, 0, $last_point);

        $this->completeField("object_id");

        // Computes complete file path
        if ($this->object_id) {
            $this->completeFilePath();
        }

        $this->_shortview = $this->_view = str_replace("_", " ", $this->file_name);

        if (CAppUI::pref("show_creation_date")) {
            $this->_view .= " (" . CMbDT::transform(null, $this->file_date, CAppUI::conf("date")) . ")";
        }

        // Toujours en version 1
        $this->_version = 1;

        $this->guessFileType();
    }

    /**
     * Complete the file path using the confs
     *
     * @return void
     * @throws Exception
     */
    function completeFilePath()
    {
        $this->completeField("file_real_filename");

        // Remove non alphanumerical chars to avoid path traversal exploitation
        $this->sanitizeFileRealFilename();

        $this->_sub_dir      = self::getSubDir($this->file_real_filename);
        $this->_absolute_dir = self::getPrivateDirectory() . $this->_sub_dir;
        $this->_file_path    = "$this->_absolute_dir/$this->file_real_filename";

        if ($this->conf('migration_started') && !$this->conf('migration_finished')) {
            // Keep old storage system to assure backwards compatibility
            $_old_sub_dir      = "$this->object_class/" . intval($this->object_id / 1000);
            $_old_absolute_dir = self::getDirectory() . "/$_old_sub_dir/$this->object_id";
            $_old_file_path    = "$_old_absolute_dir/$this->file_real_filename";
            if ($this->_id && !is_file($this->_file_path) && is_file($_old_file_path)) {
                $this->_sub_dir      = $_old_sub_dir;
                $this->_absolute_dir = $_old_absolute_dir;
                $this->_file_path    = $_old_file_path;
            }
        }
    }

    /**
     * @inheritdoc
     */
    function getPerm($permType): bool
    {
        $this->loadTargetObject();

        $parentPerm = parent::getPerm($permType);

        if ($this->_id && ($this->author_id == CMediusers::get()->_id)) {
            return $parentPerm;
        }

        if ($this->_ref_object && $this->_ref_object->_id) {
            return $parentPerm && $this->_ref_object->getPerm($permType);
        }

        return $parentPerm;
    }

    /**
     * Vérification du droit de créer un fichier au sein d'un contexte donné
     *
     * @param CMbObject $object Contexte de création du Document
     *
     * @return bool Droit de création d'un document
     */
    static function canCreate(CMbObject $object)
    {
        $file = new self();

        return $object->canRead() && $file->canClass()->edit;
    }

    /**
     * @inheritdoc
     */
    function fillFields()
    {
        if (!$this->_id) {
            if (!$this->file_date) {
                $this->file_date = CMbDT::dateTime();
            }

            if (!$this->file_real_filename) {
                $charset         = array_merge(range('a', 'f'), range(0, 9));
                $filename_length = CFile::FILENAME_LENGTH;

                if (CAppUI::conf('dPfiles CFile prefix_format')) {
                    $prefix                   = $this->getPrefix($this->file_date);
                    $filename_length          -= strlen($prefix);
                    $filename_length          = max(6, $filename_length);
                    $this->file_real_filename = $prefix . CMbSecurity::getRandomAlphaNumericString(
                            $charset,
                            $filename_length
                        );
                } else {
                    $this->file_real_filename = CMbSecurity::getRandomAlphaNumericString($charset, $filename_length);
                }
            }
        }
    }

    /**
     * @param string $user_id Get the read status of a CFile by a specific user
     *
     * @return CFileUserView
     */
    function loadRefReadStatus($user_id = null)
    {
        $user_id = $user_id ? $user_id : CAppUI::$user->_id;

        $object          = new CFileUserView();
        $object->file_id = $this->_id;
        $object->user_id = $user_id;
        $object->loadMatchingObject();

        return $this->_ref_read_status = $object;
    }

    /**
     * @inheritdoc
     */
    public function store(): ?string
    {
        if (preg_match('/[\/<>\\\\]/', $this->file_name)) {
            $this->file_name = preg_replace("/[\/<>\\\\]/", "-", $this->file_name);
        }

        if ($this->file_real_filename) {
            // Remove non alphanumerical chars to avoid path traversal exploitation
            $this->sanitizeFileRealFilename();
        }

        if ($this->_id && ($this->fieldModified("object_id") || $this->fieldModified("object_class"))) {
            $this->_old->updateFormFields();
            $this->setPrefix();

            // Old file can not be on the FS
            if (file_exists($this->_old->_file_path)) {
                $this->setMoveFrom($this->_old->_file_path);
            }
        }

        if (!$this->_id) {
            // Make sure filename is unique for an object
            $this->getUniqueFilename();

            // Migrate some files
            if (
                !CAppUI::conf("dPfiles CFile migration_finished")
                && self::$migration_enabled && $this->conf('migration_ratio') > 0
            ) {
                CApp::doProbably(
                    $this->conf('migration_limit') / $this->conf('migration_ratio'),
                    [$this, 'migrateSome']
                );
            }

            if ($this->rotation === null) {
                $this->rotation = $this->rotation === null ? 0 : $this->rotation;
                $this->rotation %= 360;
            }

            // Do not allow the store of empty file content for new files
            if ($this->_content === null && !$this->_move_path) {
                return "CFile-error-Empty content";
            }
        }

        // If file already exists and file name modified and if field extension modified,
        // check if new extension is allowed to be stored
        $ext = CMbPath::getExtension($this->file_name);

        if (
            (!$this->_id || $this->fieldModified('file_name'))
            && ((!$this->_id && $ext) || ($this->_id && ($ext !== CMbPath::getExtension($this->_old->file_name))))
            && (!self::isAllowedExtension($ext))
        ) {
            return 'CFile-error-File extension is not allowed';
        }

        try {
            // If _content is not set the file content has not been modified
            if ($this->_content !== null) {
                if (($write_result = $this->writeFile()) !== true) {
                    return is_string($write_result) ? $write_result : "CFile-error-Error writing on FS";
                }
            } elseif ($this->_move_path) {
                if (($move_result = $this->writeExistingFile()) !== true) {
                    return $move_result;
                }
            }
        } catch (CMbException $e) {
            return 'CFile-Error-Unable to encrypt file';
        }

        if ($this->_rotate !== null) {
            $this->setRotation();
        }

        if ($this->fieldModified("rotation")) {
            $this->date_rotation = "now";
        }

        $this->setCDAName();

        if ($msg = parent::store()) {
            $this->rollbackFS();
        } elseif ($this->_object_encryption && !$this->_object_encryption->object_id) {
            $this->storeNewEncryption();
        }

        // Reset _move_path to avoid writing multiple time on FS with multipe store
        $this->_move_path = null;
        $this->_uploaded  = false;
        $this->_copy      = false;

        return $msg;
    }

    public function rawStore()
    {
        $this->sanitizeFileRealFilename();

        return parent::rawStore();
    }

    /**
     * Write an existing file by moving it or copying it
     *
     * @return string|true Return string if failure or true on success
     *
     * @throws CMbException
     */
    private function writeExistingFile()
    {
        if (!$this->_move_path || !is_file($this->_move_path)) {
            return 'CFile-error-No file to move';
        }

        if (!self::isAllowedFileType($this->_move_path)) {
            return 'CFile-error-File type is not allowed';
        }

        if (!$this->prepareDir()) {
            return 'CFile-error-Unable to create directory';
        }

        $this->_ref_object = $this->loadFwdRef('object_id', true);

        // Copy file
        if ($this->_copy) {
            if (!$this->copyFile()) {
                return 'CFile-error-Error copying file';
            }
        } elseif ($this->_uploaded) {
            // Move an uploaded file
            if (!$this->moveUploadedFile()) {
                return 'CFile-error-Error moving uploaded file';
            }
        } elseif (!$this->moveFile()) {
            // Move a file
            return 'CFile-error-Error moving file';
        }

        $this->doc_size = filesize($this->_file_path);

        return true;
    }

    /**
     * Rollback the writing on the FS
     *
     * @return void
     */
    private function rollbackFS()
    {
        if ($this->_move_path && !$this->_copy && !$this->_uploaded) {
            rename($this->_file_path, $this->_move_path);
        } elseif (!$this->_id) {
            unlink($this->_file_path);
        } elseif ($this->_old_content !== null) {
            file_put_contents($this->_file_path, $this->_old_content);
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    function setRotation()
    {
        $this->completeField("rotation");

        if ($this->_rotate == "left") {
            $this->rotation -= 90;
        }
        if ($this->_rotate == "right") {
            $this->rotation += 90;
        }
        $this->rotation %= 360;
        if ($this->rotation < 0) {
            $this->rotation += 360;
        }

        $this->rotation = $this->rotation % 360;
        if ($this->rotation < 0) {
            $this->rotation += 360;
        }
    }

    /**
     * Get a unique filename from the file
     *
     * @return void
     * @throws Exception
     */
    function getUniqueFilename()
    {
        $this->completeField("file_name");
        $this->completeField("object_class");
        $this->completeField("object_id");

        $ds                    = $this->_spec->ds;
        $where["object_class"] = " = '$this->object_class'";
        $where["object_id"]    = " = '$this->object_id'";
        $where["file_name"]    = $ds->prepare("= %", $this->file_name);

        if ($this->countList($where)) {
            $last_point = strrpos($this->file_name, '.');

            $base_name = ($last_point === false) ? $this->file_name : substr($this->file_name, 0, $last_point);
            $extension = ($last_point === false) ? '' : substr($this->file_name, $last_point + 1);
            $indice    = 1;

            do {
                $indice++;
                $suffixe            = sprintf(" %02s", $indice);
                $file_name          = "{$base_name}{$suffixe}" . ($extension ? ".{$extension}" : '');
                $where["file_name"] = $ds->prepare("= %", $file_name);
            } while ($this->countList($where));

            $this->file_name = $file_name;
        }
    }

    /**
     * @inheritdoc
     */
    function delete()
    {
        // Remove previews
        $this->loadRefsFiles();
        foreach ($this->_ref_files as $_file) {
            $_file->delete();
        }

        // When delete is called from controller, the object is not fully loaded
        $this->completeField("object_class");
        $this->updateFormFields();

        if ($msg = parent::delete()) {
            return $msg;
        }

        // Actually remove the file
        unlink($this->_file_path);

        //$this->removeParentDirs();

        return null;
    }


    /**
     * Remove all parent directories if empty
     *
     * @return void
     */
    private function removeParentDirs()
    {
        $path = $this->_file_path;

        for ($i = 0; $i < 3; $i++) {
            $path = dirname($path);
            CMbPath::rmEmptyDir($path);
        }
    }

    /**
     * Move a file from a temporary (uploaded) location to the file system
     *
     * @param string $file The temporary file name
     *
     * @return void
     */
    function setMoveTempFrom($file)
    {
        $this->_move_path = $file;
        $this->_uploaded  = true;
    }

    /**
     * @param string $file The file to copy
     *
     * @return void
     */
    function setCopyFrom($file)
    {
        $this->_move_path = $file;
        $this->_copy      = true;
    }

    /**
     * Prepare the move of a file
     *
     * @param string $move_path Path to the file to move
     * @param bool   $uploaded  Is the file uploaded
     * @param bool   $copy      Only copy the file and don't move it
     *
     * @return void
     */
    function setMoveFrom($move_path, $uploaded = false, $copy = false)
    {
        $this->_move_path = $move_path;
        $this->_uploaded  = $uploaded;
        $this->_copy      = $copy;
    }

    /**
     * Move a file from $this->_move_path to $this->_file_path
     *
     * @return bool
     *
     * @throws CMbException
     */
    private function moveFile()
    {
        if ($this->_ref_object instanceof ConfidentialObjectInterface) {
            return file_put_contents(
                    $this->_file_path,
                    $this->encrypt(file_get_contents($this->_move_path), $this->_ref_object->getKeyName())
                ) && unlink($this->_move_path);
        }

        return rename($this->_move_path, $this->_file_path);
    }

    /**
     * Copy a file from $this->_move_path to $this->_file_path
     *
     * @return bool
     *
     * @throws CMbException
     */
    private function copyFile()
    {
        if ($this->_ref_object instanceof ConfidentialObjectInterface) {
            return file_put_contents(
                $this->_file_path,
                $this->encrypt(file_get_contents($this->_move_path), $this->_ref_object->getKeyName())
            );
        }

        return copy($this->_move_path, $this->_file_path);
    }

    /**
     * Move an uploaded file from $this->_move_path to $this->_file_path
     *
     * @return bool
     *
     * @throws CMbException
     */
    private function moveUploadedFile()
    {
        $return = move_uploaded_file($this->_move_path, $this->_file_path);

        if ($return && $this->_ref_object instanceof ConfidentialObjectInterface) {
            return file_put_contents(
                $this->_file_path,
                $this->encrypt(file_get_contents($this->_file_path), $this->_ref_object->getKeyName())
            );
        }


        return $return;
    }

    /**
     * Detect on old ImageMagick version on the server
     *
     * @return boolean
     */
    function oldImageMagick()
    {
        static $old = null;

        if ($old !== null) {
            return $old;
        }

        exec("convert --version", $ret);
        if (!isset($ret[0])) {
            return $old = false;
        }

        preg_match('/ImageMagick ([0-9\.-]+)/', $ret[0], $matches);

        return $old = $matches[1] < "6.5.8";
    }

    /**
     * Find the pages count of a pdf file
     *
     * @return void
     */
    function loadNbPages()
    {
        if ($this->file_type !== null && strpos($this->file_type, "pdf") !== false && file_exists($this->_file_path)) {
            $gs = CAppUI::conf('dPfiles CThumbnail gs_alias');
            exec(
                "$gs -q -dNOSAFER -dNODISPLAY -c \"({$this->_file_path}) (r) file runpdfbegin pdfpagecount = quit\"",
                $return
            );
            $this->_nb_pages = CMbArray::get($return, 0, null);
        }
    }

    /**
     * @inheritdoc
     */
    public function handleSend(): ?string
    {
        $this->completeField("file_name");
        $this->completeField("file_real_filename");
        $this->completeField("file_type");
        $this->completeField("file_date");
        $this->updateFormFields();

        return parent::handleSend();
    }

    /**
     * Empty a file
     *
     * @return void
     */
    function fileEmpty()
    {
        if (file_exists($this->_file_path)) {
            $this->setContent("");

            if ($msg = $this->store()) {
                return $msg;
            }
        }
    }

    /**
     * Thanks to the extension, detect if a file can be PDF convertible
     *
     * @param string $file_name the name of the file
     *
     * @return bool
     */
    function isPDFconvertible($file_name = null)
    {
        if (!$file_name) {
            $file_name = $this->file_name;
        }

        return
            (CAppUI::conf("dPfiles CFile ooo_active") == 1) &&
            in_array(substr(strrchr(strtolower($file_name), '.'), 1), preg_split("/[\s]+/", self::$file_types));
    }

    /**
     * Test the execution of the soffice process
     *
     * @return bool
     */
    static function openofficeLaunched()
    {
        return exec("pgrep soffice");
    }

    /**
     * Test the load of the soffice process and optionnaly can restart it
     *
     * @param int $force_restart Tell if it restarts or not
     *
     * @return void
     */
    static function openofficeOverload($force_restart = 0)
    {
        exec("sh shell/ooo_overload.sh $force_restart");
    }

    /**
     * PDF conversion of a file
     *
     * @param string $file_path path to the file
     * @param string $pdf_path  path the pdf file
     *
     * @return bool
     */
    function convertToPDF($file_path = null, $pdf_path = null)
    {
        // Vérifier si openoffice est lancé
        if (!self::openofficeLaunched()) {
            return 0;
        }

        // Vérifier sa charge en mémoire
        self::openofficeOverload();

        if (!$file_path && !$pdf_path) {
            $file = new self();
            $file->setObject($this);
            $file->private   = $this->private;
            $file->file_name = $this->file_name . ".pdf";
            $file->file_type = "application/pdf";
            $file->author_id = CAppUI::$user->_id;
            $file->fillFields();
            $file->updateFormFields();
            $file->forceDir();

            if ($msg = $file->store()) {
                CAppUI::setMsg($msg, UI_MSG_ERROR);

                return 0;
            }
            $file_path = $this->_file_path;
            $pdf_path  = $file->_file_path;
        }

        // Requête post pour la conversion.
        // Cela permet de mettre un time limit afin de garder le contrôle de la conversion.

        ini_set("default_socket_timeout", 10);

        $fileContents = base64_encode(file_get_contents($file_path));

        $url  = CAppUI::conf("base_url") . "/?m=files&raw=ajax_ooo_convert";
        $data = [
            "file_data" => $fileContents,
            "pdf_path"  => $pdf_path,
        ];

        // Fermeture de la session afin d'écrire dans le fichier de session
        CSessionHandler::writeClose();

        // Le header Connection: close permet de forcer a couper la connexion lorsque la requête est effectuée
        $ctx = stream_context_create(
            [
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-type: application/x-www-form-urlencoded charset=UTF-8\r\n" .
                        "Connection: close\r\n" .
                        "Cookie: mediboard=" . session_id() . "\r\n",
                    'content' => http_build_query($data),
                ],
            ]
        );

        // La requête post réouvre la session
        $res = file_get_contents($url, false, $ctx);

        if (isset($file) && $res == 1) {
            $file->doc_size = filesize($pdf_path);
            if ($msg = $file->store()) {
                CAppUI::setMsg($msg, UI_MSG_ERROR);

                return 0;
            }
        }
        // Si la conversion a échoué
        // on relance le service s'il ne répond plus.
        if ($res != 1) {
            self::openofficeOverload(1);
        }

        return $res;
    }

    /**
     * Convert tif files to pdf (only first page)
     *
     * @param array[] $tif_files array of tif files
     *
     * @return bool
     */
    static function convertTifPagesToPDF($tif_files)
    {
        $pngs = [];
        foreach ($tif_files as $tif) {
            $pngs[] = self::convertTifToPng($tif); // "C:\\ImageMagick6.6.0-Q16\\"
        }

        $pdf = new FPDF();

        foreach ($pngs as $png) {
            $pdf->AddPage();
            $pdf->Image($png, 5, 5, 200); // millimeters
        }

        $out = $pdf->Output("", "S");

        foreach ($pngs as $png) {
            unlink($png);
        }

        return $out;
    }

    /**
     * Convert a tif to a png (onbly first page)
     *
     * @param string $path path to the tif file
     *
     * @return string
     */
    static function convertTifToPng($path)
    {
        $tmp_tmp = tempnam(sys_get_temp_dir(), "mb_");
        unlink($tmp_tmp);

        $tmp = "$tmp_tmp.png";

        $from = escapeshellarg($path);
        $to   = escapeshellarg($tmp);
        $exec = "convert {$from}[0] {$to}";

        exec($exec, $yaks);

        return $tmp;
    }

    /**
     * Load a pdf file conversion
     *
     * @return self
     */
    function loadPDFconverted()
    {
        $file = new self();
        $file->setObject($this);
        $file->loadMatchingObject();

        return $file;
    }

    /**
     * Return the data uri's content of a file
     *
     * @return string
     */
    function getDataURI()
    {
        return $this->_data_uri = $this->_file_path ?
            "data:" . $this->file_type . ";base64," . urlencode(base64_encode(file_get_contents($this->_file_path))) :
            "";
    }

    /**
     * Returns the data URI of the thumbnail (when possible) of the file, works with images, PDF, etc
     *
     * @param array $options Options (width)
     *
     * @return null|string
     */
    function getThumbnailDataURI($options = ["width" => 640, "base64" => false, "page" => null, "quality" => "high"])
    {
        if (!$this->_id || !file_exists($this->_file_path)) {
            return null;
        }

        $vignette = CThumbnail::displayThumb(
            $this,
            CMbArray::get($options, 'page'),
            $options['width'],
            null,
            $options['quality'],
            $this->rotation
        );

        if (CMbArray::get($options, "base64", false)) {
            return urlencode(base64_encode($vignette));
        }

        return "data:" . $this->file_type . ";base64," . urlencode(base64_encode($vignette));
    }

    /**
     * Purge thumbnails
     *
     * @param int $count The max number of thumbnails to remove
     * @param int $days  Number of days old
     *
     * @return int The number of thumbnails removed
     */
    static function purgeThumbnails($count = 10, $days = 30)
    {
        $dir = static::getThumbnailDir();
        $n = $count;

        self::purgeThumbnailsDir($dir, $n, $days, true);

        return $count - $n;
    }

    public static function getThumbnailDir(): string
    {
        return rtrim(CAppUI::conf('root_dir'), '/') . '/tmp/phpthumb/';
    }

    /**
     * Recursive thumbnails removers
     *
     * @param string  $dir  Directory
     * @param int     $n    File remover count
     * @param int     $days Number of days old
     * @param boolean $root Flag to allow or not directory removal
     *
     * @return void
     */
    static protected function purgeThumbnailsDir($dir, &$n, $days = 30, $root = false)
    {
        if ($n <= 0) {
            return;
        }

        $fi = new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO);

        $min_ctime = time() - (3600 * 24 * $days);

        $count_files = 0;

        /** @var SplFileInfo $_fi */
        foreach ($fi as $_fi) {
            $count_files++;

            if ($n <= 0) {
                return;
            }

            if (mt_rand(0, 4) === 0) {
                continue;
            }

            if ($_fi->isDir()) {
                self::purgeThumbnailsDir($_fi->getRealPath(), $n);
                continue;
            }

            if ($_fi->getMTime() < $min_ctime) {
                $success = unlink($_fi->getRealPath());
                if ($success) {
                    $count_files--;
                    $n--;
                }
            }
        }

        if (!$root && $count_files === 0) {
            rmdir($dir);
        }
    }

    /**
     * Stream a file to a client
     *
     * @return void
     */
    function streamFile()
    {
        header("Pragma: ");
        header("Cache-Control: ");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");  //HTTP/1.1
        header("Cache-Control: post-check=0, pre-check=0", false);
        // END extra headers to resolve IE caching bug
        header("MIME-Version: 1.0");
        header("Content-length: {$this->doc_size}");
        header("Content-type: $this->file_type");
        header("Content-disposition: inline; filename=\"" . $this->file_name . "\"");

        readfile($this->_file_path);
    }

    /**
     * @inheritdoc
     */
    public function getUsersStats(): array
    {
        $ds    = $this->_spec->ds;
        $query = "
      SELECT 
        COUNT(`file_id`) AS `docs_count`,
        SUM(`doc_size`) AS `docs_weight`,
        `author_id` AS `owner_id`
      FROM `files_mediboard`
      WHERE `author_id` IS NOT NULL
      GROUP BY `owner_id`
      ORDER BY `docs_weight` DESC";

        return $ds->loadList($query);
    }

    /**
     * @inheritdoc
     */
    public function getUsersStatsDetails($user_ids, $date_min = null, $date_max = null): array
    {
        $ds = $this->_spec->ds;

        $query = new CRequest();
        $query->addColumn("COUNT(`file_id`)", "docs_count");
        $query->addColumn("SUM(`doc_size`)", "docs_weight");
        $query->addColumn("object_class");
        $query->addColumn("file_category_id", "category_id");
        $query->addTable("files_mediboard");
        $query->addGroup("object_class, category_id");

        if (is_array($user_ids)) {
            $in_owner = $ds->prepareIn($user_ids);
            $query->addWhereClause("author_id", $in_owner);
        }

        if ($date_min) {
            $query->addWhere("file_date <= '$date_max'");
        }

        if ($date_max) {
            $query->addWhere("file_date >= '$date_min'");
        }

        return $ds->loadList($query->makeSelect());
    }

    /**
     * @inheritdoc
     */
    function getPeriodicalStatsDetails($user_ids, $object_class = null, $category_id = null, $depth = 10)
    {
        $period_types = [
            "year"  => [
                "format" => "%Y",
                "unit"   => "YEAR",
            ],
            "month" => [
                "format" => "%m/%Y",
                "unit"   => "MONTH",
            ],
            "week"  => [
                "format" => "%Y S%U",
                "unit"   => "WEEK",
            ],
            "day"   => [
                "format" => "%d/%m",
                "unit"   => "DAY",
            ],
            "hour"  => [
                "format" => "%d %Hh",
                "unit"   => "HOUR",
            ],
        ];

        $details = [];

        $now    = CMbDT::dateTime();
        $doc    = new self;
        $ds     = $doc->_spec->ds;
        $deeper = $depth + 1;

        foreach ($period_types as $_type => $_period_info) {
            $format = $_period_info["format"];
            $unit   = $_period_info["unit"];

            $request = new CRequest();
            $request->addColumn("DATE_FORMAT(`file_date`, '$format')", "period");
            $request->addColumn("COUNT(`file_id`)", "count");
            $request->addColumn("SUM(`doc_size`)", "weight");
            $request->addColumn("MIN(`file_date`)", "date_min");
            $request->addColumn("MAX(`file_date`)", "date_max");
            $date_min = CMbDT::dateTime("- $deeper $unit", $now);
            $request->addWhereClause("file_date", " > '$date_min'");

            if (is_array($user_ids)) {
                $request->addWhereClause("author_id", CSQLDataSource::prepareIn($user_ids));
            }

            if ($object_class) {
                $request->addWhereClause("object_class", "= '$object_class'");
            }

            if ($category_id) {
                $request->addWhereClause("file_category_id", "= '$category_id'");
            }

            $request->addGroup("period");
            $results = $ds->loadHashAssoc($request->makeSelect($doc));

            foreach (range($depth, 0) as $i) {
                $period                   = CMbDT::transform("-$i $unit", $now, $format);
                $details[$_type][$period] = isset($results[$period]) ? $results[$period] : 0;
            }
        }

        return $details;
    }


    /**
     * @inheritdoc
     */
    public function getDiskUsage($user_id): array
    {
        $ds    = $this->_spec->ds;
        $query = "
      SELECT
        COUNT(`file_id`) AS `docs_count`,
        SUM(`doc_size`) AS `docs_weight`
      FROM `files_mediboard`
      WHERE `author_id` = '$user_id'
      GROUP BY `author_id`
      ORDER BY `docs_weight` DESC";

        return $ds->loadList($query);
    }

    /**
     * Check if this is an image
     *
     * @return bool
     */
    function isImage()
    {
        return strpos($this->file_type, "image") === 0;
    }

    /**
     * Get the CPatient ref from CFile TargetObject or null if not available
     *
     * @return CPatient|null
     */
    function getIndexablePatient()
    {
        $object = $this->loadTargetObject();

        if ($object instanceof CPatient) {
            return $object;
        }

        if ($object instanceof IPatientRelated) {
            return $object->loadRelPatient();
        }

        if ($object instanceof IIndexableObject) {
            return $object->getIndexablePatient();
        }

        return null;
    }

    /**
     * Get the praticien of CMbobject
     *
     * @return CMediusers
     */
    function getIndexablePraticien()
    {
        $object = $this->loadTargetObject();
        switch ($object->_class) {
            case "CConsultAnesth":
                $prat = $object->loadRefConsultation()->loadRefPraticien();
                break;
            case "CSejour":
                $prat = $object->loadRefSejour()->loadRefPraticien();
                break;
            default:
                $prat = (method_exists($object, 'loadRefPraticien')) ? $object->loadRefPraticien() : null;
        }

        return $prat;
    }

    /**
     * Loads the related fields for indexing datum
     *
     * @return array
     */
    function getIndexableData()
    {
        $array                     = [];
        $prat                      = $this->getIndexablePraticien();
        $array["id"]               = $this->_id;
        $array["author_id"]        = $this->author_id;
        $array["prat_id"]          = $prat->_id;
        $array["title"]            = utf8_encode($this->file_name);
        $array["body"]             = $this->getIndexableBody($this->_absolute_dir . "/" . $this->file_real_filename);
        $array["date"]             = str_replace("-", "/", $this->file_date);
        $array["function_id"]      = $prat->function_id;
        $array["group_id"]         = $prat->loadRefFunction()->group_id;
        $array["patient_id"]       = $this->getIndexablePatient()->_id;
        $array["object_ref_id"]    = $this->loadTargetObject()->_id;
        $array["object_ref_class"] = $this->loadTargetObject()->_class;
        $array["path"]             = $this->_file_path;
        $array["content_type"]     = $this->file_type;

        return $array;
    }

    /**
     * Redesign the content of the body you will index
     *
     * @param string $content The filename you want to index
     *
     * @return string
     */
    function getIndexableBody($content)
    {
        try {
            $parser = new CFileParser();
        } catch (Exception $e) {
            CApp::log($e->getMessage());

            return false;
        }

        return $parser->getContent($content);
    }

    /**
     * Shrink a PDF. If destination path, the file is overwritten
     *
     * @param string $file_path    Path to the PDF to shrink
     * @param string $dest_path    Optional file path destination
     * @param int    $shrink_level Shrink level (1 : font subset, 2: font subset and picture resampling)
     *
     * @return bool
     */
    static function shrinkPDF($file_path, $dest_path = "", $shrink_level = 1)
    {
        if (!is_file($file_path)) {
            return false;
        }

        $move_to_original = false;
        if (!$dest_path) {
            $dest_path        = dirname($file_path) . "/copy_" . basename($file_path);
            $move_to_original = true;
        }

        $root_dir = CAppUI::conf("root_dir");

        $command = $root_dir . "/modules/dPfiles/script/shrinkpdf.sh " . escapeshellarg($file_path) . " " .
            escapeshellarg($dest_path) . ($shrink_level == 2 ? " resample" : "");

        exec($command, $output, $res);

        if ($res === 0 && $move_to_original) {
            unlink($file_path);
            rename($dest_path, $file_path);
        }

        return $res === 0;
    }

    /**
     * Slice a PDF file from the $start page, for $length pages (or until the end of the document if not set)
     * and return the content of the result PDF
     *
     * @param CFile   $file   The CFile linking to the PDF to slice
     * @param integer $start  The number of the page from which the PDF will be sliced
     * @param integer $length If given and positive, the number of pages (at most) that the result PDF will contain.
     *                        If not set, the PDF will be starting from the $start page to the end of the document
     *
     * @return string|false
     */
    public static function slicePDF($file, $start, $length = null)
    {
        $s = strpos($file->file_type, 'pdf');
        $b = file_exists($file->_file_path);
        if (!$file || !$file->_id || strpos($file->file_type, 'pdf') === false || !file_exists($file->_file_path)) {
            return false;
        }

        try {
            $fpdi  = new FPDI();
            $pages = $fpdi->setSourceFile($file->_file_path);

            if ($start > $pages) {
                $start = $pages;
            }

            $count = 0;
            for ($i = $start; $i <= $pages; $i++) {
                $page = $fpdi->importPage($i);
                $fpdi->addPage();
                $fpdi->useTemplate($page);

                $count++;
                if ($length > 0 && $count == $length) {
                    break;
                }
            }

            return $fpdi->Output(null, 'S');
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Guess the type af a CFile
     *
     * @return void
     */
    function guessFileType()
    {
        if ($this->file_type === null) {
            return;
        }

        if (strpos($this->file_type, "pdf") !== false) {
            $this->_file_type = "pdf";
        } elseif (strpos($this->file_type, "image") !== false && $this->file_type !== "image/fabricjs") {
            $this->_file_type = "image";
        } elseif (strpos($this->file_type, "text") !== false || strpos($this->file_type, "rtf") !== false) {
            $this->_file_type = "text";
        } elseif (strpos($this->file_type, "excel") !== false || strpos($this->file_type, "spreadsheet") !== false) {
            $this->_file_type = "excel";
        } elseif (strpos($this->file_type, "word") !== false || strpos($this->file_type, "odt") !== false) {
            $this->_file_type = "word";
        }
    }

    /**
     * Perform file migration to the new filesystem structure
     *
     * @param int $limit The limit
     *
     * @return int $count Number of moved files
     */
    static function migrateFiles($limit)
    {
        $count = 0;

        // Get all classes directories
        $directories = glob(CFile::getDirectory() . '/C*', GLOB_ONLYDIR);
        foreach ($directories as $_directory) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($_directory, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            $count = self::migrateDirectory($files, $limit);
            CMbPath::rmEmptyDir($_directory);

            // Break when limit is reached
            if ($count >= $limit) {
                return $count;
            }
        }

        return $count;
    }

    /**
     * @return array
     */
    public function getPathInfo()
    {
        return pathinfo($this->file_name);
    }

    /**
     * Perform one directory migration
     *
     * @param RecursiveIteratorIterator $files An iterator object
     * @param int                       $limit File count limit
     *
     * @return int $count
     */
    private static function migrateDirectory($files, $limit)
    {
        $prefix_format = CAppUI::conf('dPfiles CFile prefix_format');
        $count         = 0;
        $fp            = fopen(CAppUI::conf('root_dir') . "/tmp/files-migration.log", "a");

        foreach ($files as $_file) {
            $pathname = $_file->getPathname();
            if (!is_dir($pathname)) {
                $filename      = $_file->getFilename();
                $file          = null;
                $_old_filename = null;

                if ($prefix_format) {
                    $_old_filename = $filename;

                    $file                     = new CFile();
                    $file->file_real_filename = $filename;
                    $file->loadMatchingObject();

                    // handle orphan files
                    if (!$file->_id) {
                        if ($hierarchy = CAppUI::conf('dPfiles CFile hierarchy')) {
                            $prefix = str_repeat("0", array_sum(explode(',', $hierarchy)));
                        } else {
                            $prefix = '000000';
                        }
                        $filename = $prefix . $file->file_real_filename;
                    } else {
                        // Prefix file according to format
                        $prefix                   = $file->getPrefix($file->file_date);
                        $filename                 = $prefix . $file->file_real_filename;
                        $file->file_real_filename = $filename;
                        if (!$file->rawStore()) {
                            trigger_error(CAppUI::tr("CFile-msg-store-failed %s", $filename), E_USER_WARNING);
                            break;
                        }
                    }
                }
                // Get new path
                $_absolute_dir = CFile::getPrivateDirectory() . self::getSubDir($filename);
                CMbPath::forceDir($_absolute_dir);

                if ($prefix_format) {
                    // Log file
                    $file_id  = $file->_id ? $file->_id : 'ORPHAN';
                    $dateTime = CMbDT::dateTime();
                    fwrite($fp, "$file_id,$dateTime,$pathname,$_absolute_dir/$filename\n");
                }

                if (rename($pathname, "$_absolute_dir/$filename")) {
                    $count++;
                } else {
                    // Rename error, rollback CFile modifications
                    if ($prefix_format) {
                        $file->file_real_filename = $_old_filename;
                        if (!$file->rawStore()) {
                            trigger_error(CAppUI::tr("CFile-msg-store-failed %s", $_old_filename), E_USER_WARNING);
                        }
                    }
                }
            } else {
                CMbPath::rmEmptyDir($pathname);
            }

            // Break when limit is reached
            if ($count >= $limit) {
                break;
            }
        }
        fclose($fp);

        return $count;
    }

    /**
     * Migrate some files to the new filesystem structure
     *
     * @return void
     */
    function migrateSome()
    {
        if (!$limit = $this->conf('migration_limit')) {
            return;
        }

        $mutex = new CMbMutex('migrate_file');
        $mutex->acquire(60);
        self::migrateFiles($limit);
        $mutex->release();
    }

    /**
     * Used when merging files with the "fast" mode, will rename files on the file system
     *
     * @param CStoredObject $from Source object
     * @param CStoredObject $to   Target object
     *
     * @return bool
     */
    function fastTransfer(CStoredObject $from, CStoredObject $to)
    {
        $sub_dir_from = "$from->_class/" . intval($from->_id / 1000);
        $files_from   = glob(self::getDirectory() . "/$sub_dir_from/$from->_id/*");

        $sub_dir_to = "$to->_class/" . intval($to->_id / 1000);
        $dir_to     = self::getDirectory() . "/$sub_dir_to/$to->_id/";

        if (count($files_from)) {
            CMbPath::forceDir($dir_to);

            foreach ($files_from as $_from) {
                rename($_from, $dir_to . basename($_from));
            }
        }

        return true;
    }

    /**
     * Parse prefix_format in order to get the prefix value
     *
     * @param string $date Date
     *
     * @return string|null Prefix
     */
    function getPrefix($date)
    {
        $prefix_format        = CAppUI::conf('dPfiles CFile prefix_format');
        $prefix_format_qualif = CAppUI::conf('dPfiles CFile prefix_format_qualif');

        if (CAppUI::conf("instance_role") == "qualif" && $prefix_format_qualif) {
            $prefix_format = $prefix_format_qualif;
        }

        if (!$prefix_format) {
            return null;
        }

        return preg_replace_callback(
            '/%[myYd]/',
            function ($matches) use ($date) {
                return CMbDT::format($date, $matches[0]);
            },
            $prefix_format
        );
    }


    /**
     * Prefix a file and stores it
     *
     * @return void
     */
    public function setPrefix()
    {
        $this->completeField('file_real_filename', 'file_date');

        if (CAppUI::conf('dPfiles CFile prefix_format')) {
            $prefix = $this->getPrefix($this->file_date);

            if (strpos($this->file_real_filename, $prefix) !== 0) {
                $this->file_real_filename = $prefix . $this->file_real_filename;
                $this->rawStore();
            }

            $this->updateFormFields();
        }
    }

    /**
     * Return the file sub directory from file real filename
     *
     * @param string $filename File real filename
     *
     * @return string
     */
    static function getSubDir($filename)
    {
        $indexes = [2, 2, 2];
        if ($hierarchy = CAppUI::conf('dPfiles CFile hierarchy')) {
            // Avoid hierarchy containing '0'
            (strpos($hierarchy, '0') !== false) ?: ($indexes = explode(',', $hierarchy));
        }

        // Récupère la partie fixe du chemin du fichier (ou null si la configuration n'est pas utilisée)
        $last_dir = self::getLastDir();

        $sub_dir = substr($filename, 0, $indexes[0]) . "/" . substr($filename, $indexes[0], $indexes[1]) . "/";
        // On force le $last_dir de qualif si il y en a un de défini au lieu de juste découper le nom du fichier
        $sub_dir .= ($last_dir) ?: substr($filename, $indexes[0] + $indexes[1], $indexes[2]);

        return $sub_dir;
    }

    /**
     * Récupère le répertoire fixe pour la séparation P°/Q° des emplacements de fichiers
     * Renvoie null si la configuration n'est pas utilisée
     *
     * @return string|null
     */
    private static function getLastDir()
    {
        $cache = new Cache('CFile.getLastDir', 'last-dir', Cache::INNER);

        if ($value = $cache->get()) {
            return $value;
        }

        // Il faut que le calcul du chemin des fichiers prenne en compte le rôle de l'instance
        $prefix_format        = CAppUI::conf('dPfiles CFile prefix_format');
        $prefix_format_qualif = CAppUI::conf('dPfiles CFile prefix_format_qualif');

        if (CAppUI::conf("instance_role") == "qualif" && $prefix_format_qualif) {
            $prefix_format = $prefix_format_qualif;
        }

        // $last_dir correspond au motif libre présent dans le format de préfix ($indexes[2])
        $last_dir = null;
        if ($prefix_format) {
            $last_dir = preg_replace("/(%[myYd])+/", '', $prefix_format);
        }

        return $cache->put($last_dir);
    }

    /**
     * Checks if given user has read this file
     *
     * @param integer $user_id CUser ID
     *
     * @return bool|int
     */
    function hasRead($user_id = null)
    {
        if (!$this->_id) {
            return false;
        }

        $user_id = ($user_id) ?: CUser::get()->_id;

        $read          = new CFileUserView();
        $read->user_id = $user_id;
        $read->file_id = $this->_id;

        return ($read->countMatchingList() > 0);
    }

    /**
     * Get the CFile private directory.
     * Init the directory if needed.
     *
     * @return string
     */
    public static function getPrivateDirectory()
    {
        if (!self::$is_directory_registered) {
            self::registerPrivateDirectory();
        }

        return self::$directory_private ?: (self::$directory . "/private/");
    }

    /**
     * Get the CFile directory.
     * Init the directory if needed.
     *
     * @return string
     */
    public static function getDirectory(): ?string
    {
        if (!self::$is_directory_registered) {
            self::registerPrivateDirectory();
        }

        return self::$directory;
    }

    /**
     * Insert image signature into a PDF
     *
     * @param string $file_guid      File guid
     * @param string $base_64        Base 64 encoded image
     * @param string $base_64_format Image extension
     *
     * @return bool
     */
    static function insertSignature($file_guid = null, $base_64 = null, $base_64_format = "png")
    {
        if (!$file_guid || $base_64 === null) {
            return false;
        }

        /** @var CFile $file */
        $file = CMbObject::loadFromGuid($file_guid);

        if (!$file || !$file->_id) {
            return false;
        }

        $temp_image = tempnam("tmp/", "signature");

        // Thumb creation needs extension in path
        rename($temp_image, $temp_image . ".$base_64_format");
        $temp_image = $temp_image . ".$base_64_format";

        file_put_contents($temp_image, base64_decode($base_64));

        $temp_file             = new CFile();
        $temp_file->_file_path = $temp_image;
        $temp_file->_file_type = $base_64_format;

        file_put_contents($temp_image, CThumbnail::displayThumb($temp_file, null, 200));

        $fpdi       = new FPDI();
        $page_count = $fpdi->setSourceFile($file->_file_path);

        for ($i = 1; $i <= $page_count; $i++) {
            $tpl = $fpdi->importPage($i);
            $fpdi->addPage();
            $fpdi->useTemplate($tpl);

            // Signature insertion on every page
            $fpdi->Image($temp_image, 150, 260);
        }

        file_put_contents($file->_file_path, $fpdi->Output(null, "S"));
        $file->doc_size = filesize($file->_file_path);
        $file->store();

        unlink($temp_image);

        return true;
    }

    /**
     * Insert "REFUSE" in an file
     *
     * @param string $file_guid File guid
     *
     * @return bool
     */
    static function refuseFile($file_guid = null)
    {
        if (!$file_guid) {
            return false;
        }

        /** @var CFile $file */
        $file = CMbObject::loadFromGuid($file_guid);

        if (!$file || !$file->_id) {
            return false;
        }

        $pdf = new FPDI;

        $page_count = $pdf->setSourceFile($file->_file_path);

        for ($_page = 1; $_page <= $page_count; $_page++) {
            $templateId = $pdf->importPage($_page);
            $pdf->AddPage();
            $pdf->useTemplate($templateId);

            //filigrane REFUSE
            $pdf->SetFont('Arial', 'I', 140);
            $pdf->SetTextColor(255, 0, 0);
            $pdf->SetXY(25, 135);
            $pdf->_out('Q');

            $angle = 45;

            $x = 250;
            $y = 50;

            $angle *= M_PI / 180;
            $c     = cos($angle);
            $s     = sin($angle);
            $cx    = $x;
            $cy    = (500 - $y);

            $pdf->_out(
                sprintf(
                    'q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm',
                    $c,
                    $s,
                    -$s,
                    $c,
                    $cx,
                    $cy,
                    -$cx,
                    -$cy
                )
            );
            $pdf->Write(0, "REFUSE");
        }
        file_put_contents($file->_file_path, $pdf->Output(null, "S"));
        $file->doc_size = filesize($file->_file_path);
        $file->store();

        return true;
    }

    static function mergeBase64toPdf(array $bases64 = [])
    {
        $pdf = new FPDF();

        foreach ($bases64 as $b64) {
            [$type, $data] = explode(';', $b64);
            [, $data] = explode(',', $data);
            $data = base64_decode($data);

            $tmp_file = tempnam("./tmp", "fine");
            $tmp_name = tempnam("./tmp", "fine");

            rename($tmp_file, $tmp_file . ".pdf");
            rename($tmp_name, $tmp_name . "." . explode("/", $type)[1]);

            $tmp_file .= ".pdf";
            $tmp_name .= "." . explode("/", $type)[1];

            file_put_contents($tmp_name, $data);

            $pdf->addPage();
            $pdf->image($tmp_name, 0, 0, 210);

            unlink($tmp_name);
            unlink($tmp_file);
        }

        $pdf->Output($tmp_file, 'F');
        $content = $pdf->Output($tmp_file, 'S');

        return $content;
    }

    static function mergeBase64Pictures($bases64 = [], $type = "jpg")
    {
        if (!count($bases64)) {
            return null;
        }

        $pdf_merger = new CMbPDFMerger();

        $tmp_files = [];

        foreach ($bases64 as $base64) {
            $tmp_file = tempnam("./tmp", "fine");
            $tmp_name = tempnam("./tmp", "fine");

            rename($tmp_file, $tmp_file . ".pdf");
            rename($tmp_name, $tmp_name . ".$type");

            $tmp_file .= ".pdf";
            $tmp_name .= ".$type";

            file_put_contents($tmp_name, base64_decode($base64));
            exec("convert \"" . $tmp_name . "\" $tmp_file", $result);

            $tmp_files[] = $tmp_file;

            $pdf_merger->addPDF($tmp_file);
        }

        $content = $pdf_merger->merge("string");

        foreach ($tmp_files as $_tmp_file) {
            unlink($_tmp_file);
        }

        return $content;
    }

    /**
     * @inheritdoc
     */
    public function isExportable($prat_ids = [], $date_min = null, $date_max = null, ...$additional_args)
    {
        $context = $this->loadTargetObject();

        return $context->isExportable($prat_ids, $date_min, $date_max);
    }

    /**
     * Checks if the signature file is parameterized and present
     *
     * @return bool TRUE if no signature file is provided of if signature file is provided and readable, FALSE
     *              otherwise.
     */
    static public function checkSignatureFile()
    {
        if (!CAppUI::conf('dPfiles CFile signature_filename')) {
            return true;
        }

        $signature_file = static::getSignatureFilePath();

        // We do not just test file existence because of some mount point net-related side effects, but instead readability of the file
        return (is_readable($signature_file));
    }

    /**
     * Get the signature absolute filepath
     *
     * @return string
     */
    static private function getSignatureFilePath()
    {
        return rtrim(static::getPrivateDirectory(), '/') . '/' . CAppUI::conf('dPfiles CFile signature_filename');
    }

    /**
     * @inheritDoc
     */
    public function matchForImport(MatcherVisitorInterface $matcher): ImportableInterface
    {
        return $matcher->matchFile($this);
    }

    /**
     * @inheritDoc
     */
    public function persistForImport(PersisterVisitorInterface $persister): ImportableInterface
    {
        return $persister->persistObject($this);
    }

    /**
     * @return mixed
     */
    public function getMovePath()
    {
        return $this->_move_path;
    }

    /**
     * @return Item|null
     * @throws ApiException
     * @throws Exception
     */
    public function getResourceContext(): ?Item
    {
        $context = $this->loadTargetObject();
        if (!$context || !$context->_id) {
            return null;
        }

        return new Item($context);
    }

    /**
     * @return Item|null
     * @throws ApiException
     * @throws Exception
     */
    public function getResourceCategory(): ?Item
    {
        $category = $this->loadRefCategory();
        if (!$category || !$category->_id) {
            return null;
        }

        return new Item($category);
    }

    /**
     * @return Item|null
     * @throws ApiException
     * @throws Exception
     */
    public function getResourceNatureFile(): ?Item
    {
        $nature_file               = new CNatureFile();
        $nature_file->_id          = $this->nature_file_id;
        $nature_file->object_class = $this->object_class;
        $nature_file->object_id    = $this->object_id;

        if (!$nature_file->loadMatchingObjectEsc()) {
            return null;
        }

        return new Item($nature_file);
    }

    private function sanitizeFileRealFilename(): void
    {
        $this->file_real_filename = preg_replace('/[\W\s_]/', '', $this->file_real_filename ?? "");
    }

    public static function isAllowedFileType(string $file_path, ?string $ext = null): bool
    {
        if (!$ext) {
            $ext = CMbPath::getExtension($file_path);
        }

        if ($ext && !self::isAllowedExtension($ext)) {
            return false;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE, '');
        $mime  = $finfo->file($file_path);

        if (!in_array($mime, self::ALLOWED_MIME_TYPES)) {
            return false;
        }

        return true;
    }

    public static function isAllowedFileContent(string $file_content, ?string $ext = null): bool
    {
        if ($ext && !self::isAllowedExtension($ext)) {
            return false;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE, '');
        $mime  = $finfo->buffer($file_content);

        if (!in_array($mime, self::ALLOWED_MIME_TYPES)) {
            return false;
        }

        return true;
    }

    public static function isAllowedExtension(string $ext): bool
    {
        return in_array(CMbString::lower($ext), self::ALLOWED_EXTENSIONS);
    }

    /**
     * Load nature file
     *
     * @return CNatureFile
     * @throws Exception
     */
    public function loadRefNatureFile()
    {
        return $this->_ref_nature_file = $this->loadFwdRef("nature_file_id", true);
    }

    /**
     * @throws CMbException
     */
    private function storeNewEncryption()
    {
        $this->_object_encryption->hash         = '';
        $this->_object_encryption->object_class = $this->_class;
        $this->_object_encryption->object_id    = $this->_id;

        if ($msg = $this->_object_encryption->store()) {
            throw new CMbException($msg);
        }
    }

    protected function createObjectEncryption(CObjectEncryption $object_encrytion, string $key_name): CObjectEncryption
    {
        $object_encrytion = $this->createObjectEncryptionTrait($object_encrytion, $key_name);

        $object_encrytion->hash         = $this->file_real_filename;
        $object_encrytion->object_class = $this->_class;

        if ($msg = $object_encrytion->store()) {
            throw new CMbException($msg);
        }

        return $object_encrytion;
    }

    public static function registerPrivateDirectory(): void
    {
        // We have to replace the backslashes with slashes because of thumbnails on Windows
        CFile::$directory  = str_replace('\\', '/', realpath(CAppUI::conf("dPfiles CFile upload_directory")));
        $directory_private = CAppUI::conf("dPfiles CFile upload_directory_private");
        if ($directory_private && is_dir($directory_private)) {
            CFile::$directory_private = rtrim(str_replace('\\', '/', realpath($directory_private)), '/') . '/';
        }
    }

    /**
     * Nommage des fichiers PDF de compte-rendu en accord avec la norme ECO.2.1.6
     *
     * @return void
     * @throws Exception
     */
    public function setCDAName(): void
    {
        if ($this->object_class !== 'CCompteRendu') {
            return;
        }

        /** @var CCompteRendu $cr */
        $cr = $this->loadTargetObject();

        if (($cr->object_class === 'CPatient') || !$cr->type_doc_dmp) {
            return;
        }

        $patient = $cr->getPatient();
        $context = $cr->loadTargetObject();

        switch (get_class($context)) {
            case CSejour::class:
                $date_acte = $context->entree;
                break;

            case CConsultation::class:
                $context->loadRefPlageConsult();
                $date_acte = $context->_date;
                break;

            case COperation::class:
                $date_acte = $context->debut_op;
                break;

            default:
                return;
        }

        $date_acte = $date_acte ? CMbDT::transform(null, $date_acte, '%Y%m%d') : null;

        $type_doc = substr(CAppUI::tr('CCompteRendu.type_doc_dmp.' . $cr->type_doc_dmp), 0, 40);

        $nom    = CMbString::upper($patient->nom_jeune_fille ?: $patient->nom);
        $prenom = $patient->prenom;

        $this->file_name =
            ($date_acte ? ($date_acte . '_') : '') . $type_doc . '_' . $nom . '_' . $prenom . '.pdf';
    }

    /**
     * @inheritDoc
     */
    public function cloneFrom(CModelObject $object): void
    {
        parent::cloneFrom($object);

        $this->file_real_filename = null;
        $this->fillFields();

        $this->setContent($object->getBinaryContent());
    }
}
