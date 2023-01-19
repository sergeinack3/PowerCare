<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\Module\CModule;
use Ox\Core\Mutex\CMbMutex;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\SalleOp\CActeCCAM;

/**
 * Link the association rule used by the practitioner to the CCodable
 */
class CCodageCCAM extends CMbObject
{
    /**
     * @var integer Primary key
     */
    public $codage_ccam_id;
    /** @var string */
    public $association_rule;
    /** @var string */
    public $association_mode;
    /** @var string */
    public $codable_class;
    /** @var int */
    public $codable_id;
    /** @var int */
    public $praticien_id;
    /** @var string */
    public $locked;
    /** @var string */
    public $activite_anesth;
    /** @var string */
    public $date;
    /** @var string */
    public $date_unlock;

    /**@var CActeCCAM[] */
    protected $_ordered_acts;

    /** @var array */
    protected $_complementary_codes;
    /** @var array */
    protected $_check_failed_acts = [];

    /** @var boolean[] */
    public $_possible_rules;

    /** @var array */
    protected $_check_rules;
    /** @var boolean */
    protected $_check_asso  = true;
    /** @var boolean */
    protected $_apply_rules = true;

    /** @var float The total price */
    public $_total;

    /** @var CCodable */
    public $_ref_codable;

    /** @var CMediusers */
    public $_ref_praticien;

    /** @var CActeCCAM[] */
    public $_ref_actes_ccam;

    /** @var CActeCCAM[] */
    public $_ref_actes_ccam_facturables;

    /** @var CCodageCCAM */
    public $_codage_sibling;

    /** @var bool Indique la visibilité des dépassements d'honoraires */
    public $_show_depassement;

    /** @var bool Indique si l'utilisateur possède une dérogation si le codable est verrouillé */
    public $_codage_derogation;

    /** @var string La date à partir de laquelle la dérogation se termine */
    public $_date_lock;

    /** @var bool If false, the doUpdateMontant of the CActe won't be called */
    public $_update_act_amounts = true;

    /** @var array */
    protected static $association_rules = [
        'M'   => 'auto',
        'G1'  => 'auto',
        'EA'  => 'ask',
        'EB'  => 'ask',
        'EC'  => 'ask',
        'ED'  => 'ask',
        'EE'  => 'ask',
        'EF'  => 'ask',
        'EG1' => 'auto',
        'EG2' => 'auto',
        'EG3' => 'auto',
        'EG4' => 'auto',
        'EG5' => 'auto',
        'EG6' => 'ask',
        'EG7' => 'auto',
        'EH'  => 'ask',
        'EI'  => 'auto',
        'GA'  => 'auto',
        'GB'  => 'auto',
        'G2'  => 'auto',
    ];

    /**
     * @see parent::getSpec()
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table                        = 'codage_ccam';
        $spec->key                          = 'codage_ccam_id';
        $spec->uniques['codable_praticien'] = [
            'codable_class',
            'codable_id',
            'praticien_id',
            'activite_anesth',
            'date',
        ];

        return $spec;
    }

    /**
     * @see parent::getProps()
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['association_rule'] = 'enum list|' . implode('|', array_keys(self::$association_rules)) . ' default|M';
        $props['association_mode'] = 'enum list|auto|user_choice default|auto';
        $props['codable_class']    = 'str notNull class';
        $props['codable_id']       = 'ref notNull class|CCodable meta|codable_class back|codages_ccam';
        $props['praticien_id']     = 'ref notNull class|CMediusers back|codage_ccam';
        $props['locked']           = 'bool notNull default|0';
        $props['activite_anesth']  = 'bool notNull default|0';
        $props['date']             = 'date notNull';
        $props['date_unlock']      = 'dateTime';
        $props['_total']           = 'currency';

        return $props;
    }

    /**
     * @inheritdoc
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        if ($this->codable_class == 'COperation' || $this->codable_class == 'CSejour') {
            $this->loadPraticien();
            $this->_show_depassement = CCodageCCAM::getVisibiliteDepassement($this->_ref_praticien);
        } else {
            $this->_show_depassement = true;
        }

        $delay_auto_relock = CAppUI::gconf('dPccam codage delay_auto_relock');
        $worked_days       = CAppUI::gconf('dPsalleOp COperation modif_actes_worked_days') == '1' ? true : false;

        if ($this->date_unlock && $delay_auto_relock) {
            $this->_date_lock = CMbDT::dateTime("+ $delay_auto_relock DAYS", $this->date_unlock);

            $duration = time() - strtotime($this->date_unlock);

            if ($worked_days) {
                while (!CMbDT::isWorkingDay($this->_date_lock)) {
                    $this->_date_lock = CMbDT::dateTime("+ 1 DAYS", $this->_date_lock);
                }
                $days = CMbDT::getDays(CMbDT::date($this->date_unlock), CMbDT::date());
                foreach ($days as $day) {
                    if ($duration >= 86400 && !CMbDT::isWorkingDay($day)) {
                        $duration -= 86400;
                    }
                }
            }

            if ($duration <= $delay_auto_relock * 86400) {
                $this->_codage_derogation = true;
            }
        }
    }

    /**
     * @see parent::store()
     */
    public function store(): ?string
    {
        $this->completeField('codable_class', 'codable_id', 'activite_anesth');

        /* Set the default value for activite_anesth because the framework doesn't set it
           before checking the uniques constraints */
        if (!$this->_id && !$this->activite_anesth) {
            $this->activite_anesth = '0';
        }

        // Mutex to avoid storing same object for scripts called at the same time
        $mutex = new CMbMutex("{$this->codable_class}-{$this->codable_id}-{$this->activite_anesth}");
        $mutex->acquire();

        /* Create the CCodageCCAM for the anesthesia activity if the practitioner is an anesthesist */
        if (!$this->_id && !$this->activite_anesth) {
            $this->loadPraticien();
            if ($this->_ref_praticien->isAnesth()) {
                $this->loadCodable();
                self::get($this->_ref_codable, $this->praticien_id, 4, $this->date);
            }
        }

        $change_date_acts = 0;
        if ($this->_id) {
            $change_date_acts = $this->fieldModified('date');
            $this->loadOldObject();
            $acts = $this->_old->loadActesCCAM();
        }

        // Prevent storing duplicate existant CCodageCCAM
        if (!$this->_id) {
            $duplicate = new static();
            foreach ($this->getSpec()->uniques['codable_praticien'] as $_field) {
                $this->completeField($_field);
                $duplicate->$_field = $this->$_field;
            }

            if ($duplicate->loadMatchingObject()) {
                $mutex->release();

                return 'CCodageCCAM-failed-codable_praticien';
            }
        }

        $msg = parent::store();

        if ($change_date_acts) {
            foreach ($acts as $_act) {
                $_date           = explode(' ', $_act->execution);
                $_act->execution = $this->date . " $_date[1]";
                $_msg            = $_act->store();
            }
        }

        $mutex->release();

        return $msg;
    }

    /**
     * @see parent::loadView()
     */
    public function loadView(): void
    {
        parent::loadView();
        $this->loadPraticien()->loadRefFunction();
        $this->loadCodable();
        $this->loadActesCCAM();
        foreach ($this->_ref_actes_ccam as &$_acte) {
            $_acte->getTarif();
            $_acte->loadRefCodeCCAM();
            $_activite = $_acte->_ref_code_ccam->activites[$_acte->code_activite];
            $_phase    = $_activite->phases[$_acte->code_phase];

            if ($_phase->_modificateurs && is_array($_phase->_modificateurs)) {
                /* Verification des modificateurs codés */
                foreach ($_phase->_modificateurs as $modificateur) {
                    $position = strpos($_acte->modificateurs, $modificateur->code);
                    if ($position !== false) {
                        if ($modificateur->_double == "1") {
                            $modificateur->_checked = $modificateur->code;
                        } elseif ($modificateur->_double == "2") {
                            $modificateur->_checked = $modificateur->code . $modificateur->_double;
                        } else {
                            $modificateur->_checked = null;
                        }
                    } else {
                        $modificateur->_checked = null;
                    }
                }
            }

            self::precodeModifiers($_phase->_modificateurs, $_acte, $this->_ref_codable);
        }

        $this->loadSibling();
    }

    /**
     * Load the siblings CCodageCCAM for the anesthetists
     *
     * @return void
     */
    public function loadSibling(): void
    {
        if ($this->_ref_praticien->isAnesth()) {
            $this->_codage_sibling = self::get(
                $this->_ref_codable,
                $this->praticien_id,
                $this->activite_anesth ? 1 : 4,
                $this->date
            );
            $this->_codage_sibling->loadActesCCAM();
        }
    }

    /**
     * Return the CCodageCCAM linked to the given codable and practitioner, and create it if it not exists
     *
     * @param CCodable $codable      The codable object
     * @param integer  $praticien_id The practitioner id
     * @param integer  $activite     Is the CCodage concern anesthesia activities or other activities
     * @param string   $date         The date
     * @param boolean  $creation     If true, a new CCodageCCAM will be created if none is found
     *
     * @return CCodageCCAM
     */
    public static function get(
        CCodable $codable,
        int $praticien_id,
        int $activite = 1,
        string $date = null,
        bool $creation = true
    ): self {
        if (!$date) {
            switch ($codable->_class) {
                case 'CConsultation':
                    $codable->loadRefPlageConsult();
                    if (!$date) {
                        $date = $codable->_date;
                    }
                    $date = $codable->_date;
                    break;
                case 'CDevisCodage':
                case 'COperation':
                case 'CModelCodage':
                case 'CEvenementPatient':
                    $date = $codable->date;
                    break;
                case 'CSejour':
                    return new CCodageCCAM();
                    break;
                default:
            }
        }

        $codage_ccam                = new CCodageCCAM();
        $codage_ccam->codable_class = $codable->_class;
        $codage_ccam->codable_id    = $codable->_id;
        $codage_ccam->praticien_id  = $praticien_id;
        $codage_ccam->loadPraticien();
        $codage_ccam->activite_anesth = ($activite === 4) ? '1' : '0';
        $codage_ccam->date            = $date;
        $codage_ccam->loadMatchingObject();

        if (!$codage_ccam->_id && $creation) {
            $codage_ccam->_apply_rules = false;
            $codage_ccam->store();
        }

        return $codage_ccam;
    }

    /**
     * Load the codable object
     *
     * @param bool $cache Use object cache
     *
     * @return CCodable|null
     */
    public function loadCodable(bool $cache = true): ?CCodable
    {
        if (!$this->codable_class || !$this->codable_id) {
            return null;
        }

        return $this->_ref_codable = $this->loadFwdRef('codable_id', $cache);
    }

