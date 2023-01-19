<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Repositories;

use Exception;
use Ox\Core\Api\Request\RequestRelations;
use Ox\Core\CMbException;
use Ox\Core\CStoredObject;
use Ox\Core\Repositories\AbstractRequestApiRepository;
use Ox\Mediboard\Sample\Entities\CSamplePerson;

/**
 * Repository to fetch CSamplePerson objects.
 */
class SamplePersonsRepository extends AbstractRequestApiRepository
{
    /**
     * @inheritDoc
     */
    protected function getObjectInstance(): CStoredObject
    {
        return new CSamplePerson();
    }

    /**
     * Massload the datas for the $relation.
     * If the keyword 'all' is passed as $relation massload all the relations.
     *
     * @param CSamplePerson[]  $objects
     *
     * @throws Exception|CMbException
     */
    protected function massLoadRelation(array $objects, string $relation): void
    {
        switch ($relation) {
            case RequestRelations::QUERY_KEYWORD_ALL:
                $this->massLoadNationalities($objects);
                $this->massLoadRoles($objects);
                $this->massLoadProfilePictures($objects);
                $this->massLoadIdx($objects);
                break;
            case CSamplePerson::RELATION_NATIONALITY:
                $this->massLoadNationalities($objects);
                break;
            case CSamplePerson::RELATION_MOVIES_PLAYED:
                $this->massLoadRoles($objects);
                break;
            case CSamplePerson::RELATION_FILES:
                $this->massLoadProfilePictures($objects);
                break;
        }
    }

    /**
     * Massload the nationality_id of the CSamplePerson list.
     *
     * @param CSamplePerson[] $objects
     *
     * @throws Exception
     */
    private function massLoadNationalities(array $objects): void
    {
        CStoredObject::massLoadFwdRef($objects, 'nationality_id');
    }

    /**
     * Massload the roles (CSampleCasting) of the CSamplePerson list.
     *
     * @param CSamplePerson[] $objects
     *
     * @throws Exception
     */
    private function massLoadRoles(array $objects): void
    {
        $casting = CStoredObject::massLoadBackRefs($objects, 'roles');
        CStoredObject::massLoadFwdRef($casting, 'movie_id');
    }

    /**
     * Massload the profile pictures of the CSamplePerson list.
     *
     * @param CSamplePerson[] $objects
     *
     * @throws Exception
     */
    private function massLoadProfilePictures(array $objects): void
    {
        CStoredObject::massLoadBackRefs(
            $objects,
            'files',
            null,
            [
                'file_name' => $this->object->getDS()->prepare('= ?', CSamplePerson::PROFILE_NAME)
            ]
        );
    }

    /**
     * @param CSamplePerson[] $objects
     *
     * @throws Exception
     */
    public function massLoadIdx(array $objects): void
    {
        CStoredObject::massLoadBackRefs($objects, 'identifiants');
    }
}
