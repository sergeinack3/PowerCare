<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Ccam\CActe;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\SalleOp\CActeCCAM;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CHPrimXMLEvenementsServeurActes
 * Serveur actes
 */
class CHPrimXMLEvenementsServeurActes extends CHPrimXMLEvenementsServeurActivitePmsi
{
    /** @var string[] H'XML actions availabled */
    public $actions = [
        'création'     => "création",
        'remplacement' => "remplacement",
        'modification' => "modification",
        'suppression'  => "suppression",
        'information'  => "information",
    ];

    /**
     * Construct
     *
     * @return CHPrimXMLEvenementsServeurActes
     */
    public function __construct()
    {
        $this->sous_type = "evenementServeurActe";
        $this->evenement = "evt_serveuractes";

        parent::__construct("serveurActes", "msgEvenementsServeurActes");
    }

    /**
     * @inheritdoc
     */
    public function generateEnteteMessage($type = null, $version = true, $group_id = null)
    {
        parent::generateEnteteMessage("evenementsServeurActes");
    }

    /**
     * @inheritdoc
     */
    public function generateFromOperation(CMbObject $codable, $referent = false)
    {
        $receiver = $this->_ref_receiver;

        $evenementsServeurActes = $this->documentElement;

        $evenementServeurActe = $this->addElement($evenementsServeurActes, "evenementServeurActe");
        $this->addDateTimeElement($evenementServeurActe, "dateAction");

        // Ajout du patient
        $patient = $this->addElement($evenementServeurActe, "patient");
        switch ($codable->_class) {
            // CSejour / CConsultation
            case 'CSejour':
            case 'CConsultation':
                $mbPatient = $codable->_ref_patient;
                break;

            // COperation
            case 'COperation':
                $mbPatient = $codable->_ref_sejour->_ref_patient;
                break;

            default:
        }
        $this->addPatient($patient, $mbPatient, false, true);

        // Ajout de la venue
        $venue = $this->addElement($evenementServeurActe, "venue");
        switch ($codable->_class) {
            // COperation / CConsultation
            case 'COperation':
            case 'CConsultation':
                $mbSejour = $codable->_ref_sejour;
                break;

            // CSejour
            case 'CSejour':
                $mbSejour = $codable;
                break;

            default:
        }
        $this->addVenue($venue, $mbSejour, null, true);

        // Ajout de l'intervention ou consultation ou sejour
        $intervention = $this->addElement($evenementServeurActe, "intervention");
        switch ($codable->_class) {
            // COperation
            case 'COperation':
                $this->addIntervention($intervention, $codable, false, true);
                break;

            // CConsultation / CSejour
            // On ajoute seulement l'identifiant de la consultation ou séjour
            case 'CConsultation':
            case 'CSejour':
                $identifiant = $this->addElement($intervention, "identifiant");
                $this->addElement($identifiant, "emetteur", substr($codable->_class, 1, 1) . "-$codable->_id");


                if ($receiver->_configs["send_prescripteur_ngap"] == "demande") {
                    $demande = $this->addElement($intervention, "demande");

                    // On recherche le premier prescripteur dans les actes NGAP
                    /** @var CActeNGAP[] $_acte_ngap */
                    foreach ($codable->_ref_actes_ngap as $_acte_ngap) {
                        // Médecin prescripteur
                        $prescripteur = $_acte_ngap->loadRefPrescripteur();
                        if ($prescripteur && $prescripteur->_id) {
                            $datePrescription = $this->addElement($demande, "datePrescription");

                            // Date d'exécution : séjour = date d'entrée, consultation = date consult
                            $date_execution = $codable instanceof CSejour ? $codable->entree : $_acte_ngap->execution;
                            $this->addDate($datePrescription, $date_execution);

                            $medecin = $this->addElement($demande, "medecin");
                            $this->addProfessionnelSante($medecin, $prescripteur, "prsc");

                            break;
                        }
                    }
                }
                break;

            default:
        }

        // Ajout des actes CCAM
        if ($receiver->_configs["send_actes"] == "ccamngap" || $receiver->_configs["send_actes"] == "ccam") {
            $actesCCAM = $this->addElement($evenementServeurActe, "actesCCAM");
            foreach ($codable->_ref_actes_ccam as $_acte_ccam) {
                if (
                    (CAppUI::conf("dPpmsi transmission_actes") == "signature") &&
                    (!$_acte_ccam->signe || $_acte_ccam->sent)
                ) {
                    continue;
                }

                // On ne va pas envoyer l'acte si la fonction du praticien est exclu
                if ($send_actes_only_functions_conf = $receiver->_configs["send_actes_only_functions"]) {
                    $send_actes_only_functions = explode('|', $send_actes_only_functions_conf);

                    /** @var CActeCCAM $_acte_ccam */
                    $fonction_executant =  $_acte_ccam->loadRefExecutant()->_ref_function;
                    if (CMbArray::in($fonction_executant->_id, $send_actes_only_functions)) {
                        continue;
                    }
                }

                $this->addActeCCAM($actesCCAM, $_acte_ccam, $codable);
            }
        }

        // Ajout des actes NGAP
        if ($receiver->_configs["send_actes"] == "ccamngap" || $receiver->_configs["send_actes"] == "ngap") {
            $actesNGAP = $this->addElement($evenementServeurActe, "actesNGAP");

            $actes_ngap_excludes = [];
            if (CAppUI::conf("hprimxml actes_ngap_excludes")) {
                $actes_ngap_excludes = array_flip(explode("|", CAppUI::conf("hprimxml actes_ngap_excludes")));
            }

            foreach ($codable->_ref_actes_ngap as $_acte_ngap) {
                if (array_key_exists($_acte_ngap->code, $actes_ngap_excludes)) {
                    continue;
                }

                // On ne va pas envoyer l'acte si la fonction du praticien est exclu
                if ($send_actes_only_functions_conf = $receiver->_configs["send_actes_only_functions"]) {
                    $send_actes_only_functions = explode('|', $send_actes_only_functions_conf);

                    /** @var CActeNGAP $_acte_ngap */
                    $fonction_executant =  $_acte_ngap->loadRefExecutant()->_ref_function;
                    if (CMbArray::in($fonction_executant->_id, $send_actes_only_functions)) {
                        continue;
                    }
                }

                $this->addActeNGAP($actesNGAP, $_acte_ngap, $codable);
            }
        }

        // Traitement final
        $this->purgeEmptyElements();
    }

