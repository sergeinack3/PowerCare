<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\Module\CModule;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\FieldSpecs\CFloatSpec;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Ccam\CActe;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Tarif
 */
class CTarif extends CMbObject
{
    // DB Table key
    public $tarif_id;

    // DB References
    public $chir_id;
    public $function_id;
    public $group_id;

    // DB fields
    public $description;
    public $secteur1;
    public $secteur2;
    public $secteur3;
    public $taux_tva;
    public $codes_ccam;
    public $codes_ngap;
    public $codes_lpp;

    // Form fields
    public $_type;
    public $_du_tva;
    public $_somme;
    public $_codes_ngap   = [];
    public $_codes_ccam   = [];
    public $_codes_lpp    = [];
    public $_new_actes    = [];

    // Remote fields
    public $_precode_ready;
    public $_secteur1_uptodate;
    public $_has_mto;

    // Behaviour fields
    public $_add_mto;
    public $_code;
    public $_code_ref;
    public $_quantite;
    public $_type_code;
    public $_update_montants;
    public $_bind_codable;
    public $_codable_class;
    public $_codable_id;

    // Object References
    /** @var CMediusers */
    public $_ref_chir;
    /** @var CFunctions */
    public $_ref_function;
    /** @var CGroups */
    public $_ref_group;

    /**
     * @see parent::getSpec()
     */
    function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = 'tarifs';
        $spec->key   = 'tarif_id';

        //$spec->xor["owner"] = array("chir_id", "function_id", "group_id");
        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    function getProps(): array
    {
        $props                 = parent::getProps();
        $props["chir_id"]      = "ref class|CMediusers back|tarifs";
        $props["function_id"]  = "ref class|CFunctions back|tarifs";
        $props["group_id"]     = "ref class|CGroups back|tarif_group";
        $props["description"]  = "str notNull confidential seekable";
        $props["secteur1"]     = "currency notNull min|0";
        $props["secteur2"]     = "currency";
        $props["secteur3"]     = "currency";
        $props["taux_tva"]     = "float default|0";
        $props["codes_ccam"]   = "str";
        $props["codes_ngap"]   = "str";
        $props['codes_lpp']    = 'str';
        $props["_du_tva"]      = "currency";
        $props["_somme"]       = "currency";
        $props["_type"]        = "";

        $props["_precode_ready"] = "bool";
        $props["_has_mto"]       = "bool";

        return $props;
    }

    /**
     * @see parent::updateFormFields()
     */
    function updateFormFields(): void
    {
        parent::updateFormFields();
        $this->_view = $this->description;
        if ($this->chir_id) {
            $this->_type = "chir";
        } elseif ($this->function_id) {
            $this->_type = "function";
        } else {
            $this->_type = "group";
        }
        $this->_codes_ngap   = explode("|", $this->codes_ngap ?? '');
        $this->_codes_ccam   = explode("|", $this->codes_ccam ?? '');
        $this->_codes_lpp    = explode("|", $this->codes_lpp ?? '');
        CMbArray::removeValue("", $this->_codes_ngap);
        CMbArray::removeValue("", $this->_codes_ccam);
        CMbArray::removeValue("", $this->_codes_lpp);
        $this->_du_tva = round($this->secteur3 * $this->taux_tva / 100, 2);
        $this->_somme  = $this->secteur1 + $this->secteur2 + $this->secteur3 + $this->_du_tva;
    }

    /**
     * @see parent::updatePlainFields()
     */
    function updatePlainFields(): void
    {
        if ($this->_type !== null) {
            if ($this->_type == "chir") {
                $this->function_id = "";
                $this->group_id    = "";
            }
            if ($this->_type == "function") {
                $this->chir_id  = "";
                $this->group_id = "";
            }
            if ($this->_type == "group") {
                $this->function_id = "";
                $this->chir_id     = "";
            }
        }

        $this->updateMontants();
        $this->bindCodable();
    }

