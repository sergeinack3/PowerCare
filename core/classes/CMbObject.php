<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;
use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CExchangeDataFormat;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CAideSaisie;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CContextDoc;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesCategory;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Notifications\CNotification;
use Ox\Mediboard\Personnel\CAffectationPersonnel;
use Ox\Mediboard\Personnel\CPersonnel;
use Ox\Mediboard\Sante400\CHyperTextLink;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\CAlert;
use Ox\Mediboard\System\CNote;
use Ox\Mediboard\System\CTagItem;
use Ox\Mediboard\System\Forms\CExLink;

/**
 * Handles: notes, documents, aides, views, model templates, echanges, idex, configurations affectations personnels (!)
 *
 * @abstract Mediboard business object layer
 */
class CMbObject extends CStoredObject
{

    /** @var string */
    public const RELATION_FILES = 'files';

    public $_aides     = []; // Aides à la saisie
    public $_aides_new = []; // Nouveau tableau des aides (sans hierarchie)

    /** @var CAideSaisie[][][] */
    public $_aides_all_depends;

    public $_nb_files_docs          = 0;
    public $_nb_files               = 0;
    public $_nb_cancelled_files     = 0;
    public $_nb_cancelled_docs      = 0;
    public $_nb_docs                = 0;
    public $_nb_forms               = 0;
    public $_alert_docs             = 0;
    public $_locked_alert_docs      = 0;
    public $_nb_exchanges;
    public $_nb_exchanges_by_format = [];
    public $_degree_notes;
    public $_count_alerts_not_handled;

    /** @var CIdSante400 */
    public $_ref_last_id400;

    /** @var CNote[] */
    public $_ref_notes = [];

    /** @var CCompteRendu[] */
    public $_ref_documents = [];

    /** @var CCompteRendu[] */
    public $_ref_documents_by_cat = [];

    /** @var CFile[] */
    public $_ref_files = [];

    /** @var CFile[][] */
    public $_ref_files_by_cat = [];

    /** @var CFile[] */
    public $_ref_named_files = [];

    public $_ref_forms = [];

    /** @var CTagItem[] */
    public $_ref_tag_items = [];

    /** @var CHyperTextLink[] */
    public $_ref_hypertext_links = [];

    /** @var CDocumentItem[][] */
    public $_refs_docitems_by_cat = [];

    /** @var CDocumentItem[] */
    public $_refs_docitems = [];

    /** @var CMbObjectConfig */
    public $_ref_object_configs;

    /** @var CNotification */
    public $_ref_notification;

    /** @var CNotification[] */
    public $_ref_notifications;

    /** @var CAlert[] */
    public $_refs_alerts_not_handled = [];

    public $_ref_affectations_personnel;
    public $_count_affectations_personnel;

    public $_all_docs;

    // Doctolib
    /** @var  CIdSante400 */
    public $_ref_doctolib_idex;

    // OX SIH Cabinet
    /** @var  CIdSante400 */
    public $_ref_sih_cabinet_idex;
    public $_ref_cabinet_sih_idex;

    // AppFine
    /** @var  CIdSante400 */
    public $_ref_appFine_idex;

    /** @var  CIdSante400 */
    public $_ref_appFine_idex_consult;

    public $_docitems_guid;
    public $_count_docitems = 0;

    /**
     * Count alerts
     *
     * @param string $level Level
     * @param string $tag   Tag
     *
     * @return int
     */
    function countAlertsNotHandled($level = null, $tag = null)
    {
        $alert          = new CAlert();
        $alert->handled = "0";
        $alert->setObject($this);
        $alert->level = $level;
        $alert->tag   = $tag;

        return $this->_count_alerts_not_handled = $alert->countMatchingList();
    }

    /**
     * Chargement des alertes non traitées
     *
     * @param string $level Niveau des alertes
     * @param string $tag   Tag des alertes
     * @param int    $perm  Permission
     *
     * @return CStoredObject[]
     */
    function loadAlertsNotHandled($level = null, $tag = null, $perm = PERM_READ)
    {
        $alert          = new CAlert();
        $alert->handled = "0";
        $alert->setObject($this);
        $alert->level                   = $level;
        $alert->tag                     = $tag;
        $this->_refs_alerts_not_handled = $alert->loadMatchingList();
        self::filterByPerm($this->_refs_alerts_not_handled, $perm);

        return $this->_refs_alerts_not_handled;
    }

    /**
     * Préchargement de masse des notes sur une collection
     *
     * @param self[] $objects Array of objects
     *
     * @return CNote[]
     */
    static function massLoadRefsNotes($objects)
    {
        return self::massLoadBackRefs($objects, "notes");
    }

    /**
     * Chargement des notes sur l'objet
     *
     * @param int $perm One of PERM_READ | PERM_EDIT
     *
     * @return int Note count
     */
    function loadRefsNotes($perm = PERM_READ)
    {
        $this->_ref_notes    = [];
        $this->_degree_notes = null;
        $notes_levels        = [];

        if ($this->_id) {
            $this->_ref_notes = $this->loadBackRefs("notes");
            self::filterByPerm($this->_ref_notes, $perm);

            // Find present levels
            foreach ($this->_ref_notes as $_note) {
                /** @var CNote $_note */
                $notes_levels[$_note->degre] = true;
            }

            // Note highest level
            if (isset($notes_levels["low"])) {
                $this->_degree_notes = "low";
            }

            if (isset($notes_levels["medium"])) {
                $this->_degree_notes = "medium";
            }

            if (isset($notes_levels["high"])) {
                $this->_degree_notes = "high";
            }
        }

        return count($this->_ref_notes);
    }

