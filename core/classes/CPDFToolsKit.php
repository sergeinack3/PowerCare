<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

/**
 * Classe for pdftk
 */
class CPDFToolsKit {

  public $path_file_source;
  public $fdf;
  public $exec = "pdftk";

  /**
   * Construct
   *
   * @param String $path File source path
   * @param String $fdf  File fdf path
   */
  function __construct($path, $fdf) {
    $this->path_file_source = $path;
    $this->fdf = $fdf;
  }

  /**
   * Fills the single input PDFs form fields with the data from an FDF file
   * XFDF file or stdin
   *
   * @param String $output  Path File of result
   * @param String $options Options
   *
   * @return bool|string
   */
  function fillForm($output = null, $options = null){

    $command = $this->createCommand("fill_form", $this->fdf, $output, $options);
    $result = $this->executeCommand($command);

    return $result;
  }

  /**
   * Generate the fdf of the pdf
   *
   * @return bool|string
   */
  function generateFdf() {

    $command = $this->createCommand("generate_fdf", null, $this->fdf, null);
    $result = $this->executeCommand($command);

    return $result;
  }

  /**
   * Create the command lign
   *
   * @param String $command  Command to execute
   * @param String $argument Operation argument
   * @param String $output   File path of output
   * @param String $options  Options
   *
   * @return string
   */
  private function createCommand($command, $argument = null, $output = null, $options = null) {
    $cmd = "$this->exec $this->path_file_source $command";

    if ($argument) {
      $cmd .= " $argument";
    }

    $cmd .= " output";

    if ($output) {
      $cmd .= " $output";
    }
    else {
      $cmd .= " -";
    }

    if ($options) {
      $cmd .= " $options";
    }

    return escapeshellcmd($cmd);
  }

  /**
   * Execute the command
   *
   * @param String $command Command to execute
   *
   * @return bool|string
   */
  private function executeCommand($command) {
    $processorInstance = proc_open($command, array(1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
    $processorResult = stream_get_contents($pipes[1]);
    $processorErrors = stream_get_contents($pipes[2]);
    proc_close($processorInstance);

    if ($processorErrors) {
      return false;
    }

    if (empty($processorResult)) {
      return true;
    }

    return $processorResult;
  }
}