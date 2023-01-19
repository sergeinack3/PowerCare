<?php
/**
 * @package Mediboard\Hprimsante
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimsante;

use DOMElement;
use DOMNode;
use DOMNodeList;
use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbObject;
use Ox\Core\CMbSecurity;
use Ox\Core\CMbString;
use Ox\Core\CMbXMLDocument;
use Ox\Interop\Eai\CInteropReceiver;
use Ox\Interop\Eai\CInteropSender;
use Ox\Interop\Eai\Repository\Exceptions\SejourRepositoryException;
use Ox\Interop\Eai\Repository\PatientRepository;
use Ox\Interop\Eai\Repository\SejourRepository;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientINSNIR;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Class CHPrimSanteMessageXML
 * Message XML HPR
 */
class CHPrimSanteMessageXML extends CMbXMLDocument {
  /** @var  CExchangeHprimSante */
  public $_ref_exchange_hpr;
  /** @var CInteropSender */
  public $_ref_sender;
  /** @var CInteropReceiver */
  public $_ref_receiver;

  public $loop;
  public $identifier_patient;

  /**
   * Return the event type
   *
   * @param String $event_name event name
   * @param string $encoding   encoding
   *
   * @return CHPrimSanteMessageXML|CHPrimSanteRecordADM|CHPrimSanteRecordORU|CHPrimSanteRecordPayment
   */
  static function getEventType($event_name = null, $encoding = "utf-8") {
    if (!$event_name) {
      return new CHPrimSanteMessageXML($encoding);
    }

    // Transfert de données d'admission
    if (strpos($event_name, "CHPrimSanteADM") === 0) {
      return new CHPrimSanteRecordADM($encoding);
    }

    // Transfert de données de règlement
    if (strpos($event_name, "CHPrimSanteREG") === 0) {
      return new CHPrimSanteRecordPayment($encoding);
    }

    // Transfert de données de règlement
    if (strpos($event_name, "CHPrimSanteORU") === 0) {
      return new CHPrimSanteRecordORU($encoding);
    }

    return new CHPrimSanteMessageXML($encoding);
  }

  /**
   * @inheritdoc
   */
  function __construct($encoding = "utf-8") {
    parent::__construct($encoding);

    $this->formatOutput = true;
  }

  /**
   * @inheritdoc
   */
  function addNameSpaces($name) {
    // Ajout des namespace pour XML Spy
    $this->addAttribute($this->documentElement, "xmlns", "urn:hpr-org:v2xml");
    $this->addAttribute($this->documentElement, "xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance");
    $this->addAttribute($this->documentElement, "xsi:schemaLocation", "urn:hpr-org:v2xml");
  }

  /**
   * @inheritdoc
   */
  function addElement(DOMNode $elParent, $elName, $elValue = null, $elNS = "urn:hpr-org:v2xml") {
    return parent::addElement($elParent, $elName, $elValue, $elNS);
  }

  /**
   * Query
   *
   * @param String  $nodeName    node name
   * @param DOMNode $contextNode contexte node
   *
   * @return DOMNodeList
   */
  function query($nodeName, DOMNode $contextNode = null) {
    $xpath = new CHPrimSanteMessageXPath($contextNode ? $contextNode->ownerDocument : $this);

    if ($contextNode) {
      return $xpath->query($nodeName, $contextNode);
    }

    return $xpath->query($nodeName);
  }

  /**
   * Query node
   *
   * @param String   $nodeName    node name
   * @param DOMNode  $contextNode context node
   * @param String[] &$data       data
   * @param bool     $root        root
   *
   * @return DOMElement
   */
  function queryNode($nodeName, DOMNode $contextNode = null, &$data = array(), $root = false) {
    $xpath = new CHPrimSanteMessageXPath($contextNode ? $contextNode->ownerDocument : $this);

    return $data[$nodeName] = $xpath->queryUniqueNode($root ? "//$nodeName" : "$nodeName", $contextNode);
  }

  /**
   * Query nodes
   *
   * @param String   $nodeName    node name
   * @param DOMNode  $contextNode context node
   * @param String[] &$data       data
   * @param bool     $root        root
   *
   * @return DOMNodeList
   */
  function queryNodes($nodeName, DOMNode $contextNode = null, &$data = array(), $root = false) {
    $nodeList = $this->query("$nodeName", $contextNode);
    foreach ($nodeList as $_node) {
      $data[$nodeName][] = $_node;
    }

    return $nodeList;
  }

