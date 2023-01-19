<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement\Controllers\Legacy;

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CClassMap;
use Ox\Core\CLegacyController;
use Ox\Core\CMbString;
use Ox\Core\Composer\CComposer;
use Ox\Core\CView;

class ClassesController extends CLegacyController
{

    public function vw_class_map():void
    {
        CCanDo::checkAdmin();
        $filter = CView::get('filter', 'str');
        $filter = $filter ? str_replace('\\\\', '\\', $filter) : null;
        CView::checkin();

        $class_map = CClassMap::getInstance();
        $maps      = $class_map->getClassMap();
        ksort($maps);

        $root_dir = CAppUI::conf("root_dir") . '/';

        foreach ($maps as $_class => &$_map) {
            if (!is_null($filter) && strpos($_class, $filter) === false) {
                unset($maps[$_class]);
                continue;
            }

            $_map['file_relative'] = str_replace($root_dir, '', $_map['file']);
        }

        $this->renderSmarty('vw_class_map', [
            'maps' => $maps,
            'filter' => $filter
        ]);
    }

    public function vw_class()
    {
        CCanDo::checkAdmin();
        CView::checkin();

        // stat
        $class_map = CClassMap::getInstance();
        $maps      = $class_map->getClassMap();
        ksort($maps);

        $classmap_file = $class_map->getClassMapFile();
        $classmap_date = null;
        $classmap_size = null;
        $classmap_line = 0;
        $classmap_dirs = $class_map->getDirs();


        if (file_exists($classmap_file)) {
            $classmap_date = filectime($classmap_file);
            $classmap_size = CMbString::toDecaBinary(filesize($classmap_file));
            $handle        = fopen($classmap_file, "r");
            while (!feof($handle)) {
                $line = fgets($handle);
                $classmap_line++;
            }
            fclose($handle);
        }

        $refs = $class_map->getClassRef();
        ksort($refs);

        $classref_file = $class_map->getClassRefFile();
        $classref_date = null;
        $classref_size = null;
        $classref_line = 0;
        $classref_keys = count($refs);

        if (file_exists($classref_file)) {
            $classref_date = filectime($classmap_file);
            $classref_size = CMbString::toDecaBinary(filesize($classref_file));
            $handle        = fopen($classref_file, "r");
            while (!feof($handle)) {
                $line = fgets($handle);
                $classref_line++;
            }
            fclose($handle);
        }

        $count_trait     = 0;
        $count_interface = 0;
        $count_parent    = 0;
        $count_children  = 0;
        $namespaces      = [];
        $modules         = [];

        foreach ($maps as $_class => $_map) {
            $names = explode("\\", $_class);
            $_key  = $names[1];
            if (!isset($namespaces[$_key])) {
                $namespaces[$_key] = 1;
            } else {
                $namespaces[$_key]++;
            }

            $_key = $_map['module'];
            if (!isset($modules[$_key])) {
                $modules[$_key] = 1;
            } else {
                $modules[$_key]++;
            }


            if ($_map['isTrait']) {
                $count_trait++;
            }

            if ($_map['isInterface']) {
                $count_interface++;
            }

            if (!empty($_map['children'])) {
                $count_parent++;
            }

            if ($_map['parent']) {
                $count_children++;
            }
        }

        ksort($modules);

        // Prefix
        $root_dir = CAppUI::conf('root_dir');
        $composer = new CComposer($root_dir);
        $prefix   = $composer->getPrefixPsr4();
        foreach ($prefix as $class => &$data) {
            $data = end($data);
            $data = str_replace($root_dir . '/', '', $data);
            if (strpos($data, 'vendor') === 0) {
                unset($prefix[$class]);
            }
        }
        ksort($prefix);

        $count_classe = count($maps);

        $tpl_vars = [
            "count_classe"    => $count_classe,
            "namespaces"      => $namespaces,
            "modules"         => $modules,
            "count_trait"     => $count_trait,
            "count_interface" => $count_interface,
            "count_parent"    => $count_parent,
            "count_children"  => $count_children,
            "prefix"          => $prefix,
            "classmap_file"   => $classmap_file,
            "classmap_date"   => $classmap_date,
            "classmap_size"   => $classmap_size,
            "classmap_line"   => $classmap_line,
            "classmap_dirs"   => $classmap_dirs,
            "classref_file"   => $classref_file,
            "classref_date"   => $classref_date,
            "classref_size"   => $classref_size,
            "classref_line"   => $classref_line,
            "classref_keys"   => $classref_keys,
        ];

        $this->renderSmarty('vw_class', $tpl_vars);
    }
}
