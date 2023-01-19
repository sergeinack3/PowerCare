<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbString;
use Ox\Core\CSQLDataSource;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CPreferences;

/**
 * Conversion html vers pdf
 * Cette classe n'est pas un MbObject et les objets ne sont pas enregistrés en base
 */
class CHtmlToPDF implements IShortNameAutoloadable {
  public $nbpages;
  public $content;

  public $display_elem = array (
    "inline" => array(
      "b", "strong", "big", "blink", "cite", "code", "del", "dfn",
      "em", "font", "i", "ins", "kbd", "nobr", "q", "s", "samp", "small",
      "span", "strike", "sub", "sup", "tt", "u", "var"
    ),
    "block"  => array(
      "address", "blockquote", "dd", "dl", "dt", "div", "dir",
      "h1", "h2", "h3", "h4", "h5", "h6", /*"hr",*/
      "listing", "isindex", "map", "menu", "multicol", "ol",
      "p", "pre", "plaintext", "table", "ul", "xmp",
    )
  );

  static $_width_page = 595.28;
  static $_marges = 2;

  static $_font_size_lookup = array(
    // For basefont support
    -3 => "4pt",
    -2 => "5pt",
    -1 => "6pt",
     0 => "7pt",

     1 => "8pt",
     2 => "10pt",
     3 => "12pt",
     4 => "14pt",
     5 => "18pt",
     6 => "24pt",
     7 => "34pt",

    // For basefont support
     8 => "48pt",
     9 => "44pt",
    10 => "52pt",
    11 => "60pt",
  );

  static $shrink_levels = array(
    "1" => "font_subset",
    "2" => "font_subset_resampling"
  );

  /**
   * Constructeur à partir d'une factory
   *
   * @param string $factory Factory name
   * @param array  $options Options
   */
  function __construct($factory = null, $options = array()) {
    if ($factory === null || $factory === "none") {
      $factory = "CWkHtmlToPDFConverter";
    }
    CHtmlToPDFConverter::init($factory, $options);
  }

  /**
   * Destructeur standard
   */
  function __destruct() {
    $this->content = null;
    unset($this->content);
  }

