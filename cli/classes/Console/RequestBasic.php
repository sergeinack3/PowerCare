<?php
/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Console;

use Ox\Cli\Request;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Request command
 */
class RequestBasic extends Request {
  protected $params;
  protected $username;
  protected $password;

  /**
   * @see parent::configure()
   */
  protected function configure() {
    parent::configure();

    $this
      ->setName('ox-request:basic')
      ->setAliases(['request:basic'])
      ->setDescription('Request a Mediboard controller')
      ->addArgument(
        'params',
        InputArgument::REQUIRED,
        'Query paramaters'
      )
      ->addOption(
        'username',
        'u',
        InputOption::VALUE_OPTIONAL,
        'Username'
      )
      ->addOption(
        'password',
        'p',
        InputOption::VALUE_OPTIONAL,
        'Password'
      );
  }

  function getParameters(InputInterface $input) {
    parent::getParameters($input);

    $this->params   = $input->getArgument('params');
    $this->username = $input->getOption('username');
    $this->password = $input->getOption('password');
  }

  /**
   * @inheritdoc
   */
  protected function getMediboardView() {
    return sprintf(
      "%s/index.php?%s",
      $this->url,
      $this->params
    );
  }

  protected function getUrl() {
    return sprintf(
      "%s/index.php?login=1&username=%s&password=%s&%s",
      $this->url,
      urlencode($this->username),
      urlencode($this->password),
      $this->params
    );
  }
}
