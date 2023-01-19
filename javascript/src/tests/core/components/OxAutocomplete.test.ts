/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import { Component } from "vue"
import { shallowMount } from "@vue/test-utils"
import oxApiService from "@/core/utils/OxApiService"
import OxAutocomplete from "@/core/components/OxAutocomplete/OxAutocomplete.vue"
import Vuetify from "vuetify"
import OxObject from "@/core/models/OxObject"

/* eslint-disable dot-notation */

const mockPostList = {
    data: [
        {
            type: "sample_movie",
            id: "309",
            attributes: {
                name: "La French",
                release: "2014-12-03",
                duration: "02:15:00",
                csa: null,
                languages: "fr"
            },
            links: {
                self: "/mediboard/api/sample/movies/309",
                schema: "/mediboard/api/schemas/sample_movie",
                history: "/mediboard/api/history/sample_movie/309",
                cover: "?m=files&raw=thumbnail&document_id=662605&thumb=0",
                self_legacy: "?m=sample&tab=displayMovieDetails&sample_movie_id=309"
            }
        },
        {
            type: "sample_movie",
            id: "16",
            attributes: {
                name: "Suonno d'ammore modifié",
                release: "1955-07-17",
                duration: "02:31:00",
                csa: "18",
                languages: "it"
            },
            links: {
                self: "/mediboard/api/sample/movies/16",
                schema: "/mediboard/api/schemas/sample_movie",
                history: "/mediboard/api/history/sample_movie/16",
                cover: "?m=files&raw=thumbnail&document_id=660734&thumb=0",
                self_legacy: "?m=sample&tab=displayMovieDetails&sample_movie_id=16"
            }
        },
        {
            type: "sample_movie",
            id: "17",
            attributes: {
                name: "Fin d'été",
                release: "1999-03-17",
                duration: "01:05:00",
                csa: null,
                languages: "fr"
            },
            links: {
                self: "/mediboard/api/sample/movies/17",
                schema: "/mediboard/api/schemas/sample_movie",
                history: "/mediboard/api/history/sample_movie/17",
                cover: "?m=files&raw=thumbnail&document_id=660735&thumb=0",
                self_legacy: "?m=sample&tab=displayMovieDetails&sample_movie_id=17"
            }
        },
        {
            type: "sample_movie",
            id: "19",
            attributes: {
                name: "The Dandy Lion",
                release: "1940-09-19",
                duration: "00:06:00",
                csa: null,
                languages: "fr"
            },
            links: {
                self: "/mediboard/api/sample/movies/19",
                schema: "/mediboard/api/schemas/sample_movie",
                history: "/mediboard/api/history/sample_movie/19",
                cover: "?m=files&raw=thumbnail&document_id=660737&thumb=0",
                self_legacy: "?m=sample&tab=displayMovieDetails&sample_movie_id=19"
            }
        }
    ],
    meta: {
        date: "2022-09-12 11:07:50+02:00",
        copyright: "OpenXtrem-2022",
        authors: "dev@openxtrem.com",
        count: 4,
        total: 282
    },
    links: {
        self: "http://localhost/mediboard/api/sample/movies?limit=4&offset=0",
        next: "http://localhost/mediboard/api/sample/movies?limit=4&offset=4",
        first: "http://localhost/mediboard/api/sample/movies?limit=4&offset=0",
        last: "http://localhost/mediboard/api/sample/movies?limit=4&offset=280"
    }
}
jest.spyOn(oxApiService, "get").mockImplementation(() => Promise.resolve({ data: mockPostList }))

/**
 * Test pour OxAutocomplete
 */
