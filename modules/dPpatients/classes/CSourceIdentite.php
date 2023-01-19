<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\ConfidentialObjectInterface;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\OpenData\CCommuneFrance;

/**
 * Source d'identité du patient
 */
class CSourceIdentite extends CMbObject implements ConfidentialObjectInterface, IGroupRelated
{
    public const TRAITS_STRICTS_REFERENCE = [
        'nom_naissance'           => 'nom_jeune_fille',
        'prenom_naissance'        => 'prenom',
        'prenoms'                 => 'prenoms',
        'date_naissance'          => 'naissance',
        'sexe'                    => 'sexe',
        'pays_naissance_insee'    => 'pays_naissance_insee',
        '_pays_naissance_insee'   => '_pays_naissance_insee',
        'commune_naissance_insee' => 'commune_naissance_insee',
        'cp_naissance'            => 'cp_naissance',
        '_lieu_naissance'         => 'lieu_naissance',
    ];

    public const ENCRYPT_KEY_NAME = 'CSourceIdentite-key';

    /** @var string */
    public const MODE_OBTENTION_INSI = 'insi';

    /** @var string */
    public const MODE_OBTENTION_MANUEL = 'manuel';

    /** @var string */
    public const MODE_OBTENTION_INTEROP = 'interop';

    /** @var string */
    public const MODE_OBTENTION_IMPORT = 'import';

    /** @var string */
    public const MODE_OBTENTION_CARTE_VITALE = 'carte_vitale';

    /** @var int Primary key */
    public $source_identite_id;

    /** @var int */
    public $patient_id;

    /** @var int */
    public $active;

    /** @var string */
    public $mode_obtention;

    /** @var int */
    public $identity_proof_type_id;

    /** @var string */
    public $date_fin_validite;

    /** @var string */
    public $nom;

    /** @var string */
    public $nom_naissance;

    /** @var string */
    public $prenom_naissance;

    /** @var string */
    public $prenoms;

    /** @var string */
    public $prenom_usuel;

    /** @var string */
    public $date_naissance;

    /** @var string */
    public $date_naissance_corrigee;

    /** @var string */
    public $sexe;

    /** @var string */
    public $pays_naissance_insee;

    /** @var string */
    public $commune_naissance_insee;

    /** @var string */
    public $cp_naissance;

    /** @var string */
    public $debut;

    /** @var string */
    public $fin;

    /** @var int */
    public $validate_identity;

    /** @var string */
    public $_pays_naissance_insee;

    /** @var string */
    public $_lieu_naissance;

    /** @var string */
    public $_oid;

    /** @var string */
    public $_ins_type;

    /** @var bool */
    public $_ins_temporaire;

    /** @var string */
    public $_ins;

    /** @var string */
    public $_previous_ins;

    /** @var string */
    public $_mode_obtention;

    /** @var CPatient */
    public $_ref_patient;

    /** @var CFile */
    public $_ref_justificatif;

    /** @var CPatientINSNIR */
    public $_ref_patient_ins_nir;

    /** @var CPatientINSNIR[] */
    public $_ref_patients_ins_nir;

    /** @var CIdentityProofType */
    public $_ref_identity_proof_type;

    /** @var bool */
    public static $in_manage;

    /** @var bool */
    public static $update_patient_status = true;

    public $_no_synchro_eai    = false;
    public $_generate_IPP      = true;
    public $_force_new_ins_nir = false;

    /**
     * @inheritdoc
     */
    public function getSpec(): CMbObjectSpec
    {
        $spec = parent::getSpec();

        $spec->table = 'source_identite';
        $spec->key   = 'source_identite_id';

        return $spec;
    }

