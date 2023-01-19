<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\ADT;

use DateTime;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2Segment;
use Ox\Interop\Hl7\Events\CHL7v2Event;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentEVN;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentIAM;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentIN1;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentIN2;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentMRG;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentNK1;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentOBX;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentPD1;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentPID;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentPV1;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentPV2;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentROL;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentZBE;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentZFA;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentZFD;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentZFM;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentZFP;
use Ox\Interop\Hl7\Segments\CHL7v2SegmentZFV;
use Ox\Interop\Ihe\CIHE;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CConstantesMedicales;
use Ox\Mediboard\Patients\CCorrespondant;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Classe CHL7v2EventADT
 * Admit Discharge Transfer
 */
class CHL7v2EventADT extends CHL7v2Event implements CHL7EventADT
{
    /** @var string */
    public $event_type = "ADT";
    /** @var string */
    public $profil = null;

    /**
     * Construct
     *
     * @param string $i18n i18n
     *
     * @return void
     */
    public function __construct($i18n = null)
    {
        parent::__construct($i18n);

        $this->profil    = $this->profil ?: ($i18n ? "PAM_$i18n" : "PAM");
        $this->msg_codes = [
            [
                $this->event_type,
                $this->code,
                $this->struct_code ? "{$this->event_type}_{$this->struct_code}" : null,
            ],
        ];

        $this->transaction = CIHE::getPAMTransaction($this->code, $i18n);
    }

    /**
     * Build event
     *
     * @param CMbObject $object Object
     *
     * @return void
     * @throws CHL7v2Exception
     * @see parent::build()
     *
     */
    public function build($object)
    {
        parent::build($object);

        // Message Header
        $this->addMSH();

        // Event Type
        $this->addEVN($this->getEVNPlannedDateTime($object), $this->getEVNOccuredDateTime($object));
    }

    /**
     * MSH - Represents an HL7 MSH message segment (Message Header)
     *
     * @return void
     * @throws CHL7v2Exception
     */
    public function addMSH(): void
    {
        $MSH = CHL7v2Segment::create("MSH", $this->message);
        $MSH->build($this);
    }

    /**
     * Represents an HL7 EVN message segment (Event Type)
     *
     * @param string $planned_datetime event planned datetime
     * @param string $occured_datetime event occured datetime
     *
     * @return void
     */
    public function addEVN(?string $planned_datetime = null, ?string $occured_datetime = null): void
    {
        /** @var CHL7v2SegmentEVN $EVN */
        $EVN                   = CHL7v2Segment::create("EVN", $this->message);
        $EVN->planned_datetime = $planned_datetime;
        $EVN->occured_datetime = $occured_datetime;
        $EVN->build($this);
    }

    /**
     * Get event planned datetime
     *
     * @param CMbObject $object Object to use
     *
     * @return DateTime Event planned
     */
    public function getEVNPlannedDateTime(CMbObject $object)
    {
    }

    /**
     * Get event planned datetime
     *
     * @param CMbObject $object Object to use
     *
     * @return DateTime Event occured
     */
    public function getEVNOccuredDateTime(CMbObject $object)
    {
    }

    /**
     * Represents an HL7 PID message segment (Patient Identification)
     *
     * @param CPatient $patient Patient
     * @param CSejour  $sejour  Admit
     *
     * @return void
     */
    public function addPID(CPatient $patient, CSejour $sejour = null): void
    {
        $segment_name = $this->_is_i18n ? "PID_FR" : "PID";

        /** @var CHL7v2SegmentPID $PID */
        $PID          = CHL7v2Segment::create($segment_name, $this->message);
        $PID->patient = $patient;
        $PID->sejour  = $sejour;
        $PID->set_id  = 1;
        $PID->build($this);
    }

    /**
     * Represents an HL7 PD1 message segment (Patient Additional Demographic)
     *
     * @param CPatient $patient Patient
     *
     * @return void
     */
    public function addPD1(CPatient $patient): void
    {
        /** @var CHL7v2SegmentPD1 $PD1 */
        $PD1          = CHL7v2Segment::create("PD1", $this->message);
        $PD1->patient = $patient;
        $PD1->build($this);
    }