    /**
     * Load the practitioner
     *
     * @param bool $cache Use object cache
     *
     * @return CMediusers|null
     */
    public function loadPraticien(bool $cache = true): ?CMediusers
    {
        return $this->_ref_praticien = $this->loadFwdRef('praticien_id', $cache);
    }

    /**
     * @inheritDoc
     */
    public function getPerm($permType): ?bool
    {
        $this->loadPraticien();

        return $this->_ref_praticien->getPerm($permType);
    }

    /**
     * Load the linked acts of the given act
     *
     * @return CActeCCAM[]
     */
    public function loadActesCCAM(): ?array
    {
        if ($this->_ref_actes_ccam) {
            return $this->_ref_actes_ccam;
        }

        $act                    = new CActeCCAM();
        $where                  = [];
        $where['object_class']  = " = '$this->codable_class'";
        $where['object_id']     = " = '$this->codable_id'";
        $where['executant_id']  = " = '$this->praticien_id'";
        $where['code_activite'] = $this->activite_anesth ? " = 4" : " != 4";
        $where['execution']     = " BETWEEN '$this->date 00:00:00' AND '$this->date 23:59:59'";
        $this->_ref_actes_ccam  = $act->loadList($where, "code_association");

        foreach ($this->_ref_actes_ccam as $_acte) {
            if (in_array($_acte->code_acte, $this->_check_failed_acts)) {
                unset($this->_ref_actes_ccam[$_acte->_id]);
                continue;
            }
            $_acte->loadRefCodeCCAM();
        }

        return $this->_ref_actes_ccam;
    }

    /**
     * Get the total sum of the price acts
     *
     * @return void
     */
    public function getTarifTotal(): void
    {
        $this->loadActesCCAM();
        $this->_total = 0;

        foreach ($this->_ref_actes_ccam as $_acte) {
            $this->_total += $_acte->getTarif();
            if ($this->_show_depassement) {
                $this->_total += $_acte->montant_depassement;
            }
        }
    }

    /**
     * Force the update of the rule
     *
     * @param bool $force force the update of the actes
     *
     * @return bool
     */
    public function updateRule(bool $force = false): bool
    {
        $this->guessRule();
        if ($this->fieldModified('association_rule') || $force) {
            $this->applyRuleToActes();

            return true;
        }
        $this->_check_asso = false;

        return false;
    }

    /**
     * @see parent::check()
     */
    public function check(): ?string
    {
        if ($this->_forwardRefMerging) {
            return null;
        }

        $this->completeField(
            'codable_class',
            'codable_id',
            'praticien_id',
            'association_mode',
            'association_rule',
            'locked',
            'activite_anesth',
            'date'
        );
        $this->loadOldObject();

        $codable = $this->loadCodable();
        if ($codable && ($codable->_class == 'COperation' || $codable->_class == 'CModelCodage')) {
            if (
                $this->date < CMbDT::date('-2 day', $codable->date) || $this->date > CMbDT::date(
                    '+2 day',
                    $codable->date
                )
            ) {
                return 'Impossible de créer un codage CCAM a une date différente de celle de l\'intervention';
            }
        }

        if ($this->_old->locked && $this->locked && !CModule::getCanDo('dPpmsi')->edit) {
            return "Codage verrouillé";
        }
        if (!$this->_id || $this->fieldModified('association_mode', 'auto')) {
            $this->guessRule();
        }
        if (!$this->_id || $this->fieldModified('association_rule')) {
            $this->applyRuleToActes();
        }

        return parent::check();
    }

    /**
     * Guess the correct rule and replace it
     *
     * @return string
     */
    public function guessRule(): string
    {
        if ($this->_id && $this->association_mode != 'auto') {
            return $this->association_rule;
        }

        return $this->association_rule = $this->checkRules();
    }

    /**
     * Guess the association code of all actes
     *
     * @return void
     */
    public function guessActesAssociation(): void
    {
        $this->completeField("association_rule");
        $this->getActsByTarif();
        if ($this->association_rule) {
            call_user_func([$this, "checkRule$this->association_rule"]);
            foreach ($this->_ref_actes_ccam as $_act) {
                $_act->_position = array_search($_act->_id, array_keys($this->_ordered_acts));
                $this->guessActeAssociation($this->association_rule, $_act);
            }
        }
    }

    /**
     * Apply the rule to all actes
     *
     * @return void
     */
    public function applyRuleToActes(): void
    {
        if (!$this->_apply_rules) {
            return;
        }
        $this->completeField("association_rule");
        $this->getActsByTarif();
        foreach ($this->_ref_actes_ccam as $_act) {
            $_act->_calcul_montant_base = 1;
            $_act->_position            = array_search($_act->_id, array_keys($this->_ordered_acts));
            $this->applyRule($this->association_rule, $_act);
            /* Force the association rules to not be updated in the CActeCCAM::store */
            $_act->_update_codage_rule = false;
            $_act->_preserve_montant = !$this->_update_act_amounts;

            if ($msg = $_act->store()) {
                CAppUI::setMsg($msg, UI_MSG_ERROR);
                if (!in_array($_act->code_acte, $this->_check_failed_acts)) {
                    $this->_check_failed_acts[] = $_act->code_acte;
                    $this->updateRule();
                    break;
                }
            }
        }
        $this->_apply_rules = false;
    }

    /**
     * Order the acts by price
     *
     * @return array
     */
    protected function getActsByTarif(): array
    {
        $this->loadActesCCAM();
        $this->checkFacturableActs();
        if (!isset($this->_ordered_acts)) {
            $this->_ordered_acts = [];
        }
        if (count($this->_ref_actes_ccam_facturables) == count($this->_ordered_acts)) {
            return $this->_ordered_acts;
        }

        foreach ($this->_ref_actes_ccam_facturables as $_act) {
            $this->_ordered_acts[$_act->_id] = $_act->getTarifBase();
        }

        return $this->_ordered_acts = self::orderActsByTarif($this->_ordered_acts);
    }

    /**
     * Reorder the acts by price
     *
     * @param array $disordered_acts The acts to reorder
     *
     * @return array
     */
    protected static function orderActsByTarif(array $disordered_acts): array
    {
        ksort($disordered_acts);
        arsort($disordered_acts);

        return $disordered_acts;
    }

    /**
     * Get all the possible associated codes of the CCAM acts
     *
     * @return void
     */
    protected function getComplementaryActs(): void
    {
        $this->loadActesCCAM();

        if (!isset($this->_complementary_acts)) {
            $this->_complementary_codes = [];
        }

        foreach ($this->_ref_actes_ccam as $act) {
            $act->loadRefCodeCCAM();
            foreach ($act->_ref_code_ccam->activites as $activite) {
                if ($act->code_activite == $activite->numero) {
                    $this->_complementary_codes = array_merge(
                        $this->_complementary_codes,
                        CMbArray::pluck($activite->assos, 'code')
                    );
                }
            }
        }
    }

    /**
     * Check if the given act is among the associated complementory codes of the coded acts
     *
     * @param CActeCCAM $act The act to check
     *
     * @return bool
     */
    protected function isComplementaryAct(CActeCCAM $act): bool
    {
        $this->getComplementaryActs();

        return in_array($act->code_acte, $this->_complementary_codes);
    }

    /**
     * Reset the facturable field of the acts, and make the acts with a price equal to 0 unfacturable
     *
     * @return void
     */
    protected function checkFacturableActs(): void
    {
        $this->_ref_actes_ccam_facturables = [];
        foreach ($this->_ref_actes_ccam as $_acte) {
            if (
                (!$_acte->facturable && !$_acte->facturable_auto)
                || ($_acte->getTarifSansAssociationNiCharge() == 0 && !$_acte->montant_depassement)
            ) {
                $_acte->_guess_facturable = '0';
            } else {
                $this->_ref_actes_ccam_facturables[$_acte->_id] = $_acte;
            }
        }
    }

    /**
     * Count the number of modifiers F, U, P and S coded
     *
     * @param CActeCCAM $act The act
     *
     * @return integer
     */
    public static function countExclusiveModifiers(CActeCCAM &$act): int
    {
        $act->getLinkedActes(true, true, true, true);
        $exclusive_modifiers       = ['F', 'U', 'P', 'S', 'O'];
        $count_exclusive_modifiers = count(array_intersect($act->_modificateurs, $exclusive_modifiers));

        foreach ($act->_linked_actes as $_linked_act) {
            $count_exclusive_modifiers += count(array_intersect($_linked_act->_modificateurs, $exclusive_modifiers));
        }

        $ngap_acts = $act->getLinkedActesNGAP(true, true);
        foreach ($ngap_acts as $ngap_act) {
            if (in_array($ngap_act->complement, ['N', 'F']) || $ngap_act->code === 'MM') {
                $count_exclusive_modifiers++;
            }
        }

        $act->_exclusive_modifiers = $count_exclusive_modifiers;

        return $count_exclusive_modifiers;
    }

    /**
     * Check if a modifier has been checked on the linked acts of the given act
     *
     * @param string    $mod The modifier
     * @param CActeCCAM $act The act
     *
     * @return boolean
     */
    public static function isModifierchecked(string $mod, CActeCCAM &$act): bool
    {
        $act->getLinkedActes(true, true, true, true);

        $modifier = false;
        foreach ($act->_linked_actes as $_linked_act) {
            if (in_array($mod, $_linked_act->_modificateurs)) {
                $modifier = true;
            }
        }

        return $modifier;
    }

