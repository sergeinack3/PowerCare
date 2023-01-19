<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Api\Utility;

use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Request\RequestFilter;
use Ox\Core\Api\Utility\ArrayFilter;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

class ArrayFilterTest extends OxUnitTestCase
{
    public function testNotValideSearchMode(): void
    {
        $request_api = RequestApi::createFromRequest(new Request(['filter' => 'key.invalid.CAb']));
        $this->expectExceptionMessage(
            'Search mode candidate \'invalid\' is not in ' . implode('|', ArrayFilter::SEARCH_MODES)
        );
        new ArrayFilter($request_api);
    }

    public function testNotValideSearchIn(): void
    {
        $request_api = RequestApi::createFromRequest(new Request(['filter' => 'invalid.contains.CAb']));
        $this->expectExceptionMessage(
            'Search in candidate \'invalid\' is not in ' . implode('|', ArrayFilter::SEARCH_IN)
        );
        new ArrayFilter($request_api);
    }

    public function testApplyWithtoutSearchWord(): void
    {
        $locales = $this->getLocales();

        $request_api = RequestApi::createFromRequest(new Request());
        $filter      = new ArrayFilter($request_api);
        // No change in the array
        $this->assertEquals($locales, $filter->apply($locales));
    }

    public function testApplyEmptySearchWord(): void
    {
        $locales = $this->getLocales();

        $request_api = RequestApi::createFromRequest(new Request(['filter' => 'key.contains.']));
        $filter      = new ArrayFilter($request_api);
        // No change in the array
        $this->assertEquals($locales, $filter->apply($locales));
    }

    public function testIsEnableTrue(): void
    {
        $request_api = RequestApi::createFromRequest(new Request(['filter' => 'key.contains.needle']));
        $filter      = new ArrayFilter($request_api);
        $this->assertTrue($filter->isEnabled());
    }

    public function testIsEnableFalse(): void
    {
        $request_api = RequestApi::createFromRequest(new Request());
        $filter      = new ArrayFilter($request_api);
        $this->assertFalse($filter->isEnabled());
    }

    /**
     * @dataProvider applyFilterOnKeyProvider
     */
    public function testApplyFilterOnKey(string $needle, string $search_mode, array $expected_result): void
    {
        $request_api = RequestApi::createFromRequest(
            new Request(['filter' => ArrayFilter::SEARCH_IN_KEY . ".{$search_mode}.{$needle}"])
        );
        $filter      = new ArrayFilter($request_api);
        $this->assertEquals($expected_result, $filter->apply($this->getLocales()));
    }

    /**
     * @dataProvider applyFilterOnValueProvider
     */
    public function testApplyFilterOnValue(string $needle, string $search_mode, array $expected_result): void
    {
        $request_api = RequestApi::createFromRequest(
            new Request(['filter' => ArrayFilter::SEARCH_IN_VALUE . ".{$search_mode}.{$needle}"])
        );
        $filter      = new ArrayFilter($request_api);
        $this->assertEquals($expected_result, $filter->apply($this->getLocales()));
    }

    public function applyFilterOnKeyProvider(): array
    {
        return [
            'starts_with_found'     => [
                'CAbo',
                RequestFilter::FILTER_BEGIN_WITH,
                [
                    'CAbonnement'                     => 'Abonnement',
                    'CAbonnement-abonnement_id'       => 'Abonnement',
                    'CAbonnement-abonnement_id-court' => 'Abonnement',
                ],
            ],
            'starts_with_not_found' => [
                uniqid(),
                RequestFilter::FILTER_BEGIN_WITH,
                [],
            ],
            'ends_with_found'       => [
                '_id',
                RequestFilter::FILTER_END_WITH,
                [
                    'CAbonnement-abonnement_id' => 'Abonnement',
                    'CSourcePOP-error-mail_id'  => 'Identifiant du message incorrect (mail_id)',
                ],
            ],
            'ends_with_not_found'   => [
                uniqid(),
                RequestFilter::FILTER_END_WITH,
                [],
            ],
            'contains_found'        => [
                'co',
                RequestFilter::FILTER_CONTAINS,
                [
                    'CAbonnement-abonnement_id-court' => 'Abonnement',
                    'Menu Icon'                       => 'Menu icones',
                    'CSourcePOP-error-noAccount'      => "Aucun compte lié à l'utilisateur %s",
                ],
            ],
            'contains_not_found'    => [
                uniqid(),
                RequestFilter::FILTER_CONTAINS,
                [],
            ],
            'equals_found'          => [
                'CSourcePOP-error-notInitiated',
                RequestFilter::FILTER_EQUAL,
                [
                    'CSourcePOP-error-notInitiated' => 'Source POP non initialisée (init requis)',
                ],
            ],
            'equals_not_found'      => [
                uniqid(),
                RequestFilter::FILTER_EQUAL,
                [],
            ],
        ];
    }

    public function applyFilterOnValueProvider(): array
    {
        return [
            'starts_with_found'     => [
                'Abon',
                RequestFilter::FILTER_BEGIN_WITH,
                [
                    'CAbonnement'                     => 'Abonnement',
                    'CAbonnement-abonnement_id'       => 'Abonnement',
                    'CAbonnement-abonnement_id-court' => 'Abonnement',
                ],
            ],
            'starts_with_not_found' => [
                uniqid(),
                RequestFilter::FILTER_BEGIN_WITH,
                [],
            ],
            'ends_with_found'       => [
                'gation',
                RequestFilter::FILTER_END_WITH,
                [
                    'Aggregation'                     => 'Agrégation',
                    'Aggregation-board'               => "Tableau de bord de l'agrégation",
                ],
            ],
            'ends_with_not_found'   => [
                uniqid(),
                RequestFilter::FILTER_END_WITH,
                [],
            ],
            'contains_found'        => [
                'in',
                RequestFilter::FILTER_CONTAINS,
                [
                    'CSourcePOP-error-mail_id'        => 'Identifiant du message incorrect (mail_id)',
                    'CSourcePOP-error-no_imap_lib'    => 'bibliothèque IMAP PHP non installée',
                    'CSourcePOP-error-notInitiated'   => 'Source POP non initialisée (init requis)',
                ],
            ],
            'contains_not_found'    => [
                uniqid(),
                RequestFilter::FILTER_CONTAINS,
                [],
            ],
            'equals_found'          => [
                'Menu icones',
                RequestFilter::FILTER_EQUAL,
                [
                    'Menu Icon'                       => 'Menu icones',
                ],
            ],
            'equals_not_found'      => [
                uniqid(),
                RequestFilter::FILTER_EQUAL,
                [],
            ],
        ];
    }

    private function getLocales(): array
    {
        return [
            'Aggregation'                     => 'Agrégation',
            'Aggregation-board'               => "Tableau de bord de l'agrégation",
            'CAbonnement'                     => 'Abonnement',
            'CAbonnement-abonnement_id'       => 'Abonnement',
            'CAbonnement-abonnement_id-court' => 'Abonnement',
            'CSourcePOP-error-mail_id'        => 'Identifiant du message incorrect (mail_id)',
            'CSourcePOP-error-noAccount'      => "Aucun compte lié à l'utilisateur %s",
            'CSourcePOP-error-no_imap_lib'    => 'bibliothèque IMAP PHP non installée',
            'CSourcePOP-error-notInitiated'   => 'Source POP non initialisée (init requis)',
            'Menu Icon'                       => 'Menu icones',
            'Menu Status'                     => 'Menu Etat',
            'Menu Text'                       => 'Menu Texte',
        ];
    }
}
