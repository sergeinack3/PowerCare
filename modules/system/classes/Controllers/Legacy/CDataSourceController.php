<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Controllers\Legacy;

use Exception;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbServer;
use Ox\Core\CSetup;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Core\Elastic\ElasticIndexManager;
use Ox\Core\Elastic\ElasticObject;
use Ox\Core\Elastic\ElasticObjectManager;
use Ox\Core\Module\CModule;

/**
 * Legacy Controller to manage DataSources
 */
class CDataSourceController extends CLegacyController
{
    /**
     * Gather datasource from setup dependencies
     * @throws Exception
     */
    public function viewDatasources(): void
    {
        $this->checkPermAdmin();
        CView::checkin();

        /*SQL Datasources*/
        $datasource_configs = CAppUI::conf("db");

        $setupClasses = CApp::getChildClasses(CSetup::class);

        $datasources     = [];
        $elastic_indices = [];

        foreach ($setupClasses as $setupClass) {
            if (!class_exists($setupClass)) {
                continue;
            }

            /** @var CSetup $setup */
            $setup    = new $setupClass();
            $mbmodule = new CModule();
            $mbmodule->compareToSetup($setup);
            $mbmodule->updateFormFields();

            if (count($setup->datasources)) {
                if (!isset($datasources[$setup->mod_name])) {
                    $datasources[$setup->mod_name] = [];
                }

                foreach ($setup->datasources as $_datasource => $_query) {
                    $datasources[$setup->mod_name][] = $_datasource;
                    unset($datasource_configs[$_datasource]);
                }
            }

            if (count($setup->getElasticDependencies())) {
                if (!isset($elastic_indices[$setup->mod_name])) {
                    $elastic_indices[$setup->mod_name] = [];
                }

                foreach ($setup->getElasticDependencies() as $_datasource) {
                    $elastic_indices[$setup->mod_name][] = $_datasource;
                }
            }
        }

        $datasources["_other_"] = array_keys($datasource_configs);

        $this->renderSmarty(
            'vw_datasources',
            [
                "datasources"         => $datasources,
                "elastic_datasources" => $elastic_indices,
            ]
        );
    }


    public function loadDsn(): void
    {
        $this->checkPermAdmin();

        $dsn     = CView::get("dsn", "str notNull");
        $dsn_uid = CView::get("dsn_uid", "str");

        CView::checkin();

        $this->renderSmarty(
            'inc_view_dsn',
            [
                "dsn"     => $dsn,
                "dsn_uid" => $dsn_uid,
            ]
        );
    }

    public function loadElasticDsn(): void
    {
        $this->checkPermAdmin();

        $dsn     = CView::get("dsn", "str notNull");
        $dsn_uid = CView::get("dsn_uid", "str");

        CView::checkin();

        $this->renderSmarty(
            'inc_view_elastic_dsn',
            [
                "dsn"     => $dsn,
                "dsn_uid" => $dsn_uid,
            ]
        );
    }

    public function testDsn(): void
    {
        $this->checkPermAdmin();

        $dsn = CView::get("dsn", "str notNull");

        CView::checkin();

        // Check params
        if (!$dsn) {
            CAppUI::stepAjax("CSQLDataSource-msg-No DSN specified", UI_MSG_ERROR);
        }

        $cache = Cache::getCache(Cache::OUTER);
        $cache->delete("ds_metadata-$dsn");

        $ds = @CSQLDataSource::get($dsn);

        $hosts = [];
        if (!$ds) {
            $dbhost = CAppUI::conf("db $dsn dbhost");
            $hosts  = preg_split('/\s*,\s*/', $dbhost);
        }

        $this->renderSmarty(
            'inc_dsn_status',
            [
                "ds"      => $ds,
                "dsn"     => $dsn,
                "section" => "db",
                "hosts"   => $hosts,
            ]
        );
    }

    public function testElasticDsn(): void
    {
        $this->checkPermAdmin();

        $dsn    = CView::get("dsn", "str notNull");
        $module = CView::get("module", "str notNull");

        CView::checkin();

        // Check params
        if (!$dsn) {
            CAppUI::stepAjax("CElasticDataSource-msg-No DSN specified", UI_MSG_ERROR);
        }

        $cache = Cache::getCache(Cache::OUTER);
        $cache->delete("ds_metadata-$dsn");

        $ds = ElasticIndexManager::get($dsn);

        $hosts = $ds->getConfig()->getConnectionParams();

        $elastic_object = $this->getElasticObjectFromCSetupModuleAndDsn($module, $dsn);
        $status         = $ds->getStatus($elastic_object);

        $this->renderSmarty(
            'inc_elastic_dsn_status',
            [
                "ds"      => $ds,
                "dsn"     => $dsn,
                "status"  => $status,
                "module"  => $module,
                "section" => "elastic",
                "hosts"   => $hosts,
            ]
        );
    }


    public function editDsn(): void
    {
        $this->checkPermAdmin();

        $dsn = CView::get("dsn", "str notNull");

        CView::checkin();

        $ds = CSQLDataSource::get($dsn, true);

        $this->renderSmarty(
            'inc_configure_dsn',
            [
                "ds"  => $ds,
                "dsn" => $dsn,
            ]
        );
    }

    public function editElasticDsn(): void
    {
        $this->checkPermAdmin();

        $dsn     = CView::get("dsn", "str notNull");

        CView::checkin();

        $this->renderSmarty(
            'inc_configure_elastic_dsn',
            [
                "dsn"     => $dsn,
            ]
        );
    }


    public function createDB(): void
    {
        $this->checkPermAdmin();

        $dsn  = CView::get("dsn", "str notNull");
        $host = CView::get("host", "str notNull");

        CView::checkin();

        $this->renderSmarty(
            'inc_create_db',
            [
                "host" => $host,
                "dsn"  => $dsn,
            ]
        );
    }

