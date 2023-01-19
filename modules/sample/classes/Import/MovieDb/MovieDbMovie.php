<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Import\MovieDb;

use JsonSerializable;
use Ox\Core\CApp;
use Ox\Core\CMbDT;
use Ox\Mediboard\Sample\Entities\CSampleCasting;
use Ox\Mediboard\Sample\Entities\CSampleCategory;
use Ox\Mediboard\Sample\Entities\CSampleMovie;
use Ox\Mediboard\Sample\Entities\CSamplePerson;
use Ox\Mediboard\Sante400\CIdSante400;

/**
 * Representation of a movie that can be serialized into a JSON:API object ready to be POST.
 */
class MovieDbMovie implements JsonSerializable
{
    /** @var int */
    private $id;

    /** @var string */
    private $title;

    /** @var string */
    private $overview;

    /** @var string */
    private $release_date;

    /** @var int */
    private $runtime;

    /** @var int */
    private $genre;

    /** @var string */
    private $csa;

    /** @var array */
    private $spoken_languages;

    /** @var int */
    private $director;

    /** @var int[] */
    private $casting = [];

    /** @var MovieDbImage */
    private $poster;

    public function __construct(array $data)
    {
        $this->id               = $data['id'];
        $this->title            = $data['title'] ?? null;
        $this->overview         = $data['overview'] ?? null;
        $this->release_date     = (isset($data['release_date']) && $data['release_date'])
            ? $data['release_date']
            : CMbDT::date();
        $this->runtime          = $data['runtime'] ?? null;
        $this->genre            = $data['genre'] ?? null;
        $this->director         = $data['director'] ?? null;
        $this->spoken_languages = $data['spoken_languages'] ?? [];
        $this->casting          = $data['casting'] ?? [];
        $this->poster           = $data['poster'] ?? null;
        $this->csa              = rand(0, 1) ? CSampleMovie::CSA[array_rand(CSampleMovie::CSA)] : null;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'            => null,
            'type'          => CSampleMovie::RESOURCE_TYPE,
            'attributes'    => [
                'name'        => utf8_encode($this->title),
                'description' => utf8_encode($this->overview),
                'release'     => $this->release_date,
                'duration'    => $this->convertDuration(),
                'languages'   => $this->extractLanguages(),
                'csa'         => $this->csa,
            ],
            'relationships' => [
                CSampleMovie::RELATION_DIRECTOR    => [
                    'data' => ['id' => $this->director, 'type' => CSamplePerson::RESOURCE_TYPE],
                ],
                CSampleMovie::RELATION_CATEGORY    => [
                    'data' => ['id' => $this->genre, 'type' => CSampleCategory::RESOURCE_TYPE],
                ],
                CSampleMovie::RELATION_FILES       => [
                    'data' => $this->poster,
                ],
                CSampleMovie::RELATION_IDENTIFIANT => [
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

    private function convertDuration(): string
    {
        if (!$this->runtime) {
            $this->runtime = rand(1, 300);
        }

        return sprintf('%02d:%02d:00', floor($this->runtime / 60), $this->runtime % 60);
    }

    private function extractLanguages(): ?string
    {
        $langs = [];
        foreach ($this->spoken_languages as $language) {
            $language = $language['iso_639_1'];
            if (in_array($language, CSampleMovie::LANGUAGES)) {
                $langs[] = $language;
            }
        }

        return implode('|', $langs);
    }

    public function convertCasting(): array
    {
        $cast  = [];
        $first = true;
        foreach ($this->casting as $casting) {
            $cast[] = [
                'type'          => CSampleCasting::RESOURCE_TYPE,
                'id'            => null,
                'attributes'    => [
                    'is_main_actor' => $first ? '1' : '0',
                ],
                'relationships' => [
                    CSampleCasting::RELATION_ACTOR => [
                        'data' => [
                            'type' => CSamplePerson::RESOURCE_TYPE,
                            'id'   => $casting,
                        ],
                    ],
                ],
            ];

            $first = false;
        }

        return $cast;
    }
}
