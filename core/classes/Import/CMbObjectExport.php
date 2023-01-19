<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Import;

use DOMElement;
use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObject;
use Ox\Core\CMbPath;
use Ox\Core\CMbString;
use Ox\Core\CMbXMLDocument;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Core\Logger\LoggerLevels;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Ccam\CDevisCodage;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CWkhtmlToPDF;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Facturation\CFacture;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\CFactureEtablissement;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\System\CContentHTML;
use Throwable;

/**
 * Object exporting utility class
 */
class CMbObjectExport
{
    public const DEFAULT_DEPTH = 20;

    public const MINIMIZED_BACKREFS_TREE = [
        "CPatient"                    => [
            "identifiants",
            "notes",
            "contantes",
            "correspondants",
            "correspondants_patient",
            "dossier_medical",
        ],
        "CDossierMedical"             => [
            "antecedents",
            "traitements",
            "prescriptions",
        ],
        "CPrescription"               => [
            "prescription_line_medicament",
            "prescription_line_element",
        ],
        "CPrescriptionLineMedicament" => [
            "prise_posologie",
        ],
        "CPrescriptionLineElement"    => [
            "prise_posologie",
        ],
    ];

    public const PRESCRIPTION_BACKREFS_TREE = [
        'CPatient'      => [
            'sejours',
        ],
        'CSejour'       => [
            'prescriptions',
        ],
        'CPrescription' => [
            'files',
            'documents',
        ],
    ];

    public const DEFAULT_BACKREFS_TREE = [
        "CPatient"                    => [
            "identifiants",
            "notes",
            "files",
            "documents",
            "permissions",
            "observation_result_sets",
            "constantes",
            "contextes_constante",
            "consultations",
            "correspondants",
            "correspondants_patient",
            "sejours",
            "dossier_medical",
            "correspondants_courrier",
            "grossesses",
            "allaitements",
            "patient_observation_result_sets",
            "patient_links",
            'arret_travail',
            "facture_patient_consult",
            "facture_patient_sejour",
            "bmr_bhre",
        ],
        "CConsultation"               => [
            'identifiants',
            "files",
            "documents",
            "notes",
            "consult_anesth",
            "examaudio",
            "examcomp",
            "examnyha",
            "exampossum",
            "sejours_lies",
            "intervs_liees",
            "consults_liees",
            "prescriptions",
            "evenements_patient",

            // Codable
            "facturable",
            "actes_ngap",
            "actes_ccam",
            "codages_ccam",
        ],
        "CConsultAnesth"              => [
            "files",
            "documents",
            "notes",
            "techniques",
        ],
        "CSejour"                     => [
            "identifiants",
            "files",
            "documents",
            "notes",
            "dossier_medical",
            "operations",
            "consultations",

            // Codable
            "facturable",
            "actes_ngap",
            "actes_ccam",
            "codages_ccam",
        ],
        "COperation"                  => [
            "files",
            "documents",
            "notes",
            "anesth_perops",

            // Codable
            "facturable",
            "actes_ngap",
            "actes_ccam",
        ],
        "CCompteRendu"                => [
            "files",
        ],
        "CDossierMedical"             => [
            "antecedents",
            "traitements",
            "etats_dent",
            "prescriptions",
            "pathologies",
            'evenements_patient',
        ],
        "CFactureCabinet"             => [
            "items",
            "reglements",
            "relance_fact",
            "rejets",
            "envois_cdm",
            "facture_liaison",
            'files',
        ],
        "CFactureEtablissement"       => [
            "items",
            "reglements",
            "relance_fact",
            "facture_liaison",
            "rejets",
            "envois_cdm",
            'files',
        ],
        "CPrescription"               => [
            "prescription_line_medicament",
            'prescription_line_element',
            "files",
        ],
        "CPrescriptionLineMedicament" => [
            "prise_posologie",
        ],
        'CElementPrescription'        => [
            'files',
        ],
        "CEvenementPatient"           => [
            'files',
            'documents',
            'facturable',
        ],
    ];

    public const MINIMIZED_FWREFS_TREE = [
        "CPatient"                    => [
            "medecin_traitant",
        ],
        "CMediusers"                  => [
            "user_id",
        ],
        "CCorrespondant"              => [
            "patient_id",
            "medecin_id",
        ],
        "CPrescription"               => [
            "praticien_id",
            "function_id",
            "group_id",
        ],
        "CPrescriptionLineMedicament" => [
            "praticien_id",
            "creator_id",
            "extension_produit_unite_id",
        ],
        "CPrescriptionLineElement"    => [
            "praticien_id",
            "creator_id",
            "element_prescription_id",
        ],
        "CElementPrescription"        => [
            "category_prescription_id",
        ],
        "CPrisePosologie"             => [
            "moment_unitaire_id",
            "object_id",
        ],
        "CCategoryPrescription"       => [
            "group_id",
            "function_id",
            "user_id",
        ],
    ];

