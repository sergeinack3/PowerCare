<?php
/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Console;

use _PHPStan_76800bfb5\React\Dns\Config\Config;
use DOMDocument;
use DOMElement;
use DOMNode;
use Ox\Cli\MediboardCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class MonitoringMakeXML
 *
 * @package Ox\Cli\Console
 */
class MonitoringMakeXML extends MediboardCommand {
  public $path;
  public $server_url;
  public $config = array();

  /** @var DOMDocument */
  public $doc;

  static $keys = array(
    'base_url',
    'root_dir',
    'shared_memory_params',
    'db std dbname',
    'db std dbhost',
    'db std dbuser',
    'db std dbpass',
    'db slave dbname',
    'db slave dbhost',
    'db slave dbuser',
    'db slave dbpass',
    'search client_host',
    'search client_port',
    'monitorClient',
    'db',
  );

  static $default_datasources = array(
    'bcb1',
    'bcb2',
    'vidal',
  );

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this
      ->setName('ox-monitoring:makexml')
      ->setAliases(['ox-monitoring:mx'])
      ->setDescription('Make monitoring XML file')
      ->setHelp('Makes a monitoring.xml file containing monitoring configuration')
      ->addOption(
        'server_url',
        's',
        InputOption::VALUE_OPTIONAL,
        'Remote monitoring server URL',
        'erp.openxtrem.com'
      )
      ->addOption(
        'path',
        'p',
        InputOption::VALUE_OPTIONAL,
        'Working copy root for which we want to build monitoring.xml',
        realpath(__DIR__ . "/../../../")
      );
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->path       = $input->getOption('path');
    $this->server_url = $input->getOption('server_url');

    if (!is_dir($this->path)) {
      throw new InvalidArgumentException("'<b>{$this->path}</b>' is not a valid directory");
    }

    if ($monitoring_xml = $this->getMonitoringXML()) {
      $file_path = "{$this->path}/modules/monitorClient/cli/conf/monitoring_test.xml";

      if (!file_put_contents($file_path, $monitoring_xml)) {
        $this->out($output, "<error>Unable to write '<b>{$file_path}</b>'</error>");

        return self::FAILURE;
      }

      $this->out($output, "monitoring.xml file written in: '<b>{$file_path}</b>'");
    }
    else {
      $this->out($output, "<error>Unable to get configuration</error>");
    }

