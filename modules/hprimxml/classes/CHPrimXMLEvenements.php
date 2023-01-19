<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use DOMNode;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;
use Ox\Core\CMbSecurity;
use Ox\Interop\Eai\Tools\CDoctorTrait;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPaysInsee;

/**
 * Class CHPrimXMLEvenements
 */
class CHPrimXMLEvenements extends CHPrimXMLDocument
{
    use CDoctorTrait;

    static $documentElements = [
        'evenementsPatients'           => "CHPrimXMLEvenementsPatients",
        'evenementsServeurActes'       => "CHPrimXMLEvenementsServeurActivitePmsi",
        'evenementsPMSI'               => "CHPrimXMLEvenementsServeurActivitePmsi",
        'evenementsFraisDivers'        => "CHPrimXMLEvenementsServeurActivitePmsi",
        'evenementServeurIntervention' => "CHPrimXMLEvenementsServeurActivitePmsi",
    ];

    /**
     * Récupération des évènements disponibles
     *
     * @return array
     */
    function getDocumentElements()
    {
        return self::$documentElements;
    }

    /**
     * Construction de l'entête du message
     *
     * @param string $type     Type de l'évènement
     * @param bool   $version  Version
     * @param int    $group_id Group id
     *
     * @return void
     */
    function generateEnteteMessage($type = null, $version = true, $group_id = null)
    {
        $evenements = $this->addElement($this, $type, null, "http://www.hprim.org/hprimXML");
        if ($version) {
            $this->addAttribute($evenements, "version", CAppUI::conf("hprimxml $this->evenement version"));
        }

        $this->addEnteteMessage($evenements, $group_id);
    }

    /**
     * Récupération des élèments de l'entête du message
     *
     * @param string $type Type de l'évènement
     *
     * @return array
     */
    function getEnteteEvenementXML($type)
    {
        $data  = [];
        $xpath = new CHPrimXPath($this);

        $entete = $xpath->queryUniqueNode("/hprim:$type/hprim:enteteMessage");

        $data['dateHeureProduction'] = CMbDT::dateTime($xpath->queryTextNode("hprim:dateHeureProduction", $entete));
        $data['identifiantMessage']  = $xpath->queryTextNode("hprim:identifiantMessage", $entete);
        $agents                      = $xpath->queryUniqueNode("hprim:emetteur/hprim:agents", $entete);
        $systeme                     = $xpath->queryUniqueNode(
            "hprim:agent[@categorie='" . $this->getAttSysteme() . "']",
            $agents,
            false
        );
        $this->destinataire          = $data['idClient'] = $xpath->queryTextNode("hprim:code", $systeme);
        $data['libelleClient']       = $xpath->queryTextNode("hprim:libelle", $systeme);

        return $data;
    }

    /**
     * Récupération de l'action de l'évènement
     *
     * @param string  $query Query
     * @param DOMNode $node  Node
     *
     * @return string
     */
    function getActionEvenement($query, DOMNode $node)
    {
        $xpath = new CHPrimXPath($node->ownerDocument);

        return $xpath->queryAttributNode($query, $node, "action");
    }

    /**
     * Est-ce que l'action est possible par rapport à l'évènement ?
     *
     * @param string                 $action  Action
     * @param CHPrimXMLAcquittements $dom_acq Acquittement
     *
     * @return null|string
     * @throws Exception
     */
    function isActionValide($action, CHPrimXMLAcquittements $dom_acq)
    {
        $acq           = null;
        $echange_hprim = $this->_ref_echange_hprim;

        if (!$action || array_key_exists($action, $this->actions)) {
            return $acq;
        }

        $acq       = $dom_acq->generateAcquittements("erreur", "E008");
        $doc_valid = $dom_acq->schemaValidate(null, false, $this->_ref_receiver->display_errors);

        $echange_hprim->acquittement_valide = $doc_valid ? 1 : 0;
        $echange_hprim->_acquittement       = $acq;
        $echange_hprim->statut_acquittement = "erreur";
        $echange_hprim->store();

        return $acq;
    }