    public const DEFAULT_FWREFS_TREE = [
        "CPatient"              => [
            "medecin_traitant",
        ],
        "CConstantesMedicales"  => [
            "context_id",
            "patient_id",
            "user_id",
        ],
        "CConsultation"         => [
            "plageconsult_id",
            "sejour_id",
            "grossesse_id",
            "patient_id",
            "consult_related_id",
        ],
        "CConsultAnesth"        => [
            "consultation_id",
            "operation_id",
            "sejour_id",
            "chir_id",
        ],
        "CPlageconsult"         => [
            "chir_id",
        ],
        "CSejour"               => [
            "patient_id",
            "praticien_id",
            "service_id",
            "group_id",
            "grossesse_id",
            "uf_medicale_id",
            "uf_soins_id",
            "uf_hebergement_id",
        ],
        "COperation"            => [
            "sejour_id",
            "chir_id",
            "anesth_id",
            "plageop_id",
            "salle_id",
            "type_anesth",
            "consult_related_id",
            "prat_visite_anesth_id",
            "sortie_locker_id",
        ],
        "CGrossesse"            => [
            "group_id",
            "parturiente_id",
        ],
        "CCorrespondant"        => [
            "patient_id",
            "medecin_id",
        ],
        "CMediusers"            => [
            "user_id",
        ],
        "CPlageOp"              => [
            "chir_id",
            "anesth_id",
            "spec_id",
            "salle_id",
        ],

        // Actes
        "CActeCCAM"             => [
            "executant_id",
        ],
        "CActeNGAP"             => [
            "executant_id",
        ],
        "CFraisDivers"          => [
            "executant_id",
        ],
        // Fin Actes

        // Facturation
        "CFactureItem"          => [
            "object_id",
            "executant_id",
        ],
        "CFactureLiaison"       => [
            "facture_id",
            "object_id",
        ],
        "CFactureCabinet"       => [
            "group_id",
            "patient_id",
            "praticien_id",
            "coeff_id",
            "category_id",
            "assurance_maladie",
            "assurance_accident",
        ],
        "CFactureEtablissement" => [
            "group_id",
            "patient_id",
            "praticien_id",
            "coeff_id",
            "category_id",
            "assurance_maladie",
            "assurance_accident",
        ],
        "CReglement"            => [
            "banque_id",
            "object_id",
        ],
        "CFactureCategory"      => [
            "group_id",
            "function_id",
        ],
        "CFactureCoeff"         => [
            "praticien_id",
            "group_id",
        ],
        "CFactureRejet"         => [
            "praticien_id",
            "facture_id",
        ],
        "CRelance"              => [
            "object_id",
        ],
        "CRetrocession"         => [
            "praticien_id",
        ],
        // Fin facturation

        "CTypeAnesth"                 => [
            "group_id",
        ],
        "CFile"                       => [
            "object_id",
            "author_id",
            "file_category_id",
        ],
        "CCompteRendu"                => [
            "object_id",
            "author_id",
            "file_category_id",

            "user_id",
            "function_id",
            "group_id",

            "content_id",

            "locker_id",
        ],
        "CPrisePosologie"             => [
            "object_id",
            "moment_unitaire_id",
        ],
        "CPathologie"                 => [
            "owner_id",
        ],
        "CEvenementPatient"           => [
            "praticien_id",
            "owner_id",
            "traitement_user_id",
            "type_evenement_patient_id",
        ],
        "CTypeEvenementPatient"       => [
            "function_id",
        ],
        "CPrescription"               => [
            "praticien_id",
            "function_id",
            "group_id",
        ],
        "CPrescriptionLineMedicament" => [
            "praticien_id",
            "creator_id",
            "extension_produit_unite_id",
        ],
        "CPrescriptionLineElement"    => [
            "praticien_id",
            "creator_id",
            "element_prescription_id",
        ],
        "CElementPrescription"        => [
            "category_prescription_id",
        ],
        "CCategoryPrescription"       => [
            "group_id",
            "function_id",
            "user_id",
        ],
    ];

    public const NOTIF_BACK_TREE = [
        'CTypeEvenementPatient' => [
            'notification',
        ],
    ];

    public const NOTIF_FW_TREE = [
        "CNotificationEvent" => [
            "praticien_id",
            "group_id",
            "function_id",
            "object_id",
        ],
    ];

    /** @var CMbXMLDocument */
    public $doc;

    /** @var CMbObject */
    public $object;

    /** @var array */
    public $backrefs_tree;

    /** @var array */
    public $fwdrefs_tree;

    /** @var array */
    public $anonymize_fields;

