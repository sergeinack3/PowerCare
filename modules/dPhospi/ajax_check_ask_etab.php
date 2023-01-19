<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectation;

CCanDo::checkEdit();

$affectation_id = CView::get("affectation_id", "ref class|CAffectation");

CView::checkin();

$affectation = new CAffectation();
$affectation->load($affectation_id);

$ask_etab_externe = CAppUI::gconf("dPhospi placement ask_etab_externe");

echo $ask_etab_externe && $affectation->loadRefService()->externe ? 1 : 0;