<?php

/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files;

use Exception;
use Ox\AppFine\Client\CAppFineClient;
use Ox\AppFine\Server\CNatureFile;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CMbString;
use Ox\Core\CStoredObject;
use Ox\Core\Module\CModule;
use Ox\Erp\CabinetSIH\CCabinetSIH;
use Ox\Interop\Dmp\CDMPDocument;
use Ox\Interop\Dmp\CDMPTools;
use Ox\Interop\Eai\CInteropActor;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\InteropResources\valueset\CANSValueSet;
use Ox\Interop\SIHCabinet\CSIHCabinet;
use Ox\Interop\Sisra\CSisraDocument;
use Ox\Interop\Sisra\CSisraTools;
use Ox\Interop\Xds\CXDSDocument;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CDestinataire;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Medimail\CMedimailMessage;
use Ox\Mediboard\Medimail\CMedimailService;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CElectronicDelivery;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\IGroupRelated;
use Ox\Mediboard\Patients\IPatientRelated;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExObject;

/**
 * The CDocumentItem class
 */
class CDocumentItem extends CMbObject implements IGroupRelated
{
    // Check send document to third party system
    /** @var bool */
    public static $check_send_problem = true;

    /** @var string[] Allow media type for CDAr2 */
    public static $extensions_authorized_for_cda = [
        'image/jpeg',
        'image/jpg',
        'image/tiff',
        'text/rtf',
        'text/plain',
        'application/pdf',
    ];

    /** @var int */
    public $file_category_id;

    /** @var string */
    public $etat_envoi;

    /** @var int */
    public $author_id;

    /** @var int */
    public $private;

    /** @var int */
    public $annule;

    /** @var int */
    public $doc_size;

    /** @var string */
    public $type_doc_dmp;

    /** @var int */
    public $type_doc_sisra;

    /** @var int */
    public $remis_patient;

    /** @var int */
    public $send;

    /** @var int */
    public $object_id;

    /** @var string */
    public $object_class;

    /** @var int */
    public $masquage_patient;

    /** @var int */
    public $masquage_praticien;

    /** @var int */
    public $masquage_representants_legaux;

    /** @var CMbObject */
    public $_ref_object;

    // Derivated fields
    /** @var string */
    public $_extensioned;

    /** @var string */
    public $_no_extension;

    /** @var int */
    public $_file_size;

    /** @var string */
    public $_file_date;

    /** @var string */
    public $_icon_name;

    /** @var int */
    public $_version;

    /** @var string */
    public $_send_problem;

    /** @var int */
    public $_category_id;

    // Behavior Field
    /** @var int */
    public $_send;

    /** @var int */
    public $_created;

    /** @var CMediusers */
    public $_ref_author;

    /** @var CFilesCategory */
    public $_ref_category;

    /** @var  CDestinataireItem[] */
    public $_ref_destinataires;

    /** @var CElectronicDelivery[] */
    public $_ref_deliveries;

    /** @var CDocumentReference[] */
    public $_ref_documents_reference;

    /** @var CNatureFile */
    public $_ref_nature_file;

    /** @var bool Indicate if the document has been sent by mail */
    public $_sent_mail = false;

    /** @var array The list of recipients that have received the document by mail */
    public $_mail_recipients = [];

    /** @var bool Indicate if the document has been sent by apicrypt */
    public $_sent_apicrypt = false;

    /** @var array The list of recipients that have received the document by apicrypt */
    public $_apicrypt_recipients = [];

    /** @var bool Indicate if the document has been sent by mssante */
    public $_sent_mssante = false;

    /** @var array The list of recipients that have received the document by mssante */
    public $_mssante_recipients = [];

    /** @var int COunt the number of times the document has been sent */
    public $_count_deliveries = 0;

    /** @var bool bool */
    public $_no_synchro_eai = false;

    //DMP
    /** @var CDMPDocument[] */
    public $_refs_dmp_document;

    /** @var int */
    public $_count_dmp_documents;

    /** @var int */
    public $_status_dmp;

    /** @var CDMPDocument */
    public $_ref_last_dmp_document;

    /** @var string */
    public $_fa_dmp;

    // Sas envoi
    /** @var CFileTraceability */
    public $_ref_last_file_traceability;

    // AppFine
    /** @var int */
    public $_status_appFineClient;

    // TAMM-SIH
    /** @var int */
    public $_status_sih_cabinet;

    /** @var  int */
    public $_status_cabinet_sih;

    // XDS
    /** @var int */
    public $_count_xds_documents;

    /** @var CXDSDocument[] */
    public $_refs_xds_document;

    //Sisra
    /** @var int */
    public $_count_sisra_documents;

    /** @var CSisraDocument[] */
    public $_refs_sisra_document;

    /** @var int */
    public $_status_sisra;

    // FHIR
    /** @var  CIdSante400 */
    public $_ref_fhir_idex;

    // SIH
    /** @var int */
    public $_ext_cabinet_id;

    /** @var CReadFile */
    public $_ref_read_file;

