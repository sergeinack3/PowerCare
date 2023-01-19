<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Controllers;

use Exception;
use Ox\Components\Cache\Exceptions\CouldNotGetCache;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\Content\RequestContentException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Item;
use Ox\Core\Api\Utility\FilterableTrait;
use Ox\Core\Cache;
use Ox\Core\CAppUI;
use Ox\Core\CController;
use Ox\Core\CMbException;
use Ox\Core\CModelObject;
use Ox\Core\CModelObjectCollection;
use Ox\Core\CSQLDataSource;
use Ox\Core\Kernel\Exception\HttpException;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CPreferences;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

/**
 * API Controller for CPreferences class
 */
class PreferencesController extends CController
{
    use FilterableTrait;

    public const CACHE_PREFIX = 'PreferencesController';

    public const NO_MODULE_PREF_NAME = 'common';

    private const ALL_MODULES = 'all';

    /**
     * @param string     $mod_name
     * @param RequestApi $request_api
     *
     * @return Response
     * @throws ApiException
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     *
     * @api
     */
    public function listPreferences(string $mod_name, RequestApi $request_api): Response
    {
        $module_prefs = ($mod_name === self::ALL_MODULES)
            ? $this->loadAllModulesPrefs() : $this->loadModulePrefs($mod_name);

        $prefs = CPreferences::getPrefValuesForList($module_prefs);

        return $this->returnResponse($this->applyFilter($request_api, $prefs));
    }

    /**
     * @param string     $mod_name
     * @param CUser      $user
     * @param RequestApi $request_api
     *
     * @return Response
     * @throws ApiException
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     *
     * @api
     */
    public function listUserPreferences(string $mod_name, CUser $user, RequestApi $request_api): Response
    {
        $module_prefs = ($mod_name === self::ALL_MODULES)
            ? $this->loadAllModulesPrefs() : $this->loadModulePrefs($mod_name);

        $prefs = CPreferences::getAllPrefsForList($user, $module_prefs);

        return $this->returnResponse($this->applyFilter($request_api, $prefs));
    }

    /**
     * [API] Set a list of preferences (default or for a user)
     *
     * @param CUser|null $user
     * @param RequestApi $request_api
     *
     * @return Response
     * @throws ApiException
     * @throws RequestContentException
     * @throws CMbException
     * @throws Exception
     *
     * @api
     */
    public function setPreferences(?CUser $user, RequestApi $request_api): Response
    {
        $restricted = $request_api->getRequest()->query->getBoolean('restricted');

        // Check permission for request
        $this->checkPermPreferences($user, $restricted);

        // Get request body data
        $preferences = $request_api->getModelObjectCollection(CPreferences::class);

        // If preference for a user, check if default preference exists before storing it
        // If at least preference does not have a default preference, throw an exception with concerned preferences.
        if ($user && $user->_id) {
            if (count($no_default_pref = $this->checkIfDefaultPreferenceExist($preferences)) > 0) {
                throw new HttpException(
                    Response::HTTP_NOT_FOUND,
                    CAppUI::tr(
                        'CPreferences-error-Preference %s does not have a default preference',
                        implode(', ', $no_default_pref)
                    )
                );
            }
        }

        /** @var CPreferences $preference */
        foreach ($preferences as $preference) {
            $value                  = $preference->value;
            $preference->user_id    = ($user) ? $user->_id : null;
            $preference->restricted = ($restricted) ? '1' : '0';
            $preference->value      = null;
            $preference->loadMatchingObjectEsc();
            $preference->value = $value;
        }

        return $this->storeCollectionAndRenderApiResponse($preferences);
    }

