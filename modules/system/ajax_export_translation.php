<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\System\CTranslationOverwrite;

CCanDo::checkRead();

$csv = new CCSVFile();

$fields = array(
  'Chaine de traduction' => null,
  'Ancienne traduction' => null,
  'Tranduction de remplacement' => null,
  'Langue' => null,
);

$csv->writeLine(array_keys($fields));

$translation = new CTranslationOverwrite();
$translations = $translation->loadList();

foreach ($translations as $_translation) {
  $_translation->loadOldTranslation();

  $_fields = array(
    'Chaine de traduction' => $_translation->source,
    'Ancienne traduction' => $_translation->_old_translation,
    'Tranduction de remplacement' => $_translation->translation,
    'Langue' => $_translation->language,
  );

  $csv->writeLine($_fields);
}

$csv->stream("Traductions");
CApp::rip();