    /**
     * @inheritdoc
     */
    public function getProps(): array
    {
        $props                            = parent::getProps();
        $props['patient_id']              = 'ref class|CPatient notNull back|sources_identite cascade';
        $props['active']                  = 'bool default|0';
        $props['mode_obtention']          = 'enum list|manuel|carte_vitale|insi|code_barre|rfid|import|interop notNull';
        $props['identity_proof_type_id']  = 'ref class|CIdentityProofType back|sources_identite';
        $props['date_fin_validite']       = 'birthDate';
        $props['nom']                     = 'str confidential';
        $props['nom_naissance']           = 'str confidential';
        $props['prenom_naissance']        = 'str';
        $props['prenoms']                 = 'str';
        $props['prenom_usuel']            = 'str';
        $props['date_naissance']          = 'birthDate';
        $props['date_naissance_corrigee'] = 'bool default|0';
        $props['sexe']                    = 'enum list|m|f|i';
        $props['pays_naissance_insee']    = 'numchar length|3';
        $props['commune_naissance_insee'] = 'str length|5';
        $props['cp_naissance']            = 'numchar length|5';
        $props['debut']                   = 'date';
        $props['fin']                     = 'date moreThan|debut';
        $props['validate_identity']       = 'bool default|1';
        $props['_pays_naissance_insee']   = 'str';
        $props['_lieu_naissance']         = 'str';
        $props['_oid']                    = 'str';
        $props['_ins']                    = 'str';
        $props['_ins_type']               = 'str';
        $props['_previous_ins']           = 'str';

        return $props;
    }

