<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Controllers\Legacy;

use Exception;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Core\Index\ClassIndexer;
use Ox\Core\Locales\Translator;
use Ox\Core\Module\CModule;
use Ox\Mediboard\System\CMergeLog;
use Ox\Mediboard\System\Keys\CKeyMetadata;
use Ox\Mediboard\System\Keys\Exceptions\CouldNotPersistKey;
use Ox\Mediboard\System\Keys\KeyBuilder;
use Throwable;

/**
 * Description
 */
class SystemLegacyController extends CLegacyController
{
    public function ajax_search_merge_logs(): void
    {
        $this->checkPermAdmin();

        $order_col = CView::get(
            'order_col',
            'enum list|date_start_merge|date_before_merge|date_after_merge|date_end_merge|duration'
        );
        $order_way = CView::get('order_way', 'enum list|ASC|DESC');

        $start = CView::get('start', 'num default|0');
        $step  = CView::get('step', 'num default|50');

        $min_date_start_merge = CView::get('_min_date_start_merge', 'dateTime');
        $max_date_start_merge = CView::get('_max_date_start_merge', 'dateTime');
        $min_date_end_merge   = CView::get('_min_date_end_merge', 'dateTime');
        $max_date_end_merge   = CView::get('_max_date_end_merge', 'dateTime');

        $object_class   = CView::get('object_class', 'str');
        $base_object_id = CView::getRefCheckRead('base_object_id', 'ref class|CStoredObject meta|object_class');
        $user_id        = CView::getRefCheckRead('user_id', 'ref class|CUser');
        $status         = CView::get('status', 'enum list|all|ok|ko');

        CView::checkin();

        $ds = CSQLDataSource::get('std');

        $order_col = ($order_col) ?: 'date_start_merge';
        $order_by  = $order_col;

        switch ($order_way) {
            case 'ASC':
                $order_by .= ' ASC';
                break;

            case 'DESC':
            default:
                $order_by .= ' DESC';
        }

        $start = ($start >= 0) ? $start : 0;
        $step  = ($step > 0) ? $step : 50;

        $limit = "{$start}, {$step}";

        $merge_log = new CMergeLog();

        $where = [];

        if ($min_date_start_merge) {
            $where[] = $ds->prepare('date_start_merge >= ?', $min_date_start_merge);
        }

        if ($max_date_start_merge) {
            $where[] = $ds->prepare('date_start_merge <= ?', $max_date_start_merge);
        }

        if ($min_date_end_merge) {
            $where[] = $ds->prepare('date_end_merge >= ?', $min_date_end_merge);
        }

        if ($max_date_end_merge) {
            $where[] = $ds->prepare('date_end_merge <= ?', $max_date_end_merge);
        }

        if ($object_class) {
            $where['object_class'] = $ds->prepare('= ?', $object_class);
        }

        if ($base_object_id) {
            $where['base_object_id'] = $ds->prepare('= ?', $base_object_id);
        }

        if ($user_id) {
            $where['user_id'] = $ds->prepare('= ?', $user_id);
        }

        $status = ($status) ?: 'all';

        if ($status === 'ko') {
            $where[] = 'date_end_merge IS NULL';
        } elseif ($status === 'ok') {
            $where[] = 'date_end_merge IS NOT NULL';
        }

        $total      = $merge_log->countList($where);
        $merge_logs = $merge_log->loadList($where, $order_by, $limit);

        $this->renderSmarty(
            'inc_vw_merge_logs',
            [
                'merge_logs' => $merge_logs,
                'order_col'  => $order_col,
                'order_way'  => $order_way,
                'total'      => $total,
                'start'      => $start,
                'step'       => $step,
            ]
        );
    }

    public function ajax_show_merge_log(): void
    {
        $this->checkPermAdmin();

        $merge_log_id = CView::getRefCheckRead('merge_log_id', 'ref class|CMergeLog notNull');

        CView::checkin();

        $merge_log = CMergeLog::findOrFail($merge_log_id);

        $merge_log->loadRefUser();
        $merge_log->loadBaseObject();
        $merge_log->loadObjects();

        $this->renderSmarty(
            'show_merge_log',
            [
                'merge_log' => $merge_log,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function viewKeysMetadata(): void
    {
        $this->checkPermAdmin();

        CView::checkin();

        $keys_metadata = (new CKeyMetadata())->loadList();

        $this->renderSmarty(
            'vw_keys_metadata',
            [
                'keys_metadata' => $keys_metadata,
            ]
        );
    }

    public function generateKey(): void
    {
        try {
            $this->checkPermAdmin();

            $metadata_id = CView::post('metadata_id', 'ref class|CKeyMetadata notNull');

            CView::checkin();

            $key_metadata = CKeyMetadata::findOrFail($metadata_id);

            if ($key_metadata->hasBeenPersisted()) {
                throw CouldNotPersistKey::alreadyExists($key_metadata->name);
            }

            $builder = new KeyBuilder();
            $builder->generateKey($key_metadata);

            CAppUI::setMsg('KeyBuilder-msg-Key have been successfully generated', UI_MSG_OK);
        } catch (Throwable $t) {
            CAppUI::setMsg($t->getMessage(), UI_MSG_ERROR);
        } finally {
            echo CAppUI::getMsg();
        }
    }

    /**
     * @throws CMbModelNotFoundException
     * @throws Exception
     */
    public function refreshMetadata(): void
    {
        $this->checkPermAdmin();

        $metadata_id = CView::get('metadata_id', 'ref class|CKeyMetadata notNull');

        CView::checkin();

        $key_metadata = CKeyMetadata::findOrFail($metadata_id);

        $this->renderSmarty(
            'inc_vw_key_metadata',
            [
                'metadata' => $key_metadata,
            ]
        );
    }

    public function showCGU(): void
    {
        $this->renderSmarty("cgu");
    }

    /**
     * @throws Exception
     */
    public function autocompleteClasses(): void
    {
        $this->checkPerm();

        $input_field = CView::get("input_field", "str");
        $keywords    = CView::get($input_field, 'str');
        $profile     = CView::get("profile", "str");
        $mod_name    = CView::get("mod_name", "str");

        CView::checkin();

        $indexer = new ClassIndexer();
        $classes = $indexer->search($keywords);

        $modules = [];
        foreach ($classes as $_class) {
            $module           = $_class->getModule();
            $modules[$module] = CModule::getActive($module);
        }

        // Only return list of unique modules
        if ($profile === 'moduleName') {
            $modules_occurs = array_fill_keys(array_keys($modules), false);

            foreach ($classes as $k => $_class) {
                $module = $_class->getModule();

                if ($modules_occurs[$module] === false) {
                    $modules_occurs[$module] = true;
                } else {
                    unset($classes[$k]);
                }
            }
        }

        // If mod_name is valued, only return classes of this specific module
        if ($mod_name !== null) {
            foreach ($classes as $k => $_class) {
                $module = $_class->getModule();

                if ($module !== $mod_name) {
                    unset($classes[$k]);
                }
            }
        }

        $this->renderSmarty(
            'autocomplete/inc_classes_autocomplete',
            [
                'keywords' => $keywords,
                'matches'  => $classes,
                'profile'  => $profile,
                'modules'  => $modules,
            ]
        );
    }
}
