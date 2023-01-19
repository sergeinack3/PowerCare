<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\AppFine\Client\CAppFineClientOrderPackProtocole;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Bloc\CBesoinRessource;
use Ox\Mediboard\Bloc\CTypeRessource;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CChargePriceIndicator;
use Ox\Mediboard\PlanningOp\CProtocole;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkAdmin();
$file   = CValue::files("import");
$dryrun = CView::post("dryrun", "bool");
$csv_result = CView::post("csv_result", "bool");
CView::checkin();

$results = array();
//tags à importer
$idex_names = array();
$unfound = array();
$i = 0;

$group_id = CGroups::loadCurrent()->_id;
$ds       = CSQLDataSource::get('std');

$systeme_materiel = CAppUI::gconf("dPbloc CPlageOp systeme_materiel") === "expert";
$counts = array(
  "error" => 0,
  "created" => 0,
  "updated" => 0
);

if ($file && ($fp = fopen($file['tmp_name'], 'r'))) {
  // Object columns on the first line
  $cols = fgetcsv($fp, null, ";");

  // Each line
  while ($line = fgetcsv($fp, null, ";")) {
    $i++;

    // Skip empty lines
    if ((!isset($line[0]) || $line[0] == "") && ((!isset($line[2]) || $line[1] == "") && (!isset($line[3]) || $line[3] == ""))) {
      continue;
    }

    // Parsing
    $line                                  = array_map("trim", $line);
    $line                                  = array_map("addslashes", $line);
    $results[$i]["function_name"]          = CMbArray::get($line, 0);
    $results[$i]["praticien_lastname"]     = CMbArray::get($line, 1);
    $results[$i]["praticien_firstname"]    = CMbArray::get($line, 2);
    $results[$i]["motif"]                  = CMbArray::get($line, 3);
    $results[$i]["libelle_sejour"]         = CMbArray::get($line, 4);
    $results[$i]["temp_operation"]         = CMbArray::get($line, 5);
    $results[$i]["codes_ccam"]             = CMbArray::get($line, 6);
    $results[$i]["DP"]                     = CMbArray::get($line, 7);
    $results[$i]["type_hospi"]             = CMbArray::get($line, 8);
    $results[$i]["duree_hospi"]            = CMbArray::get($line, 9);
    $results[$i]["duree_uscpo"]            = CMbArray::get($line, 10);
    $results[$i]["duree_preop"]            = CMbArray::get($line, 11);
    $results[$i]["presence_preop"]         = CMbArray::get($line, 12);
    $results[$i]["presence_postop"]        = CMbArray::get($line, 13);
    $results[$i]["uf_hebergement"]         = CMbArray::get($line, 14);
    $results[$i]["uf_medicale"]            = CMbArray::get($line, 15);
    $results[$i]["uf_soins"]               = CMbArray::get($line, 16);
    $facturable                            = CMbArray::get($line, 17);
    $results[$i]["facturable"]             = $facturable !== "" ? $facturable : "1";
    $results[$i]["RRAC"]                   = CMbArray::get($line, 18);
    $for_sejour                            = CMbArray::get($line, 19);
    $results[$i]["for_sejour"]             = $for_sejour !== "" ? $for_sejour : "0";
    $results[$i]["Exam_extempo_prevu"]     = CMbArray::get($line, 20);
    $results[$i]["cote"]                   = CMbArray::get($line, 21);
    $results[$i]["bilan_preop"]            = CMbArray::get($line, 22);
    $results[$i]["materiel_a_prevoir"]     = CMbArray::get($line, 23);
    $results[$i]["examens_perop"]          = CMbArray::get($line, 24);
    $results[$i]["depassement_honoraires"] = CMbArray::get($line, 25);
    $results[$i]["forfait_clinique"]       = CMbArray::get($line, 26);
    $results[$i]["fournitures"]            = CMbArray::get($line, 27);
    $results[$i]["rques_interv"]           = CMbArray::get($line, 28);
    $results[$i]["convalesence"]           = CMbArray::get($line, 29);
    $results[$i]["rques_sejour"]           = CMbArray::get($line, 30);
    $results[$i]["septique"]               = CMbArray::get($line, 31);
    $results[$i]["duree_heure_hospi"]      = CMbArray::get($line, 32);
    $results[$i]["pathologie"]             = CMbArray::get($line, 33);
    $results[$i]["type_pec"]               = CMbArray::get($line, 34);
    $results[$i]["hospit_de_jour"]         = CMbArray::get($line, 35);
    $results[$i]["service"]                = CMbArray::get($line, 36);
    $results[$i]["time_entree_prevue"]     = CMbArray::get($line, 37);
    $results[$i]["charge_price_indicator"] = CMbArray::get($line, 38);

    $key = 38;

    if ($systeme_materiel) {
      $key++; // 39
      $results[$i]["_ref_besoins"] = CMbArray::get($line, $key);
    }

    if (CModule::getActive("appFineClient")) {
      $key++; // 40
      $results[$i]["_ref_packs_appFine"] = CMbArray::get($line, $key);
    }

    // type de circuit en ambulatoire
    $key++; // 39 or 40 or 41
    $results[$i]["circuit_ambu"] = CMbArray::get($line, $key);

    // actif
    $key++; // 40 or 41 or 42
    $actif = CMbArray::get($line, $key);
    $results[$i]["actif"] = $actif !== "" ? $actif : "1";

    //identifiants externes : récupération des noms de tags (si pas encore présent dans la liste) et de l'identifiant, pour chaque ligne
    while (count($line) > $key + 1) {
      $key++;
      if (!in_array($cols[$key], $idex_names)) {
        $idex_names[] = $cols[$key];
      }
      $results[$i][$cols[$key]] = CMbArray::get($line, $key);
    }

    // Type d'hopistalisation
    $results[$i]["type_hospi"] = CValue::first(strtolower($results[$i]["type_hospi"]), "comp");
    if ($results[$i]["type_hospi"] == "hospi") {
      $results[$i]["type_hospi"] = "comp";
    }
    if ($results[$i]["type_hospi"] == "ambu") {
      $results[$i]["duree_hospi"] = 0;
    }

    $results[$i]["errors"] = array();

    if ($results[$i]["praticien_lastname"] === "" &&
      $results[$i]["praticien_firstname"] === "" &&
      $results[$i]["function_name"] === "") {
      $results[$i]["errors"][] = "Fonction et utilisateur manquants";
    }

    if ($results[$i]["motif"] === "" && $results[$i]["libelle_sejour"] === "") {
      $results[$i]["errors"][] = CAppUI::tr("CProtocole-libelle") . " " .
        CAppUI::tr("and") . " " . CAppUI::tr("CProtocole-libelle_sejour") . "  manquants";
    }
    // Fonction
    $function           = new CFunctions();
    $function->group_id = $group_id;
    $function->text     = $results[$i]["function_name"];
    $function->loadMatchingObject();

    // Praticien
    $prat      = new CMediusers();
    $lastname  = $results[$i]["praticien_lastname"];
    $firstname = $results[$i]["praticien_firstname"];
    if ($lastname) {
      $ljoin                          = array();
      $ljoin["users"]                 = "users.user_id = users_mediboard.user_id";
      $where                          = array();
      $where["users.user_last_name"]  = "= '$lastname'";
      $where["users.user_first_name"] = "= '$firstname'";
      //$where["users_mediboard.function_id"] = "= '$function->_id'";
      $prat->loadObject($where, null, null, $ljoin);
    }

    if (!$function->_id && !$prat->_id) {
      $results[$i]["errors"][]                  = "Fonction et utilisateur non trouvé";
      $unfound["praticien_lastname"][$lastname] = true;
    }

    $service = null;
    if ($results[$i]['service']) {
      $service = new CService();
        $where = [
            "service.nom"      => $ds->prepareLike("%{$results[$i]['service']}%"),
            "service.group_id" => $ds->prepare('= ?', $group_id),
        ];
        
        $service->loadObject($where);

      if (!$service || !$service->_id) {
        $results[$i]["errors"][] = "Service non trouvé";
        $service                 = null;
      }
    }

    // Protocole
    $protocole             = new CProtocole();
    $protocole->_time_op   = null;
    $protocole->for_sejour = $results[$i]["for_sejour"];
    if (isset($results[$i]["motif"]) && $results[$i]["motif"] != "") {
      $protocole->libelle = $results[$i]["motif"];
    }
    if (isset($results[$i]["libelle_sejour"]) && $results[$i]["libelle_sejour"] != "") {
      $protocole->libelle_sejour = $results[$i]["libelle_sejour"];
    }
    if (isset($results[$i]["type_hospi"]) && isset($results[$i]["type_hospi"]) != "") {
        $protocole->type = $results[$i]["type_hospi"];
    }
    if ($prat->_id) {
      $protocole->chir_id = $prat->_id;
    }
    else {
      $protocole->function_id = $function->_id;
    }

    // Mise à jour du protocole éventuel existant
    $protocole->loadMatchingObject();

    $protocole->type               = $results[$i]["type_hospi"];
    $protocole->duree_hospi        = $results[$i]["duree_hospi"];
    $protocole->temp_operation     = $results[$i]["temp_operation"] ? $results[$i]["temp_operation"] . ":00" : "";
    $protocole->codes_ccam         = $results[$i]["codes_ccam"];
    $protocole->DP                 = $results[$i]["DP"];
    $protocole->duree_uscpo        = $results[$i]["duree_uscpo"];
    $protocole->duree_preop        = $results[$i]["duree_preop"] ? $results[$i]["duree_preop"] . ":00" : "";
    $protocole->presence_preop     = $results[$i]["presence_preop"] ? $results[$i]["presence_preop"] . ":00" : "";
    $protocole->presence_postop    = $results[$i]["presence_postop"] ? $results[$i]["presence_postop"] . ":00" : "";
    $protocole->facturable         = $results[$i]["facturable"];
    $protocole->RRAC               = $results[$i]["RRAC"];
    $protocole->exam_extempo       = $results[$i]["Exam_extempo_prevu"];
    $protocole->cote               = $results[$i]["cote"];
    $protocole->examen             = $results[$i]["bilan_preop"];
    $protocole->materiel           = $results[$i]["materiel_a_prevoir"];
    $protocole->exam_per_op        = $results[$i]["examens_perop"];
    $protocole->depassement        = $results[$i]["depassement_honoraires"];
    $protocole->forfait            = $results[$i]["forfait_clinique"];
    $protocole->fournitures        = $results[$i]["fournitures"];
    $protocole->rques_operation    = $results[$i]["rques_interv"];
    $protocole->convalescence      = $results[$i]["convalesence"];
    $protocole->rques_sejour       = $results[$i]["rques_sejour"];
    $protocole->septique           = $results[$i]["septique"];
    $protocole->duree_heure_hospi  = $results[$i]["duree_heure_hospi"];
    $protocole->pathologie         = $results[$i]["pathologie"];
    $protocole->type_pec           = $results[$i]["type_pec"];
    $protocole->hospit_de_jour     = $results[$i]["hospit_de_jour"];
    $protocole->service_id         = ($service) ? $service->_id : '';
    $protocole->time_entree_prevue = $results[$i]["time_entree_prevue"];
    // type de circuit en ambulatoire
    $protocole->circuit_ambu = $results[$i]["circuit_ambu"];
    // protocole actif ou non
    $protocole->actif = $results[$i]["actif"];

      if ($protocole->temp_operation) {
          $protocole->_time_op   = $protocole->temp_operation;
      }

    // UF Hébergement
    if ($uf_hebergement = $results[$i]["uf_hebergement"]) {
      $uf           = new CUniteFonctionnelle();
      $uf->code     = $uf_hebergement;
      $uf->type     = "hebergement";
      $uf->group_id = $group_id;
      $uf->loadMatchingObject();
      if ($uf->_id) {
        $protocole->uf_hebergement_id = $uf->_id ? $uf->_id : "";
      }
      else {
        $results[$i]["errors"][]                    = "UF hébergement non trouvée";
        $unfound["uf_hebergement"][$uf_hebergement] = true;
      }
    }

    // UF Médicale
    if ($uf_medicale = $results[$i]["uf_medicale"]) {
      $uf           = new CUniteFonctionnelle();
      $uf->code     = $uf_medicale;
      $uf->type     = "medicale";
      $uf->group_id = $group_id;
      $uf->loadMatchingObject();
      if ($uf->_id) {
        $protocole->uf_medicale_id = $uf->_id ? $uf->_id : "";
      }
      else {
        $results[$i]["errors"][]              = "UF médicale non trouvée";
        $unfound["uf_medicale"][$uf_medicale] = true;
      }
    }

    // UF Soins
    if ($uf_soins = $results[$i]["uf_soins"]) {
      $uf           = new CUniteFonctionnelle();
      $uf->code     = $uf_soins;
      $uf->type     = "soins";
      $uf->group_id = $group_id;
      $uf->loadMatchingObject();
      if ($uf->_id) {
        $protocole->uf_soins_id = $uf->_id ? $uf->_id : "";
      }
      else {
        $results[$i]["errors"][]        = "UF de soins non trouvée";
        $unfound["uf_soins"][$uf_soins] = true;
      }
    }

    if ($charge_price_indicator_code = $results[$i]["charge_price_indicator"]) {
      $charge_price_indicator = new CChargePriceIndicator();

      $where = array(
        "group_id" => "= '$group_id'",
        "actif"    => "= '1'"
      );

        $where[] = "code = '$charge_price_indicator_code' OR code = '0$charge_price_indicator_code'";

      if ($protocole->type) {
        $where[] = "type = '$protocole->type'";
      }

      if ($charge_price_indicator->loadObject($where)) {
        $protocole->charge_id = $charge_price_indicator->_id;
      }
      else {
        $results[$i]["errors"][]                                         = "Mode de traitement non trouvé";
        $unfound["charge_price_indicator"][$charge_price_indicator_code] = true;
      }
    }

    if ($protocole->duree_hospi === "") {
      $results[$i]["errors"][] = "Aucune durée d'hospitalisation";
    }
    if (!$protocole->for_sejour) {
      if (($protocole->libelle == "" && $protocole->codes_ccam == "") ) {
        $results[$i]["errors"][] = "Libellé d'intervention et codes CCAM manquants";
      }
      if ($protocole->temp_operation == "") {
        $results[$i]["errors"][] = "Aucune durée d'intervention prévue";
      }
    }

    // No store on errors
    if (count($results[$i]["errors"])) {
      $counts["error"]++;
      continue;
    }

    $protocole->unescapeValues();
    $existing = $protocole->_id;

    $counts[$existing ? "updated" : "created"]++;

    // Dry run to check references
    if ($dryrun) {
      continue;
    }

    // Creation
    if ($msg = $protocole->store()) {
      if (!$csv_result) {
        CAppUI::setMsg($msg, UI_MSG_ERROR);
      }
      $results[$i]["errors"][] = $msg;
      continue;
    }

    // Besoins en matériel
    if ($systeme_materiel && $_ref_besoins = $results[$i]["_ref_besoins"]) {
      foreach (explode("|", $_ref_besoins) as $_besoin) {
        $type_ressource           = new CTypeRessource();
        $type_ressource->libelle  = $_besoin;
        $type_ressource->group_id = $group_id;
        if (!$type_ressource->loadMatchingObject()) {
          if ($msg = $type_ressource->store()) {
            $results[$i]["errors"][] = $msg;
            continue;
          }
        }
        $besoin                    = new CBesoinRessource();
        $besoin->protocole_id      = $protocole->_id;
        $besoin->type_ressource_id = $type_ressource->type_ressource_id;
        if ($msg = $besoin->store()) {
          $results[$i]["errors"][] = $msg;
        }
      }
    }

    if (CModule::getActive("appFineClient") && $results[$i]["_ref_packs_appFine"]) {
      foreach (explode("|", $results[$i]["_ref_packs_appFine"]) as $_pack) {
        $pack_protocole               = new CAppFineClientOrderPackProtocole();
        $pack_protocole->pack_id      = $_pack;
        $pack_protocole->protocole_id = $protocole->_id;
        if (!$pack_protocole->loadMatchingObject()) {
          if ($msg = $pack_protocole->store()) {
            $results[$i]["errors"][] = $msg;
            continue;
          }
        }
      }
    }

    //identifiants externes : création de l'identifiant (si existant pour la ligne donnée), suivant la liste de tags trouvée dans l'en-tête du fichier
    foreach ($idex_names as $_idex_name) {

      if ($results[$i][$_idex_name] !== "" && $results[$i][$_idex_name] !== null) {
        $idex               = new CIdSante400();
        $idex->object_class = "CProtocole";
        $idex->object_id    = $protocole->_id;
        $idex->id400        = $results[$i][$_idex_name];
        $idex->tag          = $_idex_name;
        $idex->loadMatchingObject();
        //si l'identifiant externe existe déja, on évite de le recréer
        if ($idex->_id === null) {
          if ($msg = $idex->store()) {
            $results[$i]["errors"][] = $msg;
            continue;
          }
        }
      }
    }

    if (!$csv_result) {
      CAppUI::setMsg($existing ? "CProtocole-msg-modify" : "CProtocole-msg-create", UI_MSG_OK);
    }
  }

  fclose($fp);
}