    /**
     * Récupération de la date et heure
     *
     * @param DOMNode $node Node
     *
     * @return string
     */
    function getDateHeure(DOMNode $node)
    {
        if (!$node) {
            return null;
        }

        $date  = $this->getDate($node);
        $heure = $this->getHeure($node);

        if (!$date) {
            return null;
        }

        if (!$heure) {
            "00:00:00";
        }

        return "$date $heure";
    }

    /**
     * Récupération de la date
     *
     * @param DOMNode $node Node
     *
     * @return string
     */
    function getDate(DOMNode $node)
    {
        $xpath = new CHPrimXPath($node->ownerDocument);

        return $xpath->queryTextNode("hprim:date", $node);
    }

    /**
     * Récupération de l'heure
     *
     * @param DOMNode $node Node
     *
     * @return string
     */
    function getHeure(DOMNode $node)
    {
        $xpath = new CHPrimXPath($node->ownerDocument);

        $heure = $xpath->queryTextNode("hprim:heure", $node);
        if ($heure) {
            return CMbDT::time($heure);
        }

        return null;
    }

    /**
     * Récupération du médecin
     *
     * @param DOMNode $node Node
     *
     * @return int
     */
    public function getMedecin(DOMNode $node): ?int
    {
        $sender = $this->_ref_sender;

        $xpath = new CHPrimXPath($node->ownerDocument);

        $personne = $xpath->queryUniqueNode("hprim:personne", $node, false);
        $last_name = $xpath->queryTextNode("hprim:nomUsuel", $personne);
        $prenoms   = $xpath->getMultipleTextNodes("hprim:prenoms/*", $personne);
        $first_name = CMbArray::get($prenoms, 0);

        $doctors = [
            'RPPS'       => $xpath->queryTextNode("hprim:noRPPS", $node),
            'ADELI'      => $xpath->queryTextNode("hprim:numeroAdeli", $node),
            'RI'         => $xpath->queryTextNode("hprim:identification/hprim:code", $node),
            'last_name'  => $last_name,
            'first_name' => $first_name,
        ];

        return $this->getDoctorID($doctors, new CMediusers(), $sender->group_id, true);
    }

