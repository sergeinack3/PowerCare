<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Interop\Ftp\CFTP;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbPath;
use Ox\Core\CMbXMLDocument;
use Ox\Core\CValue;
use Ox\Interop\Ftp\CSourceFTP;
use Ox\Mediboard\Labo\CPrescriptionLabo;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\System\CExchangeSource;

/**
 * Redirection
 *
 * @return void
 */
function redirect() {
  echo CAppUI::getMsg();
  CApp::rip();
}

if (!class_exists("DOMDocument")) {
  trigger_error("sorry, DOMDocument is needed");
  return;
}

CCanDo::checkRead();

$doc = new CMbXMLDocument();

$doc->setSchema("modules/dPlabo/remote/prescription.xsd");
if (!$doc->checkSchema()) {
  CAppUI::setMsg("Schema manquant", UI_MSG_ERROR );
  redirect();
}

$mbPrescription = new CPrescriptionLabo();

// Chargement de la prescription
$mb_prescription_id = CValue::post("prescription_labo_id", null);
if (!$mb_prescription_id) {
  CAppUI::setMsg("Veuillez spécifier une prescription", UI_MSG_ERROR );
  redirect();
}
if ($mbPrescription->load($mb_prescription_id)) {
  $mbPrescription->loadRefs();
}

// Chargement de l'id400 "labo code4" du praticien
$prat =& $mbPrescription->_ref_praticien;
$tagCode4 = "labo code4";
$idSantePratCode4 = new CIdSante400();
$idSantePratCode4->loadLatestFor($prat, $tagCode4);

// Chargement de l'id400 "labo code9" du praticien
$tagCode9 = "labo code9";
$idSantePratCode9 = new CIdSante400();
$idSantePratCode9->loadLatestFor($prat, $tagCode9);

// Si le praticien n'a pas d'id400, il ne peut pas envoyer la prescription
if (!$idSantePratCode4->_id || !$idSantePratCode9->_id) {
  CAppUI::setMsg("Le praticien n'a pas d'id400 pour le catalogue LABO", UI_MSG_ERROR );
  redirect();
}

$tagCatalogue = CAppUI::gconf("dPlabo CCatalogueLabo remote_name");

// Chargement de la valeur de l'id externe de la prescription ==> retourne uniquement l'id400
$idexPresc = $mbPrescription->loadIdPresc();

// Gestion du sexe du patient
$transSexe["m"] = "1";
$transSexe["f"] = "2";

$mbPatient =& $mbPrescription->_ref_patient;

// Gestion du titre du patient
if ($mbPatient->sexe == "m") {
  if ($mbPatient->_annees >= 0 && $mbPatient->_annees <= 3) {
    $titre_ = "Bébé garçon";
  }
  if ($mbPatient->civilite == "enf") {
    $titre_ = "Enfant garçon";
  }
  if ($mbPatient->civilite == "m") {
    $titre_ = "Monsieur";
  }
}

if ($mbPatient->sexe == "f") {
  if ($mbPatient->_annees >= 0 && $mbPatient->_annees <= 3) {
    $titre_ = "Bébé fille";
  }
  if ($mbPatient->civilite == "enf") {
    $titre_ = "Enfant fille";
  }
  if ($mbPatient->civilite == "mlle") {
    $titre_ = "Mademoiselle";
  }
  if ($mbPatient->civilite == "mme") {
    $titre_ = "Madame";
  }
}

$transTitre["Monsieur"]      = "1"; 
$transTitre["Madame"]        = "2"; 
$transTitre["Mademoiselle"]  = "3"; 
$transTitre["Enfant garçon"] = "4"; 
$transTitre["Enfant fille"]  = "5"; 
$transTitre["Bébé garçon"]   = "6"; 
$transTitre["Bébé fille"]    = "7"; 
$transTitre["Docteur"]       = "8"; 
$transTitre["Doctoresse"]    = "A"; 

$doc->setDocument("tmp/Prescription-".$mbPrescription->_id.".xml");

// Creation de la prescription
$prescription     = $doc->addElement($doc, "Prescription");

// Prescription --> Numero
$num_prat = str_pad($idSantePratCode4->id400, 4, '0', STR_PAD_LEFT);
$num_presc = $idexPresc;
$num_presc %= 1000;
$num_presc = str_pad($num_presc, 4, '0', STR_PAD_LEFT);

$numero           = $doc->addElement($prescription, "numero", $num_prat.$num_presc);

