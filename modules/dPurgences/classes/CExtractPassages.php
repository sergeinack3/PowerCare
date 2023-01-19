<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSQLDataSource;
use Ox\Interop\Eai\CEAITools;
use Ox\Interop\Ror\CRORException;
use Ox\Interop\Ror\CRORFactory;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;

/**
 * Class CExtractPassages
 */
class CExtractPassages extends CMbObject
{
    /** @var int */
    public const LIMIT_NOTIFY_ERROR = 3;

    // DB Table key
    public $extract_passages_id;

    // DB Fields
    public $date_extract;
    public $debut_selection;
    public $fin_selection;
    public $date_echange;
    public $nb_tentatives;
    public $message_xml;
    public $message_any;
    public $message_valide;
    public $type;
    public $group_id;
    public $rpu_sender;

    // Form fields
    public $_nb_rpus;
    public $_nb_urgences;

    // Filter fields
    public $_date_min;
    public $_date_max;

    /** @var CGroups */
    public $_ref_group;

    /**
     * Get types active in function of region set in configurations
     *
     * @param bool   $return_str choice for return function
     * @param string $glue       glue to paste elements
     *
     * @return string|array
     * @throws CRORException
     */
    public static function getTypesActive($return_str = false, $glue = '|')
    {
        $sender = CRORFactory::getSender();

        if ($return_str) {
            return implode($glue, $sender::TYPES);
        }

        return $sender::TYPES;
    }

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec           = parent::getSpec();
        $spec->table    = 'extract_passages';
        $spec->key      = 'extract_passages_id';
        $spec->loggable = false;

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                    = parent::getProps();
        $props["date_extract"]    = "dateTime notNull";
        $props["debut_selection"] = "dateTime notNull";
        $props["fin_selection"]   = "dateTime notNull";
        $props["date_echange"]    = "dateTime";
        $props["nb_tentatives"]   = "num";
        $props["message_xml"]     = "xml show|0";
        $props["message_any"]     = "text";
        $props["message_valide"]  = "bool";
        $props["type"]            = "enum list|rpu|urg|activite|uhcd|tension|deces|lits|litsChauds default|rpu";
        $props["group_id"]        = "ref notNull class|CGroups back|extract_passages";
        $props["rpu_sender"]      = "str";

        $props["_nb_rpus"]  = "num";
        $props["_date_min"] = "dateTime";
        $props["_date_max"] = "dateTime";