    /**
     * Return person
     *
     * @param DOMNode   $node       Node
     * @param CMbObject $mbPersonne Person
     *
     * @return CMbObject|CMediusers|CPatient
     */
    static function getPersonne(DOMNode $node, CMbObject $mbPersonne)
    {
        $xpath = new CHPrimXPath($node->ownerDocument);

        $civilite                = $xpath->queryAttributNode("hprim:civiliteHprim", $node, "valeur");
        $civiliteHprimConversion = [
            "mme"  => "mme",
            "mlle" => "mlle",
            "mr"   => "m",
            "dr"   => "dr",
            "pr"   => "pr",
            "bb"   => "enf",
            "enf"  => "enf",
        ];
        $nom                     = $xpath->queryTextNode("hprim:nomUsuel", $node);
        $prenoms                 = $xpath->getMultipleTextNodes("hprim:prenoms/*", $node);
        $adresses                = $xpath->queryUniqueNode("hprim:adresses", $node);
        $adresse                 = $xpath->queryUniqueNode("hprim:adresse", $adresses);
        $ligne                   = $xpath->getMultipleTextNodes("hprim:ligne", $adresse, true);
        $ville                   = $xpath->queryTextNode("hprim:ville", $adresse);
        $cp                      = $xpath->queryTextNode("hprim:codePostal", $adresse);
        if ($cp) {
            $cp = preg_replace("/[^0-9]/", "", $cp);
        }
        $telephones = $xpath->getMultipleTextNodes("hprim:telephones/*", $node);
        $email      = $xpath->getFirstTextNode("hprim:emails/*", $node);

        if ($mbPersonne instanceof CPatient) {
            if ($civilite) {
                $mbPersonne->civilite = $civiliteHprimConversion[$civilite];
            } else {
                if ($mbPersonne->civilite == null) {
                    $mbPersonne->civilite = "guess";
                }
            }
            $mbPersonne->nom             = $nom;
            $mbPersonne->nom_jeune_fille = $xpath->queryTextNode("hprim:nomNaissance", $node);
            $mbPersonne->prenom          = CMbArray::get($prenoms, 0);
            $mbPersonne->prenoms         = trim(implode(' ', [CMbArray::get($prenoms, 1), CMbArray::get($prenoms, 2)]));
            $mbPersonne->adresse         = $ligne;
            $mbPersonne->ville           = $ville;
            $mbPersonne->pays_insee      = $xpath->queryTextNode("hprim:pays", $adresse);
            $pays                        = new CPaysInsee();
            $pays->numerique             = $mbPersonne->pays_insee;
            $pays->loadMatchingObject();

            $mbPersonne->pays = $pays->nom_fr;
            $mbPersonne->cp   = $cp;

            $tel1 = $tel2 = null;
            if (isset($telephones[0])) {
                $tel1 = $telephones[0];
            }

            if (isset($telephones[1])) {
                $tel2 = $telephones[1];
            }
            $mbPersonne->tel  = ($tel1 != $mbPersonne->tel2 && strlen($tel1) <= 10) ? $tel1 : null;
            $mbPersonne->tel2 = ($tel2 != $mbPersonne->tel && strlen($tel2) <= 10) ? $tel2 : null;

            if (strlen($tel1) > 10) {
                $mbPersonne->tel_autre = $tel1;
            }
            if (strlen($tel2) > 10) {
                $mbPersonne->tel_autre = $tel2;
            }

            $mbPersonne->email = $email;
        } elseif ($mbPersonne instanceof CMediusers) {
            $mbPersonne->_user_last_name  = $nom;
            $mbPersonne->_user_first_name = CMbArray::get($prenoms, 0);
            $mbPersonne->_user_email      = $email;
            $mbPersonne->_user_phone      = CMbArray::get($telephones, 0);
            $mbPersonne->_user_adresse    = $ligne;
            $mbPersonne->_user_cp         = $cp;
            $mbPersonne->_user_ville      = $ville;
        }

        return $mbPersonne;
    }

    /**
     * Création du praticien
     *
     * @param CMediusers $mediuser Mediuser
     *
     * @return int
     */
    function createPraticien(CMediusers $mediuser)
    {
        $sender = $this->_ref_echange_hprim->_ref_sender;

        $functions           = new CFunctions();
        $functions->text     = CAppUI::conf("hprimxml functionPratImport");
        $functions->group_id = $sender->group_id;
        $functions->loadMatchingObject();
        if (!$functions->loadMatchingObject()) {
            $functions->type            = "cabinet";
            $functions->compta_partagee = 0;
            $functions->store();
        }
        $mediuser->function_id    = $functions->_id;
        $mediuser->_user_username = CMbFieldSpec::randomString(
            array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z')),
            20
        );
        $mediuser->_user_password = CMbSecurity::getRandomPassword();
        $user_type                = CAppUI::conf("hprimxml user_type");
        $mediuser->_user_type     = $user_type ? $user_type : 13; // Medecin
        $mediuser->actif          = CAppUI::conf("hprimxml medecinActif") ? 1 : 0;
        $user                     = new CUser();
        $user->user_last_name     = $mediuser->_user_last_name;
        $user->user_first_name    = $mediuser->_user_first_name;
        $listPrat                 = $user->seek("$user->user_last_name $user->user_first_name");
        if (count($listPrat) == 1) {
            $user = reset($listPrat);
            $user->loadRefMediuser();
            $mediuser = $user->_ref_mediuser;
        } else {
            $mediuser->store();
        }

        return $mediuser->_id;
    }
}
