<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CSQLDataSource;
use Ox\Core\Module\CModule;
use Ox\Import\Framework\ImportableInterface;
use Ox\Import\Framework\Matcher\MatcherVisitorInterface;
use Ox\Import\Framework\Persister\PersisterVisitorInterface;
use Ox\Mediboard\Ccam\CActe;
use Ox\Mediboard\Ccam\CCodable;
use Ox\Mediboard\Ccam\CCodeNGAP;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Facturation\CFacture;
use Ox\Mediboard\Facturation\CFactureItem;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\OxPyxvital\CPyxvitalFSE;
use Ox\Mediboard\OxPyxvital\CPyxvitalFSEAct;
use Ox\Mediboard\Patients\IGroupRelated;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Prescription\CPrescription;
use Ox\Mediboard\SalleOp\CActeCCAM;

/**
 * Actes NGAP concrets pouvant être associé à n'importe quel codable
 */
class CActeNGAP extends CActe implements ImportableInterface, IGroupRelated
{
    // DB key
    /** @var integer */
    public $acte_ngap_id;

    // DB fields
    /** @var integer */
    public $quantite;
    /** @var string */
    public $code;
    /** @var float */
    public $coefficient;
    /** @var float */
    public $taux_abattement;
    /** @var string */
    public $demi;
    /** @var string */
    public $complement;
    /** @var string */
    public $lettre_cle;
    /** @var string */
    public $lieu;
    /** @var string */
    public $exoneration;
    /** @var string */
    public $ald;
    /** @var integer */
    public $numero_dent;
    /** @var string */
    public $comment;
    /** @var integer */
    public $major_pct;
    /** @var float */
    public $major_coef;
    /** @var integer */
    public $minor_pct;
    /** @var float */
    public $minor_coef;
    /** @var integer */
    public $numero_forfait_technique;
    /** @var integer */
    public $numero_agrement;
    /** @var string */
    public $rapport_exoneration;
    /** @var integer */
    public $prescripteur_id;
    /** @var string */
    public $qualif_depense;
    /** @var string */
    public $accord_prealable;
    /** @var string */
    public $date_demande_accord;
    /** @var string */
    public $reponse_accord;
    /** @var integer */
    public $prescription_id;
    /** @var integer */
    public $other_executant_id;
    /** @var string */
    public $motif;
    /** @var string */
    public $motif_unique_cim;
    /** @var string  */
    public $comment_acte;

    /** @var CCodeNGAP */
    public $_code;

    // Distant fields
    /** @var string */
    public $_libelle;

    // Tarif final
    /** @var string */
    public $_tarif;

    /** @var float The minimum value authorized for the coefficient */
    public $_min_coef;

    /** @var float The maximum value authorized for the coefficient */
    public $_max_coef;

    /** @var array The list of the forbidden complements for this act */
    public $_forbidden_complements;

    /** @var bool If true, a DEP might be needed */
    public $_dep;

    /** @var CMediusers */
    public $_ref_prescripteur;

    /** @var CPrescription */
    public $_ref_prescription;

    /** @var array A list of code that are executed at the patient's home */
    public static $codes_domicile = [
        'V',
        'VA',
        'VL',
        'VNP',
        'VRS',
        'VS',
        'VU',
        'MD',
        'MDD',
        'MDE',
        'MDI',
        'MDN',
        'MED',
        'MEI',
        'MEN',
        'MM',
        'VG',
        'VGS',
    ];