  /**
   * Génération d'un pdf à partir d'une source, avec stream au client si demandé
   *
   * @param string        $content      source html
   * @param boolean       $stream       envoi du pdf au navigateur
   * @param CCompteRendu  $compte_rendu compte-rendu ciblé
   * @param CFile         $file         le CFile pour lequel générer le pdf
   * @param boolean       $auto_print   le pdf est en auto impression
   *
   * @return string
   */
  function generatePDF($content, $stream, $compte_rendu, $file, $auto_print = true, $load_xml = true) {
    if (strpos(get_class(CHtmlToPDFConverter::$instance), "DomPDF") !== false) {
      $content = preg_replace('/>\s+</', '><', $content);
    }

    // Suppression des caractères de contrôle non autorisés
    $invalid_characters = "/[^\x9\xa\x20-\xD7FF\xE000-\xFFFD]/";
    $content = preg_replace($invalid_characters, '', $content);

    $this->content = $this->fixBlockElements($content, $load_xml);

    // Remplacement des champs seulement à l'impression
    $this->content = str_replace("[Général - numéro de page]", '<span class="page"></span>', $this->content);
    $this->content = str_replace("[Général - nombre de pages]", '<span class="topage"></span>', $this->content);

    // Remplacement des urls d'imges qui référencent un CFile
    preg_match_all(
      "/\?m=(files|dPfiles)&(amp;)?raw=(fileviewer|thumbnail)([^\>]*)&(amp;)?(document_guid|file_id)=(CFile-)?([0-9]+)([^\s'\"]*)/",
      $this->content, $matches
    );

    if (count($matches[1])) {
      $image = new CFile();
      $where = array(
        "file_id" => CSQLDataSource::prepareIn(array_unique($matches[8]))
      );
      $images = $image->loadList($where);

      $to = array();

      foreach ($matches[8] as $_match) {
        /** @var CFile $image */
        $image = $images[$_match];
        $to[] = "file:///" . $image->_file_path;
      }

      $this->content = str_replace($matches[0], $to, $this->content);
    }


    $date_lock = "";
    $locker = new CMediusers();

    if ($compte_rendu->valide) {
      $locker = $compte_rendu->loadRefLocker();
      $log_lock = $compte_rendu->loadLastLogForField("valide");
      $date_lock = $log_lock->date;

      // Remplacement de la signature seulement à l'impression si la préférence est activée pour le praticien (signataire)
      if ($compte_rendu->signataire_id && $compte_rendu->locker_id == $compte_rendu->signataire_id) {
        $preferences = CPreferences::getAllPrefs($compte_rendu->signataire_id);

        if (isset($preferences["secure_signature"]) && $preferences["secure_signature"]) {
          $signature = CMediusers::get($compte_rendu->signataire_id)->loadRefSignature();
          $this->content = str_replace("%5BPraticien%20-%20Signature%5D", $signature->getDataURI(), $this->content);
        }
      }
    }

    $this->content = str_replace("[Meta Données - Date de verrouillage - Date]" , $compte_rendu->valide ? CMbDT::format($date_lock, CAppUI::conf("date")) : "", $this->content);
    $this->content = str_replace("[Meta Données - Date de verrouillage - Heure]", $compte_rendu->valide ? CMbDT::format($date_lock, CAppUI::conf("time")) : "", $this->content);
    $this->content = str_replace("[Meta Données - Verrouilleur - Nom]"      , $locker->_user_last_name, $this->content);
    $this->content = str_replace("[Meta Données - Verrouilleur - Prénom]"   , $locker->_user_first_name, $this->content);
    $this->content = str_replace("[Meta Données - Verrouilleur - Initiales]", $locker->_shortview, $this->content);

    CHtmlToPDFConverter::$_page_ordonnance = $compte_rendu->_page_ordonnance;

    $pdf_content = CHtmlToPDFConverter::convert($this->content, $compte_rendu->_page_format, $compte_rendu->_orientation);

    if (!$auto_print) {
      $pdf_content = CWkHtmlToPDFConverter::removeAutoPrint($pdf_content);
    }

    if ($file->_file_path) {
      // Ne pas écrire de fichier si le pdf n'a pas été généré
      if (!$pdf_content) {
        return null;
      }

      $shrink_level = CAppUI::gconf("dPcompteRendu CCompteRendu shrink_pdf");
      if ($shrink_level) {
        CFile::shrinkPDF($file->_file_path, null, $shrink_level);
        $pdf_content = CWkHtmlToPDFConverter::addAutoPrint(file_get_contents($file->_file_path));
        $file->compression = self::$shrink_levels[$shrink_level];
      }

      $file->setPrefix();
      $file->setContent($pdf_content);
    }

    $this->nbpages = preg_match_all("/\/Page\W/", $pdf_content, $matches);

    if ($stream) {
      header("Pragma: ");
      header("Cache-Control: ");
      header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
      header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
      header("Cache-Control: no-store, no-cache, must-revalidate");  //HTTP/1.1
      header("Cache-Control: post-check=0, pre-check=0", false);
      // END extra headers to resolve IE caching bug
      header("MIME-Version: 1.0");
      header("Content-length: ".strlen($pdf_content));
      header('Content-type: application/pdf');
      header("Content-disposition: inline; filename=\"".$file->file_name."\"");

      echo $pdf_content;
    }
    
    return $pdf_content;
  }

  /**
   * Nettoyage de la source qui peut être altérée par un copier-coller provenant de word
   * Expressions régulières provenant de FCKEditor
   * cf http://docs.cksource.com/FCKeditor_2.x/Developers_Guide/Configuration/Configuration_Options/CleanWordKeepsStructure
   *
   * @param string $str source html
   *
   * @return string
   */
  static function cleanWord($str) {
    $str = str_replace("<o:p>", "<p>", $str);
    $str = str_replace("</o:p>", "</p>", $str);
    $str = str_replace("<w:", '<', $str);
    $str = str_replace("</w:", '</', $str);
    $str = preg_replace("/<o:smarttagtype.*smarttagtype>/", '', $str);
    $str = preg_replace("/<\/?\w+:[^>]*>/", '', $str);
    $str = preg_replace("/<tr>\s*<\/tr>/", '', $str);
    $str = str_replace("<tr/>", '', $str);
    $str = preg_replace("/<tr>[ \t\r\n\f]*<td>[ \t\r\n\f]*&#160;[ \t\r\n\f]*<\/td>[ \t\r\n\f]*<\/tr>/", '', $str);
    $str = str_replace("text-align:=\"\"", '', $str);
    $str = preg_replace("/v:shapes*=\"[_a-z0-9]+\"/", "", $str);
    return $str;
  }