    // Medimail
    /** @var array */
    public $_is_send_mssante = [];

    /** Get all owners user IDs from aggregated owner
     *
     * @param CMbObject|null $owner Aggregated owner of class CUser|CFunctions|CGroups.
     *
     * @return null|string[] Array of user IDs, null if no owner is defined;
     */
    public static function getUserIds(CMbObject $owner = null)
    {
        if (!$owner) {
            return null;
        }

        $user_ids = [];

        if ($owner instanceof CGroups) {
            foreach ($owner->loadBackRefs("functions") as $_function) {
                $user_ids = array_merge($user_ids, $_function->loadBackIds("users"));
            }
        }

        if ($owner instanceof CFunctions) {
            $user_ids = $owner->loadBackIds("users");
        }

        if ($owner instanceof CUser || $owner instanceof CMediusers) {
            $user_ids = [$owner->_id];
        }

        return $user_ids;
    }

    /**
     * Load files for on object
     *
     * @param CMbObject $object         object to load the files
     * @param string    $order          order to sort the files (nom/date)
     * @param bool      $with_cancelled include cancelled files
     *
     * @return array[][]
     */
    public static function loadDocItemsByObject(CMbObject $object, string $order = "nom", bool $with_cancelled = true)
    {
        $where = [];

        if (!$with_cancelled) {
            $where["annule"] = "= '0'";
        }

        if (!$object->_ref_files) {
            $object->loadRefsFiles($where);
        }
        if (!$object->_ref_documents) {
            $object->loadRefsDocs($where);
        }

        // Création du tableau des catégories pour l'affichage
        $affichageFile = [
            [
                "name"  => CAppUI::tr("CFilesCategory.none"),
                "items" => [],
            ],
        ];

        foreach (CFilesCategory::listCatClass($object->_class) as $_cat) {
            $affichageFile[$_cat->_id] = [
                "name"  => $_cat->nom,
                "items" => [],
            ];
        }

        $order_by = [
            "CFile"        => $order == "date" ? "file_date" : "file_name",
            "CCompteRendu" => $order == "date" ? "creation_date" : "nom",
        ];

        // Ajout des fichiers dans le tableau
        foreach ($object->_ref_files as $_file) {
            $cat_id = $_file->file_category_id ?: 0;

            $affichageFile[$cat_id]["items"][$_file->{$order_by[$_file->_class]} . "-$_file->_guid"] = $_file;
            if (!isset($affichageFile[$cat_id]["name"])) {
                $affichageFile[$cat_id]["name"] = $cat_id ? $_file->_ref_category->nom : "";
            }
        }

        // Ajout des document dans le tableau
        foreach ($object->_ref_documents as $_doc) {
            $_doc->isLocked();
            $cat_id = $_doc->file_category_id ?: 0;

            $affichageFile[$cat_id]["items"][$_doc->{$order_by[$_doc->_class]} . "-$_doc->_guid"] = $_doc;
            if (!isset($affichageFile[$cat_id]["name"])) {
                $affichageFile[$cat_id]["name"] = $cat_id ? $_doc->_ref_category->nom : "";
            }
        }

        // Classement des Fichiers et des document par Ordre alphabétique
        foreach ($affichageFile as $keyFile => $currFile) {
            switch ($order) {
                default:
                case "nom":
                    ksort($affichageFile[$keyFile]["items"]);
                    break;
                case "date":
                    krsort($affichageFile[$keyFile]["items"]);
            }
        }

        return $affichageFile;
    }

    /**
     * Retourne les destinataires possibles pour un objet
     *
     * @param CPatient|CConsultation|CSejour $object       Objet concerné
     * @param string                         $address_type The type of address to use (mail, mssante or apicrypt)
     *
     * @return CDestinataire[]
     */
    public static function getDestinatairesCourrier(CMbObject $object, string $address_type = 'mail'): array
    {
        $destinataires = [];

        /* In case of a CPrescription, get the linked object instead */
        if ($object instanceof CPrescription) {
            $object = $object->loadRefObject();
        }
        if ($object instanceof CEvenementPatient) {
            $object = $object->loadRefPatient();
        }

        if (!in_array($object->_class, ["COperation", "CConsultation", "CPatient", "CSejour", 'CConsultAnesth'])) {
            return $destinataires;
        }

        $receivers = (new MailReceiverService($object))->getReceivers($address_type);
        foreach ($receivers as $_receivers_by_class) {
            foreach ($_receivers_by_class as $_receiver) {
                if (
                    !isset($_receiver->nom)
                    || strlen($_receiver->nom) == 0
                    || $_receiver->nom === " "
                    || (!$_receiver->email && $address_type != 'apicrypt')
                ) {
                    continue;
                }

                $destinataires[] = $_receiver;
            }
        }

        return $destinataires;
    }

    /**
     * Returns the name to display for a dmp type document code
     *
     * @param string $code - dmp document type code
     *
     * @return mixed
     * @throws Exception
     */
    public static function getDisplayNameDmp(string $code): ?string
    {
        $types = self::getDmpTypeDocs();

        return CMbArray::get($types, $code);
    }

