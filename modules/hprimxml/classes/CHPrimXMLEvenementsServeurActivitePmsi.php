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
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbRange;
use Ox\Core\CMbString;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Hospi\CItemLiaison;
use Ox\Mediboard\Hospi\CItemPrestation;
use Ox\Mediboard\Hospi\CSousItemPrestation;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Class CHPrimXMLEvenementsServeurActivitePmsi
 * Serveur d'activité PMSI
 */
class CHPrimXMLEvenementsServeurActivitePmsi extends CHPrimXMLEvenements {
  /**
   * @see parent::__construct
   */
  function __construct($dirschemaname = null, $schemafilename = null) {
    $this->type = "pmsi";

    if (!$this->evenement) {
      return;
    }

    $version = CAppUI::conf("hprimxml $this->evenement version");

    parent::__construct(
      "serveurActivitePMSI/v".str_replace(".", "_", $version),
      $schemafilename.str_replace(".", "", $version)
    );
  }

  /**
   * Mapping des actes
   *
   * @param array $data Datas
   *
   * @return array
   */
  function mappingServeurActes($data) {
    // Mapping patient
    $patient = $this->mappingPatient($data);

    // Mapping actes CCAM
    $actesCCAM = $this->mappingActesCCAM($data);

    return array (
      "patient"   => $patient,
      "actesCCAM" => $actesCCAM
    );
  }

  /**
   * Mapping du patient
   *
   * @param array $data Datas
   *
   * @return array
   */
  function mappingPatient($data) {
    $node = $data['patient'];
    $xpath = new CHPrimXPath($node->ownerDocument);

    $personnePhysique = $xpath->queryUniqueNode("hprim:personnePhysique", $node);
    $prenoms = $xpath->getMultipleTextNodes("hprim:prenoms/*", $personnePhysique);
    $elementDateNaissance = $xpath->queryUniqueNode("hprim:dateNaissance", $personnePhysique);

    return array (
      "idSourcePatient" => $data['idSourcePatient'],
      "idCiblePatient"  => $data['idCiblePatient'],
      "nom"             => $xpath->queryTextNode("hprim:nomUsuel", $personnePhysique),
      "prenom"          => $prenoms[0],
      "naissance"       => $xpath->queryTextNode("hprim:date", $elementDateNaissance)
    );
  }

  /**
   * Mapping de la venue
   *
   * @param DOMNode $node   Node
   * @param CSejour $sejour Séjour
   *
   * @return CSejour
   */
  function mappingVenue(DOMNode $node, CSejour $sejour) {
    // On ne récupère que l'entrée et la sortie 
    $sejour = CHPrimXMLEvenementsPatients::getEntree($node, $sejour);
    $sejour = CHPrimXMLEvenementsPatients::getSortie($node, $sejour);

    // On ne check pas la cohérence des dates des consults/intervs
    $sejour->_skip_date_consistencies = true;

    return $sejour;
  }

    /**
     * Mapping de l'intervention
     *
     * @param array      $data      Datas
     * @param COperation $operation Intervention
     *
     * @return array
     */
    public function mappingIntervention($data, COperation $operation)
    {
        // Intervention annulée ?
        if ($data['action'] == "suppression") {
            $operation->annulee = 1;

            return;
        } else {
            $operation->annulee = 0;
        }

        $node = $data['intervention'];

        $xpath = new CHPrimXPath($node->ownerDocument);

        $debut = $this->getDebutInterv($node);
        $fin   = $this->getFinInterv($node);

        // Traitement de la date/heure début, et durée de l'opération
        $operation->temp_operation = CMbDT::subTime(CMbDT::time($debut), CMbDT::time($fin));
        $operation->_time_op       = null;

        // Si une intervention du passée
        if ($debut < CMbDT::dateTime()) {
            // On affecte le début de l'opération
            if (!$operation->debut_op) {
                $operation->debut_op = $debut;
            }
            // On affecte la fin de l'opération
            if (!$operation->fin_op && ($fin && CMbDT::time($fin) != "00:00:00")) {
                $operation->fin_op = $fin;
            }
        } else {
            // Si dans le futur
            $operation->_time_urgence  = null;
            $operation->time_operation = CMbDT::time($debut);
        }

        $operation->libelle = CMbString::capitalize($xpath->queryTextNode("hprim:libelle", $node));
        $operation->rques   = CMbString::capitalize($xpath->queryTextNode("hprim:commentaire", $node));

        // Côté
        $cote            = array(
            "D" => "droit",
            "G" => "gauche",
            "B" => "bilatéral",
            "T" => "total",
            "I" => "inconnu"
        );
        $code_cote       = $xpath->queryTextNode("hprim:cote/hprim:code", $node);
        $operation->cote = isset($cote[$code_cote])
            ? $cote[$code_cote]
            : ($operation->cote ? $operation->cote : "inconnu");

        // Conventionnée ?
        $operation->conventionne = $xpath->queryTextNode("hprim:convention", $node);

        // Extemporané
        $indicateurs = $xpath->query("hprim:indicateurs/*", $node);
        foreach ($indicateurs as $_indicateur) {
            if ($xpath->queryTextNode("hprim:code", $_indicateur) == "EXT") {
                $operation->exam_extempo = true;
            }
        }

        // TypeAnesthésie
        $this->getTypeAnesthesie($node, $operation);

        $operation->duree_uscpo = $xpath->queryTextNode("hprim:dureeUscpo", $node);
    }