    /**
     * Load files for object with PERM_READ
     *
     * @param array $where          Optional conditions
     * @param array $with_cancelled With cancelled docs
     *
     * @return int|null Files count, null if unavailable
     */
    function loadRefsFiles($where = [], bool $with_cancelled = true)
    {
        $this->_nb_cancelled_files = 0;
        if (null == $this->_ref_files = $this->loadBackRefs("files", "file_name", null, null, null, null, null, $where)) {
            return null;
        }

        // Read permission
        foreach ($this->_ref_files as $_file) {
            if (!$with_cancelled && $_file->annule) {
                unset($this->_ref_files[$_file->_id]);
                continue;
            }
            /** @var CFile $_file */
            if (!$_file->canRead()) {
                unset($this->_ref_files[$_file->_id]);
                continue;
            }
            $this->_ref_files_by_name[$_file->file_name] = $_file;
            if ($_file->annule) {
                $this->_nb_cancelled_files++;
            }
        }

        return count($this->_ref_files);
    }

    /**
     * Load a named file for for the object, supposedly unique
     *
     * @param string $name Name of the file
     *
     * @return CFile The named file
     */
    function loadNamedFile($name)
    {
        return $this->_ref_named_files[$name] = CFile::loadNamed($this, $name);
    }

    /**
     * Load documents for object with PERM_READ
     *
     * @param array $where Optionnal conditions
     *
     * @return int|null Files count, null if unavailable
     */
    function loadRefsDocs($where = [], bool $with_cancelled = true)
    {
        $this->_nb_cancelled_docs = 0;
        if (null == $this->_ref_documents = $this->loadBackRefs("documents", "nom", null, null, null, null, null, $where)) {
            return null;
        }

        foreach ($this->_ref_documents as $_doc) {
            if (!$with_cancelled && $_doc->annule) {
                unset($this->_ref_documents[$_doc->_id]);
                continue;
            }
            /** @var CCompteRendu $_doc */
            if (!$_doc->canRead()) {
                unset($this->_ref_documents[$_doc->_id]);
                continue;
            }
            if ($_doc->annule) {
                $this->_nb_cancelled_docs++;
            }
        }

        return count($this->_ref_documents);
    }

    /**
     * Load forms for object with PERM_READ
     *
     * @param array $where Optional conditions
     *
     * @return CExLink[] Forms
     */
    function loadRefsForms($where = [])
    {
        if (!$this->_id) {
            return $this->_ref_forms = [];
        }

        $where["object_id"]    = "= '$this->_id'";
        $where["object_class"] = "= '$this->_class'";
        $where["level"]        = "= 'object'";

        $ex_link = new CExLink();

        return $this->_ref_forms = $ex_link->loadList($where);
    }

    /**
     * Load documents and files for object and sort by category
     *
     * @param boolean $with_cancelled Inclure les fichiers annulés
     * @param array   $where          Conditions optionnelles
     *
     * @return array()
     */
    function loadRefsDocItems($with_cancelled = true, $where = [])
    {
        $this->_nb_files      = $this->loadRefsFiles($where, $with_cancelled);
        $this->_nb_docs       = $this->loadRefsDocs($where, $with_cancelled);
        $this->_nb_files_docs = $this->_nb_files + $this->_nb_docs;

        $categories_files = CMbObject::massLoadFwdRef($this->_ref_files, "file_category_id");
        $categories_docs  = CMbObject::massLoadFwdRef($this->_ref_documents, "file_category_id");
        $categories       = $categories_docs + $categories_files;

        foreach ($this->_ref_documents as $_key => $_document) {
            $cat_name = $_document->file_category_id ? $categories[$_document->file_category_id]->nom : "";
            if (!isset($this->_ref_documents_by_cat[$cat_name])) {
                $this->_ref_documents_by_cat[$cat_name] = [];
            }
            $this->_ref_documents_by_cat[$cat_name][] = $_document;
            if (!isset($this->_refs_docitems_by_cat[$cat_name])) {
                $this->_refs_docitems_by_cat[$cat_name] = [];
            }
            $this->_refs_docitems_by_cat[$cat_name][] = $_document;
            $this->_refs_docitems[$_document->_guid]  = $_document;
        }
        foreach ($this->_ref_files as $_key => $_file) {
            $cat_name = $_file->file_category_id ? $categories[$_file->file_category_id]->nom : "";
            if (!isset($this->_ref_files_by_cat[$cat_name])) {
                $this->_ref_files_by_cat[$cat_name] = [];
            }
            $this->_ref_files_by_cat[$cat_name][] = $_file;
            if (!isset($this->_refs_docitems_by_cat[$cat_name])) {
                $this->_refs_docitems_by_cat[$cat_name] = [];
            }
            $this->_refs_docitems_by_cat[$cat_name][] = $_file;
            $this->_refs_docitems[$_file->_guid]      = $_file;
        }

        ksort($this->_refs_docitems_by_cat);

        return $this->_refs_docitems;
    }

    /**
     * Get the object of class $class related to $this
     *
     * @param string $class Class name
     *
     * @return CMbObject|null
     */
    function getRelatedObjectOfClass($class)
    {
        return null;
    }

    /**
     * Count documents
     *
     * @return int
     */
    function countDocs()
    {
        $this->_nb_cancelled_docs = $this->countBackRefs("documents", ["annule" => "= '1'"], null, true, "docs_cancelled");

        return $this->_nb_docs = $this->countBackRefs("documents");
    }

    /**
     * Count files
     *
     * @param array $where Where clause
     *
     * @return int
     */
    function countFiles($where = [])
    {
        $this->_nb_cancelled_files = $this->countBackRefs(
            "files",
            array_merge($where, ["annule" => "= '1'"]),
            null,
            true,
            "files_cancelled"
        );

        return $this->_nb_files = $this->countBackRefs("files", $where);
    }