    /**
     * Get content XML
     *
     * @return array
     */
    public function getContentsXML(): array
    {
        $data  = [];
        $xpath = new CHPrimXPath($this);

        $evenementServeurActe = $xpath->queryUniqueNode("/hprim:evenementsServeurActes/hprim:evenementServeurActe");

        $data['patient'] = $xpath->queryUniqueNode("hprim:patient", $evenementServeurActe);
        //@todo ajouter une configuration pour intervertir le source et le cible
        $data['idSourcePatient'] = $this->getIdCible($data['patient']);
        $data['idCiblePatient']  = $this->getIdSource($data['patient']);

        $data['venue'] = $xpath->queryUniqueNode("hprim:venue", $evenementServeurActe);
        //@todo ajouter une configuration pour intervertir le source et le cible
        $data['idSourceVenue'] = $this->getIdCible($data['venue']);
        $data['idCibleVenue']  = $this->getIdSource($data['venue']);

        $data['intervention']         = $xpath->queryUniqueNode("hprim:intervention", $evenementServeurActe);
        $data['idSourceIntervention'] = $this->getIdSource($data['intervention'], false);
        $data['idCibleIntervention']  = $this->getIdCible($data['intervention'], false);

        $data['actesCCAM'] = $xpath->queryUniqueNode("hprim:actesCCAM", $evenementServeurActe);
        $data['actesNGAP'] = $xpath->queryUniqueNode("hprim:actesNGAP", $evenementServeurActe);

        return $data;
    }

