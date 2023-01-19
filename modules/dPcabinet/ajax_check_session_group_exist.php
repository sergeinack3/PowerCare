<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultationCategorie;

CCanDo::checkRead();
$nom_categorie = CView::get("nom_categorie", "str");
CView::checkin();

$where                  = array();
$where['nom_categorie'] = "LIKE '%$nom_categorie%'";
$where['seance']        = " = '1'";

$consultation_categorie  = new CConsultationCategorie();
$consultation_categories = $consultation_categorie->loadList($where);

CApp::json($consultation_categories);
