/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import SampleMovieForm from "@modules/sample/vue/components/SampleMovieForm/SampleMovieForm.vue"
import { Component } from "vue"
import { createLocalVue, shallowMount } from "@vue/test-utils"
import pinia from "@/core/plugins/OxPiniaCore"
import OxTranslator from "@/core/plugins/OxTranslator"
import SampleMovie from "@modules/sample/vue/models/SampleMovie"
import { setActivePinia } from "pinia"
import { OxSchema } from "@/core/types/OxSchema"
import { storeSchemas } from "@/core/utils/OxStorage"
import SampleCasting from "@modules/sample/vue/models/SampleCasting"
import SamplePerson from "@modules/sample/vue/models/SamplePerson"

const localVue = createLocalVue()
localVue.use(OxTranslator)

/* eslint-disable dot-notation */

/**
 * SampleMovieForm tests
 */
export default class SampleMovieFormTest extends OxTest {
    protected component = SampleMovieForm

    private movie = new SampleMovie()
    private casting1 = new SampleCasting()
    private casting2 = new SampleCasting()

    protected beforeAllTests () {
        super.beforeAllTests()

        const schema = [
            {
                id: "429b23f96004afaa4ee3b31e7ca34c0e",
                owner: "sample_movie",
                field: "name",
                type: "str",
                fieldset: "default",
                autocomplete: null,
                placeholder: null,
                notNull: true,
                confidential: null,
                default: null,
                libelle: "Titre",
                label: "Titre",
                description: "Titre du film"
            },
            {
                id: "0a9092e87e8ddb890799273a6c8e8596",
                owner: "sample_movie",
                field: "release",
                type: "date",
                fieldset: "default",
                autocomplete: null,
                placeholder: null,
                notNull: true,
                confidential: null,
                default: null,
                libelle: "Date de sortie",
                label: "Sortie",
                description: "Date de sortie du film"
            },
            {
                id: "efd8ccabedcdca0b495941110eb85d63",
                owner: "sample_movie",
                field: "duration",
                type: "time",
                fieldset: "default",
                autocomplete: null,
                placeholder: null,
                notNull: true,
                confidential: null,
                default: null,
                libelle: "Durée",
                label: "Durée",
                description: "Durée du film"
            },
            {
                id: "19795aefbd316133ad109635c8dad0ae",
                owner: "sample_movie",
                field: "csa",
                type: "enum",
                fieldset: "default",
                autocomplete: null,
                placeholder: null,
                notNull: null,
                confidential: null,
                default: null,
                values: [
                    "10",
                    "12",
                    "16",
                    "18"
                ],
                translations: {
                    10: "-10",
                    12: "-12",
                    16: "-16",
                    18: "-18"
                },
                libelle: "Csa",
                label: "Signalétique jeunesse",
                description: "Signalétique jeunesse du film"
            },
            {
                id: "99457059e40536032606aff4a4d8ff33",
                owner: "sample_movie",
                field: "languages",
                type: "set",
                fieldset: "default",
                autocomplete: null,
                placeholder: null,
                notNull: null,
                confidential: null,
                default: "fr",
                values: [
                    "en",
                    "es",
                    "fr",
                    "ger",
                    "it"
                ],
                translations: {
                    en: "Anglais",
                    es: "Espagnõl",
                    fr: "Français",
                    ger: "Allemand",
                    it: "Italien"
                },
                libelle: "Langues",
                label: "Langues",
                description: "Langues disponibles"
            },
            {
                id: "847f7aa830e06013fa44298843f20aae",
                owner: "sample_movie",
                field: "description",
                type: "text",
                fieldset: "details",
                autocomplete: null,
                placeholder: null,
                notNull: null,
                confidential: null,
                default: null,
                libelle: "Synopsis",
                label: "Synopsis",
                description: "Synopsis du film"
            }
        ] as unknown as OxSchema[]

        setActivePinia(pinia)
        storeSchemas(schema)

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

        this.casting1.id = "1"
        this.casting1.type = "sample_casting"
        this.casting1.attributes = {
            is_main_actor: false
        }
        this.casting1.links = {
            self: "null",
            schema: "/api/schemas/sample_casting",
            history: "/api/history/sample_casting/353"
        }

        this.casting2.id = "2"
        this.casting2.type = "sample_casting"
        this.casting2.attributes = {
            is_main_actor: true
        }
        this.casting2.links = {
            self: "null",
            schema: "/api/schemas/sample_casting",
            history: "/api/history/sample_casting/352"
        }

        const actor1 = new SamplePerson()
        actor1.id = "1242"
        this.casting1.actor = actor1
        const actor2 = new SamplePerson()
        actor2.id = "1241"
        this.casting2.actor = actor2
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

    public testEditTitle (): void {
        const movieForm = this.vueComponent({ movie: this.movie, personsUrl: "/api/sample/persons" })
        this.assertEqual(this.privateCall(movieForm, "title"), "CSampleMovie-title-modify")
    }

    public testCreateTitle (): void {
        const movieForm = this.vueComponent({ personsUrl: "/api/sample/persons", movie: new SampleMovie() })
        this.assertEqual(this.privateCall(movieForm, "title"), "CSampleMovie-title-create")
    }

    public testRemoveExistingCasting (): void {
        const movieForm = this.mountComponent({
            movie: this.movie,
            casting: [this.casting1, this.casting2],
            personsUrl: "/api/sample/persons"
        })
        this.privateCall(movieForm.vm, "removeCasting", this.casting1)
        this.assertEqual(movieForm.vm["newCastings"], [this.casting2])
    }

    public testRemoveNotExistingCasting (): void {
        const movieForm = this.mountComponent({
            movie: this.movie,
            casting: [this.casting1],
            personsUrl: "/api/sample/persons"
        })
        this.privateCall(movieForm.vm, "removeCasting", this.casting2)
        this.assertEqual(movieForm.vm["newCastings"], [this.casting1])
    }

    public testUpdateValidDuration (): void {
        const movieForm = this.mountComponent({ movie: this.movie, personsUrl: "/api/sample/persons" })
        this.privateCall(movieForm.vm, "updateDuration", "01:45")
        this.assertEqual(movieForm.vm["movieMutated"].duration, "01:45:00")
    }

    public testUpdateInvalidDuration (): void {
        const movieForm = this.mountComponent({ movie: this.movie, personsUrl: "/api/sample/persons" })
        this.privateCall(movieForm.vm, "updateDuration", "01:45:00")
        this.assertEqual(movieForm.vm["movieMutated"].duration, "01:35:00")
    }

    public testCloseFormEvent (): void {
        const movieForm = this.mountComponent({ movie: this.movie, personsUrl: "/api/sample/persons" })
        this.privateCall(movieForm.vm, "closeForm")
        this.assertTrue(movieForm.emitted("close"))
    }
}

(new SampleMovieFormTest()).launchTests()