    /**
     * Represents an HL7 ROL message segment (Role)
     *
     * @param CPatient $patient Patient
     * @param CSejour  $sejour  Admit
     *
     * @return void
     */
    public function addMedecinTraitant(CPatient $patient, CSejour $sejour = null): void
    {
        $medecin_traitant = ($sejour && $sejour->loadRefMedecinTraitant()->_id) ?
            $sejour->_ref_medecin_traitant : $patient->loadRefMedecinTraitant();

        if ($medecin_traitant->_id) {
            /** @var CHL7v2SegmentROL $ROL */
            $ROL          = CHL7v2Segment::create("ROL", $this->message);
            $ROL->medecin = $medecin_traitant;
            $ROL->role_id = "ODRP";
            // Unchanged
            $action = "UC";
            if ($sejour && $sejour->_ref_medecin_traitant->_id && $sejour->fieldModified("medecin_traitant_id")) {
                $action = !$sejour->_old->_id || !$sejour->_old->medecin_traitant_id ? 'AD' : 'UP';
            } elseif ($patient->fieldModified("medecin_traitant")) {
                $action = !$patient->_old->_id || !$patient->_old->medecin_traitant ? 'AD' : 'UP';
            }

            $ROL->action = $action;
            $ROL->build($this);
        }

        // Gestion suppression medecin traitant
        if (!$medecin_traitant->_id) {
            /** @var CSejour|CPatient $old_object */
            $old_object = $sejour ? $sejour->loadOldObject() : $patient->loadOldObject();
            if (
                $old_object && $old_object->_id
                && (($old_object instanceof CPatient && $old_object->medecin_traitant)
                    || ($old_object instanceof CSejour && $old_object->medecin_traitant_id))
            ) {
                /** @var CHL7v2SegmentROL $ROL */
                $ROL          = CHL7v2Segment::create("ROL", $this->message);
                $ROL->medecin = $old_object->loadRefMedecinTraitant();
                $ROL->role_id = "ODRP";
                $ROL->action  = "DE";
                $ROL->build($this);
            }
        }
    }

    /**
     * Represents an HL7 ROL message segment (Role)
     *
     * @param CPatient $patient Patient
     *
     * @return void
     */
    public function addROLs(CPatient $patient): void
    {
        // Ajout des correspondants médicaux
        foreach ($patient->loadRefsCorrespondants() as $_correspondant) {
            $medecin = $_correspondant->loadRefMedecin();
            if ($medecin->type != "medecin") {
                continue;
            }

            // Gestion de l'ajout/mise à jour d'un correspond médical
            if ($patient->_current_correspondant && $_correspondant->_id === $patient->_current_correspondant->_id) {
                $last_log = $patient->_current_correspondant->loadLastLog();
                if ($last_log->type === 'store') {
                    /** @var CCorrespondant $old_object */
                    $old_object = $patient->_current_correspondant->loadOldObject();
                    if ($old_object && $old_object->_id) {
                        $medecin_delete = $old_object->loadRefMedecin();
                        if ($medecin_delete->type == 'medecin') {
                            /** @var CHL7v2SegmentROL $ROL */
                            $ROL          = CHL7v2Segment::create("ROL", $this->message);
                            $ROL->medecin = $medecin_delete;
                            $ROL->role_id = "RT";
                            $ROL->action  = 'DE';
                            $ROL->build($this);
                        }
                    }
                }
                $action = 'AD';
            } else {
                $action = 'UC';
            }

            /** @var CHL7v2SegmentROL $ROL */
            $ROL          = CHL7v2Segment::create("ROL", $this->message);
            $ROL->medecin = $medecin;
            $ROL->role_id = "RT";
            $ROL->action  = $action;
            $ROL->build($this);
        }

        // Gestion d'une suppression d'un correspondant médical
        if ($patient->_delete_correspondant) {
            $medecin = $patient->_delete_correspondant->loadRefMedecin();
            if ($medecin->type == 'medecin') {
                /** @var CHL7v2SegmentROL $ROL */
                $ROL          = CHL7v2Segment::create("ROL", $this->message);
                $ROL->medecin = $medecin;
                $ROL->role_id = "RT";
                $ROL->action  = 'DE';
                $ROL->build($this);
            }
        }
    }