if ($csv_result) {
  $csv = new CCSVFile();
  $head = array(
    "Etat",
    CAppUI::tr("CProtocole-function_id"),
    CAppUI::tr("CProtocole-chir_id") . " " . CAppUI::tr("CMediusers-_user_last_name"),
    CAppUI::tr("CProtocole-chir_id") . " " . CAppUI::tr("CMediusers-_user_first_name"),
    CAppUI::tr("CProtocole-libelle"),
    CAppUI::tr("CProtocole-libelle_sejour"),
    CAppUI::tr("CProtocole-temp_operation"),
    CAppUI::tr("CProtocole-codes_ccam"),
    CAppUI::tr("CProtocole-DP"),
    CAppUI::tr("CProtocole-type"),
    CAppUI::tr("CProtocole-duree_hospi"),
    CAppUI::tr("CProtocole-duree_uscpo"),
    CAppUI::tr("CProtocole-duree_preop"),
    CAppUI::tr("CProtocole-presence_preop"),
    CAppUI::tr("CProtocole-presence_postop"),
    CAppUI::tr("CProtocole-uf_hebergement_id"),
    CAppUI::tr("CProtocole-uf_medicale_id"),
    CAppUI::tr("CProtocole-uf_soins_id"),
    CAppUI::tr("CProtocole-facturable"),
    CAppUI::tr("CProtocole-RRAC"),
    CAppUI::tr("CProtocole-for_sejour"),
    CAppUI::tr("CProtocole-exam_extempo"),
    CAppUI::tr("CProtocole-cote"),
    CAppUI::tr("CProtocole-examen"),
    CAppUI::tr("CProtocole-materiel"),
    CAppUI::tr("CProtocole-exam_per_op"),
    CAppUI::tr("CProtocole-depassement"),
    CAppUI::tr("CProtocole-forfait"),
    CAppUI::tr("CProtocole-fournitures"),
    CAppUI::tr("CProtocole-rques_operation"),
    CAppUI::tr("CProtocole-convalescence"),
    CAppUI::tr("CProtocole-rques_sejour"),
    CAppUI::tr("CProtocole-septique"),
    CAppUI::tr("CProtocole-duree_heure_hospi"),
    CAppUI::tr("CProtocole-pathologie"),
    CAppUI::tr("CProtocole-type_pec"),
    CAppUI::tr("CProtocole-hospit_de_jour"),
    CAppUI::tr("CProtocole-service_id"),
    CAppUI::tr("CProtocole-time_entree_prevue"),
    CAppUI::tr("CProtocole-charge_id")
  );
  if (CAppUI::gconf("dPbloc CPlageOp systeme_materiel") === "expert") {
    $head[] = CAppUI::tr("CBesoinRessource");
  }
  if (CModule::getActive("appFineClient")) {
    $head[] = CAppUI::tr("CAppFineClientOrderPack-msg-Order|pl") . " (" . CAppUI::tr("AppFine") . ")";
  }
  $head[] = CAppUI::tr("CProtocole-circuit_ambu");
  foreach ($idex_names as $_idex_name) {
    $head[] = CAppUI::tr("CIdSant400") . " - " . $_idex_name;
  }
    $head[] = CAppUI::tr("CProtocole-actif");
  $csv->writeLine($head);

  foreach ($results as $_protocole) {
    $errors = "";
    if (is_countable($_protocole["errors"])) {
      foreach ($_protocole["errors"] as $_error) {
        $errors .= (($errors !== "") ? ", " : "") . $_error;
      }
    }
    else {
      $errors = "ok";
    }
    $body = array(
      $errors,
      $_protocole["function_name"],
      $_protocole["praticien_lastname"],
      $_protocole["praticien_firstname"],
      $_protocole["motif"],
      $_protocole["libelle_sejour"],
      $_protocole["temp_operation"],
      $_protocole["codes_ccam"],
      $_protocole["DP"],
      $_protocole["type_hospi"],
      $_protocole["duree_hospi"],
      $_protocole["duree_uscpo"],
      $_protocole["duree_preop"],
      $_protocole["presence_preop"],
      $_protocole["presence_postop"],
      $_protocole["uf_hebergement"],
      $_protocole["uf_medicale"],
      $_protocole["uf_soins"],
      $_protocole["facturable"],
      $_protocole["RRAC"],
      $_protocole["for_sejour"],
      $_protocole["Exam_extempo_prevu"],
      $_protocole["cote"],
      $_protocole["bilan_preop"],
      $_protocole["materiel_a_prevoir"],
      $_protocole["examens_perop"],
      $_protocole["depassement_honoraires"],
      $_protocole["forfait_clinique"],
      $_protocole["fournitures"],
      $_protocole["rques_interv"],
      $_protocole["convalesence"],
      $_protocole["rques_sejour"],
      $_protocole["septique"],
      $_protocole["duree_heure_hospi"],
      $_protocole["pathologie"],
      $_protocole["type_pec"],
      $_protocole["hospit_de_jour"],
      $_protocole["service"],
      $_protocole["time_entree_prevue"],
      $_protocole["charge_price_indicator"]
    );
    if (CAppUI::gconf("dPbloc CPlageOp systeme_materiel") === "expert") {
      $body[] = $_protocole["_ref_besoins"];
    }
    if (CModule::getActive("appFineClient")) {
      $body[] = $_protocole["_ref_packs_appFine"];
    }
    $body[] = $_protocole["circuit_ambu"];
    foreach ($idex_names as $_idex_name) {
      $body[]= $_protocole[$_idex_name];
    }
    $body[] = $_protocole["actif"];
    $csv->writeLine($body);
  }

  $csv->stream("results");
}
else {
  CAppUI::callbackAjax('$("systemMsg").insert', CAppUI::getMsg());

// Création du template
  $smarty = new CSmartyDP();
  $smarty->assign("counts", $counts);
  $smarty->assign("results", $results);
  $smarty->assign("idex_names",$idex_names);
  $smarty->display("protocole_dhe_import_csv");
}
