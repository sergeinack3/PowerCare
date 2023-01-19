<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers\Controllers;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CAppUI;
use Ox\Core\CController;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CCSVImportMediusers;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CModuleAction;
use Ox\Mediboard\System\Controllers\TabController;
use Ox\Mediboard\System\CPreferences;
use Ox\Mediboard\System\CTab;
use Ox\Mediboard\System\CTabHit;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;


class MediusersController extends CController
{
    public const LINK_EDIT_USER_INFOS = 'edit_infos';
    public const LINK_LOGOUT          = 'logout';
    public const LINK_DEFAULT_PAGE    = 'default';

    public const IS_MAIN_FUNCTION = 'is_main';

    public const MOST_CALLED_TABS_COUNT = 4;

    /**
     * @param RequestApi $request_api
     *
     * @return Response
     * @throws Exception
     * @api
     */
    public function listMediusers(RequestApi $request_api): Response
    {
        $type          = $request_api->getRequest()->get("type", "prat");
        $name          = $request_api->getRequest()->get("name");
        $establishment = $request_api->getRequest()->get("establishment", "0");

        switch ($type) {
            case "prat":
            default:
                $mediusers = (new CMediusers())->loadPraticiens(
                    PERM_READ,
                    $establishment ? CFunctions::getCurrent()->_id : null,
                    $name,
                    false,
                    true,
                    true,
                    CGroups::loadCurrent()->_id
                );
                break;

            case "anesth":
                $mediusers = (new CMediusers())->loadAnesthesistes(PERM_READ, null, $name);
        }

        $total = count($mediusers);

        $resource = Collection::createFromRequest($request_api, $mediusers);

        $resource->createLinksPagination($request_api->getOffset(), $request_api->getLimit(), $total);

        return $this->renderApiResponse($resource);
    }

    /**
     * @param RequestApi $request_api
     * @param CMediusers  $mediuser
     *
     * @return Response
     * @throws Exception
     * @api
     */
    public function showMediuser(RequestApi $request_api, CMediusers $mediuser): Response
    {
        $mediuser->loadRefFunction();

        return $this->renderApiResponse(Item::createFromRequest($request_api, $mediuser));
    }

    /**
     * @param RequestApi $request_api
     *
     * @return Response
     * @throws Exception
     * @api
     */
    public function showMediuserByRPPS(RequestApi $request_api): Response
    {
        $mediuser       = new CMediusers();
        $mediuser->rpps = $request_api->getRequest()->get("rpps");
        $mediuser->loadMatchingObject();
        $mediuser->loadRefFunction();

        return $this->renderApiResponse(Item::createFromRequest($request_api, $mediuser));
    }

    /**
     * @api
     */
    public function listFunctions(CMediusers $mediusers, RequestApi $request_api): Response
    {
        $main_function       = $mediusers->loadRefFunction();
        $secondary_functions = $mediusers->loadRefsSecondaryFunctions();

        $functions  = array_merge([$main_function], $secondary_functions);
        $collection = Collection::createFromRequest($request_api, $functions);

        // Add group_id
        $collection->addModelFieldset(['default', 'target']);

        /** @var Item $item */
        foreach ($collection as $item) {
            /** @var CFunctions $function */
            $function = $item->getDatas();

            $item->addAdditionalDatas(
                [
                    self::IS_MAIN_FUNCTION => ($function->_id === $main_function->_id),
                ]
            );
        }

        return $this->renderApiResponse($collection);
    }

    /**
     * @api
     */
    public function listHits(RequestApi $request_api): Response
    {
        $mediuser = CMediusers::get();

        $tabs = (new CTabHit())->getMostCalledTabs($mediuser, self::MOST_CALLED_TABS_COUNT);

        $collection = Collection::createFromRequest($request_api, $tabs);

        /** @var Collection $item */
        foreach ($collection as $item) {
            $item->setType(TabController::TAB_RESOURCE_TYPE);
            $item->addLinks(
                [
                    TabController::LINK_TAB_URL => $item->getDatas()->getUrl(),
                ]
            );
        }

        return $this->renderApiResponse($collection);
    }

    /**
     * @api
     */
    public function importMediusers(RequestApi $request_api): Response
    {

        /** @var UploadedFile $uploaded_file */
        $uploaded_file = $request_api->getRequest()->files->get('import_file');
        if (!$uploaded_file) {
            throw new CMbException('MediusersController-Error-File is mandatory for import');
        }

        $file = $uploaded_file->getRealPath();
        
        $update  = $request_api->getRequest()->get('update_found_users', false);
        $dry_run = $request_api->getRequest()->get('dry_run', true);

        $import = new CCSVImportMediusers($file, $dry_run, $update, 0, CCSVImportMediusers::MAX_LINES);

        try {
            $import->import();
        } catch (Exception $e) {
            unlink($file);

            throw $e;
        }

        unlink($file);

        $data = $this->buildData($import->getFound(), $import->getCreated(), $import->getErrors());

        $return_code = count($data['created']) ? 201 : 200;

        return $this->renderApiResponse(new Item($data), $return_code);
    }

    private function buildData(array $found, array $created, array $errors): array
    {
        $data = [
            'created' => [],
            'found'   => [],
            'errors'  => $errors,
        ];

        foreach ($found as $short_class => $count) {
            $data['found'][] = CAppUI::tr($short_class . '-msg-found') . ' (x ' . $count . ')';
        }

        foreach ($created as $short_class => $count) {
            $data['created'][] = CAppUI::tr($short_class . '-msg-create') . ' (x ' . $count . ')';
        }

        return $data;
    }
}