        return $props;
    }

    /**
     * Load group
     *
     * @return CGroups
     */
    function loadRefGroup()
    {
        return $this->_ref_group = $this->loadFwdRef("group_id", true);
    }

    /**
     * @inheritdoc
     */
    function loadRefsBack()
    {
        $this->countDocItems();
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $this->_nb_rpus = $this->countRPUs();
    }

    /**
     * Count RPU
     *
     * @return int
     */
    function countRPUs()
    {
        $rpu_passage                      = new CRPUPassage();
        $rpu_passage->extract_passages_id = $this->_id;

        return $rpu_passage->countMatchingList();
    }

    /**
     * @inheritdoc
     */
    public function store()
    {
        $this->completeField('message_valide');
        if ($this->_id) {
            if (!$this->fieldModified('message_xml')) {
                $this->message_xml = null;
            }
            if (!$this->fieldModified('message_any')) {
                $this->message_any = null;
            }
        } else {
            /* Possible purge when creating a CExtractPassages */
            CApp::doProbably(
                CAppUI::conf('dPurgences CExtractPassages purge_probability'),
                [$this, 'purgeAllSome']
            );
        }

        if ($this->fieldModified('message_valide') && $this->message_valide === 0) {
            $this->notifyError();
        }

        return parent::store();
    }

    /**
     * @return bool
     */
    public function notifyError(): bool
    {
        if (!CAppUI::gconf('ror General send_error_mail', $this->group_id)) {
            return false;
        }

        if (!$this->_id) {
            return false;
        }

        try {
            $this->nb_tentatives = $this->nb_tentatives ? $this->nb_tentatives + 1 : 1;
            if ($msg = $this->store()) {
                return false;
            }
            if ($this->nb_tentatives <= self::LIMIT_NOTIFY_ERROR) {
                return false;
            }

            return $this->notifyRPUError();
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function notifyRPUError(): bool
    {
        return (new CEAITools())->notifyRPUError($this);
    }

    /**
     * Purge the CExchangeDataFormat older than the configured threshold
     *
     * @return bool|resource|void
     */
    function purgeAllSome()
    {
        $this->purgeEmptySome();
        $this->purgeDeleteSome();
    }

    /**
     * Purge the CExtractPassages older than the configured threshold
     *
     * @return bool|resource|void
     */
    function purgeEmptySome()
    {
        $purge_empty_threshold = CAppUI::conf('dPurgences CExtractPassages purge_empty_threshold');

        $date  = CMbDT::dateTime("- {$purge_empty_threshold} days");
        $limit = CAppUI::conf("dPurgences CExtractPassages purge_probability") * 10;
        if (!$limit) {
            return null;
        }

        $where                 = [];
        $where[]               = "message_xml IS NOT NULL OR message_any IS NOT NULL";
        $where["date_extract"] = " < '$date'";

        $order = "date_extract ASC";

        // Marquage des passages
        $ds                      = $this->getDS();
        $extract_passages_ids    = $this->loadIds($where, $order, $limit);
        $in_extract_passages_ids = CSQLDataSource::prepareIn($extract_passages_ids);

        $query = "UPDATE `{$this->_spec->table}` 
              SET `message_xml` = NULL, `message_any` = NULL
              WHERE `{$this->_spec->key}` $in_extract_passages_ids";

        $ds->exec($query);
    }

    /**
     * Purge the CExtractPassages older than the configured threshold
     *
     * @return bool|resource|void
     */
    function purgeDeleteSome()
    {
        $purge_delete_threshold = CAppUI::conf('dPurgences CExtractPassages purge_delete_threshold');

        $date  = CMbDT::dateTime("- {$purge_delete_threshold} days");
        $limit = CAppUI::conf("dPurgences CExtractPassages purge_probability") * 10;
        if (!$limit) {
            return null;
        }

        $where                 = [];
        $where[]               = "message_xml IS NULL OR message_any IS NULL";
        $where["date_extract"] = " < '$date'";

        $order = "date_extract ASC";

        $extract_passages  = new CExtractPassages();
        $extracts_passages = $extract_passages->loadList($where, $order, $limit);
        foreach ($extracts_passages as $_extract_passage) {
            // Suppression du CFile
            $_extract_passage->loadRefsFiles();
            foreach ($_extract_passage->_ref_files as $_file) {
                /** @var CFile $_file */
                $_file->delete();
            }

            // Suppression des RPU du passage
            $where                        = [];
            $where["extract_passages_id"] = " = '$_extract_passage->_id'";

            $rpu_passage = new CRPUPassage();
            $passage_ids = $rpu_passage->loadIds($where);
            $rpu_passage->deleteAll($passage_ids);

            // Suppression du passage
            $_extract_passage->delete();
        }
    }

    /**
     * Store a CFile linked to $this
     *
     * @param string $filename File name
     * @param string $filedata File contents
     *
     * @return bool
     */
    function addFile($filename, $filedata)
    {
        if (!$filedata) {
            return false;
        }

        $file = new CFile();
        $file->setObject($this);
        $file->file_name = $filename;
        $file->file_type = "text/plain";
        $file->doc_size  = strlen($filedata);
        $file->author_id = CAppUI::$instance->user_id;
        $file->fillFields();
        $file->setContent($filedata);

        if ($file->store()) {
            return false;
        }

        return true;
    }
}
