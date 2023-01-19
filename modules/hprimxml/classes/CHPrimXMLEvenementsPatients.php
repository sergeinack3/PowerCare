<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use DOMElement;
use DOMNode;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Interop\Eai\CInteropSender;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CChambre;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CINSPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CHPrimXMLEvenementsPatients
 * Patients
 */
class CHPrimXMLEvenementsPatients extends CHPrimXMLEvenements
{
    /**
     * Construct
     *
     * @return CHPrimXMLEvenementsPatients
     */
    function __construct()
    {
        $this->evenement = "evt_patients";
        $this->type      = "patients";

        $version = CAppUI::conf('hprimxml evt_patients version');

        parent::__construct(
            "patients/v" . str_replace(".", "_", $version),
            self::getVersionEvenementsPatients()
        );
    }

    /**
     * Get version
     *
     * @return string
     */
    static function getVersionEvenementsPatients()
    {
        return "msgEvenementsPatients" . str_replace(".", "", CAppUI::conf('hprimxml evt_patients version'));
    }

    /**
     * Récupérer les personnes à prévenir
     *
     * @param DOMNode  $node      Node
     * @param CPatient $mbPatient Patient
     *
     * @return CPatient
     */
    static function getPersonnesPrevenir(DOMNode $node, CPatient $mbPatient)
    {
        $xpath = new CHPrimXPath($node->ownerDocument);

        $personnesPrevenir = $xpath->query("hprim:personnesPrevenir/*", $node);

        foreach ($personnesPrevenir as $personnePrevenir) {
            $prevenir           = new CCorrespondantPatient;
            $prevenir->relation = "prevenir";
            $prevenir->nom      = $xpath->queryTextNode("hprim:nomUsuel", $personnePrevenir);
            $prenoms            = $xpath->getMultipleTextNodes("hprim:prenoms/*", $personnePrevenir);
            $prevenir->prenom   = CMbArray::get($prenoms, 0);

            $adresses          = $xpath->queryUniqueNode("hprim:adresses", $personnePrevenir);
            $adresse           = $xpath->queryUniqueNode("hprim:adresse", $adresses);
            $prevenir->adresse = $xpath->queryTextNode("hprim:ligne", $adresse);
            $prevenir->ville   = $xpath->queryTextNode("hprim:ville", $adresse);
            $prevenir->cp      = $xpath->queryTextNode("hprim:codePostal", $adresse);

            $telephones    = $xpath->getMultipleTextNodes("hprim:telephones/*", $personnePrevenir);
            $prevenir->tel = CMbArray::get($telephones, 0);

            $mbPatient->_ref_correspondants_patient[] = $prevenir;
        }

        return $mbPatient;
    }

    /**
     * Get admit attributes
     *
     * @param DOMNode $node Node
     *
     * @return array
     */
    static function getAttributesVenue(DOMNode $node)
    {
        $xpath = new CHPrimXPath($node->ownerDocument);

        $attributes                               = [];
        $attributes['confidentiel']               = $xpath->getValueAttributNode($node, "confidentiel");
        $attributes['etat']                       = $xpath->getValueAttributNode($node, "etat");
        $attributes['facturable']                 = $xpath->getValueAttributNode($node, "facturable");
        $attributes['declarationMedecinTraitant'] = $xpath->getValueAttributNode($node, "declarationMedecinTraitant");

        return $attributes;
    }

    /**
     * Est-ce que la venue à un praticien ?
     *
     * @param DOMNode $node Node
     *
     * @return bool
     */
    static function isVenuePraticien(DOMNode $node)
    {
        $xpath = new CHPrimXPath($node->ownerDocument);

        $medecins = $xpath->queryUniqueNode("hprim:medecins", $node);

        if (!$medecins instanceof DOMNode) {
            return false;
        }

        $medecin = $medecins->childNodes;
        foreach ($medecin as $_med) {
            $lien = $xpath->getValueAttributNode($_med, "lien");
            if ($lien != "rsp") {
                return false;
            }
        }

        return true;
    }

    /**
     * Get events
     *
     * @return array
     */
    function getEvenements()
    {
        return self::$evenements;
    }