    /** @var int */
    public $depth = self::DEFAULT_DEPTH;

    /** @var bool */
    public $empty_values = true;

    /** @var bool */
    public $anonymize_values = false;

    /** @var callable Callback executed when object is exported */
    protected $object_callback;

    /** @var callable Callback executed when object is exported, which tells if the object is to be exported (returns a boolean) */
    protected $filter_callback;

    /** @var array */
    protected $hashs = [];

    /**
     * Trim no break space and 0xFF chars
     *
     * @param string $s String to trim
     *
     * @return string
     */
    protected function trimString(?string $s)
    {
        return trim(trim($s ?? ""), "\xA0\xFF");
    }

    /**
     * Export constructor
     *
     * @param CMbObject  $object        Object to export
     * @param array|null $backrefs_tree Backrefs tree
     *
     * @throws CMbException
     */
    public function __construct(CMbObject $object = null, $backrefs_tree = [])
    {
        if ($object) {
            if (!$object->getPerm(PERM_READ)) {
                throw new CMbException("Permission denied");
            }

            $this->object        = $object;
            $this->backrefs_tree = isset($backrefs_tree) ? $backrefs_tree : $object->getExportedBackRefs();
        }
    }

    /**
     * Callback exexuted on each object
     *
     * @param callable $callback The callback
     *
     * @return void
     */
    public function setObjectCallback(callable $callback)
    {
        $this->object_callback = $callback;
    }

    /**
     * Callback exexuted on each object to tell if it has to be exported
     *
     * @param callable $callback The callback
     *
     * @return void
     */
    public function setFilterCallback(callable $callback)
    {
        $this->filter_callback = $callback;
    }

    /**
     * Set the forward refs tree to export
     *
     * @param array $fwdrefs_tree Forward refs tree to export
     *
     * @return void
     */
    public function setForwardRefsTree($fwdrefs_tree)
    {
        $this->fwdrefs_tree = $fwdrefs_tree;
    }

    /**
     * Export to DOM
     *
     * @return CMbXMLDocument
     */
    public function toDOM()
    {
        $this->doc               = new CMbXMLDocument("utf-8");
        $this->doc->formatOutput = true;
        $root                    = $this->doc->createElement("mediboard-export");
        $root->setAttribute("date", CMbDT::dateTime());
        $root->setAttribute("root", $this->object->_guid);
        $this->doc->appendChild($root);

        $this->_toDOM($this->object, $this->depth);
        $this->hashToDOM();

        return $this->doc;
    }

    /**
     * Convert a list of object to DOM
     *
     * @param array $objects Objects to convert to DOM
     *
     * @return CMbXMLDocument
     */
    public function objectListToDOM($objects)
    {
        $this->doc               = new CMbXMLDocument('utf-8');
        $this->doc->formatOutput = true;
        $root                    = $this->doc->createElement("mediboard-export");
        $root->setAttribute("date", CMbDT::dateTime());
        $root->setAttribute("root", $this->object->_guid);
        $this->doc->appendChild($root);

        foreach ($objects as $_obj) {
            $this->_toDOM($_obj, $this->depth);
        }

        return $this->doc;
    }

    /**
     * Append an object to the DOM
     *
     * @param CStoredObject $object Object to append
     *
     * @return DOMElement|null
     */
    public function appendObject(CStoredObject $object)
    {
        if (!$object->_id) {
            $object->_id = "none";
        }

        return $this->_toDOM($object, 1);
    }

    /**
     * Convert an objet to a DOMElement. Export the refs and the backs if they are in fw_tree and back_tree
     */
    protected function _toDOM(CStoredObject $object, int $depth): ?DOMElement
    {
        if (!$this->canExportObject($object, $depth)) {
            return null;
        }

        $doc         = $this->doc;
        $object_node = $doc->getElementById($object->_guid);

        // Objet deja exporté
        if ($object_node) {
            return $object_node;
        }

        $object_node = $this->createObjectNode($doc, $object);

        $doc->documentElement->appendChild($object_node);

        foreach ($object->getExportableFields() as $key => $value) {
            $this->addFieldToNode($doc, $object_node, $object, $depth, $key, $value);
        }

        // Apply object callback
        if ($this->object_callback && is_callable($this->object_callback)) {
            call_user_func($this->object_callback, $object, $object_node, $depth);
        }

        // If no back refs in the tree for object return
        if (isset($this->backrefs_tree[$object->_class])) {
            foreach ($object->_backProps as $backName => $backProp) {
                $this->addBackRef($object, $backName, $depth);
            }
        }

        return $object_node;
    }