  /**
   * Recherche de l'intervention
   *
   * @param array      $data       Datas
   * @param COperation $operation  Intervention
   * @param int        $plageop_id Identifiant de la plage
   *
   * @return void
   * @throws Exception
   */
  function searchIntervention($data, COperation $operation, $plageop_id) {
    $operation->loadRefPlageOp();
    $operation->plageop_id     = null;
    $operation->time_operation = null;
    $operation->date           = $operation->_ref_plageop->_id ? $operation->_ref_plageop->date : $operation->date;

    $node  = $data['intervention'];
    $xpath = new CHPrimXPath($node->ownerDocument);
    if ($blocOperatoire = $xpath->queryUniqueNode("hprim:blocOperatoire", $node)) {
      $debut = $xpath->queryTextNode("hprim:debutIntervention", $blocOperatoire);
      $fin   = $xpath->queryTextNode("hprim:finIntervention", $blocOperatoire);
    }
    else {
      $debut = $this->getDebutInterv($data['intervention']);
      $fin   = $this->getFinInterv($data['intervention']);
    }

    // Traitement de la date/heure début, et durée de l'opération
    $operation->temp_operation = CMbDT::subTime(CMbDT::time($debut), CMbDT::time($fin));
    $operation->_time_op       = null;

    // Si une intervention du passée
    if ($debut < CMbDT::dateTime()) {
      // On affecte le début de l'opération
      if (!$operation->debut_op && $debut) {
        $operation->debut_op = $debut;
      }
      // On affecte la fin de l'opération
      if (!$operation->fin_op && $fin) {
        $operation->fin_op = $fin;
      }
    }
    // Si dans le futur
    else {
      $operation->_time_urgence  = null;
      $operation->time_operation = CMbDT::time($debut);
    }

    if ($operation->countMatchingList() == 1) {
      $operation->loadMatchingObject();
    }

    if (!$operation->_id && $plageop_id) {
      $operation->plageop_id = $plageop_id;
      $operation->date       = null;
    }
  }

  /**
   * Mapping de l'intervention
   *
   * @param array      $data      Datas
   * @param COperation $operation Intervention
   *
   * @return void|null
   * @throws Exception
   */
  function mappingTimingsOp($data, COperation $operation) {
    $node  = $data['intervention'];
    $xpath = new CHPrimXPath($node->ownerDocument);

    if (!$blocOperatoire = $xpath->queryUniqueNode("hprim:blocOperatoire", $node)) {
      return null;
    }
    $entreeBloc        = $xpath->queryTextNode("hprim:entreeBloc", $blocOperatoire);
    $entreeSalle       = $xpath->queryTextNode("hprim:entreeSalle", $blocOperatoire);
    $debutIntervention = $xpath->queryTextNode("hprim:debutIntervention", $blocOperatoire);
    $finIntervention   = $xpath->queryTextNode("hprim:finIntervention", $blocOperatoire);
    $sortieSalle       = $xpath->queryTextNode("hprim:sortieSalle", $blocOperatoire);
    $entreeSSPI        = $xpath->queryTextNode("hprim:entreeSSPI", $blocOperatoire);
    $sortieSSPI        = $xpath->queryTextNode("hprim:sortieSSPI", $blocOperatoire);
    // Timings opération
    if ($entreeBloc) {
      $operation->entree_bloc = CMbDT::dateTime($entreeBloc);
    }
    if ($entreeSalle) {
      $operation->entree_salle = CMbDT::dateTime($entreeSalle);
    }
    if ($debutIntervention) {
      $operation->debut_op = CMbDT::dateTime($debutIntervention);
    }
    if ($finIntervention) {
      $operation->fin_op = CMbDT::dateTime($finIntervention);
    }
    if ($sortieSalle) {
      $operation->sortie_salle = CMbDT::dateTime($sortieSalle);
    }
    if ($entreeSSPI) {
      $operation->entree_reveil = CMbDT::dateTime($entreeSSPI);
    }
    if ($sortieSSPI) {
      $operation->sortie_reveil_reel = $operation->sortie_reveil_possible = CMbDT::dateTime($sortieSSPI);
    }
  }