    /**
     * Count forms
     *
     * @return int
     */
    function countForms()
    {
        if (!$this->_id) {
            return $this->_nb_forms = 0;
        }

        return $this->_nb_forms =
            $this->countBackRefs('ex_links_meta', ['level' => "= 'object'"], null, true, 'ex_links_object');
    }

    /**
     * Count doc items (that is documents and files), delegate when permission type defined
     *
     * @param int $permType Permission type, one of PERM_READ, PERM_EDIT
     *
     * @return int
     */
    function countDocItems($permType = null)
    {
        $this->_nb_files_docs = $permType ?
            $this->countDocItemsWithPerm($permType) :
            $this->countFiles() + $this->countDocs() + $this->countForms();

        return $this->_nb_files_docs;
    }

    /**
     * Mass count doc items shortcut
     *
     * @param self[] $objects Array of objects
     *
     * @return int
     */
    static function massCountDocItems($objects)
    {
        self::massCountBackRefs($objects, "documents", ["annule" => "= '1'"], null, "docs_cancelled");
        self::massCountBackRefs($objects, "files", ["annule" => "= '1'"], null, "files_cancelled");

        return
            self::massCountBackRefs($objects, "documents") +
            self::massCountBackRefs($objects, "files") +
            self::massCountBackRefs($objects, 'ex_links_meta', ['level' => "= 'object'"], null, 'ex_links_object');
    }

    /**
     * Count doc items according to given permission
     *
     * @param int $permType Permission type, one of PERM_READ, PERM_EDIT
     *
     * @return int
     * @todo Merge with countDocItems(), unnecessary delegation
     *
     */
    function countDocItemsWithPerm($permType = PERM_READ)
    {
        $this->loadRefsFiles();
        if ($this->_ref_files) {
            self::filterByPerm($this->_ref_files, $permType);
            $this->_nb_files = count($this->_ref_files);
        }

        $this->loadRefsDocs();
        if ($this->_ref_documents) {
            self::filterByPerm($this->_ref_documents, $permType);
            $this->_nb_docs = count($this->_ref_documents);
        }

        $this->loadRefsForms();
        if ($this->_ref_forms) {
            self::filterByPerm($this->_ref_forms, $permType);
            $this->_nb_forms = count($this->_ref_forms);
        }

        return $this->_nb_files + $this->_nb_docs;
    }

    /**
     * Mass count all documents with an alert
     *
     * @param self[] $objects Objects
     *
     * @return void
     */
    static function countAlertDocs($objects)
    {
        if (!count($objects)) {
            return;
        }

        self::massCountBackRefs($objects, "documents", ["alert_creation" => "= '1'"], null, "alert_docs");

        foreach ($objects as $_object) {
            $_object->_alert_docs = $_object->_count["alert_docs"];
        }
    }

    /**
     * Mass count all locked documents with an alert
     *
     * @param self[] $objects Objects
     *
     * @return void
     */
    static function countLockedAlertDocs($objects)
    {
        if (!count($objects)) {
            return;
        }

        $where_locked = [
            "valide"         => "= '1'",
            "alert_creation" => "= '1'",
        ];

        self::massCountBackRefs($objects, "documents", $where_locked, null, "locked_alert_docs");

        foreach ($objects as $_object) {
            $_object->_locked_alert_docs = $_object->_count["alert_docs"];
        }
    }

    /**
     * Count exchanges, make totals by format
     *
     * @param string $type    Exchange type
     * @param string $subtype Exchange subtype
     *
     * @return int The absolute total
     * @throws Exception
     */
    function countExchanges($type = null, $subtype = null)
    {
        foreach (CExchangeDataFormat::getAll() as $_data_format) {
            /** @var CExchangeDataFormat $data_format */
            $data_format = new $_data_format;
            if (!$data_format->hasTable()) {
                continue;
            }
            $data_format->object_id    = $this->_id;
            $data_format->object_class = $this->_class;

            $data_format->type      = $type;
            $data_format->sous_type = $subtype;

            $this->_nb_exchanges_by_format[$_data_format] = $data_format->countMatchingList();
        }

        foreach ($this->_nb_exchanges_by_format as $_nb_exchange_format) {
            $this->_nb_exchanges += $_nb_exchange_format;
        }

        return $this->_nb_exchanges;
    }

    /**
     * Count the exchanges for the all sejours
     *
     * @param CMbObject[] $objects CMbObject
     * @param String      $type    Type
     * @param String      $subtype Sous type
     *
     * @return void
     * @throws Exception
     */
    static function massCountExchanges($objects, $type = null, $subtype = null)
    {
        if (!count($objects)) {
            return null;
        }
        $object     = current($objects);
        $object_ids = CMbArray::pluck($objects, $object->_spec->key);
        $object_ids = array_unique($object_ids);
        CMbArray::removeValue("", $object_ids);

        if (!count($object_ids)) {
            return null;
        }

        $where = [
            "object_id"    => CSQLDataSource::prepareIn($object_ids),
            "object_class" => "= '$object->_class'",
        ];

        if ($type) {
            $where["type"] = "= '$type'";
        }

        if ($subtype) {
            $where["sous_type"] = "= '$subtype'";
        }

        $count_exchanges = [];
        foreach (CExchangeDataFormat::getAll() as $_data_format) {
            /** @var CExchangeDataFormat $data_format */
            $data_format = new $_data_format;
            if (!$data_format->hasTable()) {
                continue;
            }

            $table_exchange                   = $data_format->_spec->table;
            $count_exchanges[$table_exchange] = $data_format->countMultipleList($where, null, "object_id", null, ["object_id"]);
        }

        foreach ($count_exchanges as $_exchange => $_counts) {
            foreach ($_counts as $_value) {
                $total     = $_value["total"];
                $object_id = $_value["object_id"];
                if (!isset($objects[$object_id]->_nb_exchanges_by_format[$_exchange])) {
                    $objects[$object_id]->_nb_exchanges_by_format[$_exchange] = 0;
                }

                $objects[$object_id]->_nb_exchanges_by_format[$_exchange] += $total;
                $objects[$object_id]->_nb_exchanges                       += $total;
            }
        }
    }