    /**
     * Chargement de la consultation associée
     *
     * @return void
     */
    function bindCodable(): void
    {
        if (!$this->_bind_codable || is_null($this->_codable_class) || is_null($this->_codable_id)) {
            return;
        }

        $this->_bind_codable = false;

        /** @var CCodable $codable */
        $codable = new $this->_codable_class();
        $codable->load($this->_codable_id);

        $codable->loadRefsActes();
        $codable->loadRefPraticien();

        // Affectation des valeurs au tarif
        $this->codes_ccam   = $codable->_tokens_ccam;
        $this->codes_ngap   = $codable->_tokens_ngap;
        $this->codes_lpp    = $codable->_tokens_lpp;
        $this->chir_id      = $codable->_ref_praticien->_id;
        $this->function_id  = "";

        if ($codable instanceof CConsultation) {
            /** @var CConsultation $consultation */
            $consultation = $codable;
            $consultation->loadRefPlageConsult();
            $this->secteur1    = $consultation->secteur1;
            $this->secteur2    = $consultation->secteur2;
            $this->secteur3    = $consultation->secteur3;
            $this->description = $consultation->tarif;
        }
    }

    /**
     * @see parent::store()
     */
    function store(): ?string
    {
        if ($this->_add_mto) {
            $this->completeField("codes_ngap");
            $this->codes_ngap .= "|1-MTO-1---0-";
        }

        return parent::store();
    }

    /**
     * Mise à jour du montant du tarif
     *
     * @return integer|null
     **/
    function updateMontants(): ?int
    {
        if (!$this->_update_montants) {
            return $this->secteur1;
        }

        $types_code = [
            "codes_ccam" => "CActeCCAM",
            "codes_ngap" => "CActeNGAP",
        ];

        $this->loadRefsFwd();
        $this->completeField(array_keys($types_code));
        if (!$this->codes_ngap && !$this->codes_ccam) {
            return $this->secteur1;
        }

        $this->secteur1    = 0.00;
        $secteur2          = $this->secteur2;
        $affected_secteur2 = 0;
        $actes             = [];

        foreach ($types_code as $codes => $class_acte) {
            $_codes        = "_" . $codes;
            $this->$_codes = explode("|", $this->$codes);
            CMbArray::removeValue("", $this->$_codes);
            foreach ($this->$_codes as &$_code) {
                /** @var CActe $acte */
                $acte = new $class_acte;
                if ($this->chir_id) {
                    $acte->executant_id = $this->chir_id;
                } elseif ($this->function_id) {
                    /* Recupération de l'id du premier praticien de la fonction dont la spécialité est renseignée */
                    $ds    = CSQLDataSource::get('std');
                    $query = new CRequest();
                    $query->addColumn('user_id');
                    $query->addTable('users_mediboard');
                    $query->addWhereClause('function_id', "= $this->function_id");
                    $query->addWhereClause('spec_cpam_id', 'IS NOT NULL');
                    $result = $ds->loadColumn($query->makeSelect(), 1);
                    if (!empty($result)) {
                        $acte->executant_id = $result[0];
                    }
                }
                $acte->setFullCode($_code);
                $this->secteur1 += $acte->updateMontantBase();

                if ($acte->montant_depassement) {
                    $affected_secteur2 += $acte->montant_depassement;
                }

                $_code   = $acte->makeFullCode();
                $actes[] = $acte;
            }
            $this->$codes = implode("|", $this->$_codes);
        }

        if ($affected_secteur2 > $secteur2) {
            $this->secteur2 = $affected_secteur2;
        } elseif ($secteur2 > $affected_secteur2) {
            /* Affecte le dépassement d'honoraires restant à l'acte au prix le plus élevé */
            $secteur2_to_affect = $secteur2 - $affected_secteur2;
            CMbArray::pluckSort($actes, SORT_DESC, 'montant_base');
            $this->_codes_ccam = [];
            $this->_codes_ngap = [];

            foreach ($actes as $acte) {
                if ($secteur2_to_affect) {
                    if ($acte->montant_depassement) {
                        $acte->montant_depassement += $secteur2_to_affect;
                    } else {
                        $acte->montant_depassement = $secteur2_to_affect;
                    }
                    $secteur2_to_affect = 0;
                }

                $field            = '_' . array_search($acte->_class, $types_code);
                $this->{$field}[] = $acte->makeFullCode();
            }

            $this->codes_ccam = implode("|", $this->_codes_ccam);
            $this->codes_ngap = implode("|", $this->_codes_ngap);
        }

        return $this->secteur1;
    }

