<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Controllers;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CController;
use Ox\Core\CMbException;
use Ox\Core\CModelObject;
use Ox\Mediboard\Sample\Entities\CSamplePerson;
use Ox\Mediboard\Sample\Repositories\SamplePersonsRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller that answer the api calls on persons.
 */
class SamplePersonsController extends CController
{
    /**
     * @api
     *
     * Load a list of persons using the request parameters.
     * Before being autowire the SamplePersonsRepository is initialized using the RequestApi.
     * A collection of persons is created using the arguments of the RequestApi for fieldsets, relationships, ...
     *
     * @see SamplePersonsRepository::initFromRequest
     *
     * @throws Exception
     */
    public function listPersons(RequestApi $request_api, SamplePersonsRepository $persons_repository): Response
    {
        $persons = $persons_repository->find();

        // Massload relations to avoid loading them unitary later.
        $persons_repository->massLoadRelations($persons, $request_api->getRelations());

        $collection =  Collection::createFromRequest($request_api, $persons);

        // Add the profile picture to the links
        foreach ($collection as $item) {
            /** @var CSamplePerson $person */
            $person = $item->getDatas();
            $item->addLinks($person->buildProfilePictureLink());
        }
        $collection->createLinksPagination(
            $request_api->getOffset(),
            $request_api->getLimit(),
            $persons_repository->count()
        );

        return $this->renderApiResponse($collection);
    }

    /**
     * @api
     *
     * Use the Ox\Core\Kernel\Resolver\CStoredObjectAttributeValueResolver to inject the CSamplePerson from the
     * parameter sample_person_id.
     *
     * @see CStoredObjectAttributeValueResolver
     *
     * @throws ApiException
     */
    public function getPerson(CSamplePerson $person, RequestApi $request_api): Response
    {
        return $this->renderApiResponse(Item::createFromRequest($request_api, $person));
    }

    /**
     * @api
     *
     * Use the RequestApi to create a collection of persons from the body content.
     * Store the collection of persons en return it.
     *
     * @throws ApiException|CMbException
     */
    public function createPerson(RequestApi $request_api): Response
    {
        $persons = $request_api->getModelObjectCollection(
            CSamplePerson::class,
            [CSamplePerson::FIELDSET_DEFAULT, CSamplePerson::FIELDSET_EXTRA],
            // Temporary for retro compatibility purpose.
            ['nationality_id']
        );

        $collection = $this->storeCollection($persons);
        $collection->setModelFieldsets($request_api->getFieldsets());
        $collection->setModelRelations($request_api->getRelations());

        return $this->renderApiResponse($collection, 201);
    }

    /**
     * @api
     *
     * Update an existing person ($person) using the body of the request.
     * The user must have the edit permission on the person
     *
     * @throws ApiException|CMbException
     */
    public function updatePerson(CSamplePerson $person, RequestApi $request_api): Response
    {
        /** @var CSamplePerson $person */
        $person = $request_api->getModelObject(
            $person,
            [CSamplePerson::FIELDSET_DEFAULT, CSamplePerson::FIELDSET_EXTRA],
            // Temporary for retro compatibility purpose.
            ['nationality_id']
        );

        $item = $this->storeObject($person);
        $item->setModelFieldsets($request_api->getFieldsets());
        $item->setModelRelations($request_api->getRelations());

        return $this->renderApiResponse($item, Response::HTTP_OK);
    }

    /**
     * @api
     *
     * Delete a person ($person). The user must have the edit permission on the person.
     *
     * @throws CMbException
     */
    public function deletePerson(CSamplePerson $person): Response
    {
        $this->deleteObject($person);

        return $this->renderResponse(null, Response::HTTP_NO_CONTENT);
    }
}