    return self::SUCCESS;
  }

  /**
   * Generate monitoring.xml file
   *
   * @return DOMDocument
   */
  public function getMonitoringXML() {
    foreach (self::$keys as $_key) {
      $this->config[$_key] = $this->getMBConfig($_key);
    }

    $this->doc               = new DOMDocument();
    $this->doc->formatOutput = true;

    $root = $this->doc->createElement('monitoring');
    $root = $this->doc->appendChild($root);

    // ERP
    $erp = $this->getNode('erp', $root);

    // CLIENT
    $client = $this->getNode('client', $root);

    // CLIENT / SERVERS
    $servers = $this->getNode('servers', $client);

    // CLIENT / SERVERS / SERVER
    $server = $this->getNode('server', $servers);

    // CLIENT / SERVERS / SERVER / SYSTEM
    $system = $this->getNode('system', $server);

    // CLIENT / SERVERS / SERVER / MIDDLEWARE
    $middleware = $this->getNode('middleware', $server);

    // CLIENT / SERVERS / SERVER / APP
    $app = $this->getNode('app', $server);

    // CLIENT / SERVERS / SERVER / APP / LOAD BALANCER
    $load_balancing = $this->getNode('lb', $app);

    // CLIENT / SERVERS / SERVER / DATA
    $data = $this->getNode('data', $server);

    // CLIENT / SERVERS / SERVER / DATA / REDIS
    $redis = $this->getNode('redis', $data);

    // CLIENT / SERVERS / SERVER / DATA / REPLICATION
    $replication = $this->getNode('replication', $data);

    // CLIENT / SERVERS / SERVER / DATA / REPLICATION / SEARCH
    $search = $this->getNode('search', $data);

    // CLIENT / SERVERS / SERVER / PARTITIONS
    $partitions = $this->getNode('partitions', $server);

    // CLIENT / SERVERS / SERVER / BACKUPS
    $backups = $this->getNode('backups', $server);

    // CLIENT / SERVERS / SERVER / MOUNT POINTS
    $mount_points = $this->getNode('mount_points', $server);

    // CLIENT / MEDIBOARD
    $mediboard = $this->getNode('mediboard', $client);

    // CLIENT / MEDIBOARD / INSTANCE
    $instance = $this->getNode('instance', $mediboard);

    // CLIENT / MEDIBOARD / INSTANCE / GROUPS
    $groups = $this->getNode('groups', $instance);

    // CLIENT / MEDIBOARD / SOURCES
    $sources = $this->getNode('sources', $mediboard);

    // CLIENT / MEDIBOARD / DATASOURCES
    $datasources = $this->getNode('datasources', $mediboard);

    // CLIENT / MEDIBOARD / ACCESS LOGS
    $access_logs = $this->getNode('access_logs', $mediboard);

    // CLIENT / MEDIBOARD / INSTANCE REPORT
    $instance_report = $this->getNode('instance_report', $mediboard);

    // CLIENT / MEDIBOARD / INSTANCE REPORT / MODULE ACTIONS
    $access_logs = $this->getNode('module_actions', $instance_report);

    // CLIENT / MEDIBOARD / INSTANCE REPORT / PREFERENCES
    $access_logs = $this->getNode('preferences', $instance_report);

    // CLIENT / MEDIBOARD / INSTANCE REPORT / CONFIGURATIONS
    $access_logs = $this->getNode('configurations', $instance_report);

    // CLIENT / MEDIBOARD / INSTANCE REPORT / MB DATA
    $access_logs = $this->getNode('mb_data', $instance_report);

    // CLIENT / MEDIBOARD / INSTANCE REPORT / USER LOGS
    $user_logs = $this->getNode('user_logs', $instance_report);

    return $this->doc->saveXML();
  }

  /**
   * Node factory
   *
   * @param string  $node_name Node name
   * @param DOMNode $parent    Parent node
   *
   * @return bool|DOMNode
   */
  function getNode($node_name, $parent) {
    switch ($node_name) {
      case 'erp':
        return $this->getERPNode($parent);

      case 'client':
        return $this->getClientNode($parent);

      case 'servers':
        return $this->getServersNode($parent);

      case 'server':
        return $this->getServerNode($parent);

      case 'system':
        return $this->getSystemNode($parent);

      case 'middleware':
        return $this->getMiddlewareNode($parent);

      case 'app':
        return $this->getAppNode($parent);

      case 'data':
        return $this->getDataNode($parent);

      case 'lb':
        return $this->getLBNode($parent);

      case 'redis':
        return $this->getRedisNode($parent);

      case 'replication':
        return $this->getReplicationNode($parent);

      case 'search':
        return $this->getSearchNode($parent);

      case 'mediboard':
        return $this->getMediboardNode($parent);

      case 'instance':
        return $this->getInstanceNode($parent);

      case 'groups':
        return $this->getGroupsNode($parent);

      case 'sources':
        return $this->getSourcesNode($parent);

      case 'datasources':
        return $this->getDataSourcesNode($parent);

      case 'partitions':
        return $this->getPartitionsNode($parent);

      case 'backups':
        return $this->getBackupsNode($parent);

      case 'mount_points':
        return $this->getMountPointsNode($parent);

      case 'access_logs':
        return $this->getAccessLogsNode($parent);

      case 'instance_report':
        return $this->getInstanceReportNode($parent);

      case 'module_actions':
        return $this->getModuleActionsNode($parent);

      case 'preferences':
        return $this->getPreferencesNode($parent);

      case 'configurations':
        return $this->getConfigurationsNode($parent);

      case 'mb_data':
        return $this->getMBDataNode($parent);

      case 'user_logs':
        return $this->getUserLogsNode($parent);

      default:
        return false;
    }
  }

  /**
   * Comment node
   *
   * @param string  $text   Content
   * @param DOMElement $parent Parent node
   *
   * @return null
   */
  function createComment($text, $parent) {
    $comment = $this->doc->createComment($text);
    $parent->appendChild($comment);
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getERPNode(DOMElement $parent) {
    $this->createComment('ERP', $parent);

    $node = $this->doc->createElement('server');
    $node->setAttribute('host', $this->server_url);
    $node->setAttribute('username', '');
    $node->setAttribute('password', '');
    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getClientNode(DOMElement $parent) {
    $this->createComment('CLIENT', $parent);

    $node = $this->doc->createElement('client');
    $node->setAttribute('name', '');
    $node->setAttribute('url', $this->config['base_url']);
    $node->setAttribute('root', $this->config['root_dir']);
    $node->setAttribute('term_timeout', 15);
    $node->setAttribute('send_timeout', 30);
    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getServersNode(DOMElement $parent) {
    $this->createComment('CLIENT / SERVERS', $parent);

    $node = $this->doc->createElement('servers');
    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getServerNode(DOMElement $parent) {
    $this->createComment('CLIENT / SERVERS / SERVER', $parent);

    $node = $this->doc->createElement('server');
    $node->setAttribute('num_instance', $this->config['monitorClient']['monitor_client_num_instance']);
    $node->setAttribute('aggregate', '1');
    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getSystemNode(DOMElement $parent) {
    $this->createComment('CLIENT / SERVERS / SERVER / SYSTEM', $parent);

    $node = $this->doc->createElement('system');
    $node->setAttribute('aggregate', '1');
    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getMiddlewareNode(DOMElement $parent) {
    $this->createComment('CLIENT / SERVERS / SERVER / MIDDLEWARE', $parent);

    $node = $this->doc->createElement('middleware');
    $node->setAttribute('aggregate', '1');
    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getAppNode(DOMElement $parent) {
    $this->createComment('CLIENT / SERVERS / SERVER / APP', $parent);

    $node = $this->doc->createElement('app');
    $node->setAttribute('bin', 'apache2');
    $node->setAttribute('aggregate', '1');
    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getDataNode(DOMElement $parent) {
    $this->createComment('CLIENT / SERVERS / SERVER / DATA', $parent);

    $node = $this->doc->createElement('data');
    $node->setAttribute('host', $this->config['db std dbhost']);
    $node->setAttribute('user', $this->config['db std dbuser']);
    $node->setAttribute('password', $this->config['db std dbpass']);
    $node->setAttribute('db', $this->config['db std dbname']);
    $node->setAttribute('aggregate', '1');
    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getLBNode(DOMElement $parent) {
    $this->createComment('CLIENT / SERVERS / SERVER / APP / LOAD BALANCER', $parent);

    switch ($this->config['monitorClient']['monitor_client_load_balancer_type']) {
      case 'Logicielle':
        $method = 'wget';
        break;

      case 'Materielle':
        $method = 'ping';
        break;

      default:
        $method = '';
    }

    $node = $this->doc->createElement('load_balancing');
    $node->setAttribute('method', $method);
    $node->setAttribute('host', '');
    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getRedisNode(DOMElement $parent) {
    $this->createComment('CLIENT / SERVERS / SERVER / DATA / REDIS', $parent);

    $node = $this->doc->createElement('redis');
    $node->setAttribute('idle_threshold', $this->config['monitorClient']['redis_idle_threshold']);

    if ($this->config['shared_memory_params']) {
      $servers = preg_split('/\s*,\s*/', $this->config['shared_memory_params']);

      foreach ($servers as $_server) {
        [$_host, $_port] = explode(':', $_server);

        $_node = $this->doc->createElement('redis_server');
        $_node->setAttribute('host', $_host);
        $_node->setAttribute('port', $_port);

        $_node = $node->appendChild($_node);
      }
    }

    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getReplicationNode(DOMElement $parent) {
    $this->createComment('CLIENT / SERVERS / SERVER / DATA / REPLICATION', $parent);

    $node = $this->doc->createElement('replication');
    $node->setAttribute('slave_master_server_id', '');
    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getSearchNode(DOMElement $parent) {
    $this->createComment('CLIENT / SERVERS / SERVER / DATA / SEARCH', $parent);

    $node = $this->doc->createElement('search');
    $node->setAttribute('host', $this->config['search client_host']);
    $node->setAttribute('port', $this->config['search client_port']);
    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getPartitionsNode(DOMElement $parent) {
    $this->createComment('CLIENT / SERVERS / SERVER / PARTITIONS', $parent);

    $node = $this->doc->createElement('partitions');
    $node->setAttribute('aggregate', '10');

    $parts = explode('|', $this->config['monitorClient']['monitor_client_partitions']);
    foreach ($parts as $_part) {
      $_node = $this->doc->createElement('partition');
      $_node->setAttribute('path', $_part);
      $_node = $node->appendChild($_node);
    }

    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getBackupsNode(DOMElement $parent) {
    $this->createComment('CLIENT / SERVERS / SERVER / BACKUPS', $parent);

    $node = $this->doc->createElement('backups');
    $node->setAttribute('aggregate', '10');

    $parts = explode('|', $this->config['monitorClient']['monitor_client_backups']);
    foreach ($parts as $_part) {
      $_node = $this->doc->createElement('backup');
      $_node->setAttribute('path', $_part);
      $_node = $node->appendChild($_node);
    }

    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getMountPointsNode(DOMElement $parent) {
    $this->createComment('CLIENT / SERVERS / SERVER / MOUNT POINTS', $parent);

    $node = $this->doc->createElement('mount_points');
    $node->setAttribute('aggregate', '10');

    $parts = explode('|', $this->config['monitorClient']['monitor_client_mount_points']);
    foreach ($parts as $_part) {
      $_node = $this->doc->createElement('mount_point');
      $_node->setAttribute('path', $_part);
      $_node = $node->appendChild($_node);
    }

    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getMediboardNode(DOMElement $parent) {
    $this->createComment('CLIENT / MEDIBOARD', $parent);

    $node = $this->doc->createElement('mediboard');
    $node->setAttribute('instance_id', $this->config['monitorClient']['monitor_client_instance_id']);
    $node->setAttribute('host', $this->config['db std dbhost']);
    $node->setAttribute('user', $this->config['db std dbuser']);
    $node->setAttribute('password', $this->config['db std dbpass']);
    $node->setAttribute('db', $this->config['db std dbname']);
    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getInstanceNode(DOMElement $parent) {
    $this->createComment('CLIENT / MEDIBOARD / INSTANCE', $parent);

    $node = $this->doc->createElement('instance');
    $node->setAttribute('aggregate', '10');

    $servers_node = $this->doc->createElement('redis_servers');
    $servers_node = $node->appendChild($servers_node);

    if ($this->config['shared_memory_params']) {
      $servers = preg_split('/\s*,\s*/', $this->config['shared_memory_params']);

      foreach ($servers as $_server) {
        [$_host, $_port] = explode(':', $_server);

        $_node = $this->doc->createElement('redis_server');
        $_node->setAttribute('host', $_host);
        $_node->setAttribute('port', $_port);

        $_node = $servers_node->appendChild($_node);
      }
    }

    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getGroupsNode(DOMElement $parent) {
    $this->createComment('CLIENT / MEDIBOARD / INSTANCE / GROUPS', $parent);

    $node = $this->doc->createElement('groups');
    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getSourcesNode(DOMElement $parent) {
    $this->createComment('CLIENT / MEDIBOARD / SOURCES', $parent);

    $node = $this->doc->createElement('sources');
    $node->setAttribute('aggregate', '10');

    $node->setAttribute('username', $this->config['monitorClient']['monitor_client_login_instance']);
    $node->setAttribute('password', $this->config['monitorClient']['monitor_client_password_instance']);
    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getDataSourcesNode(DOMElement $parent) {
    $datasources = array();

    foreach ($this->config['db'] as $_datasource => $_db) {
      if (!in_array($_datasource, static::$default_datasources)) {
        continue;
      }

      if (!$_db['dbname'] || !$_db['dbuser'] || !$_db['dbpass'] || !$_db['dbhost']) {
        continue;
      }

      $datasources[] = $_db;
    }

    if (!$datasources) {
      return null;
    }

    $this->createComment('CLIENT / MEDIBOARD / DATASOURCES', $parent);

    $node = $this->doc->createElement('datasources');
    $node->setAttribute('aggregate', 10);

    foreach ($datasources as $_db) {
      $_node = $this->doc->createElement('datasource');
      $_node->setAttribute('name', $_db['dbname']);
      $_node->setAttribute('username', $_db['dbuser']);
      $_node->setAttribute('password', $_db['dbpass']);
      $_node->setAttribute('host', $_db['dbhost']);

      $_node = $node->appendChild($_node);
    }

    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getAccessLogsNode(DOMElement $parent) {
    $this->createComment('CLIENT / MEDIBOARD / ACCESS LOGS', $parent);

    $node = $this->doc->createElement('access_logs');
    $node->setAttribute('aggregate', '10');
    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getInstanceReportNode(DOMElement $parent) {
    $this->createComment('CLIENT / MEDIBOARD / INSTANCE REPORT', $parent);

    $node = $this->doc->createElement('instance_report');
    $node->setAttribute('trigger_hour', '8');
    $node->setAttribute('aggregate', '10080');
    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getModuleActionsNode(DOMElement $parent) {
    $this->createComment('CLIENT / MEDIBOARD / INSTANCE REPORT / MODULE ACTIONS', $parent);

    $node = $this->doc->createElement('module_actions');
    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getPreferencesNode(DOMElement $parent) {
    $this->createComment('CLIENT / MEDIBOARD / INSTANCE REPORT / PREFERENCES', $parent);

    $node = $this->doc->createElement('preferences');
    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getConfigurationsNode(DOMElement $parent) {
    $this->createComment('CLIENT / MEDIBOARD / INSTANCE REPORT / CONFIGURATIONS', $parent);

    $node = $this->doc->createElement('configurations');
    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getMBDataNode(DOMElement $parent) {
    $this->createComment('CLIENT / MEDIBOARD / INSTANCE REPORT / MB DATA', $parent);

    $node = $this->doc->createElement('mb_data');
    $node = $parent->appendChild($node);

    return $node;
  }

  /**
   * Node creator
   *
   * @param DOMElement $parent Parent node
   *
   * @return DOMNode
   */
  function getUserLogsNode(DOMElement $parent) {
    $this->createComment('CLIENT / MEDIBOARD / INSTANCE REPORT / USER LOGS', $parent);

    $node = $this->doc->createElement('user_logs');
    $node = $parent->appendChild($node);

    return $node;
  }
}