    /**
     * Chargement du secteur 1 du tarif
     *
     * @return $this->_secteur1_uptodate
     **/
    function getSecteur1Uptodate(): bool
    {
        if ((!$this->codes_ngap && !$this->codes_ccam)) {
            return $this->_secteur1_uptodate = "1";
        }

        // Backup ...
        $secteur1     = $this->secteur1;
        $codes_ccam   = $this->_codes_ccam;
        $codes_ngap   = $this->_codes_ngap;

        // Compute...
        $this->_update_montants = true;
        $new_secteur1           = $this->updateMontants();

        // ... and restore
        $this->secteur1      = $secteur1;
        $this->_codes_ccam   = $codes_ccam;
        $this->_codes_ngap   = $codes_ngap;

        return $this->_secteur1_uptodate = CFloatSpec::equals(
            $secteur1,
            $new_secteur1,
            $this->_specs["secteur1"]
        ) ? "1" : "0";
    }

    /**
     * Precodage des tarifs
     *
     * @return string
     **/
    function getPrecodeReady(): string
    {
        $this->_has_mto   = '0';
        $this->_new_actes = [];

        if (
            count($this->_codes_ccam) + count($this->_codes_ngap) + count($this->_codes_lpp) == 0
        ) {
            return $this->_precode_ready = '0';
        }

        $tab = [
            "_codes_ccam" => "CActeCCAM",
            "_codes_ngap" => "CActeNGAP",
            '_codes_lpp'  => 'CActeLPP',
        ];

        foreach ($tab as $codes => $class_acte) {
            foreach ($this->$codes as $code) {
                /** @var CActe $acte */
                $acte = new $class_acte;
                $acte->setFullCode($code);

                $this->_new_actes[$code] = $acte;
                if (!$acte->getPrecodeReady()) {
                    return $this->_precode_ready = '0';
                }

                if ($class_acte == "CActeNGAP" && in_array($acte->code, ["MTO", "MPJ"])) {
                    $this->_has_mto = '1';
                }
            }
        }

        return $this->_precode_ready = '1';
    }

    /**
     * @see parent::loadRefsFwd()
     */
    function loadRefsFwd(): void
    {
        $this->_ref_chir     = $this->loadFwdRef("chir_id");
        $this->_ref_function = $this->loadFwdRef("function_id");
        $this->loadRefGroup();
        $this->getPrecodeReady();
    }

    /**
     * @see parent::getPerm()
     */
    function getPerm($permType): bool
    {
        if (!$this->_ref_chir || !$this->_ref_function) {
            $this->loadRefsFwd();
        }

        return
            $this->_ref_chir->getPerm($permType) ||
            $this->_ref_function->getPerm($permType);
    }

    /**
     * Charge l'établissement associé au tarif
     *
     * @return CGroups
     */
    function loadRefGroup(): CGroups
    {
        return $this->_ref_group = $this->loadFwdRef("group_id", true);
    }

