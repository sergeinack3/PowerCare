<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\Handlers\Events\ObjectHandlerEvent;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CTarif;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\SalleOp\CActeCCAM;

/**
 * Description
 */
class CDevisCodage extends CCodable
{
    /**
     * @var integer Primary key
     */
    public $devis_codage_id;

    /**
     * @var string The class of the codable object linked to the devis
     */
    public $codable_class;

    /**
     * @var integer The id of the codable object linked to the devis
     */
    public $codable_id;

    /**
     * @var integer The id of the patient
     */
    public $patient_id;

    /**
     * @var integer The id of the responsible practitioner
     */
    public $praticien_id;

    /**
     * @var string The date of the creation of the devis
     */
    public $creation_date;

    /**
     * @var string The date of the event
     */
    public $date;

    /**
     * @var string The type of event, Consultation or Operation
     */
    public $event_type;

    /**
     * @var string A libelle for the devis
     */
    public $libelle;

    /**
     * @var string a comment on the devis
     */
    public $comment;

    /**
     * @var float The amount of the total price equal to the sum of the base fare of the acts
     */
    public $base;

    /**
     * @var float The amount of the total price above the base fare
     */
    public $dh;

    /**
     * @var float The amount of the total price on which the tax rate is applied
     */
    public $ht;

    /**
     * @var float The tax rate applied to the ht
     */
    public $tax_rate;

    /**
     * @var float
     */
    public $_ttc;

    /**
     * @var float The total price
     */
    public $_total;

    /**
     * @var CCodable The codable object
     */
    public $_ref_codable;

    /**
     * @var string The formfield, for compability with other codable
     */
    public $_date;

    /**
     * @var bool
     */
    public $_generate_pdf;

    /**
     * Initialize the class specifications
     *
     * @return CMbObjectSpec
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = "devis_codage";
        $spec->key   = "devis_codage_id";

        return $spec;
    }

    /**
     * Get the properties of our class as strings
     *
     * @return array
     */
    public function getProps(): array
    {
        $props = parent::getProps();

        $props['codable_class']      = 'str notNull class';
        $props['codable_id']         = 'ref notNull class|CCodable meta|codable_class back|devis_codage cascade';
        $props['patient_id']         = 'ref notNull class|CPatient back|devis';
        $props['praticien_id']       = 'ref notNull class|CMediusers back|devis';
        $props['consult_related_id'] .= ' back|devis';
        $props['creation_date']      = 'dateTime notNull';
        $props['date']               = 'date';
        $props['event_type']         = 'enum list|CConsultation|COperation default|COperation';
        $props['libelle']            = 'str';
        $props['comment']            = 'text helped';
        $props['base']               = 'currency min|0 show|0';
        $props['dh']                 = 'currency min|0 show|0';
        $props['ht']                 = 'currency min|0 show|0';
        $props['tax_rate']           = 'float';
        $props['_ttc']               = 'currency min|0 show|0';
        $props['_total']             = 'currency min|0 show|0';
        $props['_generate_pdf']      = 'bool default|0';

        return $props;
    }

    /**
     * @see parent::store()
     */
    public function store(): ?string
    {
        if ($msg = parent::store()) {
            return $msg;
        }
        if ($this->_bind_tarif && $this->_id) {
            $this->bindTarif();
        }

        $this->loadRefsCodagesCCAM();
        if (
            CAppUI::gconf('dPcabinet CDevisCodage codage_interv_anesth') && $this->codable_class == 'CConsultation'
            && $this->codes_ccam == '' && !count($this->_ref_codages_ccam)
        ) {
            /** @var CConsultation $consult */
            $consult        = $this->loadRefCodable();
            $consult_anesth = $consult->loadRefConsultAnesth();
            if ($consult->_ref_consult_anesth->_id) {
                $op = $consult_anesth->loadRefOperation();
                if ($op->_id) {
                    if ($msg = $this->setFromObject($op, '4')) {
                        return $msg;
                    }
                }
            }
        }

        if ($this->_id && $this->_generate_pdf) {
            $this->loadRefPatient();
            $this->loadRefPraticien();
            $this->getActeExecution();
            $this->countActes();
            $this->loadRefsActes();
            CDevisCodageToPdfFile::generateFileFromDevisCodage($this);
        }

        return null;
    }

