<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Controllers;

use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CController;
use Ox\Core\Api\Utility\FilterableTrait;
use Ox\Mediboard\System\CTranslationOverwrite;

/**
 * Description
 */
class LocalesController extends CController
{
    use FilterableTrait;

    private const NO_MODULE_NAME = 'core';

    /**
     * @api
     */
    public function listLocales(string $language, string $mod_name, RequestApi $request_api)
    {
        $locales = $this->loadLocalesFiles($language, $mod_name);

        $overwrite = new CTranslationOverwrite();
        if ($overwrite->isInstalled()) {
            $locales = $overwrite->transformLocales($locales, $language);
        }

        $locales = $this->applyFilter($request_api, $locales);

        $locales = $this->sanitize($locales);

        $ressource = new Item($locales);
        $ressource->setType('locales');

        return $this->renderApiResponse($ressource);
    }

    private function loadLocalesFiles(string $language, string $mod_name): array
    {
        $root_dir = $this->getRootDir();

        if ($mod_name !== self::NO_MODULE_NAME) {
            $mod_name = $this->getActiveModule($mod_name);
        }

        $files = [];
        if ($mod_name === self::NO_MODULE_NAME) {
            $files[] = $root_dir . '/locales/' . $language . '/common.php';
        } else {
            $base_path = $root_dir . '/modules/' . $mod_name . '/locales/';

            $files[] = $base_path . $language . '.php';
            $files[] = $base_path . $language . '.overload.php';
        }

        $locales = [];
        foreach ($files as $_file_path) {
            if (file_exists($_file_path)) {
                include $_file_path;
            }
        }

        return $locales;
    }

    private function sanitize(array $locales): array
    {
        foreach ($locales as $_key => &$_value) {
            $_value = trim(nl2br($_value));
        }

        return $locales;
    }
}
