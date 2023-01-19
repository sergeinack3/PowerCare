<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbSecurity;
use Ox\Core\CView;

CCanDo::checkRead();

$spec         = CView::get('spec', 'str');
$object_class = CView::get('object_class', 'ref class|CModelObject');
$object_field = CView::get('object_field', 'str');

CView::checkin();

if ($object_class && $object_field) {
    $object = new $object_class();
    $spec   = $object->_specs[$object_field];

    $password = CMbSecurity::getRandomPassword($spec);
} else {
    $password = CMbSecurity::getRandomPassword($spec);
}

CApp::json($password);