    /**
     * Represents an HL7 NK1 message segment (Next of Kin / Associated Parties)
     *
     * @param CPatient $patient Patient
     *
     * @return void
     * @throws CHL7v2Exception
     */
    public function addNK1s(CPatient $patient): void
    {
        $i = 1;
        foreach ($patient->loadRefsCorrespondantsPatient() as $_correspondant) {
            /** @var CHL7v2SegmentNK1 $NK1 */

            $NK1                = CHL7v2Segment::create("NK1", $this->message);
            $NK1->set_id        = $i;
            $NK1->correspondant = $_correspondant;
            $NK1->build($this);
            $i++;
        }
    }

    /**
     * Represents an HL7 PV1 message segment (Patient Visit)
     *
     * @param CSejour $sejour Admit
     * @param int     $set_id Set ID
     *
     * @return void
     */
    public function addPV1(CSejour $sejour = null, int $set_id = 1): void
    {
        $segment_name = $this->_is_i18n ? "PV1_FR" : "PV1";

        /** @var CHL7v2SegmentPV1 $PV1 */
        $PV1         = CHL7v2Segment::create($segment_name, $this->message);
        $PV1->sejour = $sejour;
        $PV1->set_id = $set_id;

        if ($sejour) {
            $PV1->curr_affectation = $sejour->_ref_hl7_affectation;
            if (!$PV1->curr_affectation) {
                $movement = $sejour->_ref_hl7_movement;
                if ($movement && $movement->affectation_id) {
                    $PV1->curr_affectation = $movement->loadRefAffectation();
                }
            }
        }
        $PV1->build($this);
    }

    /**
     * Represents an HL7 PV2 message segment (Patient Visit - Additional Information)
     *
     * @param CSejour $sejour Admit
     *
     * @return void
     */
    public function addPV2(CSejour $sejour = null): void
    {
        /** @var CHL7v2SegmentPV2 $PV2 */
        $PV2         = CHL7v2Segment::create("PV2", $this->message);
        $PV2->sejour = $sejour;

        if ($sejour) {
            $PV2->curr_affectation = $sejour->_ref_hl7_affectation;

            if (!$PV2->curr_affectation) {
                $movement = $sejour->_ref_hl7_movement;
                if ($movement && $movement->affectation_id) {
                    $PV2->curr_affectation = $movement->loadRefAffectation();
                }
            }
        }
        $PV2->build($this);
    }

    /**
     * Represents an HL7 MRG message segment (Merge Patient Information)
     *
     * @param CMbObject $deleted_object Object to destroy
     *
     * @return void
     * @throws CHL7v2Exception
     */
    public function addMRG(CMbObject $deleted_object): void
    {
        /** @var CHL7v2SegmentMRG $MRG */
        $MRG                 = CHL7v2Segment::create("MRG", $this->message);
        $MRG->deleted_object = $deleted_object;
        $MRG->build($this);
    }

    /**
     * Represents an HL7 ZBE message segment (Movement)
     *
     * @param CSejour $sejour Admit
     *
     * @return void
     * @throws CHL7v2Exception
     */
    public function addZBE(CSejour $sejour = null): void
    {
        $segment_name = $this->_is_i18n ? "ZBE_FR" : "ZBE";

        /** @var CHL7v2SegmentZBE $ZBE */
        $ZBE         = CHL7v2Segment::create($segment_name, $this->message);
        $ZBE->sejour = $sejour;
        $movement    = $sejour->_ref_hl7_movement;
        if ($movement && $movement->affectation_id) {
            $ZBE->curr_affectation = $movement->loadRefAffectation();
        }
        $ZBE->movement          = $movement;
        $ZBE->other_affectation = $sejour->_ref_hl7_affectation;
        $ZBE->build($this);
    }