    /**
     * Check the modifiers of the given act
     *
     * @param array     $modifiers The modifiers to check
     * @param CActeCCAM $act       The dateTime of the execution of the act
     * @param CCodable  $codable   The codable
     *
     * @return void
     */
    public static function precodeModifiers(
        array &$modifiers,
        CActeCCAM &$act,
        CCodable $codable,
        bool $force_check = false
    ): void {
        if (!is_array($modifiers)) {
            return;
        }

        $date = CMbDT::date(null, $act->execution);
        $time = CMbDT::time(null, $act->execution);
        $act->loadRefExecutant();
        $act->_ref_executant->loadRefDiscipline();
        $act->loadRefCodeCCAM();
        $spec_cpam = $act->_ref_executant->spec_cpam_id;
        $patient   = $codable->loadRefPatient();
        $patient->evalAge();
        $checked                     = false;
        $spec_chir_gyneco            = [4, 7, 10, 11, 15, 16, 18, 41, 43, 44, 45, 46, 47, 48, 49, 70, 77, 79];
        $spe_gyneco                  = $spe_gyneco = [7, 70, 77, 79];
        $spe_gen_pediatre            = [1, 12, 21, 22, 23];
        $count_exclusive_modifiers   = self::countExclusiveModifiers($act);
        $patient_age                 = $patient->evalAge(CMbDT::date($act->execution));
        $config_incoherent_modifiers = CAppUI::gconf('dPccam codage block_incoherent_modifiers');

        $maj_transitoire_chir = false;
        foreach ($modifiers as $_modifier) {
            if ($_modifier->code == 'J') {
                $maj_transitoire_chir = true;
                break;
            }
        }

        foreach ($modifiers as $_modifier) {
            switch ($_modifier->code) {
                case "7":
                    $checked = CAppUI::pref('precode_modificateur_7') && $codable->_class == 'COperation'
                        && (isset($codable->anesth_id) || $act->_ref_executant->isAnesth());
                    if ($checked) {
                        $_modifier->_state = 'prechecked';
                    } else {
                        $_modifier->_state = ($codable->_class == 'COperation' && (isset($codable->anesth_id)
                                || $act->_ref_executant->isAnesth())) ? null : 'not_recommended';
                    }
                    break;
                case 'A':
                    $checked           = ($patient_age < 4 || $patient_age >= 80);
                    $_modifier->_state = $checked ? 'prechecked' : 'not_recommended';
                    break;
                case 'E':
                    $checked           = $patient->_annees < 5;
                    $_modifier->_state = $checked ? 'prechecked' : 'not_recommended';
                    break;
                case 'F':
                    $checked = (($count_exclusive_modifiers == 1 && $_modifier->_checked) || $count_exclusive_modifiers == 0)
                        && (((CMbDT::transform('', $act->execution, '%w') == 0 || CMbDT::isHoliday($date))
                                && ($time > '08:00:00' && $time < '20:00:00'))
                            || ($act->_ref_executant->spec_cpam_id == 1 && CAppUI::gconf(
                                'dPccam codage holiday_charge_saturday_for_generalists'
                            )
                                && CMbDT::transform(
                                    '',
                                    $act->execution,
                                    '%w'
                                ) == 6 && $time > '12:00:00' && $time < '20:00:00')
                        );
                    if ($checked) {
                        if (!CAppUI::pref('enabled_majoration_F')) {
                            $checked = false;
                        }
                        $_modifier->_state = 'prechecked';
                    } elseif (
                        ($count_exclusive_modifiers == 1 && $_modifier->_checked) || $count_exclusive_modifiers > 0
                    ) {
                        $_modifier->_state = 'forbidden';
                    } else {
                        $_modifier->_state = 'not_recommended';
                    }
                    break;
                case 'G':
                    if ($patient_age > 3) {
                        $_modifier->_state = 'not_recommended';
                    }
                    break;
                case "J":
                    $checked           = $codable->_class == 'COperation' && CAppUI::pref('precode_modificateur_J');
                    $_modifier->_state = $checked ? 'prechecked' : null;
                    break;
                case 'K':
                    /* Modificateur applicable uniquement aux spécialités de chirurgie et de gynéco-obsétrique */
                    $checked = CAppUI::gconf('dPccam codage precheck_modifiers_k_t')
                        && in_array($act->_ref_executant->spec_cpam_id, $spec_chir_gyneco)
                        && (($act->_ref_executant->secteur === '1' || $act->_ref_executant->pratique_tarifaire == 'optamco')
                            || (($patient->c2s || $patient->acs) && !$act->montant_depassement && $act->_ref_executant->pratique_tarifaire == 'optam'));
                    if ($act->_id && in_array('T', $act->_modificateurs)) {
                        $_modifier->_state = 'forbidden';
                    } elseif ($checked) {
                        $_modifier->_state = 'prechecked';
                    } elseif (!in_array($spec_cpam, $spe_gyneco) && !$maj_transitoire_chir) {
                        $_modifier->_state = 'not_recommended';
                    }
                    break;
                case 'L':
                    $checked = self::isModifierchecked('L', $act);
                    if (self::isModifierchecked('L', $act)) {
                        $_modifier->_state = 'prechecked';
                    }
                    break;
                case 'M':
                    $checked = 0;
                    if (!in_array($spec_cpam, $spe_gen_pediatre)) {
                        $_modifier->_state = 'not_recommended';
                    }
                    break;
                case 'N':
                    $checked           = $patient->_annees < 13;
                    $_modifier->_state = $checked ? 'prechecked' : 'not_recommended';
                    break;
                case 'O':
                    $checked = $count_exclusive_modifiers == 1 && $_modifier->_checked;
                    if ($checked) {
                        $_modifier->_state = 'prechecked';
                    } elseif (
                        ($count_exclusive_modifiers == 1 && $_modifier->_checked) || $count_exclusive_modifiers > 0
                    ) {
                        $_modifier->_state = 'forbidden';
                    }
                    break;
                case 'P':
                    $checked = (($count_exclusive_modifiers == 1 && $_modifier->_checked) || $count_exclusive_modifiers == 0) &&
                        (in_array($spec_cpam, $spe_gen_pediatre)) &&
                        ($time > "20:00:00" && $time < "23:59:59");
                    if ($checked) {
                        if (!CAppUI::pref('enabled_majoration_F')) {
                            $checked = false;
                        }
                        $_modifier->_state = 'prechecked';
                    } elseif (
                        ($count_exclusive_modifiers == 1 && $_modifier->_checked) || $count_exclusive_modifiers > 0
                    ) {
                        $_modifier->_state = 'forbidden';
                    } else {
                        $_modifier->_state = 'not_recommended';
                    }
                    break;
                case 'R':
                    if (self::isModifierchecked('R', $act)) {
                        $_modifier->_state = 'prechecked';
                        $checked           = true;
                    }
                    break;
                case 'S':
                    $checked = (
                            in_array($spec_cpam, $spe_gen_pediatre) || in_array(
                                $spec_cpam,
                                $spec_chir_gyneco
                            ) || in_array($spec_cpam, $spe_gyneco)
                            || $spec_cpam == 2 || ($codable->_class == "COperation" && $codable->loadRefTypeAnesth()->name))
                        && ($time >= "00:00:00" && $time < "08:00:00") &&
                        (($count_exclusive_modifiers == 1 && $_modifier->_checked) || $count_exclusive_modifiers == 0)
                    ;
                    if ($checked) {
                        if (!CAppUI::pref('enabled_majoration_F')) {
                            $checked = false;
                        }
                        $_modifier->_state = 'prechecked';
                    } elseif (
                        ($count_exclusive_modifiers == 1 && $_modifier->_checked) || $count_exclusive_modifiers > 0
                    ) {
                        $_modifier->_state = 'forbidden';
                    } else {
                        $_modifier->_state = 'not_recommended';
                    }
                    break;
                case 'T':
                    $checked = CAppUI::gconf('dPccam codage precheck_modifiers_k_t')
                        && in_array($act->_ref_executant->spec_cpam_id, $spec_chir_gyneco)
                        && ($act->_ref_executant->pratique_tarifaire == 'optam' && $act->_ref_executant->secteur != '1')
                        && ((!$patient->acs && !$patient->c2s) || (($patient->acs || $patient->c2s) && $act->montant_depassement));
                    if ($act->_id && in_array('K', $act->_modificateurs)) {
                        $_modifier->_state = 'forbidden';
                    } elseif ($checked) {
                        $_modifier->_state = 'prechecked';
                    } elseif (!in_array($spec_cpam, $spe_gyneco) && !$maj_transitoire_chir) {
                        $_modifier->_state = 'not_recommended';
                    }
                    break;
                case 'U':
                    $checked = (($count_exclusive_modifiers == 1 && $_modifier->_checked) || $count_exclusive_modifiers == 0)
                        && ((!in_array($spec_cpam, $spe_gen_pediatre) && !in_array($spec_cpam, $spec_chir_gyneco)
                                && ($time > '20:00:00' || $time < '08:00:00'))
                            || (in_array($spec_cpam, $spec_chir_gyneco) && ($time > '20:00:00' || $time < '00:00:00')));
                    if ($checked) {
                        if (!CAppUI::pref('enabled_majoration_F')) {
                            $checked = false;
                        }
                        $_modifier->_state = 'prechecked';
                    } elseif (
                        ($count_exclusive_modifiers == 1 && $_modifier->_checked) || $count_exclusive_modifiers > 0
                    ) {
                        $_modifier->_state = 'forbidden';
                    } else {
                        $_modifier->_state = 'not_recommended';
                    }
                    break;
                default:
                    $checked = 0;
                    break;
            }

            /* If the config for blocking the incoherent modifiers is on, and the modifier is not recommended,
            *  we set the state to forbidden for blocking it */
            if ($_modifier->_state == 'not_recommended' && $config_incoherent_modifiers) {
                $_modifier->_state = 'forbidden';
            }

            if ((!$act->_id || $force_check) && !isset($_modifier->_checked)) {
                $_modifier->_checked = $checked;
            }
        }

        /* Handle the case where the mods S and U are both prechecked */
        if (isset($modifiers['S']) && isset($modifiers['U'])) {
            $modS = &$modifiers['S'];
            $modU = &$modifiers['U'];
            if ($modS->_state == 'prechecked' && $modU->_state == 'prechecked') {
                $modU->_checked = 0;
                $modU->_state   = 'forbidden';
            }
        }
    }

    /**
     * Vérification de l'application d'une règle nommée sur un acte
     *
     * @param string    $rulename Rule name
     * @param CActeCCAM $act      The act
     *
     * @return void
     */
    public function guessActeAssociation(string $rulename, CActeCCAM $act): void
    {
        if ($act->_position === false) {
            $act->facturable         = '0';
            $act->_guess_association = '';
            $act->_guess_regle_asso  = $rulename;
        } else {
            $act->loadRefCodeCCAM();
            call_user_func([$this, "applyRule$rulename"], $act);
        }
    }

    /**
     * Application d'une règle nommée sur un acte
     *
     * @param string    $rulename Rule name
     * @param CActeCCAM $act      The act
     *
     * @return void
     */
    protected function applyRule(string $rulename, CActeCCAM $act): void
    {
        $this->guessActeAssociation($rulename, $act);
        $act->code_association = $act->_guess_association;
        if ($act->facturable_auto) {
            $act->facturable = $act->_guess_facturable;
        }
    }

