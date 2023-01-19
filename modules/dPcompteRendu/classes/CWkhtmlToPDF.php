<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbObject;
use Ox\Core\CMbPDFMerger;
use Ox\Core\Logger\LoggerLevels;
use Ox\Core\Sessions\CSessionHandler;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;

class CWkhtmlToPDF implements IShortNameAutoloadable
{

    const BIN_VERSION     = '-0-12-3';
    const BIN_OLD_VERSION = '-0-11-0-RC1';
    const BIN_NEW_VERSION = '-0-12-6';
    const BIN_NAME        = 'wkhtmltopdf-amd64';
    const BIN_PATH        = '/vendor/bin/';


    static $options = [];

    static $pdfs = [];

    /**
     * Get the right executable
     *
     * @return string
     */
    static function getExecutable($options = [])
    {
        if (self::isWindows()) {
            $bin = "wkhtmltopdf.exe";
        } else {
            $root_dir = CAppUI::conf("root_dir");
            $path     = self::BIN_PATH;
            if (isset($options['old'])) {
                $name = self::BIN_NAME . self::BIN_OLD_VERSION;
            } elseif (isset($options['new'])) {
                $name = self::BIN_NAME . self::BIN_NEW_VERSION;
            } else {
                $name = self::BIN_NAME . self::BIN_VERSION;
            }

            $bin = $root_dir . $path . DIRECTORY_SEPARATOR . $name;
        }

        return $bin;
    }

    /**
     * Tells if we are under Windows
     *
     * @return bool
     */
    protected static function isWindows()
    {
        return stripos(PHP_OS, "WIN") === 0;
    }

    static function addCookieSession()
    {
        return "--cookie \"" . session_name() . "\" \"" . session_id() . "\"";
    }

    /**
     * Build and return a PDF from a collection of urls
     *
     * @param CMbObject $object      Object targetted
     * @param string    $file_name   File name
     * @param array     $urls        List of urls to turn into a pdf
     * @param string    $size        Page size
     * @param string    $orientation Page orientation
     * @param string    $media       Default media
     * @param bool      $store_file  Store the CFile
     *
     * @return string   Resulting PDF
     */
    static function makePDF($object, $file_name, $urls, $size = "A4", $orientation = "Portrait", $media = "screen", $store_file = true)
    {
        self::$pdfs = [];

        $bin = self::getExecutable(['new' => true]);

        $base_url = CApp::getLocalBaseUrl() . "?";
        $cookie   = PHP_SAPI !== "cli" ? self::addCookieSession() : "";

        CSessionHandler::writeClose();

        // Create the PDFs
        foreach ($urls as $_url) {
            if (!count($_url)) {
                continue;
            }
            $build_url = $base_url;

            // Gestion de l'attente du window.status pour la génération PDF
            $window_status = null;
            if (array_key_exists("window-status", $_url)) {
                $window_status = $_url["window-status"];
                unset($_url["window-status"]);
            }

            $url_params = [];
            foreach ($_url as $key => $param) {
                $url_params[] = "$key=$param";
            }
            $build_url .= implode("&", $url_params);
            $result    = tempnam(CAppUI::conf("root_dir") . "/tmp", "result");
            $command   = "$bin --quiet --page-size $size --orientation $orientation $cookie " . ($media == "print" ? "--print-media-type " : "--no-print-media-type ") . "\"$build_url\"";

            if ($window_status) {
                $command .= " --window-status $window_status";
            }

      exec($command . " \"$result\" 2>&1", $output, $result_code);

      if ($result_code !== 0) {
          CApp::log('WkHtmlToPDF error ' . $result_code, $output, LoggerLevels::LEVEL_DEBUG);
      }

            self::$pdfs[] = $result;
        }

        // Merge the PDFs
        $merger = new CMbPDFMerger();
        foreach (self::$pdfs as $_pdf) {
            $merger->addPDF($_pdf);
        }

        try {
            $content = $merger->merge("string");
        } catch (Exception $e) {
            return null;
        }

        // Store the CFile
        if ($store_file) {
            $file = new CFile();
            $file->setObject($object);
            $file->author_id = CMediusers::get()->_id;
            $file->file_name = $file_name;
            $file->file_type = "application/pdf";
            $file->fillFields();
            $file->setContent($content);

            $file->store();
        }

        foreach (self::$pdfs as $_pdf) {
            unlink($_pdf);
        }

        return $content;
    }
}
