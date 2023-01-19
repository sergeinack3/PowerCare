<?php

/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Manager;

use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbPath;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\Manager\Exceptions\FileManagerException;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Files\CFileTraceability;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * trait CSejourTrait
 * Sejour utilities EAI
 */
class FileManager
{
    /** @var string */
    public const STRATEGY_FILENAME_DATE = 'datefile_name';
    /** @var string */
    public const STRATEGY_FILENAME_TIMESTAMP_CATEGORY = 'timestamp_datefile_category';
    /** @var string */
    public const STRATEGY_FILENAME_DATE_CATEGORY = 'datefile_category';
    /** @var string */
    public const STRATEGY_FILENAME_DEFAULT = 'name';

    /** @var string[] */
    public const STRATEGIES_FILENAME = [
        self::STRATEGY_FILENAME_DATE,
        self::STRATEGY_FILENAME_TIMESTAMP_CATEGORY,
        self::STRATEGY_FILENAME_DATE_CATEGORY,
        self::STRATEGY_FILENAME_DEFAULT,
    ];

    private string             $strategy_filename;
    private ?CFilesCategory    $category     = null;
    private ?string            $type_dmp     = null;
    private ?string            $tag_idex     = null;
    private ?CFileTraceability $traceability = null;
    /** @var bool|string */
    private $load_matching = false;

    /**
     * @param string $strategy_filename the way to generate file name of file
     */
    public function __construct(string $strategy_filename = self::STRATEGY_FILENAME_DEFAULT)
    {
        $this->strategy_filename = $strategy_filename;
    }

    /**
     * Try to store the file with option given and make trace if enabled
     *
     * @param CFile $file
     *
     * @return CFile
     * @throws FileManagerException
     */
    public function store(CFile $file): CFile
    {
        if (!$file->getContent()) {
            throw new FileManagerException(FileManagerException::CONTENT_EMPTY);
        }

        // load matching file
        if (!empty($this->load_matching)) {
            if ($this->load_matching === true) {
                $file = $this->loadMatchingFile($file);
            } else {
                $file = $this->loadMatchingFromIdex($file);
            }
        }

        // set category if given
        if ($this->category) {
            $file->file_category_id = $this->category->_id;
        }

        // update meta
        $file->file_name    = $this->generateName($file);
        $file->doc_size     = strlen($file->getContent());
        $file->type_doc_dmp = $file->type_doc_dmp ?: $this->type_dmp;
        $file->fillFields();
        $file->updateFormFields();

        if ($traceability = $this->traceability) {
            // no target : we link the file to the trace
            if (!$file->object_class || !$file->object_id) {
                $traceability->store();
                $file->setObject($traceability);
            } else {
                // else we store the trace and status is set to "auto" (found by interop)
                $traceability->status = "auto";
                $traceability->store();
            }
        }

        // no target : exception
        if (!$file->object_class || !$file->object_id) {
            throw new FileManagerException(FileManagerException::NO_TARGET_OBJECT);
        }

        // no handler for handle of file
        $file->_no_synchro_eai = true;

        // store file
        if ($msg = $file->store()) {
            if ($traceability) {
                $traceability->delete();
            }

            throw new FileManagerException(FileManagerException::INVALID_STORE_FILE, $msg);
        }

        // if traceability enabled, link it to the file
        if ($traceability) {
            $traceability->setObject($file);
            $traceability->store();
        }

        // mark with an idex the file
        if ($this->load_matching && is_string($this->load_matching) && $this->tag_idex) {
            $this->mark($file);
        }

        return $file;
    }

    /**
     * Multiple way to generate filename of file
     *
     * @param CFile $file
     *
     * @return string
     */
    private function generateName(CFile $file): string
    {
        $name          = $file->file_name;
        if (!$name) {
            $name =  uniqid(rand(), true);
            $name = $name . ($file->file_type ? '.' . CMbPath::getExtensionByMimeType($file->file_type) : '');
        }
        $date_file     = $file->file_date ?: CMbDT::dateTime();
        $category_name = null;
        if ($this->category) {
            $category_name = $this->category->nom;
        }

        switch ($this->strategy_filename) {
            case self::STRATEGY_FILENAME_DATE_CATEGORY:
                $file_name = CMbDT::dateTime($date_file) . "_" . $category_name . '_' . $name;
                break;

            case self::STRATEGY_FILENAME_DATE:
                $file_name = CMbDT::dateTime($date_file) . '_' . $name;
                break;

            case self::STRATEGY_FILENAME_TIMESTAMP_CATEGORY:
                $file_name = CMbDT::dateTime($date_file)
                    . '_' . $category_name
                    . '_' . CMbDT::toTimestamp(CMbDT::dateTime())
                    . '_' . $name;
                break;

            case self::STRATEGY_FILENAME_DEFAULT:
            default:
                $file_name = $name;
        }

        return $file_name;
    }

