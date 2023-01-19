<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

global $can, $m, $remote_name;

use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CMbPath;
use Ox\Core\CMbXMLDocument;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Labo\CExamenLabo;
use Ox\Mediboard\Labo\CPackExamensLabo;
use Ox\Mediboard\Labo\CPackItemExamenLabo;
use Ox\Mediboard\Sante400\CIdSante400;

$can->needsAdmin();

/**
 * Packs imports
 *
 * @param SimpleXMLElement $packs Liste des packs
 *
 * @throws Exception
 *
 * @return void
 */
function importPacks($packs){
  global $m, $remote_name;

  // Chargement des identifiants externes des packs
  $idPackExamen = new CIdSante400();
  $idPackExamen->tag = $remote_name;
  $idPackExamen->object_class = "CPackExamensLabo";
  $idPackExamens = $idPackExamen->loadMatchingList();

  // Parcours des identifiants externes des Packs d'examens
  foreach ($idPackExamens as $_id_pack_examen) {
    $packExamen = new CPackExamensLabo();
    $packExamen->load($_id_pack_examen->object_id);

    // Chargement des items de packs
    $packExamen->loadRefsItemExamenLabo();

    // On vide chaque pack
    foreach ($packExamen->_ref_items_examen_labo as $_packItemExamen) {
      // Chargement de l'examen labo pour obtenir l'identifiant necessaire pour supprime l'id externe
      $_packItemExamen->loadRefExamen();

      // Suppression de l'id400 du packItem
      $_id_pack_examen = new CIdSante400();
      $_id_pack_examen->tag = $remote_name;
      $_id_pack_examen->object_class = $_packItemExamen->_class;
      $_id_pack_examen->object_id = $_packItemExamen->_id;
      $_id_pack_examen->loadMatchingObject();

      if ($_id_pack_examen->_id) {
        $_id_pack_examen->delete();
      }
      // Suppression du pack item
      $_packItemExamen->delete();
    }
    if ($packExamen->_id) {
      $packExamen->obsolete = 1;
      $packExamen->store();
    }
  }

  // Nombre de packs et d'analyses
  $nb["packs"] = 0;
  $nb["analysesOK"] = 0;
  $nb["analysesKO"] = 0;

  // Liste des analyses nono trouvees
  $erreurs = array();

  // On crée chaque pack ainsi qu'un id400 associé
  foreach ($packs->bilan as $_pack) {
    $pack = new CPackExamensLabo();
    $pack->function_id = "";
    $pack->libelle = utf8_decode((string) $_pack->libelle);
    $pack->code = (int) $_pack->code;

    // Sauvegarde du pack
    $idPack = new CIdSante400();
    // tag des id externe des packs => nom du laboatoire ==> LABO
    $idPack->tag = $remote_name;
    $idPack->id400 = (int) $_pack->code;

    $pack->obsolete = 0;
    $idPack->bindObject($pack);

    // On crée les analyses correspondantes
    foreach ($_pack->analyses->cana as $_analyse) {
      // Creation de l'analyse
      $analyse = new CPackItemExamenLabo();

      // Chargement de l'analyse
      $examLabo = new CExamenLabo();
      $whereExam = array();
      $whereExam['identifiant'] = (string) " = '$_analyse'";
      $examLabo->loadObject($whereExam);

      if ($examLabo->_id) {
        $analyse->pack_examens_labo_id = $pack->_id;
        $analyse->examen_labo_id = $examLabo->examen_labo_id;

        // Sauvegarde de l'analyse et de son id400
        $idExamen = new CIdSante400();
        $idExamen->tag = $remote_name;
        $idExamen->id400 = (string) $_analyse;

        $idExamen->bindObject($analyse);
        $nb["analysesOK"]++;
      }
      else {
        $erreurs[][(string) $_pack->libelle] = (string) $_analyse;
        $nb["analysesKO"]++;
      }
    }
    $nb["packs"]++;
  }

  // Recapitulatif des importations
  CAppUI::stepAjax("Packs Importés: ".$nb["packs"], UI_MSG_OK);
  CAppUI::stepAjax("Analyses Importées: ".$nb["analysesOK"], UI_MSG_OK);
  CAppUI::stepAjax("Analyses non importées: ".$nb["analysesKO"], UI_MSG_WARNING);
  foreach ($erreurs as $erreur) {
    foreach ($erreur as $_key => $_erreur) {
      CAppUI::stepAjax("Analyse non trouvée: ".$_erreur." dans le pack ".utf8_decode($_key), UI_MSG_WARNING);
    }
  }
}


// Check import configuration
if (null == $remote_name = CAppUI::gconf("$m CCatalogueLabo remote_name")) {
  CAppUI::stepAjax("Remote name not configured", UI_MSG_ERROR);
}

if (null == $remote_url = CAppUI::gconf("$m CPackExamensLabo remote_url")) {
  CAppUI::stepAjax("Remote URL not configured", UI_MSG_ERROR);
}

if (false === $content = file_get_contents($remote_url)) {
  CAppUI::stepAjax("Couldn't connect to remote url", UI_MSG_ERROR);
}


// Check imported catalogue document
$doc = new CMbXMLDocument;

if (!$doc->loadXML($content)) {
  CAppUI::stepAjax("Document is not well formed", UI_MSG_ERROR);
}

$tmpPath = "tmp/dPlabo/import_packs.xml";
CMbPath::forceDir(dirname($tmpPath));
$doc->save($tmpPath);
$doc->load($tmpPath);

if (!$doc->schemaValidate("modules/$m/remote/packs.xsd")) {
  CAppUI::stepAjax("Document is not valid", UI_MSG_ERROR);
}

CAppUI::stepAjax("Document is valid", UI_MSG_OK);

// Check access to idSante400
$canSante400 = CModule::getCanDo("dPsante400");
if (!$canSante400->edit) {
  CAppUI::stepAjax("No permission for module 'dPsante400' or module not installed", UI_MSG_ERROR);
}

// Import packs
$packs = new SimpleXMLElement($content);
try {
  importPacks($packs);
} 
catch (CMbException $e) {
  $e->stepAjax();
}
