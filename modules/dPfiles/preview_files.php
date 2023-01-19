<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Récupération des variables
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CClassMap;
use Ox\Core\CLogger;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CMbPath;
use Ox\Core\CMbString;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Import\Ami\CAMIDocument;
use Ox\Import\Medistory\Document\CMedistoryImprime;
use Ox\Import\Osoft\COsoftDossier;
use Ox\Import\Osoft\COsoftHistorique;
use Ox\Import\Resurgences\CResUrgencesConstantes;
use Ox\Import\Surgica\CSurgicaDoc;
use Ox\Interop\Cda\CCdaTools;
use Ox\Interop\Cda\Levels\Level3\CCDALevel3;
use Ox\Interop\Hprimsante\CHPrimSante;
use Ox\Interop\Hprimsante\CHPrimSanteMessage;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Files\CThumbnail;
use Ox\Mediboard\Mssante\CMSSanteCDADocument;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceSMTP;
use RtfHtmlPhp\Document;
use RtfHtmlPhp\Html\HtmlFormatter;

CCanDo::checkRead();

$objectClass    = CView::get("objectClass", "str");
$objectId       = CView::get("objectId", "ref class|$objectClass");
$elementClass   = CView::get("elementClass", "str");
$elementId      = CView::get("elementId", "ref class|$elementClass");
$popup          = CView::get("popup", "bool default|0");
$nonavig        = CView::get("nonavig", "bool");
$sfn            = CView::get("sfn", "num default|0");
$typeVue        = CView::get("typeVue", "num", true);
$order_docitems = CView::get("order_docitems", "enum list|nom|date default|nom", true);
$view_light     = CView::get("view_light", "bool default|0");
$no_buttons     = CView::get("no_buttons", "bool default|0");
$embed          = CView::get("embed", "str default|null");
$inline_header  = CView::get("inline_header", "bool default|0");

CView::checkin();

if (!$objectClass || !$objectId || !$elementClass || !$elementId) {
    CAppUI::stepAjax("CDocumentItem-_missing_arguments_preview");
}

// Déclaration de variables
$file_id          = null;
$object           = null;
$fileSel          = null;
$keyFileSel       = null;
$page_prev        = null;
$page_next        = null;
$pageEnCours      = null;
$includeInfosFile = null;
$catFileSel       = null;
$arrNumPages      = [];   // navigation par pages (PDF)
$isConverted      = false;
$display_as_is    = false;

$pdf_active = CAppUI::pref("pdf_and_thumbs") == 1;

// Chargement de l'objet
/** @var CMbObject $object */
$object = new $objectClass();

if (!$object->load($objectId)) {
    CAppUI::stepAjax("CDocumentItem-_object_deleted", UI_MSG_WARNING);
    CApp::rip();
}

if ($object->_class == "CFolder" && $object->_id) {
    $object->loadRefFolderAckForUser();
}

// Recherche du fichier/document demandé et Vérification droit Read
if ($elementClass == "CFile") {
    $fileSel = CFile::findOrNew($elementId);
    $nameFile = $order_docitems == "nom" ? "file_name" : "file_date";
}

if ($elementClass == "CCompteRendu") {
    $fileSel = CCompteRendu::findOrNew($elementId);
    $nameFile = $order_docitems == "nom" ? "nom" : "creation_date";
}

$keyTable   = $fileSel->_spec->key;
$keyFileSel = $fileSel->$nameFile;
$keyFileSel .= "-" . $elementClass . "-";
$keyFileSel .= $fileSel->$keyTable;

if ($pdf_active && get_class($fileSel) === CCompteRendu::class) {
    $fileSel               = new CFile();
    $fileSel->object_class = "CCompteRendu";
    $fileSel->object_id    = $elementId;
    $fileSel->loadMatchingObject();
    $file_id = $fileSel->_id;
}

if (!$fileSel->_id) {
    CAppUI::stepAjax("CDocumentItem-_not_available", UI_MSG_WARNING);
    CApp::rip();
}

// Récupération de la catégorie
$catFileSel = $fileSel->loadRefCategory();

$show_editor = true;
$file_list   = null;
$zip_file    = null;
$root_zip    = null;