// Prescription --> Patient
$patient          = $doc->addElement($prescription, "Patient");
$nom              = $doc->addElement($patient, "nom", $mbPatient->nom);
$prenom           = $doc->addElement($patient, "prenom", $mbPatient->prenom);
$titre            = $doc->addElement($patient, "titre", $transTitre[$titre_]);
$sexe             = $doc->addElement($patient, "sexe", $transSexe[$mbPatient->sexe]);
$datenaissance    = $doc->addElement($patient, "datenaissance", CMbDT::format($mbPatient->naissance, "%Y%m%d"));
$adresseligne1    = $doc->addElement($patient, "adresseligne1", $mbPatient->adresse);
$adresseligne2    = $doc->addElement($patient, "adresseligne2", "");
$codepostal       = $doc->addElement($patient, "codepostal", $mbPatient->cp);
$ville            = $doc->addElement($patient, "ville", $mbPatient->ville);
$pays             = $doc->addElement($patient, "pays", $mbPatient->pays);
$assurance        = $doc->addElement($patient, "assurance", $mbPatient->regime_sante);

// Prescription --> Dossier 
$dossier          = $doc->addElement($prescription, "Dossier");
$dateprelevement  = $doc->addElement($dossier, "dateprelevement", CMbDT::format(CMbDT::date($mbPrescription->date), "%Y%m%d"));
$heureprelevement = $doc->addElement($dossier, "heureprelevement", CMbDT::time($mbPrescription->date));
$urgent           = $doc->addElement($dossier, "urgent", $mbPrescription->urgence);
$afaxer           = $doc->addElement($dossier, "afaxer", "");
$atelephoner      = $doc->addElement($dossier, "atelephoner", "");

// Prescription --> Analyse
$analyse          = $doc->addElement($prescription, "Analyse"); 


// Tableau d'analyses
$tab_prescription = array();

// Parcours des analyses
foreach ($mbPrescription->_ref_prescription_items as $key => $item) {
  // Si l'analyse fait parti d'un pack, on stocke le code du pack
  if ($item->_ref_pack->_id) {
    $tab_prescription[$item->_ref_pack->_id] = $item->_ref_pack->code;  
  }
  // Sinon, on stocke l'identifiant de l'analyse si elle est externe
  else {
    $examen =& $item->_ref_examen_labo;
    if ($examen->_external) {
      $tab_prescription[] = $examen->identifiant;    
    }
  }
}

// Ajout des codes dans le fichier xml
foreach ($tab_prescription as $curr_analyse) {
  $code = $doc->addElement($analyse, "code", $curr_analyse);
}

// Prescription -> Prescripteur
$prescripteur = $doc->addElement($prescription, "Prescripteur");
$code9 = $doc->addElement($prescripteur, "Code9", $idSantePratCode9->id400);
$code4 = $doc->addElement($prescripteur, "Code4", $idSantePratCode4->id400);

// Sauvegarde du fichier temporaire
$tmpPath = "tmp/dPlabo/export_prescription.xml";
CMbPath::forceDir(dirname($tmpPath));
$doc->save($tmpPath);
$doc->load($tmpPath);

// Validation du document
if (!$doc->schemaValidate()) {
  CAppUI::setMsg("Document de prescription non valide", UI_MSG_ERROR );
  redirect();
}

// Envoi de la prescription par sur un seveurFTP
// Envoi à la source créée 'PrescriptionLabo' (FTP)
$prescriptionlabo_source = CExchangeSource::get("prescriptionlabo", CSourceFTP::TYPE);
// Creation du FTP
$ftp = new CFTP;
$ftp->init($prescriptionlabo_source);

if (!$ftp->hostname) {
  CAppUI::setMsg("Le document n'a pas pu être envoyé, configuration FTP manquante", UI_MSG_ERROR );
  redirect();
}

// Transfert
$destination_basename = "Prescription-".$mbPrescription->_id;
$file = "tmp/dPlabo/export_prescription.xml";

try {
  $ftp->connect();
  $ftp->sendFile($file, "$destination_basename.xml");
} catch (CMbException $e) {
  $e->stepAjax();
  $ftp->close(); 
  redirect();
}

$ftp->close();

CAppUI::setMsg("Document envoyé", UI_MSG_OK );

// Créer le document joint
if ($msg = $doc->addFile($mbPrescription)) {  
  CAppUI::setMsg("Document non attaché à la prescription: $msg", UI_MSG_ERROR );
}
redirect();
