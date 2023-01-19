<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// post values
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CValue;
use Ox\Core\Module\CModule;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CHtmlToPDF;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Mediusers\CMediusers;

$dispo   = CValue::post("tab_disposition");
$user_id = CValue::post("user_id");
$cat_id  = CValue::post("category_id");
$print   = CValue::post("print", 0);

// files
$file_array = CValue::post("file");
$data = $file_array[$dispo];

// context
$object_guid = CValue::post("context_guid");
$context = CMbObject::loadFromGuid($object_guid);


// get data uri
foreach ($data as $_key => &$_data ) {
  $file = new CFile();
  $file->load($_data["file_id"]);
  $_data["file_uri"] = $file->getThumbnailDataURI(["width" => 1024, "quality" => "high"]);
}

//user
$user = CMediusers::get($user_id);

// file
$file = new CFile();
$file->setObject($context);
$file->file_name = CAppUI::tr("CFile-create-mozaic")." de ".CAppUI::tr($context->_class)." du ".CMbDT::dateToLocale(CMbDT::date()).".pdf";
$file->file_type  = "application/pdf";
$file->file_category_id = $cat_id;
if (CModule::getActive("oxCabinet") && $context->_class === "CEvenementPatient") {
    $file->file_category_id = CAppUI::gconf("oxCabinet CEvenementPatient categorie_{$context->type}_default");
}
$file->author_id = CMediusers::get()->_id;
$file->fillFields();
$file->updateFormFields();
$file->forceDir();

$cr = new CCompteRendu();
$cr->_page_format = "A4";
$cr->_orientation = "portrait";

// use template for header and footer
$template_header = new CTemplateManager();
$context->fillTemplate($template_header);
$header = CCompteRendu::getSpecialModel($user, "CPatient", "[ENTETE MOZAIC]");
if ($header->_id) {
  $header->loadContent();
  $template_header->renderDocument($header->_source);
}
else {
  $template_header->document = "<p style=\"text-align:center;\">".$context->_view."</p>";

}

$template_footer = new CTemplateManager();
$context->fillTemplate($template_footer);
$footer = CCompteRendu::getSpecialModel($user, "CPatient", "[PIED DE PAGE MOZAIC]");
if ($footer->_id) {
  $footer->loadContent();
  $template_footer->renderDocument($footer->_source);
}
else {
  $template_footer->document = "<p style=\"text-align:center;\">".CMbDT::dateToLocale(CMbDT::dateTime())."</p>";
}

// main body
$_dispo = explode("_", $dispo);
$_dispos = explode("x", $_dispo[1]);
$cols  = $_dispos[0];
$lines = $_dispos[1];

$content = "<html>
<head>
<style>
  body {border:0;margin:0; position:relative;}
  #header, #footer {position:absolute; top: 0; width:100%; overflow: hidden;}
  #footer {top:25cm;}
  #body {height:23cm; width:100%; border-collapse: collapse; table-layout: fixed; position: absolute; top: 2cm;}
  #images {position:relative; width: 100%; height:100%;}
  #body div.col {
    position: absolute;
    overflow: hidden;
    line-height: 100%;
    max-width: 100%;
    text-align: center;
    vertical-align: middle!important;
  }

  #images img {
  box-shadow: 0 0 5px #b8b8b8;
  }

  #body p {
    position:absolute;
    bottom:0;
    left:0;
    width:100%;
    text-align: center;
  }

  #body p span {
    background-color:white;
    border:solid 1px #6e6e6e;
    padding:3px;
  }

  .droppable {
    padding:4px;
  }

  #body img {max-width:100%; max-height: 100%;}
  .nb_line_1 {height:100%;}
  .nb_line_2 {height:50%;}
  .nb_line_3 {height:33%;}

  .nb_col_1 {width:100%;}
  .nb_col_2 {width:50%;}
  .nb_col_3 {width:33%;}

  .line_1 {top:0;}
  .nb_line_2.line_2 {top:50%;}
  .nb_line_3.line_2 {top:33%;}
  .nb_line_3.line_3 {top:66%;}

  .col_1 {left:0;}
  .nb_col_2.col_2 {left:50%;}
  .nb_col_3.col_2 {left:33%;}
  .nb_col_3.col_3 {left:66%;}

</style>
</head>
<body>
<div id=\"header\">$template_header->document</div>
<div id=\"body\"><div id=\"images\">";
for ($a=1; $a<=$lines; $a++) {
  for ($b=1; $b<=$cols; $b++) {
    $content.= "
    <div class=\"col nb_col_$cols nb_line_$lines col_$b line_$a\">";
    $content.= $data[$_dispo[1]."_".$a."x".$b]["file_id"] ? "<img src=\"".$data[$_dispo[1]."_".$a."x".$b]["file_uri"]."\" alt=\"\"/>" : null;
    $content.= $data[$_dispo[1]."_".$a."x".$b]["file_id"] ? "<p><span>".$data[$_dispo[1]."_".$a."x".$b]["name"]."</span></p>" : null;
    $content.= "
    </div>";
  }
}
$content.= "
</div>
</div>
<div id=\"footer\">$template_footer->document</div>
</body>
</html>";
ob_clean();
$htmltopdf = new CHtmlToPDF();

$content = $htmltopdf->generatePDF($content, $print, $cr, new CFile());

$file->setContent($content);
$file->store();

CApp::rip();
