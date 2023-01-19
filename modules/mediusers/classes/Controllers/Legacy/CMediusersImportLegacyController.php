<?php

/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers\Controllers\Legacy;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CLegacyController;
use Ox\Core\CMbException;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CCSVImportMediusers;
use Ox\Mediboard\Mediusers\CMediusersXMLImport;
use Ox\Mediboard\Mediusers\CMediusersXmlImportManager;

/**
 * Description
 */
class CMediusersImportLegacyController extends CLegacyController
{
    public function vw_import_mediusers(): void
    {
        $this->checkPermAdmin();

        $this->renderSmarty('vw_import_mediusers');
    }

    public function do_import_mediusers(): void
    {
        $this->checkPermAdmin();

        $file = CValue::files('formfile');

        $options = $this->createOptions();

        CView::checkin();

        $manager = new CMediusersXmlImportManager();
        $manager->importMediusers($options, $file['tmp_name'][0], $file['name'][0]);

        $this->removeWaitingMessage();
        $this->displayMessages();
        $this->displayErrors($manager->getErrors());

        CApp::rip();
    }

    public function vw_import_profile(): void
    {
        $this->checkPermAdmin();

        $etabs = CGroups::loadGroups(PERM_READ);

        $this->renderSmarty('vw_import_profile', ['etabs' => $etabs]);
    }

    public function inc_check_import_profiles(): void
    {
        $this->checkPermAdmin();

        $file = CValue::files('formfile');

        CView::checkin();

        $manager = new CMediusersXmlImportManager();
        $import_users = $manager->getImportUsers($file['tmp_name'][0], $file['name'][0]);
        $errors = $manager->getErrors();

        if ($errors) {
            $this->displayErrors($errors);
        }

        $this->renderSmarty(
            'inc_check_import_profiles',
            [
                'import_users' => $import_users,
            ]
        );
    }

    public function ajax_show_profile_compare(): void
    {
        $this->checkPermAdmin();

        $user_guid = CView::get('user_guid', 'str notNull');

        CView::checkin();

        $manager = new CMediusersXmlImportManager();
        $compare = $manager->compare($user_guid);

        $this->renderSmarty(
            'inc_show_profile_compare',
            [
                'compare'    => $compare,
                'user_types' => CUser::$types,
                'file_name'  => $user_guid,
            ]
        );
    }

    public function do_import_new_profile(): void
    {
        $this->checkPermAdmin();

        $file_name = CView::post('file_name', 'str notNull');

        $options = $this->createOptions(
            [
                'update_perms'             => 1,
                'update_prefs'             => 1,
                'update_perms_functionnal' => 1,
                'default_prefs'            => 0,
                'profile'                  => 1,
                'new_name'                 => CView::post('new_name', 'str'),
                'ignore_find'              => 1,
            ]
        );

        CView::checkin();

        $manager = new CMediusersXmlImportManager();
        $manager->importNewProfile($file_name, $options);


        CApp::rip();
    }

    public function do_import_existing_profile(): void
    {
        $this->checkPermAdmin();

        $file_name = CView::post('file_name', 'str notNull');

        $permissions = [
            'perms_module'      => CView::post('perms_module', 'str') ?: [],
            'perms_module_view' => CView::post('perms_module_view', 'str') ?: [],
            'perms_object'      => CView::post('perms_object', 'str') ?: [],
            'preferences'       => CView::post('preferences', 'str') ?: [],
            'perms_functionnal' => CView::post('permissions_functionnal', 'str') ?: [],
        ];

        $options = $this->createOptions(
            [
                'update_perms'             => 1,
                'update_prefs'             => 1,
                'update_perms_functionnal' => 1,
                'default_prefs'            => 0,
                'profile'                  => 1,
            ]
        );

        CView::checkin();

        $manager = new CMediusersXmlImportManager();
        $manager->updateProfile($file_name, $permissions, $options);

        CApp::rip();
    }

    public function ajax_import_mediusers_csv()
    {
        $this->checkPermAdmin();

        $file   = CValue::files("formfile");
        $dryrun = CView::post("dryrun", "bool default|0");
        $update = CView::post("update", "bool default|0");

        CView::checkin();

        if (!$file || !$file['tmp_name']) {
            CAppUI::stepAjax("CFile-not-exists", UI_MSG_ERROR, $file);
        }

        $import = new CCSVImportMediusers($file['tmp_name'][0], $dryrun, $update);

        try {
            $import->import();
        } catch (CMbException $e) {
            CAppUI::stepAjax($e->getMessage(), UI_MSG_ERROR);
        }

        $results = $import->getResults();
        $unfound = $import->getUnfound();

        foreach ($import->getCreated() as $class_name => $count) {
            for ($i = 0; $i < $count; $i++) {
                CAppUI::setMsg($class_name . '-msg-create', UI_MSG_OK);
            }
        }

        foreach ($import->getFound() as $class_name => $count) {
            for ($i = 0; $i < $count; $i++) {
                CAppUI::setMsg($class_name . '-msg-create', UI_MSG_OK);
            }
        }

        foreach ($import->getErrors() as $error) {
            $parts = array_slice($error, 1);
            CAppUI::setMsg($error[0], UI_MSG_WARNING, ...$parts);
        }

        echo CAppUI::getMsg();

        $this->renderSmarty(
            'inc_import_mediusers_csv',
            [
                'results' => $results,
                'unfound' => $unfound,
                'dryrun'  => $dryrun,
            ]
        );
    }

    public function vw_import_mediusers_csv(): void
    {
        $this->checkPermAdmin();

        $this->renderSmarty('vw_import_mediusers_csv');
    }

    private function createOptions(array $options = []): array
    {
        return array_merge(
            [
                'profile'                  => CView::post('profile', 'bool default|0'),
                'planning'                 => CView::post('planning', 'bool default|0'),
                'perms'                    => CView::post('perms', 'bool default|0'),
                'update_perms'             => CView::post('update_perms', 'bool default|0'),
                'prefs'                    => CView::post('prefs', 'bool default|0'),
                'update_prefs'             => CView::post('update_prefs', 'bool default|0'),
                'perms_functionnal'        => CView::post('perms_functionnal', 'bool default|0'),
                'update_perms_functionnal' => CView::post('update_perms_functionnal', 'bool default|0'),
                'default_prefs'            => CView::post('default_prefs', 'bool default|0'),
                'update_default_prefs'     => CView::post('update_default_prefs', 'bool default|0'),
                'tarification'             => CView::post('tarification', 'bool default|0'),
                'update_tarification'      => CView::post('update_tarification', 'bool default|0'),
                'create_functions'         => CView::post('functions', 'bool default|0'),
                'create_ufs'               => CView::post('ufs', 'bool default|0'),
            ],
            $options
        );
    }

    private function displayErrors(array $errors): void
    {
        foreach ($errors as $_error) {
            CAppUI::stepAjax(...$_error);
        }
    }

    private function displayMessages(): void
    {
        // Display message
        foreach (CMediusersXMLImport::$already_imported as $_class => $_nb) {
            if ($_nb > 0) {
                CAppUI::stepAjax("common-import-object-found", UI_MSG_OK, $_nb, CAppUI::tr($_class));
            }
        }
    }

    private function removeWaitingMessage(): void
    {
        CAppUI::js("$('wait-import-mediusers').innerText = ''");
    }
}
