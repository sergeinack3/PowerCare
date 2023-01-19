<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Controllers;

use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CAppUI;
use Ox\Core\CController;
use Ox\Core\CMbException;
use Ox\Core\Kernel\Exception\HttpException;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CPinnedTab;
use Ox\Mediboard\System\CTab;
use Ox\Mediboard\System\CTabHit;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller to handle tabs
 */
class TabController extends CController
{
    public const LINK_TAB_URL = 'tab_url';
    public const TAB_RESOURCE_TYPE = 'tab';

    /**
     * @api
     */
    public function showPinnedTabs(string $mod_name): Response
    {
        $module = CModule::getActive($mod_name);
        if ($module === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'The module ' . $mod_name . ' is not active.');
        }

        if (!$module->getPerm(PERM_READ)) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Access denied');
        }

        return $this->renderApiResponse(new Collection($module->getPinnedTabs()));
    }

    /**
     * @api
     */
    public function setPinnedTab(string $mod_name, RequestApi $request_api): Response
    {
        $pins = $request_api->getModelObjectCollection(CPinnedTab::class, [], ['_tab_name']);

        $module = CModule::getActive($mod_name);
        if ($module === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'The module ' . $mod_name . ' is not active.');
        }

        if (!$module->getPerm(PERM_READ)) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Access denied');
        }

        $current_user = CMediusers::get();

        try {
            /** @var CPinnedTab $pin */
            foreach ($pins as $pin) {
                $pin->_mod_name = $mod_name;
                $pin->createPin($current_user);
            }

            CPinnedTab::removePinnedTabs($mod_name, $current_user);
        } catch (CMbException $e) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }

        return $this->renderApiResponse($this->storeCollection($pins), 201);
    }

    /**
     * @throws ApiException
     * @api
     *
     */
    public function listModuleTabs(string $mod_name, RequestApi $request_api): Response
    {
        $module = CModule::getActive($mod_name);
        if ($module === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'The module ' . $mod_name . ' is not installed.');
        }

        $tabs = $module->getTabs();

        $collection = Collection::createFromRequest($request_api, $tabs);
        /** @var Item $item */
        foreach ($collection as $item) {
            /** @var CTab $tab */
            $tab = $item->getDatas();
            $item->addLinks([self::LINK_TAB_URL => $tab->getUrl()]);
            $item->setType(self::TAB_RESOURCE_TYPE);
        }
        
        return $this->renderApiResponse($collection);
    }
}
