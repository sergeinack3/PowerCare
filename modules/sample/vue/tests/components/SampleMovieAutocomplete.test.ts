/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import SampleMovieAutocomplete from "@modules/sample/vue/components/SampleMovieAutocomplete/SampleMovieAutocomplete.vue"
import { Component } from "vue"
import { createLocalVue, shallowMount } from "@vue/test-utils"
import pinia from "@/core/plugins/OxPiniaCore"
import OxTranslator from "@/core/plugins/OxTranslator"
import SampleMovie from "@modules/sample/vue/models/SampleMovie"
import { storeObject } from "@/core/utils/OxStorage"
import { setActivePinia } from "pinia"
import SampleCategory from "@modules/sample/vue/models/SampleCategory"

const localVue = createLocalVue()
localVue.use(OxTranslator)

/* eslint-disable dot-notation */

/**
 * SampleMovieAutocomplete tests
 */
export default class SampleMovieAutocompleteTest extends OxTest {
    protected component = SampleMovieAutocomplete

    private movie = new SampleMovie()
    private category = new SampleCategory()

    protected beforeAllTests () {
        super.beforeAllTests()

        this.movie.id = "1"
        this.movie.type = "sample_movie"
        this.movie.attributes = {
            name: "Test film",
            release: "2009-01-01",
            duration: "01:35:00",
            csa: null,
            languages: "es"
        }
        this.movie.relationships = {
            category: {
                data: {
                    type: "sample_category",
                    id: "37"
                }
            }
        }
        this.movie.links = {
            self: "/api/sample/movies/1",
            schema: "/api/schemas/sample_movie",
            history: "/api/history/sample_movie/1",
            cover: "?m=files&raw=thumbnail&document_id=42916&thumb=0",
            self_legacy: "?m=sample&tab=displayMovieDetails&sample_movie_id=1"
        }

        this.category.id = "37"
        this.category.type = "sample_category"
        this.category.attributes = {
            name: "Drame",
            color: "D67846",
            active: true
        }

        setActivePinia(pinia)
        storeObject(this.category)
    }

    /**
     * @inheritDoc
     */
    protected mountComponent (
        props: object = {},
        stubs: { [key: string]: Component | string | boolean } | string[] = {},
        slots: { [key: string]: (Component | string)[] | Component | string } = {}
    ) {
        return shallowMount(
            this.component,
            {
                propsData: props,
                mocks: {},
                slots: slots,
                stubs: stubs,
                methods: {},
                localVue,
                pinia
            }
        )
    }

    /**
     * @inheritDoc
     */
    protected vueComponent (
        props: object = {},
        stubs: { [key: string]: Component | string | boolean } | string[] = {},
        slots: { [key: string]: (Component | string)[] | Component | string } = {}
    ) {
        return this.mountComponent(props, stubs, slots).vm
    }

    public testSampleMovieLineDefault () {
        const movieAutocomplete = this.mountComponent({ movie: this.movie })

        this.assertEqual(movieAutocomplete.vm["movie"].title, "Test film")
    }
}

(new SampleMovieAutocompleteTest()).launchTests()
