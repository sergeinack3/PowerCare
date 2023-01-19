<?php

/**
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Ccam;

use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Atih\CRUM;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CProtocole;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\SalleOp\CActeCCAM;

/**
 * Description
 */
class CModelCodage extends CCodable
{
    /**
     * @var integer Primary key
     */
    public $model_codage_id;

    public $praticien_id;
    public $anesth_id;
    public $date;
    public $libelle;
    public $objects_guid;

    public $_objects_count = 0;
    public $_ref_objects   = [];
    public $_date;

    /**
     * Initialize the class specifications
     *
     * @return CMbObjectSpec
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec        = parent::getSpec();
        $spec->table = "model_codage";
        $spec->key   = "model_codage_id";

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

        $props['praticien_id']       = 'ref notNull class|CMediusers back|CModelCodage';
        $props['anesth_id']          = 'ref class|CMediusers back|modeles_codage';
        $props['consult_related_id'] .= ' back|model_codage';
        $props['date']               = 'date';
        $props['libelle']            = 'str';
        $props['objects_guid']       = 'text';

        return $props;
    }

    /**
     * @see parent::store
     */
    public function store(): ?string
    {
        /** @var self $old */
        $old = $this->loadOldObject();

        return parent::store();
    }

    /**
     * @see parent::delete()
     */
    public function delete(): ?string
    {
        $this->loadRefsCodagesCCAM();
        $this->loadRefsActesCCAM();

        foreach ($this->_ref_actes_ccam as $_act) {
            $_act->delete();
        }

        foreach ($this->_ref_codages_ccam as $_codage_by_prat) {
            /** @var CCodageCCAM $_codage */
            foreach ($_codage_by_prat as $_codage) {
                $_codage->delete();
            }
        }

        $this->loadRefsActesNGAP();
        foreach ($this->_ref_actes_ngap as $_act) {
            $_act->delete();
        }

        $this->loadRefsActesLPP();
        foreach ($this->_ref_actes_lpp as $_act) {
            $_act->delete();
        }

        return parent::delete();
    }

