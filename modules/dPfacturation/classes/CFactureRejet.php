<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Facturation;

use Ox\Core\CApp;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbXMLDocument;
use Ox\Core\CMbXPath;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Patients\CCorrespondantPatient;
use Ox\Mediboard\Patients\CPatient;

/**
 * Trace des rejets de facture par les assurances
 */
class CFactureRejet extends CMbObject {

  // DB Table key
  public $facture_rejet_id;

  // DB Fields
  public $praticien_id;
  public $file_name;
  public $num_facture;
  public $date;
  public $motif_rejet;
  public $statut;
  public $name_assurance;
  public $traitement;
  public $facture_id;
  public $facture_class;

  // Object References
  public $_date_facture;
  public $_avs;
  public $_patient;
  public $_commentaire;
  public $_erreurs;
  public $_status_in;
  public $_status_out;
  public $_pending = 0;
  public $_contact = array();

  public $_ref_file_xml;
  public $_ref_facture;

  /**
   * @see parent::getSpec()
   */
  function getSpec() {
    $spec = parent::getSpec();
    $spec->table = 'facture_rejet';
    $spec->key   = 'facture_rejet_id';
    return $spec;
  }

  /**
   * @see parent::getProps()
   */
  function getProps() {
    $props = parent::getProps();
    $props["praticien_id"]  = "ref class|CMediusers back|rejets_prat";
    $props["file_name"]     = "str";
    $props["num_facture"]   = "str";
    $props["date"]          = "date";
    $props["motif_rejet"]   = "text";
    $props["name_assurance"]= "str";
    $props["statut"]        = "enum list|attente|traite default|attente";
    $props["traitement"]    = "dateTime";

    $props["facture_id"]    = "ref class|CFacture meta|facture_class back|rejets";
    $props["facture_class"] = "enum list|CFactureCabinet|CFactureEtablissement show|0";

    $props["_date_facture"] = "date";
    $props["_patient"] = "ref class|CPatient";
    $props["_contact"] = "str";
    return $props;
  }

  /**
   * Load file
   *
   * @return CFile
   */
  function loadRefFile() {
    $this->loadRefsFiles();
    return $this->_ref_file_xml = reset($this->_ref_files);
  }

  /**
   * Chargement de la facture
   *
   * @return CFacture
   */
  function loadRefFacture() {
    return $this->_ref_facture = $this->loadFwdRef("facture_id", true);
  }