    /**
     * @see parent::bindTarif()
     */
    public function bindTarif(): ?string
    {
        $this->_bind_tarif = false;

        // Chargement du tarif
        $tarif = new CTarif();
        $tarif->load($this->_tarif_id);

        if (!$this->tarif) {
            $this->tarif = $tarif->description;
        }
        $this->store();
        // Mise à jour de codes CCAM prévus, sans information serialisée complémentaire
        foreach ($tarif->_codes_ccam as $_code_ccam) {
            $this->_codes_ccam[] = substr($_code_ccam, 0, 7);
        }
        $this->updateCCAMPlainField();
        if (!$this->exec_tarif) {
            $this->exec_tarif      = $this->date . " " . CMbDT::format(CMbDT::dateTime(), "%H:%M:%S");
            $this->_acte_execution = $this->exec_tarif;
        }

        if ($msg = $this->store()) {
            ;

            return $msg;
        }

        $codable = CMbObject::loadFromGuid("$this->codable_class-$this->codable_id");
        $codable->loadRefPraticien();
        $chir_id = $codable->_ref_praticien->_id;

        $this->_acte_execution = $this->exec_tarif;

        // Precodage des actes NGAP avec information sérialisée complète
        $this->_tokens_ngap = $tarif->codes_ngap;
        if ($msg = $this->precodeActe("_tokens_ngap", "CActeNGAP", $chir_id)) {
            return $msg;
        }

        $this->codes_ccam = $tarif->codes_ccam;
        // Precodage des actes CCAM avec information sérialisée complète
        if ($msg = $this->precodeCCAM($chir_id)) {
            return $msg;
        }
        $this->updateCCAMPlainField();

        if (CModule::getActive('lpp') && CAppUI::gconf('lpp General cotation_lpp')) {
            /* Precodage des actes LPP avec information sérialisée complète */
            $this->_tokens_lpp = $tarif->codes_lpp;
            if ($msg = $this->precodeActe('_tokens_lpp', 'CActeLPP', $chir_id)) {
                return $msg;
            }
        }

        $this->doUpdateMontants();

        return null;
    }

