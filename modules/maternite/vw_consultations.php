<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;

/**
 * Liste des consultations de sage-femme
 */
CCanDo::checkRead();

global $mode_maternite;
$mode_maternite = true;

CAppUI::requireModuleFile('dPcabinet', 'vw_journee');