    /**
     * @see parent::getSpec()
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = 'acte_ngap';
        $spec->key   = 'acte_ngap_id';

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props["object_id"]                .= " back|actes_ngap";
        $props["code"]                     = "str notNull maxLength|5";
        $props["quantite"]                 = "num notNull min|1 maxLength|2";
        $props["coefficient"]              = "float notNull min|0.1";
        $props['taux_abattement']          = 'float';
        $props["demi"]                     = "enum list|0|1 default|0";
        $props["complement"]               = "enum list|N|F|U";
        $props["lettre_cle"]               = "bool default|0";
        $props["lieu"]                     = "enum list|C|D default|C";
        $props["exoneration"]              = "enum list|N|3|7 default|N";
        $props["ald"]                      = "bool default|0";
        $props["numero_dent"]              = "num min|11 max|85";
        $props["comment"]                  = "str";
        $props["major_pct"]                = "num";
        $props["major_coef"]               = "float";
        $props["minor_pct"]                = "num";
        $props["minor_coef"]               = "float";
        $props["numero_forfait_technique"] = "num min|1 max|99999";
        $props["numero_agrement"]          = "num min|1 max|99999999999999";
        $props["rapport_exoneration"]      = "enum list|4|7|C|R";
        $props['prescripteur_id']          = 'ref class|CMediusers back|actes_ngap_prescrits';
        $props['qualif_depense']           = 'enum list|d|e|f|g|n|a|b|l';
        $props['accord_prealable']         = 'bool default|0';
        $props['date_demande_accord']      = 'date';
        $props['reponse_accord']           = 'enum list|no_answer|accepted|emergency|refused';
        $props["prescription_id"]          = "ref class|CPrescription back|actes_ngap";
        $props["executant_id"]             .= " back|actes_ngap_executes";
        $props["other_executant_id"]       = "ref class|CMedecin back|actes_ngap";
        $props["motif"]                    = "text helped";
        $props["motif_unique_cim"]         = "code cim10 show|0";
        $props['_tarif']                   = 'currency';
        $props["comment_acte"]             = 'text helped';

        return $props;
    }

    /**
     * @see parent::updateFormFields()
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        // Vue codée
        $this->_shortview = $this->quantite > 1 ? "{$this->quantite}x" : "";
        $this->_shortview .= $this->code;
        if ($this->coefficient != 1) {
            $this->_shortview .= $this->coefficient;
        }
        if ($this->demi) {
            $this->_shortview .= "/2";
        }

        $this->_view = "Acte NGAP $this->_shortview";
        if ($this->object_class && $this->object_id) {
            $this->_view .= " de $this->object_class-$this->object_id";
        }

        if ($this->_id) {
            $this->checkEntentePrealable();
            $this->getLibelle();
        }

        $this->_tarif = round((float)$this->montant_base + (float)$this->montant_depassement, 2);
    }

    /**
     * @see parent::updatePlainFields()
     */
    public function updatePlainFields(): void
    {
        parent::updatePlainFields();

        if ($this->code) {
            $this->code = strtoupper($this->code);
        }
    }

    /**
     * Prepare un acte NGAP vierge en vue d'être associé à un codable
     *
     * @param CCodable    $codable   Codable ciblé
     * @param ?CMediusers $executant Un exécutant optionel
     *
     * @return CActeNGAP
     */
    public static function createEmptyFor(CCodable $codable, ?CMediusers $executant = null): self
    {
        $acte = new self();
        $acte->setObject($codable);
        $acte->quantite    = 1;
        $acte->coefficient = 1;
        $acte->gratuit     = '0';
        $acte->loadExecution();
        $acte->guessExecutant();

        if ($executant) {
            $acte->executant_id   = $executant->_id;
            $acte->_ref_executant = $executant;
        }

        if (
            CAppUI::gconf('dPccam ngap prefill_prescriptor')
            && (($acte->object_class == 'CConsultation' && $acte->_ref_object->sejour_id)
                || ($acte->object_class == 'CSejour' && $acte->_ref_object->_id))
        ) {
            /** @var CSejour $sejour */
            $sejour                  = $acte->object_class == 'CConsultation' ? $acte->_ref_object->loadRefSejour(
            ) : $acte->_ref_object;
            $acte->prescripteur_id   = $sejour->praticien_id;
            $acte->_ref_prescripteur = $sejour->loadRefPraticien();
        }

        if (
            ($codable instanceof CConsultation && $codable->concerne_ALD === '1')
            || ($codable instanceof CSejour && $codable->ald === '1')
        ) {
            $acte->ald = '1';
        }

        return $acte;
    }

