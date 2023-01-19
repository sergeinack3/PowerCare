<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectationUniteFonctionnelle;
use Ox\Mediboard\Hospi\CLitLiaisonItem;
use Ox\Mediboard\Hospi\CService;

CCanDo::checkAdmin();

CView::enforceSlave();
$only_actifs = CView::get("only_actifs", "bool");

CView::checkin();

$group = CGroups::loadCurrent();

if (!$group->getPerm(PERM_READ)) {
  CAppUI::stepAjax('access-forbidden', UI_MSG_ERROR);
}

$header = array(
  'service', 'chambre', 'lit', 'lit_complet', 'ufh_service', 'ufh_chambre', 'ufh_lit', 'ufs_service', 'ufs_chambre', 'ufs_lit',
  'prestas'
);

if (!$only_actifs) {
  $header = array_merge($header, array('service_actif', 'chambre_active', 'lit_actif'));
}

$date      = CMbDT::date();
$file_name = "export-infrastructure-{$group->text}-{$date}";

$filepath = rtrim(CAppUI::conf('root_dir'), '/\\') . "/tmp/$file_name";

$fp  = fopen($filepath, 'w+');
$csv = new CCSVFile($fp);
$csv->setColumnNames($header);
$csv->writeLine($header);

$service           = new CService();
$service->group_id = $group->_id;
$services          = $service->loadMatchingListEsc();

$chambres = array();
$lits     = array();

/** @var CService $_service */
foreach ($services as $_service) {
  if ($only_actifs && $_service->cancelled) {
    continue;
  }
  $serv_uf  = $_service->loadRefsUFs();
  $serv_ufh = array();
  $serv_ufs = array();
  foreach ($serv_uf as $_uf) {
    if ($_uf->type == 'soins') {
      $serv_ufs[] = $_uf->code;
    }
    elseif ($_uf->type == 'hebergement') {
      $serv_ufh[] = $_uf->code;
    }
  }
  $serv_ufh = implode('|', $serv_ufh);
  $serv_ufs = implode('|', $serv_ufs);

  $chambres = $_service->loadRefsChambres();
  foreach ($chambres as $_chambre) {
    if ($only_actifs && $_chambre->annule) {
      continue;
    }
    $affectation_uf = $_chambre->loadBackRefs('ufs');
    $chambre_ufs    = array();
    $chambre_ufh    = array();
    /** @var CAffectationUniteFonctionnelle $_affectation */
    foreach ($affectation_uf as $_affectation) {
      $uf = $_affectation->loadRefUniteFonctionnelle();
      if ($uf->type == 'soins') {
        $chambre_ufs[] = $uf->code;
      }
      elseif ($uf->type == 'hebergement') {
        $chambre_ufh[] = $uf->code;
      }
    }
    $chambre_ufs = implode('|', $chambre_ufs);
    $chambre_ufh = implode('|', $chambre_ufh);

    $lits = $_chambre->loadRefsLits(!$only_actifs);
    foreach ($lits as $_lit) {
      if ($only_actifs && $_lit->annule) {
        continue;
      }
      $lit_uf  = $_lit->loadBackRefs('ufs');
      $lit_ufs = array();
      $lit_ufh = array();

      foreach ($lit_uf as $_affectation) {
        $uf = $_affectation->loadRefUniteFonctionnelle();
        if ($uf->type == 'soins') {
          $lit_ufs[] = $uf->code;
        }
        elseif ($uf->type == 'hebergement') {
          $lit_ufh[] = $uf->code;
        }
      }

      $prestas = array();
      $links   = $_lit->loadBackRefs("liaisons_items");
      /** @var CLitLiaisonItem $_link */
      foreach ($links as $_link) {
        $presta_item = $_link->loadRefItemPrestation();
        $prestas[]   = $presta_item->nom;
      }


      $line = array(
        'service'     => $_service->nom,
        'chambre'     => $_chambre->nom,
        'lit'         => $_lit->nom,
        'lit_complet' => $_lit->nom_complet,
        'ufh_service' => $serv_ufh,
        'ufh_chambre' => $chambre_ufh,
        'ufh_lit'     => implode('|', $lit_ufh),
        'ufs_service' => $serv_ufs,
        'ufs_chambre' => $chambre_ufs,
        'ufs_lit'     => implode('|', $lit_ufs),
        'prestas'     => implode('|', $prestas),
      );
      if (!$only_actifs) {
        $line = array_merge($line, array(
          'service_actif' => CAppUI::tr($_service->cancelled ? "No" : "Yes"),
          'chambre_actif' => CAppUI::tr($_chambre->annule ? "No" : "Yes"),
          'lit_actif'     => CAppUI::tr($_lit->annule ? "No" : "Yes"),
        ));
      }

      $csv->writeLine($line);
    }
  }
}

$csv->stream($file_name, true);

$csv->close();
unlink($filepath);