  /**
   * Mapping de l'intervention
   *
   * @param array      $data      Datas
   * @param COperation $operation Intervention
   *
   * @return void|null
   * @throws Exception
   */
  function mappingTimingsBrancardage($data, COperation $operation) {
    $node  = $data['intervention'];
    $xpath = new CHPrimXPath($node->ownerDocument);

    if (!$blocOperatoire = $xpath->queryUniqueNode("hprim:blocOperatoire", $node)) {
      return null;
    }

    // Timings brancardage
    CMbDT::dateTime($xpath->queryTextNode("hprim:departChambre", $blocOperatoire));
    CMbDT::dateTime($xpath->queryTextNode("hprim:retourChambre", $blocOperatoire));
  }

  /**
   * Mapping de la prestation
   *
   * @param DOMNode $node        Frais divers node
   * @param CSejour $sejour      Séjour
   * @param array   $prestations Prestations
   *
   * @return string|null
   */
  function mappingPrestation(DOMNode $node, CSejour $sejour, &$prestations = array()) {
    $sender = $this->_ref_sender;
    $xpath  = new CHPrimXPath($node->ownerDocument);

    $action = $xpath->queryAttributNode(".", $node, "action");

    $presta_name = $this->getPrestationName($node);
    $quantite = $xpath->queryTextNode("hprim:quantite", $node);
    if (!$quantite) {
      $quantite = $xpath->queryTextNode("hprim:coefficient", $node);
    }
    $date  = $xpath->queryTextNode("hprim:execute/hprim:date" , $node);
    $heure = $xpath->queryTextNode("hprim:execute/hprim:heure", $node);
    $identifiant = $this->getIdSource($node, false);

    $tab = array(
      "identifiant" => $identifiant,
      "lettreCle"   => $presta_name,
      "quantite"    => $quantite,
      "dateHeure"   => $date . " " . ($heure ? $heure : "00:00:00"),
    );

    if (!$identifiant) {
      $prestations[] = array_merge($tab, array(
        "code"    => "A500",
        "statut"  => "avt",
        "comment" => CAppUI::tr("hprimxml-error-A500", $presta_name)
      ));

      return null;
    }

    $idex = new CIdSante400();
    $idex->object_class = "CItemLiaison";
    $idex->tag   = $sender->_tag_hprimxml;
    $idex->id400 = $identifiant;
    $idex->loadMatchingObject();

    if ($action != "creation" && !$idex->_id) {
      $prestations[] = array_merge($tab, array(
        "code"    => "A501",
        "statut"  => "avt",
        "comment" => CAppUI::tr("hprimxml-error-A501", $presta_name)
      ));

      return null;
    }

    if ($action == "creation" && $idex->_id) {
      $prestations[] = array_merge($tab, array(
        "code"    => "A505",
        "statut"  => "avt",
        "comment" => CAppUI::tr("hprimxml-error-A505", $presta_name)
      ));

      return null;
    }

    // Recherche dans un sous-item avant tout
    $sous_item_presta = new CSousItemPrestation();
    if ($sender->_configs["prestation"] == "idex") {
      $idexSousItemPresta = CIdSante400::getMatch("CSousItemPrestation", $sender->_tag_hprimxml, $presta_name);
      if ($idexSousItemPresta->_id) {
        $sous_item_presta->load($idexSousItemPresta->object_id);
      }

      $item_presta = $sous_item_presta->loadRefItemPrestation();
    }
    else {
      $sous_item_presta->nom = $presta_name;
      $sous_item_presta->loadMatchingObjectEsc();

      $item_presta = $sous_item_presta->loadRefItemPrestation();
    }

    if (!$sous_item_presta->_id) {
      $item_presta = new CItemPrestation();
      if ($sender->_configs["prestation"] == "idex") {
        $idexItemPresta = CIdSante400::getMatch("CItemPrestation", $sender->_tag_hprimxml, $presta_name);
        if ($idexItemPresta->_id) {
          $item_presta->load($idexItemPresta->object_id);
        }
      }
      else {
        $item_presta->nom = $presta_name;
        $item_presta->loadMatchingObjectEsc();
      }
    }

    // Si on ne retrouve pas la presta. retour acquittement en warning
    if (!$item_presta->_id) {
      $prestations[] = array_merge($tab, array(
        "code"    => "A502",
        "statut"  => "avt",
        "comment" => CAppUI::tr("hprimxml-error-A502", $presta_name)
      ));

      return null;
    }

    $item_liaison = new CItemLiaison();
    if ($idex->_id) {
      $item_liaison->load($idex->object_id);

      if ($action == "suppression") {
        $msg = $item_liaison->delete();
        $idex->delete();
        $code = $msg ? "A503" : "I501";
        $prestations[] = array_merge($tab, array(
          "code"    => $code,
          "statut"  => $msg ? "avt" : "ok",
          "comment" => CAppUI::tr("hprimxml-error-$code", $presta_name)
        ));

        if ($msg) {
          return null;
        }

        return $item_liaison;
      }
    }
    else {
      $where["item_liaison.sejour_id"] = " = '$sejour->_id'";
      $where[] = "item_liaison.item_souhait_id = '$item_presta->_id'";

      $item_liaison->loadObject($where, "date ASC");
    }

    if (!$item_liaison->_id) {
      $item_liaison->sejour_id = $sejour->_id;
    }

    $item_liaison->prestation_id = "";
    if ($item_presta->object_class == "CPrestationJournaliere") {
      $item_liaison->prestation_id = $item_presta->object_id;
    }

    $item_liaison->date = CMbDT::date($date ? : $sejour->entree);
    $item_liaison->loadMatchingObject();

    $item_liaison->quantite         = $quantite;
    $item_liaison->item_souhait_id  = $item_presta->_id;
    if ($sous_item_presta->_id) {
      $item_liaison->sous_item_id  = $sous_item_presta->_id;
    }
    $item_liaison->_eai_sender_guid = $sender->_guid;
    $msg = $item_liaison->store();
    if (!$msg) {
      $idex->object_id = $item_liaison->_id;
      $idex->store();
    }

    $code = $msg ? "A504" : "I502";
    $prestations[] = array_merge($tab, array(
      "code"    => $code,
      "statut"  => $msg ? "avt" : "ok",
      "comment" => CAppUI::tr("hprimxml-error-$code", $presta_name, $msg)
    ));

    if ($msg) {
      return null;
    }

    return $item_liaison;
  }

