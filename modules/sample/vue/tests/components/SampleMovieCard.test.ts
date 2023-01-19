/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import SampleMovieCard from "@modules/sample/vue/components/SampleMovieCard/SampleMovieCard.vue"
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
 * SampleMovieCard tests
 */
export default class SampleMovieCardTest extends OxTest {
    protected component = SampleMovieCard

    private movie1 = new SampleMovie()
    private movie2 = new SampleMovie()
    private category = new SampleCategory()

    protected beforeAllTests () {
        super.beforeAllTests()

        this.movie1.id = "1"
        this.movie1.type = "sample_movie"
        this.movie1.attributes = {
            name: "Test film",
            release: "2009-01-01",
            duration: "01:35:00",
            csa: "18",
            languages: "es"
        }
        this.movie1.relationships = {
            category: {
                data: {
                    type: "sample_category",
                    id: "37"
                }
            }
        }
        this.movie1.links = {
            self: "/api/sample/movies/1",
            schema: "/api/schemas/sample_movie",
            history: "/api/history/sample_movie/1",
            cover: "?m=files&raw=thumbnail&document_id=42916&thumb=0",
            self_legacy: "?m=sample&tab=displayMovieDetails&sample_movie_id=1"
        }

        this.movie2.id = "2"
        this.movie2.type = "sample_movie"
        this.movie2.attributes = {
            name: "Test film",
            release: "2004-01-01",
            duration: "01:30:00",
            csa: "16",
            languages: "fr"
        }
        this.movie2.relationships = {
            category: {
                data: {
                    type: "sample_category",
                    id: "37"
                }
            }
        }
        this.movie2.links = {
            self: "/api/sample/movies/2",
            schema: "/api/schemas/sample_movie",
            history: "/api/history/sample_movie/2",
            cover: "?m=files&raw=thumbnail&document_id=42916&thumb=0",
            self_legacy: "?m=sample&tab=displayMovieDetails&sample_movie_id=2"
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

    public testSampleMovieCardCreation () {
        const movieCard = this.mountComponent({ movie: this.movie1 })

        this.assertEqual(movieCard.vm["movie"].title, "Test film")
    }

    public testCsaIsShowed () {
        const movieCard = this.vueComponent({ movie: this.movie1 })
        this.assertTrue(this.privateCall(movieCard, "showCSA"))
    }

    public testCsaIsNotShowed () {
        const movieCard = this.vueComponent({ movie: this.movie2 })
        this.assertFalse(this.privateCall(movieCard, "showCSA"))
    }
}

(new SampleMovieCardTest()).launchTests()