  /**
   * Query text node
   *
   * @param String  $nodeName    node name
   * @param DOMNode $contextNode context node
   * @param bool    $root        root
   *
   * @return string
   */
  function queryTextNode($nodeName, DOMNode $contextNode, $root = false) {
    $xpath = new CHPrimSanteMessageXPath($contextNode ? $contextNode->ownerDocument : $this);

    return $xpath->queryTextNode($nodeName, $contextNode);
  }

  /**
   * Get the segment
   *
   * @param String    $name   name
   * @param String    $data   data
   * @param CMbObject $object object
   *
   * @return void
   */
  function getSegment($name, $data, $object) {
    if (!array_key_exists($name, $data) || $data[$name] === null) {
      return;
    }

    $function = "get$name";

    $this->$function($data[$name], $object);
  }

    /**
     * Get the person identifiers
     *
     * @param DOMNode $node node
     *
     * @return String[]
     * @throws Exception
     */
    public function getPersonIdentifiers($node)
    {
        $xpath = new CHPrimSanteMessageXPath($node ? $node->ownerDocument : $this);
        $data  = [];

        $data["identifier"]       = $xpath->queryTextNode("P.2/FNM.1", $node);
        $data["identifier_merge"] = $xpath->queryTextNode("P.2/FNM.2", $node);
        $data["merge"]            = $xpath->queryTextNode("P.2/FNM.3", $node);

        // ins
        $ins_oid = $xpath->queryTextNode('P.11/INS.6', $node);
        if (!$ins_oid) {
            $matching = [
                'INS-NIA' => CPatientINSNIR::OID_INS_NIA,
                'INS-NIR' => CPatientINSNIR::OID_INS_NIR,
            ];
            if ($ins_type = $xpath->queryTextNode('P.11/INS.2', $node)) {
                $ins_oid = $matching[$ins_type] ?? null;
            }
        }

        $ins = $xpath->queryTextNode('P.11/INS.1', $node);
        if ($ins_oid === CPatientINSNIR::OID_INS_NIR) {
            $data['INS_NIR'] = $ins;
        } elseif ($ins_oid === CPatientINSNIR::OID_INS_NIA) {
            $data['INS_NIA'] = $ins;
        }

        return $data;
    }

    /**
   * Get the sejour identifiers
   *
   * @param DOMNode $node node
   *
   * @return String[]
   */
  function getSejourIdentifier($node) {
    $xpath = new CHPrimSanteMessageXPath($node ? $node->ownerDocument : $this);
    $data = array();

    $data["sejour_identifier"] = $xpath->queryTextNode("P.4/HD.1", $node);
    $data["rang"]              = $xpath->queryTextNode("P.4/HD.2", $node);

    return $data;
  }

  /**
   * Get death date
   *
   * @param DOMNode $node node
   *
   * @return string
   */
  function getDeathDate($node) {
    $xpath = new CHPrimSanteMessageXPath($node ? $node->ownerDocument : $this);
    $date = $xpath->queryTextNode("P.33/TS.1", $node);
    if ($date) {
      $date = CMbDT::dateTime($date);
    }
    return $date;
  }

  /**
   * Get marital status
   *
   * @param DOMNode $node node
   *
   * @return null|string
   */
  function getMaritalStatus($node) {
    $xpath = new CHPrimSanteMessageXPath($node ? $node->ownerDocument : $this);
    $marital = $xpath->queryTextNode("P.28", $node);
    return $marital == "U" ? null: $marital;
  }

  /**
   * Get location
   *
   * @param DOMNode $node node
   *
   * @return array|null
   */
  function getLocalisation($node) {
    $xpath = new CHPrimSanteMessageXPath($node ? $node->ownerDocument : $this);
    if (!$p25 = $xpath->queryUniqueNode("P.25", $node)) {
      return null;
    };
    $data = array();
    $data["lit"]     = $xpath->queryTextNode("PL.1", $p25);
    $data["chambre"] = $xpath->queryTextNode("PL.2", $p25);
    $data["service"] = $xpath->queryTextNode("PL.3", $p25);

    return $data;
  }