    /**
     * @inheritdoc
     */
    function generateEnteteMessage($type = null, $version = true, $group_id = null)
    {
        parent::generateEnteteMessage("evenementsPatients", false, $group_id);
    }

    /**
     * Mapping patient
     *
     * @param DOMNode  $node      Node
     * @param CPatient $mbPatient Patient
     *
     * @return CPatient
     */
    function mappingPatient(DOMNode $node, CPatient $mbPatient)
    {
        $sender = $this->_ref_echange_hprim->_ref_sender;

        $mbPatient = $this->getPersonnePhysique($node, $mbPatient, $sender);
        $mbPatient = $this->getActiviteSocioProfessionnelle($node, $mbPatient);
        //$mbPatient = $this->getPersonnesPrevenir($node, $mbPatient);

        if (isset($sender->_configs) && array_key_exists(
                "fully_qualified",
                $sender->_configs
            ) && !$sender->_configs["fully_qualified"]) {
            $mbPatient->nullifyAlteredFields();
        }

        return $mbPatient;
    }

    /**
     * Get
     *
     * @param DOMNode        $node      Node
     * @param CPatient       $mbPatient Person
     * @param CInteropSender $sender    Sender
     *
     * @return CMbObject|CMediusers|CPatient
     */
    static function getPersonnePhysique(DOMNode $node, CPatient $mbPatient, CInteropSender $sender)
    {
        $xpath = new CHPrimXPath($node->ownerDocument);

        // Création de l'element personnePhysique
        $personnePhysique = $xpath->queryUniqueNode("hprim:personnePhysique", $node);

        $sexe = $xpath->queryAttributNode("hprim:personnePhysique", $node, "sexe");
        if (!$sexe) {
            $sexe = "M";
        }
        $sexeConversion  = [
            "M" => "m",
            "F" => "f",
        ];
        $mbPatient->sexe = $sexeConversion[$sexe];

        // Récupération du typePersonne
        $mbPatient = self::getPersonne($personnePhysique, $mbPatient);

        $elementDateNaissance = $xpath->queryUniqueNode("hprim:dateNaissance", $personnePhysique);
        $mbPatient->naissance = $xpath->queryTextNode("hprim:date", $elementDateNaissance);

        $lieuNaissance                   = $xpath->queryUniqueNode("hprim:lieuNaissance", $personnePhysique);
        $mbPatient->lieu_naissance       = $xpath->queryTextNode("hprim:ville", $lieuNaissance);
        $mbPatient->pays_naissance_insee = $xpath->queryTextNode("hprim:pays", $lieuNaissance);
        $mbPatient->cp_naissance         = $xpath->queryTextNode("hprim:codePostal", $lieuNaissance);

        if (!$mbPatient->rang_naissance && CMbArray::get($sender->_configs, 'force_birth_rank_if_null')) {
            $mbPatient->rang_naissance = 1;
        }

        return $mbPatient;
    }

    /**
     * Récupérer l'activité socio-professionnelle
     *
     * @param DOMNode  $node      Node
     * @param CPatient $mbPatient Patient
     *
     * @return CPatient
     */
    static function getActiviteSocioProfessionnelle(DOMNode $node, CPatient $mbPatient)
    {
        $xpath = new CHPrimXPath($node->ownerDocument);

        $activiteSocioProfessionnelle = $xpath->queryTextNode("hprim:activiteSocioProfessionnelle", $node);

        $mbPatient->profession = $activiteSocioProfessionnelle ? $activiteSocioProfessionnelle : null;

        return $mbPatient;
    }

    /**
     * Vérifier si les patients sont similaires
     *
     * @param CPatient $mbPatient  Patient
     * @param DOMNode  $xmlPatient Patient provenant des données XML
     *
     * @return string
     */
    function checkSimilarPatient(CPatient $mbPatient, $xmlPatient)
    {
        $sender = $this->_ref_sender;

        if (!$sender->_configs || (isset($sender->_configs) && array_key_exists(
                    "check_similar",
                    $sender->_configs
                ) && !$sender->_configs["check_similar"])) {
            return null;
        }

        $xpath = new CHPrimXPath($xmlPatient->ownerDocument);

        // Création de l'element personnePhysique
        $personnePhysique = $xpath->queryUniqueNode("hprim:personnePhysique", $xmlPatient);

        $nom     = $xpath->queryTextNode("hprim:nomUsuel", $personnePhysique);
        $prenoms = $xpath->getMultipleTextNodes("hprim:prenoms/*", $personnePhysique);
        $prenom  = CMbArray::get($prenoms, 0);

        $commentaire = null;

        if (!$mbPatient->checkSimilar($nom, $prenom)) {
            $commentaire = "Le nom ($nom/$mbPatient->nom) et/ou le prénom ($prenom/$mbPatient->prenom) sont très différents.";
        }

        return $commentaire;
    }