    /**
     * Chargement du dernier identifiant id400
     *
     * @param string $tag Tag à utiliser comme filtre
     *
     * @return CIdSante400
     */
    function loadLastId400($tag = null)
    {
        $idex = new CIdSante400();
        if ($idex->_ref_module) {
            $idex->loadLatestFor($this, $tag);
            $this->_ref_last_id400 = $idex;
        }

        return $idex;
    }

    /**
     * Load object view information
     *
     * @return void
     */
    function loadView()
    {
        parent::loadView();
        $this->loadRefsNotes();
    }

    /**
     * Load object view information when used in an edit form (see system/ajax_edit_object.php)
     *
     * @return void
     */
    function loadEditView()
    {
        $this->loadView();
    }

    /**
     * Load complete object view information
     *
     * @return void
     */
    function loadComplete()
    {
        $this->loadRefsNotes();
        $this->loadRefs();
    }

    /**
     * Loads the object's view from an ExObject perspective
     *
     * @return void
     */
    public function loadExView()
    {
        // Nothing, for further usage
    }

    /**
     * @return void
     * @deprecated Out of control resource consumption
     *
     * Back references global loader
     *
     */
    function loadRefsBack()
    {
        parent::loadRefsBack();
        $this->loadExternal();
    }

    /**
     * Load idexs
     *
     * @return void
     */
    function loadExternal()
    {
        return $this->_external = $this->countBackRefs("identifiants");
    }

    /**
     * Charge toutes les aides à la saisie de l'objet pour un utilisateur donné
     *
     * @param int    $user_id        Utilisateur
     * @param string $keywords       Permet de filtrer les aides commançant par le filtre, si non null
     * @param string $depend_value_1 Valeur de la dépendance 1 lié à l'aide
     * @param string $depend_value_2 Valeur de la dépendance 2 lié à l'aide
     * @param string $object_field   Type d'objet concerné
     * @param string $strict         True or False
     *
     * @return void
     */
    function loadAides(
        $user_id,
        $keywords = null,
        $depend_value_1 = null,
        $depend_value_2 = null,
        $object_field = null,
        $strict = "true"
    ) {
        foreach ($this->_specs as $field => $spec) {
            if (isset($spec->helped)) {
                $this->_aides[$field] = ["no_enum" => null];
            }
        }

        // Chargement de l'utilisateur courant
        $user = new CMediusers();
        $user->load($user_id);
        $user->loadRefFunction();

        // Préparation du chargement des aides
        $ds =& $this->_spec->ds;

        // Construction du Where
        $where = [];

        $where[] = "(user_id = '$user_id' OR 
      function_id = '$user->function_id' OR 
      group_id = '{$user->_ref_function->group_id}')
      OR (user_id IS NULL AND function_id IS NULL AND group_id IS NULL)";

        if ($this->_class == 'Cerfa') {
            $where["class"] = $ds->prepare("IN ('Cerfa', 'CCerfa')");
        } else {
            $where["class"] = $ds->prepare("= %",$this->_class);
        }

        if ($strict === "true") {
            if ($depend_value_1) {
                $where["depend_value_1"] = " = '$depend_value_1'";
            }

            if ($depend_value_2) {
                $where["depend_value_2"] = " = '$depend_value_2'";
            }
        } else {
            if ($depend_value_1) {
                $where[] = "(depend_value_1 = '$depend_value_1' OR depend_value_1 IS NULL)";
            }
            if ($depend_value_2) {
                $where[] = "(depend_value_2 = '$depend_value_2' OR depend_value_2 IS NULL)";
            }
        }

        if ($object_field) {
            $where["field"] = " = '$object_field'";
        }

        // tri par user puis function puis group (ordre inversé pour avoir ce résultat)
        $order = "group_id, function_id, user_id, depend_value_1, depend_value_2, name, text";

        // Chargement des Aides de l'utilisateur
        $aide = new CAideSaisie();
        // TODO: si on veut ajouter un $limit, il faudrait l'ajouter en argument de la fonction loadAides
        /** @var CAideSaisie[] $aides */
        $aides = $aide->seek($keywords, $where, null, null, null, $order);

        $this->orderAides($aides, $depend_value_1, $depend_value_2);

        CAideSaisie::massLoadDependObjects($this->_aides_new);

