<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Mediboard\Atih\CUniteMedicaleInfos;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CSecteur;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CUniteMedicale;

CCanDo::checkAdmin();

$type_name = CValue::get("type_name");

$service_id = CValue::getOrSession("service_id");
$chambre_id = CValue::getOrSession("chambre_id");
$lit_id     = CValue::getOrSession("lit_id");
$uf_id      = CValue::getOrSession("uf_id");
$secteur_id = CValue::getOrSession("secteur_id");

$group = CGroups::loadCurrent();

// Liste des Etablissements
$etablissements = CMediusers::loadEtablissements(PERM_READ);

$praticiens = CAppUI::$user->loadPraticiens();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("praticiens", $praticiens);
$smarty->assign("etablissements", $etablissements);

// Récupération des chambres/services
$where             = array();
$where["group_id"] = "= '$group->_id'";

if ($type_name == "services") {
  // Chargement des services
  $service = new CService();
  /** @var CService[] $services */
  $services = $service->loadListWithPerms(PERM_READ, $where, "nom");

  $count_cancelled_services = 0;
  $count_cancelled_chambres = 0;
  $count_cancelled_lits = 0;
  foreach ($services as $_service) {
    // Chargement des chambres et lits
    if ($_service->cancelled) {
      $count_cancelled_services++;
    }
    foreach ($_service->loadRefsChambres() as $_chambre) {
      if ($_chambre->annule) {
        $count_cancelled_chambres++;
      }
      foreach ($_chambre->loadRefsLits(true) as $_lit) {
        if ($_lit->annule) {
          $count_cancelled_lits++;
        }
      }
    }
  }

  $smarty->assign("services", $services);
  $smarty->assign("count_cancelled_services", $count_cancelled_services);
  $smarty->assign("count_cancelled_chambres", $count_cancelled_chambres);
  $smarty->assign("count_cancelled_lits", $count_cancelled_lits);
  $smarty->assign("count_cancelled_elements", $count_cancelled_services + $count_cancelled_chambres + $count_cancelled_lits);
  $smarty->display("inc_vw_idx_services.tpl");
}

if ($type_name == "UF") {
  // Chargement de l'uf à ajouter/éditer
  $uf           = new CUniteFonctionnelle();
  $uf->group_id = $group->_id;
  $uf->load($uf_id);
  $uf->loadRefUm();
  $uf->loadRefsNotes();

  // Récupération des ufs
  $order = "group_id, code";
  $ufs   = array(
    "hebergement" => $uf->loadGroupList(array("type" => "= 'hebergement'"), $order),
    "medicale"    => $uf->loadGroupList(array("type" => "= 'medicale'"), $order),
    "soins"       => $uf->loadGroupList(array("type" => "= 'soins'"), $order),
  );

  // Récupération des Unités Médicales (pmsi)
  $ums       = array();
  $ums_infos = array();
  $um        = new CUniteMedicale();
  if (CSQLDataSource::get("sae") && CModule::getActive("atih")) {
    $um_infos            = new CUniteMedicaleInfos();
    $ums                 = $um->loadListUm();
    $group               = CGroups::loadCurrent();
    $where["group_id"]   = " = '$group->_id'";
    $where["mode_hospi"] = " IS NOT NULL";
    $where["nb_lits"]    = " IS NOT NULL";
    $ums_infos           = $um_infos->loadList($where);
  }

  $smarty->assign("ufs", $ufs);
  $smarty->assign("uf", $uf);
  $smarty->assign("ums", $ums);
  $smarty->assign("ums_infos", $ums_infos);
  $smarty->display("inc_vw_idx_ufs.tpl");
}

if ($type_name == "secteurs") {
  // Chargement du secteur à ajouter / éditer
  $secteur           = new CSecteur;
  $secteur->group_id = $group->_id;
  $secteur->load($secteur_id);
  $secteur->loadRefsNotes();
  $secteur->loadRefsServices();

  // Récupération des prestations
  $order = "group_id, nom";

  // Récupération des secteurs
  $secteurs = $secteur->loadListWithPerms(PERM_READ, $where, $order);

  foreach ($secteurs as $_secteur) {
    /** @var CSecteur $_secteur */
    $_secteur->loadRefsServices();
  }

  $smarty->assign("secteurs", $secteurs);
  $smarty->assign("secteur", $secteur);
  $smarty->display("inc_vw_idx_secteurs.tpl");
}