  /**
   * Correction de problèmes de dom
   * 
   * @param string $str source html
   * 
   * @return string 
   */
  function fixBlockElements($str, $load_xml = true) {
    $xml = new DOMDocument('1.0', 'iso-8859-1');

    if ($load_xml) {
        $str = CMbString::convertHTMLToXMLEntities($str);
    }

    $str = self::cleanWord($str);

    // Suppression des caractères de contrôle
    $from = array(
      chr(3), // ETX (end of text)
      chr(7),  // BEL
      chr(30)  // Record separator
    );

    $to = "";

    $str = str_replace($from, $to, $str);

    if ($load_xml) {
        $xml->loadXML(utf8_encode($str), LIBXML_VERSION >= 20900 ? LIBXML_PARSEHUGE : null);

        $html = $xml->getElementsByTagName("body")->item(0);

        if (is_null($html)) {
            $html = $xml->firstChild;
        }
        // If $html is null $str may contain forbidden characters for XML
        if (is_null($html)) {
            CAppUI::stepAjax("CCompteRendu-empty-doc");
            CApp::rip();
        }

        $xpath = new DOMXpath($xml);

        // Solution temporaire pour les problèmes de mise en page avec domPDF
        while ($elements = $xpath->query("//span[@class='field']")) {
            if ($elements->length == 0) {
                break;
            }
            foreach ($elements as $_element) {
                foreach ($_element->childNodes as $child) {
                    /** @var DOMElement $child */
                    $_element->parentNode->insertBefore($child->cloneNode(true), $_element);
                }
                $_element->parentNode->removeChild($_element);
            }
        }

        $this->recursiveRemove($html);
        $this->recursiveRemoveNestedFont($html);
        $this->resizeTable($html);

        // Suppression des sauts de pages dans l'entête et le pied de page
        $elements = $xpath->query("//div[@id='header']//hr[@class='pagebreak']");

        if (!is_null($elements)) {
            foreach ($elements as $_element) {
                $_element->parentNode->removeChild($_element);
            }
        }

        $elements = $xpath->query("//div[@id='footer']//hr[@class='pagebreak']");

        if (!is_null($elements)) {
            foreach ($elements as $_element) {
                $_element->parentNode->removeChild($_element);
            }
        }

        $str = $xml->saveHTML();
    }

    $str = preg_replace("/<br>/", "<br/>", $str);
    return $str;
  }

  /**
   * Correction récursive d'éléments de display inline qui imbriquent
   * des éléments de display block
   *
   * @param DOMElement|DOMNode &$node noeud à parcourir
   *
   * @return void
   */
  function recursiveRemove(DOMNode &$node) {
    if (!$node->hasChildNodes()) {
      return;
    }
    foreach ($node->childNodes as $child) {
      if ((in_array($child->nodeName, $this->display_elem["block"]) &&
           in_array($node->nodeName, $this->display_elem["inline"])) ||
           ($node->nodeName == "span" && $child->nodeName == "hr")
      ) {
        // On force le display: block pour les éléments en display:inline et qui imbriquent des élements
        // en display: block.
        $style = $node->getAttribute("style");
        if (strpos($style, ";") != (strlen($style) - 1) && $style != "") {
          $style .= ";";
        }
        $node->setAttribute("style", $style . "display: block;");
        break;
      }
      $this->recursiveRemove($child);
    }
  }

  /**
   * Transformation des tailles des tableaux de pixels en pourcentages
   * Feuille A4
   *   largeur en cm : 21
   *   largeur en pixels : 595.28
   *
   * @param DOMElement|DOMNode &$node noeud à parcourir
   *
   * @return void
   */
  function resizeTable(DOMNode &$node) {
    if (!$node->hasChildNodes()) {
      return;
    }

    /** @var DOMElement $_child */
    foreach ($node->childNodes as $_child) {
      if ($_child->nodeName == "table") {
        $width = $_child->getAttribute("width");
        $width_without_marges = self::$_width_page - (self::$_marges / self::$_width_page) * 100;
        if (!strrpos($width, "%")) {
          if ($width > $width_without_marges) {
            $_child->setAttribute("width", "100%");
          }
          else if ($width <= $width_without_marges & $width > 0) {
            $new_width = ($width * 100) / ($width_without_marges - self::$_marges * 2);
            $_child->setAttribute("width", "$new_width%");
          }
        }
      }
      self::resizeTable($_child);
    }
  }

    /**
     * Suppression des balises fonts imbriquées
     *
     * @param DOMNode &$node noeud à parcourir
     *
     * @return void
     */
    function recursiveRemoveNestedFont(DOMNode &$node) {
        if (!$node->hasChildNodes()) {
            return;
        }

        foreach ($node->childNodes as $child) {
            /** @var DOMElement $node->firstChild */
            if ($node->nodeName == "font" && $child->nodeName == "font" &&
                $node->firstChild &&
                $node->firstChild === $node->lastChild
            ) {
                if ($node->firstChild->getAttribute("family") == "") {
                    $node->firstChild->setAttribute("family", $node->getAttribute("family"));
                }
                $child = $node->removeChild($node->firstChild);
                $parent = $node->parentNode;
                $parent->insertBefore($child, $node);
                $parent->removeChild($node);
            }
            self::recursiveRemoveNestedFont($child);
        }
    }

  /**
   * Validation d'une source html
   * 
   * @return boolean
   */
  function htmlValidate() {
    $doc = new DOMDocument();
    return $doc->loadHTML($this->content) == 1;
  }
}
