<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFilesCategory;

CCanDo::checkEdit();
$category_id = CView::get("file_category_id", "ref class|CFilesCategory");
CView::checkin();

$category = new CFilesCategory();
$category->load($category_id);

$type_docs = array(
  "DMP"   => $category->type_doc_dmp ?: 0,
  "SISRA" => $category->type_doc_sisra ?: 0
);

CApp::json($type_docs);
