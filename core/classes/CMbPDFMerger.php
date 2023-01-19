<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use DirectoryIterator;
use Exception;
use FPDI;
use Ox\Mediboard\CompteRendu\CWkHtmlToPDFConverter;
use Ox\Mediboard\Files\CFile;

/**
 * Classe de gestion de fusion de pdf
 */
class CMbPDFMerger {
  public $_files = array();

  /**
   * Ajout d'un fichier PDF à fusionner
   *
   * @param string $file_path Chemin du pdf
   *
   * @return void
   */
  function addPDF($file_path) {
    if (!file_get_contents($file_path)) {
      return;
    }
    // Suppression de l'autoprint et travail sur copie temporaire (afin de ne pas altérer le document original)
    $base_dir = CAppUI::conf("root_dir");
    @mkdir("$base_dir/tmp/pdfmerge");
    $temp_file = tempnam("$base_dir/tmp/pdfmerge", "pdfmerge");
    $this->convertPDToVersion14("pdf", $temp_file, $file_path);
    $content = file_get_contents($temp_file);
    $content = CWkHtmlToPDFConverter::removeAutoPrint($content);
    file_put_contents($temp_file, $content);
    $this->_files[] = $temp_file;
  }

  /**
   * Fusion des fichiers PDF
   *
   * @param string  $outputmode Mode de sortie du fichier résultat
   * @param string  $outputpath Nom du fichier résultat
   * @param boolean $autoprint  Autoprint du fichier résultat
   * @param boolean $use_fpdi   Use fpdi to merge
   *
   * @return mixed
   */
  function merge($outputmode = "browser", $outputpath = "newfile.pdf", $autoprint = false, $use_fpdi = false) {
    try {
      if ($use_fpdi) {
        $fpdi = new FPDI();

        foreach ($this->_files as $_file) {
          $count = $fpdi->setSourceFile($_file);

          for ($i = 1; $i <= $count; $i++) {
            $template = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($template);
            $orientation = $size["w"] > $size["h"] ? "L" : "P";
            $fpdi->AddPage($orientation, array($size["w"], $size["h"]));
            $fpdi->useTemplate($template);
          }
        }

        $content = $fpdi->Output(null, "S");
      }
      else {
        $gs  = CAppUI::conf("dPfiles CThumbnail gs_alias");
        $cmd = "$gs -q -dCompatibilityLevel=1.4 -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dPDFSETTINGS=/prepress -sOutputFile=- ";
        //Add each pdf file to the end of the command
        foreach ($this->_files as $file) {
          $cmd .= $file . " ";
        }
        $content = shell_exec($cmd);
      }
    }
    catch(Exception $e) {
      $this->deleteTempFiles();
      throw $e;
    }

    if ($autoprint) {
      $content = CWkHtmlToPDFConverter::addAutoPrint($content);
      // Il ne peut n'y avoir qu'une commande OpenAction : on retire celle déjà présente
      $content = str_replace("/OpenAction [3 0 R /FitH null]", "", $content);
    }

    $this->deleteTempFiles();

    if (PHP_SAPI !== "cli") {
      $shrink_level = CAppUI::gconf("dPcompteRendu CCompteRendu shrink_pdf");
      if ($shrink_level) {
        $temp_file = tempnam(CAppUI::conf("root_dir") . "/tmp/pdfmerge", "font_subset");
        file_put_contents($temp_file, $content);
        CFile::shrinkPDF($temp_file, null, $shrink_level);
        $content = file_get_contents($temp_file);
        unlink($temp_file);
      }
    }

    switch ($outputmode) {
      case "download":
        if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
          header('Content-Type: application/force-download');
        }
        else {
          header('Content-Type: application/octet-stream');
        }
        header('Content-Length: '.strlen($content));
        header('Content-disposition: attachment; filename="'.$outputpath.'"');
        echo $content;
        break;

      case "file":
        $f = fopen($outputpath, 'wb');
        if (!$f) {
          // Todo: Remove?
          throw new Exception('Unable to create output file: '.$outputpath);
        }
        fwrite($f, $content, strlen($content));
        fclose($f);
        break;

      case "string":
        return $content;

      default:
      case "browser":
        header('Content-Type: application/pdf');
        header('Content-Length: '.strlen($content));
        header('Content-disposition: inline; filename="'.$outputpath.'"');
        echo $content;
        break;
    }
  }

  /**
   * Suppression des fichiers tempoiraires
   *
   * @return void
   */
  function deleteTempFiles() {
    foreach ($this->_files as $_temp_file) {
      if (file_exists($_temp_file)) {
        unlink($_temp_file);
      }
    }

    if (mt_rand(1, 10) != 1) {
      return;
    }

    // Suppression des fichiers temporaires de plus de 24 heures
    $iterator = new DirectoryIterator("./tmp/pdfmerge");
    $count = 0;
    $now = time();

    foreach ($iterator as $_file) {
      if ($_file->isDot()) {
        continue;
      }

      $count++;

      $hours_creation = ($now - $_file->getMTime()) / 86400;

      if ($hours_creation > 24) {
        unlink($_file->getRealPath());
      }

      if ($count > 100) {
        break;
      }
    }
  }

  /**
   * Convert PDF to version 1.4
   *
   * @param string $type      Type
   * @param string $file      File
   * @param string $temp_file File temp
   *
   * @return string|null
   */
  function convertPDToVersion14($type, $file, $temp_file) {
    $gs        = CAppUI::conf('dPfiles CThumbnail gs_alias');
    $file_path = null;

    if (strpos($type, 'pdf') !== false) {
      // Convert high version to version 1.4
      $command = "$gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dBATCH -sOutputFile=$file $temp_file";

      exec($command);
      $file_path = $file;
    }

    return $file_path;
  }
}
