<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Import\MovieDb;

use JsonSerializable;
use Ox\Mediboard\Sample\Entities\CSampleMovie;
use Ox\Mediboard\Sample\Entities\CSamplePerson;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Representation of a person that can be serialized into a JSON:API object ready to be posted.
 */
class MovieDbPerson implements JsonSerializable
{
    /** @var int */
    private $id;

    /** @var int */
    private $gender;

    /** @var string */
    private $name;

    /** @var string */
    private $birthday;

    /** @var bool */
    private $director;

    /** @var MovieDbImage */
    private $profile;

    public function __construct(array $data)
    {
        $this->id       = $data['id'];
        $this->gender   = $data['gender'] ?? null;
        $this->name     = $data['name'] ?? null;
        $this->birthday = $data['birthday'] ?? null;
        $this->director = $data['director'] ?? null;
        $this->profile  = $data['profile'] ?? null;
    }

    public function jsonSerialize(): array
    {
        if (strpos($this->name, ' ') === false) {
            $first_name = $last_name = $this->name;
        } else {
            [$first_name, $last_name] = explode(' ', $this->name, 2);
        }

        return [
            'id'            => null,
            'type'          => CSamplePerson::RESOURCE_TYPE,
            'attributes'    => [
                'last_name'   => utf8_encode($last_name),
                'first_name'  => utf8_encode($first_name),
                'sex'         => !$this->gender ? null : ($this->gender === 1 ? 'f' : 'm'),
                'birthdate'   => $this->birthday,
                'is_director' => $this->director ? '1' : '0',
            ],
            'relationships' => [
                CSamplePerson::RELATION_FILES      => [
                    'data' => $this->profile,
                ],
                CSamplePerson::RELATION_IDENTIFIANT => [
                    'data' => [
                        [
                            'id'         => null,
                            'type'       => CIdSante400::RESOURCE_TYPE,
                            'attributes' => [
                                'id400' => $this->id,
                                'tag'   => SampleMovieImport::IMPORT_TAG_NAME,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