    /**
     * Returns an array of all dmp document types
     *
     * @return array
     * @throws Exception
     */
    public static function getDmpTypeDocs(): array
    {
        $jdv_type = CANSValueSet::load('typeCode');

        $type_docs = [];
        foreach ($jdv_type as $_type) {
            $code             = CMbArray::get($_type, "codeSystem") . "^" . CMbArray::get($_type, "code");
            $disp_name        = CMbArray::get($_type, "displayName");
            $type_docs[$code] = $disp_name;
        }

        return $type_docs;
    }

    /**
     * @see parent::getProps()
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props["file_category_id"] = "ref class|CFilesCategory fieldset|default";
        $props["etat_envoi"]       = "enum notNull list|oui|non|obsolete default|non show|0 fieldset|default";
        $props["author_id"]        = "ref class|CMediusers fieldset|extra";
        $props["private"]          = "bool default|0 show|0 fieldset|default";
        $props["annule"]           = "bool default|0 show|0 fieldset|default";
        $props["doc_size"]         = "num min|0 show|0 fieldset|default";

        $props["object_id"]    = "ref notNull class|CMbObject meta|object_class cascade fieldset|extra";
        $props["object_class"] = "str notNull class show|0 fieldset|extra show|1";

        $props['masquage_patient']              = 'bool';
        $props['masquage_praticien']            = 'bool';
        $props['masquage_representants_legaux'] = 'bool';

        $type_doc_dmp = "";
        if (CModule::getActive("dmp")) {
            $type_doc_dmp = CDMPTools::getTypesDoc();
        }
        $props["type_doc_dmp"] = (empty($type_doc_dmp) ? "str" : "enum list|$type_doc_dmp");
        $sisra_types           = "";
        if (CModule::getActive("sisra")) {
            $sisra_types = CSisraTools::getSisraTypeDocument();
            $sisra_types = implode("|", $sisra_types);
        }
        $props["type_doc_sisra"] = (empty($sisra_types) ? "str" : "enum list|$sisra_types") . " fieldset|extra";
        $props["remis_patient"]  = "bool default|0 fieldset|default";
        $props["send"]           = "bool default|1 fieldset|default";

        $props["_extensioned"]    = "str notNull";
        $props["_no_extension"]   = "str notNull";
        $props["_file_size"]      = "str show|1";
        $props["_file_date"]      = "dateTime";
        $props["_send_problem"]   = "text";
        $props["_category_id"]    = "ref class|CFilesCategory";
        $props["_version"]        = "num";
        $props["_ext_cabinet_id"] = "num";

        return $props;
    }

    /**
     * @param CStoredObject $object
     *
     * @return void
     * @deprecated
     *
     */
    public function setObject(CStoredObject $object): void
    {
        CMbMetaObjectPolyfill::setObject($this, $object);
    }

    /**
     * Return idex type if it's special (e.g. AppFine/...)
     *
     * @param CIdSante400 $idex Idex
     *
     * @return string|null
     */
    public function getSpecialIdex(CIdSante400 $idex): ?string
    {
        if (CModule::getActive("appFineClient")) {
            if ($idex_type = CAppFineClient::getSpecialIdex($idex)) {
                return $idex_type;
            }
        }

        return null;
    }

    /**
     * Get last dmp document for file
     *
     * @return CDMPDocument
     */
    public function loadRefLastDMPDocument(): CDMPDocument
    {
        $document_dmp = new CDMPDocument();
        $document_dmp->getLastSend($this->_id, $this->_class);

        return $this->_ref_last_dmp_document = $document_dmp;
    }

    /**
     * Return the action on the document for the DMP
     *
     * @return CDMPDocument[]|CStoredObject[]
     * @throws Exception
     */
    public function loadDocumentDMP(): ?array
    {
        if (!CModule::getActive("dmp")) {
            return null;
        }

        return $this->_refs_dmp_document = $this->loadBackRefs("dmp_documents", "date DESC");
    }

    /**
     * Return the action on the document for the DMP
     *
     * @return CXDSDocument[]|CStoredObject[]
     * @throws Exception
     */
    public function loadDocumentXDS(): array
    {
        return $this->_refs_xds_document = $this->loadBackRefs("xds_documents", "date DESC");
    }

    /**
     * Return the action on the document for the DMP
     *
     * @return CSisraDocument[]|CStoredObject[]
     * @throws Exception
     */
    public function loadDocumentSisra(): ?array
    {
        if (!CModule::getActive("sisra")) {
            return null;
        }

        return $this->_refs_sisra_document = $this->loadBackRefs("sisra_documents", "date DESC");
    }

    /**
     * Load documents reference
     *
     * @param CInteropActor $actor Interop actor
     *
     * @return CDocumentReference
     * @throws Exception
     */
    public function loadDocumentReferenceActor(CInteropActor $actor): CDocumentReference
    {
        $where = [
            "actor_id"    => " = '$actor->_id'",
            "actor_class" => " = '$actor->_class'",
        ];

        if ($document_reference = $this->loadDocumentsReference($where)) {
            return reset($document_reference);
        }

        return new CDocumentReference();
    }

