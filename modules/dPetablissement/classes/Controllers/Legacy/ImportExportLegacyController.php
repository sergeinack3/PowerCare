<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Etablissement\Controllers\Legacy;

use DOMDocument;
use DOMXPath;
use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbArray;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CMbPath;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Etablissement\CGroupsImport;
use Ox\Mediboard\Etablissement\Import\GroupsXMLExport;
use Ox\Mediboard\Mediusers\CFunctions;

class ImportExportLegacyController extends CLegacyController
{
    /**
     * @throws Exception
     */
    public function uploadImportGroup(): void
    {
        $this->checkPermAdmin();

        $tmp_filename = $_FILES["import"]["tmp_name"];

        $dom = new DOMDocument();
        $dom->load($tmp_filename);

        $xpath = new DOMXPath($dom);
        if ($xpath->query("/mediboard-export")->length == 0) {
            CAppUI::js("window.parent.Group.uploadError()");
            CApp::rip();
        }

        $temp     = CAppUI::getTmpPath("group_import");
        $uid      = preg_replace('/[^\d]/', '', uniqid("", true));
        $filename = "$temp/$uid";
        CMbPath::forceDir($temp);

        move_uploaded_file($tmp_filename, $filename);

        // Cleanup old files (more than 4 hours old)
        $other_files = glob("$temp/*");
        $now         = time();
        foreach ($other_files as $_other_file) {
            if (filemtime($_other_file) < $now - (3600 * 4)) {
                unlink($_other_file);
            }
        }

        CAppUI::js("window.parent.Group.uploadSaveUID('$uid')");
        CApp::rip();
    }

    /**
     * @throws Exception
     */
    public function importGroup(): void
    {
        $this->checkPermAdmin();
        $uid     = CView::post("file_uid", "str notNull");
        $from_db = CView::post("fromdb", "str");
        $options = CView::post("options", "str");
        CView::checkin();

        $options = CMbArray::mapRecursive("stripslashes", $options);

        $uid  = preg_replace('/[^\d]/', '', $uid);
        $temp = CAppUI::getTmpPath("group_import");
        $file = "$temp/$uid";

        try {
            $import = new CGroupsImport($file);
            $import->import($from_db, $options);
        } catch (Exception $e) {
            CAppUI::stepAjax($e->getMessage(), UI_MSG_WARNING);
        }
    }

    /**
     * @throws CMbModelNotFoundException
     * @throws Exception
     */
    public function exportObject(): void
    {
        $this->checkPermAdmin();

        $group_id        = CView::get("group_id", "ref class|CGroups");
        $function_select = CView::get("function_select", "str");

        CView::checkin();

        CStoredObject::$useObjectCache = false;

        $group  = CGroups::find($group_id);
        $object = $group;

        if (!$group || !$group->_id) {
            throw new Exception('A group is mandatory for export');
        }

        if (($function_select !== null) && ($function_select !== 'all')) {
            $function = CFunctions::findOrFail($function_select);

            // If group_id is passed, check if function is in this group
            $ids = [];
            if ($group && $group->_id) {
                foreach ($group->loadFunctions() as $_group_function) {
                    $ids[] = $_group_function->_id;
                }

                if (!in_array($function->_id, $ids)) {
                    throw new Exception('This function is not in this group');
                }
            }

            $object = $function;
        }

        try {
            $export = new GroupsXMLExport($object);
            $export->streamXML();
        } catch (Exception $e) {
            CAppUI::stepAjax($e->getMessage(), UI_MSG_ERROR);
        }
    }
}
