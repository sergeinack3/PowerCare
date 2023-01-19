<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\Mutex\CMbMutex;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CHtmlToPDF;
use Ox\Mediboard\CompteRendu\CStatutCompteRendu;
use Ox\Mediboard\CompteRendu\CWkHtmlToPDFConverter;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;

/**
 * Génération d'un PDF à partir d'une source html
 */
$compte_rendu_id = CView::post("compte_rendu_id", 'ref class|CCompteRendu');

// On s'arrête là si pas d'id
if (!$compte_rendu_id) {
  CApp::rip();
}

$compte_rendu = new CCompteRendu();
$compte_rendu->load($compte_rendu_id);

if (!$compte_rendu->_id) {
  CAppUI::stepAjax(CAppUI::tr("CCompteRendu-alert_doc_deleted"));
  CApp::rip();
}

$compte_rendu->loadContent();
$generate_thumbs = CView::post("generate_thumbs", 'bool default|0');
$mode        = CView::post("mode", "str default|doc");
$print       = CView::post("print", 'bool default|0');
$type        = CView::post("type", array('str', 'default' => $compte_rendu->type));
$preface_id  = CView::post("preface_id", array('ref', 'class' => 'CCompteRendu', 'default' => $compte_rendu->preface_id));
$ending_id   = CView::post("ending_id" , array('ref', 'class' => 'CCompteRendu', 'default' => $compte_rendu->ending_id));
$header_id   = CView::post("header_id" , array('ref', 'class' => 'CCompteRendu', 'default' => $compte_rendu->header_id));
$footer_id   = CView::post("footer_id" , array('ref', 'class' => 'CCompteRendu', 'default' => $compte_rendu->footer_id));
$stream      = CView::post("stream", 'bool default|0');
$content     = stripslashes(urldecode(CView::post("content", 'str')));
if (!$content) {
  $content = $compte_rendu->_source;
}

$save_content = $content;

$ids_corres  = CView::post("_ids_corres", 'str');
$write_page  = CView::post("write_page", 'bool default|0');
$update_date_print = CView::post("update_date_print", 'bool default|0');
$page_format = CView::post(
  "page_format",
  array(
    'enum',
    'list' => implode("|", array_keys(CCompteRendu::$_page_formats)),
    'default' => $compte_rendu->_page_format
  )
);
$orientation = CView::post("orientation", 'enum list|portrait|landscape default|' . $compte_rendu->_orientation);
$first_time  = CView::post("first_time", 'bool default|0');
$user_id     = CView::post("user_id", 'ref class|CMediusers default|' . CAppUI::$user->_id);
$page_width  = CView::post("page_width", 'float min|1 default|' . $compte_rendu->page_width);
$page_height = CView::post("page_height", 'float min|1 default|' . $compte_rendu->page_height);
$height      = CView::post("height", 'float min|0 default|' . $compte_rendu->height);
$margins     = CView::post(
  "margins",
  array(
    'str',
    'default' => array (
      $compte_rendu->margin_top,
      $compte_rendu->margin_right,
      $compte_rendu->margin_bottom,
      $compte_rendu->margin_left
    )
  )
);

CView::checkin();

if ($ids_corres) {
  $ids = explode("-", $ids_corres);
  $_GET["nbDoc"] = array();
  foreach ($ids as $doc_id) {
    if ($doc_id) {
      $_GET["nbDoc"][$doc_id] = 1;
    }
  }
  CAppUI::requireModuleFile("dPcompteRendu", "print_docs");
  CApp::rip();
}

// Initialisation d'un fichier de verrou pour éviter les générations multiples (vérification toutes les secondes)
$mutex = new CMbMutex($compte_rendu->_guid);
$mutex->acquire(CMbMutex::DEFAULT_TIMEOUT, 1000000);

$file = new CFile();
if ($compte_rendu->_id) {
  $compte_rendu->loadFile();
  $file = $compte_rendu->_ref_file;
}

