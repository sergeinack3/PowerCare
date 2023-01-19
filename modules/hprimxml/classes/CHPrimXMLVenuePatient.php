<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Interop\Eai\CEAISejour;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CHPrimXMLVenuePatient
 * Évènements venue patient
 */
class CHPrimXMLVenuePatient extends CHPrimXMLEvenementsPatients
{
    public $actions = [
        'création'     => "création",
        'remplacement' => "remplacement",
        'modification' => "modification",
        'suppression'  => "suppression",
    ];

    /**
     * @see parent::__construct()
     */
    function __construct()
    {
        $this->sous_type = "venuePatient";

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    function generateFromOperation(CMbObject $mbVenue, $referent = false)
    {
        $evenementsPatients = $this->documentElement;
        $evenementPatient   = $this->addElement($evenementsPatients, "evenementPatient");

        $venuePatient     = $this->addElement($evenementPatient, "venuePatient");
        $actionConversion = [
            "create" => "création",
            "store"  => "modification",
            "delete" => "suppression",
        ];
        $action           = $actionConversion[$mbVenue->_ref_last_log->type];
        if ($mbVenue->annule) {
            $action = "suppression";
        }
        $this->addAttribute($venuePatient, "action", $action);

        $patient = $this->addElement($venuePatient, "patient");
        // Ajout du patient
        $this->addPatient($patient, $mbVenue->_ref_patient, $referent);

        $venue = $this->addElement($venuePatient, "venue");
        // Ajout de la venue
        $this->addVenue($venue, $mbVenue, $referent);

        $echg_hprim = $this->_ref_echange_hprim;
        $receiver   = $echg_hprim->_ref_receiver;

        // Cas d'une annulation dans Mediboard on passe en trash le num dossier
        if (CAppUI::conf("hprimxml trash_numdos_sejour_cancel") && $mbVenue->annule && $mbVenue->_NDA) {
            $NDA = CIdSante400::getMatch("CSejour", $receiver->_tag_sejour, $mbVenue->_NDA);
            if ($NDA->_id) {
                $NDA->tag = CAppUI::conf('dPplanningOp CSejour tag_dossier_trash') . $receiver->_tag_sejour;
                $NDA->loadMatchingObject();
                $NDA->store();
            }
        }

        $version = CAppUI::conf("hprimxml $this->evenement version");
        if ($version == "1.054" && $receiver->_configs["send_child_admit"]) {
            $sejour_maman = $mbVenue->loadRefNaissance()->loadRefSejourMaman();
            $sejour_maman->loadNDA($receiver->group_id);
            if ($sejour_maman->_id) {
                $maman = $this->addElement($venuePatient, "maman");

                // Ajout du NDA de la maman
                $this->addMaman($maman, $sejour_maman, $referent);
            }
        }

        $receiver->loadConfigValues();
        // Ajout du volet médical
        if ($receiver->_configs["send_volet_medical"]) {
            $voletMedical = $this->addElement($venuePatient, "voletMedical");
            $this->addVoletMedical($voletMedical, $mbVenue);
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

        $evenementPatient = $xpath->queryUniqueNode($query);
        $venuePatient     = $xpath->queryUniqueNode("hprim:venuePatient", $evenementPatient);

        $data['action'] = $this->getActionEvenement("hprim:venuePatient", $evenementPatient);

        $data['patient'] = $xpath->queryUniqueNode("hprim:patient", $venuePatient);
        $data['venue']   = $xpath->queryUniqueNode("hprim:venue", $venuePatient);

        $data['idSourcePatient'] = $this->getIdSource($data['patient']);
        $data['idCiblePatient']  = $this->getIdCible($data['patient']);

        $data['idSourceVenue'] = $this->getIdSource($data['venue']);
        $data['idCibleVenue']  = $this->getIdCible($data['venue']);

        return $data;
    }

    /**
     * Record admit
     *
     * @param CHPrimXMLAcquittementsPatients  $dom_acq    Acquittement
     * @param CPatient                        $newPatient Patient
     * @param array                           $data       Data
     * @param CSejour                        &$newVenue   Admit
     *
     * @return CHPrimXMLAcquittementsPatients $msgAcq
     **/
    function venuePatient(CHPrimXMLAcquittementsPatients $dom_acq, CPatient $newPatient, $data, &$newVenue = null)
    {
        $echg_hprim = $this->_ref_echange_hprim;

        // Cas 1 : Traitement du patient
        $domEnregistrementPatient                     = new CHPrimXMLEnregistrementPatient();
        $domEnregistrementPatient->_ref_echange_hprim = $echg_hprim;
        $msgAcq                                       = $domEnregistrementPatient->enregistrementPatient(
            $dom_acq,
            $newPatient,
            $data
        );
        if ($echg_hprim->statut_acquittement != "OK") {
            return $msgAcq;
        }

        // Cas 2 : Traitement de la venue
        $dom_acq                        = new CHPrimXMLAcquittementsPatients();
        $dom_acq->_identifiant_acquitte = $data['identifiantMessage'];
        $dom_acq->_sous_type_evt        = $this->sous_type;
        $dom_acq->_ref_echange_hprim    = $echg_hprim;

        // Traitement du message des erreurs
        $avertissement = $msgID400 = $msgVenue = $msgNDA = "";
        $_code_Venue   = $_code_NumDos = $_num_dos_create = $_modif_venue = false;

        $sender = $echg_hprim->_ref_sender;
        $sender->loadConfigValues();
        $this->_ref_sender = $sender;

        $idSourceVenue = $data['idSourceVenue'];
        $idCibleVenue  = $data['idCibleVenue'];

        if (!$newVenue) {
            $newVenue = new CSejour();
        }

        // Cas d'une annulation
        $cancel = false;
        if ($data['action'] == "suppression") {
            $cancel = true;
        }

        // Affectation du patient
        $newVenue->patient_id = $newPatient->_id;
        // Affectation de l'établissement
        $newVenue->group_id = $sender->group_id;

        $commentaire = "";
        $codes       = [];

        // Acquittement d'erreur : identifiants source et cible non fournis pour le patient / venue
        if (!$idSourceVenue && !$idCibleVenue) {
            return $echg_hprim->setAckError($dom_acq, "E100", $commentaire, $newVenue);
        }

        $nda = CIdSante400::getMatch("CSejour", $sender->_tag_sejour, $idSourceVenue);
        // idSource non connu
        if (!$nda->_id) {
            // idCible fourni
            if ($idCibleVenue) {
                if ($newVenue->load($idCibleVenue)) {
                    // Dans le cas d'une annulation de la venue
                    if ($cancel) {
                        if ($msgAcq = $this->doNotCancelVenue($newVenue, $dom_acq, $echg_hprim)) {
                            return $msgAcq;
                        }
                    }

                    // Recherche d'un num dossier déjà existant pour cette venue
                    // Mise en trash du numéro de dossier reçu
                    $newVenue->loadNDA();
                    if ($this->trashNDA($newVenue, $sender)) {
                        $nda->_trash = true;
                    } else {
                        // Mapping du séjour si pas de numéro de dossier
                        $newVenue = $this->mappingVenue($data['venue'], $newVenue, $cancel);

                        $msgVenue    = CEAISejour::storeSejour($newVenue, $sender);
                        $commentaire = CEAISejour::getComment($newVenue);

                        $_code_NumDos = "I121";
                        $_code_Venue  = true;
                    }
                } else {
                    $_code_NumDos = "I120";
                }
            } else {
                $_code_NumDos = "I122";
            }
            if (!$newVenue->_id) {
                // Mapping du séjour
                $newVenue->_NDA = $nda->id400;
                $newVenue       = $this->mappingVenue($data['venue'], $newVenue, $cancel);

                // Séjour retrouvé
                if (CAppUI::conf("hprimxml strictSejourMatch")) {
                    if ($newVenue->loadMatchingSejour(null, true, $sender->_configs["use_sortie_matching"])) {
                        // Dans le cas d'une annulation de la venue
                        if ($cancel) {
                            if ($msgAcq = $this->doNotCancelVenue($newVenue, $dom_acq, $echg_hprim)) {
                                return $msgAcq;
                            }
                        }

                        // Recherche d'un num dossier déjà existant pour cette venue
                        // Mise en trash du numéro de dossier reçu
                        $newVenue->loadNDA();
                        if ($this->trashNDA($newVenue, $sender)) {
                            $nda->_trash = true;
                        } else {
                            // Mapping du séjour
                            $newVenue = $this->mappingVenue($data['venue'], $newVenue, $cancel);

                            $msgVenue    = CEAISejour::storeSejour($newVenue, $sender);
                            $commentaire = CEAISejour::getComment($newVenue);

                            $_code_NumDos = "A121";
                            $_code_Venue  = true;
                        }
                    }
                } else {
                    $collision = $newVenue->getCollisions();

                    if (count($collision) == 1) {
                        $newVenue = reset($collision);
                        // Dans le cas d'une annulation de la venue
                        if ($cancel) {
                            if ($msgAcq = $this->doNotCancelVenue($newVenue, $dom_acq, $echg_hprim)) {
                                return $msgAcq;
                            }
                        }

                        // Recherche d'un num dossier déjà existant pour cette venue
                        // Mise en trash du numéro de dossier reçu
                        $newVenue->loadNDA();
                        if ($this->trashNDA($newVenue, $sender)) {
                            $nda->_trash = true;
                        } else {
                            // Mapping du séjour
                            $newVenue = $this->mappingVenue($data['venue'], $newVenue, $cancel);

                            $msgVenue    = CEAISejour::storeSejour($newVenue, $sender);
                            $commentaire = CEAISejour::getComment($newVenue);

                            $_code_NumDos = "A122";
                            $_code_Venue  = true;
                        }
                    }
                }
                if (!$newVenue->_id && !isset($nda->_trash)) {
                    // Mapping du séjour
                    $newVenue = $this->mappingVenue($data['venue'], $newVenue, $cancel);

                    $msgVenue    = CEAISejour::storeSejour($newVenue, $sender);
                    $commentaire = CEAISejour::getComment($newVenue);
                }
            }

            if (isset($nda->_trash)) {
                $nda->tag = CAppUI::conf('dPplanningOp CSejour tag_dossier_trash') . $sender->_tag_sejour;
                $nda->loadMatchingObject();
                $codes       = ["I125"];
                $commentaire = "Sejour non récupéré. Impossible d'associer le numéro de dossier.";
            }

            if ($cancel) {
                $codes[]  = "A130";
                $nda->tag = CAppUI::conf('dPplanningOp CSejour tag_dossier_trash') . $sender->_tag_sejour;
            }

            $msgNDA = CEAISejour::storeNDA($nda, $newVenue, $sender);

            if (!isset($nda->_trash)) {
                $codes = [
                    $msgVenue ? ($_code_Venue ? "A103" : "A102") : ($_code_Venue ? "I102" : "I101"),
                    $msgNDA ? "A105" : $_code_NumDos,
                ];
            }

            if ($msgVenue || $msgNDA) {
                $avertissement = $msgVenue . " " . $msgNDA;
            } else {
                if (!isset($nda->_trash)) {
                    $commentaire .= "Numéro dossier créé : $nda->id400.";
                }
            }
        } // idSource connu
        else {
            $newVenue->_NDA = $nda->id400;
            $newVenue->load($nda->object_id);
            // Dans le cas d'une annulation de la venue
            if ($cancel) {
                if ($msgAcq = $this->doNotCancelVenue($newVenue, $dom_acq, $echg_hprim)) {
                    return $msgAcq;
                }
            }

            // Mapping du séjour
            $newVenue = $this->mappingVenue($data['venue'], $newVenue, $cancel);

            // idCible non fourni
            if (!$idCibleVenue) {
                $_code_NumDos = "I123";
            } else {
                $tmpVenue = new CSejour();
                // idCible connu
                if ($tmpVenue->load($idCibleVenue)) {
                    if ($tmpVenue->_id != $nda->object_id) {
                        $commentaire = "L'identifiant source fait référence au séjour : $nda->object_id";
                        $commentaire .= "et l'identifiant cible au séjour : $tmpVenue->_id.";

                        return $dom_acq->generateAcquittementsError("E104", $commentaire, $newVenue);
                    }
                    $_code_NumDos = "I124";
                } // idCible non connu
                else {
                    $_code_NumDos = "A120";
                }
            }

            $msgVenue = CEAISejour::storeSejour($newVenue, $sender);

            $codes = [$msgVenue ? "A103" : "I102", $_code_NumDos];

            if ($cancel) {
                $codes[]  = "A130";
                $nda->tag = CAppUI::conf('dPplanningOp CSejour tag_dossier_trash') . $sender->_tag_sejour;
                $nda->loadMatchingObject();
                $msgNDA = $nda->store();
            }

            if ($msgVenue || $msgNDA) {
                $avertissement = $msgVenue . " " . $msgNDA;
            }

            $commentaire = CEAISejour::getComment($newVenue);
        }

        return $echg_hprim->setAck($dom_acq, $codes, $avertissement, $commentaire, $newVenue);
    }
}