  /**
   * Lecture du fichier XML associé au rejet
   *
   * @param string $content_file contenu du fichier
   * @param bool   $details      enregistrement des informations
   *
   * @return null
   */
  function readXML($content_file = null, $details = false) {
    if (!$content_file) {
      $file = $this->loadRefFile();
      if (!$file) {
        return;
      }
      $file->updateFormFields();
      if (!is_readable($file->_file_path)) {
        return;
      }
      $content_file = file_get_contents($file->_file_path);
    }

    $doc = new CMbXMLDocument();
    $doc->loadXML($content_file);

    $xpath = new CMbXPath($doc);
    $xpath->registerNamespace("invoice", "http://www.forum-datenaustausch.ch/invoice");

    $payload = $xpath->queryUniqueNode("//invoice:payload");
    $timestamp = $xpath->getValueAttributNode($payload, "response_timestamp");
    $this->date = CMbDT::strftime ('%Y-%m-%d', $timestamp);

    $invoice = $xpath->queryUniqueNode("//invoice:invoice");
    $this->num_facture   = $xpath->getValueAttributNode($invoice, "request_id");
    $this->_date_facture = $xpath->getValueAttributNode($invoice, "request_date");

    $insurance = $xpath->queryUniqueNode("//invoice:insurance");
    $ean_party = $xpath->getValueAttributNode($insurance, "ean_party");
    $corr = new CCorrespondantPatient();
    $corr->ean = $ean_party;
    $corr->loadMatchingObject();
    $this->name_assurance = $corr->nom;

    $patient = $xpath->queryUniqueNode("//invoice:patient");
    $this->_avs = $xpath->getValueAttributNode($patient, "ssn");

    if ($company = $xpath->queryUniqueNode("//invoice:contact/invoice:company")) {
      $this->_contact[] = $xpath->queryTextNode("invoice:companyname", $company);
      $this->_contact[] = $xpath->queryTextNode("invoice:department", $company);
      $this->_contact[] = $xpath->queryTextNode("invoice:subaddressing", $company);

      $postal = $xpath->queryUniqueNode("invoice:postal", $company);
      $this->_contact[] = $xpath->queryTextNode("invoice:pobox", $postal);
      $this->_contact[] = $xpath->queryTextNode("invoice:street", $postal);
      $this->_contact[] = $xpath->queryTextNode("invoice:zip", $postal)." ".$xpath->queryTextNode("invoice:city", $postal);

      $this->_contact[] = $xpath->queryTextNode("invoice:phone", $xpath->queryUniqueNode("invoice:telecom", $company));
      $this->_contact[] = $xpath->queryTextNode("invoice:email", $xpath->queryUniqueNode("invoice:online", $company));
    }

    if ($employee = $xpath->queryUniqueNode("//invoice:contact/invoice:employee")) {
      $this->_contact[] = $xpath->getValueAttributNode($employee, "salutation")." ".$xpath->queryTextNode("invoice:givenname", $employee)." ".$xpath->queryTextNode("invoice:familyname", $employee);
      $this->_contact[] = $xpath->queryTextNode("invoice:phone", $xpath->queryUniqueNode("invoice:telecom", $employee));
      $this->_contact[] = $xpath->queryTextNode("invoice:email", $xpath->queryUniqueNode("invoice:online", $employee));
    }

    if ($person = $xpath->queryUniqueNode("//invoice:contact/invoice:person")) {
      $this->_contact[] = $xpath->getValueAttributNode($person, "salutation")." ".$xpath->queryTextNode("invoice:givenname", $person)." ".$xpath->queryTextNode("invoice:familyname", $person);
      $this->_contact[] = $xpath->queryTextNode("invoice:phone", $xpath->queryUniqueNode("invoice:subaddressing", $person));
      $postal = $xpath->queryUniqueNode("invoice:postal", $person);
      $this->_contact[] = $xpath->queryTextNode("invoice:pobox", $postal);
      $this->_contact[] = $xpath->queryTextNode("invoice:street", $postal);
      $this->_contact[] = $xpath->queryTextNode("invoice:zip", $postal)." ".$xpath->queryTextNode("invoice:city", $postal);
      $this->_contact[] = $xpath->queryTextNode("invoice:phone", $xpath->queryUniqueNode("invoice:telecom", $person));
      $this->_contact[] = $xpath->queryTextNode("invoice:email", $xpath->queryUniqueNode("invoice:online", $person));
    }

    $this->_contact = array_filter($this->_contact);

    $pending = $xpath->query("//invoice:pending");
    foreach ($pending as $_pending) {
      $explanation = $xpath->queryTextNode("invoice:explanation", $_pending);
      $this->motif_rejet  = "$explanation \r\n";
      $this->_commentaire = $explanation;
      $this->_status_in  = $xpath->getValueAttributNode($_pending, "status_in");
      $this->_status_out = $xpath->getValueAttributNode($_pending, "status_out");

      $nb_message = 0;
      $messages = $xpath->query("//invoice:message");
      foreach ($messages as $_message)  {
        $code = $xpath->getValueAttributNode($_message, "code");
        $text = $xpath->getValueAttributNode($_message, "text");
        if (!$details) {
          $this->motif_rejet .= "$code: $text \r\n";
        }
        else {
          $this->_erreurs[$nb_message]['code'] = $code;
          $this->_erreurs[$nb_message]['text'] = $text;
        }
        $nb_message++;
      }
      $this->_pending = 1;
    }

    $rejected = $xpath->query("//invoice:rejected");
    foreach ($rejected as $_rejected) {
      $explanation = $xpath->queryTextNode("invoice:explanation", $_rejected);
      $this->motif_rejet = "$explanation \r\n";
      $this->_commentaire = $explanation;
      $this->_status_in  = $xpath->getValueAttributNode($_rejected, "status_in");
      $this->_status_out = $xpath->getValueAttributNode($_rejected, "status_out");

      $nb_message = 0;
      $messages = $xpath->query("//invoice:error");
      foreach ($messages as $_message)  {
        $code = $xpath->getValueAttributNode($_message, "code");
        $text = $xpath->getValueAttributNode($_message, "text");
        if (!$details) {
          $this->motif_rejet .= "$code: $text \r\n";
        }
        else {
          $this->_erreurs[$nb_message]['code'] = $code;
          $this->_erreurs[$nb_message]['text'] = $text;
        }

        if ($error_value = $xpath->getValueAttributNode($_message, "error_value")) {
          $valid_value = $xpath->getValueAttributNode($_message, "valid_value");

          if (!$details) {
            $this->motif_rejet .= "($error_value/$valid_value)";
          }
          else {
            $this->_erreurs[$nb_message]['error_value'] = $error_value;
            $this->_erreurs[$nb_message]['valid_value'] = $valid_value;
            $this->_erreurs[$nb_message]['record_id']   = $xpath->getValueAttributNode($_message, "record_id");
          }
        }
        $nb_message++;
      }
    }

    if (!$details) {
      if ($msg = $this->store()) {
        CApp::log($msg);
      }
    }
  }

  /**
   * Chargement du patient
   *
   * @return null|CPatient
   */
  function loadRefPatient() {
    if (!$this->_avs) {
      return null;
    }

    $patient = new CPatient();
    $patient->avs = $this->_avs;
    $patient->loadMatchingObject();
    return $this->_patient = $patient;
  }
}