    /**
     * Charge l'ensemble des tarifs d'un utilisateur
     *
     * @param CMediusers $user    Praticien concerné
     * @param string     $keyword Keyword to search
     * @param string     $type    The type of code (ccam, ngap or lpp)
     *
     * @return CTarif[]
     */
    static function loadTarifsUser($user, $keyword = null, $type = null): array
    {
        $tarif  = new self;
        $tarifs = [];
        $order  = "description";

        $where            = [];
        $where["chir_id"] = "= '$user->user_id'";

        if ($keyword) {
            $where["description"] = "LIKE '%$keyword%'";
        }

        $tarifs["user"] = $tarif->loadList($where, $order);
        foreach ($tarifs["user"] as $_tarif) {
            /* @var CTarif $_tarif */
            $_tarif->getPrecodeReady();
        }

        $where                = [];
        $where["function_id"] = "= '$user->function_id'";

        if ($keyword) {
            $where["description"] = "LIKE '%$keyword%'";
        }

        switch ($type) {
            case 'ccam':
                $where['codes_ccam'] = ' IS NOT NULL';
                break;
            case 'ngap':
                $where['codes_ngap'] = ' IS NOT NULL';
                break;
            case 'lpp':
                $where['codes_lpp'] = ' IS NOT NULL';
                break;
            default:
        }

        $tarifs["func"] = $tarif->loadList($where, $order);
        foreach ($tarifs["func"] as $_tarif) {
            $_tarif->getPrecodeReady();
        }
        if (CAppUI::gconf("dPcabinet Tarifs show_tarifs_etab")) {
            $where             = [];
            $where["group_id"] = "= '" . CGroups::loadCurrent()->_id . "'";
            $tarifs["group"]   = $tarif->loadList($where, $order);
            foreach ($tarifs["group"] as $_tarif) {
                $_tarif->getPrecodeReady();
            }
        }

        return $tarifs;
    }

    /**
     *
     *
     * @param CMbObject $object The CMediusers, CFunction or CGroups
     *
     * @return bool|CCSVFile
     */
    public static function exportTarifsFor($object)
    {
        if (!in_array($object->_class, ['CMediusers', 'CFunctions', 'CGroups'])) {
            return false;
        }

        $tarif = new CTarif();
        switch ($object->_class) {
            case 'CMediusers':
                $tarif->chir_id = $object->_id;
                break;
            case 'CFunctions':
                $tarif->function_id = $object->_id;
                break;
            default:
                $tarif->group_id = $object->_id;
        }

        /** @var CTarif[] $tarifs */
        $tarifs = $tarif->loadMatchingList(null, null, 'tarif_id');

        $file = new CCSVFile();

        $file->writeLine(
            [
                CAppUI::tr('CTarif-description'),
                CAppUI::tr('CTarif-secteur1'),
                CAppUI::tr('CTarif-secteur2'),
                CAppUI::tr('CTarif-secteur3'),
                CAppUI::tr('CTarif-taux_tva'),
                CAppUI::tr('CTarif-codes_ccam'),
                CAppUI::tr('CTarif-codes_ngap'),
                CAppUI::tr('CTarif-codes_lpp'),
            ]
        );

        foreach ($tarifs as $tarif) {
            $file->writeLine(
                [
                    $tarif->description,
                    $tarif->secteur1,
                    $tarif->secteur2,
                    $tarif->secteur3,
                    $tarif->taux_tva,
                    $tarif->codes_ccam,
                    $tarif->codes_ngap,
                    $tarif->codes_lpp,
                ]
            );
        }

        return $file;
    }

    /**
     * @param CMbObject $object The CMediusers, CFunctions or CGroups
     * @param CCSVFile  $file   The CSV file
     *
     * @return bool|array
     */
    public static function importTarifsFor($object, $file)
    {
        if (!in_array($object->_class, ['CMediusers', 'CFunctions', 'CGroups'])) {
            return false;
        }

        $status = [
            'success' => 0,
            'errors'  => 0,
            'founds'  => 0,
        ];

        switch ($object->_class) {
            case 'CMediusers':
                $field = 'chir_id';
                break;
            case 'CFunctions':
                $field = 'function_id';
                break;
            default:
                $field = 'group_id';
        }

        $file->setColumnNames(
            [
                'description',
                'secteur1',
                'secteur2',
                'secteur3',
                'taux_tva',
                'codes_ccam',
                'codes_ngap',
                'codes_lpp',
            ]
        );

        $file->jumpLine(1);

        while ($line = $file->readLine(true)) {
            $tarif         = new CTarif();
            $tarif->$field = $object->_id;

            foreach ($line as $key => $value) {
                $tarif->$key = $value;
            }

            $tarif->loadMatchingObjectEsc();

            if ($tarif->_id) {
                $status['founds']++;
            } elseif ($msg = $tarif->store()) {
                $status['errors']++;
            } else {
                $status['success']++;
            }
        }

        return $status;
    }
}
