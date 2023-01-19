<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample\Controllers\Legacy;

use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\RequestFieldsets;
use Ox\Core\Api\Request\RequestRelations;
use Ox\Core\Api\Resources\AbstractResource;
use Ox\Core\CCanDo;
use Ox\Core\CLegacyController;
use Ox\Core\CMbException;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CMbString;
use Ox\Core\CView;
use Ox\Core\EntryPoint;
use Ox\Core\Kernel\Routing\RouterBridge;
use Ox\Mediboard\Sample\Entities\CSampleCasting;
use Ox\Mediboard\Sample\Entities\CSampleMovie;

/**
 * Legacy controller that display the tabs readme and movies.
 */
class SampleMoviesController extends CLegacyController
{
    public const LEGACY_MOVIE_DETAIL_LINK = '?m=sample&tab=displayMovieDetails&sample_movie_id=%d';

    private const README = 'readme.md';

    public function readme(): void
    {
        $this->checkPermRead();

        $readme = CMbString::markdown(
            file_get_contents(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . self::README)
        );

        $this->renderSmarty('readme', ['readme' => $readme,]);
    }

    /**
     * Legcay route to display movies
     *
     * @throws ApiException|CMbException|Exception
     */
    public function displayMovies(): void
    {
        $this->checkPermRead();

        CView::checkin();

        $entry = new EntryPoint('SampleMovieList', RouterBridge::getInstance());
        $entry->setScriptName('sampleMovieList')
            ->addLink('categories', 'sample_categories_list')
            ->addLink('nationalities', 'sample_nationalities_list')
            ->addLink('movies', 'sample_movies_list')
            ->addLink('persons', 'sample_persons_list');

        if (CCanDo::edit()) {
            $entry->addLink('add-movie', 'sample_movies_create');
        }

        $this->renderEntryPoint($entry);
    }

    /**
     * Legacy route to display a movie with it's casting.
     *
     * @throws CMbModelNotFoundException|CMbException|Exception
     */
    public function displayMovieDetails(): void
    {
        $this->checkPermRead();

        $movie_id = CView::getRefCheckRead('sample_movie_id', 'ref class|CSampleMovie notNull');

        CView::checkin();

        $entry = new EntryPoint('SampleMovieDetails', RouterBridge::getInstance());
        $entry->setScriptName('sampleMovieDetails')
            ->addLink(
                'movie',
                'sample_movies_show',
                [
                    'sample_movie_id'                        => $movie_id,
                    RequestFieldsets::QUERY_KEYWORD          => RequestFieldsets::QUERY_KEYWORD_ALL,
                    RequestRelations::QUERY_KEYWORD_INCLUDE  => CSampleMovie::RELATION_CATEGORY
                        . RequestRelations::RELATION_SEPARATOR . CSampleMovie::RELATION_DIRECTOR,
                    AbstractResource::PERMISSIONS_KEYWORD    => true,
                ]
            )
            ->addLink(
                'casting',
                'sample_casting_list',
                [
                    'sample_movie_id'                        => $movie_id,
                    RequestRelations::QUERY_KEYWORD_INCLUDE => CSampleCasting::RELATION_ACTOR,
                ]
            )
            ->addLink('categories', 'sample_categories_list')
            ->addLink('nationalities', 'sample_nationalities_list')
            ->addLink('persons', 'sample_persons_list')
            ->addLinkValue('back', '?m=sample&tab=displayMovies');
        
        $this->renderEntryPoint($entry);
    }
}
