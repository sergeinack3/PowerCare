/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import SampleMovie from "@modules/sample/vue/models/SampleMovie"

/**
 * SampleMovie tests
 */
export default class SampleMovieTest extends OxTest {
    protected component = "SampleMovie"

    public testMovieGetLanguagesWithOneLanguage () {
        const movie = new SampleMovie()
        movie.languagesData = "fr"
        expect(movie.languages).toEqual("CSampleMovie.languages.fr")
    }

    public testMovieGetLanguagesWithMultiLanguage () {
        const movie = new SampleMovie()
        movie.languagesData = "fr|es"
        expect(movie.languages).toEqual("CSampleMovie.languages.fr, CSampleMovie.languages.es")
    }

    public testMovieHasPermEdit () {
        const movie = new SampleMovie()
        movie.meta = {
            permissions: {
                perm: "edit"
            }
        }
        expect(movie.permEdit).toBeTruthy()
    }

    public testMovieHasNotPermEdit () {
        const movie = new SampleMovie()
        expect(movie.permEdit).toBeFalsy()
    }

    public testMovieEmptyDetailLink () {
        const movie = new SampleMovie()
        movie.links = {
            self: "/api/sample/movies/1",
            schema: "/api/schemas/sample_movie",
            history: "/api/history/sample_movie/1",
            cover: "?m=files&raw=thumbnail&document_id=42916&thumb=0"
        }
        expect(movie.detailLink).toEqual("")
    }

    public testMovieNotEmptyDetailLink () {
        const movie = new SampleMovie()
        movie.links = {
            self: "/api/sample/movies/1",
            schema: "/api/schemas/sample_movie",
            history: "/api/history/sample_movie/1",
            cover: "?m=files&raw=thumbnail&document_id=42916&thumb=0",
            self_legacy: "?m=sample&tab=displayMovieDetails&sample_movie_id=1"
        }
        expect(movie.detailLink).toEqual("?m=sample&tab=displayMovieDetails&sample_movie_id=1")
    }
}

(new SampleMovieTest()).launchTests()