    /**
     * Enregistrement des actes CCAM
     *
     * @param CHPrimXMLAcquittements $dom_acq  DOM Acquittement
     * @param CMbObject              $mbObject Object
     * @param array                  $data     Data that contain the nodes
     *
     * @return string Acquittement
     **/
    public function handle(CHPrimXMLAcquittements $dom_acq, CMbObject $mbObject, $data)
    {
        /** @var COperation $mbObject */
        $exchange_hprim = $this->_ref_echange_hprim;
        $sender         = $exchange_hprim->_ref_sender;
        $sender->loadConfigValues();

        $this->_ref_sender = $sender;

        // Acquittement d'erreur : identifiants source du patient / séjour non fournis
        if (!$data['idSourcePatient'] || !$data['idSourceVenue']) {
            return $exchange_hprim->setAckError($dom_acq, "E206", null, $mbObject, $data);
        }

        // IPP non connu => message d'erreur
        $IPP = CIdSante400::getMatch("CPatient", $sender->_tag_patient, $data['idSourcePatient']);
        if (!$IPP->_id) {
            return $exchange_hprim->setAckError($dom_acq, "E013", null, $mbObject, $data);
        }

        // Chargement du patient
        $patient = new CPatient();
        $patient->load($IPP->object_id);

        // Num dossier non connu => message d'erreur
        $NDA = CIdSante400::getMatch("CSejour", $sender->_tag_sejour, $data['idSourceVenue']);
        if (!$NDA->_id) {
            return $exchange_hprim->setAckError($dom_acq, "E014", null, $mbObject, $data);
        }

        // Chargement du séjour
        $sejour = new CSejour();
        $sejour->load($NDA->object_id);

        // Si patient H'XML est différent du séjour
        if ($sejour->patient_id != $patient->_id) {
            return $exchange_hprim->setAckError($dom_acq, "E015", null, $mbObject, $data);
        }

        // Chargement du patient du séjour
        $sejour->loadRefPatient();

        //Mapping actes CCAM
        $actes   = [
            "CCAM" => $this->mappingActesCCAM($data),
            "NGAP" => $this->mappingActesNGAP($data),
        ];
        $codes   = [];
        $warning = [];
        foreach ($actes as $type => $_actes) {
            foreach ($_actes as $_key => $_acte) {
                $return = $this->storeActe($_acte, $type, $sejour, $patient, $sender->_tag_hprimxml);
                $number = $type == "CCAM" ? "0" : "1";
                //Cas d'une erreur lors de l'ajoutement
                if (!is_object($return)) {
                    $warning["A401"][]                  = $return;
                    $codes[$_acte["idSourceActe$type"]] = ["code" => "A4{$number}1", "commentaires" => $return];
                    $actes[$type][$_key]["statut"]      = "avt";
                    continue;
                }
                $actes[$type][$_key]["statut"] = "ok";
                //Cas d'une modification ou d'un ajout
                if ($return->_id) {
                    $codes[$_acte["idSourceActe$type"]] = ["code" => "I4{$number}1", "commentaires" => null];
                    continue;
                }
                //Cas de la suppression
                $codes[$_acte["idSourceActe$type"]] = ["code" => "I4{$number}2", "commentaires" => null];
            }
        }

        return $exchange_hprim->setAck($dom_acq, $codes, $warning, null, $sejour, $actes);
    }

    /**
     * Store Acte
     *
     * @param string[] $data    Value
     * @param string   $type    CCAM or NGAP
     * @param CSejour  $sejour  Sejour
     * @param CPatient $patient Patient
     * @param string   $tag     Tag
     *
     * @return String|CActe
     */
    private function storeActe(array $data, string $type, CSejour $sejour, CPatient $patient, string $tag)
    {
        $code_acte = "code";
        if ($type == "CCAM") {
            $field_object = "codes_ccam";
            $code_acte    = "code_acte";
        }
        $action = $data["action"];

        $idex         = CIdSante400::getMatch("CActe$type", $tag, $data["idSourceActe$type"]);
        $executant_id = $data["executant_id"];
        if ($idex->_id) {
            $class = "CActe$type";
            /** @var CActeCCAM|CActeNGAP $acte */
            $acte = new $class();
            $acte->load($idex->object_id);

            $object = $acte->loadTargetObject();

            if ($action === "suppression") {
                if ($type == "CCAM") {
                    $code    = $acte->$code_acte;
                    $replace = explode("|", $object->$field_object);
                    CMbArray::removeValue($code, $replace);
                    $object->$field_object = $replace ? implode("|", $replace) : "";
                }

                if ($msg = $this->deleteActe($acte, $object, $idex)) {
                    return $msg;
                }

                return $acte;
            }

            if (!$executant_id) {
                return 'Le médecin n\'a pas été retrouvé';
            }

            /** @var CActeCCAM|CActeNGAP $new_acte */
            $new_acte     = $this->{"createActe$type"}($data["acte$type"], $object, $executant_id);
            $modification = $new_acte->$code_acte != $acte->$code_acte;
            if ($modification) {
                if ($type == "CCAM") {
                    $new_code              = preg_replace(
                        "#$acte->$code_acte#",
                        $new_acte->$code_acte,
                        $object->$field_object,
                        1
                    );
                    $object->$field_object = $new_code;
                }

                if ($msg = $this->deleteActe($acte, $object, $idex)) {
                    return $msg;
                }

                $acte = new $class();
            }

            $acte->extendsWith($new_acte, true);
            if ($msg = $acte->store()) {
                return $msg;
            }

            if ($modification) {
                $idex->setObject($acte);
                if ($msg = $idex->store()) {
                    return $msg;
                }
            }

            return $acte;
        }

        if ($action !== "création") {
            return "$action impossible car l'acte n'a pas été trouvé";
        }

        if (!$executant_id) {
            return 'Le médecin n\'a pas été retrouvé';
        }

        $date = CMbDT::date($data["acte$type"]["date"]);

        $object = $this->getObject($date, $executant_id, $patient->_id);
        $object = $object ? $object : $sejour;

        /** @var CActe $acte */
        $acte = $this->{"createActe$type"}($data["acte$type"], $object, $executant_id);
        if ($type == "CCAM") {
            $object->$field_object .= $object->$field_object ? "|{$acte->$code_acte}" : $acte->$code_acte;
        }
        if ($msg = $object->store()) {
            return $msg;
        }
        if ($msg = $acte->store()) {
            return $msg;
        }

        $idex        = new CIdSante400();
        $idex->id400 = $data["idSourceActe$type"];
        $idex->tag   = $tag;
        $idex->setObject($acte);
        if ($msg = $idex->store()) {
            return $msg;
        }

        return $acte;
    }

