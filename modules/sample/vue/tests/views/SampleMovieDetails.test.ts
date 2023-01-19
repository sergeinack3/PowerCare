/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import SampleMovieDetails from "@modules/sample/vue/views/SampleMovieDetails/SampleMovieDetails.vue"
import { Component } from "vue"
import { createLocalVue, shallowMount } from "@vue/test-utils"
import pinia from "@/core/plugins/OxPiniaCore"
import OxTranslator from "@/core/plugins/OxTranslator"
import oxApiService from "@/core/utils/OxApiService"
import { setActivePinia } from "pinia"

const localVue = createLocalVue()
localVue.use(OxTranslator)

/* eslint-disable dot-notation */

const mockMovie = {
    data: {
        type: "sample_movie",
        id: "27",
        attributes: {
            name: "Titre film",
            release: "2016-11-11",
            duration: "01:57:00",
            csa: "18",
            languages: "fr",
            description: "Description du film."
        },
        relationships: {
            actors: {
                data: [
                    {
                        type: "sample_person",
                        id: "1645"
                    },
                    {
                        type: "sample_person",
                        id: "1646"
                    }
                ]
            },
            director: {
                data: {
                    type: "sample_person",
                    id: "1644"
                }
            },
            category: {
                data: {
                    type: "sample_category",
                    id: "16"
                }
            }
        },
        links: {
            self: "/api/sample/movies/27",
            schema: "/api/schemas/sample_movie?fieldsets=default,details",
            history: "/api/history/sample_movie/27",
            cover: "?m=files&raw=thumbnail&document_id=42166&thumb=0",
            self_legacy: "?m=sample&tab=displayMovieDetails&sample_movie_id=27",
            casting: "/api/sample/movies/27/casting"
        },
        meta: {
            permissions: {
                perm: "edit"
            }
        }
    },
    meta: {
        date: "2022-09-15 10:43:37+02:00",
        copyright: "OpenXtrem-2022",
        authors: "dev@openxtrem.com",
        schema: [
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
        ]
    },
    included: [
        {
            type: "sample_person",
            id: "1645",
            attributes: {
                last_name: "Haniszewski",
                first_name: "Miroslaw",
                is_director: false
            },
            links: {
                profile_picture: "?m=files&raw=thumbnail&document_id=44287&thumb=0"
            }
        },
        {
            type: "sample_person",
            id: "1646",
            attributes: {
                last_name: "Jakubik",
                first_name: "Arkadiusz",
                is_director: false
            },
            links: {
                profile_picture: "?m=files&raw=thumbnail&document_id=44288&thumb=0"
            }
        },
        {
            type: "sample_person",
            id: "1644",
            attributes: {
                last_name: "Pieprzyca",
                first_name: "Maciej",
                is_director: true
            },
            links: {
                profile_picture: "?m=files&raw=thumbnail&document_id=44286&thumb=0"
            }
        }
    ]
}

const mockCasting = {
    data: [
        {
            type: "sample_casting",
            id: "68",
            attributes: {
                actor_id: "1646",
                movie_id: "27",
                is_main_actor: false
            },
            relationships: {
                actor: {
                    data: {
                        type: "sample_person",
                        id: "1646"
                    }
                }
            },
            links: {
                self: null,
                schema: "/api/schemas/sample_casting",
                history: "/api/history/sample_casting/68"
            }
        },
        {
            type: "sample_casting",
            id: "67",
            attributes: {
                actor_id: "1645",
                movie_id: "27",
                is_main_actor: true
            },
            relationships: {
                actor: {
                    data: {
                        type: "sample_person",
                        id: "1645"
                    }
                }
            },
            links: {
                self: null,
                schema: "/api/schemas/sample_casting",
                history: "/api/history/sample_casting/67"
            }
        }
    ],
    meta: {
        date: "2022-09-15 11:39:14+02:00",
        copyright: "OpenXtrem-2022",
        authors: "dev@openxtrem.com",
        count: 2,
        total: 2
    },
    links: {
        self: "http://localhost/api/sample/movies/27/casting?limit=50&offset=0&relations=actor",
        first: "http://localhost/api/sample/movies/27/casting?limit=50&offset=0&relations=actor",
        last: "http://localhost/api/sample/movies/27/casting?limit=50&offset=0&relations=actor"
    },
    included: [
        {
            type: "sample_person",
            id: "1646",
            attributes: {
                last_name: "Jakubik",
                first_name: "Arkadiusz",
                is_director: false
            },
            links: {
                profile_picture: "?m=files&raw=thumbnail&document_id=44288&thumb=0"
            }
        },
        {
            type: "sample_person",
            id: "1645",
            attributes: {
                last_name: "Haniszewski",
                first_name: "Miroslaw",
                is_director: false
            },
            links: {
                profile_picture: "?m=files&raw=thumbnail&document_id=44287&thumb=0"
            }
        }
    ]
}
jest.spyOn(oxApiService, "get")
    .mockResolvedValueOnce({ data: mockMovie })
    .mockResolvedValueOnce({ data: mockCasting })

/**
 * SampleMovieDetails tests
 */
export default class SampleMovieDetailsTest extends OxTest {
    protected component = SampleMovieDetails

    protected beforeAllTests () {
        super.beforeAllTests()

        setActivePinia(pinia)
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

    public async testSampleMovieDetailsCreation () {
        const sampleMovieDetails = this.mountComponent({
            links: {
                movie: "/api/sample/movies/27?fieldsets=all&relations=category,director&permissions=1",
                casting: "/api/sample/movies/27/casting?relations=actor",
                back: "?m=sample&tab=displayMovies",
                categories: "/api/sample/categories",
                nationalities: "/api/sample/nationalities",
                persons: "/api/sample/persons"
            }
        })

        await sampleMovieDetails.vm.$nextTick()
        expect(oxApiService.get).toBeCalledWith(
            expect.stringContaining(
                "/api/sample/movies/27?fieldsets=default%2Cdetails&relations=actors%2Cdirector%2Ccategory&permissions=1&schema=true"
            )
        )

        await sampleMovieDetails.vm.$nextTick()
        expect(oxApiService.get).toBeCalledWith(
            expect.stringContaining(
                "/api/sample/movies/27/casting?relations=actor"
            )
        )
    }
}

(new SampleMovieDetailsTest()).launchTests()