    /**
     * Tell if we can export the object or not.
     *
     * $depth must be more than 0, $object must have an id and the current user must have read write on $object.
     * If a filter function is provided, it must return true.
     */
    protected function canExportObject(CStoredObject $object, int $depth): bool
    {
        return $depth > 0 && $object->_id && $object->getPerm(PERM_READ)
            && (
                !$this->filter_callback
                || (
                    $this->filter_callback
                    && is_callable($this->filter_callback)
                    && call_user_func($this->filter_callback, $object)
                )
            );
    }

    /**
     * Create a basic DOMElement from an objet with it's class, it's guid as the id of the node.
     * Add the node to the XML Document.
     */
    protected function createObjectNode(CMbXMLDocument $doc, CStoredObject $object): DOMElement
    {
        $object_node = $doc->createElement("object");
        $object_node->setAttribute('class', $object->_class);
        $object_node->setAttribute('id', $object->_guid);
        $object_node->setIdAttribute('id', true);

        return $object_node;
    }

    /**
     * Add a field to the node.
     */
    protected function addFieldToNode(
        CMbXMLDocument $doc,
        DOMElement $object_node,
        CStoredObject $object,
        int $depth,
        string $key,
        ?string $value
    ): void {
        $_fwd_spec = $object->_specs[$key];
        if ($_fwd_spec instanceof CRefSpec) {
            $this->addFwRefField($object_node, $object, $key, $depth);
        } else {
            $this->addScalarField($doc, $object_node, $object, $key, $value);
        }
    }

    /**
     * Add a ref field as an attribute to the node. The guid of the ref will be added to the attribute $key of the node.
     *
     * @throws Exception
     */
    protected function addFwRefField(DOMElement $object_node, CStoredObject $object, string $key, int $depth): void
    {
        if (!$this->isExportableRef($object, $key)) {
            return;
        }

        $guid = null;

        $_object = $object->loadFwdRef($key);

        if ($_object && $_object->_id) {
            $this->_toDOM($_object, $depth - 1);

            $guid = $_object->_guid;

            // Special treatment for CFile and CCompteRendu
            if ($key === 'object_id' && $object instanceof CDocumentItem) {
                [$object_class, $object_id] = self::getObjectTargetForExport($object);
                $guid = $object_class . '-' . $object_id;
            }
        }

        if ($this->empty_values || $guid) {
            $object_node->setAttribute($key, $guid ?? '');
        }
    }

    /**
     * Add a scalar field to the current node by appending a child node to it.
     */
    private function addScalarField(
        CMbXMLDocument $doc,
        DOMElement $object_node,
        CStoredObject $object,
        string $key,
        ?string $value
    ): void {
        $value = $this->trimString($value);

        if ($this->empty_values || ($value !== "" && $value !== null)) {
            if ($object instanceof CContentHTML && $key === 'content') {
                $value = str_replace('&', '&amp;', $value);
            }

            if ($key === 'object_class' && $object instanceof CDocumentItem) {
                [$object_class,] = self::getObjectTargetForExport($object);
                $value = $object_class;
            }

            $doc->insertTextElement($object_node, "field", $value, ["name" => $key]);
        }
    }

    /**
     * Add a backref to the XML Document (if the backref is declared in the tree).
     * Add all the objets for the backref, will call _toDOM for each object.
     *
     * @throws Exception
     */
    protected function addBackRef(CStoredObject $object, string $backName, int $depth): void
    {
        if (!in_array($backName, $this->backrefs_tree[$object->_class])) {
            return;
        }

        $_backspec = $object->makeBackSpec($backName);

        // Add fwd ref field value for each object in the collection
        if ($_backspec) {
            $_class = $_backspec->class;
            if (!isset($this->fwdrefs_tree[$_class])) {
                $this->fwdrefs_tree[$_class] = [];
            }

            if (!array_key_exists($_backspec->field, $this->fwdrefs_tree[$_class])) {
                $this->fwdrefs_tree[$_class][] = $_backspec->field;
            }
        }

        $objects = $object->loadBackRefs($backName);

        if ($objects) {
            foreach ($objects as $_object) {
                $this->_toDOM($_object, $depth - 1);
            }
        }
    }

    /**
     * A ref is exportable if
     * - it's not the primary key (avoid inifite loop)
     * - the className field of the CMbFieldSpec is the object class (it's not a spec from another object)
     * - the class is present in the fw_tree of exportable objects and the $key is in the array
     *
     * Special check for CMediusers::$user_id which is primary key but also a ref to CUser
     */
    private function isExportableRef(CStoredObject $object, string $key): bool
    {
        return ($key !== $object->_spec->key || $object instanceof CMediusers)
            && $object->_specs[$key]->className === $object->_class
            && isset($this->fwdrefs_tree[$object->_class])
            && in_array($key, $this->fwdrefs_tree[$object->_class]);
    }