    /**
     * If given, the file will be associate to this category
     *
     * @param CFilesCategory|null $category
     *
     * @return $this
     */
    public function setCategory(?CFilesCategory $category): self
    {
        if ($category && $category->_id) {
            $this->category = $category;
        }

        return $this;
    }

    /**
     * Try to find code for dmp file, if we found it, it will be set on the file
     *
     * @param string|null $type_dmp
     *
     * @return $this
     */
    public function setTypeDmp(?string $type_dmp): self
    {
        if ($type_dmp && CModule::getActive('dmp') && CModule::getActive('interopResources')) {
            $datas_type_dmp = CANSValueSet::loadEntries(
                "typeCode",
                $type_dmp
            );

            if (CMbArray::get($datas_type_dmp, "codeSystem") && CMbArray::get($datas_type_dmp, "code")) {
                $this->type_dmp = CMbArray::get($datas_type_dmp, "codeSystem")
                    . "^" . CMbArray::get($datas_type_dmp, "code");
            }
        }

        return $this;
    }

    /**
     * Allow to enable loadMatching before store the file
     * False for disable loadMatching
     * True for load matching on target_object, file_name and file_type
     * String for load from Idex (id partner)
     *
     * @param bool|string $load_matching
     *
     * @return $this
     */
    public function enableLoadMatching($load_matching): self
    {
        $this->load_matching = $load_matching;

        return $this;
    }

    /**
     * Allow to load match a file from target_object, file_name and file_type
     *
     * @param CFile $file
     *
     * @return CFile
     */
    private function loadMatchingFile(CFile $file): CFile
    {
        if ($file->_id) {
            return $file;
        }

        $matching_file               = new CFile();
        $matching_file->object_id    = $file->object_id;
        $matching_file->object_class = $file->object_class;
        $matching_file->file_name    = $this->generateName($file);
        $matching_file->file_type    = $file->file_type;
        $matching_file->loadMatchingObjectEsc();

        if ($matching_file->_id) {
            $file->_id = $matching_file->_id;
        }

        return $file;
    }

    /**
     * Give a CFileTraceability allow to create trace on CFile
     *
     * @param CFileTraceability|null $traceability
     *
     * @return void
     */
    public function enableTraceability(?CFileTraceability $traceability): self
    {
        $this->traceability = $traceability;

        return $this;
    }

    /**
     * Get file traceability
     *
     * @return CFileTraceability|null
     */
    public function getTraceability(): ?CFileTraceability
    {
        return $this->traceability;
    }

    /**
     * Try to found the file with an idex
     *
     * @param CFile $file
     *
     * @return CFile
     * @throws FileManagerException
     */
    private function loadMatchingFromIdex(CFile $file): CFile
    {
        if ((!$tag = $this->tag_idex) || !$file->object_id || !$file->object_class) {
            return $file;
        }

        $file_matching = new CFile();
        $idex          = CIdSante400::getMatch($file->_class, $tag, $this->load_matching);
        if ($idex && $idex->_id) {
            $file_matching->load($idex->object_id);
            // Vérification que le contexte est le même
            if ($file_matching->_id) {
                if (($file_matching->object_id != $file->object_id) || ($file_matching->object_class != $file->object_class)) {
                    throw new FileManagerException(FileManagerException::FILE_CONTEXT_DIVERGENCE);
                }

                // update file but keep values already set
                $file->_id = $file_matching->_id;
            }
        }

        return $file;
    }

    /**
     * Mark file with an IDEX
     *
     * @param CFile $file
     *
     * @return void
     * @throws FileManagerException
     */
    private function mark(CFile $file): void
    {
        $idex = CIdSante400::getMatch($file->_class, $this->tag_idex, $this->load_matching);
        if (!$idex || !$idex->_id) {
            $idex->object_id = $file->_id;
            if ($msg = $idex->store()) {
                throw new FileManagerException(FileManagerException::INVALID_STORE_IDEX, $msg);
            }
        }
    }

    /**
     * @param string|null $tag_idex
     *
     * @return FileManager
     */
    public function setTag(?string $tag_idex): self
    {
        $this->tag_idex = $tag_idex;

        return $this;
    }
}