    /**
     * Load documents reference
     *
     * @return CDocumentReference[]|CStoredObject[]
     * @throws Exception
     */
    public function loadDocumentsReference(array $where = []): array
    {
        return $this->_ref_documents_reference = $this->loadBackRefs(
            "document_reference",
            null,
            null,
            null,
            null,
            null,
            null,
            $where
        );
    }

    /**
     * Retrieve content as binary data
     *
     * @return string Binary Content
     */
    public function getBinaryContent(): ?string
    {
        return null;
    }

    /**
     * Retrieve extensioned like file name
     *
     * @return string Binary Content
     */
    public function getExtensioned(): ?string
    {
        return $this->_extensioned;
    }

    /**
     * @see parent::updateFormFields()
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        $this->_file_size = CMbString::toDecaBinary($this->doc_size);

        $this->getSendProblem();
        $this->loadRefCategory();

        self::makeIconName($this);
    }

    /**
     * Retrieve send problem user friendly message
     *
     * @return string Store-like problem message
     */
    public function getSendProblem(): ?string
    {
        if (!self::$check_send_problem) {
            return null;
        }

        if ($sender = self::getDocumentSender()) {
            $this->_send_problem = $sender->getSendProblem($this);
        }

        return $this->_send_problem;
    }

    /**
     * Try and instanciate document sender according to module configuration
     *
     * @return CDocumentSender sender or null on error
     */
    public static function getDocumentSender(): ?CDocumentSender
    {
        if (null == $system_sender = CAppUI::gconf("dPfiles CDocumentSender system_sender")) {
            return null;
        }

        if (!is_subclass_of($system_sender, CDocumentSender::class)) {
            trigger_error("Instanciation du Document Sender impossible.");

            return null;
        }

        return new $system_sender();
    }

    /**
     * Load category
     *
     * @return CFilesCategory
     * @throws Exception
     */
    public function loadRefCategory(): CFilesCategory
    {
        return $this->_ref_category = $this->loadFwdRef("file_category_id", true);
    }

    public static function makeIconName(CMbObject $object): ?string
    {
        switch (get_class($object)) {
            default:
            case CCompteRendu::class:
                $file_name = $object->nom;
                break;
            case CFile::class:
                $file_name = $object->file_name;
                break;
            case CExClass::class:
                $file_name = $object->name;
        }

        $max_length = 25;

        if (strlen($file_name) <= $max_length) {
            return $object->_icon_name = $file_name;
        }

        return $object->_icon_name = substr_replace(
            $file_name,
            " ... ",
            round($max_length / 2),
            round(-$max_length / 2)
        );
    }

    /**
     * @see parent::store()
     */
    public function store(): ?string
    {
        $this->completeField("etat_envoi");
        $this->completeField("object_class");
        $this->completeField("object_id");
        $this->completeField("file_category_id");

        // remove old code not supported by dmp when we re store file
        if ($this->type_doc_dmp && !$this->fieldModified('type_doc_dmp') && CModule::getActive("dmp")) {
            $available_codes_dmp = explode('|', CDMPTools::getTypesDoc());
            if (!in_array($this->type_doc_dmp, $available_codes_dmp)) {
                $this->type_doc_dmp = '';
            }
        }

        if (
            (!$this->_id && $this->file_category_id && !$this->type_doc_dmp)
            || ($this->fieldModified('file_category_id'))
        ) {
            $file_category = $this->loadRefCategory();
            if ($file_category->_id && $file_category->type_doc_dmp) {
                $this->type_doc_dmp = $file_category->type_doc_dmp;
            }
        }

        if ($msg = $this->handleSend()) {
            return $msg;
        }

        if ($msg = parent::store()) {
            return $msg;
        }

        if ($this->_ext_cabinet_id) {
            // If there is a cabinet id, store it as a external id
            $idex = CIdSante400::getMatch($this->_class, "cabinet_id", $this->_ext_cabinet_id, $this->_id);
            $idex->store();
        }

        return null;
    }

    /**
     * Handle document sending store behaviour
     *
     * @return string Store-like error message
     */
    public function handleSend(): ?string
    {
        if (!$this->_send) {
            return null;
        }

        $this->_send = false;

        if (null == $sender = self::getDocumentSender()) {
            return "Document Sender not available";
        }

        switch ($this->etat_envoi) {
            case "non":
                if (!$sender->send($this)) {
                    return "Erreur lors de l'envoi.";
                }
                CAppUI::setMsg("Document transmis.");
                break;
            case "oui":
                if (!$sender->cancel($this)) {
                    return "Erreur lors de l'invalidation de l'envoi.";
                }
                CAppUI::setMsg("Document annulé.");
                break;
            case "obsolete":
                if (!$sender->resend($this)) {
                    return "Erreur lors du renvoi.";
                }
                CAppUI::setMsg("Document annulé/transmis.");
                break;
            default:
                return "Fonction d'envoi '$this->etat_envoi' non reconnue.";
        }

        return null;
    }