    /**
     * Copy the CCAM codage and acts from the given object
     *
     * @param CCodable $object   The object
     * @param string   $activity The activity to copy, default all
     *
     * @return string|bool
     */
    protected function setFromObject(CCodable $object, string $activity = null): ?string
    {
        $this->codes_ccam = $object->codes_ccam;

        if ($msg = $this->store()) {
            return $msg;
        }

        $object->loadRefsCodagesCCAM();
        if (array_key_exists($this->praticien_id, $object->_ref_codages_ccam)) {
            /** @var CCodageCCAM $_model_codage */
            foreach ($object->_ref_codages_ccam[$this->praticien_id] as $_model_codage) {
                if (
                    !$activity || ($_model_codage->activite_anesth == '1' && $activity == '4')
                    || ($_model_codage->activite_anesth == '0' && $activity != '4')
                ) {
                    $_codage                   = new CCodageCCAM();
                    $_codage->codable_class    = $this->_class;
                    $_codage->codable_id       = $this->_id;
                    $_codage->association_mode = $_model_codage->association_mode;
                    $_codage->association_rule = $_model_codage->association_rule;
                    $_codage->praticien_id     = $this->praticien_id;
                    $_codage->activite_anesth  = $_model_codage->activite_anesth;
                    $_codage->date             = $this->date;

                    if ($msg = $_codage->store()) {
                        return $msg;
                    }

                    $_model_codage->loadActesCCAM();

                    foreach ($_model_codage->_ref_actes_ccam as $_model_act) {
                        $_act                      = new CActeCCAM();
                        $_act->object_id           = $this->_id;
                        $_act->object_class        = $this->_class;
                        $_act->execution           = $this->getActeExecution();
                        $_act->code_acte           = $_model_act->code_acte;
                        $_act->code_activite       = $_model_act->code_activite;
                        $_act->code_phase          = $_model_act->code_phase;
                        $_act->modificateurs       = $_model_act->modificateurs;
                        $_act->code_association    = $_model_act->code_association;
                        $_act->montant_base        = $_model_act->montant_base;
                        $_act->montant_depassement = $_model_act->montant_depassement;
                        $_act->executant_id        = $_model_act->executant_id;
                        $_act->rembourse           = $_model_act->rembourse;
                        $_act->facturable          = $_model_act->facturable;
                        $_act->motif_depassement   = $_model_act->motif_depassement;

                        $this->loadRefPraticien();
                        if ($this->_ref_praticien->isAnesth()) {
                            $_act->extension_documentaire = $_model_act->extension_documentaire;
                        }

                        if ($msg = $_act->store()) {
                            return $msg;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * @see parent::delete()
     */
    public function delete(): ?string
    {
        $this->loadRefsActes();

        foreach ($this->_ref_actes as $act) {
            if ($msg = $act->delete()) {
                return $msg;
            }
        }

        $this->loadRefsCodagesCCAM();

        foreach ($this->_ref_codages_ccam as $_codage_by_prat) {
            /** @var CCodageCCAM $_codage */
            foreach ($_codage_by_prat as $_codage) {
                if ($msg = $_codage->delete()) {
                    return $msg;
                }
            }
        }

        return parent::delete();
    }

    /**
     * @see parent::updateFormFields()
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        $this->_ttc          = round($this->ht * $this->tax_rate / 100, 2);
        $this->_total        = round($this->base + $this->dh + $this->_ttc, 2);
        $this->_praticien_id = $this->praticien_id;
        $this->getActeExecution();
    }

    /**
     * Register all the template properties (distant and genuine)
     *
     * @param CTemplateManager $template
     *
     * @return void
     *
     * @see parent::fillTemplate()
     */
    public function fillTemplate(&$template)
    {
        $this->updateFormFields();
        $this->loadRefPatient();
        $this->loadRefPraticien();

        $this->_ref_patient->fillTemplate($template);
        $this->_ref_praticien->fillTemplate($template);
        $this->fillLimitedTemplate($template);
    }

    /**
     * Register the object's template properties
     *
     * @param CTemplateManager $template
     *
     * @return void
     *
     * @see parent::fillTemplate()
     */
    public function fillLimitedTemplate(&$template)
    {
        $this->notify(ObjectHandlerEvent::BEFORE_FILL_LIMITED_TEMPLATE(), $template);

        $template->addDateProperty('Devis - Date', $this->date);
        $template->addProperty('Devis - Type d\'événement', CAppUI::tr($this->event_type));
        $template->addProperty('Devis - Libellé', $this->libelle);
        $template->addProperty('Devis - Actes NGAP', $this->printActesNGAP(), '', false);
        $template->addProperty('Devis - Actes CCAM', $this->printActesCCAM(), '', false);
        $template->addProperty('Devis - Frais divers', $this->printFraisDivers(), '', false);
        $template->addProperty('Devis - Tableau Actes NGAP', $this->printTableActesNGAP(), '', false);
        $template->addProperty('Devis - Tableau Actes CCAM', $this->printTableActesCCAM(), '', false);
        $template->addProperty('Devis - Tableau Frais divers', $this->printTableFraisDivers(), '', false);
        $template->addProperty('Devis - Commentaire', $this->comment);
        $template->addProperty('Devis - Base', $this->base);
        $template->addProperty('Devis - Dépassements d\'honoraire', $this->dh);
        $template->addProperty('Devis - Hors taxe', $this->ht);
        $template->addProperty('Devis - TTC', $this->_ttc);
        $template->addProperty('Devis - Total', $this->_total);

        $this->notify(ObjectHandlerEvent::AFTER_FILL_LIMITED_TEMPLATE(), $template);
    }

    /**
     * Update the different amounts (base, dh) from the linked acts
     *
     * @return null|string
     */
    public function doUpdateMontants(): ?string
    {
        $this->base = 0;
        $this->dh   = 0;
        $this->ht   = 0;

        $this->loadRefsActes();
        foreach ($this->_ref_actes as $_acte) {
            switch ($_acte->_class) {
                case "CActeLPP":
                    $this->base += round($_acte->montant_final, 2);
                    $this->dh   += $_acte->montant_depassement;
                    break;
                case "CFraisDivers":
                    $this->ht += $_acte->montant_base + $_acte->montant_depassement;
                    break;
                default:
                    $this->base += $_acte->montant_base;
                    $this->dh   += $_acte->montant_depassement;
                    break;
            }
        }

        return $this->store();
    }

    /**
     * Calcul de la date d'execution de l'acte
     *
     * @return string
     */
    public function getActeExecution(): string
    {
        $this->_acte_execution = $this->date . ' ' . CMbDT::time();

        return $this->_acte_execution;
    }

    /**
     * Load the linked codable object
     *
     * @param bool $cache Use the cache
     *
     * @return CCodable|null
     */
    public function loadRefCodable(bool $cache = true): ?CCodable
    {
        if (!$this->_ref_codable) {
            $this->_ref_codable = $this->loadFwdRef('codable_id', $cache);
        }

        return $this->_ref_codable;
    }

    /**
     * Load the responsible practitioner
     *
     * @param bool $cache Utilisation du cache
     *
     * @return CMediusers
     */
    public function loadRefPraticien(bool $cache = true): ?CMediusers
    {
        if (!$this->_ref_praticien) {
            $this->_ref_praticien = $this->loadFwdRef('praticien_id', $cache);
            $this->_ref_executant = $this->_ref_praticien;
        }

        return $this->_ref_praticien;
    }

    /**
     * Load the linked patient
     *
     * @param bool $cache Use the cache
     *
     * @return CPatient|null
     */
    public function loadRefPatient(bool $cache = true): ?CPatient
    {
        if (!$this->_ref_patient) {
            $this->_ref_patient = $this->loadFwdRef('patient_id', $cache);
        }

        return $this->_ref_patient;
    }

    /**
     * @param string $code_activite The activity code
     *
     * @return integer
     * @see parent::getExecutantId()
     *
     */
    public function getExecutantId(string $code_activite = null): int
    {
        return $this->praticien_id;
    }

    /**
     * Format an html output of the NGAP acts for the documents fields
     *
     * @return string
     */
    public function printActesNGAP(): string
    {
        $this->loadRefsActesNGAP();

        $html = '<table style="font: inherit;">';

        foreach ($this->_ref_actes_ngap as $_acte) {
            $html .= "<tr><td>$_acte->quantite x<strong> $_acte->code</strong></td>";
            $html .= "<td>$_acte->coefficient</td><td>"
                . ($_acte->montant_base + $_acte->montant_depassement) . "</td></tr>";
        }


        return $html . '</table>';
    }

    /**
     * Format an table output of the NGAP acts for the documents fields
     *
     * @return string
     */
    public function printTableActesNGAP(): string
    {
        $this->loadRefsActesNGAP();

        $smarty = new CSmartyDP('modules/dPccam');
        $smarty->assign('devis', $this);

        return preg_replace('`([\\n\\r])`', '', $smarty->fetch('devis/print_actes_ngap.tpl'));
    }

    /**
     * Format an html output of the CCAM acts for the documents fields
     *
     * @return string
     */
    public function printActesCCAM(): string
    {
        $this->loadRefsActesCCAM();

        $html = '<table style="font: inherit;">';

        foreach ($this->_ref_actes_ccam as $_acte) {
            $html .= "<tr><td><strong>$_acte->code_acte</strong></td>
                <td><span class=\"circled\">$_acte->code_activite - $_acte->code_phase</span></td>";
            $html .= '<td>Asso : ' . CAppUI::tr("CActeCCAM.code_association.$_acte->code_association") . '</td>';
            $html .= "<td>$_acte->modificateurs</td><td>$_acte->_tarif</td></tr>";
        }

        return $html . '</table>';
    }

    /**
     * Format an html output of the CCAM acts for the documents fields
     *
     * @return string
     */
    public function printTableActesCCAM(): string
    {
        $this->loadRefsActesCCAM();

        $smarty = new CSmartyDP('modules/dPccam');
        $smarty->assign('devis', $this);

        return preg_replace('`([\\n\\r])`', '', $smarty->fetch('devis/print_actes_ccam.tpl'));
    }

    /**
     * Format an html output of the diverse costs for the documents fields
     *
     * @return string
     */
    public function printFraisDivers(): string
    {
        $this->loadRefsFraisDivers();

        $html = '<table>';

        foreach ($this->_ref_frais_divers as $_frais) {
            $_frais->loadRefType();
            $html .= "<tr><td>$_frais->quantite x <strong>" . $_frais->_ref_type->libelle
                . '(' . $_frais->_ref_type->code . ")</strong></td>";
            $html .= "<td>$_frais->coefficient</td><td>$_frais->_montant</td></tr>";
        }

        return $html . '</table>';
    }

    /**
     * Format an html output of the diverse costs for the documents fields
     *
     * @return string
     */
    public function printTableFraisDivers(): string
    {
        $this->loadRefsFraisDivers();

        foreach ($this->_ref_frais_divers as $_frais) {
            $_frais->loadRefType();
        }

        $smarty = new CSmartyDP('modules/dPccam');
        $smarty->assign('devis', $this);

        return preg_replace('`([\\n\\r])`', '', $smarty->fetch('devis/print_frais_divers.tpl'));
    }
}