  /**
   * Get sejour status
   *
   * @param DOMNode $node node
   *
   * @return array
   */
  function getSejourStatut($node) {
    $xpath = new CHPrimSanteMessageXPath($node ? $node->ownerDocument : $this);
    $data = array();

    $nodes = $xpath->query("P.23", $node);

    if ($nodes->length >= 1) {
      $data["entree"] = $xpath->queryTextNode("TS.1", $nodes->item(0));
    }
    if ($nodes->length >= 2) {
      $data["sortie"] = $xpath->queryTextNode("TS.1", $nodes->item(1));
    }

    $data["statut"] = $xpath->queryTextNode("P.24", $node);

    return $data;
  }

    /**
     * Get the name person
     *
     * @param DOMNode|null $node node
     *
     * @return array
     */
    public function getNamePerson(?DOMNode $node): array
    {
        $xpath = new CHPrimSanteMessageXPath($node ? $node->ownerDocument : $this);
        $data  = [];

        $family_name = $xpath->queryTextNode("P.6/PBN.1", $node);
        if ($family_name === null) {
            // treatment for HprimSante Version < 2.5
            $family_name = $xpath->queryTextNode("P.6", $node);
        } else {
            $data["birth_given_name"] = $xpath->queryTextNode("P.6/PBN.2", $node);
            $data["birth_given_names"] = $xpath->queryTextNode("P.6/PBN.3", $node);
        }

        $data["family_name"]      = $family_name;
        $data["name"]             = $xpath->queryTextNode("P.5/PN.1", $node);
        $data["firstname"]        = $xpath->queryTextNode("P.5/PN.2", $node);
        $data["secondname"]       = $xpath->queryTextNode("P.5/PN.3", $node);
        $data["pseudonyme"]       = $xpath->queryTextNode("P.5/PN.4", $node);
        $data["civilite"]         = $xpath->queryTextNode("P.5/PN.5", $node);
        $data["diplome"]          = $xpath->queryTextNode("P.5/PN.6", $node);

        return $data;
    }

    /**
   * Get the birthdate
   *
   * @param DOMNode $node node
   *
   * @return null|string
   */
  function getBirthdate($node) {
    $xpath = new CHPrimSanteMessageXPath($node ? $node->ownerDocument : $this);
    $birthdate = $xpath->queryTextNode("P.7", $node);
    return $birthdate ? CMbDT::transform(null, $birthdate, "%Y-%m-%d"): null;
  }

  /**
   * Get the sex of the person
   *
   * @param DOMNode $node node
   *
   * @return string
   */
  function getSexPerson($node) {
    $xpath = new CHPrimSanteMessageXPath($node ? $node->ownerDocument : $this);

    return $xpath->queryTextNode("P.8", $node);
  }

  /**
   * Get the address
   *
   * @param DOMNode $node node
   *
   * @return array
   */
  function getAddress($node) {
    $xpath = new CHPrimSanteMessageXPath($node ? $node->ownerDocument : $this);
    $data = array();

    $data["street"]  = $xpath->queryTextNode("P.10/AD.1", $node);
    $data["comp"]    = $xpath->queryTextNode("P.10/AD.2", $node);
    $data["city"]    = $xpath->queryTextNode("P.10/AD.3", $node);
    $data["state"]   = $xpath->queryTextNode("P.10/AD.4", $node);
    $data["postal"]  = $xpath->queryTextNode("P.10/AD.5", $node);
    $data["country"] = $xpath->queryTextNode("P.10/AD.6", $node);

    return $data;
  }

  /**
   * Get the phone
   *
   * @param DOMNode $node node
   *
   * @return array
   */
  function getPhone($node) {
    $xpath = new CHPrimSanteMessageXPath($node ? $node->ownerDocument : $this);
    $node_phone = $xpath->query("P.12", $node);
    $data = array();
    foreach ($node_phone as $_node) {
      $data[] = $xpath->queryTextNode(".", $_node);
    }

    return $data;
  }

  /**
   * Get matricule
   *
   * @param DOMNode $node node
   *
   * @return string
   */
  function getMatricule($node) {
    $xpath = new CHPrimSanteMessageXPath($node ? $node->ownerDocument : $this);

    return $xpath->queryTextNode("AP.5", $node);
  }

    /**
     * Get the patient
     *
     * @param array   $identifier identifier
     * @param DOMNode $node       node
     *
     * @return CPatient|null
     * @throws Exception
     */
    public function getPatient($identifiers, $node): ?CPatient
    {
        $sender   = $this->_ref_sender;
        $IPP      = $identifiers['identifier'] ?? null;
        $INS_NIR  = $identifiers['INS_NIR'] ?? null;
        $INS_NIA  = $identifiers['INS_NIA'] ?? null;
        $strategy = $sender->_configs['search_patient_strategy'];

        return (new PatientRepository($strategy))
            ->withIPP($IPP, $sender->_tag_patient)
            ->withINS($INS_NIR, $INS_NIA)
            ->withPatientSearched($this->mapPatientPrimary($node), $sender->group_id)
            ->find();
    }

