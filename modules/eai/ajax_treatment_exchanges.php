<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CValue;

/**
 * Send message
 */
CCanDo::checkRead();

$source_guid = CValue::get("source_guid");

// Chargement de l'objet
$source = CMbObject::loadFromGuid($source_guid);

CApp::log('EAI source exchange', $source);