  /**
   * Récupération du nom de la presta.
   *
   * @param DOMNode $node Node
   *
   * @return string
   */
  function getPrestationName(DOMNode $node) {
    $xpath = new CHPrimXPath($node->ownerDocument);

    return $xpath->queryTextNode("hprim:lettreCle", $node);
  }

  /**
   * Récupération du type d'anesthésie
   *
   * @param DOMNode    $node      Node
   * @param COperation $operation Intervention
   *
   * @return void
   */
  function getTypeAnesthesie(DOMNode $node, COperation $operation) {
    $xpath = new CHPrimXPath($node->ownerDocument);

    if (!$typeAnesthesie = $xpath->queryTextNode("hprim:typeAnesthesie", $node)) {
      return;
    }

    $operation->type_anesth = CIdSante400::getMatch("CTypeAnesth", $this->_ref_sender->_tag_hprimxml, $typeAnesthesie)->object_id;
  }

  /**
   * Récupération de la plage de l'intervention
   *
   * @param DOMNode    $node      Node
   * @param COperation $operation Intervention
   *
   * @return void
   */
  function mappingPlage(DOMNode $node, COperation $operation) {
    $debut = $this->getDebutInterv($node);

    // Traitement de la date/heure début, et durée de l'opération
    $date_op  = CMbDT::date($debut);
    $time_op  = CMbDT::time($debut);

    // Recherche d'une éventuelle plageOp avec la salle
    $plageOp           = new CPlageOp();
    $plageOp->chir_id  = $operation->chir_id;
    $plageOp->salle_id = $operation->salle_id;
    $plageOp->date     = $date_op;
    $plageOps          = $plageOp->loadMatchingList();

    // Si on a pas de plage on recherche éventuellement une plage dans une autre salle
    if (count($plageOps) == 0) {
      $plageOp->salle_id = null;
      $plageOps = $plageOp->loadMatchingList();

      // Si on retrouve des plages alors on ne prend pas en compte la salle du flux
      if (count($plageOps) > 0) {
        $operation->salle_id = null;
      }
    }

    foreach ($plageOps as $_plage) {
      // Si notre intervention est dans la plage Mediboard
      if (CMbRange::in($time_op, $_plage->debut, $_plage->fin)) {
        $plageOp = $_plage;
        break;
      }
    }

    if ($plageOp->_id) {
      $operation->plageop_id = $plageOp->_id;
      $operation->salle_id   = $plageOp->salle_id;
    }
    else {
      // Dans le cas où l'on avait une plage sur l'interv on la supprime
      $operation->plageop_id = "";

      $operation->date = $date_op;
    }
  }

