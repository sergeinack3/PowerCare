<?php 
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Developpement\CClassDefinitionChecker;

CCanDo::checkRead();
CView::checkin();

$definition_checker = new CClassDefinitionChecker();
$definition_checker->selected_classes = CApp::getInstalledClasses(); // TODO namespace : check if need shortnames or fullnames

// Only get index error type
foreach (CClassDefinitionChecker::$error_types as $type) {
  $definition_checker->types[$type] = ($type === 'index');
}

// Pour toutes les classes selectionnées
if ($definition_checker->selected_classes) {
  foreach ($definition_checker->selected_classes as $_class) {
    /** @var CStoredObject $object */
    $object = new $_class;

    if (!$object->_spec->table) {
      continue;
    }

    $definition_checker->getDetailsSelectedClasses($_class, $object);
  }
}
$list_errors = $definition_checker->checkErrors();

$index_error_count = 0;
foreach ($list_errors as $_list_error) {
  if (is_array($_list_error)) {
    $index_error_count += array_sum($_list_error);
  }
}

$csv = new CCSVFile(null, CCSVFile::PROFILE_OPENOFFICE);
$csv->writeLine(array('index_error_count'));
$csv->writeLine(array($index_error_count));
$csv->stream("index");

CApp::rip();