    /**
     * Add a hash to the XML file
     *
     * @return void
     */
    private function hashToDOM()
    {
        foreach ($this->hashs as $_hash_name => $_hash) {
            $object_node = $this->doc->createElement("hash");
            $object_node->setAttribute('hash_name', $_hash_name);
            $object_node->setAttribute('hash_value', $_hash);
            $this->doc->documentElement->appendChild($object_node);
        }
    }

    /**
     * Stream in text/xml mimetype
     *
     * @param bool $download Force download
     *
     * @return void
     */
    public function streamXML(bool $download = true)
    {
        $this->stream("text/xml", $download);
    }

    /**
     * Stream the DOM
     *
     * @param string $mimetype Mime type type
     * @param bool   $download Force download
     *
     * @return void
     */
    private function stream(string $mimetype, bool $download = true)
    {
        $xml  = $this->toDOM()->saveXML();
        $date = CMbDT::dateTime();

        if ($download) {
            header("Content-Disposition: attachment;filename=\"{$this->object} - $date.xml\"");
        }

        header("Content-Type: $mimetype");
        header("Content-Length: " . strlen($xml));

        echo $xml;
    }

    /**
     * @param string $hash_name Hash name
     * @param string $hash      Hash
     *
     * @return void
     */
    public function addHash($hash_name, $hash)
    {
        $this->hashs[$hash_name] = $hash;
    }

    /**
     * Get all the patients
     *
     * @param int   $start Start at
     * @param int   $step  Number of patients to get
     * @param array $order Order to retrieve patients
     *
     * @return array
     */
    public static function getAllPatients($start = 0, $step = 100, $order = null)
    {
        $patient = new CPatient();
        $ds      = $patient->getDS();

        $group = CGroups::loadCurrent();

        $ljoin_consult = [
            "consultation"        => "consultation.patient_id = patients.patient_id",
            "plageconsult"        => "plageconsult.plageconsult_id = consultation.plageconsult_id",
            'users_mediboard'     => 'plageconsult.chir_id = users_mediboard.user_id',
            'functions_mediboard' => 'functions_mediboard.function_id = users_mediboard.user_id',
        ];

        $where_consult = [
            "consultation.annule"          => " = '0'",
            'functions_mediboard.group_id' => $ds->prepare('= ?', $group->_id),
        ];


        $patient_ids_consult = $patient->loadIds($where_consult, $order, null, "patients.patient_id", $ljoin_consult);

        $ljoin_sejour = [
            "sejour" => "sejour.patient_id = patients.patient_id",
        ];

        $where_sejour = [
            "sejour.annule"   => "= '0'",
            'sejour.group_id' => $ds->prepare('= ?', $group->_id),
        ];


        $patient_ids_sejour = $patient->loadIds($where_sejour, $order, null, "patients.patient_id", $ljoin_sejour);

        $patient_ids = array_merge($patient_ids_consult, $patient_ids_sejour);
        $patient_ids = array_unique($patient_ids);

        $total = count($patient_ids);

        if ($step) {
            $patient_ids = array_slice($patient_ids, $start, $step);
        }

        $where = [
            "patient_id" => $patient->getDS()->prepareIn($patient_ids),
        ];

        /** @var CPatient[] $patients */
        $patients = $patient->loadList($where);

        return [$patients, $total];
    }