    /**
     * Get source ID
     *
     * @param string $query_evt  Event
     * @param string $query_type Type
     *
     * @return string
     */
    function getIdSourceObject($query_evt, $query_type)
    {
        $xpath = new CHPrimXPath($this);

        $query = "/hprim:evenementsPatients/hprim:evenementPatient";

        $evenementPatient = $xpath->queryUniqueNode($query);
        $typeEvenement    = $xpath->queryUniqueNode($query_evt, $evenementPatient);

        $object = $xpath->queryUniqueNode($query_type, $typeEvenement);

        return $this->getIdSource($object);
    }

    /**
     * Mapping admit
     *
     * @param DOMNode $node    Node
     * @param CSejour $mbVenue Admit
     * @param bool    $cancel  Cancel
     *
     * @return CSejour
     */
    function mappingVenue(DOMNode $node, CSejour $mbVenue, $cancel = false)
    {
        // Si annulation
        if ($cancel) {
            $mbVenue->annule = 1;

            return $mbVenue;
        }

        $mbVenue = $this->getNatureVenue($node, $mbVenue);
        $mbVenue = self::getEntree($node, $mbVenue);
        $mbVenue = $this->getMedecins($node, $mbVenue);
        $mbVenue = self::getPlacement($node, $mbVenue);
        $mbVenue = self::getSortie($node, $mbVenue);

        /* TODO Supprimer ceci après l'ajout des times picker */
        $mbVenue->_hour_entree_prevue = null;
        $mbVenue->_min_entree_prevue  = null;
        $mbVenue->_hour_sortie_prevue = null;
        $mbVenue->_min_sortie_prevue  = null;

        return $mbVenue;
    }

    /**
     * Récupération de la nature de la venue
     *
     * @param DOMNode $node    Node
     * @param CSejour $mbVenue Venue
     *
     * @return CSejour
     */
    function getNatureVenue(DOMNode $node, CSejour $mbVenue)
    {
        $xpath = new CHPrimXPath($node->ownerDocument);

        if ((CAppUI::conf("dPpmsi passage_facture") == "reception") && self::getEtatVenue($node) == "clôturée") {
            $mbVenue->facture = 1;
        }

        $sender = $this->_ref_echange_hprim->_ref_sender;

        // Obligatoire pour MB
        $nature               = $xpath->queryAttributNode("hprim:natureVenueHprim", $node, "valeur", "", false);
        $attrNatureVenueHprim = [
            "hsp"  => "comp",
            "cslt" => "consult",
            "sc"   => "seances",
            "ambu" => "ambu",
            "exte" => "exte",
            "urg"  => "urg",
        ];

        // Détermine le type de venue depuis la config des numéros de dossier
        $type_config = self::getVenueType($sender, $mbVenue->_NDA);
        if ($type_config) {
            $mbVenue->type = $type_config;
        }

        // Cas des urgences : dans tous les cas ce sera de l'hospi comp.
        $rpu = $mbVenue->loadRefRPU();
        if ($rpu && $rpu->_id && $rpu->sejour_id == $rpu->mutation_sejour_id) {
            $mbVenue->type = "comp";
        }

        if (!$mbVenue->type) {
            if ($nature) {
                $mbVenue->type = $attrNatureVenueHprim[$nature];
            }
        }

        if (!$mbVenue->type) {
            $mbVenue->type = "comp";
        }

        return $mbVenue;
    }

    /**
     * Get admit state
     *
     * @param DOMNode $node Node
     *
     * @return string
     */
    static function getEtatVenue(DOMNode $node)
    {
        $xpath = new CHPrimXPath($node->ownerDocument);

        return $xpath->getValueAttributNode($node, "etat");
    }

