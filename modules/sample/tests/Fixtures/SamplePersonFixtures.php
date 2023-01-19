<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Tests\Fixtures;

use Exception;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbFieldSpec;
use Ox\Core\CMbString;
use Ox\Core\FieldSpecs\CStrSpec;
use Ox\Mediboard\Sample\Entities\CSampleNationality;
use Ox\Mediboard\Sample\Entities\CSamplePerson;
use Ox\Mediboard\System\CFirstNameAssociativeSex;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * Fixtures that create :
 *      - 1 CSamplePerson which is director
 *      - 5 CSamplePerson which are not directors with a tag
 *      - 5 CSamplePerson which are not directors without a tag
 */
class SamplePersonFixtures extends Fixtures implements GroupFixturesInterface
{
    public const DIRECTOR_TAG = 'sample_director';

    public const ACTOR_TAG_1 = 'sample_actor_1';
    public const ACTOR_TAG_2 = 'sample_actor_2';
    public const ACTOR_TAG_3 = 'sample_actor_3';
    public const ACTOR_TAG_4 = 'sample_actor_4';
    public const ACTOR_TAG_5 = 'sample_actor_5';

    public const ACTOR_TAG_PREFIX    = 'sample_actor_';
    public const DIRECTOR_TAG_PREFIX = 'sample_director_';

    public const ACTOR_COUNT    = 50;
    public const DIRECTOR_COUNT = 10;

    private const NATIONALITIES = [
        'Néo-Zélandaise',
        'Française',
        'Finlandaise',
        'Espagnole',
        'Tadjik',
    ];

    /**
     * @inheritDoc
     *
     * @throws FixturesException
     */
    public function load(): void
    {
        $director_count = $this->isFullMode() ? self::DIRECTOR_COUNT * 2 : 2;
        $actor_count    = $this->isFullMode() ? self::ACTOR_COUNT * 2 : 10;

        // Create DIRECTOR_COUNT directors only on is tagged wit hthe constant
        $names = $this->getRandomNames($director_count);
        for ($i = 0; $i < $director_count; $i += 2) {
            $actor = $this->buildPerson($names[$i], $names[$i + 1], true);

            $value = ($i / 2) + 1;
            $this->store($actor, $value === 1 ? self::DIRECTOR_TAG : (self::DIRECTOR_TAG_PREFIX . $value));
        }

        $names = $this->getRandomNames($actor_count);
        for ($i = 0; $i < $actor_count; $i += 2) {
            $actor = $this->buildPerson($names[$i], $names[$i + 1]);

            $value = ($i / 2) + 1;
            $const = 'self::ACTOR_TAG_' . $value;
            $this->store($actor, defined($const) ? constant($const) : (self::ACTOR_TAG_PREFIX . $value));
        }
    }

    /**
     * @inheritDoc
     */
    public static function getGroup(): array
    {
        return ['sample_fixtures', 200];
    }

    /**
     * Get a single random nationality from the NATIONALITIES const.
     */
    private function getNationality(): ?CSampleNationality
    {
        $nationality       = new CSampleNationality();
        $nationality->name = self::NATIONALITIES[array_rand(self::NATIONALITIES)];
        $nationality->loadMatchingObjectEsc();

        if (!$nationality->_id) {
            $nationality->code = substr($nationality->name, 0, 3);
            $this->store($nationality);
        }

        return $nationality->_id ? $nationality : null;
    }

    /**
     * Build a CSamplePerson from random data.
     */
    private function buildPerson(
        string $last_name,
        string $first_name,
        bool $is_director = false
    ): CSamplePerson {
        $nationality = $this->getNationality();

        // Last name and first name muste have at least 4 caracters to be used with the match against from seek.
        while (strlen($last_name) < 4) {
            $last_name .= $last_name;
        }

        while (strlen($first_name) < 4) {
            $first_name .= $first_name;
        }

        $person                 = new CSamplePerson();
        $person->last_name      = $last_name;
        $person->first_name     = $first_name;
        $person->sex            = $this->getRandomSex();
        $person->is_director    = $is_director ? '1' : '0';
        $person->birthdate      = CMbDT::getRandomDate('1850-01-01', CMbDT::date('- 5 YEAR'), 'Y-m-d');
        $person->activity_start = CMbDT::date('+ 5 YEAR', $person->birthdate);
        $person->nationality_id = $nationality->_id ?? null;

        return $person;
    }

    /**
     * Get random names using the CFirstNameAssociativeSex if possible or get random strings.
     *
     * @throws Exception
     */
    private function getRandomNames(int $name_count): array
    {
        $first_name = new CFirstNameAssociativeSex();
        if (($count = $first_name->countList()) >= $name_count) {
            $start = rand(0, ($count - $name_count));

            $names = $first_name->loadList(null, null, "{$start},{$name_count}");

            // Need to reset the keys of the array
            return array_values(CMbArray::pluck($names, 'firstname'));
        }

        $names = [];
        for ($i = 0; $i < $name_count; $i++) {
            $names[] = CStrSpec::randomString(CMbFieldSpec::$chars, 16);
        }

        return $names;
    }

    /**
     * Get a random sex.
     */
    private function getRandomSex(): string
    {
        return rand(0, 1) ? 'm' : 'f';
    }
}