    /**
     * [API] Delete a list of preferences (for a user)
     *
     * @param CUser|null $user
     * @param RequestApi $request_api
     *
     * @return Response
     * @throws ApiException
     * @throws RequestContentException
     * @throws Exception
     * @api
     */
    public function deletePreferences(?CUser $user, RequestApi $request_api): Response
    {
        if (!$user || !$user->_id) {
            throw new ApiException(
                CAppUI::tr('CPreferences-error-You cannot delete default preferences')
            );
        }

        $restricted = $request_api->getRequest()->query->getBoolean('restricted');

        // Check permission for request
        $this->checkPermPreferences($user, $restricted);

        $to_delete = [];
        $not_exist = [];

        /** @var Item $_pref */
        foreach ($request_api->getResource()->getItems() as $_pref) {
            /** @var CPreferences $preference */
            $preference             = $_pref->createModelObject(CPreferences::class, true)
                ->hydrateObject([CModelObject::FIELDSET_DEFAULT])
                ->getModelObject();
            $preference->user_id    = $user->_id;
            $preference->restricted = ($restricted) ? '1' : '0';

            if ($preference->loadMatchingObjectEsc()) {
                $to_delete[] = $preference->pref_id;
            } else {
                $not_exist[] = $preference->key;
            }
        }

        // If at least one preference does not exist, throw exception with concerned preferences.
        if (count($not_exist) > 0) {
            throw new HttpException(
                Response::HTTP_NOT_FOUND,
                CAppUI::tr('CPreferences-error-Preferences does not exist %s', implode(', ', $not_exist))
            );
        }

        // Delete all requested preferences
        if ($msg = (new CPreferences())->deleteAll($to_delete)) {
            throw new ApiException($msg, Response::HTTP_NOT_FOUND);
        }

        return $this->renderResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Check if requested preferences have a default preference in database
     *
     * @param CModelObjectCollection $preferences
     *
     * @return array
     * @throws Exception
     */
    private function checkIfDefaultPreferenceExist(CModelObjectCollection $preferences): array
    {
        $requested_pref = [];

        /** @var CPreferences $_pref */
        foreach ($preferences as $_pref) {
            $requested_pref[] = $_pref->key;
        }

        $preference             = new CPreferences();
        $existing_default_prefs = $preference->loadColumn(
            'user_preferences.key',
            [
                "key"     => CSQLDataSource::prepareIn($requested_pref),
                "user_id" => $preference->getDS()->prepare("IS NULL"),
            ]
        );

        return array_diff($requested_pref, $existing_default_prefs);
    }

    /**
     * @param CUser|null $user
     * @param bool       $restricted
     *
     * @return void
     * @throws ApiException
     * @throws Exception
     */
    private function checkPermPreferences(?CUser $user, bool $restricted): void
    {
        $current_user = CMediusers::get();

        // Check if current user is type Administrator if another user is requested
        if ($user && $user->_id) {
            if (($user->_id !== $current_user->_id) && !$current_user->isAdmin()) {
                throw new ApiException(
                    CAppUI::tr('CPreferences-error-You need to be an admin to set or delete other users preferences')
                );
            }
        }

        // Check if request is setting or deleting a default pref, check if admin
        if (!$user && !$current_user->isAdmin()) {
            throw new ApiException(
                CAppUI::tr('CPreferences-error-You need to be an admin to set default preferences')
            );
        }

        // Check if current user is type Administrator if restricted param is true
        if ($restricted && !$current_user->isAdmin()) {
            throw new ApiException(
                CAppUI::tr('CPreferences-error-You need to be an admin to set or delete restricted preferences')
            );
        }
    }

    /**
     * @param array $preferences
     *
     * @return Response
     * @throws ApiException
     */
    private function returnResponse(array $preferences): Response
    {
        $ressource = new Item($preferences);
        $ressource->setType('preferences');

        return $this->renderApiResponse($ressource);
    }

    /**
     * @return array
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     */
    private function loadAllModulesPrefs(): array
    {
        $prefs = $this->loadModulePrefs(self::NO_MODULE_PREF_NAME);
        foreach (CModule::getActive() as $_mod) {
            $prefs = array_merge($prefs, $this->loadModulePrefs($_mod->mod_name));
        }

        return $prefs;
    }

    /**
     * @param string $mod_name
     *
     * @return array
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     */
    private function loadModulePrefs(string $mod_name): array
    {
        if ($mod_name !== self::NO_MODULE_PREF_NAME) {
            $mod_name = $this->getActiveModule($mod_name);
        }

        $cache = new Cache(self::CACHE_PREFIX, $mod_name, Cache::INNER_OUTER);
        if (!$cache->exists()) {
            // Load prefs names from module preferences file
            CPreferences::loadModule($mod_name);

            $cache->put(CPreferences::$modules[$mod_name] ?? []);
        }

        // Return loaded prefs
        return $cache->get();
    }
}
