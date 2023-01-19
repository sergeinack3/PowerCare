<?php

/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Files\Controllers\Legacy;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CFilesExplorer;

/**
 * Description
 */
class CFilesExplorerLegacyController extends CLegacyController
{
    private const EXPORT_TIME_LIMIT   = 600;
    private const EXPORT_MEMORY_LIMIT = '4096M';

    private function getFilterArguments(?int $start = null, ?int $limit = null): array
    {
        $min_size = CView::get('min_size', 'str');
        $max_size = CView::get('max_size', 'str');

        if ($start === null) {
            $start = CView::get('start', 'num default|0');
        }

        if (!$limit) {
            $limit = CView::get('limit', 'num default|50');
        }

        $annule = CView::get('annule', 'str');

        $user_id     = CView::get('user_id', 'str');
        $category_id = CView::get('category_id', 'str');
        $function_id = CView::get('function_id', 'str');

        return [
            'from_date'    => CView::get('from_date', 'str'),
            'to_date'      => CView::get('to_date', 'str'),
            '_order'       => CView::get('_order', 'str default|file_date'),
            '_way'         => CView::get('_way', 'str default|DESC'),
            'min_size'     => ($min_size !== null) ? (int)$min_size : null,
            'max_size'     => ($max_size !== null) ? (int)$max_size : null,
            'mimetype'     => CView::get('mimetype', 'str'),
            'object_class' => CView::get('object_class', 'str'),
            'file_hash'    => CView::get('file_hash', 'str'),
            'file_name'    => CView::get('file_name', 'str'),
            'annule'       => ($annule !== '') ? (bool)$annule : null,
            'user_id'      => ($user_id !== null) ? (int)$user_id : null,
            'category_id'  => ($category_id !== null) ? (int)$category_id : null,
            'function_id'  => ($function_id !== null) ? (int)$function_id : null,
            'limit'        => "{$start}, {$limit}",
        ];
    }

    public function vw_files_explorer()
    {
        $this->checkPermEdit();

        $from_date = CMbDT::dateTime('-1 week', CMbDT::dateTime('now'));
        $to_date   = CMbDT::dateTime('now');
        $_order    = CView::get('_order', 'str default|file_date');
        $_way      = CView::get('_way', 'str default|DESC');
        $min_size  = CView::get('min_size', 'str');
        $max_size  = CView::get('max_size', 'str');
        $mimetype  = CView::get('mimetype', 'str');
        $file_hash = CView::get('file_hash', 'str');
        $file_name = CView::get('file_name', 'str');
        $annule    = CView::get('annule', 'ref class|CFile');

        CView::checkin();

        $file = new CFile();

        $classes = CApp::getChildClasses(CMbObject::class, false, true);

        $this->renderSmarty(
            'vw_files_explorer',
            [
                'limit'     => 100,
                'file'      => $file,
                'from_date' => $from_date,
                'to_date'   => $to_date,
                '_order'    => $_order,
                '_way'      => $_way,
                'min_size'  => $min_size,
                'max_size'  => $max_size,
                'mimetype'  => $mimetype,
                'file_hash' => $file_hash,
                'file_name' => $file_name,
                'annule'    => $annule,
                'classes'   => $classes,
            ]
        );
    }

    public function ajax_search_files()
    {
        $this->checkPermEdit();

        $start = CView::get('start', 'num default|0');
        $limit = CView::get('limit', 'num default|100');

        $filter = $this->getFilterArguments($start, $limit);
        unset($filter['_mode']);

        CView::checkin();
        CView::enforceSlave();

        $file = new CFile();
        $file->needsRead();

        $export = new CFilesExplorer();
        [$files, $total] = $export->getFileList(...array_values($filter));
        $file_statuses = $export->buildFileInfos($files);

        $stats = $export->getStats();
        CAppUI::js(
            "FilesExplorer.updateStats('{$stats['file_count']}', '{$stats['min_access_time']}', '{$stats['max_access_time']}', '{$stats['mean_access_time']}', '{$stats['std_deviation_access_time']}');"
        );

        $this->renderSmarty(
            'inc_vw_files_search_results',
            [
                'files'         => $files,
                'total'         => $total,
                'start'         => $start,
                'file_statuses' => $file_statuses,
                'limit'         => $limit,
                '_order'        => $filter['_order'],
                '_way'          => $filter['_way'],
            ]
        );
    }

    public function ajax_export_files()
    {
        $this->checkPermEdit();

        $filter = $this->getFilterArguments();

        $mode         = CView::get('_mode', 'enum list|default|fast|min default|default');
        $only_missing = CView::get('only_missing', 'bool default|0');

        CView::checkin();
        CView::enforceSlave();

        CApp::setTimeLimit(self::EXPORT_TIME_LIMIT);
        CApp::setMemoryLimit(self::EXPORT_MEMORY_LIMIT);

        $file = new CFile();
        $file->needsRead();

        $filter['start']        = null;
        $filter['limit']        = null;
        $filter['mode']         = $mode;
        $filter['only_missing'] = $only_missing;

        $explorer = new CFilesExplorer();
        $explorer->exportCsvWithTimer($filter);

        CApp::rip();
    }
}
