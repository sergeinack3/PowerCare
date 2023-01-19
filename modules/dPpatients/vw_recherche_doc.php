<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

CCanDo::checkRead();

$date_min          = CView::get("_date_min", "date default|" . CMbDT::date("-1 month"), true);
$date_max          = CView::get("_date_max", "date default|" . CMbDT::date(), true);
$object_class      = CView::get("object_class", "str", true);
$nom_doc           = CView::get("nom_patient", "str", true);
$user_id           = CView::get("user_id", "ref class|CMediusers", true);
$patient_id_search = CView::get("patient_id_search", "ref class|CPatient", true);
$status_doc        = CView::get("_status", "enum list|signe|non_signe|sent", true);
$praticien_id      = CView::get("praticien_id", "ref class|CMediusers", true);
$service_id        = CView::get("service_id", "ref class|CService", true);
$type              = CView::get("type", "str", true);
$page              = CView::get("page", "num default|0", true);
CView::checkin();

$compte_rendu                                   = new CCompteRendu();
$compte_rendu->_specs['object_class']->_locales = CCompteRendu::$templated_classes;
$compte_rendu->_date_min                        = $date_min;
$compte_rendu->_date_max                        = $date_max;
$compte_rendu->_nom                             = $nom_doc;
$compte_rendu->_status                          = $status_doc;

$user = new CMediusers();
$user->load($user_id);

$praticien = new CMediusers();
$praticien->load($praticien_id);

$patient = new CPatient();
$patient->load($patient_id_search);

$service  = new CService();
$services = $service->loadListWithPerms();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("object_class", $object_class);
$smarty->assign("user", $user);
$smarty->assign("patient", $patient);
$smarty->assign("compte_rendu", $compte_rendu);
$smarty->assign("praticien", $praticien);
$smarty->assign("services", $services);
$smarty->assign("type", $type);
$smarty->assign("page", $page);

$smarty->display("vw_recherche_doc.tpl");