    /**
     * @param DOMNode $node_P
     *
     * @return CPatient
     */
    public function mapPatientPrimary(DOMNode $node_P): CPatient
    {
        $person                   = $this->getNamePerson($node_P);
        $patient                  = new CPatient();
        $patient->nom             = $person["name"];
        $patient->prenom          = $person["firstname"];
        $patient->nom_jeune_fille = $person['family_name'];
        $patient->naissance       = $this->getBirthdate($node_P);
        $patient->sexe            = CMbString::lower($this->getSexPerson($node_P));

        return $patient;
    }

    public function mapPatientFull(DOMNode $node_P): CPatient
    {
        $patient = $this->mapPatientPrimary($node_P);

        $person = $this->getNamePerson($node_P);
        $patient->prenoms         = $person["secondname"];
        $patient->civilite        = $person["civilite"] ? CMbString::lower($person["civilite"]) : "guess";

        $address = $this->getAddress($node_P);
        $address["street"] .= $address["comp"] ? "\n{$address["comp"]}" : null;
        $patient->adresse = $address["street"];
        $patient->ville   = $address["city"];
        $patient->pays    = $address["country"];
        $patient->cp      = $address["postal"];

        $phone = $this->getPhone($node_P);
        $patient->tel  = trim(str_replace(".", " ", CMbArray::get($phone, 0)));
        $patient->tel2 = trim(str_replace(".", " ", CMbArray::get($phone, 1)));

        $patient->situation_famille = $this->getMaritalStatus($node_P);

        $patient->deces = $this->getDeathDate($node_P);

        // Gestion du numéro de sécurité social
        if ($node_P->nextSibling && $node_P->nextSibling->tagName == "AP") {
            $node_assure = $node_P->nextSibling;
            $patient->matricule = $this->getMatricule($node_assure);
        }

        return $patient;
    }

  /**
   * Get the INS
   *
   * @param DOMNode $node node
   *
   * @return array
   */
  function getINS($node) {
    $xpath = new CHPrimSanteMessageXPath($node ? $node->ownerDocument : $this);
    $nodeINS = $xpath->query("P.11");
    $ins = array();
    foreach ($nodeINS as $_node) {
      $ins[]["ins"]  = $xpath->queryTextNode("INS.1", $_node);
      $ins[]["type"] = $xpath->queryTextNode("INS.2", $_node);
      $ins[]["date"] = $xpath->queryTextNode("INS.3", $_node);
    }

    return $ins;
  }