    /**
     * Get all the patients to export
     *
     * @param array             $praticien_ids      Praticiens ids to use
     * @param string            $date_min           Minimum consult and sejour date
     * @param string            $date_max           Maximum consult and sejour date
     * @param int               $start              Start at
     * @param int               $step               Number of patients to retrieve
     * @param array|string|null $order              Order to get patients
     * @param string            $type               Type to search for (consult or sejour)
     * @param string            $export_patient_ids Patient ids to use
     *
     * @return array
     */
    public static function getPatientsToExport(
        array $praticien_ids,
        ?string $date_min = null,
        ?string $date_max = null,
        int $start = 0,
        int $step = null,
        $order = null,
        ?string $type = null,
        array $export_patient_ids = []
    ) {
        $patient = new CPatient();
        $ds      = $patient->getDS();

        $patient_ids_consult = [];

        if (!$type || $type == 'consult') {
            $ljoin_consult = [
                "consultation" => "consultation.patient_id = patients.patient_id",
                "plageconsult" => "plageconsult.plageconsult_id = consultation.plageconsult_id",
            ];

            $where_consult = [];

            $where_consult["plageconsult.chir_id"] = $ds->prepareIn($praticien_ids);
            $where_consult["consultation.annule"]  = " = '0'";

            if ($export_patient_ids && (count($export_patient_ids) > 0)) {
                $where_consult["patients.patient_id"] = $ds->prepareIn($export_patient_ids);
            }

            if ($date_min && $date_max) {
                $where_consult["plageconsult.date"] = $ds->prepare("BETWEEN ?1 AND ?2", $date_min, $date_max);
            } elseif ($date_min) {
                $where_consult["plageconsult.date"] = $ds->prepare(">= ?", $date_min);
            } elseif ($date_max) {
                $where_consult["plageconsult.date"] = $ds->prepare("<= ?", $date_max);
            }

            $patient_ids_consult = $patient->loadIds(
                $where_consult,
                $order,
                null,
                "patients.patient_id",
                $ljoin_consult
            );
        }

        $patient_ids_sejour = [];

        if (!$type || $type == 'sejour') {
            $ljoin_sejour = [
                "sejour" => "sejour.patient_id = patients.patient_id",
            ];

            $where_sejour                        = [];
            $where_sejour["sejour.praticien_id"] = $ds->prepareIn($praticien_ids);
            $where_sejour["annule"]              = " = '0'";

            if ($export_patient_ids && (count($export_patient_ids) > 0)) {
                $where_sejour["patients.patient_id"] = $ds->prepareIn($export_patient_ids);
            }

            if ($date_min && $date_max) {
                $where_sejour["sejour.sortie"] = $ds->prepare("BETWEEN ?1 AND ?2", $date_min, $date_max);
            } elseif ($date_min) {
                $where_sejour["sejour.sortie"] = $ds->prepare(">= ?", $date_min);
            } elseif ($date_max) {
                $where_sejour["sejour.sortie"] = $ds->prepare("<= ?", $date_max);
            }

            $patient_ids_sejour = $patient->loadIds($where_sejour, $order, null, "patients.patient_id", $ljoin_sejour);
        }

        $patient_ids = array_merge($patient_ids_consult, $patient_ids_sejour);
        $patient_ids = array_unique($patient_ids);

        $total = count($patient_ids);

        if ($step) {
            $patient_ids = array_slice($patient_ids, $start, $step);
        }

        $where = [
            "patient_id" => $patient->getDS()->prepareIn($patient_ids),
        ];

        /** @var CPatient[] $patients */
        $patients = $patient->loadList($where);

        return [$patients, $total];
    }

    /**
     * Get all the praticiens from the current group
     *
     * @param array $types    Types to load
     * @param bool  $actif    Only active users or not
     * @param int   $permType Perm type
     *
     * @return CMediusers[]
     */
    public static function getPraticiensFromGroup(
        $types = ["Chirurgien", "Anesthésiste", "Médecin", "Dentiste"],
        $actif = true,
        $permType = PERM_READ
    ) {
        $where = [];
        $ljoin = [];

        if ($actif) {
            $where["users_mediboard.actif"] = "= '1'";
        }

        // Filters on users values
        $ljoin["users"] = "`users`.`user_id` = `users_mediboard`.`user_id`";

        $ljoin["functions_mediboard"] = "functions_mediboard.function_id = users_mediboard.function_id";

        $group_id = CGroups::loadCurrent()->_id;
        $where[]  = "functions_mediboard.group_id = '$group_id'";

        // Filter on user type
        if (is_array($types)) {
            $utypes_flip = array_flip(CUser::$types);
            foreach ($types as &$_type) {
                $_type = $utypes_flip[$_type];
            }

            $where["users.user_type"] = CSQLDataSource::prepareIn($types);
        }

        $order    = "`users`.`user_last_name`, `users`.`user_first_name`";
        $group_by = ["user_id"];

        // Get all users
        $mediuser = new CMediusers();
        /** @var CMediusers[] $mediusers */
        $mediusers = $mediuser->loadList($where, $order, null, $group_by, $ljoin);

        // Mass fonction standard preloading
        CStoredObject::massLoadFwdRef($mediusers, "function_id");

        // Filter a posteriori to unable mass preloading of function
        CStoredObject::filterByPerm($mediusers, $permType);

        // Associate cached function
        foreach ($mediusers as $_mediuser) {
            $_mediuser->loadRefFunction();
        }

        return $mediusers;
    }