    /**
     * @inheritDoc
     */
    public function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view = $this->getFormattedValue('mode_obtention');
    }

    public function getNomPays(): ?string
    {
        return $this->_pays_naissance_insee = $this->pays_naissance_insee ?
            CPaysInsee::getNomFR($this->pays_naissance_insee) : null;
    }

    public function getNomCommune(): ?string
    {
        return $this->_lieu_naissance = $this->commune_naissance_insee ?
            (new CCommuneFrance())->loadByInsee($this->commune_naissance_insee)->commune : null;
    }

    public function updateLieuNaissance(): void
    {
        $this->getNomPays();
        $this->getNomCommune();
    }

    public function loadRefPatient(): CPatient
    {
        return $this->_ref_patient = $this->loadFwdRef('patient_id', true);
    }

    public function loadRefJustificatif(): CFile
    {
        return $this->_ref_justificatif = $this->loadUniqueBackRef('files');
    }

    public function updatePlainFields(): void
    {
        parent::updatePlainFields();

        $anonyme = is_numeric($this->nom);
        if ($this->nom) {
            $this->nom = CPatient::applyModeIdentitoVigilance($this->nom, false, null, $anonyme);
        }

        if ($this->nom_naissance) {
            $this->nom_naissance = CPatient::applyModeIdentitoVigilance($this->nom_naissance, false, null, $anonyme);
        }

        if ($this->prenom_usuel) {
            $this->prenom_usuel = CPatient::applyModeIdentitoVigilance($this->prenom_usuel, true, null, $anonyme);
        }

        if ($this->prenom_naissance) {
            $this->prenom_naissance = CPatient::applyModeIdentitoVigilance(
                $this->prenom_naissance,
                true,
                null,
                $anonyme
            );
        }

        if ($this->prenoms) {
            $prenoms = explode(' ', $this->prenoms);

            foreach ($prenoms as $_key => $_prenom) {
                $prenoms[$_key] = CPatient::applyModeIdentitoVigilance($_prenom, true, null, $anonyme);
            }

            $this->prenoms = implode(' ', $prenoms);
        }
    }

    /**
     * @inheritDoc
     */
    public function store(): ?string
    {
        foreach (self::TRAITS_STRICTS_REFERENCE as $_trait_source => $_trait_patient) {
            $this->completeField($_trait_source);
        }

        $create = !$this->_id;

        if ($create && $this->mode_obtention === static::MODE_OBTENTION_INSI && !$this->_oid && !$this->_ins) {
            return null;
        }

        $this->completeField('patient_id', 'mode_obtention', 'prenoms', 'nom', 'nom_naissance', 'debut');

        // En attendant que le nom de naissance du patient soit correctement renseigné
        if ($this->nom && !$this->nom_naissance) {
            $this->nom_naissance = $this->nom;
        }

        // Ne pas permettre de créer une autre source insi avec le même nir et oid
        if ($create && $this->mode_obtention === static::MODE_OBTENTION_INSI && $this->_oid && $this->_ins) {
            $patient_ins_nir = new CPatientINSNIR();
            $ds              = $this->getDS();

            $where = [
                'ins_nir' => $ds->prepare('= ?', $this->_ins),
                'oid'     => $ds->prepare('= ?', $this->_oid),
                'active'  => "= '1'",
            ];

            $ljoin = [
                'source_identite' => 'source_identite.source_identite_id = patient_ins_nir.source_identite_id',
            ];

            if ($patient_ins_nir->loadObject($where, null, null, $ljoin)) {
                return CAppUI::tr('CSourceIdentite-Cannot create duplicate source with same ins and oid');
            }
        }

        if ($this->_pays_naissance_insee) {
            $this->pays_naissance_insee = CPaysInsee::getPaysNumByNomFR($this->_pays_naissance_insee);
        }

        // Si pas de date de début et que l'on est sur une création la date de début de la source = date de création
        if (!$this->_id && !$this->debut) {
            $this->debut = 'now';
        }

        if ($msg = parent::store()) {
            return $msg;
        }

        if ($this->mode_obtention === static::MODE_OBTENTION_INSI && $this->_oid && $this->_ins) {
            CPatientINSNIR::createUpdate(
                $this->patient_id,
                $this->nom_naissance,
                $this->prenom_naissance,
                $this->date_naissance,
                $this->_ins,
                'INSi',
                $this->_oid,
                $this->_ins_type === 'NIA',
                $this->_id,
                $this->_ins_temporaire,
                $this->_force_new_ins_nir
            );

            if ($this->_previous_ins) {
                foreach (json_decode($this->_previous_ins) as $_previous_ins) {
                    CPatientINSNIR::createUpdate(
                        $this->patient_id,
                        $this->nom_naissance,
                        $this->prenom_naissance,
                        $this->date_naissance,
                        $_previous_ins->ins,
                        'INSi',
                        $_previous_ins->oid,
                        false,
                        $this->_id,
                        null,
                        true
                    );
                }
            }
        }

        // Mise à jour du statut du patient si pas en fusion de patient
        if (self::$update_patient_status && !$this->_forwardRefMerging) {
            $patient                       = $this->loadRefPatient();
            $patient->_ignore_eai_handlers = $this->_ignore_eai_handlers;
            $patient->_no_synchro_eai      = $this->_no_synchro_eai;
            $patient->_generate_IPP        = $this->_generate_IPP;

            return (new PatientStatus($patient))->updateStatus();
        }

        return null;
    }

    public function loadRefPatientINSNIR(): CPatientINSNIR
    {
        return $this->_ref_patient_ins_nir = $this->loadFirstBackRef('patient_ins_nir', 'patient_ins_nir_id');
    }

    public function loadRefsPatientsINSNIR(): array
    {
        $this->_ref_patients_ins_nir = $this->loadBackRefs('patient_ins_nir', 'patient_ins_nir_id');

        $this->loadRefPatientINSNIR();

        if (is_countable($this->_ref_patients_ins_nir)) {
            array_shift($this->_ref_patients_ins_nir);
        }

        return $this->_ref_patients_ins_nir;
    }

    public function loadRefIdentityProofType(): CIdentityProofType
    {
        return $this->_ref_identity_proof_type = $this->loadFwdRef('identity_proof_type_id', true);
    }

    public function mapFields(CPatient $patient): void
    {
        foreach (static::TRAITS_STRICTS_REFERENCE as $_trait_source => $_trait_patient) {
            if ($this->{$_trait_source}) {
                $patient->{$_trait_patient} = $this->{$_trait_source};
            }
        }
    }

    public function mapPatientFields(CPatient $patient = null): ?string
    {
        $this->completeField('patient_id', ...array_keys(static::TRAITS_STRICTS_REFERENCE));

        $patient = $patient ?: $this->loadRefPatient();

        if (($patient->source_identite_id !== $this->_id) || !$this->active) {
            return null;
        }

        $field_changed = false;

        foreach (static::TRAITS_STRICTS_REFERENCE as $_trait_source => $_trait_patient) {
            if ($field_changed && ($patient->{$_trait_patient} !== $this->{$_trait_source})) {
                $field_changed = true;
            }
            if ($this->{$_trait_source}) {
                $patient->{$_trait_patient} = $this->{$_trait_source};
            }
        }

        $patient_ins_nir = $this->loadRefPatientINSNIR();

        if ($patient_ins_nir->_id && ($patient->matricule !== $patient_ins_nir->ins_nir)) {
            $patient->matricule = $patient_ins_nir->ins_nir;
            $field_changed      = true;
        }

        if ($field_changed) {
            return $patient->store();
        }

        return null;
    }

    public static function manageSource(CPatient $patient, ?string $mode_obtention): ?string
    {
        $generate_ipp   = $patient->_generate_IPP;
        $no_synchro_eai = $patient->_no_synchro_eai;

        if (self::$in_manage || !self::$update_patient_status || $patient->_merging) {
            return null;
        }

        self::$in_manage = true;

        $mode_obtention = $patient->_force_manual_source ?
            'manuel' : ($patient->_vitale_lastname ? 'carte_vitale' : $mode_obtention);

        // Recherche de la source d'identité en fonction du mode d'obtention du contexte patient
        $source_identite = new self();

        $source_identite->patient_id     = $patient->_id;
        $source_identite->mode_obtention = $mode_obtention;
        $source_identite->active         = '1';

        // Pas de recherche de source si en mode changement des traits stricts
        if (!$patient->_force_new_manual_source) {
            $source_identite->loadMatchingObject('source_identite_id DESC');
        }

        if (!$source_identite->_id || ($patient->_oid && $patient->_ins)) {
            // Dans le cas de la création d'une nouvelle source insi, on la clone
            if ($source_identite->_id && $patient->_oid && $patient->_ins) {
                // Cas back store du patient avec INS + OID déjà existant
                if (!$patient->_force_new_insi_source) {
                    $patient_insnir = $source_identite->loadRefPatientINSNIR();
                    if (
                        $patient_insnir->_id && $patient_insnir->ins_nir === $patient->_ins
                        && $patient_insnir->oid === $patient->_oid
                    ) {
                        self::$in_manage = false;

                        return null;
                    }
                }

                $old_source = $source_identite;

                $clone_source = new static();
                $clone_source->cloneFrom($source_identite);

                $patient->source_identite_id = '';

                if ($msg = $patient->store()) {
                    self::$in_manage = false;

                    return $msg;
                }

                // Désactication de l'ancienne source d'identité insi
                $old_source->active = '0';
                if ($msg = $old_source->store()) {
                    self::$in_manage = false;

                    return $msg;
                }

                $source_identite                     = $clone_source;
                $source_identite->_force_new_ins_nir = true;
            }

            // Copie des traits stricts
            foreach (static::TRAITS_STRICTS_REFERENCE as $_trait_source => $_trait_patient) {
                $_field_trait_patient = ($patient->_map_source_form_fields ? '_source_' : null) . $_trait_patient;
                if (!$patient->$_field_trait_patient) {
                    continue;
                }
                $source_identite->{$_trait_source} = $patient->{$_field_trait_patient};
            }

            $source_identite->_oid                    = $patient->_oid;
            $source_identite->_ins                    = $patient->_ins;
            $source_identite->_ins_type               = $patient->_ins_type;
            $source_identite->_ins_temporaire         = $patient->_ins_temporaire;
            $source_identite->date_naissance_corrigee = $patient->_source_naissance_corrigee;

            $source_identite->_ignore_eai_handlers = $patient->_ignore_eai_handlers;
            $source_identite->_generate_IPP        = $patient->_generate_IPP;
            $source_identite->_no_synchro_eai      = $patient->_no_synchro_eai;

            // Si les sources d'identité du patient ont déjà été chargées, il faut les retirer du cache
            // sinon la mise à jour du statut va parcourir une collection erronée
            $patient->_ref_sources_identite = null;
            $patient->clearBackRefCache('sources_identite');

            // Création ou mise à jour de la source
            if ($msg = $source_identite->store()) {
                self::$in_manage = false;

                return $msg;
            }

            // Cas source INSi sans INS et OID
            if (!$source_identite->_id) {
                self::$in_manage = false;

                return null;
            }

            $source_identite->_force_new_ins_nir = false;
        }

        // Ajout de la pièce justificative dans une nouvelle source
        // Pas de vérification de la présence de fichier si ajout via un flux
        if ($patient->_identity_proof_type_id) {
            $source_identite2                         = new self();
            $source_identite2->patient_id             = $patient->_id;
            $source_identite2->mode_obtention         = $mode_obtention === self::MODE_OBTENTION_INSI ?
                self::MODE_OBTENTION_INTEROP : self::MODE_OBTENTION_MANUEL;
            $source_identite2->identity_proof_type_id = $patient->_identity_proof_type_id;
            $source_identite2->active                 = 1;

            foreach (self::TRAITS_STRICTS_REFERENCE as $_source_field => $_patient_field) {
                // En création de patient, on reprend les traits stricts depuis l'objet patient
                $_patient_field = ($patient->_map_source_form_fields ? '_source_' : null) . $_patient_field;

                if (!isset($patient->$_patient_field) || !$patient->$_patient_field) {
                    continue;
                }

                $source_identite2->$_source_field = $patient->$_patient_field;
            }

            $source_identite2->date_fin_validite = $patient->_source__date_fin_validite;
            $source_identite2->validate_identity = $patient->_source__validate_identity;

            // Si les sources d'identité du patient ont déjà été chargées, il faut les retirer du cache
            // sinon la mise à jour du statut va parcourir une collection erronée
            $patient->_ref_sources_identite = null;
            $patient->clearBackRefCache('sources_identite');

            if ($msg = $source_identite2->store()) {
                self::$in_manage = false;

                return $msg;
            }

            if ($patient->_copy_file_id || (count($_FILES) && isset($_FILES['formfile']))) {
                $file            = new CFile();
                $file->file_name = 'Paper.jpg';
                [$file->object_class, $file->object_id] = [$source_identite2->_class, $source_identite2->_id];
                $file->author_id = CMediusers::get()->_id;

                $file->updateFormFields();
                $file->fillFields();

                if (count($_FILES) && isset($_FILES['formfile'])) {
                    $file->file_type = $_FILES['formfile']['type'][0];
                    $file->setContent(file_get_contents($_FILES['formfile']['tmp_name'][0]));
                } else {
                    $original_file = CFile::findOrFail($patient->_copy_file_id);
                    $file->setCopyFrom($original_file->_file_path);
                }

                if ($msg = $file->store()) {
                    self::$in_manage = false;

                    return $msg;
                }

                // Pas d'enregistrement du fichier de carte d'identité sur la fiche patient
                if (count($_FILES) && isset($_FILES['formfile'])) {
                    unset($_FILES['formfile']);
                }
            }

            if ($mode_obtention !== self::MODE_OBTENTION_INSI) {
                $source_identite = $source_identite2;
            }
        }

        $patient->_generate_IPP   = $generate_ipp;
        $patient->_no_synchro_eai = $no_synchro_eai;

        // Si le patient n'a pas de source d'identité ou celle que l'on crée est meilleure,
        // alors on l'associe au patient
        if (
            !$patient->source_identite_id
            || (
                $patient->source_identite_id !== $source_identite->_id
                && $source_identite->validate_identity
                && self::isBetterModeObtention(
                    $patient,
                    $source_identite->mode_obtention,
                    $source_identite->identity_proof_type_id
                )
            )
        ) {
            // Changement de source
            $patient->source_identite_id = $source_identite->_id;

            // Copie des traits stricts
            if ($msg = $source_identite->mapPatientFields($patient)) {
                self::$in_manage = false;

                return $msg;
            }

            // Dans le cas d'une source INS, on met à jour le matricule du patient
            if (
                $source_identite->mode_obtention === static::MODE_OBTENTION_INSI
                && $source_identite->_ins
                && !$source_identite->_ins_temporaire
            ) {
                $patient->matricule = $source_identite->_ins;
            }

            if ($msg = $patient->store()) {
                self::$in_manage = false;

                return $msg;
            }
        }

        self::$in_manage = false;

        return null;
    }

    public static function isBetterModeObtention(
        CPatient $patient,
        string $new_mode_obtention,
        string $new_identity_proof_type_id = null
    ): bool {
        $actual_source_identite = $patient->loadRefSourceIdentite();
        $actual_mode_obtention  = $actual_source_identite->getModeObtention();

        if ($patient->_force_manual_source) {
            return true;
        }

        switch ($actual_mode_obtention) {
            case static::MODE_OBTENTION_MANUEL:
            case static::MODE_OBTENTION_IMPORT:
            case static::MODE_OBTENTION_INTEROP:
                if ($new_mode_obtention === static::MODE_OBTENTION_INSI) {
                    return true;
                } elseif (
                    in_array(
                        $new_mode_obtention,
                        [static::MODE_OBTENTION_MANUEL, static::MODE_OBTENTION_IMPORT, static::MODE_OBTENTION_INTEROP]
                    )
                    && $new_identity_proof_type_id
                ) {
                    return true;
                } elseif ($new_mode_obtention === static::MODE_OBTENTION_CARTE_VITALE) {
                    return true;
                }

                return false;

            case static::MODE_OBTENTION_CARTE_VITALE:
                if ($new_mode_obtention === static::MODE_OBTENTION_INSI) {
                    return true;
                } elseif (
                    in_array(
                        $new_mode_obtention,
                        [
                            static::MODE_OBTENTION_MANUEL,
                            static::MODE_OBTENTION_IMPORT,
                            static::MODE_OBTENTION_INTEROP,
                            static::MODE_OBTENTION_CARTE_VITALE,
                        ]
                    )
                    && $new_identity_proof_type_id
                ) {
                    return true;
                }

                return false;

            default:
                return false;
        }
    }

    /**
     * Inactication des sources d'identité lorssque l'identité est douteuse ou fictive
     *
     * @param CPatient $patient
     */
    public static function manageFictifDouteux(CPatient $patient): ?string
    {
        if (!$patient->_fictif_stored && !$patient->_douteux_stored) {
            return null;
        }

        $patient->_fictif_stored = $patient->_douteux_stored = false;

        self::$update_patient_status = false;

        $source_identite_id = null;

        foreach ($patient->loadRefsSourcesIdentite() as $_source_identite) {
            if ($_source_identite->mode_obtention === 'manuel' && !$_source_identite->loadRefJustificatif()->_id) {
                if (!$patient->source_identite_id || $patient->source_identite_id !== $_source_identite->_id) {
                    $source_identite_id = $_source_identite->_id;
                }
                continue;
            }

            $_source_identite->active = 0;
            if ($msg = $_source_identite->store()) {
                self::$update_patient_status = true;

                return $msg;
            }
            if ($_source_identite->mode_obtention === static::MODE_OBTENTION_INSI) {
                if ($msg = $_source_identite->loadRefPatientINSNIR()->delete()) {
                    return $msg;
                }
            }
        }

        if ($source_identite_id) {
            $patient->source_identite_id = $source_identite_id;
            if ($msg = $patient->store()) {
                self::$update_patient_status = true;

                return $msg;
            }
        }

        self::$update_patient_status = true;

        return null;
    }

    public function getKeyName(): string
    {
        return self::ENCRYPT_KEY_NAME;
    }

    /**
     * Retourne le node d'obtention en prenant en compte le flag ins temporaire
     *
     * @return string|null
     */
    public function getModeObtention(): ?string
    {
        switch ($this->mode_obtention) {
            case static::MODE_OBTENTION_INSI:
                $ins_nir = $this->loadRefPatientINSNIR();

                return $this->_mode_obtention =
                    $ins_nir->ins_temporaire ? static::MODE_OBTENTION_MANUEL : static::MODE_OBTENTION_INSI;

            default:
                return $this->_mode_obtention = $this->mode_obtention;
        }
    }

    /**
     * @return CGroups
     */
    public function loadRelGroup(): CGroups
    {
        return $this->loadRefPatient()->loadRefGroup();
    }
}
