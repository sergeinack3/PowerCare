<?php
/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Facturation\CFactureAvoir;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();
$avoir_id       = CView::get("facture_avoir_id", "ref class|CFactureAvoir");
CView::checkin();

$avoir = new CFactureAvoir();
$avoir->load($avoir_id);

$model = CCompteRendu::getSpecialModel(CMediusers::get(), "CFactureAvoir", "[AVOIR]");

if (!$model || !$model->_id) {
  CAppUI::commonError("CFactureAvoir-no model");
  CApp::rip();
}

CCompteRendu::streamDocForObject($model, $avoir);