    /**
     * Delete acte
     *
     * @param CActe       $acte   Acte
     * @param CMbObject   $object Object
     * @param CIdSante400 $idex   Idex
     *
     * @return string|null
     */
    private function deleteActe(CActe $acte, CMbObject $object, CIdSante400 $idex): ?string
    {
        if ($msg = $idex->delete()) {
            return $msg;
        }
        if ($msg = $acte->delete()) {
            return $msg;
        }
        if ($msg = $object->store()) {
            return $msg;
        }

        return null;
    }

    /**
     * Return a object concern praticien and a patient in date
     *
     * @param string $date         Date
     * @param int    $praticien_id Praticien id
     * @param int    $patient_id   Patient id
     *
     * @return CConsultation|COperation|null
     */
    private function getObject(string $date, int $praticien_id, int $patient_id)
    {
        $intervention = new COperation();
        $where        = [
            "plagesop.date"      => "= '$date'",
            "operations.chir_id" => "= '$praticien_id'",
            "sejour.patient_id"  => "= '$patient_id'",
        ];
        $leftjoin     = [
            "plagesop" => "operations.plageop_id = plagesop.plageop_id",
            "sejour"   => "operations.sejour_id = sejour.sejour_id",
        ];
        $intervention->loadObject($where, "plagesop.debut DESC", null, $leftjoin);
        $object = $intervention;

        if (!$object->_id) {
            $consultation = new CConsultation();
            $where        = [
                "plageconsult.date"       => "= '$date'",
                "plageconsult.chir_id"    => "= '$praticien_id'",
                "consultation.patient_id" => "= '$patient_id'",
            ];
            $leftjoin     = [
                "plageconsult" => "consultation.plageconsult_id = plageconsult.plageconsult_id",
            ];
            $consultation->loadObject($where, "consultation.heure DESC", null, $leftjoin);
            $object = $consultation;

            if (!$object->_id) {
                return null;
            }
        }

        return $object;
    }

