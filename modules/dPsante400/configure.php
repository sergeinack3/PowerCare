<?php

/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CSmartyDP;
use Ox\Mediboard\Sante400\CIdSante400;
use Ox\Mediboard\Sante400\CMouvFactory;

CCanDo::checkAdmin();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("types", CMouvFactory::getTypes());
$smarty->assign("modes", array_keys(CMouvFactory::$modes));
$smarty->display("configure.tpl");
