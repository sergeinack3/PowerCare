<?php
/**
 * @package Mediboard\dPcabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CTypeEvenementPatient;

CCanDo::checkRead();

$user_current = CMediusers::get();
$default_user = "";
if ($user_current->isProfessionnelDeSante()) {
  $default_user = " default|$user_current->_id";
}
$only_send         = CView::get("only_send", "bool default|0", true);
$praticien_id      = CView::getRefCheckRead("praticien_id", "ref class|CMediusers$default_user", true);
$filter            = new CConsultation();
$filter->_date_min = CView::get("_date_min", "date default|" . CMbDT::date("first day of this month"));

$pref_filter_date  = CAppUI::pref("event_remember_date_filter");
$date_max          = CMbDT::date("+ $pref_filter_date month");
$filter->_date_max = CView::get("_date_max", "date default|" . CMbDT::date('last day of this month', $date_max));

$event_type_id = CView::get("event_type", "ref class|CTypeEvenementPatient");
$page          = CView::get("page", "num default|0");
$refresh       = CView::get("refresh", "bool default|0");

CView::checkin();

$event_type = CTypeEvenementPatient::find($event_type_id);

// Load practitioners
$praticiens = $user_current->loadProfessionnelDeSanteByPref(PERM_READ);
/* The owners of a CEvenementPatient may not only be practitioners (for example a secretary can be the owner of an event) */
$owners = $user_current->loadUsers(PERM_READ);

// Load events
$ds    = CSQLDataSource::get("std");
$ljoin = [
    'evenement_alert_user' =>
        "evenement_alert_user.object_id = evenement_patient.evenement_patient_id
         AND evenement_alert_user.object_class = 'CEvenementPatient'"
];
$where = [
  "date"   => $ds->prepare("BETWEEN ?1 AND ?2", $filter->_date_min, $filter->_date_max),
];

$where[] = "rappel = '1' OR alerter = '1'";

if ($event_type) {
  $where["type_evenement_patient_id"] = $ds->prepare("= ?", $event_type->_id);
}

if ($praticien_id) {
  $where["praticien_id"] = $ds->prepare("= ?", $praticien_id) .
      ' OR evenement_alert_user.user_id' . $ds->prepare('= ?', $praticien_id);
}
else {
  $where[] = "praticien_id " . CSQLDataSource::prepareIn(array_keys($praticiens)) . " 
  OR owner_id " . CSQLDataSource::prepareIn(array_keys($owners)) .
  ' OR evenement_alert_user.user_id ' . CSQLDataSource::prepareIn(array_keys($praticiens));
}

if ($only_send) {
  $ljoin["notification_object"] = "notification_object.object_id = evenement_patient.evenement_patient_id
                                   AND notification_object.object_class = 'CEvenementPatient'";
  $where[]                      = "notification_object.status = 'sent' OR notification_object.status = 'delivered'";
}

$evenement  = new CEvenementPatient();
$evenements = $evenement->loadList($where, "date", "$page, 10", "evenement_patient_id", $ljoin);

$event_types = (new CTypeEvenementPatient())->loadListWithPerms(PERM_EDIT);

$cat_event_types = ["mailing" => [], "normal" => []];
foreach ($event_types as $_event_type) {
  if ($_event_type->mailing_model_id) {
    $cat_event_types["mailing"][] = $_event_type;
  }
  else {
    $cat_event_types["normal"][] = $_event_type;
  }
}

$nb_untreated_evts = 0;

$users = CStoredObject::massLoadFwdRef($evenements, 'praticien_id');
CStoredObject::massLoadFwdRef($users, 'function_id');
CStoredObject::massLoadFwdRef($evenements, 'type_evenement_patient_id');
$dossiers = CStoredObject::massLoadFwdRef($evenements, 'dossier_medical_id');
CStoredObject::massLoadFwdRef($dossiers, 'object_id');
$notifications = CStoredObject::massLoadBackRefs($evenements, 'context_notifications');
CStoredObject::massLoadFwdRef($notifications, 'message_id');
CStoredObject::massLoadBackRefs($evenements, 'event_sent_mail');

foreach ($evenements as $_evt) {
  /* @var CEvenementPatient $_evt */
  if ($_evt->praticien_id && !$_evt->loadRefPraticien()->getPerm(PERM_EDIT)) {
    unset($evenements[$_evt->_id]);
    continue;
  }
  $_evt->loadRefTypeEvenementPatient();
  $_evt->loadRefPraticien()->loadRefFunction();
  $_evt->countDocItems();
  $_evt->loadRefPatient();
  $_evt->loadRefNotification();
  $_evt->loadRefSentMail();

  if (!$_evt->traitement_user_id) {
    $nb_untreated_evts++;
  }
}

$smarty = new CSmartyDP("modules/dPpatients");

$smarty->assign("praticien_id", $praticien_id);
$smarty->assign("praticiens", $praticiens);

$smarty->assign("nb_untreated_evts", $nb_untreated_evts);
$smarty->assign("filter", $filter);
$smarty->assign("only_send", $only_send);
$smarty->assign("mailing", ($event_type && $event_type->mailing_model_id));
$smarty->assign("event_type_id", $event_type_id);

if (!$refresh) {
  $smarty->assign("evenements", $evenements);
  $smarty->assign("cat_evenements", $cat_event_types);
  $smarty->assign("event_types", $event_types);
  $smarty->assign("total", ($evenements) ? count($evenements) : 0);
  $smarty->assign("page", $page);
  $smarty->assign("step", 10);

  $smarty->display("vw_evenements_rappel");
}
else {
  $smarty->assign("dossier_medical", null);
  $smarty->assign("edit_mode", 0);
  $smarty->assign("use_table", 0);
  $smarty->assign("evenements_patient", $evenements);
  $smarty->display("inc_vw_evenements_patient");
}