    /**
     * Represents an HL7 ZFA message segment (DMP)
     *
     * @param CPatient $patient Person
     *
     * @return void
     */
    public function addZFA(CPatient $patient = null): void
    {
        /** @var CHL7v2SegmentZFA $ZFA */
        $ZFA          = CHL7v2Segment::create("ZFA", $this->message);
        $ZFA->patient = $patient;
        $ZFA->build($this);
    }

    /**
     * Represents an HL7 ZFP message segment (Situation professionnelle)
     *
     * @param CSejour $sejour Admit
     *
     * @return void
     */
    public function addZFP(CSejour $sejour = null): void
    {
        /** @var CHL7v2SegmentZFP $ZFP */
        $ZFP          = CHL7v2Segment::create("ZFP", $this->message);
        $ZFP->patient = $sejour->_ref_patient;
        $ZFP->build($this);
    }

    /**
     * Represents an HL7 ZFV message segment (Compléments d'information sur la venue)
     *
     * @param CSejour $sejour Admit
     *
     * @return void
     * @throws CHL7v2Exception
     */
    public function addZFV(CSejour $sejour = null): void
    {
        /** @var CHL7v2SegmentZFV $ZFV */
        $ZFV                   = CHL7v2Segment::create("ZFV", $this->message);
        $ZFV->sejour           = $sejour;
        $ZFV->curr_affectation = $sejour->_ref_hl7_affectation;
        if (!$ZFV->curr_affectation) {
            $movement = $sejour->_ref_hl7_movement;
            if ($movement && $movement->affectation_id) {
                $ZFV->curr_affectation = $movement->loadRefAffectation();
            }
        }
        $ZFV->build($this);
    }

    /**
     * Represents an HL7 ZFM message segment (Mouvement PMSI)
     *
     * @param CSejour $sejour Admit
     *
     * @return void
     * @throws CHL7v2Exception
     */
    public function addZFM(CSejour $sejour = null): void
    {
        /** @var CHL7v2SegmentZFM $ZFM */
        $ZFM                   = CHL7v2Segment::create("ZFM", $this->message);
        $ZFM->sejour           = $sejour;
        $ZFM->curr_affectation = $sejour->_ref_hl7_affectation;
        if (!$ZFM->curr_affectation) {
            $movement = $sejour->_ref_hl7_movement;
            if ($movement && $movement->affectation_id) {
                $ZFM->curr_affectation = $movement->loadRefAffectation();
            }
        }
        $ZFM->build($this);
    }

    /**
     * Represents an HL7 ZFD message segment (Complément démographique)
     *
     * @param CPatient $patient Patient
     *
     * @return void
     */
    public function addZFD(CPatient $patient = null): void
    {
        /** @var CHL7v2SegmentZFD $ZFD */
        $ZFD          = CHL7v2Segment::create("ZFD", $this->message);
        $ZFD->patient = $patient;
        $ZFD->build($this);
    }

    /**
     * Represents an HL7 GT1 message segment (Guarantor)
     *
     * @param CPatient $patient Patient
     *
     * @return void
     */
    public function addGT1(CPatient $patient = null): void
    {
    }

    /**
     * Represents an HL7 IN1 message segment (Insurance )
     *
     * @param CPatient $patient Patient
     * @param int      $set_id  Set ID
     *
     * @return void
     */
    public function addIN1(CPatient $patient, int $set_id = 1): void
    {
        if (!$patient->code_regime && !$patient->caisse_gest && !$patient->centre_gest) {
            return;
        }

        /** @var CHL7v2SegmentIN1 $IN1 */
        $IN1          = CHL7v2Segment::create("IN1", $this->message);
        $IN1->patient = $patient;
        $IN1->set_id  = $set_id;
        $IN1->build($this);
    }