    /**
     * @see parent::loadRefsFwd()
     */
    public function loadRefsFwd(): void
    {
        $this->loadTargetObject();
        $this->loadRefCategory();
        $this->loadRefAuthor();
    }

    /**
     * @param bool $cache
     *
     * @return bool|CStoredObject|CExObject|null
     * @throws Exception
     */
    public function loadTargetObject(bool $cache = true): ?CMbObject
    {
        return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
    }

    /**
     * Load author
     *
     * @return CMediusers
     * @throws Exception
     */
    public function loadRefAuthor(): CMediusers
    {
        return $this->_ref_author = $this->loadFwdRef("author_id", true);
    }

    /**
     * @see parent::getPerm()
     */
    public function getPerm($permType): bool
    {
        $this->loadRefAuthor();
        $this->loadRefCategory();

        // Permission de base
        $perm = parent::getPerm($permType);

        // Il faut au moins avoir le droit de lecture sur la catégories
        if ($this->file_category_id) {
            $perm &= $this->_ref_category->getPerm(PERM_READ);
        }

        $curr_user = CMediusers::get();

        // Gestion de la catégorie médicale
        if ($this->_ref_category->_id && $this->_ref_category->medicale) {
            $perm = $curr_user->isProfessionnelDeSante() ||
                $curr_user->isAdmin() ||
                $curr_user->isPMSI() ||
                ($curr_user->function_id == $this->_ref_author->function_id);
        }

        // Gestion d'un document confidentiel (cabinet ou auteur)
        if ($this->private) {
            switch (CAppUI::gconf("dPcompteRendu CCompteRendu private_owner_func")) {
                default:
                case "function":
                    $perm &= ($this->_ref_author->function_id === $curr_user->function_id);
                    break;
                case "owner":
                    $perm &= ($this->author_id === $curr_user->_id);
            }

            $perm |= $curr_user->isAdmin();
        }

        return $perm;
    }

    /**
     * Load aggregated doc item ownership
     *
     * @return array collection of arrays with docs_count, docs_weight and author_id keys
     */
    public function getUsersStats(): array
    {
        return [];
    }

    /**
     * Advanced user stats on modeles
     *
     * @param string[]|null $user_ids User IDs, null if no filter
     * @param string|null   $date_min Creation date minimal filter
     * @param string|null   $date_max Creation date maximal filter
     *
     * @return array collection of arrays with docs_count, docs_weight, object_class and category_id keys
     */
    public function getUsersStatsDetails($user_ids, $date_min = null, $date_max = null): array
    {
        return [];
    }

    /**
     * Advanced periodical stats on modeles
     *
     * @param string[]|null $user_ids     Owner user IDs, null if no filter
     * @param string|null   $object_class Document class filter
     * @param string|null   $category_id  Document category filter
     * @param int           $depth        Period count for each period types
     *
     * @return int[][] collection of arrays daily, weekly, monthly and yearly keys
     */
    public function getPeriodicalStatsDetails($user_ids, $object_class = null, $category_id = null, $depth = 10)
    {
        $detail = [
            "period"   => "yyyy",
            "count"    => 10,
            "weight"   => 20000,
            "date_min" => "yyyy-mm-dd hh:mm:ss",
            "date_max" => "yyyy-mm-dd hh:mm:ss",
        ];

        $sample = array_fill(0, $depth, $detail);

        return [
            "hour"  => $sample,
            "day"   => $sample,
            "week"  => $sample,
            "month" => $sample,
            "year"  => $sample,
        ];
    }

    /**
     * Disk usage of a user
     *
     * @param string $user_id User id, connected user by default
     *
     * @return array collection of arrays with total usage, last year usage and last month usage
     */
    public function getDiskUsage($user_id): array
    {
        return [];
    }

    /**
     * Return the patient
     *
     * @return CPatient|null
     */
    public function loadRelPatient(): ?CPatient
    {
        /** @var CPatient|IPatientRelated $object */
        $object = $this->loadTargetObject();
        if ($object instanceof CPatient) {
            return $object;
        }
        if (in_array(IPatientRelated::class, class_implements($object))) {
            return $object->loadRelPatient();
        }

        return null;
    }

