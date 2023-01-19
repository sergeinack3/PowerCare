<?php
/**
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Labo\CCatalogueLabo;

CCanDo::checkEdit();

$catalogue_labo_id = CView::get("catalogue_labo_id", "ref class|CCatalogueLabo", true);
$examen_labo_id    = CView::get("examen_labo_id", "ref class|CExamenLabo", true);

CView::checkin();

$catalogue = new CCatalogueLabo();
$catalogue->load($catalogue_labo_id);

$catalogue->loadExamens();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("examens"  , $catalogue->_ref_examens_labo);
$smarty->assign("examen_id", $examen_labo_id);

$smarty->display("list_examens");
