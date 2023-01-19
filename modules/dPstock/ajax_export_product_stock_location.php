<?php

/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CAppUI;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Stock\CProductStockLocation;

CCanDo::checkRead();

$group = CGroups::loadCurrent();

CView::checkin();

$types = [
  'CGroups'         => 'Pharmacie',
  'CBlocOperatoire' => 'Bloc',
  'CService'        => 'Service'
];
$psl   = new CProductStockLocation();
$ds    = $psl->getDS();
$where = [
  'group_id' => $ds->prepare('= ?', $group->_id)
];

$filename = "export-emplacements-" . str_replace(' ', '_', $group->_view);

$csv = new CCSVFile(null, CCSVFile::PROFILE_EXCEL);

$line = [
  CAppUI::tr("CProductStockLocation-name"),
  CAppUI::tr("CProductStockLocation-object_class-court"),
  CAppUI::tr("CProductStockLocation-position-court"),
  CAppUI::tr("CProductStockLocation-desc-court"),
  CAppUI::tr("CProductStockLocation-actif-court"),
];
$csv->writeLine($line);
$csv->setColumnNames($line);

$pslocation = $psl->loadList($where);
foreach ($pslocation as $_psl) {
    $line = [
        $_psl->name,
        $types[$_psl->object_class],
        $_psl->position,
        $_psl->desc,
        $_psl->actif,
    ];
    $csv->writeLine($line);
}
$csv->stream($filename, true);
CApp::rip();