    /**
     * @param string $sender   The sender's email address
     * @param string $receiver The receiver's email address
     *
     * @return string
     */
    public function makeHprimHeader(string $sender, string $receiver): string
    {
        $object   = $this->loadTargetObject();
        $receiver = explode('@', $receiver);
        $sender   = explode('@', $sender);

        /* Handle the case when the file is generated by Mediboard */
        if ($object->_class == 'CCompteRendu') {
            $object = $object->loadTargetObject();
        }

        $patient     = null;
        $record_id   = null;
        $record_date = null;
        switch ($object->_class) {
            case 'CConsultation':
                /** @var $object CConsultation */
                $patient = $object->loadRefPatient();
                $object->loadRefSejour();
                if ($object->_ref_sejour) {
                    $object->_ref_sejour->loadNDA();
                    $record_id = $object->_ref_sejour->_NDA;
                }
                $object->loadRefPlageConsult();
                $record_date = $object->_ref_plageconsult->getFormattedValue('date');
                break;
            case 'CConsultAnesth':
                /** @var $object CConsultAnesth */
                $patient = $object->loadRefPatient();
                $object->loadRefSejour();
                if ($object->_ref_sejour) {
                    $object->_ref_sejour->loadNDA();
                    $record_id = $object->_ref_sejour->_NDA;
                }
                $object->loadRefConsultation();
                $object->_ref_consultation->loadRefPlageConsult();
                $record_date = $object->_ref_consultation->_ref_plageconsult->getFormattedValue('date');
                break;
            case 'CSejour':
                /** @var $object CSejour */
                $patient = $object->loadRefPatient();
                $object->loadNDA();
                $record_id = $object->_NDA;
                $object->updateFormFields();
                $record_date = $object->getFormattedValue('_date_entree');
                break;
            case 'COperation':
                /** @var $object COperation */
                $patient = $object->loadRefPatient();
                $object->loadRefSejour();
                if ($object->_ref_sejour) {
                    $object->_ref_sejour->loadNDA();
                    $record_id = $object->_ref_sejour->_NDA;
                }

                /* Récupération de la date */
                if ($object->date) {
                    $record_date = $object->getFormattedValue('date');
                } else {
                    $object->loadRefPlageOp();
                    $record_date = $object->_ref_plageop->getFormattedValue('date');
                }
                break;
            case 'CPatient':
                $patient = $object;
                break;
            default:
                $patient = new CPatient();
        }

        $patient->loadIPP();
        $adresse = explode("\n", $patient->adresse);

        if (count($adresse) == 1) {
            $adresse[1] = "";
        } elseif (count($adresse) > 2) {
            $adr_tmp = $adresse;
            $adresse = [$adr_tmp[0]];
            unset($adr_tmp[0]);
            $adr_tmp   = implode(" ", $adr_tmp);
            $adresse[] = str_replace(["\n", "\r"], ["", ""], $adr_tmp);
        }

        return $patient->_IPP . "\n"
            . strtoupper($patient->nom) . "\n"
            . ucfirst($patient->prenom) . "\n"
            . $adresse[0] . "\n"
            . $adresse[1] . "\n"
            . $patient->cp . " " . $patient->ville . "\n"
            . $patient->getFormattedValue("naissance") . "\n"
            . $patient->matricule . "\n"
            . $record_id . "\n"
            . $record_date . "\n"
            . ".          $sender[0]\n"
            . ".          $receiver[0]\n\n";
    }

    /**
     * Count the receivers
     *
     * @return CDestinataireItem[]
     */
    public function loadRefsDestinataires(): array
    {
        return $this->_ref_destinataires = $this->loadBackRefs("destinataires");
    }

    /**
     * Check the electronic delivery status of the document
     */
    public function getDeliveryStatus(): void
    {
        if (!CModule::getActive("messagerie")) {
            return;
        }

        $this->_mail_recipients = CElectronicDelivery::getMailRecipients($this);
        $this->_sent_mail       = (bool)(count($this->_mail_recipients) > 0);

        $this->_apicrypt_recipients = CElectronicDelivery::getApicryptRecipients($this);
        $this->_sent_apicrypt       = (bool)(count($this->_apicrypt_recipients) > 0);

        $this->_mssante_recipients = CElectronicDelivery::getMssanteRecipients($this);
        $this->_sent_mssante       = (bool)(count($this->_mssante_recipients) > 0);
    }

    /**
     * @inheritdoc
     */
    public function loadView()
    {
        parent::loadView();

        if (CModule::getActive('messagerie')) {
            $this->loadRefDeliveries();
            if (is_array($this->_ref_deliveries)) {
                foreach ($this->_ref_deliveries as $delivery) {
                    $delivery->loadRefMessage();
                }
            }
        }

        $this->checkSendDocument();
        $this->countSynchronizedRecipients();
    }

    /**
     * Load the CElectronicDelivery
     *
     * @return CElectronicDelivery[]|null
     */
    public function loadRefDeliveries(): ?array
    {
        $this->_ref_deliveries = $this->loadBackRefs('deliveries');

        if (is_array($this->_ref_deliveries)) {
            $this->_count_deliveries = count($this->_ref_deliveries);
        }

        return $this->_ref_deliveries;
    }

    /**
     * Loading recipients synchronized with the document
     *
     * @return void
     * @throws Exception
     */
    public function countSynchronizedRecipients(): void
    {
        if (CModule::getActive('appFineClient')) {
            $this->checkSynchroAppFine();
        }

        if (CModule::getActive('dmp')) {
            $this->countDocumentDMP();
        }

        if (CModule::getActive('xds')) {
            $this->countDocumentXDS();
        }

        if (CModule::getActive('sisra')) {
            $this->countDocumentSisra();
        }

        if (CModule::getActive('oxSIHCabinet')) {
            $this->checkSynchroSIHCabinet();
        }

        if (CModule::getActive('oxCabinetSIH')) {
            $this->checkSynchroCabinetSIH();
        }
    }