  /**
   * Récupération du début de l'intervention
   *
   * @param DOMNode $node Node
   *
   * @return string
   */
  function getDebutInterv(DOMNode $node) {
    $xpath = new CHPrimXPath($node->ownerDocument);

    return $this->getDateHeure($xpath->queryUniqueNode("hprim:debut", $node, false));
  }

  /**
   * Récupération de la fin de l'intervention
   *
   * @param DOMNode $node Node
   *
   * @return string
   */
  function getFinInterv(DOMNode $node) {
    $xpath = new CHPrimXPath($node->ownerDocument);

    return $this->getDateHeure($xpath->queryUniqueNode("hprim:fin", $node, false));
  }

  /**
   * Récupération des participants de l'intervention
   *
   * @param DOMNode $node   Node
   * @param CSejour $sejour Séjour
   *
   * @return string
   */
  function getParticipant(DOMNode $node) {
    $xpath = new CHPrimXPath($node->ownerDocument);

    $medecin = $xpath->queryUniqueNode("hprim:participants/hprim:participant/hprim:medecin", $node);

    return $this->getMedecin($medecin);
  }

  /**
   * Récupération de la salle
   *
   * @param DOMNode $node   Node
   * @param CSejour $sejour Séjour
   *
   * @return string
   */
  function getSalle(DOMNode $node, CSejour $sejour) {
    $xpath = new CHPrimXPath($node->ownerDocument);
    $name = $xpath->queryTextNode("hprim:uniteFonctionnelle/hprim:code", $node);

    // Recherche de la salle par le nom
    $salle = new CSalle();
    $where = array(
      "sallesbloc.nom"           => $salle->_spec->ds->prepare("=%", $name),
      "bloc_operatoire.group_id" => "= '$sejour->group_id'"
    );
    $ljoin = array(
      "bloc_operatoire" => "bloc_operatoire.bloc_operatoire_id = sallesbloc.bloc_id"
    );

    $salle->loadObject($where, null, null, $ljoin);

    // Recherche de la salle par le code
    if (!$salle->_id) {
      $salle = new CSalle();
      $where = array(
        "sallesbloc.code"          => $salle->_spec->ds->prepare("=%", $name),
        "bloc_operatoire.group_id" => "= '$sejour->group_id'"
      );
      $ljoin = array(
        "bloc_operatoire" => "bloc_operatoire.bloc_operatoire_id = sallesbloc.bloc_id"
      );

      $salle->loadObject($where, null, null, $ljoin);
    }

    return $salle;
  }

  /**
   * Mapping des actes CCAM
   *
   * @param array $data Datas
   *
   * @return array
   */
  function mappingActesCCAM($data) {
    $node = $data['actesCCAM'];

    $actesCCAM = array();

    if (!$node) {
      return $actesCCAM;
    }

    foreach ($node->childNodes as $_acteCCAM) {
      $actesCCAM[] = $this->mappingActeCCAM($_acteCCAM, $data);
    }

    return $actesCCAM;
  }

