<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbException;
use Ox\Core\CMbString;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;

/**
 * Description
 */
class CCommunesSearch
{
    public const COLUMN_CP            = 'code_postal';
    public const COLUMN_COMMUNE       = 'commune';
    public const COLUMN_PAYS          = 'pays';
    public const COLUMN_INSEE_COMMUNE = 'INSEE';
    public const COLUMN_NUMERIC       = 'numerique';
    public const COLUMN_CODE_INSEE    = 'code_insee';

    public const DEFAULT_SEARCH_COLUMNS = [self::COLUMN_CP, self::COLUMN_COMMUNE, self::COLUMN_INSEE_COMMUNE];

    public const CONF_INSEE = 'dPpatients INSEE';

    private const COUNTRIES = [
        CPaysInsee::NUMERIC_FRANCE    => 'france',
        CPaysInsee::NUMERIC_SUISSE    => 'suisse',
        CPaysInsee::NUMERIC_ALLEMAGNE => 'allemagne',
        CPaysInsee::NUMERIC_ESPAGNE   => 'espagne',
        CPaysInsee::NUMERIC_PORTUGAL  => 'portugal',
        CPaysInsee::NUMERIC_GB        => 'gb',
        CPaysInsee::NUMERIC_BELGIQUE  => 'belgique',
    ];

    /** @var CSQLDataSource */
    private $ds;

    /** @var array */
    private $available_countries = [];

    /** @var int */
    private $limit;

    /** @var array */
    private $matches = [];

    public function __construct()
    {
        $this->ds = CSQLDataSource::get('INSEE');

        foreach (CAppUI::conf(self::CONF_INSEE) as $country => $active) {
            if ($active && in_array($country, self::COUNTRIES) && $this->ds->hasTable("communes_{$country}")) {
                $this->available_countries[array_search($country, self::COUNTRIES)] = $country;
            }
        }
    }

    public function match(string $keyword, string $column, int $max): array
    {
        $needle = $this->prepareNeedle($keyword, $column);

        $this->setLimit($max);

        $res = [];

        if ($column == self::COLUMN_INSEE_COMMUNE) {
            $res[self::COUNTRIES[CPaysInsee::NUMERIC_FRANCE]] = $this->searchCommunes(
                $needle,
                $column,
                self::COUNTRIES[CPaysInsee::NUMERIC_FRANCE],
                CPaysInsee::NUMERIC_FRANCE
            );
        } else {
            foreach ($this->available_countries as $numeric => $country) {
                $communes      = $this->searchCommunes($needle, $column, $country, $numeric);
                $res[$country] = isset($res[$country]) ? array_merge($res[$country], $communes) : $communes;
            }
        }

        $res = $this->getLimitedResult($res);

        $this->matches = $this->sortResults($res, $column);
        $this->matches = $this->sanitizeResults($this->matches);

        return $this->matches;
    }

    private function getLimitedResult(array $matches): array
    {
        $limitedRes = [];
        $matches    = array_filter($matches);

        if ($matches) {
            $limit = ceil($this->limit / count($matches));

            foreach ($matches as $country) {
                if ($country) {
                    $limitedRes = array_merge($limitedRes, array_slice($country, 0, $limit));
                }
            }
        }
        return $limitedRes;
    }

    private function searchCommunes(string $needle, string $column, string $country, int $numeric): array
    {
        $query = new CRequest();
        $query->addSelect($this->buildSelect($country));
        $query->addTable(["communes_{$country}", "pays"]);
        $query->addWhere(
            [
                "numerique" => $this->ds->prepare("= ?", $numeric),
            ]
        );
        if ($column == self::COLUMN_COMMUNE) {
            $needle = str_replace("-", " ", $needle);
            $query->addSelect("LENGTH(commune) as length");
            $query->addWhere("REPLACE(commune,'-',' ')" . $this->ds->prepareLike($needle));
            $query->addOrder("length");
        } else {
            $query->addWhere(
                [
                    $column     => $this->ds->prepareLike($needle),
                ]
            );
        }
        $query->setLimit($this->limit);

        return $this->ds->loadList($query->makeSelect());
    }

    private function buildSelect(string $country): array
    {
        $france = $country === 'france';

        return [
            "commune",
            "code_postal",
            ($france ? '' : "'' AS ") . "departement",
            ($france ? '' : "'' AS ") . "INSEE",
            "code_insee",
            "numerique",
            "'" . ucfirst($country) . "' AS pays",
        ];
    }

    /**
     * @throws CMbException
     */
    private function prepareNeedle(string $keyword, string $column): string
    {
        if ($column === self::COLUMN_CP) {
            return "{$keyword}%";
        } elseif ($column === self::COLUMN_COMMUNE) {
            return "%{$keyword}%";
        } elseif ($column === self::COLUMN_INSEE_COMMUNE) {
            return "%{$keyword}%";
        }

        throw new CMbException(
            'CCommunesSearch-Error-Column col must be in array',
            $column,
            self::DEFAULT_SEARCH_COLUMNS
        );
    }

    private function setLimit(int $max): void
    {
        if ($max === 0) {
            $max = 1;
        }

        $this->limit = $max;
    }

    private function sortResults(array $matches, string $column): array
    {
        $order_1 = CMbArray::pluck($matches, $column === self::COLUMN_COMMUNE ? 'length' : 'code_postal');
        $order_2 = CMbArray::pluck($matches, $column === self::COLUMN_COMMUNE ? 'code_postal' : 'commune');
        array_multisort($order_1, SORT_ASC, $order_2, SORT_ASC, $matches);

        return $matches;
    }

    private function sanitizeResults(array $matches): array
    {
        foreach ($matches as &$match) {
            $match['commune']     = CMbString::capitalize($match['commune']);
            $match['departement'] = CMbString::capitalize($match['departement']);
            $match['pays']        = CMbString::capitalize($match['pays']);
        }

        return $matches;
    }

    public function getAvailableCountries(): array
    {
        return $this->available_countries;
    }
}
