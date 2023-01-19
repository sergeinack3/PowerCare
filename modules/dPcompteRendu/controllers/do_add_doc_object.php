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
use Ox\Mediboard\CompteRendu\CModeleToPack;
use Ox\Mediboard\CompteRendu\CPack;

$compte_rendu_id = CView::post("compte_rendu_id", "ref class|CCompteRendu");
$pack_id         = CView::post("pack_id", "ref class|CPack");
$object_class    = CView::post("object_class", "str");
$object_id       = CView::post("object_id", "ref class|$object_class");

CView::checkin();

$compte_rendu = new CCompteRendu();

$header_id = $footer_id = null;

if ($pack_id) {
  $pack = new CPack();
  $pack->load($pack_id);

  $compte_rendu->loadContent();
  $pack->loadContent();

  $compte_rendu->nom = $pack->nom;
  $compte_rendu->_source = $pack->_source;

  $pack->loadHeaderFooter();

  $header_id = $pack->_header_found->_id;
  $footer_id = $pack->_footer_found->_id;

  // Marges et format
  /** @var $links CModeleToPack[] */
  $links = $pack->_back['modele_links'];
  $first_modele = reset($links);
  $first_modele = $first_modele->_ref_modele;

  foreach (array(
    "factory", "font", "size", "page_height", "page_width",
    "margin_top", "margin_left", "margin_right", "margin_bottom"
    ) as $_field) {
    $compte_rendu->{$_field} = $first_modele->{$_field};
  }
}
else {
  $compte_rendu->load($compte_rendu_id);
  $compte_rendu->loadContent();

  $compte_rendu->_id = "";
}

$compte_rendu->object_class = $object_class;
$compte_rendu->object_id = $object_id;
$compte_rendu->user_id = "";
$compte_rendu->function_id = "";
$compte_rendu->group_id = "";
$compte_rendu->content_id = "";
$compte_rendu->_ref_content->_id = "";

$compte_rendu->_source = $compte_rendu->generateDocFromModel(null, $header_id, $footer_id);

$msg = $compte_rendu->store();

if ($msg) {
  CAppUI::setMsg($msg, UI_MSG_ERROR);
}
else {
  CAppUI::setMsg(CAppUI::tr("CCompteRendu-msg-create"));
}

echo CAppUI::getMsg();