    /**
     * Check status synchro file with AppFine
     *
     * @return int
     */
    public function checkSynchroAppFine(CInteropReceiver $receiver = null): int
    {
        if ($receiver) {
            // On essaye de récupérer une file_traceability pour ce document
            $last_file_traceability = $this->loadRefLastFileTraceability($receiver);
            if ($last_file_traceability->_id) {
                switch ($last_file_traceability->status) {
                    case "pending":
                        return $this->_status_appFineClient = 3;
                        break;
                    case "rejected":
                        return $this->_status_appFineClient = 5;
                        break;
                }
            }
        }

        return $this->_status_appFineClient = CAppFineClient::loadIdex($this)->_id ? 1 : 0;
    }

    /**
     * Get last file traceability
     *
     * @param CInteropReceiver $receiver
     *
     * @return CFileTraceability
     * @throws Exception
     */
    public function loadRefLastFileTraceability(CInteropReceiver $receiver = null): CFileTraceability
    {
        $file_traceability = new CFileTraceability();
        $where             = [
            "version"      => " = '$this->_version' ",
            "object_id"    => " = '$this->_id' ",
            "object_class" => " = '$this->_class' ",
        ];

        if ($receiver) {
            $where["actor_class"] = " = '$receiver->_class' ";
            $where["actor_id"]    = " = '$receiver->_id'";
        }

        $files_traceability = $file_traceability->loadList($where, "created_datetime DESC", 1);

        if ($files_traceability) {
            $file_traceability = reset($files_traceability);
            $file_traceability->getMasquage();

            return $this->_ref_last_file_traceability = $file_traceability;
        }

        return $this->_ref_last_file_traceability = $file_traceability;
    }

    /**
     * Count the action on the document for the DMP
     *
     * @param array $where Where
     *
     * @return int|null
     * @throws Exception
     */
    public function countDocumentDMP(array $where = []): ?int
    {
        if (!CModule::getActive("dmp")) {
            return null;
        }

        $this->checkSynchroDMP();

        return $this->_count_dmp_documents = $this->countBackRefs("dmp_documents", $where);
    }

    /**
     * Check status synchro file with DMP
     *
     * @param CInteropReceiver $receiver receiver
     *
     * @return int|null
     * @throws Exception
     */
    public function checkSynchroDMP(CInteropReceiver $receiver = null): int
    {
        // On essaye de récupérer une file_traceability pour ce document
        if ($receiver) {
            $last_file_traceability = $this->loadRefLastFileTraceability($receiver);
            if ($last_file_traceability->_id) {
                switch ($last_file_traceability->status) {
                    case "pending":
                        return $this->_status_dmp = 3;
                    case "rejected":
                        return $this->_status_dmp = 5;
                    default:
                }
            }
        }

        // Récupération du cdmp_document pour la version actuelle
        /** @var CDMPDocument $dmp_document_actually */
        $dmp_document_actually = $this->loadLastBackRef('dmp_documents');

        // Synchro de fichier avec le DMP
        if ($dmp_document_actually->_id) {
            // fichier dépublié du DMP
            if ($dmp_document_actually->etat == "DELETE") {
                return $this->_status_dmp = 4;
            }

            // fichier synchro ou version antérieure synchro
            return $this->_status_dmp = $dmp_document_actually->document_item_version == $this->_version ? 1 : 2;
        }

        // fichier non synchro
        return $this->_status_dmp = 0;
    }

    /**
     * Count the action on the document for the DMP
     *
     * @param array $where Where
     *
     * @return int|null
     * @throws Exception
     */
    public function countDocumentXDS(array $where = []): ?int
    {
        return $this->_count_xds_documents = $this->countBackRefs("xds_documents", $where);
    }

    /**
     * Count the action on the document for the DMP
     *
     * @param array $where Where
     *
     * @return int|null
     * @throws Exception
     */
    public function countDocumentSisra(array $where = []): ?int
    {
        if (!CModule::getActive("sisra")) {
            return null;
        }

        $this->checkSynchroSisra();

        return $this->_count_sisra_documents = $this->countBackRefs("sisra_documents", $where);
    }

    public function checkSynchroSisra(CInteropReceiver $receiver = null): int
    {
        if ($receiver) {
            // On essaye de récupérer une file_traceability pour ce document
            $last_file_traceability = $this->loadRefLastFileTraceability($receiver);
            if ($last_file_traceability->_id) {
                switch ($last_file_traceability->status) {
                    case "pending":
                        return $this->_status_sisra = 3;
                    case "rejected":
                        return $this->_status_sisra = 5;
                }
            }
        }

        return $this->_status_sisra = $this->countBackRefs("sisra_documents") > 0 ? 1 : 0;
    }

