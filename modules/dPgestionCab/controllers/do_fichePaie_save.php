<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\GestionCab\CFichePaie;

$do           = new CDoObjectAddEdit("CFichePaie");
$do->redirect = null;
$do->doIt();

$fichePaie = new CFichePaie();
$fichePaie->load($do->_obj->_id);
$fichePaie->loadRefsFwd();
$fichePaie->_ref_params_paie->loadRefsFwd();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("fichePaie", $fichePaie);

$fichePaie->final_file = $smarty->fetch("print_fiche");
CApp::log($fichePaie->store());
CApp::rip();
