<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CUserMail;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$mail_id = CView::get('mail_id', 'ref class|CUserMail');
$pat_id  = CView::get('pat_id', 'ref class|CPatient');

CView::checkin();

//apicrypt & search
$patient = new CPatient();
$dossier = new CSejour();

//looking for apicrypt => patient
$mail= new CUserMail();
$mail->load($mail_id);
$mail->loadContentPlain();
$mail->checkHprim();
$mail->checkApicrypt();

//apicrypt case
if ($mail->is_apicrypt || $mail->_is_hprim) {
  $lines = explode("\n", $mail->_text_plain->content);
  if ($mail->_is_hprim) {
    $lines = $mail->_hprim_content;
  }
  $fl = ($lines[0] != "[apicrypt]") ? 0 : 1;  //first line


  //cleanup line 1 to 13
  for ($a = $fl; $a<$fl+12; $a++) {
    $lines[$a] = trim($lines[$a]);
  }

  //init
  $ipp        = $lines[$fl];
  $nom        = $lines[$fl+1];
  $prenom     = $lines[$fl+2];
  $addr       = $lines[$fl+3];
  $addr_2     = $lines[$fl+4];
  $cp_ville   = $lines[$fl+5];
  $naissance  = CMbDT::dateFromLocale($lines[$fl+6]);
  $codeSecu   = $lines[$fl+6];
  $nda        = $lines[$fl+8];
  $date       = CMbDT::dateTime(CMbDT::dateFromLocale($lines[$fl+9]));
  $codeCores  = $lines[$fl+10];
  $codePresc  = $lines[$fl+11];

  if ($codePresc == '') {
    $ipp = '';
    $nom        = $lines[$fl];
    $prenom     = $lines[$fl+1];
    $naissance  = CMbDT::dateFromLocale($lines[$fl+5]);
  }

  //IPP
  if ($lines[$fl] != '') {
    $patient->_IPP = $ipp;
    $patient->loadFromIPP();
  }

  //search
  if (!$patient->_id && $nom != '' && $prenom != "") {
    $where = array();
    $where[]            = "`nom` LIKE '$nom%' OR `nom_jeune_fille` LIKE '$nom%'";
    $where["prenom"]    = "LIKE '$prenom%' ";
    $where["naissance"] = "LIKE '$naissance' ";

    $curr_user = CMediusers::get();
    $curr_group = CGroups::loadCurrent();

    if (CAppUI::isCabinet()) {
        $where['function_id'] = "= '{$curr_user->function_id}'";
    } elseif (CAppUI::isGroup()) {
        $where['group_id'] = "= '{$curr_group->_id}'";
    }

    $patient->loadObject($where);
  }


  //NDA
  if ($patient->_id && $nda) {
    $dossier->loadFromNDA($nda);
  }

  // patient + date (et pas de nda)
  if ($patient->_id && !$dossier->_id && $date) {
    $where = array();
    $where[]             = " '$date' BETWEEN entree AND sortie ";
    $where["patient_id"] = " = '$patient->_id'";

    $dossier->loadObject($where);
  }
}

$smarty = new CSmartyDP();

$smarty->assign('mail',       $mail);
$smarty->assign("mail_id",    $mail_id);
$smarty->assign("patient",    $patient);
$smarty->assign("dossier_id", $dossier->_id);

if ($mail->is_apicrypt || $mail->_is_hprim) {
  $smarty->assign('last_name', strtoupper($nom));
  $smarty->assign('first_name', ucwords($prenom));
  $smarty->assign('birth', CMbDT::dateToLocale($naissance));
}

$smarty->display("inc_vw_attach_piece.tpl");