    /**
     * Check status synchro file with AppFine
     *
     * @param CInteropReceiver $receiver
     *
     * @return int
     * @throws Exception
     */
    public function checkSynchroSIHCabinet(CInteropReceiver $receiver = null): int
    {
        if ($receiver) {
            // On essaye de récupérer une file_traceability pour ce document
            $last_file_traceability = $this->loadRefLastFileTraceability($receiver);
            if ($last_file_traceability->_id) {
                switch ($last_file_traceability->status) {
                    case "pending":
                        return $this->_status_sih_cabinet = 2;
                    case "rejected":
                        return $this->_status_sih_cabinet = 3;
                    default:
                }
            }
        }

        return $this->_status_sih_cabinet = CSIHCabinet::loadIdex($this, CSIHCabinet::DOCUMENT_TAG)->_id ? 1 : 0;
    }

    /**
     * Check status synchro file with AppFine
     *
     * @param CInteropReceiver $receiver
     *
     * @return int
     * @throws Exception
     */
    public function checkSynchroCabinetSIH(CInteropReceiver $receiver = null): int
    {
        if ($receiver) {
            // On essaye de récupérer une file_traceability pour ce document
            $last_file_traceability = $this->loadRefLastFileTraceability($receiver);
            if ($last_file_traceability->_id) {
                switch ($last_file_traceability->status) {
                    case "pending":
                        return $this->_status_cabinet_sih = 2;
                    case "rejected":
                        return $this->_status_cabinet_sih = 3;
                    default:
                }
            }
        }

        return $this->_status_cabinet_sih = CCabinetSIH::loadIdex($this, CCabinetSIH::DOCUMENT_TAG)->_id ? 1 : 0;
    }

    public function getPatient(): ?CPatient
    {
        $target = $this->loadTargetObject();

        $patient = null;

        if (get_class($target) === CPatient::class) {
            $patient = $target;
        } elseif ($target instanceof IPatientRelated) {
            $patient = $target->loadRelPatient();
        }

        return $patient;
    }

    /**
     * Si le contexte du document est lié à un patient, on vérifie que ce dernier autorise l'envoi à un professionnel
     *
     * @return bool
     * @throws Exception
     */
    public function isMSSanteProSendable(): bool
    {
        if ($this->masquage_praticien) {
            return false;
        }

        if ($patient = $this->getPatient()) {
            return $patient->isConsentMSSantePro();
        }

        return true;
    }

    /**
     * Si le contexte du document est lié à un patient, on vérifie que ce dernier autorise la réception du document
     * à son adresse mssanté
     *
     * @return bool
     */
    public function isMSSantePatientSendable(): bool
    {
        if ($this->masquage_patient || $this->masquage_representants_legaux) {
            return false;
        }

        if ($patient = $this->getPatient()) {
            return $patient->isConsentMSSantePatient();
        }

        return true;
    }

    public function loadRefReadFile(): CReadFile
    {
        return $this->_ref_read_file = $this->loadUniqueBackRef("read_files");
    }

    /**
     * Loads the document's sending information
     *
     * @return void
     * @throws Exception
     */
    public function checkSendDocument(): void
    {
        $this->isSendToMSSante();
    }

    /**
     * Sanitize string for compatibility
     *
     * @param string $string
     *
     * @return string
     */
    public function sanitizeName(string $string): string
    {
        return preg_replace("/([^A-Za-z\d.])/", "", CMbString::removeAccents($string));
    }

    /**
     * Load medimail messages that contain this document (only send)
     *
     * @return array
     * @throws Exception
     */
    private function loadBackRefsMedimailMessages(): array
    {
        return
            $this->loadBackRefs('medimail_messages', null, null, null, null, null, null, ["sent" => "= '1'"]);
    }

    /**
     * Look if the document has been sent by MSSante or not
     *
     * @return void
     * @throws Exception
     */
    private function isSendToMSSante(): void
    {
        if (!CModule::getActive('medimail')) {
            return;
        }

        /** @var CMedimailMessage[] $medimail_messages */
        $medimail_messages = $this->loadBackRefsMedimailMessages();

        $this->_is_send_mssante = [
            CMedimailService::TYPE_MSSANTE_PATIENT => false,
            CMedimailService::TYPE_MSSANTE_PRO     => false,
        ];

        foreach ($medimail_messages as $message) {
            if (strpos($message->signatories, CPatient::MSSANTE_MAIL_DOMAIN) != false) {
                $this->_is_send_mssante[CMedimailService::TYPE_MSSANTE_PATIENT] = true;
            } else {
                $this->_is_send_mssante[CMedimailService::TYPE_MSSANTE_PRO] = true;
            }
        }
    }

    /**
     * @return CGroups|null
     * @throws Exception
     */
    public function loadRelGroup(): ?CGroups
    {
        $target = $this->loadTargetObject();
        if ($target instanceof IGroupRelated) {
            return $target->loadRelGroup();
        }

        return null;
    }
}
