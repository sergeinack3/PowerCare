<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\ResourceLoaders\CCSSLoader;
use Ox\Core\ResourceLoaders\CFaviconLoader;
use Ox\Core\ResourceLoaders\CJSLoader;
use Ox\Mediboard\Repas\CPlat;

CCanDo::checkRead();

global $uistyle, $messages, $version;

CApp::setTimeLimit(90);

$indexFile  = CValue::post("indexFile", 0);
$style      = CValue::post("style", 0);
$image      = CValue::post("image", 0);
$javascript = CValue::post("javascript", 0);
$lib        = CValue::post("lib", 0);
$typeArch   = CValue::post("typeArch", "zip");

// Création du fichier Zip
if (file_exists("tmp/mediboard_repas.zip")) {
    unlink("tmp/mediboard_repas.zip");
}
if (file_exists("tmp/mediboard_repas.tar.gz")) {
    unlink("tmp/mediboard_repas.tar.gz");
}

if ($typeArch == "zip") {
    $zipFile = new ZipArchive();
    $zipFile->open("tmp/mediboard_repas.zip", ZIPARCHIVE::CREATE);
} elseif ($typeArch == "tar") {
    $zipFile = new Archive_Tar("tmp/mediboard_repas.tar.gz", true);
} else {
    return;
}

if ($indexFile) {
    // Création du fichier index.html
    $plats = new CPlat();

    $smarty = new CSmartyDP();
    $smarty->assign("plats", $plats);

    $smartyStyle = new CSmartyDP();

    $smartyStyle->assign("offline", true);
    $smartyStyle->assign("localeInfo", $locale_info);
    $smartyStyle->assign("mediboardShortIcon", CFaviconLoader::loadFile("style/$uistyle/images/icons/favicon.ico"));
    $smartyStyle->assign("mediboardStyle", CCSSLoader::loadFiles());
    $smartyStyle->assign("mediboardScript", CJSLoader::loadFiles());
    $smartyStyle->assign("messages", $messages);
    $smartyStyle->assign("infosystem", CAppUI::pref("INFOSYSTEM"));
    $smartyStyle->assign("errorMessage", CAppUI::getMsg());
    $smartyStyle->assign("uistyle", $uistyle);

    ob_start();
    $smartyStyle->display("header.tpl");
    $smarty->display("repas_offline.tpl");
    $smartyStyle->display("footer.tpl");
    $indexFile = ob_get_contents();
    ob_end_clean();
    file_put_contents("tmp/index.html", $indexFile);

    if ($typeArch == "zip") {
        $zipFile->addFile("tmp/index.html", "index.html");
    } elseif ($typeArch == "tar") {
        $zipFile->addModify("tmp/index.html", null, "tmp/");
    }
}

function delSmartyDir($action, $fileProps)
{
    if (preg_match("/templates/", $fileProps["filename"])
        || preg_match("/templates_c/", $fileProps["filename"])) {
        return false;
    } else {
        return true;
    }
}

function addFiles($src, &$zipFile, $typeArch)
{
    if ($typeArch == "tar") {
        return $zipFile->add("$src/", ["callback_pre_add" => "delSmartyDir"]);
    }
    $values = [".", "..", "templates", "templates_c"];
    $dir    = opendir($src);
    while (false !== ($file = readdir($dir))) {
        if ((!in_array($file, $values))) {
            if (is_dir("$src/$file")) {
                addFiles("$src/$file", $zipFile, $typeArch);
            } else {
                $zipFile->addFile("$src/$file", "$src/$file");
            }
        }
    }
}

if ($style) {
    addFiles("style", $zipFile, $typeArch);
}

if ($image) {
    addFiles("images", $zipFile, $typeArch);
}

if ($lib) {
    addFiles("lib/dojo", $zipFile, $typeArch);
    addFiles("lib/datepicker", $zipFile, $typeArch);
    addFiles("lib/scriptaculous", $zipFile, $typeArch);
}

if ($javascript) {
    addFiles("includes/javascript", $zipFile, $typeArch);
    addFiles("modules/dPrepas/javascript", $zipFile, $typeArch);
}

if ($typeArch == "tar") {
    CApp::log("Contenu de l'archive", $zipFile->listContent());
} else {
    for ($i = 0; $i < $zipFile->numFiles; $i++) {
        $stat = $zipFile->statIndex($i);
        CApp::log(basename($stat['name']));
    }
}

if ($typeArch == "zip") {
    $zipFile->close();
}

CApp::rip();