    /**
     * Get all the patients to export using users' functions
     *
     * @param array $praticien_ids Users ids to use for functions
     * @param int   $start         Start at
     * @param int   $step          Number of patients to retrieve
     * @param array $patient_ids   patient ids
     *
     * @return array
     */
    public static function getPatientToExportFunction($praticien_ids, $start = 0, $step = 100, $patient_ids = [])
    {
        $patient = new CPatient();
        $ds      = $patient->getDS();

        $query = new CRequest();
        $query->addSelect('DISTINCT (P.patient_id) as patient_id');
        $tables = ['patients P', 'users_mediboard M'];
        $where  = ['M.user_id' => $ds->prepareIn($praticien_ids)];

        if ($patient_ids && (count($patient_ids) > 0)) {
            $where['P.patient_id'] = $ds->prepareIn($patient_ids);
        }

        if (CAppUI::isCabinet()) {
            $where['P.function_id'] = '= M.function_id';
        } elseif (CAppUI::isGroup()) {
            $tables[] = 'functions_mediboard F';

            $where['P.group_id']    = '= F.group_id';
            $where['F.function_id'] = '= M.function_id';
        }

        $query->addTable($tables);
        $query->addWhere($where);

        $ids     = $ds->loadList($query->makeSelect());
        $all_ids = CMbArray::pluck($ids, 'patient_id');

        $patient_total = ($all_ids) ? count($all_ids) : 0;

        $patient  = new CPatient();
        $where    = ['patient_id' => $ds->prepareIn($all_ids)];
        $limit    = (!$start && !$step) ? null : "$start,$step";
        $patients = $patient->loadList($where, 'patient_id ASC', $limit);

        return [
            $patients,
            $patient_total,
        ];
    }

    /**
     * Callback to filter objects to export
     *
     * @param CStoredObject $object          Object to check
     * @param string        $date_min        Date min to filter
     * @param string        $date_max        Date max to filter
     * @param array         $praticiens_ids  Praticiens ids to filter
     * @param array         $ignored_classes Classes to ignore for the export
     *
     * @return bool
     */
    public static function exportFilterCallback(
        CStoredObject $object,
        ?string $date_min,
        ?string $date_max,
        array $praticiens_ids,
        array $ignored_classes = [],
        bool $ignore_consult_tag = false
    ) {
        if (in_array($object->_class, $ignored_classes)) {
            return false;
        }

        return $object->isExportable($praticiens_ids, $date_min, $date_max, $ignore_consult_tag);
    }

    /**
     * Callback to do actions on certain classes
     *
     * @param CStoredObject $object               Object to filter
     * @param string        $dir                  Export directory
     * @param bool          $generate_pdfpreviews Generate files previews
     * @param bool          $ignore_files         Ignore files
     * @param bool          $archive_sejour       Make PDF archive for sejours
     * @param bool          $zip_files            Zip the CSejour print
     * @param bool          $archive_mode         Archive the data, make timeline and synthese_med to PDF
     *
     * @return int
     */
    public static function exportCallBack(
        CStoredObject $object,
        string $dir,
        bool $generate_pdfpreviews = true,
        bool $ignore_files = false,
        bool $archive_sejour = false,
        bool $zip_files = false,
        bool $archive_mode = false
    ): int {
        switch (get_class($object)) {
            case CPatient::class:
                if ($archive_mode) {
                    $exp_dir = "$dir/" . CMbString::removeDiacritics(CAppUI::tr($object->_class)) . "/"
                        . preg_replace('/\W+/', '_', CMbString::removeDiacritics($object->_view));

                    if (CModule::getActive("oxCabinet")) {
                        if (!is_dir($exp_dir)) {
                            CMbPath::forceDir($exp_dir);
                        }

                        // Impression timeline
                        $query = [
                            "m"                     => "oxCabinet",
                            "dialog"                => "ajax_print_global",
                            "patient_id"            => $object->_id,
                            "patient_event_type_id" => null,
                            "print"                 => 1,
                            "categories_names"      => [
                                "allergie",
                                "antecedents",
                                "constantes",
                                "consultations",
                                "documents",
                                "evenements",
                                "formulaires",
                                "infogroup",
                                "laboratoire",
                                "naissance",
                                "ordonnances",
                                "pathologie",
                                "rosp",
                                "traitements",
                            ],
                            "archive"               => 1,
                            "archive_path"          => $exp_dir,
                        ];

                        CApp::fetchQuery($query);

                        // Synthèse medicale
                        $query = [
                            [
                                "m"          => "oxCabinet",
                                "a"          => "vw_synthese_medicale",
                                "patient_id" => $object->_id,
                                "dialog"     => 1,
                            ],
                        ];

                        $pdf = CWkhtmlToPDF::makePDF(null, null, $query, "A4", "Portrait", "screen", false);
                        file_put_contents($exp_dir . "/synthese_medicale.pdf", $pdf);
                    } else {
                        // Fiche patient
                        $query = [
                            [
                                "m"          => "dPpatients",
                                "a"          => "print_patient",
                                "patient_id" => $object->_id,
                                "dialog"     => 1,
                            ],
                        ];

                        $pdf = CWkhtmlToPDF::makePDF(null, null, $query, "A4", "Portrait", "screen", false);
                        file_put_contents($exp_dir . "/Fiche patient.pdf", $pdf);
                    }
                }
                break;
            case CCompteRendu::class:
                /** @var CCompteRendu $object */
                if ($generate_pdfpreviews) {
                    try {
                        if ($object->factory === 'CDomPDFConverter') {
                            $object->factory = 'CWkHtmlToPDFConverter';
                        }

                        $object->makePDFpreview(true);
                    } catch (Throwable $e) {
                        CApp::log($e->getMessage(), null, LoggerLevels::LEVEL_WARNING);

                        return 0;
                    }

                    if ($object->_ref_file && $object->_ref_file->_id) {
                        return $object->_ref_file->doc_size;
                    }
                }
                break;

            case CFile::class:
                if ($ignore_files) {
                    break;
                }

                /** @var CFile $object */
                [$object_class, $object_id] = self::getObjectTargetForExport($object);

                /** @var CFile object */
                $object->object_class = $object_class;
                $object->object_id    = $object_id;

                if ($archive_mode) {
                    $target    = $object->loadTargetObject();
                    $_dir      = "$dir/" . CMbString::removeDiacritics(
                            CAppUI::tr($object->object_class)
                        ) . "/" . preg_replace('/\W+/', '_', CMbString::removeDiacritics($target->_view));
                    $file_name = utf8_encode($object->file_name);
                } else {
                    $_dir      = "$dir/$object->object_class/$object->object_id";
                    $file_name = $object->file_real_filename;
                }

                CMbPath::forceDir($_dir);

                $writes = file_put_contents($_dir . "/" . $file_name, @$object->getBinaryContent());
                if ($writes === false) {
                    CApp::log(
                        CAppUI::tr(
                            'CMbObjectExport-Error-Unable to write file to destination',
                            $object->_file_path,
                            $_dir . '/' . $file_name
                        ),
                        null,
                        LoggerLevels::LEVEL_WARNING
                    );
                }

                return $object->doc_size;

            case CSejour::class:
                if ($ignore_files || !$archive_sejour) {
                    break;
                }

                CView::disableSlave();
                /** @var CSejour $object */
                static::archiveSejour($object, $zip_files);
                CView::enforceSlave();
                break;

            default:
                // Do nothing
        }

        return 0;
    }