export default class OxAutocompleteTest extends OxTest {
    protected component = OxAutocomplete

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
                slots: slots,
                vuetify: new Vuetify()
            }
        )
    }

    protected afterTest () {
        super.afterTest()
        jest.clearAllMocks()
    }

    public testIsAutocompleteComponentByDefault () {
        const object = new OxObject()
        const autocomplete = this.mountComponent({ value: object, url: "test" })
        expect(autocomplete.vm["autocompleteComponent"]).toEqual("v-autocomplete")
    }

    public testIsComboboxComponentWhenSearchable () {
        const object = new OxObject()
        const autocomplete = this.mountComponent({ value: object, url: "test", searchable: true })
        expect(autocomplete.vm["autocompleteComponent"]).toEqual("v-combobox")
    }

    public testHasItem () {
        const autocomplete = this.mountComponent(
            { value: new OxObject(), url: "test" },
            {},
            { item: "<ox-object-autocomplete />" }
        )
        expect(autocomplete.vm["hasItem"]).toBeTruthy()
    }

    public testHasNotItemByDefault () {
        const autocomplete = this.mountComponent(
            { value: new OxObject(), url: "test" }
        )
        expect(autocomplete.vm["hasItem"]).toBeFalsy()
    }

    public testHasSelection () {
        const autocomplete = this.mountComponent(
            { value: new OxObject(), url: "test" },
            {},
            { selection: "<ox-object-selection />" }
        )
        expect(autocomplete.vm["hasSelection"]).toBeTruthy()
    }

    public testHasNotSelectionByDefault () {
        const autocomplete = this.mountComponent(
            { value: new OxObject(), url: "test" }
        )
        expect(autocomplete.vm["hasSelection"]).toBeFalsy()
    }

    public testAutocompleteNotClearableByDefault () {
        const autocomplete = this.mountComponent(
            { value: new OxObject(), url: "test" }
        )
        expect(autocomplete.vm["isClearable"]).toBeFalsy()
    }

    public testAutocompleteNotClearableIfNoValue () {
        const autocomplete = this.mountComponent(
            { value: new OxObject(), url: "test", clearable: true }
        )
        expect(autocomplete.vm["isClearable"]).toBeFalsy()
    }

    public async testClearableAutocomplete () {
        const autocomplete = this.mountComponent(
            { value: new OxObject(), url: "test", clearable: true }
        )
        autocomplete.vm["input"]("test")
        await autocomplete.vm.$nextTick()
        expect(autocomplete.vm["isClearable"]).toBeTruthy()
    }

    public testShowMenuByDefault () {
        const autocomplete = this.mountComponent(
            { value: new OxObject(), url: "test" }
        )
        expect(autocomplete.vm["autocompleteClasses"]).toBe("")
    }

    public testHideMenuOnEnterKey () {
        const autocomplete = this.mountComponent(
            { value: new OxObject(), url: "test" }
        )
        autocomplete.vm["enterDown"]()
        expect(autocomplete.vm["autocompleteClasses"]).toBe("hideMenu")
    }

    public async testShowMenuOnSearch () {
        const autocomplete = this.mountComponent(
            { value: new OxObject(), url: "test" }
        )
        autocomplete.vm["enterDown"]()
        autocomplete.vm["search"] = "Test"
        await autocomplete.vm.$nextTick()
        expect(autocomplete.vm["autocompleteClasses"]).toBe("")
    }

    public async testDebounceLoadItems () {
        const autocomplete = this.mountComponent(
            { value: new OxObject(), url: "test" }
        )
        // @ts-ignore
        const spy = jest.spyOn(autocomplete.vm, "loadItems")
        autocomplete.vm["searchItems"] = jest.fn()
        autocomplete.vm["search"] = "T"
        autocomplete.vm["search"] = "Tes"
        autocomplete.vm["search"] = "Test"
        await new Promise(resolve => setTimeout(resolve, 500))
        autocomplete.vm["search"] = "Test2"
        await autocomplete.vm.$nextTick()
        expect(spy).toBeCalledTimes(2)
    }

    public async testAutocompleteEmitInputOnChange () {
        const autocomplete = this.mountComponent(
            { value: new OxObject(), url: "test" }
        )
        const objectSelected = new OxObject()
        objectSelected.id = "1"
        autocomplete.vm["input"](objectSelected)
        await autocomplete.vm.$nextTick()
        expect(autocomplete.emitted().input).toBeTruthy()
        expect(autocomplete.emitted().input).toEqual([[objectSelected]])
    }

    public async testSearchableEmitInputWhenObjectSelected () {
        const autocomplete = this.mountComponent(
            { value: new OxObject(), url: "test", searchable: true }
        )
        const objectSelected = new OxObject()
        objectSelected.id = "1"
        autocomplete.vm["input"](objectSelected)
        await autocomplete.vm.$nextTick()
        expect(autocomplete.emitted().input).toBeTruthy()
        expect(autocomplete.emitted().input).toEqual([[objectSelected]])
    }

    public async testSearchableDoesNotEmitOnSearch () {
        const autocomplete = this.mountComponent(
            { value: new OxObject(), url: "test", searchable: true }
        )
        autocomplete.vm["input"]("search label")
        await autocomplete.vm.$nextTick()
        expect(autocomplete.emitted().input).toBeFalsy()
    }

    public async testSearchableEmitSearchOnEnter () {
        const autocomplete = this.mountComponent(
            { value: new OxObject(), url: "test", searchable: true }
        )
        autocomplete.vm["search"] = "Search label"
        autocomplete.vm["enterDown"]()
        await autocomplete.vm.$nextTick()
        expect(autocomplete.emitted().enter).toBeTruthy()
        expect(autocomplete.emitted().enter).toEqual([["Search label"]])
    }

    public async testKeepSelectedItemOnBlur () {
        const objectSelected = new OxObject()
        objectSelected.id = "1"
        const autocomplete = this.mountComponent(
            { url: "test", itemText: "id", value: objectSelected }
        )
        autocomplete.vm["onBlur"]()
        await autocomplete.vm.$nextTick()
        expect(autocomplete.vm["itemsData"]).toEqual([objectSelected])
    }

    public async testItemsWatcher () {
        const objectSelected1 = new OxObject()
        objectSelected1.id = "1"
        const objectSelected2 = new OxObject()
        objectSelected2.id = "2"
        const autocomplete = this.mountComponent(
            { value: new OxObject(), url: "test", items: [objectSelected1, objectSelected2] }
        )
        await autocomplete.vm.$nextTick()
        expect(autocomplete.vm["itemsData"]).toEqual([objectSelected1, objectSelected2])
    }

    public testAutocompleteWithDefaultValue () {
        const objectSelected = new OxObject()
        objectSelected.id = "1"
        const autocomplete = this.mountComponent({ url: "test", value: objectSelected })
        expect(autocomplete.vm["itemsData"]).toEqual([objectSelected])
    }

    public testAutocompleteWithDefaultValues () {
        const objectSelected1 = new OxObject()
        objectSelected1.id = "1"
        const objectSelected2 = new OxObject()
        objectSelected2.id = "2"
        const autocomplete = this.mountComponent({ url: "test", value: [objectSelected1, objectSelected2], oxObject: OxObject })
        expect(autocomplete.vm["itemsData"]).toEqual([objectSelected1, objectSelected2])
    }

    public testAutocompleteWithSpecifiedOxObject () {
        const autocomplete = this.mountComponent(
            { url: "test", oxObject: OxObject }
        )
        expect(autocomplete.vm["objectClass"]).toEqual(OxObject)
    }

    public testAutoTypeInferenceFromDefaultValue () {
        const autocomplete = this.mountComponent(
            { url: "test", value: new OxObject() }
        )
        expect(autocomplete.vm["objectClass"]).toEqual(OxObject)
    }

    public testImpossibleAutoTypeInference () {
        // Ignore console.error from created
        // @ts-ignore
        window.console.error = jest.fn()
        expect(() => this.mountComponent(
            { url: "test" }
        )).toThrowError(new Error("Impossible type inference"))
    }

    public async testNoSearchOnEmptyValue () {
        const autocomplete = this.mountComponent(
            { url: "/test", value: new OxObject() }
        )
        await autocomplete.vm["searchItems"]("")
        expect(oxApiService.get).not.toBeCalled()
    }

    public async testNoSearchWithMinChar () {
        const autocomplete = this.mountComponent(
            { url: "/test", value: new OxObject(), minCharSearch: 5 }
        )
        await autocomplete.vm["searchItems"]("test")
        expect(oxApiService.get).not.toBeCalled()
    }

    public async testCallOnSearchAttributeByDefault () {
        const autocomplete = this.mountComponent(
            { url: "/test", value: new OxObject() }
        )
        await autocomplete.vm["searchItems"]("test")
        expect(oxApiService.get).toBeCalledWith(expect.stringContaining("/test?limit=5&search=test"))
    }

    public async testCallOnSpecificField () {
        const autocomplete = this.mountComponent(
            { url: "/test", value: new OxObject(), searchField: "specificField" }
        )
        await autocomplete.vm["searchItems"]("test")
        expect(oxApiService.get).toBeCalledWith(expect.stringContaining("/test?limit=5&filter=specificField.contains.test"))
    }

    public async testMergeApiResponseWithValue () {
        const object1 = new OxObject()
        object1.id = "1"
        const object2 = new OxObject()
        object2.id = "2"
        const autocomplete = this.mountComponent(
            { url: "/test", multiple: true, value: [object1, object2], oxObject: OxObject }
        )
        await autocomplete.vm["searchItems"]("test")
        expect(autocomplete.vm["itemsData"]).toHaveLength(6)
    }

    public async testGetResultFromApi () {
        const autocomplete = this.mountComponent({ url: "/test", value: new OxObject() })
        await autocomplete.vm["searchItems"]("test")
        expect(autocomplete.vm["itemsData"]).toHaveLength(4)
    }

    public testRulesForNotNullAutocomplete () {
        const autocomplete = this.mountComponent({ url: "/test", value: new OxObject(), notNull: true })
        expect(autocomplete.vm["rules"]).toBeInstanceOf(Array)
        expect(autocomplete.vm["rules"]).toHaveLength(1)
    }

    public testRulesForNullableAutocomplete () {
        const autocomplete = this.mountComponent({ url: "/test", value: new OxObject() })
        expect(autocomplete.vm["rules"]).toBeInstanceOf(Array)
        expect(autocomplete.vm["rules"]).toHaveLength(0)
    }

    public testLabelNotNullAutocomplete () {
        const autocomplete = this.mountComponent({ url: "/test", value: new OxObject(), notNull: true, label: "Test" })
        expect(autocomplete.vm["decoratedLabel"]).toBe("Test *")
    }

    public testLabelNullableAutocomplete () {
        const autocomplete = this.mountComponent({ url: "/test", value: new OxObject(), label: "Test" })
        expect(autocomplete.vm["decoratedLabel"]).toBe("Test")
    }
}

(new OxAutocompleteTest()).launchTests()
