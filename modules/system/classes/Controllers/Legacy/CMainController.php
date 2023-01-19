<?php

/**
 * @package Mediboard\system
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Controllers\Legacy;

use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CLegacyController;
use Ox\Core\CMbDT;
use Ox\Core\CValue;
use Ox\Core\CViewHistory;
use Ox\Core\Kernel\Routing\RouterBridge;
use Ox\Core\Module\CModule;
use Ox\Core\ResourceLoaders\CCSSLoader;
use Ox\Core\ResourceLoaders\CFaviconLoader;
use Ox\Core\ResourceLoaders\CJSLoader;
use Ox\Mediboard\Admin\CKerberosLdapIdentifier;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\AppBar;
use Ox\Mediboard\System\CMessage;
use Ox\Mediboard\System\CMessageAcquittement;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

class CMainController extends CLegacyController
{
    public function header(string $mod_name, string $action, ?string $tab): void
    {
        $uistyle = CAppUI::MEDIBOARD_EXT_THEME;

        //current Group
        $current_group = CGroups::loadCurrent();
        $current_user  = CMediusers::get();

        // For admin user which is not linked to a CMediusers object
        if (!$current_user || !$current_user->_id) {
            $user                                 = CUser::get();
            $current_user                         = new CMediusers();
            $current_user->_id                    = $user->_id;
            $current_user->_user_username         = $user->user_username;
            $current_user->_allow_change_password = $user->allow_change_password;
        }

        $module = CModule::getActive($mod_name);
        if ($module === null) {
            $module = new CModule();
            $module->mod_name = $mod_name;
        }

        $appbar = new AppBar(RouterBridge::getInstance(), $module, $current_group, $current_user, CApp::getVersion(), $tab);
        $appbar->build();

        CJSLoader::$additionnal_files = array_merge(CJSLoader::$additionnal_files, $appbar->getScriptsJs());

        $tpl_vars = [
            // common.tpl vars
            'localeInfo'         => CAppUI::$locale_info,
            'mediboardShortIcon' => CFaviconLoader::loadFile("style/$uistyle/images/icons/favicon.ico"),
            'mediboardStyle'     => CCSSLoader::loadAllFiles(),
            'mediboardScript'    => CJSLoader::loadAllFiles(),
            'cp_group'           => $current_group->cp,
            "allInOne"           => CValue::get("_aio"),
            'current_group'      => $current_group->text,

            // obsolete_module.tpl vars
            'obsolete_module'    => CModule::getObsolete($mod_name, $action),

            // message.tpl vars
            'messages'           =>
                (new CMessage())->loadPublications("present", $current_user->_id, $mod_name, $current_group->_id),
            'acquittal'          => new CMessageAcquittement(),

            // offline_mode.tpl vars
            // <N/A>

            // headerV2.tpl vars
            'offline'            => false,
            'errorMessage'       => CAppUI::getMsg(),
            'showInfoSystem'     => CAppUI::pref("INFOSYSTEM"),

            // headerV2.tpl + common.tpl
            'dialog'             => CAppUI::$dialog,

            // Appbar view component
            'appbar'             => $appbar,
        ];

        $this->renderSmarty('header', $tpl_vars, "style/{$uistyle}");
    }

    public function moduleInactive()
    {
        $this->renderSmarty("module_inactive", [], "modules/system");
    }

    public function viewInfo($props, $params)
    {
        $this->renderSmarty(
            "view_info",
            [
                "props"  => $props,
                "params" => $params,
            ],
            "modules/system"
        );
    }

    public function footer()
    {
        global $m, $action;
        $user = CAppUI::$user;
        if ($infosystem = CAppUI::pref("INFOSYSTEM")) {
            $latest_cache_key = "$user->_guid-latest_cache";
            $latest_cache     = [
                "meta"   => [
                    "module" => $m,
                    "action" => $action,
                    "user"   => $user->_view,
                ],
                "totals" => Cache::getTotals(),
                "hits"   => Cache::getHits(),
            ];

            $cache = Cache::getCache(Cache::OUTER)->withCompressor();
            $cache->set($latest_cache_key, $latest_cache);
        }

        $tpl_vars = [
            "offline"            => false,
            "infosystem"         => $infosystem,
            "performance"        => CApp::$performance,
            "show_performance"   => CAppUI::pref("show_performance"),
            "errorMessage"       => CAppUI::getMsg(),
            "navigatory_history" => CViewHistory::getHistory(),
            "multi_tab_msg_read" => CAppUI::isMultiTabMessageRead(),
        ];

        $this->renderSmarty('footer', $tpl_vars, "style/" . CAppUI::MEDIBOARD_EXT_THEME);
    }

    public function login()
    {
        $style      = CAppUI::MEDIBOARD_EXT_THEME;
        $redirect   = CValue::get("logout") ? "" : CValue::read($_SERVER, "QUERY_STRING");
        $psc_button = CAppUI::isLoginPSCEnabled();

        $tpl_vars = [
            "localeInfo"         => CAppUI::$locale_info,
            "mediboardShortIcon" => CFaviconLoader::loadFile("style/{$style}/images/icons/favicon.ico"),
            "mediboardStyle"     => CCSSLoader::loadAllFiles(),
            "mediboardScript"    => CJSLoader::loadAllFiles(false),
            "errorMessage"       => CAppUI::getMsg(),
            "time"               => time(),
            "redirect"           => $redirect,
            "uistyle"            => $style,
            "nodebug"            => true,
            "offline"            => false,
            "allInOne"           => CValue::get("_aio"),
            "applicationVersion" => CApp::getVersion()->toArray(),
            "kerberos_button"    => CKerberosLdapIdentifier::isLoginButtonEnabled(),
            "psc_button"         => $psc_button,
        ];

        $this->renderSmarty('login', $tpl_vars, "style/{$style}");
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws \Exception
     */
    public function offline(string $message): Response
    {
        $root_dir = CAppUI::conf("root_dir");;
        $path      = "./images/pictures";
        $bg_custom = "./images/pictures/bg_custom.jpg";

        $vars = [
            "bg_custom"   => $bg_custom,
            "bg"          => is_file($bg_custom),
            "src_logo"    => (file_exists(
                "$root_dir/$path/logo_custom.png"
            ) ? "$path/logo_custom.png" : "$path/logo.png"),
            "message"     => $message,
            "application" => CAppUI::conf("product_name"),
        ];

        $loader = new FilesystemLoader($root_dir . '/templates/');
        $twig   = new Environment($loader);
        $body   = $twig->render("offline.html.twig",$vars);

        $response = new Response($body);
        $response->headers->add(
            [
                "Retry-After"  => 300,
                "Content-Type" => "text/html; charset=iso-8859-1",
            ]
        );

        return $response;
    }

    public function ajaxErrors()
    {
        $this->renderSmarty(
            'ajax_errors',
            [
                "performance"      => CApp::$performance,
                "show_performance" => CAppUI::pref("show_performance"),
                "requestID"        => CValue::get("__requestID"),
            ],
            'modules/system'
        );
    }

    public function unlocalized()
    {
        $this->renderSmarty("inc_unlocalized_strings", [], 'modules/system');
    }

    public function tabboxOpen($tabs, $tab)
    {
        $this->renderSmarty(
            'tabbox',
            [
                'tabs'         => $tabs,
                'tab'          => $tab,
                'statics_tabs' => CModule::TABS,
            ],
            'style/' . CAppUI::MEDIBOARD_EXT_THEME
        );
    }

    public function tabboxClose()
    {
        echo '</div></div>';
    }
}