    /**
     * @param CSejour $sejour   Sejour to create archive for
     * @param bool    $zip_file Zip the generated file
     *
     * @return void
     * @throws Exception
     */
    private static function archiveSejour(CSejour $sejour, bool $zip_file = false)
    {
        try {
            $sejour->makePDFarchive("Dossier complet", true, $zip_file, false);
        } catch (Exception $e) {
            CApp::log($e->getMessage(), null, LoggerLevels::LEVEL_WARNING);
        }


        if (CModule::getActive("dPprescription")) {
            $prescriptions = $sejour->loadRefsPrescriptions();

            foreach ($prescriptions as $_type => $_prescription) {
                if ($_prescription->_id && in_array($_type, ["pre_admission", "sortie"])) {
                    if ($_prescription->countBackRefs("prescription_line_medicament") > 0
                        || $_prescription->countBackRefs("prescription_line_element") > 0
                        || $_prescription->countBackRefs("prescription_line_comment") > 0
                        || $_prescription->countBackRefs("prescription_line_mix") > 0
                        || $_prescription->countBackRefs("administration_dm") > 0
                    ) {
                        $query = [
                            "m"               => "prescription",
                            "raw"             => "print_prescription",
                            "prescription_id" => $_prescription->_id,
                            "dci"             => 0,
                            "in_progress"     => 0,
                            "preview"         => 0,
                        ];

                        $base = $_SERVER["SCRIPT_NAME"] . "?" . http_build_query($query, "", "&");

                        CApp::serverCall("http://127.0.0.1$base");

                        CAppUI::stepAjax(
                            "Archive créée pour la prescription de %s",
                            UI_MSG_OK,
                            CAppUI::tr("CPrescription.type.$_type")
                        );
                    }
                }
            }
        }
    }

    public static function getObjectTargetForExport(CDocumentItem $object): array
    {
        $new_target = null;
        $target     = $object->loadTargetObject();

        if (class_exists(CPrescription::class) && $object->object_class == 'CPrescription') {
            /** @var CPrescription $target */
            $new_target = $target->loadRefObject();
        }

        if (
            (class_exists(CFactureCabinet::class) && $object->object_class == 'CFactureCabinet')
            || (class_exists(CFactureEtablissement::class) && $object->object_class == 'CFactureEtablissement')
        ) {
            /** @var CFacture $target */
            $new_target = $target->loadRefPatient();
        }

        if (class_exists(CDevisCodage::class) && $object->object_class == 'CDevisCodage') {
            /** @var CDevisCodage $target */
            $new_target = $target->loadRefCodable();
        }

        if ($new_target) {
            $object->setObject($new_target);
        }

        return [$object->object_class, $object->object_id];
    }
}
