<?php
/**
 * @package Mediboard\Cli
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Cli\Console;

use Cz\Git\GitRepository;
use DateTimeImmutable;
use DOMDocument;
use DOMElement;
use Ox\Cli\MediboardCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * release:makexml / release:mx command
 */
class ReleaseMakeXML extends MediboardCommand {
  /**
   * @see parent::configure()
   */
  protected function configure() {
    $this
      ->setName('ox-release:makexml')
      ->setAliases(['ox-release:mx'])
      ->setDescription('Make release XML file')
      ->setHelp('Makes a release.xml file containing release information')
      ->addOption(
        'other',
        'o',
        InputOption::VALUE_OPTIONAL,
        'Global pattern for which we need a release.xml file'
      )
      ->addOption(
        'path',
        'p',
        InputOption::VALUE_OPTIONAL,
        'Working copy root for which we want to build release.xml',
        realpath(__DIR__ . "/../../../")
      );
  }

  /**
   * @param GitRepository $wc
   * @param string        $path
   * @param string        $current_branch
   *
   * @return DOMDocument
   */
  public function getReleaseXML($wc, $path, $current_branch) {
    $last_commit_id = $wc->getLastCommitId();

    $last_commit_data = $wc->getCommitData($last_commit_id);

    // release.xml file
    $release_element = new DOMElement("release");

    $dom_release = new DOMDocument();
    $dom_release->appendChild($release_element);

    $release_element->setAttribute("code", $current_branch);
    $release_element->setAttribute("date", (new DateTimeImmutable($last_commit_data['date']))->format(DateTimeImmutable::ATOM));
    $release_element->setAttribute("revision", $last_commit_data['commit']);

    return $dom_release;
  }

  /**
   * @see parent::execute()
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $path = $input->getOption('path');

    if (!is_dir($path)) {
      throw new InvalidArgumentException("'$path' is not a valid directory");
    }

    $wc             = new GitRepository($path);
    $current_branch = $wc->getCurrentBranchName();

    $matches = [];
    if (preg_match("@^release/(.*)@", $current_branch, $matches)) {
      $current_branch = $matches[1];
    }
    else {
      $this->out($output, "Current branch is not a release branch: <b>{$current_branch}</b>");

      return self::FAILURE;
    }

    $this->out($output, "Current release branch: '<b>$current_branch</b>'");

    // Make GPL release.xml
    $dom_release = $this->getReleaseXML($wc, $path, $current_branch);

    file_put_contents("$path/release.xml", $dom_release->saveXML());
    $this->out($output, "release.xml file written in: '<b>$path/release.xml</b>'");

    $wc->addFile(["$path/release.xml"]);
    $this->out($output, "'<b>$path/release.xml</b>' added to version control");

    $other = $input->getOption("other");

    if ($other) {
      $base_path = dirname($other);

      $add_files = [];

      $other_wc             = new GitRepository($base_path);
      $other_current_branch = $other_wc->getCurrentBranchName();

      $matches = [];
      if (preg_match("@^release/(.*)@", $other_current_branch, $matches)) {
        $other_current_branch = $matches[1];
      }

      if ($other_current_branch != $current_branch) {
        $this->out($output, "<error>WARNING: current branch ($current_branch) is not the same as other branch ($other_current_branch)</error>");

        return self::FAILURE;
      }

      $list = glob($other);

      foreach ($list as $_path) {
        $_dom_release = $this->getReleaseXML($other_wc, $_path, $other_current_branch);
        file_put_contents("$_path/release.xml", $_dom_release->saveXML());

        $add_files[] = "$_path/release.xml";

        $this->out($output, "release.xml file written in: '<b>$_path/release.xml</b>'");
      }

      $other_wc->addFile($add_files);
      $this->out($output, count($add_files) . " files added to version control, <b>ready to commit</b> !");
    }

    return self::SUCCESS;
  }
}
