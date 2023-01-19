<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement\Controllers\Legacy;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Developpement\CClassDefinitionChecker;
use Ox\Mediboard\Developpement\CIndexChecker;
use Ox\Mediboard\Developpement\CTablesIntegrityChecker;

/**
 * Description
 */
class DbIntegrityController extends CLegacyController
{
    public function vw_indexes()
    {
        $this->checkPermRead();

        $module = CView::get('index_module', 'str', true);

        CView::checkin();

        $module_list = CModule::getInstalled();
        $modules_trads = [];
        foreach ($module_list as $_mod) {
            $modules_trads[CAppUI::tr("module-{$_mod->mod_name}-court")] = $_mod;
        }

        CMbArray::naturalKeySort($modules_trads);

        $this->renderSmarty(
            'vw_indexes',
            [
                'error_types' => CIndexChecker::ERROR_TYPES,
                'key_types'   => CIndexChecker::KEY_TYPES,
                'module_list' => $modules_trads,
                'module'      => $module,
            ]
        );
    }

    public function ajax_vw_indexes()
    {
        $this->checkPermRead();

        $error_type      = CView::get(
            'error_type',
            'enum list|' . implode('|', CIndexChecker::ERROR_TYPES) . ' default|all'
        );
        $key_type        = CView::get(
            'key_type',
            'enum list|' . implode('|', CIndexChecker::KEY_TYPES) . ' default|index'
        );
        $module          = CView::get('index_module', 'str', true);
        $show_all_fields = CView::get('show_all_fields', 'bool default|0');

        CView::checkin();

        CView::enforceSlave();

        $index_checker = new CIndexChecker($key_type, $error_type, $module, (bool)$show_all_fields);
        $errors        = $index_checker->check();

        $this->renderSmarty(
            'inc_vw_indexes',
            [
                'errors'                => $errors,
                'count_missing_db'      => $index_checker->getCountMissingDb(),
                'count_not_expected_db' => $index_checker->getCountNotExpectedDb(),
            ]
        );
    }

    public function vw_db_checks()
    {
        $this->checkPermRead();

        CView::checkin();

        $this->renderSmarty('vw_db_checks');
    }

    public function mnt_table_classes()
    {
        $this->checkPermRead();

        $module      = CView::get("module", "str", true);
        $class       = CView::get("class", "str", true);
        $error_types = CView::get("types", "str", true);

        CView::setSession('module', $module);
        CView::checkin();
        CView::enforceSlave();

        $definition_checker = new CClassDefinitionChecker((empty($module)) ? $class : $module);

        $error_types = ($error_types) ? explode('|', $error_types) : null;

        foreach (CClassDefinitionChecker::$error_types as $type) {
            $definition_checker->types[$type] = !isset($error_types) || in_array($type, $error_types);
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

        // Enregistre les suggestions pour chaque classe
        foreach ($definition_checker->list_classes as $_class => &$class_details) {
            $class_details['suggestion'] = $definition_checker->getQueryForClass($class_details);
        }

        $checked_types = array_filter(
            $definition_checker->types,
            function ($type) {
                return $type;
            }
        );

        $this->renderSmarty(
            'mnt_table_classes',
            [
                'list_classes'      => $definition_checker->list_classes,
                'module'            => $definition_checker->module,
                'installed_modules' => $definition_checker->installed_modules,
                'list_errors'       => $list_errors,
                'types'             => $definition_checker->types,
                'checked_types'     => implode('|', $checked_types),
                'class'             => $class,
                'installed_classes' => CApp::getInstalledClasses([], true),
            ]
        );
    }

    public function csv_class_tables()
    {
        $this->checkPermRead();

        $csv = new CCSVFile();

        $columns = array(
            "class",
            "class-title",
            "table",
            "key",
            "module",
            "module-title",
        );

        $csv->writeLine($columns);

        CApp::getMbClasses($instances, true);

        foreach ($instances as $_class => $_instance) {
            if (!$_instance->_spec->table || !$_instance->_ref_module) {
                continue;
            }

            $_module = $_instance->_ref_module;

            $line = array(
                $_class,
                CAppUI::tr($_class),
                $_instance->_spec->table,
                $_instance->_spec->key,
                $_module->mod_name,
                CAppUI::tr("module-{$_module->mod_name}-court"),
            );
            $csv->writeLine($line);
        }

        $csv->stream("Class to table");
    }

    public function vw_tables_integrity()
    {
        $this->checkPermRead();

        CView::checkin();

        $dsn = array_keys(CAppUI::conf('db'));

        $modules = CModule::getInstalled();
        $mod_trads = [];
        foreach ($modules as $_mod_name => $_mod) {
            $mod_trads[$_mod_name] = CAppUI::tr("module-{$_mod_name}-court");
        }
        asort($mod_trads);

        $this->renderSmarty('vw_tables_integrity', ['dsn' => $dsn, 'modules' => $mod_trads]);
    }

    public function ajax_check_tables_integrity()
    {
        $this->checkPermRead();

        $dsn           = CView::get('dsn', 'str');
        $type          = CView::get('type', 'enum list|all|table_missing|class_missing default|all');
        $mod_name      = CView::get('mod_name', 'str');

        CView::checkin();

        $integirty = new CTablesIntegrityChecker($type);

        if ($dsn) {
            $integirty->setDsn($dsn);
        }

        if ($mod_name) {
            $integirty->setModule($mod_name);
        }

        $integrity_result = $integirty->checkTablesIntegrity();

        $this->renderSmarty('inc_check_tables_integrity', ['integrity_result' => $integrity_result]);
    }
}
