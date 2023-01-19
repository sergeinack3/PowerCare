<?php

/**
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\FacturePrintService;

CCanDo::checkEdit();
$facture_class = CView::get('facture_class', 'str notNull', true);
$facture_id    = CView::get('facture_id', 'ref meta|facture_class notNull', true);

CView::checkin();

/* @var CFactureCabinet $facture */
$facture = new $facture_class();
$facture->load($facture_id);

$service = new FacturePrintService($facture);
$file    = $service->generatePdfFile(false, true);
$file->store();
$file->streamFile();