        foreach ($this->_aides_new as $_aide) {
            $_aide->loadDependValues();
        }
    }

    /**
     * Order aides
     *
     * @param CAideSaisie[] $aides          Aides à la saisie
     * @param string        $depend_value_1 Valeur de la dépendance 1 lié à l'aide
     * @param string        $depend_value_2 Valeur de la dépendance 2 lié à l'aide
     *
     * @return void
     */
    function orderAides($aides, $depend_value_1 = null, $depend_value_2 = null)
    {
        foreach ($aides as $aide) {
            $owner = CAppUI::tr("CAideSaisie._owner.$aide->_owner");
            $aide->loadRefOwner();

            // si on filtre seulement sur depend_value_1, il faut afficher les resultats suivant depend_value_2
            if ($depend_value_1) {
                $depend_field_2 = $aide->_depend_field_2;
                $depend_2       = CAppUI::tr("$this->_class.$aide->_depend_field_2.$aide->depend_value_2");
                if ($aide->depend_value_2) {
                    $this->_aides[$aide->field][$owner][$depend_2][$aide->text] = $aide->name;
                } else {
                    $depend_name_2                                                                  = CAppUI::tr("$this->_class-$depend_field_2");
                    $this->_aides[$aide->field][$owner]["$depend_name_2 non spécifié"][$aide->text] = $aide->name;
                }
                continue;
            }

            // ... et réciproquement
            if ($depend_value_2) {
                $depend_field_1 = $aide->_depend_field_1;
                $depend_1       = CAppUI::tr("$this->_class.$aide->_depend_field_1.$aide->depend_value_1");
                if ($aide->depend_value_1) {
                    $this->_aides[$aide->field][$owner][$depend_1][$aide->text] = $aide->name;
                } else {
                    $depend_name_1                                                                  = CAppUI::tr("$this->_class-$depend_field_1");
                    $this->_aides[$aide->field][$owner]["$depend_name_1 non spécifié"][$aide->text] = $aide->name;
                }
                continue;
            }

            $this->_aides_all_depends[$aide->field][$aide->depend_value_1][$aide->depend_value_2][$aide->_id] = $aide;

            // Ajout de l'aide à la liste générale
            $this->_aides[$aide->field]["no_enum"][$owner][$aide->text] = $aide->name;
        }

        $this->_aides_new = $aides;
    }

    /**
     * Chargement des affectations de personnel par emplacements
     *
     * @param array $where Where clause
     *
     * @return CAffectationPersonnel[][]|null Affections, null if unavailable
     * @throws Exception
     */
    function loadAffectationsPersonnel($where = [])
    {
        // Initialisation
        $personnel = new CPersonnel();
        foreach ($personnel->_specs["emplacement"]->_list as $emplacement) {
            $this->_ref_affectations_personnel[$emplacement] = [];
        }

        // Module actif
        if (null === $affectations = $this->loadBackRefs("affectations_personnel", null, null, null, null, null, "", $where)) {
            return null;
        }

        $this->_count_affectations_personnel = count($affectations);

        // Chargement et classement

        /** @var CAffectationPersonnel $affectation */
        foreach ($affectations as $affectation) {
            $personnel = $affectation->loadRefPersonnel();
            $personnel->loadRefUser()->loadRefFunction();
            $this->_ref_affectations_personnel[$personnel->emplacement][$affectation->_id] = $affectation;
        }

        return $this->_ref_affectations_personnel;
    }

    /**
     * Load the object's tag items
     *
     * @param bool $cache Use cache
     *
     * @return array
     */
    function loadRefsTagItems($cache = true)
    {
        if ($cache && !empty($this->_ref_tag_items)) {
            return $this->_ref_tag_items;
        }

        /** @var CTagItem[] $tag_items */
        $tag_items = $this->loadBackRefs("tag_items");

        CStoredObject::massLoadFwdRef($tag_items, "tag_id");

        foreach ($tag_items as $_tag_item) {
            $_tag_item->loadRefTag();
        }

        return $this->_ref_tag_items = $tag_items;
    }

    /**
     * Load the object's hyperlink text
     *
     * @param bool $cache Use cache
     *
     * @return array
     */
    function loadRefsHyperTextLink($cache = true)
    {
        if ($cache && !empty($this->_ref_hypertext_links)) {
            return $this->_ref_hypertext_links;
        }

        return $this->_ref_hypertext_links = $this->loadBackRefs("hypertext_links");
    }

    /**
     * Get the object's tags
     *
     * @param bool $cache Use cache
     *
     * @return array
     */
    function getTags($cache = true)
    {
        $tag_items = $this->loadRefsTagItems($cache);

        return CMbArray::pluck($tag_items, "_ref_tag");
    }

    /**
     * Get the related object by class for template filling
     *
     * @return array Collection of class => id relations
     */
    function getTemplateClasses()
    {
        return [$this->_class => $this->_id];
    }

    /**
     * This function register all templated properties for the object
     * Will load as necessary and fill in values
     *
     * @param CTemplateManager $template Template manager
     *
     * @return void
     */
    function fillTemplate(&$template)
    {
    }

    /**
     * This function register most important templated properties for the object
     * Won't register distant properties
     * Will load as necessary and fill in values
     *
     * @param CTemplateManager $template Template manager
     *
     * @return void
     */
    function fillLimitedTemplate(&$template)
    {
    }

    /**
     * This function registers fields for the label printing
     *
     * @param array $fields Array of fields
     * @param array $params Array of params
     *
     * @return void
     */
    function completeLabelFields(&$fields, $params)
    {
    }

    /**
     * Load object config
     *
     * string $classname Classname
     *
     * @return void
     * @throws Exception
     */
    function loadConfigValues($classname = null)
    {
        if (!$classname) {
            $classname = $this->_class;
        }

        $object_class = $classname . "Config";
        if (!class_exists($object_class)) {
            return;
        }

        $cache = new Cache("{$this->_class}loadConfigValues", [$this->_guid], Cache::INNER);
        if ($cache->exists()) {
            return $this->_configs = $cache->get();
        }

        // Chargement des configs de la classe
        $where              = [];
        $where["object_id"] = " IS NULL";
        /** @var CMbObjectConfig $class_config */
        $class_config = new $object_class();
        $class_config->loadObject($where);

        if (!$class_config->_id) {
            $class_config->valueDefaults();
        }

        // Chargement des configs de l'objet
        $object_config = $this->loadUniqueBackRef("object_configs");

        $class_config->extendsWith($object_config);

        $this->_configs = $class_config->getConfigValues();

        return $cache->put($this->_configs);
    }

    /**
     * Get value of the object config
     *
     * @return string[]
     */
    function getConfigValues()
    {
        $configs = [];

        $fields = $this->getPlainFields();
        unset($fields[$this->_spec->key]);
        unset($fields["object_id"]);
        foreach ($fields as $_name => $_value) {
            $configs[$_name] = $_value;
        }

        return $configs;
    }


    /**
     * Backward references
     *
     * @return void
     */
    function loadRefObjectConfigs()
    {
        $object_class = $this->_class . "Config";
        if (class_exists($object_class)) {
            $this->_ref_object_configs = $this->loadUniqueBackRef("object_configs");
        }
    }

    /**
     * Returns the path to the class-specific template
     *
     * @param string $type view|autocomplete|edit
     *
     * @return string|null
     */
    function getTypedTemplate($type)
    {
        if (!in_array($type, ["view", "autocomplete", "edit"])) {
            return null;
        }

        $mod_name = $this->_ref_module->mod_name;
        $template = "$mod_name/templates/{$this->_class}_$type.tpl";

        if (!is_file("modules/$template")) {
            $template = "system/templates/CMbObject_$type.tpl";
        }

        return "../../$template";
    }

    /**
     * Fills the object with random sample data, for testing purposes
     *
     * @param array $staticsProps Properties to assess
     *
     * @return void
     */
    function sample($staticsProps = [])
    {
        foreach ($this->_specs as $key => $spec) {
            if (isset($staticsProps[$key])) {
                $this->$key = $staticsProps[$key];
            } elseif ($key[0] !== "_") {
                $spec->sample($this, false);
            }
        }
    }

    /**
     * Fills the object with random sample data from database
     *
     * @return void
     */
    function random()
    {
        $fields = $this->getPlainFields();
        unset($fields[$this->_spec->key]);

        foreach ($fields as $_field => $value) {
            $this->$_field = $this->getRandomValue($_field);
        }
    }

    /**
     * Get random value
     *
     * @param string $field       Field name
     * @param bool   $is_not_null Search field not null
     *
     * @return mixed
     */
    function getRandomValue($field, $is_not_null = false)
    {
        $ds = $this->getDS();

        $query = new CRequest(false);
        $query->addSelect($field);
        $query->addTable($this->_spec->table);
        if ($is_not_null) {
            $query->addWhereClause($field, "IS NOT NULL");
        }
        $query->addOrder("RAND()");
        $query->setLimit(1);

        return $ds->loadResult($query->makeSelect());
    }

    /**
     * Return idex type if it's special (e.g. IPP/NDA/...)
     *
     * @param CIdSante400 $idex Idex
     *
     * @return string|null
     */
    function getSpecialIdex(CIdSante400 $idex)
    {
        return null;
    }

    /**
     * Get object tag
     *
     * @param string $group_id Group
     *
     * @return string|null
     */
    static function getObjectTag($group_id = null)
    {
        // Permettre des idex en fonction de l'établissement
        if (!$group_id) {
            $group_id = CGroups::loadCurrent()->_id;
        }

        $cache = new Cache(CClassMap::getSN(get_called_class()) . ".getObjectTag", [$group_id], Cache::INNER);

        if ($cache->exists()) {
            return $cache->get();
        }

        /** @var CMbObject $object */
        $object = new static;
        $tag    = $object->getDynamicTag();

        return $cache->put(str_replace('$g', $group_id, $tag));
    }

    /**
     * Get object dynamic tag
     *
     * @return string|null
     */
    function getDynamicTag()
    {
        return null;
    }

    /**
     * Load all the documents linked to the object
     *
     * @param array $params optional parameters
     *
     * @return CDocumentItem[]
     */
    function loadAllDocs($params = [])
    {
        return [];
    }

    /**
     * Remove duplicate devis items in CFile
     *
     */
    public function filterDuplicatingDevis()
    {

        $ds = $this->getDS();
        $list_remove = [];

        foreach ($this->_all_docs['docitems'] as $_key => $_docitems) {
            usort($_docitems, function($a, $b) {
                if (isset($a->file_id) && isset($b->file_id)) {
                    return ($a->file_id > $b->file_id) ? -1 : 1;
                }
            });
            foreach ($_docitems as $_docitem) {
                if ($_docitem instanceof CExLink) {
                    continue;
                }

                if ($_docitem->_ref_category->class == 'CDevisCodage') {
                    if (!in_array($_docitem->_ref_category->nom, $list_remove)) {
                        $list_remove[] = $_docitem->_ref_category->nom;
                        continue;
                    }

                    if (in_array($_docitem->_ref_category->nom, $list_remove)) {
                        unset($this->_all_docs['docitems'][$_key][$_docitem->_guid]);
                    }
                }
            }
        }
    }

    /**
     * Remove similar docitems (avoiding duplicates on copy purpose)
     *
     * @param string|null $context_copy_guid
     */
    public function filterDuplicatingDocs(string $context_copy_guid = null)
    {
        if (!$context_copy_guid) {
            return;
        }

        [$object_class, $object_id] = explode('-', $context_copy_guid);
        $ds = $this->getDS();

        $where = [
            'object_class' => $ds->prepare('= ?', $object_class),
            'object_id'    => $ds->prepare('= ?', $object_id),
            'annule'       => "= '0'",
        ];

        $files_names = (new CFile())->loadColumn('file_name', $where);
        $docs_names  = (new CCompteRendu())->loadColumn('nom', $where);

        foreach ($this->_all_docs['docitems'] as $_key => $_docitems) {
            foreach ($_docitems as $_docitem) {
                switch (get_class($_docitem)) {
                    case CCompteRendu::class:
                        if (in_array($_docitem->nom, $docs_names)) {
                            unset($this->_all_docs['docitems'][$_key][$_docitem->_guid]);
                        }
                        break;

                    case CFile::class:
                        if (in_array($_docitem->file_name, $files_names)) {
                            unset($this->_all_docs['docitems'][$_key][$_docitem->_guid]);
                        }
                        break;

                    default:
                        unset($this->_all_docs['docitems'][$_key][$_docitem->_guid]);
                }

                if (!count($this->_all_docs['docitems'][$_key])) {
                    unset($this->_all_docs['docitems'][$_key]);
                }
            }
        }
    }

    /**
     * Map documents to the object
     *
     * @param CMbObject $object reference object for the docs
     * @param array     $params parameters
     *
     * @return void
     */
    function mapDocs($object, $params = [])
    {
        if ($object instanceof CConsultation && CAppUI::gconf("dPcabinet CConsultation verification_access")) {
            $consult_anesth = $object->loadRefConsultAnesth();

            if (!$object->sejour_id && (!$consult_anesth->_id || !$consult_anesth->sejour_id) && !$object->canEdit()) {
                return;
            }
        }

        // Documents et fichiers
        $pdf_and_thumbs = CAppUI::pref("pdf_and_thumbs");

        $with_cancelled = CMbArray::extract($params, "with_cancelled", true);
        $tri            = CMbArray::extract($params, "tri", "date");
        $cat_ids        = CMbArray::extract($params, "cat_ids", []);
        $importance     = CMbArray::extract($params, "importance");
        $user_id        = CMbArray::extract($params, "user_id");
        $function_id    = CMbArray::extract($params, "function_id");
        $with_docs      = CMbArray::extract($params, 'with_docs', 1);
        $with_files     = CMbArray::extract($params, 'with_files', 1);
        $with_forms     = CMbArray::extract($params, 'with_forms', 1);

        $user_ids = [];

        if ($user_id) {
            $user_ids = [$user_id];
        }

        if ($function_id) {
            $user       = new CMediusers();
            $where_func = [
                "function_id" => "= '$function_id'",
            ];
            $user_ids   = $user->loadIds($where_func);
        }

        // Suppression des valeurs vides
        CMbArray::removeValue('', $cat_ids);

        $sub_filter_cat_ids = [];
        if ($importance) {
            switch ($importance) {
                default:
                case "high":
                    $cats = CFilesCategory::getImportantCategories();
                    break;
                case "medical":
                    $cats = CFilesCategory::getMedicalCategories();
            }

            // En cas de catégories spécifiés, elles sont passées en sous-filtre de la requête principale
            if (count($cat_ids)) {
                $sub_filter_cat_ids = $cat_ids;
            }

            $cat_ids = CMbArray::pluck($cats, '_id');
        }

        $where = [];

        if (count($cat_ids)) {
            $where["file_category_id"] = CSQLDataSource::prepareIn($cat_ids)
                . (count($sub_filter_cat_ids) ? (' AND file_category_id ' . CSQLDataSource::prepareIn($sub_filter_cat_ids)) : '');
        }

        if (count($user_ids)) {
            $where["author_id"] = CSQLDataSource::prepareIn($user_ids);
        }

        if ($with_docs) {
            $object->loadRefsDocs($where, $with_cancelled);
        }

        if ($with_files) {
            $object->loadRefsFiles($where, $with_cancelled);
        }

        CStoredObject::massLoadFwdRef($object->_ref_documents, "file_category_id");
        CStoredObject::massLoadFwdRef($object->_ref_documents, "content_id");
        CStoredObject::massLoadFwdRef($object->_ref_documents, "author_id");
        if ($pdf_and_thumbs) {
            CStoredObject::massCountBackRefs($object->_ref_documents, "files");
        }

        if (!isset($this->_all_docs["contexts"])) {
            $this->_all_docs = [
                "contexts" => [],
                "docitems" => [],
            ];
        }

        $appfine = CModule::getActive('appFineClient');

        foreach ($object->_ref_documents as $_doc) {
            CDocumentItem::makeIconName($_doc);
            $_doc->loadContent(false);
            $_doc->loadRefAuthor()->loadRefFunction();
            $_doc->countSynchronizedRecipients();
            if ($pdf_and_thumbs && !$_doc->_count["files"]) {
                unset($_doc->_count["files"]);
            }
            $_doc->loadRefCategory();
            $_doc->_ref_object                                   = $object;
            $prefixe                                             = $this->makePrefix($tri, $object, $_doc);
            $this->_all_docs["docitems"][$prefixe][$_doc->_guid] = $_doc;
            $this->_all_docs["contexts"][$prefixe]               = $this->makeDateTime($object);

            if ($appfine) {
                CAppFineClient::loadIdex($_doc);
            }
        }

        CStoredObject::massLoadFwdRef($object->_ref_files, "file_category_id");
        CStoredObject::massLoadFwdRef($object->_ref_files, "author_id");
        foreach ($object->_ref_files as $_file) {
            CDocumentItem::makeIconName($_file);
            $_file->loadRefCategory();
            $_file->loadRefAuthor()->loadRefFunction();
            $_file->countSynchronizedRecipients();
            $_file->_ref_object                                   = $object;
            $prefixe                                              = $this->makePrefix($tri, $object, $_file);
            $this->_all_docs["docitems"][$prefixe][$_file->_guid] = $_file;
            $this->_all_docs["contexts"][$prefixe]                = $this->makeDateTime($object);

            if ($appfine) {
                CAppFineClient::loadIdex($_file);
            }
        }

        // Formulaires
        if ($with_forms && !count($cat_ids)) {
            $where = [];

            if (count($user_ids)) {
                $where["owner_id"] = CSQLDataSource::prepareIn($user_ids);
            }

            $ex_links = $object->loadRefsForms($where);
            CExLink::massLoadExObjects($ex_links);

            foreach ($ex_links as $_link) {
                $_ex = $_link->loadRefExObject();

                $_ex->updateCreationFields();
                $object = $_ex->loadTargetObject();
                $_ex->loadRefExClass();
                $_ex->loadRefOwner()->loadRefFunction();
                CDocumentItem::makeIconName($_ex->_ref_ex_class);
                $prefixe                                              = $this->makePrefix($tri, $object, $_ex);
                $this->_all_docs["docitems"][$prefixe][$_link->_guid] = $_link;
                $this->_all_docs["contexts"][$prefixe]                = $this->makeDateTime($object);
            }
        }

        if (count($this->_all_docs["docitems"])) {
            switch ($tri) {
                default:
                    ksort($this->_all_docs["docitems"]);
                    break;
                case "context":
                    array_multisort($this->_all_docs["contexts"], SORT_DESC, $this->_all_docs["docitems"]);
                    break;
                case "date":
                    krsort($this->_all_docs["docitems"]);
            }
        }
    }

    /**
     * Make prefix for an object
     *
     * @param string        $tri    Type de préfixe utilisé
     * @param CDocumentItem $object Objet concerné
     * @param CDocumentItem $item   Item
     *
     * @return string
     */
    function makePrefix($tri, $object, $item)
    {
        switch ($tri) {
            default:
            case "date":
                return "";
                break;
            case "context":
                switch ($object->_class) {
                    case "CConsultation":
                        $object->loadRefPlageConsult();

                        return "Consultation du " . CMbDT::dateToLocale($object->_date);
                    default:
                        return $object->_view;
                }
                break;
            case "cat":
                if ($item instanceof CDocumentItem && $item->_ref_category->_id) {
                    return $item->_ref_category->nom;
                }

                return CAppUI::tr("common-Other|pl");
        }
    }

    /**
     * Get a datime from an object
     *
     * @param CMbObject $object The object
     *
     * @return string
     */
    function makeDateTime(CMbObject $object)
    {
        switch ($object->_class) {
            case "CConsultation":
                $object->loadRefPlageConsult();

                return $object->_datetime;
            case "CSejour":
                return $object->entree;
            case "COperation":
                return $object->_datetime;
            case "CEvenementPatient":
                return $object->date;
            default:
                return CMbDT::dateTime();
        }
    }

    /**
     * Charge la notification sms
     *
     * @return CNotification|null
     * @throws Exception
     */
    function loadRefNotification()
    {
        if (!CModule::getActive("notifications")) {
            return null;
        }

        return $this->_ref_notification = $this->loadUniqueBackRef("context_notifications");
    }

    /**
     * Charge les notifications sms
     *
     * @return CNotification[]|null
     * @throws Exception
     */
    function loadRefNotifications()
    {
        if (!CModule::getActive("notifications")) {
            return null;
        }

        return $this->_ref_notifications = $this->loadBackRefs("context_notifications");
    }

    function loadRefsDocItemsGuids()
    {
        $context_docs = $this->loadBackRefs("context_doc");

        /** @var CContextDoc $_context_doc */
        foreach ($context_docs as $_context_doc) {
            $field = "_docitems_guid" . ($_context_doc->type ? "_$_context_doc->type" : "");

            $guids = [];

            $_context_doc->loadRefsDocs();
            foreach ($_context_doc->_ref_documents as $_doc) {
                $guids[] = $_doc->_guid;
            }

            $_context_doc->loadRefsFiles();
            foreach ($_context_doc->_ref_files as $_file) {
                $guids[] = $_file->_guid;
            }

            if (count($guids)) {
                $this->{$field} = implode(",", $guids);
            }
        }
    }

    function countContextDocItems($type = null)
    {
        $count = 0;

        /** @var CContextDoc $_context_doc */
        foreach ($this->loadBackRefs("context_doc") as $_context_doc) {
            if ($type && $_context_doc->type !== $type) {
                continue;
            }
            $field        = "_count_docitems" . ($_context_doc->type ? "_$_context_doc->type" : "");
            $this->$field = $_context_doc->countDocItems();

            $count += $this->$field;
        }

        return $count;
    }

    function storeDocItems()
    {
        if (!$this->_docitems_guid) {
            return null;
        }

        foreach (explode(",", $this->_docitems_guid) as $docitem_guid) {
            $docitem = CStoredObject::loadFromGuid($docitem_guid);
            $docitem->setObject($this);
            $docitem->_id = "";

            switch ($docitem->_class) {
                default:
                case "CCompteRendu":
                    $docitem->loadContent();

                    $docitem->content_id        = "";
                    $docitem->_ref_content->_id = "";
                    break;
                case "CFile":
                    $old_path = $docitem->_file_path;

                    $docitem->file_real_filename = "";

                    $docitem->fillFields();
                    $docitem->updateFormFields();
                    $docitem->setCopyFrom($old_path);
            }

            if ($msg = $docitem->store()) {
                return $msg;
            }
        }

        // On vide le form field pour éviter de repasser dans cette fonction.
        $this->_docitems_guid = null;
    }

    /**
     * @return Collection|array
     * @throws Api\Exceptions\ApiException
     */
    public function getResourceFiles()
    {
        if (!$this->loadRefsFiles()) {
            return [];
        }

        return new Collection($this->_ref_files);
    }

    /**
     * Load external identifiers
     *
     * @param int $group_id Group ID
     *
     * @return void
     */
    function loadExternalIdentifiers($group_id = null)
    {
    }

    /**
     * Assign multiple properties from values array()
     *
     * @param array      $values Name-Value pairs
     * @param object    &$object The object to feed
     * @param bool       $exist  Only fill existing properties
     * @param bool       $strict Do not assign non existing properties
     *
     * @return void
     */
    public static function setProperties($values, &$object, bool $exist = true, bool $strict = true): void
    {
        foreach ($values as $_name => $_value) {
            if ($exist && property_exists($object, $_name)) {
                $object->$_name = $_value;
            } elseif (!$strict) {
                $object->$_name = $_value;
            }
        }
    }
}