    /**
     * @see parent::updateFormFields()
     */
    public function updateFormFields(): void
    {
        parent::updateFormFields();

        $this->_praticien_id = $this->praticien_id;
        /* Used in certain templates, notably inc_line_codage_ngap */
        $this->_date = $this->date;
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
     * Load the anesthesist
     *
     * @param bool $cache If true, use the object cache
     *
     * @return CMediusers
     */
    public function loadRefAnesth(bool $cache = true): ?CMediusers
    {
        if (!$this->_ref_anesth) {
            $this->_ref_anesth = $this->loadFwdRef('anesth_id', $cache);
        }

        return $this->_ref_anesth;
    }

    /**
     * @return CPatient
     */
    public function loadRefPatient(bool $cache = true): ?CPatient
    {
        return $this->_ref_patient = new CPatient();
    }

    /**
     * Charge les éléments de codage CCAM
     *
     * @return CCodageCCAM[]
     */
    public function loadRefsCodagesCCAM(): array
    {
        if ($this->_ref_codages_ccam) {
            return $this->_ref_codages_ccam;
        }

        $codages                 = $this->loadBackRefs("codages_ccam");
        $this->_ref_codages_ccam = [];
        foreach ($codages as $_codage) {
            if (!array_key_exists($_codage->praticien_id, $this->_ref_codages_ccam)) {
                $this->_ref_codages_ccam[$_codage->praticien_id] = [];
            }

            $this->_ref_codages_ccam[$_codage->praticien_id][] = $_codage;
        }

        /* Si les objets codages du chirurgien n'existent pas, ils sont créés automatiquement */
        $chir = $this->loadRefPraticien();
        if ($chir->_id && !array_key_exists($chir->_id, $this->_ref_codages_ccam)) {
            $_codage                             = CCodageCCAM::get($this, $chir->_id, 1);
            $this->_ref_codages_ccam[$chir->_id] = [$_codage];

            if ($chir->isAnesth()) {
                $_codage                               = CCodageCCAM::get($this, $chir->_id, 4);
                $this->_ref_codages_ccam[$chir->_id][] = $_codage;
            }
        }

        return $this->_ref_codages_ccam;
    }

    /**
     * @param integer $code_activite The activity code
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
     * Load the linked objects
     *
     * @return CCodable[]
     */
    public function loadObjects(): array
    {
        if ($this->objects_guid != '') {
            $objects_guid = explode('|', $this->objects_guid);

            foreach ($objects_guid as $_guid) {
                $_object                           = CMbObject::loadFromGuid($_guid);
                $this->_ref_objects[$_object->_id] = $_object;
            }
        }

        return $this->_ref_objects;
    }

    /**
     * Prepare the data for setting the tarif
     *
     * @return array
     */
    public function getTarifData(): array
    {
        $data      = [
            'codes_ngap'   => [],
            'codes_ccam'   => [],
            'codes_lpp'    => [],
        ];
        $secteur_1 = 0;
        $secteur_2 = 0;

        $this->loadRefsActes();

        foreach ($this->_ref_actes_ngap as $_acte) {
            $data['codes_ngap'][] = $_acte->makeFullCode();
            $secteur_1            += $_acte->montant_base;
            $secteur_2            += $_acte->montant_depassement;
        }

        foreach ($this->_ref_actes_ccam as $_acte) {
            $_acte->updateMontantBase();
            $data['codes_ccam'][] = $_acte->makeFullCode();
            $secteur_1            += $_acte->montant_base;
            $secteur_2            += $_acte->montant_depassement;
        }

        foreach ($this->_ref_actes_lpp as $_acte) {
            $data['codes_lpp'][] = $_acte->makeFullCode();
            $secteur_1           += $_acte->montant_final;
            $secteur_2           += $_acte->montant_depassement;
        }

        $data['secteur1'] = $secteur_1;
        $data['secteur2'] = $secteur_2;

        return $data;
    }

    /**
     * Set the model codage from the given object
     *
     * @param CCodable $object       The object
     * @param integer  $protocole_id The protocole id
     *
     * @return void
     */
    public function setFromObject(CCodable $object, int $protocole_id = null): void
    {
        $object->loadRefsCodagesCCAM();

        $this->date = CMbDT::date();

        if ($protocole_id) {
            $protocole = new CProtocole();
            $protocole->load($protocole_id);
            $this->codes_ccam = $protocole->codes_ccam;

            $msg = $this->store();
            $this->loadRefPraticien();

            $_subject_codage                  = new CCodageCCAM();
            $_subject_codage->codable_class   = 'CModelCodage';
            $_subject_codage->codable_id      = $this->_id;
            $_subject_codage->praticien_id    = $this->praticien_id;
            $_subject_codage->activite_anesth = '0';
            $_subject_codage->date            = CMbDT::date();
            $_subject_codage->store();

            if ($this->_ref_praticien->isAnesth()) {
                $_subject_codage                  = new CCodageCCAM();
                $_subject_codage->codable_class   = 'CModelCodage';
                $_subject_codage->codable_id      = $this->_id;
                $_subject_codage->praticien_id    = $this->praticien_id;
                $_subject_codage->activite_anesth = '1';
                $_subject_codage->date            = CMbDT::date();
                $_subject_codage->store();
            }
        } else {
            $this->codes_ccam = $object->codes_ccam;
            $this->store();
            $this->loadRefPraticien();

            if (array_key_exists($this->praticien_id, $object->_ref_codages_ccam)) {
                /** @var CCodageCCAM $_codage */
                foreach ($object->_ref_codages_ccam[$this->praticien_id] as $_codage) {
                    $_subject_codage                   = new CCodageCCAM();
                    $_subject_codage->codable_class    = 'CModelCodage';
                    $_subject_codage->codable_id       = $this->_id;
                    $_subject_codage->association_mode = $_codage->association_mode;
                    $_subject_codage->association_rule = $_codage->association_rule;
                    $_subject_codage->praticien_id     = $this->praticien_id;
                    $_subject_codage->activite_anesth  = $_codage->activite_anesth;
                    $_subject_codage->date             = CMbDT::date();
                    $_subject_codage->store();

                    $_codage->loadActesCCAM();

                    foreach ($_codage->_ref_actes_ccam as $_act) {
                        $_subject_act                      = new CActeCCAM();
                        $_subject_act->object_id           = $this->_id;
                        $_subject_act->object_class        = 'CModelCodage';
                        $_subject_act->execution           = $this->getActeExecution();
                        $_subject_act->code_acte           = $_act->code_acte;
                        $_subject_act->code_activite       = $_act->code_activite;
                        $_subject_act->code_phase          = $_act->code_phase;
                        $_subject_act->modificateurs       = $_act->modificateurs;
                        $_subject_act->code_association    = $_act->code_association;
                        $_subject_act->montant_base        = $_act->montant_base;
                        $_subject_act->montant_depassement = $_act->montant_depassement;
                        $_subject_act->executant_id        = $_act->executant_id;
                        $_subject_act->rembourse           = $_act->rembourse;
                        $_subject_act->facturable          = $_act->facturable;
                        $_subject_act->motif_depassement   = $_act->motif_depassement;

                        if ($this->_ref_praticien->isAnesth()) {
                            $_subject_act->extension_documentaire = $_act->extension_documentaire;
                        }

                        $_subject_act->store();
                    }
                }
            } else {
                $_subject_codage                  = new CCodageCCAM();
                $_subject_codage->codable_class   = 'CModelCodage';
                $_subject_codage->codable_id      = $this->_id;
                $_subject_codage->praticien_id    = $this->praticien_id;
                $_subject_codage->activite_anesth = '0';
                $_subject_codage->date            = CMbDT::date();
                $_subject_codage->store();

                if ($this->_ref_praticien->isAnesth()) {
                    $_subject_codage                  = new CCodageCCAM();
                    $_subject_codage->codable_class   = 'CModelCodage';
                    $_subject_codage->codable_id      = $this->_id;
                    $_subject_codage->praticien_id    = $this->praticien_id;
                    $_subject_codage->activite_anesth = '1';
                    $_subject_codage->date            = CMbDT::date();
                    $_subject_codage->store();
                }
            }
        }
    }

    /**
     * Copy the CCAM codage and acts from the given object for the PMSI
     *
     * @param CCodable $object The object CSejour or COperation
     * @param CRUM     $rum    The object CRUM
     *
     * @return string|bool
     */
    public function setFromObjectPMSI(CCodable $object, CRUM $rum): ?string
    {
        $object->loadRefsCodagesCCAM($rum->date_entree_um, $rum->date_sortie_um);

        if ($msg = $this->store()) {
            return $msg;
        }

        if ($object instanceof COperation) {
            /** @var CCodageCCAM $_codage */
            foreach ($object->_ref_codages_ccam as $prat_id => $_codage_ccam) {
                foreach ($_codage_ccam as $_codage) {
                    $_subject_codage                   = new CCodageCCAM();
                    $_subject_codage->codable_class    = 'CModelCodage';
                    $_subject_codage->codable_id       = $this->_id;
                    $_subject_codage->association_mode = $_codage->association_mode;
                    $_subject_codage->association_rule = $_codage->association_rule;
                    $_subject_codage->praticien_id     = $prat_id;
                    $_subject_codage->activite_anesth  = $_codage->activite_anesth;
                    $_subject_codage->date             = $_codage->date;
                    $_subject_codage->store();

                    $_codage->loadActesCCAM();

                    foreach ($_codage->_ref_actes_ccam as $_act) {
                        $_subject_act                      = new CActeCCAM();
                        $_subject_act->object_id           = $this->_id;
                        $_subject_act->object_class        = 'CModelCodage';
                        $_subject_act->execution           = $_act->execution;
                        $_subject_act->code_acte           = $_act->code_acte;
                        $_subject_act->code_activite       = $_act->code_activite;
                        $_subject_act->code_extension      = $_act->code_extension;
                        $_subject_act->code_phase          = $_act->code_phase;
                        $_subject_act->modificateurs       = $_act->modificateurs;
                        $_subject_act->code_association    = $_act->code_association;
                        $_subject_act->montant_base        = $_act->montant_base;
                        $_subject_act->montant_depassement = $_act->montant_depassement;
                        $_subject_act->executant_id        = $_act->executant_id;
                        $_subject_act->rembourse           = $_act->rembourse;
                        $_subject_act->facturable          = $_act->facturable;
                        $_subject_act->motif_depassement   = $_act->motif_depassement;

                        if ($_act->extension_documentaire) {
                            $_subject_act->extension_documentaire = $_act->extension_documentaire;
                        }
                        if ($msg = $_subject_act->store()) {
                            return $msg;
                        }
                    }
                }
            }
        } else {
            /** @var CCodageCCAM $_codage */
            foreach ($object->_ref_codages_ccam as $prat_id => $_codage_sejour) {
                foreach ($_codage_sejour as $_codages_ccam) {
                    foreach ($_codages_ccam as $_codage) {
                        $_subject_codage                   = new CCodageCCAM();
                        $_subject_codage->codable_class    = 'CModelCodage';
                        $_subject_codage->codable_id       = $this->_id;
                        $_subject_codage->association_mode = $_codage->association_mode;
                        $_subject_codage->association_rule = $_codage->association_rule;
                        $_subject_codage->praticien_id     = $prat_id;
                        $_subject_codage->activite_anesth  = $_codage->activite_anesth;
                        $_subject_codage->date             = $_codage->date;
                        $_subject_codage->store();

                        $_codage->loadActesCCAM();

                        foreach ($_codage->_ref_actes_ccam as $_act) {
                            $_subject_act                      = new CActeCCAM();
                            $_subject_act->object_id           = $this->_id;
                            $_subject_act->object_class        = 'CModelCodage';
                            $_subject_act->execution           = $_act->execution;
                            $_subject_act->code_acte           = $_act->code_acte;
                            $_subject_act->code_activite       = $_act->code_activite;
                            $_subject_act->code_extension      = $_act->code_extension;
                            $_subject_act->code_phase          = $_act->code_phase;
                            $_subject_act->modificateurs       = $_act->modificateurs;
                            $_subject_act->code_association    = $_act->code_association;
                            $_subject_act->montant_base        = $_act->montant_base;
                            $_subject_act->montant_depassement = $_act->montant_depassement;
                            $_subject_act->executant_id        = $_act->executant_id;
                            $_subject_act->rembourse           = $_act->rembourse;
                            $_subject_act->facturable          = $_act->facturable;
                            $_subject_act->motif_depassement   = $_act->motif_depassement;

                            if ($_act->extension_documentaire) {
                                $_subject_act->extension_documentaire = $_act->extension_documentaire;
                            }
                            if ($msg = $_subject_act->store()) {
                                return $msg;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }
}