  /**
   * Mapping des actes CCAM
   *
   * @param array $data Datas
   *
   * @return array
   */
  function mappingActesNGAP($data) {
    $node = $data['actesNGAP'];
    $actesNGAP = array();

    if (!$node) {
      return $actesNGAP;
    }

    foreach ($node->childNodes as $_acteNGAP) {
      $actesNGAP[] = $this->mappingActeNGAP($_acteNGAP, $data);
    }

    return $actesNGAP;
  }

  /**
   * Mapping des actes CCAM
   *
   * @param DOMNode $node Node
   * @param array   $data Datas
   *
   * @return array
   */
  function mappingActeCCAM(DOMNode $node, $data) {
    $xpath = new CHPrimXPath($node->ownerDocument);

    $acteCCAM = array();
    $acteCCAM["code_acte"]     = $xpath->queryTextNode("hprim:codeActe"           , $node);
    $acteCCAM["code_activite"] = $xpath->queryTextNode("hprim:codeActivite"       , $node);
    $acteCCAM["code_phase"]    = $xpath->queryTextNode("hprim:codePhase"          , $node);
    $acteCCAM["date"]          = $xpath->queryTextNode("hprim:execute/hprim:date" , $node);
    $acteCCAM["heure"]         = $xpath->queryTextNode("hprim:execute/hprim:heure", $node);

    $acteCCAM["modificateur"] = array();
    $modificateurs = $xpath->query("hprim:modificateurs/hprim:modificateur", $node);
    foreach ($modificateurs as $_modificateur) {
      if ($modificateur = $xpath->queryTextNode(".", $_modificateur)) {
        $acteCCAM["modificateur"][] = $modificateur;
      }
    }

    $acteCCAM["commentaire"]              = $xpath->queryTextNode("hprim:commentaire", $node);
    $acteCCAM["signe"]                    = $xpath->queryAttributNode(".", $node, "signe");
    $acteCCAM["facturable"]               = $xpath->queryAttributNode(".", $node, "facturable");
    $acteCCAM["rembourse"]                = $xpath->queryAttributNode(".", $node, "remboursementExceptionnel");
    $acteCCAM["charges_sup"]              = $xpath->queryAttributNode(".", $node, "supplementCharges");
    $acteCCAM                             = array_merge($acteCCAM, $this->getMontant($node));

    $position_dentaire                    = $xpath->query("hprim:positionsDentaires/hprim:positionDentaire");
    $acteCCAM["position_dentaire"] = array();
    foreach ($position_dentaire as $_position_dentaire) {
      if ($dent = $xpath->queryTextNode(".", $_position_dentaire)) {
        $acteCCAM["position_dentaire"][] = $dent;
      }
    }

    $acteCCAM["code_association"]    = $xpath->queryTextNode("hprim:codeAssociationNonPrevue", $node);
    $acteCCAM["code_extension"]      = $xpath->queryTextNode("hprim:codeExtensionDocumentaire", $node);
    $acteCCAM["rapport_exoneration"] = $xpath->queryAttributNode(".", $node, "rapportExoneration");

    $idSourceActesCCAM = $this->getIdSource($node, false);
    $idCibleActesCCAM  = $this->getIdCible($node , false);

    $medecin = $xpath->queryUniqueNode("hprim:executant/hprim:medecins/hprim:medecinExecutant[@principal='oui']/hprim:medecin", $node);
    //si pas de medecin principal, on recherche le premier médecin exécutant
    if (!$medecin) {
      $medecin = $xpath->getNode("hprim:executant/hprim:medecins/hprim:medecinExecutant/hprim:medecin", $node);
    }
    $mediuser_id = $this->getMedecin($medecin);
    $action = $xpath->queryAttributNode(".", $node, "action");

    return array (
      "idSourceIntervention" => $data['idSourceIntervention'],
      "idCibleIntervention"  => $data['idCibleIntervention'],
      "idSourceActeCCAM"     => $idSourceActesCCAM,
      "idCibleActeCCAM"      => $idCibleActesCCAM,
      "action"               => $action,
      "acteCCAM"             => $acteCCAM,
      "executant_id"         => $mediuser_id,
    );
  }