  /**
   * Get the sejour
   *
   * @param CPatient $patient    Patient
   * @param String   $identifier Identifier
   * @param DOMNode  $node       Node
   * @param boolean  $create     En mode création
   *
   * @return CSejour|CHPrimSanteError
   * @throws Exception
   */
  function getSejour($patient, $identifier, $node, $create = false) {
    /** @var CInteropSender $sender */
    $sender     = $this->_ref_sender;
    $patient_id = $patient->_id;

    if (!$identifier) {
      return new CHPrimSanteError($this->_ref_exchange_hpr, "T", "16", array("P", $this->loop+1, $this->identifier_patient), "8.4");
    }

    try {
        $sejour = (new SejourRepository(SejourRepository::STRATEGY_ONLY_NDA))
            ->setPatient($patient)
            ->setNDA($identifier, $sender->_tag_sejour)
            ->find();
    } catch (SejourRepositoryException $exception) {
        if ($exception->getId() === SejourRepositoryException::PATIENT_DIVERGENCE_FOUND) {
            return new CHPrimSanteError($this->_ref_exchange_hpr, "T", "13", array("P", $this->loop+1, $this->identifier_patient), "8.5");
        }

        throw $exception;
    }

    $sejour = $sejour ?: new CSejour();
    $sejour->patient_id = $patient_id;

    $data   = $this->getSejourStatut($node);
    $entree = isset($data["entree"]) ? CMbDT::dateTime($data["entree"]): null;
    $sortie = isset($data["sortie"]) ? CMbDT::dateTime($data["sortie"]): null;
    switch ($data["statut"]) {
      case "OP":
        $sejour->sortie_reelle = $sortie;
        break;
      case "IP":
        $sejour->type = "comp";
        $sejour->entree_reelle = $entree;
        $sejour->sortie_prevue = $sortie;
        break;
      case "IO":
        $sejour->type = "ambu";
        $sejour->entree_reelle = $entree;
        $sejour->sortie_prevue = $sortie;
        break;
      case "ER":
        $sejour->type = "urg";
        $sejour->entree_reelle = $entree;
        break;
      case "PA":
        $sejour->type = "comp";
        $sejour->entree_prevue = $entree;
        break;
      case "MP":
        //modification patient
        if (!$identifier && !$entree) {
          return null;
        }

        //Pas de séjour retrouvé
        if (!$sejour->_id) {
          return new CHPrimSanteError(
            $this->_ref_exchange_hpr, "P", "06", array("P", $this->loop + 1, $this->identifier_patient), "8.24"
          );
        }

        if ($sender->_configs["notifier_entree_reelle"]) {
          $sejour->entree_reelle = $entree;
        }
        else {
          $sejour->entree_prevue = $entree;
        }

        $sejour->sortie_prevue = $sortie;
        break;
      default:
    }

    // Après mapping
    if ($sejour->_id) {
      return $sejour;
    }

      $search_min_admit = CAppUI::gconf('hprimsante search_interval search_min_admit');
      $search_max_admit = CAppUI::gconf('hprimsante search_interval search_max_admit');
      $date_before      = CMbDT::date("- $search_min_admit DAY", $entree);
      $date_after       = CMbDT::date("+ $search_max_admit DAY", $entree);

      try {
          $sejour = (new SejourRepository(SejourRepository::STRATEGY_ONLY_DATE_EXTENDED))
              ->setPatient($patient)
              ->setDateSejour($entree)
              ->setParameter(SejourRepository::PARAMETER_DATE_BEFORE, $date_before)
              ->setParameter(SejourRepository::PARAMETER_DATE_AFTER, $date_after)
              ->setGroupId($sender->group_id)
              ->find();
      } catch (SejourRepositoryException $exception) {
          if ($exception->getId() === SejourRepositoryException::MULTIPLE_SEJOUR_FOUND) {
              return new CHPrimSanteError(
                  $this->_ref_exchange_hpr,
                  "P",
                  "04",
                  ["P", $this->loop + 1, $this->identifier_patient],
                  "8.25"
              );
          }

          throw $exception;
      }

    // Si on est en mode IPP_NDA(récupération du nda) et qu'aucun séjour n'a été retrouvé
    if (!$create && (!$sejour || !$sejour->_id)) {
      return new CHPrimSanteError($this->_ref_exchange_hpr, "P", "06", array("P", $this->loop+1, $this->identifier_patient), "8.25");
    }

    return $sejour;
  }