    /**
     * @see parent::makeFullCode()
     */
    public function makeFullCode(): string
    {
        return $this->_full_code =
            $this->quantite .
            "-" . $this->code .
            "-" . $this->coefficient .
            "-" . $this->montant_base .
            "-" . str_replace("-", "*", $this->montant_depassement ?? "") .
            "-" . $this->demi .
            "-" . $this->complement .
            "-" . $this->gratuit .
            "-" . $this->qualif_depense .
            '-' . $this->lieu .
            "-" . $this->exoneration .
            "-" . $this->comment_acte;
    }

    /**
     * @inheritdoc
     */
    public function setFullCode(string $code): void
    {
        $details = explode("-", $code);

        $this->quantite    = $details[0];
        $this->code        = $details[1];
        $this->coefficient = $details[2];

        if (count($details) >= 4) {
            $this->montant_base = $details[3];
        }

        if (count($details) >= 5) {
            $this->montant_depassement = str_replace("*", "-", $details[4]);
        }

        if (count($details) >= 6) {
            $this->demi = $details[5];
        }

        if (count($details) >= 7) {
            $this->complement = $details[6];
        }

        if (count($details) >= 8) {
            $this->gratuit = $details[7];
        }

        if (count($details) >= 9) {
            $this->qualif_depense = $details[8];
        }

        if (count($details) >= 10) {
            $this->lieu = $details[9];
        }

        if (count($details) >= 11) {
            $this->exoneration = $details[10];
        }

        if (count($details) >= 12) {
            $this->comment_acte = $details[11];
        }

        $this->getLibelle();
        if (!$this->lettre_cle) {
            $this->lettre_cle = 0;
        }

        $this->updateFormFields();
    }

    /**
     * @see parent::getPrecodeReady()
     */
    public function getPrecodeReady(): bool
    {
        return $this->quantite && $this->code && $this->coefficient;
    }

    /**
     * @see parent::check()
     */
    public function check(): ?string
    {
        if ($msg = $this->checkCoded()) {
            return $msg;
        }

        if ($this->code) {
            $this->loadCode();

            /* Check if the act exists */
            if ($this->_code->_unknown) {
                return 'CActeNGAP-unknown';
            }

            /* Check if the act is deprecated */
            if ($this->_code->_deprecated) {
                CAppUI::setMsg('CActeNGAP-deprecated', UI_MSG_WARNING, $this->code);
            }
        }

        if ((in_array($this->complement, ['F', 'N']) || $this->code === 'MM') && $this->checkExclusiveModifiers()) {
            return $this->code === 'MM' ? CAppUI::tr('CActeNGAP-error-MM_exclusive_modifiers') : CAppUI::tr(
                'CActeCCAM-error-NGAP_exclusive_modifiers'
            );
        }

        return parent::check();
    }

    public function checkExclusiveModifiers(): bool
    {
        if ($this->isExecutantKine()) {
            return $this->checkExclusiveModifiersForKine();
        } else {
            $ccam_acts = $this->getLinkedActesCCAM(true, true);
            foreach ($ccam_acts as $ccam_act) {
                if (count(array_intersect($ccam_act->_modificateurs, ['F', 'U', 'P', 'S']))) {
                    return true;
                }
            }

            $ngap_acts = $this->getLinkedActesNGAP(true, true);
            foreach ($ngap_acts as $ngap_act) {
                if ($ngap_act->code === 'MM' || in_array($ngap_act->complement, ['N', 'U', 'F'])) {
                    return true;
                }
            }
        }

        return false;
    }

    public function checkExclusiveModifiersForKine(): bool
    {
        $is_afternoon = CMbDT::time($this->execution) > "12:00:00";

        $ccam_acts = $this->getLinkedActesCCAM(true, true);
        foreach ($ccam_acts as $ccam_act) {
            if (
                count(array_intersect($ccam_act->_modificateurs, ['F', 'U', 'P', 'S']))
                && (
                    ($is_afternoon && CMbDT::time($ccam_act->execution) > "12:00:00")
                    || (!$is_afternoon && CMbDT::time($ccam_act->execution) <= "12:00:00")
                )
            ) {
                return true;
            }
        }

        $ngap_acts = $this->getLinkedActesNGAP(true, true);
        foreach ($ngap_acts as $ngap_act) {
            if (
                ($ngap_act->code === 'MM' || in_array($ngap_act->complement, ['N', 'U', 'F']))
                && (
                    ($is_afternoon && CMbDT::time($ngap_act->execution) > "12:00:00")
                    || (!$is_afternoon && CMbDT::time($ngap_act->execution) <= "12:00:00")
                )
            ) {
                return true;
            }
        }

        return false;
    }

