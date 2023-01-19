<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Ox\AppFine\Client\CAppFineClient;
use Ox\AppFine\Server\CAppFineServer;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Interop\Eai\CEAIPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CHPrimXMLEnregistrementPatient
 */
class CHPrimXMLEnregistrementPatient extends CHPrimXMLEvenementsPatients
{
    /** @var string[] */
    public $actions = [
        'création'     => "création",
        'remplacement' => "remplacement",
        'modification' => "modification",
    ];

    /**
     * @see parent::__construct()
     */
    public function __construct()
    {
        $this->sous_type = "enregistrementPatient";

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function generateFromOperation(CMbObject $mbPatient, $referent = false)
    {
        $evenementsPatients = $this->documentElement;
        $evenementPatient   = $this->addElement($evenementsPatients, "evenementPatient");

        $enregistrementPatient = $this->addElement($evenementPatient, "enregistrementPatient");
        $actionConversion      = [
            "create" => "création",
            "store"  => "modification",
            "delete" => "suppression",
        ];
        $action                = (!$mbPatient->_ref_last_log) ?
            "modification" : $actionConversion[$mbPatient->_ref_last_log->type];

        $this->addAttribute($enregistrementPatient, "action", $action);

        $patient = $this->addElement($enregistrementPatient, "patient");
        // Ajout du patient
        $this->addPatient($patient, $mbPatient, $referent);

        $echg_hprim = $this->_ref_echange_hprim;
        $echg_hprim->loadRefReceiver();
        $receiver = $echg_hprim->_ref_receiver;
        $receiver->loadConfigValues();

        if ($receiver->_configs["send_volet_medical"]) {
            $voletMedical = $this->addElement($enregistrementPatient, "voletMedical");

            if (CModule::getActive("appFine")) {
                CAppFineServer::addVoletMedical($voletMedical, $mbPatient, $this);
            } elseif (CModule::getActive("appFineClient") && CAppFineClient::loadIdex($mbPatient)->_id) {
                CAppFineClient::addVoletMedical($voletMedical, $mbPatient, $this);
            } else {
                $this->addVoletMedical($voletMedical, $mbPatient);
            }
        }

        // Traitement final
        $this->purgeEmptyElements();
    }

    /**
     * @see parent::getContentsXML()
     */
    public function getContentsXML(): array
    {
        $xpath = new CHPrimXPath($this);

        $query = "/hprim:evenementsPatients/hprim:evenementPatient";

        $evenementPatient      = $xpath->queryUniqueNode($query);
        $enregistrementPatient = $xpath->queryUniqueNode("hprim:enregistrementPatient", $evenementPatient);

        $data['action'] = $this->getActionEvenement("hprim:enregistrementPatient", $evenementPatient);

        $data['patient']      = $xpath->queryUniqueNode("hprim:patient", $enregistrementPatient);
        $data['voletMedical'] = $xpath->queryUniqueNode("hprim:voletMedical", $enregistrementPatient);

        $data['idSourcePatient'] = $this->getIdSource($data['patient']);
        $data['idCiblePatient']  = $this->getIdCible($data['patient']);
        $data["numeroSante"]     = $xpath->queryUniqueNode("hprim:numeroIdentifiantSante", $enregistrementPatient);

        return $data;
    }

    /**
     * Recording a patient with an IPP in the system
     *
     * @param CHPrimXMLAcquittementsPatients  $dom_acq    Acquittement
     * @param CPatient                       &$newPatient Patient
     * @param array                           $data       Datas
     *
     * @return string $msgAcq
     **/
    public function enregistrementPatient(
        CHPrimXMLAcquittementsPatients $dom_acq,
        CPatient &$newPatient,
        array $data
    ): ?string {
        // Traitement du message des erreurs
        $codes          = [];
        $commentaire    = $avertissement = $msgID400 = $msgIPP = "";
        $_modif_patient = false;

        $echg_hprim = $this->_ref_echange_hprim;
        $sender     = $echg_hprim->_ref_sender;
        $sender->loadConfigValues();
        $this->_ref_sender = $sender;

        if ($msg = $this->check($dom_acq, $newPatient, $data)) {
            return $msg;
        }

        if (CModule::getActive("appFineClient") && $sender->_configs["handle_appFine"]) {
            return CAppFineClient::getDossierMedicalTiers($sender, $data, $echg_hprim, $dom_acq);
        }

        if (CModule::getActive("appFine") && $sender->_configs["handle_appFine"]) {
            return CAppFineServer::getDossierMedical($sender, $data, $echg_hprim, $dom_acq);
        }

        $idSourcePatient = $data['idSourcePatient'];
        $idCiblePatient  = $data['idCiblePatient'];

        $IPP = CIdSante400::getMatch("CPatient", $sender->_tag_patient, $idSourcePatient);

        // idSource non connu
        if (!$IPP->_id) {
            // idCible fourni
            if ($idCiblePatient) {
                if ($newPatient->load($idCiblePatient)) {
                    // Le patient trouvé est-il différent ?
                    if ($commentaire = $this->checkSimilarPatient($newPatient, $data['patient'])) {
                        return $echg_hprim->setAckError($dom_acq, "E016", $commentaire, $newPatient);
                    }

                    // Mapping du patient
                    $newPatient = $this->mappingPatient($data['patient'], $newPatient);

                    // On store le patient
                    $msgPatient  = CEAIPatient::storePatient($newPatient, $sender);
                    $commentaire = CEAIPatient::getComment($newPatient);

                    $_code_IPP      = "I021";
                    $_modif_patient = true;
                } else {
                    $_code_IPP = "I020";
                }
            } else {
                $_code_IPP = "I022";
            }
            // Mapping du patient
            $newPatient = $this->mappingPatient($data['patient'], $newPatient);

            if (!$newPatient->_id) {
                // Patient retrouvé
                if ($newPatient->loadMatchingPatient(false, true, [], false, $sender->group_id)) {
                    // Mapping du patient
                    $newPatient = $this->mappingPatient($data['patient'], $newPatient);

                    // On store le patient
                    $msgPatient  = CEAIPatient::storePatient($newPatient, $sender);
                    $commentaire = CEAIPatient::getComment($newPatient);

                    $_code_IPP      = "A021";
                    $_modif_patient = true;
                } else {
                    // On store le patient
                    $msgPatient  = CEAIPatient::storePatient($newPatient, $sender);
                    $commentaire = CEAIPatient::getComment($newPatient);
                }
            }

            $msgIPP = CEAIPatient::storeIPP($IPP, $newPatient, $sender);

            $codes = [
                $msgPatient ? ($_modif_patient ? "A003" : "A002") :
                    ($_modif_patient ? "I002" : "I001"),
                $msgIPP ? "A005" : $_code_IPP,
            ];

            if ($msgPatient || $msgIPP) {
                $avertissement = $msgPatient . " " . $msgIPP;
            } else {
                $commentaire .= "IPP créé : $IPP->id400.";
            }
        } // idSource connu
        else {
            $newPatient->load($IPP->object_id);
            if ($commentaire = $this->checkSimilarPatient($newPatient, $data['patient'])) {
                return $echg_hprim->setAckError($dom_acq, "E016", $commentaire, $newPatient);
            }

            // Mapping du patient
            $newPatient = $this->mappingPatient($data['patient'], $newPatient);

            // idCible non fourni
            if (!$idCiblePatient) {
                $_code_IPP = "I023";
            } else {
                $tmpPatient = new CPatient();
                // idCible connu
                if ($tmpPatient->load($idCiblePatient)) {
                    if ($tmpPatient->_id != $IPP->object_id) {
                        $commentaire = "L'identifiant source fait référence au patient : $IPP->object_id ";
                        $commentaire .= "et l'identifiant cible au patient : $tmpPatient->_id.";

                        return $echg_hprim->setAckError($dom_acq, "E004", $commentaire, $newPatient);
                    }
                    $_code_IPP = "I024";
                } // idCible non connu
                else {
                    $_code_IPP = "A020";
                }
            }

            // On store le patient
            $msgPatient  = CEAIPatient::storePatient($newPatient, $sender);
            $commentaire = CEAIPatient::getComment($newPatient);

            if ($newPatient->_id && $sender->_configs["insc_integrated"]) {
                $this->storeINSC($newPatient, $data["numeroSante"]);
            }

            $codes = [$msgPatient ? "A003" : "I002", $_code_IPP];

            if ($msgPatient) {
                $avertissement = $msgPatient . " ";
            }
        }

        return $echg_hprim->setAck($dom_acq, $codes, $avertissement, $commentaire, $newPatient);
    }

    /**
     * Check datas
     *
     * @param CHPrimXMLAcquittementsPatients $dom_acq    Acquittement
     * @param CPatient                       $newPatient Patient
     * @param array                          $data       Datas
     *
     * @return string
     */
    private function check(CHPrimXMLAcquittementsPatients $dom_acq, CPatient $newPatient, array $data): ?string
    {
        $idSourcePatient = $data['idSourcePatient'];
        $idCiblePatient  = $data['idCiblePatient'];

        // Acquittement d'erreur : identifiants source et cible non fournis
        if (!$idCiblePatient && !$idSourcePatient) {
            return $this->_ref_echange_hprim->setAckError($dom_acq, "E005", null, $newPatient);
        }

        return null;
    }
}