  /**
   * Return or create the doctor of the message
   *
   * @param DOMNode $node   Node
   * @param bool    $search If search in node or P
   *
   * @return CMediusers|int|null
   */
  function getDoctor($node, $search = false) {
    $xpath = new CHPrimSanteMessageXPath($node ? $node->ownerDocument : $this);

    $nodeDoctor[] = $node;
    if (!$search) {
      $nodeDoctor = $xpath->query("P.13", $node);
    }

    $code = $nom = $prenom = $type_code = null;
    foreach ($nodeDoctor as $_node_doctor) {
      $code       = $xpath->queryTextNode("CNA.1"     , $_node_doctor);
      $nom        = $xpath->queryTextNode("CNA.2/PN.1", $_node_doctor);
      $prenom     = $xpath->queryTextNode("CNA.2/PN.2", $_node_doctor);
      $type_code  = $xpath->queryTextNode("CNA.3"     , $_node_doctor);
      if ($code && $nom) {
        break;
      }
    }

    $mediuser = new CMediusers();

    $mediuser->_user_last_name = $nom;

    switch ($type_code) {
      case "R":
        $mediuser->rpps  = $code;
        break;
      case "A":
        $mediuser->adeli = $code;
        break;
      default:
        if (strlen($code) == 9 && CMbString::luhn($code)) {
          $mediuser->adeli = $code;
        }
        if (strlen($code) == 11 && CMbString::luhn($code)) {
          $mediuser->rpps  = $code;
        }
    }

    // Cas où l'on a aucune information sur le médecin
    if (!$mediuser->rpps && !$mediuser->adeli && !$mediuser->_id && !$mediuser->_user_last_name) {
      return $mediuser;
    }

    $sender = $this->_ref_sender;
    $ds     = $mediuser->getDS();

    $ljoin = array();
    $ljoin["functions_mediboard"] = "functions_mediboard.function_id = users_mediboard.function_id";

    $where   = array();
    $where["functions_mediboard.group_id"] = " = '$sender->group_id'";

    if (($mediuser->rpps || $mediuser->adeli)) {
      if ($mediuser->rpps) {
        $where[] = $ds->prepare("rpps = %", $mediuser->rpps);
      }
      if ($mediuser->adeli) {
        $where[] = $ds->prepare("adeli = %", $mediuser->adeli);
      }

      // Dans le cas où le praticien recherché par son ADELI ou RPPS est multiple
      if ($mediuser->countList($where, null, $ljoin) > 1) {
        $ljoin["users"] = "users_mediboard.user_id = users.user_id";
        $where[]        = $ds->prepare("users.user_last_name = %" , $nom);
      }

      $mediuser->loadObject($where, null, null, $ljoin);

      if ($mediuser->_id) {
        return $mediuser;
      }
    }

    $user = new CUser;

    $ljoin = array();
    $ljoin["users_mediboard"]     = "users.user_id = users_mediboard.user_id";
    $ljoin["functions_mediboard"] = "functions_mediboard.function_id = users_mediboard.function_id";

    $where   = array();
    $where["functions_mediboard.group_id"] = " = '$sender->group_id'";
    $where[] = $ds->prepare("users.user_first_name = %", $prenom);
    $where[] = $ds->prepare("users.user_last_name = %" , $nom);

    $order = "users.user_id ASC";
    if ($user->loadObject($where, $order, null, $ljoin)) {
      return $user->loadRefMediuser();
    }

    $mediuser->_user_first_name = $prenom;
    $mediuser->_user_last_name  = $nom;

    return $this->createDoctor($mediuser);
  }

  /**
   * Create the mediuser
   *
   * @param CMediusers $mediuser mediuser
   *
   * @return int
   */
  function createDoctor(CMediusers $mediuser) {
    $sender = $this->_ref_sender;

    $function = new CFunctions();
    $function->text = CAppUI::conf("hprimsante importFunctionName");
    $function->group_id = $sender->group_id;
    $function->loadMatchingObjectEsc();
    if (!$function->_id) {
      $function->type = "cabinet";
      $function->compta_partagee = 0;
      $function->store();
    }
    $mediuser->function_id = $function->_id;
    $mediuser->_user_username = CMbFieldSpec::randomString(array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z')), 20);
    $mediuser->_user_password = CMbSecurity::getRandomPassword();
    $mediuser->_user_type = 13; // Medecin
    $mediuser->actif = CAppUI::conf("hprimsante doctorActif") ? 1 : 0;

    $user = new CUser();
    $user->user_last_name   = $mediuser->_user_last_name;
    $user->user_first_name  = $mediuser->_user_first_name;
    // On recherche par le seek
    $users                  = $user->seek("$user->user_last_name $user->user_first_name");
    if (count($users) == 1) {
      $user = reset($users);
      $user->loadRefMediuser();
      $mediuser = $user->_ref_mediuser;
    }
    else {
      // Dernière recherche si le login est déjà existant
      $user = new CUser();
      $user->user_username = $mediuser->_user_username;
      if ($user->loadMatchingObject()) {
        // On affecte un username aléatoire
        $mediuser->_user_username .= rand(1, 10);
      }

      $mediuser->store();
    }

    return $mediuser;
  }

  /**
   * Get the H evenement
   *
   * @return array
   */
  function getHEvenementXML() {
    $data = array();

    $H = $this->queryNode("H", null, $foo, true);

    $data['dateHeureProduction'] = CMbDT::dateTime($this->queryTextNode("H.13/TS.1", $H));
    $data['filename']            = $this->queryTextNode("H.2", $H);

    return $data;
  }

  /**
   * Get the content nodes
   *
   * @return array
   */
  function getContentNodes() {
    $data  = array();

    return $data;
  }

  /**
   * Handle
   *
   * @param CHPrimSanteAcknowledgment $ack        Acknowledgment
   * @param CMbObject                 $newPatient Object
   * @param String                    $data       data
   *
   * @return void
   */
  function handle(CHPrimSanteAcknowledgment $ack, CMbObject $newPatient, $data) {
  }
}
