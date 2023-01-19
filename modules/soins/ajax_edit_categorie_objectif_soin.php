<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Soins\CObjectifSoinCategorie;

CCanDo::checkRead();

$user         = CMediusers::get();
$categorie_id = CView::get('categorie_id', 'ref class|CObjectifSoinCategorie');

CView::checkin();

// Chargement de la categorie
$categorie = new CObjectifSoinCategorie();

if ($categorie_id) {
  $categorie->load($categorie_id);
}

$groups = CMediusers::loadEtablissements(PERM_EDIT);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("categorie", $categorie);
$smarty->assign("groups", $groups);

$smarty->display("inc_edit_categorie_objectif_soin");