    /**
     * Create a CCAM
     *
     * @param String[]  $data         CCAM field with value
     * @param CMbObject $object       Reference Obect
     * @param int       $praticien_id Practicien id
     *
     * @return CActeCCAM
     */
    private function createActeCCAM(array $data, CMbObject $object, int $praticien_id): CActeCCAM
    {
        $ccam = new CActeCCAM();

        $ccam->code_acte     = $data["code_acte"];
        $ccam->code_activite = $data["code_activite"];
        $ccam->code_phase    = $data["code_phase"];

        $heure = $data["heure"];

        if (!$heure) {
            $heure = $this->getHourWithObject($object);
        } else {
            $heure = CMbDT::transform(null, $heure, "%H:%M:%S");
        }

        $ccam->execution                = $data["date"] . " $heure";
        $ccam->modificateurs            = implode($data["modificateur"]);
        $ccam->commentaire              = $data["commentaire"];
        $ccam->signe                    = $data["signe"] ? $data["signe"] == "oui" ? "1" : "0" : null;
        $ccam->facturable               = $data["facturable"] ? $data["facturable"] == "non" ? "0" : "1" : "1";
        $ccam->rembourse                = $data["rembourse"] ? $data["rembourse"] == "oui" ? "1" : "0" : null;
        $ccam->charges_sup              = $data["charges_sup"] ? $data["charges_sup"] == "c" ? "1" : "0" : null;
        $ccam->montant_depassement      = $data["montantDepassement"];
        $ccam->numero_forfait_technique = $data["numeroForfaitTechnique"];
        $ccam->numero_agrement          = $data["numeroAgrementAppareil"];
        $ccam->position_dentaire        = implode("|", $data["position_dentaire"]);
        if ($data["code_association"] && $data["code_association"] > 0 && $data["code_association"] < 6) {
            $ccam->code_association = $data["code_association"];
        }
        if ($data["code_extension"] && $data["code_extension"] > 0 && $data["code_extension"] < 7) {
            $ccam->extension_documentaire = $data["code_extension"];
        }
        $ccam->rapport_exoneration = $data["rapport_exoneration"];

        $ccam->executant_id = $praticien_id;
        $ccam->setObject($object);

        return $ccam;
    }

    /**
     * Create a NGAP acte
     *
     * @param String[]  $data         Data with fiel and value
     * @param CMbObject $object       Reference object
     * @param int       $praticien_id Praticen id
     *
     * @return CActeNGAP
     */
    private function createActeNGAP(array $data, CMbObject $object, int $praticien_id): CActeNGAP
    {
        $ngap                           = new CActeNGAP();
        $ngap->code                     = $data["code"];
        $ngap->coefficient              = $data["coefficient"];
        $ngap->quantite                 = $data["quantite"] ? $data["quantite"] : 1;
        $ngap->numero_dent              = $data["numero_dent"];
        $ngap->comment                  = $data["comment"];
        $ngap->montant_depassement      = $data["montantDepassement"];
        $ngap->numero_forfait_technique = $data["numeroForfaitTechnique"];
        $ngap->numero_agrement          = $data["numeroAgrementAppareil"];
        $ngap->minor_coef               = $data["minor_coef"];
        $ngap->minor_pct                = $data["minor_pct"];
        $ngap->major_coef               = $data["major_coef"];
        $ngap->major_pct                = $data["major_pct"];
        $ngap->facturable               = $data["facturable"] ? $data["facturable"] == "non" ? "0" : "1" : "1";
        $ngap->rapport_exoneration      = $data["rapportExoneration"];

        $date  = $data["date"];
        $heure = $data["heure"];

        if (!$heure) {
            $heure = $this->getHourWithObject($object);
        } else {
            $heure = CMbDT::transform(null, $heure, "%H:%M:%S");
        }
        $ngap->execution = "$date $heure";
        $complement      = null;
        if ($data["executionNuit"] && $data["executionNuit"] !== "non") {
            $complement = "N";
        }
        if ($data["executionDimancheJourFerie"] && $data["executionDimancheJourFerie"] !== "non") {
            $complement = "F";
        }
        $ngap->complement = $complement;
        $ngap->setObject($object);
        $ngap->executant_id = $praticien_id;

        return $ngap;
    }

    /**
     * Return the time of the object
     *
     * @param CMbObject $object Reference Object
     *
     * @return null|string
     */
    private function getHourWithObject(CMbObject $object): ?string
    {
        $heure = null;
        switch (get_class($object)) {
            case COperation::class:
                /** @var COperation $object */
                $time_operation = ($object->time_operation == "00:00:00") ? null : $object->time_operation;
                $heure          = CValue::first(
                    CMbDT::time($object->debut_op),
                    CMbDT::time($object->entree_salle),
                    $time_operation,
                    CMbDT::time($object->horaire_voulu)
                );
                break;
            case CConsultation::class:
                /** @var CConsultation $object */
                $heure = $object->heure;
                break;
            case CSejour::class:
                /** @var CSejour $object */
                $heure = CMbDT::time($object->entree);
                break;
            default:
        }

        return $heure;
    }
}