    /**
     * Guess the association code for an act
     *
     * @return string
     */
    public function checkRules(): ?string
    {
        $this->getActsByTarif();
        $this->_check_rules    = [];
        $this->_possible_rules = [];
        $firstRule             = null;
        foreach (self::$association_rules as $_rule => $_type) {
            if (self::isRuleAllowed($_rule)) {
                $this->_possible_rules[$_rule] = call_user_func([$this, "checkRule$_rule"]);
                if ($firstRule === null && $this->_possible_rules[$_rule] && $_type == "auto") {
                    $firstRule = $_rule;
                }
            }
        }

        return $firstRule;
    }

    /**
     * Check if the rule is allowed to be used
     *
     * @param string $rule The name of the rule
     *
     * @return boolean
     */
    protected static function isRuleAllowed(string $rule): bool
    {
        $feature = "dPccam associations rules $rule";
        if (strpos($rule, 'G') === 0) {
            $feature = "dPccam associations rules G";
        }

        return (bool)CAppUI::gconf($feature);
    }

    /* Association rules */

    /**
     * Check the association rule G1
     *
     * @return bool
     */
    protected function checkRuleM(): bool
    {
        if (count($this->_ref_actes_ccam_facturables) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Apply the association rule G1 to the given act
     *
     * @param CActeCCAM $act The act
     *
     * @return void
     * @throws Exception
     */
    protected function applyRuleM(CActeCCAM $act): void
    {
        $act->completeField('facturable', 'code_association');
        $act->_guess_facturable  = $act->facturable;
        $act->_guess_association = $act->code_association;
        $act->_guess_regle_asso  = 'M';
    }

    /**
     * Check the association rule G1
     *
     * @return bool
     */
    protected function checkRuleG1(): bool
    {
        if (count($this->_ref_actes_ccam_facturables) != 1) {
            return false;
        }

        return true;
    }

    /**
     * Apply the association rule G1 to the given act
     *
     * @param CActeCCAM $act The act
     *
     * @return void
     */
    protected function applyRuleG1(CActeCCAM $act): void
    {
        switch ($act->_position) {
            case 0:
                $act->_guess_facturable  = '1';
                $act->_guess_association = '';
                $act->_guess_regle_asso  = 'G1';
                break;
            default:
                $act->_guess_facturable  = '0';
                $act->_guess_association = '';
                $act->_guess_regle_asso  = 'G1';
        }
    }

    /**
     * ### Règle d'association générale A ###
     *
     * * Nombre d'actes : 2
     * * Cas d'utilisation : Dans le cas d'une association de __2 actes seulement__, dont l'un est un soit un geste
     * complémentaire, soit un supplément, soit un acte d'imagerie pour acte de radiologie interventionnelle ou
     * cardiologie interventionnelle (Paragraphe 19.01.09.02), il ne faut pas indiquer de code d'association
     *
     * @return bool
     */
    protected function checkRuleGA(): bool
    {
        if (count($this->_ref_actes_ccam_facturables) != 2) {
            return false;
        }

        $complement = 0;
        foreach ($this->_ref_actes_ccam_facturables as $_acte_ccam) {
            if (
                $_acte_ccam->_ref_code_ccam->isComplement()
                || $_acte_ccam->_ref_code_ccam->isSupplement()
                || $_acte_ccam->_ref_code_ccam->isRadioCardioInterv()
                || $this->isComplementaryAct($_acte_ccam)
            ) {
                $complement++;
            }
        }

        if ($complement != 1) {
            return false;
        }

        return true;
    }

    /**
     * Apply the association rule GA to the given act
     *
     * @param CActeCCAM $act The act
     *
     * @return void
     */
    protected function applyRuleGA(CActeCCAM $act): void
    {
        $this->loadCodable();

        $ordered_acts_ga = $this->_ordered_acts;
        foreach ($this->_ref_actes_ccam_facturables as $_acte_ccam) {
            if (
                $_acte_ccam->_ref_code_ccam->isSupplement()
                || $_acte_ccam->_ref_code_ccam->isComplement()
                || $_acte_ccam->_ref_code_ccam->isRadioCardioInterv()
                || $this->isComplementaryAct($_acte_ccam)
            ) {
                unset($ordered_acts_ga[$_acte_ccam->_id]);
                if ($_acte_ccam->_id == $act->_id) {
                    $act->_position = -1;
                }
            }
        }

        if ($act->_position != -1) {
            self::orderActsByTarif($ordered_acts_ga);
            $act->_position = array_search($act->_id, array_keys($ordered_acts_ga));
        }

        /* Exception for the acts AHQJ021 and YYYY041: the association codes must be set to 1,
           even if those acts are supplements */
        if (
            (strpos($this->_ref_codable->codes_ccam, 'AHQJ021') !== false || strpos(
                $this->_ref_codable->codes_ccam,
                'YYYY041'
            ) !== false) && $this->activite_anesth == '1'
        ) {
            $act->_guess_facturable  = '1';
            $act->_guess_association = '1';
            $act->_guess_regle_asso  = 'GA';
        } elseif (
            $act->_position == 0 || $act->_position == -1 || $act->_ref_code_ccam->isSupplement()
            || $act->_ref_code_ccam->isComplement() || $act->_ref_code_ccam->isRadioCardioInterv(
            ) || $this->isComplementaryAct($act)
        ) {
             /* Normal case for the rule GA */
            $act->_guess_facturable  = '1';
            $act->_guess_association = '';
            $act->_guess_regle_asso  = 'GA';
        } else {
            $act->_guess_facturable  = '0';
            $act->_guess_association = '';
            $act->_guess_regle_asso  = 'GA';
        }
    }

    /**
     * ### Règle d'association générale B ###
     * * Nombre d'actes : 3
     * * Cas d'utilisation : Si un acte est associé à un geste complémentaire et à un supplément, le code d'assciation
     * est 1 pour chacun des actes.
     *
     * @return bool
     */
    protected function checkRuleGB(): bool
    {
        if (count($this->_ref_actes_ccam_facturables) != 3) {
            return false;
        }

        $supp  = 0;
        $comp  = 0;
        $radio = 0;
        foreach ($this->_ref_actes_ccam_facturables as $_acte_ccam) {
            if ($_acte_ccam->_ref_code_ccam->isComplement() || $this->isComplementaryAct($_acte_ccam)) {
                $comp++;
            } elseif ($_acte_ccam->_ref_code_ccam->isSupplement()) {
                $supp++;
            } elseif ($_acte_ccam->_ref_code_ccam->isRadioCardioInterv()) {
                $radio++;
            }
        }
        $total = $supp + $radio + $comp;

        if ($total == 2 && $supp != 2 && $comp != 2 && $radio != 2) {
            return true;
        }

        return false;
    }

    /**
     * Apply the association rule GB to the given act
     *
     * @param CActeCCAM $act The act
     *
     * @return void
     */
    protected function applyRuleGB(CActeCCAM $act): void
    {
        $ordered_acts_gb = $this->_ordered_acts;
        foreach ($this->_ref_actes_ccam_facturables as $_acte_ccam) {
            if (
                $_acte_ccam->_ref_code_ccam->isSupplement()
                || $_acte_ccam->_ref_code_ccam->isComplement()
                || $_acte_ccam->_ref_code_ccam->isRadioCardioInterv()
                || $this->isComplementaryAct($_acte_ccam)
            ) {
                unset($ordered_acts_gb[$_acte_ccam->_id]);
                if ($_acte_ccam->_id == $act->_id) {
                    $act->_position = -1;
                }
            }
        }

        if ($act->_position != -1) {
            self::orderActsByTarif($ordered_acts_gb);
            $act->_position = array_search($act->_id, array_keys($ordered_acts_gb));
        }

        if (
            $act->_position == 0 || $act->_ref_code_ccam->isSupplement()
            || $act->_ref_code_ccam->isComplement()
            || $act->_ref_code_ccam->isRadioCardioInterv()
            || $this->isComplementaryAct($act)
        ) {
            $act->_guess_facturable  = '1';
            $act->_guess_association = '1';
            $act->_guess_regle_asso  = 'GB';
        } else {
            $act->_guess_facturable  = '0';
            $act->_guess_association = '';
            $act->_guess_regle_asso  = 'GB';
        }
    }

    /**
     * Check the association rule G2
     *
     * @return bool
     */
    protected function checkRuleG2(): bool
    {
        if (count($this->_ref_actes_ccam_facturables) >= 2) {
            return true;
        }

        return false;
    }

    /**
     * Apply the association rule G2 to the given act
     *
     * @param CActeCCAM $act The act
     *
     * @return void
     */
    protected function applyRuleG2(CActeCCAM $act): void
    {
        $ordered_acts_g2 = $this->_ordered_acts;
        foreach ($this->_ref_actes_ccam_facturables as $_acte_ccam) {
            if (
                $_acte_ccam->_ref_code_ccam->isSupplement()
                || $_acte_ccam->_ref_code_ccam->isComplement()
                || $_acte_ccam->_ref_code_ccam->isRadioCardioInterv()
                || $this->isComplementaryAct($_acte_ccam)
            ) {
                unset($ordered_acts_g2[$_acte_ccam->_id]);
                if ($_acte_ccam->_id == $act->_id) {
                    $act->_position = -1;
                }
            }
        }

        if ($act->_position != -1) {
            self::orderActsByTarif($ordered_acts_g2);
            $act->_position = array_search($act->_id, array_keys($ordered_acts_g2));
        }

        switch ($act->_position) {
            case -1:
            case 0:
                $act->_guess_facturable  = '1';
                $act->_guess_association = '1';
                $act->_guess_regle_asso  = 'G2';
                break;
            case 1:
                $act->_guess_facturable  = '1';
                $act->_guess_association = '2';
                $act->_guess_regle_asso  = 'G2';
                break;
            default:
                $act->_guess_facturable  = '0';
                $act->_guess_association = '';
                $act->_guess_regle_asso  = 'G2';
        }
    }

    /**
     * ### Exception sur les actes de chirugie (membres différents) ###
     * * Nombre d'actes : 2
     * * Cas d'utilisation : Pour les __actes de chirurgie portant sur des membres différents__ (sur le tronc et un
     * membre, sur la tête et un membre), l'acte dont le tarif (hors modificateurs) est le moins élevé est tarifé à 75%
     * de sa valeur
     *
     * @return bool
     */
    protected function checkRuleEA(): bool
    {
        $chap11 = 0;
        $chap12 = 0;
        $chap13 = 0;
        $chap14 = 0;
        foreach ($this->_ref_actes_ccam_facturables as $_act) {
            switch ($_act->_ref_code_ccam->chapitres[0]['db']) {
                case '000011':
                    $chap11++;
                    break;
                case '000012':
                    $chap12++;
                    break;
                case '000013':
                    $chap13++;
                    break;
                case '000014':
                    $chap14++;
                    break;
                default:
            }
        }

        if (count($this->_ref_actes_ccam_facturables) < 2 || (!$chap11 && !$chap12 && !$chap13 && !$chap14)) {
            return false;
        }

        return true;
    }

    /**
     * Apply the association rule EA to the given act
     *
     * @param CActeCCAM $act The act
     *
     * @return void
     */
    protected function applyRuleEA(CActeCCAM $act): void
    {
        $ordered_acts_ea = $this->_ordered_acts;
        foreach ($this->_ref_actes_ccam_facturables as $_act) {
            if (
                $_act->_ref_code_ccam->isSupplement()
                || $_act->_ref_code_ccam->isComplement()
                || $_act->_ref_code_ccam->isRadioCardioInterv()
                || $this->isComplementaryAct($_act)
            ) {
                unset($ordered_acts_ea[$_act->_id]);
                if ($_act->_id == $act->_id) {
                    $act->_position = -1;
                }
            }
        }
        if ($act->_position != -1) {
            $ordered_acts_ea = self::orderActsByTarif($ordered_acts_ea);
            $act->_position  = array_search($act->_id, array_keys($ordered_acts_ea));
        }

        switch ($act->_position) {
            case -1:
            case 0:
                $act->_guess_facturable  = '1';
                $act->_guess_association = '1';
                $act->_guess_regle_asso  = 'EA';
                break;
            case 1:
                $act->_guess_facturable  = '1';
                $act->_guess_association = '3';
                $act->_guess_regle_asso  = 'EA';
                break;
            default:
                $act->_guess_facturable  = '0';
                $act->_guess_association = '';
                $act->_guess_regle_asso  = 'EA';
        }
    }

    /**
     * ### Exception sur les actes de chirugie (lésions traumatiques multiples et récentes) ###
     * * Nombre d'actes : 2 ou 3
     * * Cas d'utilisation : Pour les __actes de chirurgie pour lésions traumatiques et récentes__, l'association de
     * trois actes au plus, y comprit les gestes complémentaires, peut être tarifée.
     * L'acte dont le tarif (hors modificateurs) est le plus élevé est tarifé à taux plein. Le deuxième est tarifé à
     * 75% de sa valeur, et le troisième à 50%.
     *
     * @return bool
     */
    protected function checkRuleEB(): bool
    {
        $nb_chir = 0;
        foreach ($this->_ref_actes_ccam_facturables as $_act) {
            if ($_act->getCodeRegroupement() == 'ADC') {
                $nb_chir++;
            }
        }
        if (!$nb_chir) {
            return false;
        }

        return true;
    }

    /**
     * Apply the association rule EB to the given act
     *
     * @param CActeCCAM $act The act
     *
     * @return void
     */
    protected function applyRuleEB(CActeCCAM $act): void
    {
        $ordered_acts_eb = $this->_ordered_acts;
        foreach ($this->_ref_actes_ccam_facturables as $_act) {
            if (
                $_act->_ref_code_ccam->isSupplement()
                || $_act->_ref_code_ccam->isComplement()
                || $_act->_ref_code_ccam->isRadioCardioInterv()
                || $this->isComplementaryAct($_act)
            ) {
                unset($ordered_acts_eb[$_act->_id]);
                if ($_act->_id == $act->_id) {
                    $act->_position = -1;
                }
            }
        }

        if ($act->_position != -1) {
            $ordered_acts_eb = self::orderActsByTarif($ordered_acts_eb);
            $act->_position  = array_search($act->_id, array_keys($ordered_acts_eb));
        }

        switch ($act->_position) {
            case -1:
            case 0:
                $act->_guess_facturable  = '1';
                $act->_guess_association = '1';
                $act->_guess_regle_asso  = 'EB';
                break;
            case 1:
                $act->_guess_facturable  = '1';
                $act->_guess_association = '3';
                $act->_guess_regle_asso  = 'EB';
                break;
            case 2:
                $act->_guess_facturable  = '1';
                $act->_guess_association = '2';
                $act->_guess_regle_asso  = 'EB';
                break;
            default:
                $act->_guess_facturable  = '0';
                $act->_guess_association = '';
                $act->_guess_regle_asso  = 'EB';
        }
    }

    /**
     * ### Actes de chirugie carcinologique en ORL associant une exérèse, un curage et une reconstruction ###
     * * Nombre d'actes : 3
     * * Cas d'utilisation : Pour les __actes de chirugie carcinologique en ORL associant une exérèse, un curage et une
     * reconstruction__, l'acte dont le tarif (hots modificateurs) est le plus élevé est tarifé à taux plein, le
     * deuxième et le troisième sont tarifés à 50% de leurs valeurs.
     *
     * @return bool
     */
    protected function checkRuleEC(): bool
    {
        $exerese = false;
        $curage  = false;
        $reconst = false;
        foreach ($this->_ref_actes_ccam_facturables as $_acte_ccam) {
            $libelle = $_acte_ccam->_ref_code_ccam->libelleLong;
            if (stripos($libelle, 'exérèse') !== false) {
                $exerese = true;
            } elseif (stripos($libelle, 'curage') !== false) {
                $curage = true;
            } elseif (stripos($libelle, 'reconstruction') !== false) {
                $reconst = true;
            }
        }

        if (!$exerese && !$curage && !$reconst) {
            return false;
        }

        return true;
    }

    /**
     * Apply the association rule EC to the given act
     *
     * @param CActeCCAM $act The act
     *
     * @return void
     */
    protected function applyRuleEC(CActeCCAM $act): void
    {
        switch ($act->_position) {
            case 0:
                $act->_guess_facturable  = '1';
                $act->_guess_association = '1';
                $act->_guess_regle_asso  = 'EC';
                break;
            case 1:
            case 2:
                $act->_guess_facturable  = '1';
                $act->_guess_association = '2';
                $act->_guess_regle_asso  = 'EC';
                break;
            default:
                $act->_guess_facturable  = '0';
                $act->_guess_association = '';
                $act->_guess_regle_asso  = 'EC';
        }
    }

    /**
     * Actes d'échographie portant sur plusieurs régions anatomiques
     *
     * @return bool
     */
    protected function checkRuleED(): bool
    {
        $chapters_echo = [
            '01.01.03.',
            '02.01.02.',
            '04.01.03.',
            '06.01.02.',
            '07.01.03.',
            '08.01.02.',
            '09.01.02.',
            '10.01.01.',
            '14.01.01.',
            '15.01.01.',
            '16.01.01.',
            '16.02.01.',
            '17.01.01.',
            '19.01.04.',
        ];
        $nb_echo       = 0;

        foreach ($this->_ref_actes_ccam_facturables as $_acte_ccam) {
            $chapters = $_acte_ccam->_ref_code_ccam->chapitres;
            if (isset($chapters[2]) && in_array($chapters[2]['rang'], $chapters_echo)) {
                $nb_echo++;
            }
        }

        if (!$nb_echo) {
            return false;
        }

        return true;
    }

    /**
     * Apply the association rule ED to the given act
     *
     * @param CActeCCAM $act The act
     *
     * @return void
     */
    protected function applyRuleED(CActeCCAM $act): void
    {
        switch ($act->_position) {
            case 0:
                $act->_guess_facturable  = '1';
                $act->_guess_association = '1';
                $act->_guess_regle_asso  = 'ED';
                break;
            case 1:
                $act->_guess_facturable  = '1';
                $act->_guess_association = '2';
                $act->_guess_regle_asso  = 'ED';
                break;
            default:
                $act->_guess_facturable  = '0';
                $act->_guess_association = '';
                $act->_guess_regle_asso  = 'ED';
        }
    }

    /**
     * ### Actes de scanographie ###
     * * Nombre d'actes : 2 ou 3
     * * Cas d'utilisation : Pour les __actes de scanographie, lorsque l'examen porte sur plusieurs régions
     * anatomiques__, un seul acte doit être tarifé, sauf dans le cas ou l'examen effectué est conjoint des régions
     * anatomiques suivantes : membres et tête, membres et thorax, membres et abdomen, tête et abdomen, thorax et
     * abdomen complet, tête et thorax, quel que soit le nombres de coupes nécéssaires, avec ou sans injection de
     * produit de contraste.
     *
     * Dans ce cas, deux actes ou plus peuvent être tarifés à taux plein. Deux forfaits techniques peuvent alors être
     * facturés, le second avec une minaration de 85% de son tarfi.
     *
     * Quand un libellé décrit l'examen conjoint de plusieurs régions anatomiques, il ne peut être tarifé avec aucun
     * autre acte de scanographie. Deux forfaits techniques peuvent alors être tarifés, le second avec une minoration
     * de 85% de son tarfi.
     *
     * L'acte de guidage scanographique ne peut être tarfié qu'avec les actes dont le libellé précise qu'ils
     * nécessitent un guidage scanoraphique. Dans ce cas, deux acte au plus peuvent être tarifés à taux plein.
     *
     * @return bool
     */
    protected function checkRuleEE(): bool
    {
        $chapters_scano = [
            '01.01.05.',
            '04.01.05.',
            '05.01.02.',
            '06.01.04.',
            '07.01.05.',
            '09.01.04.',
            '11.01.04.',
            '12.01.04.',
            '13.01.02',
            '14.01.03.',
            '16.01.02.',
            '16.02.03.',
            '17.01.03.',
        ];
        $nb_scano       = 0;

        foreach ($this->_ref_actes_ccam_facturables as $_acte_ccam) {
            $chapters = $_acte_ccam->_ref_code_ccam->chapitres;
            if (isset($chapters[2]) && in_array($chapters[2]['rang'], $chapters_scano)) {
                $nb_scano++;
            }
        }

        if (!$nb_scano) {
            return false;
        }

        return true;
    }

    /**
     * Apply the association rule EE to the given act
     *
     * @param CActeCCAM $act The act
     *
     * @return void
     */
    protected function applyRuleEE(CActeCCAM $act): void
    {
        switch ($act->_position) {
            case 0:
            case 1:
            case 2:
                $act->_guess_facturable  = '1';
                $act->_guess_association = '4';
                $act->_guess_regle_asso  = 'EE';
                break;
            default:
                $act->_guess_facturable  = '0';
                $act->_guess_association = '';
                $act->_guess_regle_asso  = 'EE';
        }
    }

    /**
     * Association rule EF
     *
     * @return bool
     */
    protected function checkRuleEF(): bool
    {
        $chapters_remno = [
            '01.01.06.',
            '04.01.06.',
            '05.01.03.',
            '06.01.05.',
            '07.01.06.',
            '11.01.05.',
            '12.01.05.',
            '13.01.03.',
            '14.01.04.',
            '16.01.03.',
            '16.02.04.',
            '17.01.04.',
        ];
        $nb_remno       = 0;
        $guidage_remno  = 0;

        foreach ($this->_ref_actes_ccam_facturables as $_acte_ccam) {
            $chapters = $_acte_ccam->_ref_code_ccam->chapitres;
            if (strpos($_acte_ccam->_ref_code_ccam->libelleLong, 'guidage remnographique') !== false) {
                $guidage_remno++;
            } elseif (isset($chapters[2]) && in_array($chapters[2]['rang'], $chapters_remno)) {
                $nb_remno++;
            }
        }

        if (!$nb_remno && !$guidage_remno) {
            return false;
        }

        $this->_check_rules['EF'] = [
            'nb_remno'      => $nb_remno,
            'guidage_remno' => $guidage_remno,
        ];

        return true;
    }

    /**
     * Apply the association rule EF to the given act
     *
     * @param CActeCCAM $act The act
     *
     * @return void
     */
    protected function applyRuleEF(CActeCCAM $act): void
    {
        if ($this->_check_rules['EF']['guidage_remno'] == 2) {
            switch ($act->_position) {
                case 0:
                    $act->_guess_facturable  = '1';
                    $act->_guess_association = '1';
                    $act->_guess_regle_asso  = 'EF';
                    break;
                case 1:
                    $act->_guess_facturable  = '1';
                    $act->_guess_association = '2';
                    $act->_guess_regle_asso  = 'EF';
                    break;
                default:
                    $act->_guess_facturable  = '0';
                    $act->_guess_association = '';
                    $act->_guess_regle_asso  = 'EF';
            }
        } else {
            switch ($act->_position) {
                case 0:
                    $act->_guess_facturable  = '1';
                    $act->_guess_association = '';
                    $act->_guess_regle_asso  = 'EF';
                    break;
                default:
                    $act->_guess_facturable  = '0';
                    $act->_guess_association = '';
                    $act->_guess_regle_asso  = 'EF';
            }
        }
    }

    /**
     * ### Exception actes de radiologie vasculaire et imagerie conventionnelle ###
     * * Nombre d'actes : 2
     * * Cas d'utilisation : Les __actes du sous paragraphe 19.01.09.02__ (radiologie vasculaire et imagerie
     * conventionnelle) sont associés à taux plein, deux actes au plus peuvent tarifés.
     *
     * @return bool
     */
    protected function checkRuleEG1(): bool
    {
        $cond = 0;
        foreach ($this->_ref_actes_ccam_facturables as $_acte_ccam) {
            $chapters = $_acte_ccam->_ref_code_ccam->chapitres;
            if (isset($chapters[3]) && $chapters[3]['rang'] == '19.01.09.02.') {
                $cond++;
            }
        }

        if ($cond != 2) {
            return false;
        }

        return true;
    }

    /**
     * Apply the association rule EG1 to the given act
     *
     * @param CActeCCAM $act The act
     *
     * @return void
     */
    protected function applyRuleEG1(CActeCCAM $act): void
    {
        $ordered_acts_eg1 = $this->_ordered_acts;
        foreach ($this->_ref_actes_ccam_facturables as $_acte_ccam) {
            if (
                $_acte_ccam->_ref_code_ccam->isSupplement()
                || $_acte_ccam->_ref_code_ccam->isComplement()
                || $this->isComplementaryAct($_acte_ccam)
            ) {
                unset($ordered_acts_eg1[$_acte_ccam->_id]);
                if ($_acte_ccam->_id == $act->_id) {
                    $act->_position = -1;
                }
            }
        }

        if ($act->_position != -1) {
            self::orderActsByTarif($ordered_acts_eg1);
            $act->_position = array_search($act->_id, array_keys($ordered_acts_eg1));
        }

        switch ($act->_position) {
            case -1:
            case 0:
            case 1:
                $act->_guess_facturable  = '1';
                $act->_guess_association = '1';
                $act->_guess_regle_asso  = 'EG1';
                break;
            default:
                $act->_guess_facturable  = '0';
                $act->_guess_association = '';
                $act->_guess_regle_asso  = 'EG1';
        }
    }

    /**
     * ### Exception : actes d'anatomie et de cytologie pathologique ###
     * * Nombre d'actes : 2 ou3
     * * Cas d'utilisation : Les __actes d'anatomie et de cytologie pathologique__ peuvent être associés à
     * taux plein entre eux et/ou à un autre acte, quelque soit le nombre d'acte d'anatomie et de cytologie
     * pathologique.
     *
     * @return bool
     */
    protected function checkRuleEG2(): bool
    {
        $chapters_anapath = [
            '01.01.14.',
            '02.01.10.',
            '04.01.10.',
            '05.01.08.',
            '06.01.11.',
            '07.01.13.',
            '08.01.09.',
            '09.01.07.',
            '10.01.05.',
            '15.01.07.',
            '16.01.06.',
            '16.02.06.',
            '17.02.',
        ];
        $nb_anapath       = 0;
        foreach ($this->_ref_actes_ccam_facturables as $_act) {
            $chapters = $_act->_ref_code_ccam->chapitres;
            if (
                (isset($chapters[2]) && in_array($chapters[2]['rang'], $chapters_anapath))
                || (isset($chapters[1]) && in_array($chapters[1]['rang'], $chapters_anapath))
            ) {
                $nb_anapath++;
            }
        }

        if (!$nb_anapath) {
            return false;
        }

        $this->_check_rules['EG2'] = [
            'nb_anapath' => $nb_anapath,
        ];

        return true;
    }

    /**
     * Apply the association rule EG2 to the given act
     *
     * @param CActeCCAM $act The act
     *
     * @return void
     */
    protected function applyRuleEG2(CActeCCAM $act): void
    {
        $ordered_acts_eg2 = $this->_ordered_acts;
        $chapters_anapath = [
            '01.01.14.',
            '02.01.10.',
            '04.01.10.',
            '05.01.08.',
            '06.01.11.',
            '07.01.13.',
            '08.01.09.',
            '09.01.07.',
            '10.01.05.',
            '15.01.07.',
            '16.01.06.',
            '16.02.06.',
            '17.02.',
        ];

        foreach ($this->_ref_actes_ccam_facturables as $_act) {
            $chapters = $_act->_ref_code_ccam->chapitres;
            if (
                (isset($chapters[2]) && in_array($chapters[2]['rang'], $chapters_anapath))
                || (isset($chapters[1]) && in_array($chapters[1]['rang'], $chapters_anapath))
            ) {
                unset($ordered_acts_eg2[$_act->_id]);
                if ($_act->_id == $act->_id) {
                    $act->_position = -1;
                }
            }
        }
        if ($act->_position != -1) {
            $ordered_acts_eg2 = self::orderActsByTarif($ordered_acts_eg2);
            $act->_position   = array_search($act->_id, array_keys($ordered_acts_eg2));
        }

        $nb_anapath = $this->_check_rules['EG2']['nb_anapath'];
        if ($nb_anapath == 2 || ($nb_anapath == 1 && count($ordered_acts_eg2) == 1)) {
            $act->_guess_facturable  = '1';
            $act->_guess_association = '4';
            $act->_guess_regle_asso  = 'EG2';
        } else {
            switch ($act->_position) {
                case -1:
                case 0:
                    $act->_guess_facturable  = '1';
                    $act->_guess_association = '1';
                    $act->_guess_regle_asso  = 'EG2';
                    break;
                case 1:
                    $act->_guess_facturable  = '1';
                    $act->_guess_association = '2';
                    $act->_guess_regle_asso  = 'EG2';
                    break;
                default:
                    $act->_guess_facturable  = '0';
                    $act->_guess_association = '';
                    $act->_guess_regle_asso  = 'EG2';
            }
        }
    }

    /**
     * ### Exception : actes d'électromyographie, de mesure de vitesse de conduction, d'études des lances et des
     * réflexes ###
     * * Nombre d'actes : 2 ou 3
     * * Cas d'utilisation : Les __actes d'électromyographie, de mesure de vitesse de conduction, d'études des lances
     * et des réflexes__
     * (figurants aux paragraphes 01.01.01.01, 01.01.01.02, 01.01.01.03 de la CCAM) peuvent être associés à taux plein
     * entre eux ou à un autre acte, quelque soit le nombre d'actes
     *
     * @return bool
     */
    protected function checkRuleEG3(): bool
    {
        $nb_electromyo = 0;
        foreach ($this->_ref_actes_ccam_facturables as $_acte_ccam) {
            $chapters = $_acte_ccam->_ref_code_ccam->chapitres;
            if (
                isset($chapters[3]) && in_array($chapters[3]['rang'], ['01.01.01.01.', '01.01.01.02.', '01.01.01.03.'])
            ) {
                $nb_electromyo++;
            }
        }

        if (!$nb_electromyo) {
            return false;
        }

        $this->_check_rules['EG3'] = ['nb_electromyo' => $nb_electromyo];

        return true;
    }

    /**
     * Apply the association rule EG3 to the given act
     *
     * @param CActeCCAM $act The act
     *
     * @return void
     */
    protected function applyRuleEG3(CActeCCAM $act): void
    {
        $ordered_acts_eg3 = $this->_ordered_acts;
        foreach ($this->_ref_actes_ccam_facturables as $_acte_ccam) {
            $chapters = $_acte_ccam->_ref_code_ccam->chapitres;
            if (
                isset($chapters[3]) && in_array($chapters[3]['rang'], ['01.01.01.01.', '01.01.01.02.', '01.01.01.03.'])
            ) {
                unset($ordered_acts_eg3[$_acte_ccam->_id]);
                if ($_acte_ccam->_id == $act->_id) {
                    $act->_position = -1;
                }
            } elseif (isset($chapters[1]) && $chapters[1]['rang'] == '19.02.') {
                unset($ordered_acts_eg3[$_acte_ccam->_id]);
                if ($_acte_ccam->_id == $act->_id) {
                    $act->_position = -1;
                }
            }
        }
        if ($act->_position != -1) {
            $ordered_acts_eg3 = self::orderActsByTarif($ordered_acts_eg3);
            $act->_position   = array_search($act->_id, array_keys($ordered_acts_eg3));
        }

        $nb_electromyo = $this->_check_rules['EG3']['nb_electromyo'];

        if ($nb_electromyo == 2 || ($nb_electromyo == 1 && count($ordered_acts_eg3) == 1)) {
            $act->_guess_facturable  = '1';
            $act->_guess_association = '4';
            $act->_guess_regle_asso  = 'EG3';
        } else {
            switch ($act->_position) {
                case -1:
                case 0:
                    $act->_guess_facturable  = '1';
                    $act->_guess_association = '1';
                    $act->_guess_regle_asso  = 'EG3';
                    break;
                case 1:
                    $act->_guess_facturable  = '1';
                    $act->_guess_association = '2';
                    $act->_guess_regle_asso  = 'EG3';
                    break;
                default:
                    $act->_guess_facturable  = '0';
                    $act->_guess_association = '';
                    $act->_guess_regle_asso  = 'EG3';
            }
        }
    }

    /**
     * ### Exception : actes d'irradiation en radiothérapie ###
     * * Nombre d'actes : 2 ou 3
     * * Cas d'utilisation : Les __actes d'irradiation en radiothérapie__, ainsi que les suppléments autorisés avec ces
     * actes, peuvent être associés à taux plein, quel que soit le nombre d'actes.
     *
     * @return bool
     */
    protected function checkRuleEG4(): bool
    {
        $irrad = 0;
        $supp  = 0;
        foreach ($this->_ref_actes_ccam_facturables as $_acte_ccam) {
            $chapters = $_acte_ccam->_ref_code_ccam->chapitres;
            if (isset($chapters[2]) && in_array($chapters[2]['rang'], ['17.04.02.', '19.01.10.'])) {
                $irrad++;
            } elseif (
                $_acte_ccam->_ref_code_ccam->isSupplement()
                || $_acte_ccam->_ref_code_ccam->isComplement()
                || $_acte_ccam->_ref_code_ccam->isRadioCardioInterv()
                || $this->isComplementaryAct($_acte_ccam)
            ) {
                $supp++;
            }
        }
        if (!$irrad || (($irrad + $supp) != count($this->_ref_actes_ccam_facturables))) {
            return false;
        }

        return true;
    }

    /**
     * Apply the association rule EG4 to the given act
     *
     * @param CActeCCAM $act The act
     *
     * @return void
     */
    protected function applyRuleEG4(CActeCCAM $act): void
    {
        $act->_guess_facturable  = '1';
        $act->_guess_association = '4';
        $act->_guess_regle_asso  = 'EG4';
    }

    /**
     * ### Exception : actes de médecin nucléaire ###
     * * Nombre d'actes : 2
     * * Cas d'utilisation : Les __actes de médecin nucléaire__ sont associés à taux plein, deux actes au plus peuvent
     * être tarfiés. Il en est de même pour un acte de médecine nucléaire associé à un autre acte.
     *
     * @return bool
     */
    protected function checkRuleEG5(): bool
    {
        /* @todo Identifier les actes de médecin nucélaire */
        $cond = 0;

        if (!$cond) {
            return false;
        }

        return true;
    }

    /**
     * Apply the association rule EG5 to the given act
     *
     * @param CActeCCAM $act The act
     *
     * @return void
     */
    protected function applyRuleEG5(CActeCCAM $act): void
    {
        switch ($act->_position) {
            case 0:
            case 1:
                $act->_guess_facturable  = '1';
                $act->_guess_association = '4';
                $act->_guess_regle_asso  = 'EG5';
                break;
            default:
                $act->_guess_facturable  = '0';
                $act->_guess_association = '';
                $act->_guess_regle_asso  = 'EG5';
        }
    }

    /**
     * ### Exception : forfait de cardiologie, de réanimation, actes de surveillance post-opératoire, actes
     * d'acocuchements ###
     * * Nombre d'actes : 2
     * * Cas d'utilisation : Les __forfait de cardilogie, de réanimation, actes de surveillance post-opératoire (d'un
     * patient de chirurgie cardiaque avec CEC), actes d'acocuchements__ peuvent être associés à taux plein à un seul
     * des actes introduits par la note "facturation : éventuellement en supplément".
     *
     * @return bool
     */
    protected function checkRuleEG6(): bool
    {
        /* Forfaits de cardiologie : YYYY001, YYYY002 (19.01.02)
         * Forfaits de réanimation : YYYY015, YYYY020 (19.01.11)
         * Surveillance post-op chirurgie cardiaque avec CEC : YYYY108, YYYY118
         * Actes d'accouchements : 09.03.03
         */
        $cond = 0;
        foreach ($this->_ref_actes_ccam_facturables as $_act) {
            $chapters = $_act->_ref_code_ccam->chapitres;
            if (
                in_array($_act->code_acte, ['YYYY001', 'YYYY002', 'YYYY015', 'YYYY020', 'YYYY108', 'YYYY118'])
                || (isset($chapters[2]) && $chapters[2]['rang'] = '09.03.03')
            ) {
                $cond++;
            }
        }
        if ($cond) {
            return true;
        }

        return false;
    }

    /**
     * Apply the association rule EG6 to the given act
     *
     * @param CActeCCAM $act The act
     *
     * @return void
     */
    protected function applyRuleEG6(CActeCCAM $act): void
    {
        $act->_guess_facturable  = '1';
        $act->_guess_association = '4';
        $act->_guess_regle_asso  = 'EG6';
    }

    /**
     * ### Exception : actes bucco-dentaires ###
     * * Nombre d'actes : 2 ou 3
     * * Cas d'utilisation : Les __actes bucco-dentaires__, y comprit les suppléments autorisés avec ces actes, peuvent
     * être associés à taux plein ente eux ou à eux-même ou à un autre acte, quel que soit le nombre d'actes
     * bucco-dentaires.
     *
     * @return bool
     */
    protected function checkRuleEG7(): bool
    {
        $chapters_dentaire = [
            '07.01.04.01.',
            '07.01.08.01.',
            '07.02.02.01.',
            '07.02.02.03.',
            '07.02.02.04.',
            '07.02.02.05.',
            '07.02.02.06.',
            '07.02.02.08.',
            '07.02.02.09.',
            '07.02.02.10.',
            '07.02.02.11.',
            '07.02.02.12.',
            '07.02.02.15.',
            '07.02.03.',
            '07.02.05.',
            '07.02.06.10.',
            '11.02.05.02.',
            '11.02.05.03.',
            '11.02.05.04.',
        ];
        $exclude_codes     = ['HJQD001'];
        $includes_codes    = [
            'LBGA280',
            'LBGA441',
            'LBGA354',
            'LBGA049',
            'LBGA004',
            'LBGA003',
            'LBGA002',
            'LBGA006',
            'LBGA007',
            'LBGA008',
            'LBGA009',
            'LBGA139',
            'LBGA052',
            'HBLD057',
            'HBLD078',
            'HBLD056',
            'LBGA168',
            'HBLD084',
            'HBLD084',
            'HBMP001',
            'LBLD014',
        ];

        $nb_bucco_dentaires = 0;
        $nb_supplements     = 0;
        foreach ($this->_ref_actes_ccam_facturables as $_act) {
            $chapters = $_act->_ref_code_ccam->chapitres;
            if (
                !in_array($_act->code_acte, $exclude_codes) && ((isset($chapters[2]) && in_array(
                    $chapters[2]['rang'],
                    $chapters_dentaire
                ))
                || (isset($chapters[3]) && in_array($chapters[3]['rang'], $chapters_dentaire))
                || in_array($_act->code_acte, $includes_codes))
            ) {
                $nb_bucco_dentaires++;
            } elseif (
                $_act->_ref_code_ccam->isSupplement()
                || $_act->_ref_code_ccam->isComplement()
                || $_act->_ref_code_ccam->isRadioCardioInterv()
            ) {
                $nb_supplements++;
            }
        }

        if (
            !$nb_bucco_dentaires
            || ($nb_bucco_dentaires == 1 && $nb_supplements == 1 && count($this->_ref_actes_ccam_facturables) == 2)
        ) {
            return false;
        }

        $this->_check_rules['EG7'] = [
            'nb_bucco_dentaires' => $nb_bucco_dentaires,
        ];

        return true;
    }

    /**
     * Apply the association rule EG7 to the given act
     *
     * @param CActeCCAM $act The act
     *
     * @return void
     */
    protected function applyRuleEG7(CActeCCAM $act): void
    {
        $ordered_acts_eg7  = $this->_ordered_acts;
        $chapters_dentaire = [
            '07.01.04.01.',
            '07.01.08.01.',
            '07.02.02.01.',
            '07.02.02.03.',
            '07.02.02.04.',
            '07.02.02.05.',
            '07.02.02.06.',
            '07.02.02.08.',
            '07.02.02.09.',
            '07.02.02.10.',
            '07.02.02.11.',
            '07.02.02.12.',
            '07.02.02.15.',
            '07.02.03.',
            '07.02.05.',
            '07.02.06.10.',
            '11.02.05.02.',
            '11.02.05.03.',
            '11.02.05.04.',
            '18.02.07.01.',
            '18.02.07.06.',
        ];
        $exclude_codes     = ['HJQD001'];
        $includes_codes    = [
            'LBGA280',
            'LBGA441',
            'LBGA354',
            'LBGA049',
            'LBGA004',
            'LBGA003',
            'LBGA002',
            'LBGA006',
            'LBGA007',
            'LBGA008',
            'LBGA009',
            'LBGA139',
            'LBGA052',
            'HBLD057',
            'HBLD078',
            'HBLD056',
            'LBGA168',
            'HBLD084',
            'HBLD084',
            'HBMP001',
            'LBLD014',
        ];

        foreach ($this->_ref_actes_ccam_facturables as $_act) {
            $chapters = $_act->_ref_code_ccam->chapitres;
            if (
                !in_array($_act->code_acte, $exclude_codes) && ((isset($chapters[2]) && in_array(
                    $chapters[2]['rang'],
                    $chapters_dentaire
                ))
                || (isset($chapters[3]) && in_array($chapters[3]['rang'], $chapters_dentaire))
                || in_array($_act->code_acte, $includes_codes))
            ) {
                unset($ordered_acts_eg7[$_act->_id]);
                if ($_act->_id == $act->_id) {
                    $act->_position = -1;
                }
            } elseif (isset($chapters[1]) && $chapters[1]['rang'] == '19.02.') {
                unset($ordered_acts_eg7[$_act->_id]);
                if ($_act->_id == $act->_id) {
                    $act->_position = -1;
                }
            }
        }

        if ($act->_position != -1) {
            $ordered_acts_eg7 = self::orderActsByTarif($ordered_acts_eg7);
            $act->_position   = array_search($act->_id, array_keys($ordered_acts_eg7));
        }

        if (!count($ordered_acts_eg7) || count($ordered_acts_eg7) == 1) {
            $act->_guess_facturable  = '1';
            $act->_guess_association = '4';
            $act->_guess_regle_asso  = 'EG7';
        } else {
            switch ($act->_position) {
                case -1:
                case 0:
                    $act->_guess_facturable  = '1';
                    $act->_guess_association = '1';
                    $act->_guess_regle_asso  = 'EG7';
                    break;
                case 1:
                    $act->_guess_facturable  = '1';
                    $act->_guess_association = '2';
                    $act->_guess_regle_asso  = 'EG7';
                    break;
                default:
                    $act->_guess_facturable  = '0';
                    $act->_guess_association = '';
                    $act->_guess_regle_asso  = 'EG7';
            }
        }
    }

    /**
     * ### Exception : actes discontinus ###
     * * Nombre d'actes : 2 ou 3
     * * Cas d'utilisation : Actes effectués dans un temps différent et discontinu de la même journée.
     *
     * @return bool
     */
    protected function checkRuleEH(): bool
    {
        $moments         = [];
        $ordered_acts_eh = $this->_ref_actes_ccam_facturables;
        CMbArray::pluckSort($ordered_acts_eh, SORT_ASC, 'execution');

        $first_execution = null;
        foreach ($ordered_acts_eh as $_act) {
            if (!$first_execution) {
                $first_execution = $_act->execution;
                $moments[]       = $first_execution;
            } elseif (CMbDT::minutesRelative($first_execution, $_act->execution) >= 60) {
                $moments[] = $_act->execution;
            }
        }

        return count($moments) > 1;
    }

    /**
     * Apply the association rule EH to the given act
     *
     * @param CActeCCAM $act The act
     *
     * @return void
     */
    protected function applyRuleEH(CActeCCAM $act): void
    {
        $ordered_acts_eh = $this->_ref_actes_ccam_facturables;
        CMbArray::pluckSort($ordered_acts_eh, SORT_ASC, 'execution');
        $moments = [
            /* First moment */
            0 => [],
            /* Second moment */
            1 => [],
        ];

        $first_execution = null;
        foreach ($ordered_acts_eh as $_act) {
            /* Check if act is a supplement or a complement */
            if (
                $_act->_ref_code_ccam->isSupplement() || $_act->_ref_code_ccam->isComplement()
                || $_act->_ref_code_ccam->isRadioCardioInterv() || $this->isComplementaryAct($_act)
            ) {
                if ($_act->_id == $act->_id) {
                    $act->_position = -1;
                    break;
                }
            } elseif (!$first_execution) {
                 /* First act */
                $moments[0][]    = $_act;
                $first_execution = $_act->execution;
            } elseif (CMbDT::minutesRelative($first_execution, $_act->execution) < 60) {
                /* Act executed at the same time as the first one */
                $moments[0][] = $_act;
            } else {
                /* Act executed at a different time than the previous one */
                $moments[1][] = $_act;
            }
        }

        /* Sort moment acts by price and get the position */
        foreach ($moments as $_key => $moment) {
            CMbArray::multiSortByProps($moment, '_tarif_base', SORT_DESC, false);
            foreach ($moment as $_index => $_act) {
                if ($_act->_id == $act->_id) {
                    $act->_position = "$_key.$_index";
                    break 2;
                }
            }
        }

        switch ($act->_position) {
            case -1:
            case '0.0':
                $act->_guess_facturable  = '1';
                $act->_guess_association = '1';
                $act->_guess_regle_asso  = 'EH';
                break;
            case '1.0':
                $act->_guess_facturable  = '1';
                $act->_guess_association = '5';
                $act->_guess_regle_asso  = 'EH';
                break;
            case '0.1':
            case '1.1':
                $act->_guess_facturable  = '1';
                $act->_guess_association = '2';
                $act->_guess_regle_asso  = 'EH';
                break;
            default:
                $act->_guess_facturable  = '0';
                $act->_guess_association = '';
                $act->_guess_regle_asso  = 'EH';
        }
    }

    /**
     * ### Exception : actes de radiologie conventionnelle ###
     * * Nombre d'actes : 2, 3, ou 4
     * * Cas d'utilisation : Les __actes de radiologie conventionnelle__ peuvent être associés entre eux (quel que soit
     * leur nombre), ou à d'autres actes.
     *
     * @return bool
     */
    protected function checkRuleEI(): bool
    {
        $chapters_radio = [
            '01.01.04.',
            '02.01.03.',
            '04.01.04.',
            '05.01.01.',
            '06.01.03.',
            '07.01.04.',
            '08.01.03.',
            '09.01.03.',
            '11.01.03.',
            '12.01.03.',
            '13.01.01.',
            '14.01.02.',
            '15.01.02.',
            '16.02.02.',
            '17.01.02',
        ];
        $nb_radio       = 0;
        foreach ($this->_ref_actes_ccam_facturables as $_act) {
            $chapters = $_act->_ref_code_ccam->chapitres;
            if (isset($chapters[2]) && in_array($chapters[2]['rang'], $chapters_radio)) {
                $nb_radio++;
            }
        }

        if ($nb_radio < 1) {
            return false;
        }

        $this->_check_rules['EI'] = [
            'nb_radio' => $nb_radio,
        ];

        return true;
    }

    /**
     * Apply the association rule EI to the given act
     *
     * @param CActeCCAM $act The act
     *
     * @return void
     */
    protected function applyRuleEI(CActeCCAM $act): void
    {
        $ordered_acts_ei    = $this->_ordered_acts;
        $ordered_acts_radio = [];
        $chapters_radio     = [
            '01.01.04.',
            '02.01.03.',
            '04.01.04.',
            '05.01.01.',
            '06.01.03.',
            '07.01.04.',
            '08.01.03.',
            '09.01.03.',
            '11.01.03.',
            '12.01.03.',
            '13.01.01.',
            '14.01.02.',
            '15.01.02.',
            '17.01.02',
        ];

        $nb_radio_sein = 0;

        foreach ($this->_ref_actes_ccam_facturables as $_act) {
            $chapters = $_act->_ref_code_ccam->chapitres;
            if (isset($chapters[2]) && in_array($chapters[2]['rang'], $chapters_radio)) {
                unset($ordered_acts_ei[$_act->_id]);
                $ordered_acts_radio[$_act->_id] = $_act->getTarifSansAssociationNiCharge();
                if ($_act->_id == $act->_id) {
                    $act->_position = -2;
                }
            } elseif (isset($chapters[1]) && in_array($chapters[1]['rang'], ['19.02.', '18.02.'])) {
                unset($ordered_acts_ei[$_act->_id]);
                if ($_act->_id == $act->_id) {
                    $act->_position = -1;
                }
            } elseif (isset($chapters[2]) && in_array($chapters[2]['rang'], ['16.02.01.', '16.02.02.'])) {
                $nb_radio_sein++;
            }
        }

        if ($act->_position == -2) {
            $ordered_acts_radio = self::orderActsByTarif($ordered_acts_radio);
            $act->_position     = array_search($act->_id, array_keys($ordered_acts_radio));
            $act->_position     = $act->_position > 1 ? 1 : $act->_position;
        } elseif ($act->_position != -1) {
            $ordered_acts_ei = self::orderActsByTarif($ordered_acts_ei);
            $act->_position  = array_search($act->_id, array_keys($ordered_acts_ei));
        }

        if ($nb_radio_sein == 2) {
            $act->_position = array_search($act->_id, array_keys($this->_ordered_acts));
            switch ($act->_position) {
                case 0:
                    $act->_guess_facturable  = '1';
                    $act->_guess_association = '1';
                    $act->_guess_regle_asso  = 'EI';
                    break;
                case 1:
                    $act->_guess_facturable  = '1';
                    $act->_guess_association = '2';
                    $act->_guess_regle_asso  = 'EI';
                    break;
                default:
                    $act->_guess_facturable  = '0';
                    $act->_guess_association = '';
                    $act->_guess_regle_asso  = 'EI';
            }
        } else {
            switch ($act->_position) {
                case -1:
                case 0:
                    $act->_guess_facturable  = '1';
                    $act->_guess_association = '1';
                    $act->_guess_regle_asso  = 'EI';
                    break;
                case 1:
                    $act->_guess_facturable  = '1';
                    $act->_guess_association = '2';
                    $act->_guess_regle_asso  = 'EI';
                    break;
                default:
                    $act->_guess_facturable  = '0';
                    $act->_guess_association = '';
                    $act->_guess_regle_asso  = 'EI';
            }
        }
    }

    /**
     * Retourne la visibilité des dépassements d'honoraires par rapport à l'exécutant des actes,
     * l'utilisateur connecté et la configuration visibilite_depassement
     *
     * @param CMediusers|int $executant L'executant des actes (mediuser object or id)
     *
     * @return bool
     */
    public static function getVisibiliteDepassement($executant): bool
    {
        if (!is_object($executant)) {
            $executant = CMediusers::get($executant);
        }

        $user = CMediusers::get();
        $user->loadRefFunction();
        $secondary_functions = $user->loadRefsSecondaryFunctions();

        $visibility = false;

        if ($user->isAdmin() || $user->isPMSI()) {
            $visibility = true;
        } else {
            switch (CAppUI::gconf('dPsalleOp COperation visibilite_depassement')) {
                case 'practitioners':
                    $visibility = $user->isPraticien();
                    break;
                case 'functions':
                    $visibility = $user->function_id == $executant->function_id
                        || array_key_exists($executant->function_id, $secondary_functions);
                    break;
                case 'functions_and_practitioners':
                    $visibility = $user->function_id == $executant->function_id || $user->isPraticien()
                        || array_key_exists($executant->function_id, $secondary_functions);
                    break;
                case 'executant':
                    $visibility = $user->_id == $executant->_id;
                    break;
                case 'everyone':
                default:
                    $visibility = true;
            }
        }

        return $visibility;
    }
}
