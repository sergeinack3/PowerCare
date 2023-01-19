<?php

/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Medimail\CMedimailAccount;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Mssante\CMSSanteUserAccount;

/**
 * Instanciation de CKEditor
 */
$templateManager = unserialize(gzuncompress($_SESSION["dPcompteRendu"]["templateManager"] ?? ''));

CView::checkin();

header("Content-Type: text/javascript");

$keys = array_map("Ox\Core\CMbString::removeDiacritics", array_keys($templateManager->sections));
array_multisort($keys, SORT_FLAG_CASE | SORT_NATURAL, $templateManager->sections);

foreach ($templateManager->sections as $key => $_section) {
    $keys = array_map("Ox\Core\CMbString::removeDiacritics", array_keys($templateManager->sections[$key]));

    // Pour les sous sections, leur nom est après un tiret : on prend donc seulement la deuxième partie
    $keys = array_map(
        function ($elt) {
            if (strpos($elt, "-") === false) {
                return $elt;
            }
            $explode = explode("-", $elt);

            return $explode[1];
        },
        $keys
    );

    array_multisort($keys, SORT_FLAG_CASE | SORT_NATURAL, $templateManager->sections[$key]);

    if (isset($templateManager->sections[$key]["field"])) {
        continue;
    }

    foreach ($templateManager->sections[$key] as $_key => $_sub_section) {
        $keys = array_map("Ox\Core\CMbString::removeDiacritics", array_keys($templateManager->sections[$key][$_key]));
        array_multisort($keys, SORT_FLAG_CASE | SORT_NATURAL, $templateManager->sections[$key][$_key]);
    }
}

$sections = [
    'TAMM' => $templateManager->sections,
];

foreach ($sections['TAMM'] as $_section => $_fields) {
    preg_match('/^(SIH.*) -/', $_section, $results);

    if (isset($results[1])) {
        $section_name = $results[1];
        if (!isset($sections[$section_name])) {
            $sections[$section_name] = [];
        }

        $sections[$section_name][$_section] = $_fields;
        unset($sections['TAMM'][$_section]);
    }
}

$templateManager->sections = $sections;

$user = CMediusers::get();

$use_apicrypt = false;
if (CModule::getActive("apicrypt")) {
    $use_apicrypt = !$user->isPraticien() || $user->mail_apicrypt;
}

$use_mssante = false;
if (CModule::getActive('mssante')) {
    $use_mssante = !$user->isPraticien() || CMSSanteUserAccount::isUserHasAccount($user);
}

$use_medimail = false;
if (CModule::getActive('medimail')) {
    $use_medimail = !$user->isPraticien() || CMedimailAccount::isUserHasAccount($user);
}

// Création du template
$smarty = new CSmartyDP("modules/dPcompteRendu");

$smarty->assign("templateManager", $templateManager);
$smarty->assign("nodebug", true);
$smarty->assign("use_apicrypt", $use_apicrypt);
$smarty->assign('use_mssante', $use_mssante);
$smarty->assign('use_medimail', $use_medimail);

$smarty->display("mb_fckeditor");
