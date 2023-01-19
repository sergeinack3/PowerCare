<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CView;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CThumbnail;

/**
 * Affichage en base64 d'une vignette d'un PDF
 */

$file_id = CView::get("file_id", 'num');
$index   = CView::get("index", 'str');

CView::checkin();

$file = new CFile();
$file->load($file_id);

// Si le pdf a été supprimé ou vidé car on ferme la popup sans enregistrer
// le document, alors on ne génère pas la vignette
if (!$file->_id || !file_exists($file->_file_path) || file_get_contents($file->_file_path) == "") {
  return;
}

$vignette = CThumbnail::displayThumb($file, $index, 400);

$vignette = base64_encode($vignette);

echo json_encode($vignette);