switch ($fileSel->_class) {
    // Gestion des pages pour les Fichiers PDF et fichiers TXT
    case "CFile":
        $fileSel->canDo();

        $pdf_convertible = $fileSel->isPDFconvertible();

        if ($fileSel->file_type == "application/zip") {
            $f = CThumbnail::extractFileForPreview($fileSel);

            if (!is_array($f)) {
                $zip_file           = $fileSel->_file_path = $f;
                $fileSel->file_type = CMbPath::guessMimeType($fileSel->_file_path);
                $root_zip           = dirname($zip_file);
            } else {
                foreach ($f as $_file_path) {
                    if (!$root_zip) {
                        $root_zip = dirname($_file_path);
                    }

                    $file_list[] = basename($_file_path);
                    if (is_file($_file_path)) {
                        unlink($_file_path);
                    } elseif (is_dir($_file_path)) {
                        CMbPath::emptyDir($_file_path);
                    }
                }

                break;
            }
        }

        if (file_exists($fileSel->_file_path)) {
            $raw_content = $fileSel->getBinaryContent();

            switch ($fileSel->file_type) {
                case "application/x-hprim-sante":
                    if (CModule::getActive('hprimsante')) {
                        $message = new CHPrimSanteMessage();
                        $message->parse($raw_content);
                        $includeInfosFile = $message->toHTML();
                        $display_as_is    = true;
                        $show_editor      = false;
                    } else {
                        $includeInfosFile = CMbString::htmlSpecialChars($raw_content);
                    }
                    break;
                case 'application/x-hprim-med':
                    if (CModule::getActive('hprimsante')) {
                        $includeInfosFile = CHPrimSante::formatHPRIMBiologie($raw_content);
                        $display_as_is    = true;
                        $show_editor      = false;
                    } else {
                        $includeInfosFile = CMbString::htmlSpecialChars($raw_content);
                    }
                    break;
                case 'application/cda.exam-report':
                    if (CModule::getActive('mssante')) {
                        $includeInfosFile = CMSSanteCDADocument::display($raw_content);
                        $display_as_is    = true;
                        $show_editor      = false;
                    } else {
                        $includeInfosFile = CMbString::htmlSpecialChars($raw_content);
                    }
                    break;

                case "text/osoft":
                    if (class_exists(COsoftHistorique::class)) {
                        $osoft_histo      = new COsoftHistorique(false);
                        $includeInfosFile = $osoft_histo->toHTML($raw_content);
                        $show_editor      = false;
                    }
                    break;

                case "application/osoft":
                    if (class_exists(COsoftDossier::class)) {
                        $osoft_dossier    = new COsoftDossier(false);
                        $includeInfosFile = $osoft_dossier->toHTML($raw_content);
                        $show_editor      = false;
                    }
                    break;

                case "text/medistory-form":
                    if (class_exists(CMedistoryImprime::class)) {
                        $includeInfosFile = CMedistoryImprime::toHTML($raw_content);
                        $show_editor      = false;
                        $display_as_is    = true;
                    }
                    break;

                case "application/vnd.surgica.form":
                    if (class_exists(CSurgicaDoc::class)) {
                        $includeInfosFile = CSurgicaDoc::toHTML($raw_content);
                        $show_editor      = false;
                        $display_as_is    = true;
                    }
                    break;

                case "text/ami-patient-text":
                    if (class_exists(CAMIDocument::class)) {
                        $includeInfosFile = CAMIDocument::toHTML($raw_content);
                        $show_editor      = false;
                        $display_as_is    = true;
                    }
                    break;

                case 'text/resurgences-constantes-text':
                    if (class_exists(CResUrgencesConstantes::class)) {
                        $includeInfosFile = CResUrgencesConstantes::toHTML($raw_content);
                        $show_editor      = false;
                        $display_as_is    = true;
                    }
                    break;

                case "application/rtf":
                    if (!$pdf_convertible) {
                        try {
                            // Disable error handler waiting for lib to be updated
                            // Trim to avoid getting space after the } closing the rtf
                            $doc              = @new Document(trim($raw_content));
                            $formatter        = new HtmlFormatter();
                            $includeInfosFile = "<div class='rtf-preview'>" . $formatter->Format($doc) . "</div>";
                        } catch (Exception $e) {
                            $includeInfosFile = "<pre>" . CMbString::htmlSpecialChars($raw_content) . "</pre>";
                        } catch (Error $e) {
                            $includeInfosFile = "<pre>" . CMbString::htmlSpecialChars($raw_content) . "</pre>";
                        }

                        $show_editor   = false;
                        $display_as_is = true;
                    }
                    break;

                case "text/plain":
                    $includeInfosFile = CMbString::htmlSpecialChars($raw_content);
                    break;

                case "text/html":
                    $includeInfosFile = CMbString::purifyHTML($raw_content);
                    $show_editor      = false;
                    $display_as_is    = true;
                    break;

                default:
            }
        } else {
            CApp::log('File not found ' . $fileSel->file_name . ' at ' . $fileSel->_file_path, null, CLogger::LEVEL_WARNING);
        }

        // liste type doc cda level 3 ANS
        $type_docs_available = array_map(
            function (string $cda) {
                /** @var $cda CCDALevel3 */
                return $cda::TYPE_DOC;
            },
            CClassMap::getInstance()->getClassChildren(CCDALevel3::class, false, true)
        );

        // Gestion des CDAr2 => VSM, Lettre de liaison
        if ($fileSel->type_doc_dmp && $fileSel->file_type == 'application/xml') {
            $includeInfosFile = CCdaTools::display($raw_content, $fileSel);
            $display_as_is    = true;
            $show_editor      = false;
        }

        if ($pdf_convertible) {
            $isConverted = true;
            $fileconvert = $fileSel->loadPDFconverted();
            $success     = 1;

            if (!$fileconvert->_id) {
                $success = $fileSel->convertToPDF();
            }
            if ($success == 1) {
                $fileconvert = $fileSel->loadPDFconverted();
                $fileconvert->loadNbPages();
                $fileSel->_nb_pages = $fileconvert->_nb_pages;
            }
        }

        if (!$fileSel->_nb_pages) {
            $fileSel->loadNbPages();
        }

        if ($fileSel->_nb_pages) {
            if ($sfn > $fileSel->_nb_pages || $sfn < 0) {
                $sfn = 0;
            }

            if ($sfn != 0) {
                $page_prev = $sfn - 1;
            }
            if ($sfn < ($fileSel->_nb_pages - 1)) {
                $page_next = $sfn + 1;
            }

            $arrNumPages = range(1, $fileSel->_nb_pages);
        }
        break;
    case "CCompteRendu":
        if ($pdf_active) {
            $fileSel->loadNbPages();

            if ($fileSel->_nb_pages) {
                if ($sfn > $fileSel->_nb_pages || $sfn < 0) {
                    $sfn = 0;
                }
                if ($sfn != 0) {
                    $page_prev = $sfn - 1;
                }
                if ($sfn < ($fileSel->_nb_pages - 1)) {
                    $page_next = $sfn + 1;
                }

                $arrNumPages = range(1, $fileSel->_nb_pages);
            }
        } else {
            $fileSel->loadContent();
            $includeInfosFile = $fileSel->_source;

            // Initialisation de CKEditor
            if ($includeInfosFile) {
                $templateManager            = new CTemplateManager();
                $templateManager->printMode = true;
                $templateManager->initHTMLArea();
            }
        }
        break;
    default:
}