    public function isExecutantKine(): bool
    {
        $this->loadRefExecutant();

        return 26 == $this->_ref_executant->spec_cpam_id;
    }

    /**
     * @see parent::store()
     */
    public function store(): ?string
    {
        // Chargement du oldObject

        if ($this->code == 'MTO') {
            return null;
        }

        $oldObject = new CActeNGAP();
        $oldObject->load($this->_id);

        /* Synchronization du champ gratuit et du motif de dépassement */
        if ($this->fieldModified('gratuit') || ($this->gratuit && !$this->_id)) {
            if ($this->gratuit) {
                $this->qualif_depense = 'g';
            } else {
                $this->qualif_depense = '';
            }
        } elseif ($this->fieldModified('qualif_depense') || ($this->qualif_depense && !$this->_id)) {
            if ($this->qualif_depense == 'g') {
                $this->gratuit = '1';
            } elseif ($this->_old && $this->_old->qualif_depense == 'g') {
                $this->gratuit = '0';
            }
        }

        $this->completeField('object_class');
        if ((!$this->_id || $this->fieldModified('execution')) && $this->object_class !== 'CModelCodage') {
            $this->guessComplement();
        }

        if (!$this->_id || $this->fieldModified('complement')) {
            $this->updateMontantBase();
        }

        if ($msg = parent::store()) {
            return $msg;
        }

        /* We create a link between the act and the fse in creation for the linked consultation */
        if (CModule::getActive('oxPyxvital') && $this->object_class == 'CConsultation' && !$oldObject->_id) {
            $this->loadTargetObject();
            $fses = CPyxvitalFSE::loadForConsult($this->_ref_object);

            foreach ($fses as $_fse) {
                if ($_fse->state == 'creating') {
                    $_link            = new CPyxvitalFSEAct();
                    $_link->fse_id    = $_fse->_id;
                    $_link->act_class = $this->_class;
                    $_link->act_id    = $this->_id;

                    if ($msg = $_link->store()) {
                        return $msg;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Ajout du compléments Férié pour les cas suivants :
     *   - Acte effectué un dimanche ou un jour férié entre 08h et 20h,
     *   - Acte effectué le samedi après midi pour les kinés
     *   - Acte effectué le samedi après midi pour les généralistes en fonction de la configuration
     *      holiday_charge_saturday_for_generalists
     *
     * @throws \Exception
     */
    private function guessComplement(): void
    {
        $this->completeField('code');
        $this->completeField('coefficient');
        $this->loadRefExecutant();
        $this->getForbiddenComplements();

        $date = CMbDT::date($this->execution);
        $time = CMbDT::time($this->execution);
        if (
            (((CMbDT::isHoliday($date) || CMbDT::format($date, '%w') === '0')
                    && $time < '20:00:00' && $time >= '08:00:00')
                || (
                    ($this->_ref_executant->spec_cpam_id == 26 || (
                            $this->_ref_executant->spec_cpam_id == 1 && CAppUI::gconf(
                                'dPccam codage holiday_charge_saturday_for_generalists'
                            ))
                    ) && CMbDT::format($date, '%w') === '6' && $time >= '12:00:00' && $time < '20:00:00'))
            && !in_array('F', $this->_forbidden_complements) && !$this->checkExclusiveModifiers()
            && ($this->coefficient >= 0.01 || is_null($this->coefficient))
            && CAppUI::pref('enabled_majoration_F')
        ) {
            $this->complement = 'F';
        } elseif ($this->complement == 'F' && CAppUI::pref('enabled_majoration_F')) {
            $this->complement = '';
        }

        if (
            (($time >= '20:00:00' && $time <= '23:59:59') || ($time >= '06:00:00' && $time < '08:00:00'))
            && !in_array('N', $this->_forbidden_complements) && !$this->checkExclusiveModifiers()
            && ($this->coefficient >= 1 || is_null($this->coefficient))
            && CAppUI::pref('enabled_majoration_F')
        ) {
            $this->complement = 'N';
        } elseif ($this->complement == 'N' && CAppUI::pref('enabled_majoration_F')) {
            $this->complement = '';
        }
    }

    /**
     * @see parent::delete()
     */
    public function delete(): ?string
    {
        /* We delete the links between the act and the fse that are in creation or cancelled */
        if (CModule::getActive('oxPyxvital') && $this->object_class == 'CConsultation') {
            /** @var CPyxvitalFSEAct[] $fse_links */
            $fse_links = $this->loadBackRefs('fse_links');
            if ($fse_links) {
                foreach ($fse_links as $_link) {
                    $_link->loadRefFSE();
                    if ($_link->_ref_fse->state == 'creating' || $_link->_ref_fse->state == 'cancelled') {
                        if ($msg = $_link->delete()) {
                            return $msg;
                        }
                    }
                }
            }
        }

        return parent::delete();
    }

    /**
     * @see parent::canDeleteEx()
     */
    public function canDeleteEx(): ?string
    {
        if ($msg = $this->checkCoded()) {
            return $msg;
        }

        $msg = parent::canDeleteEx();

        if ($msg) {
            return $msg;
        }

        if (CModule::getActive('oxPyxvital') && $this->object_class == 'CConsultation') {
            /** @var CPyxvitalFSEAct[] $fse_links */
            $fse_links = $this->loadBackRefs('fse_links');
            if ($fse_links) {
                foreach ($fse_links as $_link) {
                    $_link->loadRefFSE();
                    if ($_link->_ref_fse->state != 'creating' && $_link->_ref_fse->state != 'cancelled') {
                        $msg = CAppUI::tr('CMbObject-msg-nodelete-backrefs') . ': ' . count($fse_links)
                            . ' ' . CAppUI::tr("CActe-back-fse_links");
                    }
                }
            }
        }

        return $msg;
    }

    /**
     * Set the ist of forbidden complements
     *
     * @return void
     */
    public function getForbiddenComplements(): void
    {
        $code = $this->loadCode();
        $code->getTarifFor($this->_ref_executant, CMbDT::date($this->execution));

        $this->_forbidden_complements = [];
        if ($code->_tarif) {
            if (!$code->_tarif->complement_ferie) {
                $this->_forbidden_complements[] = 'F';
            }
            if (!$code->_tarif->complement_nuit) {
                $this->_forbidden_complements[] = 'N';
            }
            if (!$code->_tarif->complement_urgence) {
                $this->_forbidden_complements[] = 'U';
            }
        }
    }

    /**
     * Calcule le montant de base de l'acte
     *
     * @return float
     */
    public function updateMontantBase(): float
    {
        $this->loadRefExecutant();
        $this->_ref_executant->loadRefFunction();

        if ($this->gratuit) {
            return $this->montant_base = 0.0;
        } else {
            $code = $this->loadCode();
            $code->getTarifFor($this->_ref_executant, CMbDT::date($this->execution));

            if ($code->_tarif) {
                $this->_forbidden_complements = [];
                if (!$code->_tarif->complement_ferie) {
                    $this->_forbidden_complements[] = 'F';
                }
                if (!$code->_tarif->complement_nuit) {
                    $this->_forbidden_complements[] = 'N';
                }
                if (!$code->_tarif->complement_urgence) {
                    $this->_forbidden_complements[] = 'U';
                }

                $this->_min_coef = $code->_tarif->coef_min;
                $this->_max_coef = $code->_tarif->coef_max;

                if ($this->coefficient == '' || $this->coefficient == 0) {
                    $this->coefficient = 1;
                }

                if ($this->_min_coef && $this->coefficient < $this->_min_coef) {
                    $this->coefficient = $this->_min_coef;
                }
                //        elseif ($this->_max_coef && $this->coefficient > $this->_max_coef) {
                //          $this->coefficient = $this->_max_coef;
                //        }

                if ($code->_tarif->entente_prealable) {
                    $this->_dep = true;
                }

                $this->montant_base = $code->_tarif->tarif;
                $this->montant_base *= $this->coefficient;
                $this->montant_base *= $this->quantite;

                if ($this->demi) {
                    $this->montant_base /= 2;
                }

                if ($this->complement == "F") {
                    $this->montant_base += $code->_tarif->maj_ferie;
                }

                if ($this->complement == "N") {
                    $this->montant_base += $code->_tarif->maj_nuit;
                }
            } else {
                $this->montant_base = 0.0;
            }
        }

        $this->montant_base = round($this->montant_base, 2, PHP_ROUND_HALF_UP);

        /* Gestion du taux d'abattement des indemnités kilométriques pour les infirmiers */
        if ($this->isIKInfirmier()) {
            $this->calculTauxAbattementIndemnitesKilometriques();
        }

        return $this->montant_base;
    }

    public function checkEntentePrealable(): void
    {
        $this->loadRefExecutant();
        $code = $this->loadCode();
        $code->getTarifFor($this->_ref_executant, CMbDT::date($this->execution));

        if ($code->_tarif && $code->_tarif->entente_prealable) {
            $this->_dep = $code->_tarif->entente_prealable;
        } else {
            $this->_dep = '0';
        }
    }

    /**
     * Produit le libellé NGAP complet de l'acte
     *
     * @return string
     */
    public function getLibelle(): ?string
    {
        $this->loadCode();

        $this->_libelle = CAppUI::tr('CActeNGAP-Unknown or deleted act');

        if (!$this->_code->_unknown) {
            $this->_libelle   = $this->_code->libelle;
            $this->lettre_cle = $this->_code->lettre_cle ? '1' : '0';
        }

        return $this->_libelle;
    }

    /**
     * Set the field Lieu depending on the act's code, and return it's value
     *
     * @return string
     */
    public function getLieu(): string
    {
        $this->lieu = 'C';

        if (in_array($this->code, self::$codes_domicile)) {
            $this->lieu = 'D';
        }

        return $this->lieu;
    }

    /**
     * Load the Code from the cache or the database
     *
     * @return CCodeNGAP
     */
    public function loadCode(): ?CCodeNGAP
    {
        $this->completeField('code');

        if (!$this->_code) {
            $this->_code = CCodeNGAP::get($this->code);
        }

        return $this->_code;
    }

    /**
     * Création d'un item de facture pour un code ngap
     *
     * @param CFacture $facture la facture
     *
     * @return string|null
     */
    public function creationItemsFacture(CFacture $facture): ?string
    {
        $ligne                      = new CFactureItem();
        $ligne->libelle             = $this->comment_acte ? $this->_libelle . ' - ' . $this->comment_acte : $this->_libelle;
        $ligne->code                = $this->code;
        $ligne->type                = $this->_class;
        $ligne->object_id           = $facture->_id;
        $ligne->object_class        = $facture->_class;
        $ligne->date                = CMbDT::date($this->execution);
        $ligne->montant_base        = $this->montant_base;
        $ligne->montant_depassement = $this->montant_depassement;
        $ligne->quantite            = $this->quantite;
        $ligne->coeff               = $this->coefficient;

        return $ligne->store();
    }

    /**
     * Load the prescriptor
     *
     * @return CMediusers
     */
    public function loadRefPrescripteur(): ?CMediusers
    {
        /** @var CMediusers $prescripteur */
        $prescripteur = $this->loadFwdRef('prescripteur_id', true);
        $prescripteur->loadRefFunction();

        return $this->_ref_prescripteur = $prescripteur;
    }

    /**
     * Load the related prescription
     *
     * @return CPrescription
     */
    public function loadRefPrescription(): ?CPrescription
    {
        return $this->_ref_prescription = $this->loadFwdRef("prescription_id", true);
    }

    /**
     * Vérifie si l'acte est une Indemnité kilométrique et que l'exécutant est un infirmier
     *
     * @return bool
     */
    public function isIKInfirmier(): bool
    {
        $this->loadRefExecutant();
        if (in_array($this->code, ['IK', 'IKM', 'IKS']) && $this->_ref_executant->spec_cpam_id == 24) {
            return true;
        }

        return false;
    }

    /**
     * Calcule le taux d'abattement, ainsi que montant base de l'acte avec ce taux.
     *
     * @return void
     * @throws \Exception
     *
     */
    public function calculTauxAbattementIndemnitesKilometriques(): void
    {
        if (is_null($this->taux_abattement) || $this->taux_abattement === '') {
            $where = [
                'code'         => CSQLDataSource::prepareIn(['IK', 'IKS', 'IKM']),
                'executant_id' => " = $this->executant_id",
                "execution <= '$this->execution' AND execution >= '" . CMbDT::date($this->execution) . " 00:00:00'",
            ];

            if ($this->_id) {
                $where['acte_ngap_id'] = " != $this->acte_ngap_id";
            }

            $actes_ik = $this->loadList($where, 'execution ASC');

            $total_ik = 0;
            foreach ($actes_ik as $ik) {
                $total_ik += $ik->quantite;
            }

            /* Le calcul du taux d'abattement ne prend en compte que le 1er kilomètres de l'acte en cours */
            $total_ik++;

            /* Les différents taux d'abattements sont fournis dans la table 24 du CdC (ou dans l'Avenant 24) */
            if ($total_ik <= 299) {
                $this->taux_abattement = 1.00;
            } elseif ($total_ik <= 399) {
                $this->taux_abattement = 0.50;
            } else {
                $this->taux_abattement = 0.00;
            }
        }

        if ($this->taux_abattement === 0.50) {
            $this->montant_base *= 0.5;
        } elseif (in_array($this->taux_abattement, [0.00, '0'])) {
            $this->montant_base = 0;
            $this->gratuit      = '1';
        }
    }

    /**
     * @param bool $same_executant
     * @param bool $same_day
     *
     * @return CActeCCAM[]
     * @throws \Exception
     */
    public function getLinkedActesCCAM(bool $same_executant = true, bool $same_day = false): array
    {
        $act = new CActeCCAM();

        $where = ['object_class' => " = '{$this->object_class}'", 'object_id' => " = '{$this->object_id}'"];

        if ($same_executant) {
            $where['executant_id'] = " = '{$this->executant_id}'";
        }

        if ($same_day) {
            $begin              = CMbDT::format($this->execution, '%Y-%m-%d 00:00:00');
            $end                = CMbDT::format($this->execution, '%Y-%m-%d 23:59:59');
            $where['execution'] = " BETWEEN '$begin' AND '$end'";
        }

        return $act->loadList($where);
    }

    /**
     * @param bool $same_executant
     * @param bool $same_day
     *
     * @return self[]
     * @throws \Exception
     */
    public function getLinkedActesNGAP(bool $same_executant = true, bool $same_day = false): array
    {
        $act = new self();

        $where = [
            'acte_ngap_id' => "<> '$this->_id'",
            'object_class' => " = '{$this->object_class}'",
            'object_id'    => " = '{$this->object_id}'",
        ];

        if ($same_executant) {
            $where['executant_id'] = " = '{$this->executant_id}'";
        }

        if ($same_day) {
            $begin              = CMbDT::format($this->execution, '%Y-%m-%d 00:00:00');
            $end                = CMbDT::format($this->execution, '%Y-%m-%d 23:59:59');
            $where['execution'] = " BETWEEN '$begin' AND '$end'";
        }

        return $act->loadList($where);
    }

    public function matchForImport(MatcherVisitorInterface $matcher): ImportableInterface
    {
        return $matcher->matchActeNGAP($this);
    }

    public function persistForImport(PersisterVisitorInterface $persister): ImportableInterface
    {
        return $persister->persistObject($this);
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
