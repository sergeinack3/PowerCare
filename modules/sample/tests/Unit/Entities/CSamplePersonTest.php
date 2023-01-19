<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Tests\Unit\Entities;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CMbException;
use Ox\Mediboard\Files\CFile;
use Ox\Mediboard\Sample\Entities\CSamplePerson;
use Ox\Mediboard\Sample\Tests\Fixtures\SamplePersonFixtures;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use ReflectionException;

/**
 * Test class for CSamplePerson
 */
class CSamplePersonTest extends OxUnitTestCase
{
    /**
     * The store of a new CSamplePerson must create a profile picture as a CFile.
     *
     * @throws TestsException
     */
    public function testStoreNewPersonGetProfilePicture(): void
    {
        $sample_person             = new CSamplePerson();
        $sample_person->last_name  = uniqid();
        $sample_person->first_name = uniqid();

        $this->storeOrFailed($sample_person);

        $profile_picture = $this->getPrivateProperty($sample_person, 'profile_picture');
        $this->assertInstanceOf(CFile::class, $profile_picture);
        $this->assertNotNull($profile_picture->_id);
    }

    /**
     * The getResourceNationality return null instead a an empty Item if no nationality is set for the CSamplePerson
     *
     * @throws ApiException
     */
    public function testGetResourceNationalityWithoutNationality(): void
    {
        $person = new CSamplePerson();
        $this->assertNull($person->getResourceNationality());
    }

    /**
     * The profile picture resource is a CFile.
     *
     * @throws ApiException
     */
    public function testGetResourceProfilePictureOk(): void
    {
        /** @var CSamplePerson $person */
        $person = $this->getObjectFromFixturesReference(CSamplePerson::class, SamplePersonFixtures::ACTOR_TAG_1);
        $item = $person->getResourceProfilePicture();
        $this->assertInstanceOf(Item::class, $item);

        $this->assertArrayHasKey('profile_picture', $item->getLinks());

        $file = $item->getDatas();
        $this->assertInstanceOf(CFile::class, $file);
        $this->assertNotNull($file->_id);
    }

    /**
     * If no profile picture exists the getResourceProfilePicture must return null.
     *
     * @throws ApiException
     */
    public function testGetResourceProfilePictureWithoutProfilePicture(): void
    {
        $person = new CSamplePerson();
        $this->assertNull($person->getResourceProfilePicture());
    }

    /**
     * If no profile picture exists the getLink must return an empty array.
     *
     * @throws Exception
     */
    public function testBuildProfilePictureLinkEmpty(): void
    {
        $person = new CSamplePerson();
        $this->assertEmpty($person->buildProfilePictureLink());
    }

    /**
     * The creation of a profile picture is only possible on an already stored CSamplePerson.
     * If the CSamplePerson have no ID an exception must be thrown.
     *
     * @throws TestsException|ReflectionException
     */
    public function testCreateProfilePictureThrowException(): void
    {
        $this->expectExceptionObject(
            new CMbException('CSamplePerson-Error-Cannot-create-profile-picture-for-non-stored-person')
        );
        $this->invokePrivateMethod(new CSamplePerson(), 'createProfilePicture');
    }
}