    private function getElasticObjectFromCSetupModuleAndDsn(string $module, string $dsn): ElasticObject
    {
        $setup_class = CSetup::getCSetupClass($module);

        /** @var CSetup $setup */
        $setup          = new $setup_class();
        $elastic_object = $setup->getElasticClassForDsn($dsn);
        if ($elastic_object === null) {
            trigger_error(
                CAppUI::tr(
                    "CDataSourceController-error-Did not find the specific class for the dsn (%s) in module (%s)",
                    $dsn,
                    $module
                )
            );
        }

        return $elastic_object;
    }


    public function doInitElasticObject(): void
    {
        $this->checkPermAdmin();

        $dsn          = CView::get("dsn", "str notNull");
        $setup_module = CView::get("setup_module", "str notNull");

        $elastic_object = $this->getElasticObjectFromCSetupModuleAndDsn($setup_module, $dsn);

        CView::checkin();

        ElasticObjectManager::init($elastic_object);
    }

    public function doCreateElasticIndex(): void
    {
        $this->checkPermAdmin();

        $dsn          = CView::get("dsn", "str notNull");
        $setup_module = CView::get("setup_module", "str notNull");

        $elastic_object = $this->getElasticObjectFromCSetupModuleAndDsn($setup_module, $dsn);

        CView::checkin();

        ElasticObjectManager::createFirstIndex($elastic_object);
    }

    public function doDeleteElasticIndex(): void
    {
        $this->checkPermAdmin();

        $dsn          = CView::get("dsn", "str notNull");
        $setup_module = CView::get("setup_module", "str notNull");

        $elastic_object = $this->getElasticObjectFromCSetupModuleAndDsn($setup_module, $dsn);

        CView::checkin();

        ElasticObjectManager::getInstance()->deleteIndex($elastic_object);
    }

    public function doCreateElasticTemplate(): void
    {
        $this->checkPermAdmin();

        $dsn          = CView::get("dsn", "str notNull");
        $setup_module = CView::get("setup_module", "str notNull");

        $elastic_object = $this->getElasticObjectFromCSetupModuleAndDsn($setup_module, $dsn);

        CView::checkin();

        dump(ElasticObjectManager::createTemplate($elastic_object));
    }

    public function doDeleteElasticTemplate(): void
    {
        $this->checkPermAdmin();

        $dsn          = CView::get("dsn", "str notNull");
        $setup_module = CView::get("setup_module", "str notNull");

        $elastic_object = $this->getElasticObjectFromCSetupModuleAndDsn($setup_module, $dsn);

        CView::checkin();

        ElasticObjectManager::getInstance()->deleteTemplate($elastic_object);
    }

    public function doCreateElasticILM(): void
    {
        $this->checkPermAdmin();

        $dsn          = CView::get("dsn", "str notNull");
        $setup_module = CView::get("setup_module", "str notNull");

        $elastic_object = $this->getElasticObjectFromCSetupModuleAndDsn($setup_module, $dsn);

        CView::checkin();

        ElasticObjectManager::createILM($elastic_object);
    }

    public function doDeleteElasticILM(): void
    {
        $this->checkPermAdmin();

        $dsn          = CView::get("dsn", "str notNull");
        $setup_module = CView::get("setup_module", "str notNull");

        $elastic_object = $this->getElasticObjectFromCSetupModuleAndDsn($setup_module, $dsn);

        CView::checkin();

        ElasticObjectManager::getInstance()->deleteILM($elastic_object);
    }

    public function doCreateDsn(): void
    {
        $this->checkPermAdmin();

        $dsn         = CView::post("dsn", "str notNull");
        $master_user = CView::post("master_user", "str notNull");
        $master_pass = CView::post("master_pass", "str");
        $master_host = CView::post("master_host", "str notNull");

        CView::checkin();

        // Check params
        if (!$dsn) {
            CAppUI::stepAjax("Aucun DSN spécifié", UI_MSG_ERROR);
        }

        global $dPconfig;

        if (!array_key_exists($dsn, $dPconfig["db"])) {
            CAppUI::stepAjax("Configuration pour le DSN '$dsn' inexistante", UI_MSG_ERROR);
        }

        $dsConfig =& $dPconfig["db"][$dsn];
        $dbtype   = $dsConfig["dbtype"];
        if (strpos($dbtype, "mysql") === false) {
            CAppUI::stepAjax("Seules les DSN MySQL peuvent tre crées par un accès administrateur", UI_MSG_ERROR);
        }

        // Substitute admin access
        $user = $dsConfig["dbuser"];
        $pass = $dsConfig["dbpass"];
        $name = $dsConfig["dbname"];
        $host = $dsConfig["dbhost"];

        $dsConfig["dbuser"] = $master_user;
        $dsConfig["dbpass"] = $master_pass;
        $dsConfig["dbhost"] = $master_host;
        $dsConfig["dbname"] = "";

        $ds = @CSQLDataSource::get($dsn);
        if (!$ds) {
            CAppUI::stepAjax("Connexion en tant qu'administrateur échouée", UI_MSG_ERROR);
        }

        CAppUI::stepAjax("Connexion en tant qu'administrateur réussie");

        $client_host = "localhost";
        if (!in_array($host, ["127.0.0.1", "localhost"])) {
            $client_host = CMbServer::getServerVar('SERVER_ADDR');
        }

        foreach ($ds->queriesForDSN($user, $pass, $name, $client_host) as $key => $query) {
            if (!$ds->exec($query)) {
                CAppUI::stepAjax("Requête '$key' échouée", UI_MSG_WARNING);
                continue;
            }

            CAppUI::stepAjax("Requête '$key' effectuée");
        }
    }
}