// S'il n'y a pas de pdf ou qu'il est vide, on considère qu'il n'est pas généré
// pour le mode document
if ($mode != "modele" && !$compte_rendu->valide && (((!$file || !$file->_id) && $first_time == 1 && !$compte_rendu->object_id)
    || ($file && $file->_id && $first_time == 1 && (!is_file($file->_file_path) || file_get_contents($file->_file_path) == "")))
) {
  CAppUI::stepAjax(CAppUI::tr("CCompteRendu-no-pdf-generated"));
  $mutex->release();
  return;
}
elseif ($file && $file->_id && $first_time == 1 && is_file($file->_file_path)
    && $compte_rendu->object_id && $mode == "doc" && file_get_contents($file->_file_path) != ''
) {
  // Rien
}
else {
  if ($mode == "modele") {
    switch ($type) {
      case "header":
      case "footer":
        $content = $compte_rendu->loadHTMLcontent(
          $content,
          $mode,
          $margins,
          CCompteRendu::$fonts[$compte_rendu->font],
          $compte_rendu->size,
          true,
          $type, "", $height,
          "",
          ""
        );
        break;
      case "body":
      case "preface":
      case "ending":
        $header  = ""; $sizeheader = 0;
        $footer  = ""; $sizefooter = 0;
        $preface = "";
        $ending  = "";
        if ($header_id) {
          $component = new CCompteRendu;
          $component->load($header_id);
          $component->loadContent();
          $header = $component->_source;
          $sizeheader = $component->height;
        }
        if ($preface_id) {
          $component = new CCompteRendu;
          $component->load($preface_id);
          $component->loadContent();
          $preface = $component->_source;
        }
        if ($ending_id) {
          $component = new CCompteRendu;
          $component->load($ending_id);
          $component->loadContent();
          $ending = $component->_source;
        }
        if ($footer_id) {
          $component = new CCompteRendu;
          $component->load($footer_id);
          $component->loadContent();
          $footer = $component->_source;
          $sizefooter = $component->height;
        }
        
        $content = $compte_rendu->loadHTMLcontent(
          $content,
          $mode,
          $margins,
          CCompteRendu::$fonts[$compte_rendu->font],
          $compte_rendu->size,
          true,
          $type,
          $header,
          $sizeheader,
          $footer,
          $sizefooter,
          $preface,
          $ending
        );
        break;
      default:
    }
  }
  else {
    $content = $compte_rendu->loadHTMLcontent(
      $content,
      $mode,
      $margins,
      CCompteRendu::$fonts[$compte_rendu->font],
      $compte_rendu->size
    );
  }
  
  // Traitement du format de la page
  if ($page_format == "") {
    $page_width  = round((72 / 2.54) * $page_width, 2);
    $page_height = round((72 / 2.54) * $page_height, 2);
    $page_format = array(0, 0, $page_width, $page_height);
  }

  
  // Création du CFile si inexistant
  if (!$file->_id) {
    $file = new CFile();
    $file->setObject($compte_rendu);
    $file->file_type  = "application/pdf";
    $file->author_id = $user_id;
    $file->fillFields();
    $file->updateFormFields();
    $file->forceDir();
  }
  
  if ($file->_id && !file_exists($file->_file_path)) {
    $file->forceDir();
  }
  
  $file->file_name  = $compte_rendu->nom . ".pdf";
  
  $c1 = preg_replace("!\s!", '', $save_content);
  $c2 = preg_replace("!\s!", '', $compte_rendu->_source);
  
  // Si la source envoyée et celle présente en base sont différentes, on regénère le PDF
  // Suppression des espaces, tabulations, retours chariots et sauts de lignes pour effectuer le md5
  if ($compte_rendu->valide || md5($c1) != md5($c2) || !$file->_id
      || !file_exists($file->_file_path) || file_get_contents($file->_file_path) == ""
  ) {
    $htmltopdf = new CHtmlToPDF($compte_rendu->factory);
    $content = CCompteRendu::restoreId($content);
    $htmltopdf->generatePDF($content, 0, $compte_rendu, $file);
  }
  
  // Il peut y avoir plusieurs cfiles pour un même compte-rendu, à cause 
  // de n requêtes simultanées pour la génération du pdf.
  // On supprime donc les autres cfiles.
  $compte_rendu->loadRefsFiles();
  $files = $compte_rendu->_ref_files;
  
  if ($file->_id) {
    unset($files[$file->_id]);
  }
  
  foreach ($files as $_file) {
    $_file->delete();
  }
  
  $file->store();
}

// Mise à jour de la date d'impression
if ($update_date_print) {
  $compte_rendu->date_print = "now";
  if ($msg = $compte_rendu->store()) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
  }
}
if ($compte_rendu->valide) {
    $statut                  = new CStatutCompteRendu();
    $statut->datetime        = CMbDT::dateTime();
    $statut->compte_rendu_id = $compte_rendu_id;
    $statut->user_id         = CMediusers::get()->_id;
    $statut->statut          = "envoye";
    $statut->store();
}
// Ajout de l'autoprint pour wkhtmltopdf (Cas où le pdf est déjà généré)
if ($compte_rendu->factory == "CWkHtmlToPDFConverter") {
  $content = file_get_contents($file->_file_path);

  if (!preg_match("#".CWkHtmlToPDFConverter::$from_autoprint_remove."#", $content)) {
    $content = CWkHtmlToPDFConverter::addAutoPrint($content);
    $file->setContent($content);
    //$file->doc_size = strlen($content);
    $file->store();
  }
}

$mutex->release();

if ($stream) {
  $file->streamFile();
  CApp::rip();
}

if ($write_page) {
  $file->loadNbPages();
  $smarty = new CSmartyDP;
  $smarty->assign("file_id", $file->_id);
  $smarty->assign("_nb_pages", $file->_nb_pages);
  $smarty->assign("print", $print);
  $smarty->assign("category_id", $compte_rendu->file_category_id);
  $smarty->display("inc_thumbnail");
}