    /**
     * Represents an HL7 IN2 message segment (Insurance - Additional Information)
     *
     * @param CPatient $patient Patient
     * @param int      $set_id  Set ID
     *
     * @return void
     */
    public function addIN2(CPatient $patient, int $set_id = 1): void
    {
        if (!$patient->code_regime && !$patient->caisse_gest && !$patient->centre_gest) {
            return;
        }

        /** @var CHL7v2SegmentIN2 $IN2 */
        $IN2          = CHL7v2Segment::create("IN2", $this->message);
        $IN2->patient = $patient;
        $IN2->set_id  = $set_id;
        $IN2->build($this);
    }

    /**
     * Represents an HL7 OBX message segment (Observation/Result)
     *
     * @param CPatient $patient Patient
     *
     * @return void
     * @throws CHL7v2Exception
     */
    public function addOBXs(CPatient $patient): void
    {
        if ($patient->_annees > 2) {
            return;
        }

        /* @todo On transmet uniquement le poids et la taille du premier relevé */
        $constante = $patient->getFirstConstantes();
        if (!$constante->_id) {
            return;
        }

        $list_constantes = CConstantesMedicales::$list_constantes;

        // Poids
        if ($constante->poids) {
            /** @var CHL7v2SegmentOBX $OBX */
            $OBX                    = CHL7v2Segment::create("OBX", $this->message);
            $observation_identifier = [
                [
                    "3141-9", // Poids corporel [Masse] Patient ; Numérique ; Résultat mesuré
                    "BODY WEIGHT",
                    "LN",
                ],
            ];
            /*if ($constante->poids < 1) {
              $unit  = $list_constantes["_poids_g"]["unit"];
              $value = $constante->_poids_g;
            }
            else {*/
            $unit  = $list_constantes["poids"]["unit"];
            $value = $constante->poids;
            //}
            $OBX->initializeDatas(
                $patient,
                1,
                "NM",
                $observation_identifier,
                $value,
                $unit,
                $constante->getCreationDate()
            );
            $OBX->build($this);
        }

        // taille
        if ($constante->taille) {
            /** @var CHL7v2SegmentOBX $OBX */
            $OBX                    = CHL7v2Segment::create("OBX", $this->message);
            $observation_identifier = [
                "3137-7", // Taille du patient [Longueur] Patient ; Numérique ; Résultat mesuré
                "BODY HEIGHT",
                "LN",
            ];
            $unit                   = $list_constantes["taille"]["unit"];
            $OBX->initializeDatas(
                $patient,
                1,
                "NM",
                $observation_identifier,
                $constante->taille,
                $unit,
                $constante->getCreationDate()
            );
            $OBX->build($this);
        }
    }

    /**
     * Represents an HL7 IAM message segment (Patient Adverse Reaction Information)
     *
     * @param CPatient    $patient  Patient
     * @param CAntecedent $allergie Antecedent
     *
     * @return void
     * @throws CHL7v2Exception
     */
    public function addIAMs(CPatient $patient, CAntecedent $antecedent): void
    {
        $i           = 1;
        $antecedents = $patient->loadRefDossierMedical()->loadRefsAntecedents();

        $types_antecedents_adt_a60     = explode("|", CAppUI::conf("hl7 type_antecedents_adt_a60"));
        $appareils_antecedents_adt_a60 = explode("|", CAppUI::conf("hl7 appareil_antecedents_adt_a60"));
        foreach ($antecedents as $_atcd) {
            if (
                !CMbArray::in($_atcd->type, $types_antecedents_adt_a60) &&
                !CMbArray::in(
                    $_atcd->appareil,
                    $appareils_antecedents_adt_a60
                )
            ) {
                continue;
            }

            /** @var CHL7v2SegmentIAM $IAM */
            $IAM                     = CHL7v2Segment::create("IAM", $this->message);
            $IAM->set_id             = $i;
            $IAM->patient            = $patient;
            $IAM->antecedent         = $_atcd;
            $IAM->antecedent_handled = $antecedent;
            $IAM->build($this);
            $i++;
        }
    }
}
