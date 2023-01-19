<?php

/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Mediboard\Soins\Controllers\Legacy\DossierSoinsController;

$_GET["popup"]     = 1;

try {
    (new DossierSoinsController())->viewDossierSejour();
} catch (Exception $e) {
    CAppUI::setMsg("Error", UI_MSG_ERROR);
}