    /**
     * Mapping des types de la venue
     *
     * @param CInteropSender $sender Sender
     * @param string         $nda    NDA
     *
     * @return string|null
     */
    static function getVenueType(CInteropSender $sender, $nda)
    {
        $types = [
            "type_sej_hospi"   => "comp",
            "type_sej_ambu"    => "ambu",
            "type_sej_urg"     => "urg",
            "type_sej_exte"    => "exte",
            "type_sej_scanner" => "seances",
            "type_sej_chimio"  => "seances",
            "type_sej_dialyse" => "seances",
        ];

        if (!$sender->_configs) {
            return null;
        }

        foreach ($types as $config => $type) {
            if (!$sender->_configs[$config]) {
                continue;
            }

            if (preg_match($sender->_configs[$config], $nda)) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Récupération de l'entrée
     *
     * @param DOMNode $node    Node
     * @param CSejour $mbVenue Venue
     *
     * @return CSejour
     */
    static function getEntree(DOMNode $node, CSejour $mbVenue)
    {
        $xpath = new CHPrimXPath($node->ownerDocument);

        $entree = $xpath->queryUniqueNode("hprim:entree", $node);

        $date  = $xpath->queryTextNode("hprim:dateHeureOptionnelle/hprim:date", $entree);
        $heure = CMbDT::transform(
            $xpath->queryTextNode("hprim:dateHeureOptionnelle/hprim:heure", $entree),
            null,
            "%H:%M:%S"
        );

        $xpath->queryAttributNode("hprim:modeEntree", $entree, "valeur");

        $dateHeure = "$date $heure";

        if (CAppUI::conf("hprimxml notifier_entree_reelle") &&
            (self::getEtatVenue($node) == "encours" || self::getEtatVenue($node) == "clôturée")
        ) {
            $mbVenue->entree_reelle = $dateHeure;
        } else {
            $mbVenue->entree_prevue = $dateHeure;
        }

        return $mbVenue;
    }

    /**
     * Récupération des médecins
     *
     * @param DOMNode $node    Node
     * @param CSejour $mbVenue Venue
     *
     * @return CSejour
     */
    function getMedecins(DOMNode $node, CSejour $mbVenue)
    {
        $xpath = new CHPrimXPath($node->ownerDocument);

        $sender   = $this->_ref_echange_hprim->_ref_sender;
        $medecins = $xpath->queryUniqueNode("hprim:medecins", $node);
        if ($medecins instanceof DOMElement) {
            $medecin = $medecins->childNodes;

            foreach ($medecin as $_med) {
                $mediuser_id = $this->getMedecin($_med);
                $lien        = $xpath->getValueAttributNode($_med, "lien");
                if ($lien == "rsp") {
                    $mbVenue->praticien_id = $mediuser_id;
                }
            }
        }

        // Dans le cas ou la venue ne contient pas de medecin responsable
        // Attribution d'un medecin indeterminé
        if (!$mbVenue->praticien_id) {
            $user                 = new CUser();
            $mediuser             = new CMediusers();
            $user->user_last_name = CAppUI::conf("hprimxml medecinIndetermine") . " $sender->group_id";
            if (!$user->loadMatchingObject()) {
                $mediuser->_user_last_name = $user->user_last_name;
                $mediuser->_id             = $this->createPraticien($mediuser);
            } else {
                $user->loadRefMediuser();
                $mediuser = $user->_ref_mediuser;
            }
            $mbVenue->praticien_id = $mediuser->_id;
        }

        return $mbVenue;
    }

    /**
     * Récupération du placement
     *
     * @param DOMNode $node    Node
     * @param CSejour $mbVenue Venue
     *
     * @return CSejour
     */
    static function getPlacement(DOMNode $node, CSejour $mbVenue)
    {
        $xpath = new CHPrimXPath($node->ownerDocument);

        $placement = $xpath->queryUniqueNode("hprim:Placement", $node);

        if ($placement) {
            $mbVenue->modalite = $xpath->queryAttributNode(
                "hprim:modePlacement",
                $placement,
                "modaliteHospitalisation"
            );
        }

        return $mbVenue;
    }

    /**
     * Récupération de la sortie
     *
     * @param DOMNode $node    Node
     * @param CSejour $mbVenue Venue
     *
     * @return CSejour
     */
    static function getSortie(DOMNode $node, CSejour $mbVenue)
    {
        $xpath = new CHPrimXPath($node->ownerDocument);

        $sortie = $xpath->queryUniqueNode("hprim:sortie", $node);

        $date  = $xpath->queryTextNode("hprim:dateHeureOptionnelle/hprim:date", $sortie);
        $heure = CMbDT::transform(
            $xpath->queryTextNode("hprim:dateHeureOptionnelle/hprim:heure", $sortie),
            null,
            "%H:%M:%S"
        );
        if ($date) {
            $dateHeure = "$date $heure";
        } elseif (!$date && !$mbVenue->sortie_prevue) {
            $addDateTime = CAppUI::gconf("dPplanningOp CSejour sortie_prevue " . $mbVenue->type);
            switch ($addDateTime) {
                case "1/4":
                    $addDateTime = "00:15:00";
                    break;
                case "1/2":
                    $addDateTime = "00:30:00";
                    break;
                default:
                    $addDateTime = $addDateTime . ":00:00";
            }
            $dateHeure = CMbDT::addDateTime(
                $addDateTime,
                $mbVenue->entree_reelle ? $mbVenue->entree_reelle : $mbVenue->entree_prevue
            );
        } else {
            $dateHeure = $mbVenue->sortie_reelle ? $mbVenue->sortie_reelle : $mbVenue->sortie_prevue;
        }

        // Cas dans lequel on ne récupère pas de sortie tant que l'on a pas la sortie réelle
        if (CAppUI::conf("hprimxml notifier_sortie_reelle") && self::getEtatVenue($node) == "clôturée") {
            $mbVenue->sortie_reelle = $dateHeure;
        } else {
            $mbVenue->sortie_prevue = $dateHeure;
        }

        $modeSortieHprim = $xpath->queryAttributNode("hprim:modeSortieHprim", $sortie, "valeur");
        if (!$modeSortieHprim) {
            return $mbVenue;
        }
        // décès
        switch ($modeSortieHprim) {
            case "05":
                $mbVenue->mode_sortie = "deces";
                break;

            case "02":
                // autre transfert dans un autre CH
                $mbVenue->mode_sortie = "transfert";

                $destination = $xpath->queryUniqueNode("hprim:destination", $sortie);
                if ($destination) {
                    $mbVenue = self::getEtablissementTransfert($mbVenue);
                }
                break;

            default:
                //retour au domicile
                $mbVenue->mode_sortie = "normal";
                break;
        }

        return $mbVenue;
    }

    /**
     * Récupération de l'établissement de transfert
     *
     * @param CSejour $mbVenue Venue
     *
     * @return mixed
     */
    static function getEtablissementTransfert(CSejour $mbVenue)
    {
        return $mbVenue->etablissement_sortie_id;
    }

    /**
     * Admit ?
     *
     * @param CSejour $mbVenue Admit
     * @param array   $data    Datas
     *
     * @return bool
     */
    function admitFound(CSejour $mbVenue, $data)
    {
        $sender = $this->_ref_sender;

        $idSourceVenue = CValue::read($data, "idSourceVenue");
        $idCibleVenue  = CValue::read($data, "idCibleVenue");

        $NDA = new CIdSante400();
        if ($idSourceVenue) {
            $NDA = CIdSante400::getMatch("CSejour", $sender->_tag_sejour, $idSourceVenue);
        }

        if ($NDA->_id) {
            $mbVenue->load($NDA->object_id);

            return true;
        }

        if ($mbVenue->load($idCibleVenue)) {
            return true;
        }

        return false;
    }

    /**
     * Mapping mouvements
     *
     * @param DOMNode $node     Node
     * @param CSejour $newVenue Venue
     *
     * @return CSejour
     */
    function mappingMouvements(DOMNode $node, CSejour $newVenue)
    {
        $xpath = new CHPrimXPath($node->ownerDocument);

        $movements = $xpath->query("hprim:mouvement", $node);

        foreach ($movements as $_movement) {
            $affectation = new CAffectation();

            if ($msg = $this->mappingMovement($_movement, $newVenue, $affectation)) {
                return $msg;
            }
        }

        return null;
    }

    /**
     * Mapping mouvements
     *
     * @param DOMNode      $node        Node
     * @param CSejour      $newVenue    Venue
     * @param CAffectation $affectation Affectation
     *
     * @return string
     */
    function mappingMovement(DOMNode $node, CSejour $newVenue, CAffectation $affectation)
    {
        $xpath  = new CHPrimXPath($node->ownerDocument);
        $sender = $this->_ref_echange_hprim->_ref_sender;

        // Recherche d'une affectation existante
        $id = $newVenue->_guid . "-" . $xpath->queryTextNode("hprim:identifiant/hprim:emetteur", $node);

        $tag = $sender->_tag_hprimxml;

        $idex = CIdSante400::getMatch("CAffectation", $tag, $id);
        if ($idex->_id) {
            $affectation->load($idex->object_id);

            if ($affectation->sejour_id != $newVenue->_id) {
                return CAppUI::tr("hprimxml-error-E301");
            }
        }

        $affectation->sejour_id = $newVenue->_id;

        // Praticien responsable
        $medecinResponsable        = $xpath->queryUniqueNode("hprim:medecinResponsable", $node);
        $affectation->praticien_id = $this->getMedecin($medecinResponsable);

        // Emplacement
        $this->getEmplacement($node, $newVenue, $affectation);

        // Début de l'affectation
        $debut = $xpath->queryUniqueNode("hprim:debut", $node);
        $date  = $xpath->queryTextNode("hprim:date", $debut);
        $heure = CMbDT::transform($xpath->queryTextNode("hprim:heure", $debut), null, "%H:%M:%S");

        $affectation->entree = "$date $heure";

        // Fin de l'affectation
        $fin = $xpath->queryUniqueNode("hprim:fin", $node);
        if ($fin) {
            $date  = $xpath->queryTextNode("hprim:date", $fin);
            $heure = CMbDT::transform($xpath->queryTextNode("hprim:heure", $fin), null, "%H:%M:%S");

            $affectation->sortie = "$date $heure";
        }

        if (!$affectation->_id) {
            $affectation = $newVenue->forceAffectation($affectation, true);
            if (is_string($affectation)) {
                return $affectation;
            }
        } else {
            if ($msg = $affectation->store()) {
                return $msg;
            }
        }

        if (!$idex->_id) {
            $idex->object_id = $affectation->_id;
            if ($msg = $idex->store()) {
                return $msg;
            }
        }

        return null;
    }

    /**
     * Récupération de l'emplacement du patient
     *
     * @param DOMNode      $node        Node
     * @param CSejour      $newVenue    Sejour
     * @param CAffectation $affectation Affectation
     *
     * @return void
     */
    function getEmplacement(DOMNode $node, CSejour $newVenue, CAffectation $affectation)
    {
        $xpath  = new CHPrimXPath($node->ownerDocument);
        $sender = $this->_ref_echange_hprim->_ref_sender;

        $chambreSeul = $xpath->queryAttributNode("hprim:emplacement", $node, "chambreSeul");
        if ($chambreSeul) {
            $newVenue->chambre_seule = $chambreSeul == "oui" ? 1 : 0;
        }

        $emplacement = $xpath->queryUniqueNode("hprim:emplacement", $node);

        // Récupération de la chambre
        $chambre_node = $xpath->queryUniqueNode("hprim:chambre", $emplacement);
        $nom_chambre  = $xpath->queryTextNode("hprim:code", $chambre_node);
        $chambre      = new CChambre();

        // Récupération du lit
        $lit_node = $xpath->queryUniqueNode("hprim:lit", $emplacement);
        $nom_lit  = $xpath->queryTextNode("hprim:code", $lit_node);
        $lit      = new CLit();

        $where                   = $ljoin = [];
        $ljoin["service"]        = "service.service_id = chambre.service_id";
        $where["chambre.nom"]    = " = '$nom_chambre'";
        $where["group_id"]       = " = '$sender->group_id'";
        $where["chambre.annule"] = " = '0'";

        $chambre->escapeValues();
        $chambre->loadObject($where, null, null, $ljoin);
        $chambre->unescapeValues();

        $where = $ljoin = [];

        $ljoin["chambre"] = "chambre.chambre_id = lit.chambre_id";
        $ljoin["service"] = "service.service_id = chambre.service_id";

        $where["lit.nom"]           = " = '$nom_lit'";
        $where["lit.annule"]        = " = '0'";
        $where["service.cancelled"] = " = '0'";
        $where["group_id"]          = " = '$sender->group_id'";
        if ($chambre->_id) {
            $where["chambre.chambre_id"] = " = '$chambre->_id'";
        }

        $lit->escapeValues();
        $lit->loadObject($where, null, null, $ljoin);
        $lit->unescapeValues();

        // Affectation du lit
        $affectation->lit_id = $lit->_id;
    }

    /**
     * Récupération du médecin responsable
     *
     * @param DOMNode $node    Node
     * @param CSejour $mbVenue Venue
     *
     * @return CSejour
     */
    function getMedecinResponsable(DOMNode $node, CSejour $mbVenue)
    {
        $xpath = new CHPrimXPath($node->ownerDocument);

        $medecinResponsable = $xpath->queryUniqueNode("hprim:medecinResponsable", $node);

        if ($medecinResponsable) {
            $mbVenue->praticien_id = $this->getMedecin($medecinResponsable);
        }

        return $mbVenue;
    }

    /**
     * Mapping débiteurs
     *
     * @param DOMNode  $node      Node
     * @param CPatient $mbPatient Patient
     *
     * @return CPatient
     */
    function mappingDebiteurs(DOMNode $node, CPatient $mbPatient)
    {
        $xpath = new CHPrimXPath($node->ownerDocument);
        /* @FIXME Penser a parcourir tous les debiteurs par la suite */
        $debiteur = $xpath->queryUniqueNode("hprim:debiteur", $node);

        $mbPatient = $this->getAssurance($debiteur, $mbPatient);

        return $mbPatient;
    }

    /**
     * Récupérération de l'assurance
     *
     * @param DOMNode  $node      Node
     * @param CPatient $mbPatient Patient
     *
     * @return CPatient
     */
    static function getAssurance(DOMNode $node, CPatient $mbPatient)
    {
        $xpath = new CHPrimXPath($node->ownerDocument);

        $assurance = $xpath->queryUniqueNode("hprim:assurance", $node);

        // Obligatoire pour MB
        $assure    = $xpath->queryUniqueNode("hprim:assure", $assurance, false);
        $mbPatient = self::getAssure($assure, $mbPatient);

        $dates              = $xpath->queryUniqueNode("hprim:dates", $assurance);
        $mbPatient->deb_amo = $xpath->queryTextNode("hprim:dateDebutDroit", $dates);
        $mbPatient->fin_amo = $xpath->queryTextNode("hprim:dateFinDroit", $dates);

        $obligatoire            = $xpath->queryUniqueNode("hprim:obligatoire", $assurance);
        $mbPatient->code_regime = $xpath->queryTextNode("hprim:grandRegime", $obligatoire);
        $mbPatient->caisse_gest = $xpath->queryTextNode("hprim:caisseAffiliation", $obligatoire);
        $mbPatient->centre_gest = $xpath->queryTextNode("hprim:centrePaiement", $obligatoire);

        return $mbPatient;
    }

    /**
     * Récupération de l'assuré
     *
     * @param DOMNode  $node      Node
     * @param CPatient $mbPatient Patient
     *
     * @return CPatient
     */
    static function getAssure(DOMNode $node, CPatient $mbPatient)
    {
        $xpath = new CHPrimXPath($node->ownerDocument);

        $immatriculation             = $xpath->queryTextNode("hprim:immatriculation", $node);
        $mbPatient->matricule        = $immatriculation;
        $mbPatient->assure_matricule = $immatriculation;

        $personne = $xpath->queryUniqueNode("hprim:personne", $node);
        if (!$personne) {
            return $mbPatient;
        }

        $sexe           = $xpath->queryAttributNode("hprim:personne", $node, "sexe");
        $sexeConversion = [
            "M" => "m",
            "F" => "f",
        ];

        $mbPatient->assure_sexe      = $sexeConversion[$sexe];
        $mbPatient->assure_nom       = $xpath->queryTextNode("hprim:nomUsuel", $personne);
        $prenoms                     = $xpath->getMultipleTextNodes("hprim:prenoms/*", $personne);
        $mbPatient->assure_prenom    = CMbArray::get($prenoms, 0);
        $mbPatient->assure_prenoms   = trim(implode(' ', [CMbArray::get($prenoms, 1), CMbArray::get($prenoms, 2)]));
        $mbPatient->assure_naissance = $xpath->queryTextNode("hprim:naissance", $personne);

        $elementDateNaissance         = $xpath->queryUniqueNode("hprim:dateNaissance", $personne);
        $mbPatient->assure_naissance  = $xpath->queryTextNode("hprim:date", $elementDateNaissance);
        $mbPatient->rang_beneficiaire = $xpath->queryTextNode("hprim:lienAssure", $node);
        $mbPatient->qual_beneficiaire = CValue::read(CPatient::$rangToQualBenef, $mbPatient->rang_beneficiaire);

        return $mbPatient;
    }

    /**
     * Annulation du séjour ?
     *
     * @param CSejour                        $venue      Venue
     * @param CHPrimXMLAcquittementsPatients $dom_acq    Acquittement
     * @param CEchangeHprim                  $echg_hprim Echange H'XML
     *
     * @return null|string
     */
    function doNotCancelVenue(CSejour $venue, $dom_acq, $echg_hprim)
    {
        // Impossible d'annuler un séjour en cours
        if ($venue->entree_reelle) {
            $commentaire = "La venue $venue->_id que vous souhaitez annuler est impossible.";

            return $echg_hprim->setAckError($dom_acq, "E108", $commentaire, $venue);
        }

        // Impossible d'annuler un dossier ayant une intervention
        $where            = [];
        $where['annulee'] = " = '0'";
        $venue->loadRefsOperations($where);
        if (count($venue->_ref_operations) > 0) {
            $commentaire = "La venue $venue->_id que vous souhaitez annuler est impossible.";

            return $echg_hprim->setAckError($dom_acq, "E109", $commentaire, $venue);
        }

        return null;
    }

    /**
     * Passage en trash du NDA
     *
     * @param CSejour        $venue  Venue
     * @param CInteropSender $sender Expéditeur
     *
     * @return bool
     */
    function trashNDA(CSejour $venue, CInteropSender $sender)
    {
        if (isset($sender->_configs["type_sej_pa"])) {
            if ($venue->_NDA && preg_match($sender->_configs["type_sej_pa"], $venue->_NDA)) {
                // Passage en pa_ de l'id externe
                $num_pa = CIdSante400::getMatch("CSejour", $sender->_tag_sejour, $venue->_NDA);
                if ($num_pa->_id) {
                    $num_pa->tag = CAppUI::conf('dPplanningOp CSejour tag_dossier_pa') . $sender->_tag_sejour;
                    $num_pa->store();
                }

                return false;
            }
        }

        if ($venue->_NDA) {
            return true;
        }

        return false;
    }

    /**
     * Sauvegarde des INSC
     *
     * @param CPatient $patient Patient
     * @param DOMNode  $node    Elément NumeroIdentifiantSante
     *
     * @return void
     */
    function storeINSC(CPatient $patient, DOMNode $node)
    {
        $xpath            = new CHPrimXPath($node->ownerDocument);
        $list_insc        = $xpath->query("insC", $node);
        $insc             = new CINSPatient();
        $insc->type       = "C";
        $insc->patient_id = $patient->_id;

        foreach ($list_insc as $_insc) {
            $ins  = $xpath->queryTextNode("valeur", $_insc);
            $date = $xpath->queryTextNode("dateEffet", $_insc);

            if (!$ins) {
                continue;
            }

            $insc->ins_patient_id = null;
            $insc->date           = null;
            $insc->provider       = null;
            $insc->ins            = $ins;
            $insc->loadMatchingObject();

            if ($insc->date < $date) {
                $insc->date     = $date;
                $insc->provider = $this->_ref_sender->nom;
            }

            $insc->store();
        }
    }
}
