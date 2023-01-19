<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Controllers\Legacy;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CLegacyController;
use Ox\Core\CMbException;
use Ox\Core\CView;
use Ox\Core\EntryPoint;
use Ox\Core\Kernel\Routing\RouterBridge;
use Ox\Mediboard\Sample\Import\MovieDb\SampleMovieImport;
use Ox\Mediboard\Sample\Import\SampleCategoryImport;
use Ox\Mediboard\Sample\Import\SampleNationalityImport;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceHTTP;

/**
 * Acces to the configuration of the module
 */
class SampleConfigureController extends CLegacyController
{
    public function configure(): void
    {
        $this->checkPermAdmin();

        $this->renderSmarty(
            'configure',
            [
                'source_available' => SampleMovieImport::isSourceAvailable(),
                'source'           => SampleMovieImport::getSource(),
                'base_url'         => rtrim(CApp::getBaseUrl(), '/'),
            ]
        );
    }

    /**
     * Display a list of persons as json api.
     *
     * @throws ApiException|CMbException|Exception
     */
    public function displayPersons(): void
    {
        $this->checkPermAdmin();

        CView::checkin();

        $entry = new EntryPoint('SampleMovieSettings', RouterBridge::getInstance());
        $entry->setScriptName('sampleMovieSettings')
            ->addLink('nationalities', 'sample_nationalities_list')
            ->addLink('personsList', 'sample_persons_list');

        if (CCanDo::edit()) {
            $entry->addLink('personsCreate', 'sample_persons_create');
        }

        $this->renderEntryPoint($entry);
    }

    /**
     * Legacy route for the admin that allow the import of categories.
     *
     * @throws Exception
     */
    public function importCategories(): void
    {
        $this->checkPermAdmin();

        $import = new SampleCategoryImport();
        $count  = $import->import();

        CAppUI::setMSg('CSampleCategory-msg-Imported', UI_MSG_OK, $count);

        foreach ($import->getErrors() as $msg) {
            CAppUI::setMsg($msg, UI_MSG_WARNING);
        }

        echo CAppUI::getMsg();

        CApp::rip();
    }

    /**
     * Legacy route for the admin that allow the import of nationalities.
     *
     * @throws Exception
     */
    public function importNationalities(): void
    {
        $this->checkPermAdmin();

        $import = new SampleNationalityImport();
        $count  = $import->import();

        CAppUI::setMSg('CSampleNationality-msg-Imported', UI_MSG_OK, $count);

        foreach ($import->getErrors() as $msg) {
            CAppUI::setMsg($msg, UI_MSG_WARNING);
        }

        echo CAppUI::getMsg();

        CApp::rip();
    }
}
