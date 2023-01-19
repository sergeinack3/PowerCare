<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;

$compte_rendu_id = CView::post("compte_rendu_id", "ref class|CCompteRendu");

CView::checkin();

$compte_rendu = new CCompteRendu();
$compte_rendu->load($compte_rendu_id);

$compte_rendu->loadContent(false);

$content = $compte_rendu->_ref_content;


$content->content = preg_replace("/<table([ a-z0-9%=:;\"]+)align=\"(left|center|right)\"/", "<table$1", $content->content);

$msg = $content->store();

CAppUI::setMsg($msg ? : CAppUI::tr("CCompteRendu-msg-modify"), $msg ? UI_MSG_ERROR : UI_MSG_OK);

echo CAppUI::getMsg();