if ($zip_file) {
    unlink($zip_file);
}

if ($root_zip) {
    CMbPath::recursiveRmEmptyDir($root_zip);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("objectClass", $objectClass);
$smarty->assign("objectId", $objectId);
$smarty->assign("elementClass", $elementClass);
$smarty->assign("elementId", $elementId);
$smarty->assign("catFileSel", $catFileSel);
$smarty->assign("fileSel", $fileSel);
$smarty->assign("arrNumPages", $arrNumPages);
$smarty->assign("object", $object);
$smarty->assign("page_prev", $page_prev);
$smarty->assign("page_next", $page_next);
$smarty->assign("sfn", $sfn);
$smarty->assign("includeInfosFile", $includeInfosFile);
$smarty->assign("popup", $popup);
$smarty->assign("file_id", $file_id);
$smarty->assign("isConverted", $isConverted);
$smarty->assign("show_editor", $show_editor);
$smarty->assign("display_as_is", $display_as_is);
$smarty->assign("view_light", $view_light);
$smarty->assign("file_list", $file_list);
$smarty->assign("no_buttons", $no_buttons);
$smarty->assign("embed", $embed);
$smarty->assign("inline_header", $inline_header);

if ($popup == 1) {
    $listCat  = null;
    $fileprev = null;
    $filenext = null;

    if ($object) {
        $affichageFile = CDocumentItem::loadDocItemsByObject($object, $order_docitems, false);

        // Récupération du fichier/doc préc et suivant
        $aAllFilesDocs = [];
        foreach ($affichageFile as $keyCat => $currCat) {
            $aAllFilesDocs = array_merge($aAllFilesDocs, $affichageFile[$keyCat]["items"]);
        }

        // Les fichiers possèdent un nom au format titre.ext-CFile-10023, on récupère l'identifiant à la fin et on trie dans l'ordre croissant.
        uksort($aAllFilesDocs,
            function ($a, $b) {
                $aKey = explode('-', $a);
                $bKey = explode('-', $b);

                return intval($aKey[count($aKey) - 1]) > intval($bKey[count($bKey) - 1]);
            }
        );

        // Pas de précédent ou suivant pour un fichier annulé
        if (!$fileSel->annule) {
            $aFilePrevNext = CMbArray::getPrevNextKeys($aAllFilesDocs, $keyFileSel);
            foreach ($aFilePrevNext as $key => $value) {
                if ($value) {
                    $aFile           =& $aAllFilesDocs[$aFilePrevNext[$key]];
                    $keyFile         = $aFile->_spec->key;
                    ${"file" . $key} = [
                        "elementId"    => $aFile->$keyFile,
                        "elementClass" => $aFile->_class,
                    ];
                }
            }
        }

        $exchange_source = CExchangeSource::get("mediuser-" . CAppUI::$user->_id, CSourceSMTP::TYPE);
        $smarty->assign("exchange_source", $exchange_source);
        $smarty->assign("nonavig", $nonavig);
        $smarty->assign("filePrev", $fileprev);
        $smarty->assign("fileNext", $filenext);
        $smarty->display("inc_preview_file_popup");
    }
} else {
    $smarty->display("inc_preview_file");
}