  /**
   * Mapping des actes NGAP
   *
   * @param DOMNode $node Node
   * @param array   $data Datas
   *
   * @return array
   */
  function mappingActeNGAP(DOMNode $node, $data) {
    $xpath = new CHPrimXPath($node->ownerDocument);

    $acteNGAP = array();
    $acteNGAP["code"]                       = $xpath->queryTextNode("hprim:lettreCle"          , $node);
    $acteNGAP["coefficient"]                = $xpath->queryTextNode("hprim:coefficient"        , $node);
    $acteNGAP["quantite"]                   = $xpath->queryTextNode("hprim:quantite"           , $node);
    $acteNGAP["date"]                       = $xpath->queryTextNode("hprim:execute/hprim:date" , $node);
    $acteNGAP["heure"]                      = $xpath->queryTextNode("hprim:execute/hprim:heure", $node);
    $acteNGAP["numero_dent"]                = $xpath->queryTextNode("hprim:positionDentaire"   , $node);
    $acteNGAP["comment"]                    = $xpath->queryTextNode("hprim:commentaire"        , $node);
    $acteNGAP                               = array_merge($acteNGAP, $this->getMontant($node));

    $minoration                             = $xpath->queryUniqueNode("hprim:minorMajor/hprim:minoration", $node);
    $acteNGAP["minor_pct"]                  = $xpath->queryTextNode("hprim:pourcentage", $minoration);
    $acteNGAP["minor_coef"]                 = $xpath->queryTextNode("hprim:coefficient", $minoration);
    $majoration                             = $xpath->queryUniqueNode("hprim:minorMajor/hprim:majoration", $node);
    $acteNGAP["major_pct"]                  = $xpath->queryTextNode("hprim:pourcentage", $majoration);
    $acteNGAP["major_coef"]                 = $xpath->queryTextNode("hprim:coefficient", $majoration);

    $acteNGAP["facturable"]                 = $xpath->queryAttributNode(".", $node, "facturable");
    $acteNGAP["rapportExoneration"]         = $xpath->queryAttributNode(".", $node, "rapportExoneration");
    $acteNGAP["executionNuit"]              = $xpath->queryAttributNode(".", $node, "executionNuit");
    $acteNGAP["executionDimancheJourFerie"] = $xpath->queryAttributNode(".", $node, "executionDimancheJourFerie");

    $medecin  = $xpath->query("hprim:prestataire/hprim:medecins/hprim:medecin", $node);
    $mediuser_id = $this->getMedecin($medecin->item(0));

    $idSourceActeNGAP = $this->getIdSource($node, false);
    $idCibleActeNGAP  = $this->getIdCible($node, false);
    $action = $xpath->queryAttributNode(".", $node, "action");

    return array (
      "idSourceIntervention" => $data['idSourceIntervention'],
      "idCibleIntervention"  => $data['idCibleIntervention'],
      "idSourceActeNGAP"     => $idSourceActeNGAP,
      "idCibleActeNGAP"      => $idCibleActeNGAP,
      "action"               => $action,
      "acteNGAP"             => $acteNGAP,
      "executant_id"         => $mediuser_id,
    );
  }

  /**
   * Mapp the montant node
   *
   * @param DOMNode $node Node
   *
   * @return array
   */
  function getMontant($node) {
    $xpath = new CHPrimXPath($node->ownerDocument);
    $data = array();
    $montant = $xpath->queryUniqueNode("hprim:montant", $node);
    $data["montantTotal"]           = $xpath->queryTextNode("montantTotal"          , $montant);
    $data["numeroForfaitTechnique"] = $xpath->queryTextNode("numeroForfaitTechnique", $montant);
    $data["numeroAgrementAppareil"] = $xpath->queryTextNode("numeroAgrementAppareil", $montant);
    $data["montantDepassement"]     = $xpath->queryTextNode("montantDepassement"    , $montant);

    return $data;
  }

  /**
   * Enregistrement des données du serveur d'activité PMSI
   *
   * @param CHPrimXMLAcquittements $dom_acq  DOM Acquittement
   * @param CMbObject              $mbObject Object
   * @param array                  $data     Data that contain the nodes
   *
   * @return string Acquittement
   **/
  function handle(CHPrimXMLAcquittements $dom_acq, CMbObject $mbObject, $data) {
  }
}
