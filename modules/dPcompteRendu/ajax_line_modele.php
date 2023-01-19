<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;

CCanDo::checkRead();

$modele_id = CView::get("compte_rendu_id", "ref class|CCompteRendu");

CView::checkin();

$_modele = new CCompteRendu();
$_modele->load($modele_id);

if (!$_modele->_id) {
  CAppUI::callbackAjax("Modele.delLine", $modele_id);
  CApp::rip();
}

$_modele->canDo();
$_modele->loadRefCategory();
$_modele->loadContent()->getImageStatus();

switch ($_modele->type) {
  case "body":
    $_modele->loadComponents();
    $_modele->countBackRefs("documents_generated");
    break;
  case "header":
    $_modele->countBackRefs("modeles_headed", array("object_id" => "IS NULL"));
    break;
  case "footer":
    $_modele->countBackRefs("modeles_footed", array("object_id" => "IS NULL"));
    break;
  case "preface":
    $_modele->countBackRefs("modeles_prefaced");
    break;
  case "ending":
    $_modele->countBackRefs("modeles_ended");
}

CCompteRendu::massGetDateLastUse([$_modele->_id => $_modele]);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("_modele", $_modele);
$smarty->assign("filtre", $_modele);
$smarty->assign("with_tr", false);

$smarty->display("inc_line_modele");