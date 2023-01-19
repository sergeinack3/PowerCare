/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest, OxThemeCore } from "oxify"
import OxDatagrid from "@/core/components/OxDatagrid/OxDatagrid.vue"
import { Component } from "vue"
import { createLocalVue, shallowMount } from "@vue/test-utils"
import pinia from "@/core/plugins/OxPiniaCore"
import OxTranslator from "@/core/plugins/OxTranslator"
import OxCollection from "@/core/models/OxCollection"
import { OxUrlBuilder } from "@/core/utils/OxUrlTools"
import { OxCollectionLinks, OxCollectionMeta } from "@/core/types/OxCollectionTypes"
import { storeSchemas } from "@/core/utils/OxStorage"
import { OxSchema } from "@/core/types/OxSchema"
import { setActivePinia } from "pinia"
import OxObject from "@/core/models/OxObject"
import { tr } from "@/core/utils/OxTranslator"

const localVue = createLocalVue()
localVue.use(OxTranslator)

/* eslint-disable dot-notation */

/**
 * Test pour OxDatagrid
 */
export default class OxDatagridTest extends OxTest {
    protected component = OxDatagrid

    private columns = [
        {
            text: "Acteur",
            value: "fullName",
            filterValues: ["first_name", "last_name"]
        },
        {
            text: "Réalisateur",
            value: "isDirectorString",
            filterValues: "is_director"
        },
        {
            value: "sex"
        },
        {
            value: "birthdate"
        },
        {
            text: "Nationalité",
            value: "nationality.name",
            filterValues: ""
        },
        {
            value: "activityStart"
        }
    ]

    private personsCollection = new OxCollection<SamplePerson>()
    private personsCollection2 = new OxCollection<SamplePerson>()
    private personsCollection3 = new OxCollection<SamplePerson>()
    private person1 = new SamplePerson()
    private person2 = new SamplePerson()

    protected beforeAllTests () {
        super.beforeAllTests()
        const schema = [
            {
                id: "489f3046fbdf81481652a4b19b45a25c",
                owner: "sample_person",
                field: "last_name",
                type: "str",
                fieldset: "default",
                autocomplete: null,
                placeholder: null,
                notNull: true,
                confidential: null,
                default: null,
                libelle: "Nom",
                label: "Nom",
                description: "Nom de famille"
            },
            {
                id: "9b835cdbdf32d4ba811093336b069970",
                owner: "sample_person",
                field: "first_name",
                type: "str",
                fieldset: "default",
                autocomplete: null,
                placeholder: null,
                notNull: true,
                confidential: null,
                default: null,
                libelle: "Prénom",
                label: "Prénom",
                description: "Prénom"
            },
            {
                id: "0f18b60317532cf3493bc79132667421",
                owner: "sample_person",
                field: "is_director",
                type: "bool",
                fieldset: "default",
                autocomplete: null,
                placeholder: null,
                notNull: null,
                confidential: null,
                default: "0",
                libelle: "Réalisateur",
                label: "Réalisateur",
                description: "Est un réalisateur"
            },
            {
                id: "6f9a18d762968f4c7af7d50581bee48f",
                owner: "sample_person",
                field: "birthdate",
                type: "birthDate",
                fieldset: "extra",
                autocomplete: null,
                placeholder: "99/99/9999",
                notNull: null,
                confidential: null,
                default: null,
                libelle: "Date de naissance",
                label: "Naissance",
                description: "Date de naissance"
            },
            {
                id: "b187c3c5dca8287f28623e931943509a",
                owner: "sample_person",
                field: "sex",
                type: "enum",
                fieldset: "extra",
                autocomplete: null,
                placeholder: null,
                notNull: null,
                confidential: null,
                default: null,
                values: [
                    "m",
                    "f"
                ],
                translations: {
                    m: "Masculin",
                    f: "Féminin"
                },
                libelle: "Sexe",
                label: "Sexe",
                description: "Sexe"
            },
            {
                id: "1c1f00ed017758b7de04174e5a21177f",
                owner: "sample_person",
                field: "activity_start",
                type: "date",
                fieldset: "extra",
                autocomplete: null,
                placeholder: null,
                notNull: null,
                confidential: null,
                default: null,
                libelle: "Début d'activité",
                label: "Début d'activité",
                description: "Date de début d'activité"
            }
        ] as unknown as OxSchema[]

        this.person1.id = "185"
        this.person1.type = "sample_person"
        this.person1.attributes = {
            last_name: "Lambertini",
            first_name: "Lucia",
            is_director: false,
            birthdate: "1926-06-26",
            sex: "f",
            activity_start: "1998-03-03"
        }
        this.person1.relationships = {
            nationality: {
                data: {
                    type: "sample_nationality",
                    id: "17"
                }
            }
        }
        this.person1.links = {
            self: "/api/sample/persons/185",
            schema: "/api/schemas/sample_person?fieldsets=default,extra",
            history: "/api/history/sample_person/185",
            profile_picture: "?m=files&raw=thumbnail&document_id=36909&thumb=0"
        }
        this.person1.meta = {
            permissions: {
                perm: "edit"
            }
        }

        this.person2.id = "168"
        this.person2.type = "sample_person"
        this.person2.attributes = {
            last_name: "Romance",
            first_name: "Viviane",
            is_director: true,
            birthdate: "1912-07-04",
            sex: "f",
            activity_start: "1998-03-03"
        }
        this.person2.relationships = {
            nationality: {
                data: {
                    type: "sample_nationality",
                    id: "113"
                }
            }
        }
        this.person2.links = {
            self: "/api/sample/persons/168",
            schema: "/api/schemas/sample_person?fieldsets=default,extra",
            history: "/api/history/sample_person/168",
            profile_picture: "?m=files&raw=thumbnail&document_id=36892&thumb=0"
        }
        this.person2.meta = {
            permissions: {
                perm: "edit"
            }
        }

        this.personsCollection.objects = [
            this.person1,
            this.person2
        ] as unknown as SamplePerson[]
        this.personsCollection.links = {
            self: "http://localhost/api/sample/persons?fieldsets=default,extra&limit=2&offset=0&permissions=true&relations=nationality&schema=true",
            next: "http://localhost/api/sample/persons?fieldsets=default,extra&limit=2&offset=2&permissions=true&relations=nationality&schema=true",
            first: "http://localhost/api/sample/persons?fieldsets=default,extra&limit=2&offset=0&permissions=true&relations=nationality&schema=true",
            last: "http://localhost/api/sample/persons?fieldsets=default,extra&limit=2&offset=538&permissions=true&relations=nationality&schema=true"
        } as OxCollectionLinks
        this.personsCollection.meta = {
            date: "2022-08-01 16:50:12+02:00",
            copyright: "OpenXtrem-2022",
            authors: "dev@openxtrem.com",
            count: 2,
            schema: schema,
            total: 539
        } as OxCollectionMeta

        this.personsCollection2.objects = [
            this.person1,
            this.person2
        ] as unknown as SamplePerson[]
        this.personsCollection2.links = {
            self: "http://localhost/api/sample/persons?fieldsets=default,extra&offset=0&permissions=true&relations=nationality&schema=true",
            first: "http://localhost/api/sample/persons?fieldsets=default,extra&offset=0&permissions=true&relations=nationality&schema=true",
            last: "http://localhost/api/sample/persons?fieldsets=default,extra&offset=538&permissions=true&relations=nationality&schema=true"
        } as OxCollectionLinks
        this.personsCollection2.meta = {
            date: "2022-08-01 16:50:12+02:00",
            copyright: "OpenXtrem-2022",
            authors: "dev@openxtrem.com",
            count: 2,
            schema: schema,
            total: 2
        } as OxCollectionMeta

        this.personsCollection3.objects = []
        this.personsCollection3.links = {
            self: "http://localhost/api/sample/persons?filter=first_name.equal.toto&limit=50&offset=0",
            first: "http://localhost/api/sample/persons?filter=first_name.equal.toto&limit=50&offset=0",
            last: "http://localhost/api/sample/persons?filter=first_name.equal.toto&limit=50&offset=0"
        } as OxCollectionLinks
        this.personsCollection3.meta = {
            date: "2022-08-01 16:50:12+02:00",
            copyright: "OpenXtrem-2022",
            authors: "dev@openxtrem.com",
            count: 0,
            total: 0
        } as OxCollectionMeta

        setActivePinia(pinia)
        storeSchemas(schema)
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

    public testDatagridCreationDefault () {
        const datagrid = this.mountComponent({ columns: this.columns, value: this.personsCollection })

        this.assertEqual(datagrid.vm["itemsData"], [this.person1, this.person2])
        this.assertEqual(datagrid.vm["totalItems"], 539)
    }

    public testDatagridCreationWithNotEmptyCollection () {
        const datagrid = this.mountComponent({ columns: this.columns, value: this.personsCollection })

        this.assertEqual(datagrid.vm["objectClass"], SamplePerson)
        this.assertEqual(datagrid.vm["resourceType"], "sample_person")
    }

    public testDatagridCreationWithEmptyCollection () {
        const datagrid = this.mountComponent({ columns: this.columns, value: this.personsCollection3, oxObject: SamplePerson })

        this.assertEqual(datagrid.vm["objectClass"], SamplePerson)
        this.assertEqual(datagrid.vm["resourceType"], "sample_person")
    }

    public testDatagridCreationWithEmptyCollectionAndNoOxObjectProp () {
        /// Ignore console.error from created
        window.console.error = jest.fn()
        expect(() => this.mountComponent({ columns: this.columns, value: this.personsCollection3 }))
            .toThrowError(new Error("Impossible type inference"))
    }

    public testDatagridCreationWithCollectionSelfLink () {
        const datagrid = this.mountComponent({ columns: this.columns, value: this.personsCollection })

        this.assertEqual(datagrid.vm["footerProps"]["items-per-page-options"], [1, 2, 3])
    }

    public testDatagridCreationWithCollectionSelfLinkDefaultLimit () {
        const datagrid = this.mountComponent({ columns: this.columns, value: this.personsCollection2 })

        this.assertEqual(datagrid.vm["footerProps"]["items-per-page-options"], [10, 20, 30])
    }

    public testDatagridClassesDefault () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection })
        this.assertEqual(this.privateCall(datagrid, "datagridClasses"), "OxDatagrid-table")
    }

    public async testDatagridClassesWithMassActionsEnabled () {
        const datagrid = this.mountComponent({ columns: this.columns, value: this.personsCollection })

        datagrid.vm["showMassActions"] = true

        this.assertEqual(
            this.privateCall(datagrid.vm, "datagridClasses"),
            "massActionsEnabled OxDatagrid-table"
        )
    }

    public testHeaders () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection, showActions: true })
        const headers = [
            {
                class: "OxDatagrid-tableHeader",
                cellClass: "OxDatagrid-tableCell",
                groupable: false,
                text: "Acteur",
                value: "fullName"
            },
            {
                class: "OxDatagrid-tableHeader",
                cellClass: "OxDatagrid-tableCell",
                groupable: false,
                text: "Réalisateur",
                value: "isDirectorString"
            },
            {
                class: "OxDatagrid-tableHeader",
                cellClass: "OxDatagrid-tableCell",
                groupable: false,
                text: "Sexe",
                value: "sex"
            },
            {
                class: "OxDatagrid-tableHeader",
                cellClass: "OxDatagrid-tableCell",
                groupable: false,
                text: "Date de naissance",
                value: "birthdate"
            },
            {
                class: "OxDatagrid-tableHeader",
                cellClass: "OxDatagrid-tableCell",
                groupable: false,
                text: "Nationalité",
                value: "nationality.name"
            },
            {
                class: "OxDatagrid-tableHeader",
                cellClass: "OxDatagrid-tableCell",
                groupable: false,
                text: "Début d'activité",
                value: "activityStart"
            },
            {
                cellClass: "OxDatagrid-tableCell OxDatagrid-tableActions",
                text: "",
                value: "actions",
                sortable: false,
                groupable: false
            }
        ]

        this.assertEqual(this.privateCall(datagrid, "headers"), headers)
    }

    public testHasPrevNextLinks () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection })
        this.assertTrue(this.privateCall(datagrid, "hasPrevNextLinks"))
    }

    public testHasNotPrevNextLinks () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection2 })
        this.assertFalse(this.privateCall(datagrid, "hasPrevNextLinks"))
    }

    public testServerItemsLengthWithPrevNextLinks () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection })
        this.assertEqual(this.privateCall(datagrid, "serverItemsLength"), 539)
    }

    public testServerItemsLengthWithoutPrevNextLinks () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection2 })
        this.assertEqual(this.privateCall(datagrid, "serverItemsLength"), -1)
    }

    public testGroupByIsDefined () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection2, groupBy: "sex" })
        this.assertTrue(this.privateCall(datagrid, "showGroupBy"))
    }

    public testGroupByIsNotDefined () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection2 })
        this.assertFalse(this.privateCall(datagrid, "showGroupBy"))
    }

    public testGroupByWithTitle () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection2, groupBy: "sex" })
        this.assertEqual(this.privateCall(datagrid, "groupByTitle"), "Sexe")
    }

    public testGroupByWithoutTitle () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection2, groupBy: "last_name" })
        this.assertEqual(this.privateCall(datagrid, "groupByTitle"), "")
    }

    public testPrimaryColor () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection })
        this.assertEqual(this.privateCall(datagrid, "primaryColor"), OxThemeCore.primary)
    }

    public testSelectionTextSingular () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection })
        this.assertEqual(this.privateCall(datagrid, "selectionText"), "common-selection")
    }

    public async testSelectionTextPlural () {
        const datagrid = this.mountComponent({ columns: this.columns, value: this.personsCollection })

        datagrid.vm["selectedItems"] = [this.person1, this.person2]

        this.assertEqual(
            this.privateCall(datagrid.vm, "selectionText"),
            "common-selection|pl"
        )
    }

    public testSearchFieldCancelIcon () {
        const datagrid = this.mountComponent({ columns: this.columns, value: this.personsCollection })
        datagrid.vm["search"] = "my search"
        this.assertEqual(this.privateCall(datagrid.vm, "searchFieldIcon"), "cancel")
    }

    public testSearchFieldSearchIcon () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection })
        this.assertEqual(this.privateCall(datagrid, "searchFieldIcon"), "search")
    }

    public testDefaultNoDataText () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection })
        this.assertEqual(this.privateCall(datagrid, "customableNoDataText"), "common-No data")
    }

    public testCustomNoDataText () {
        const datagrid = this.vueComponent({ columns: this.columns, noDataText: "No data custom", value: this.personsCollection })
        this.assertEqual(this.privateCall(datagrid, "customableNoDataText"), "No data custom")
    }

    public async testWatchOptions () {
        const datagrid = this.mountComponent({ columns: this.columns, value: this.personsCollection })

        // @ts-ignore
        const spy = jest.spyOn(datagrid.vm, "refresh")

        datagrid.vm["options"] = {
            page: 1,
            itemsPerPage: 2,
            sortBy: [],
            sortDesc: [
                false
            ],
            groupBy: [],
            groupDesc: [],
            mustSort: false,
            multiSort: true
        }

        await datagrid.vm.$nextTick()

        datagrid.vm["options"] = {
            page: 1,
            itemsPerPage: 2,
            sortBy: [
                "isDirectorString"
            ],
            sortDesc: [
                false,
                false
            ],
            groupBy: [
                "activityStart"
            ],
            groupDesc: [
                false,
                false
            ],
            mustSort: false,
            multiSort: true
        }

        await datagrid.vm.$nextTick()
        const url = new OxUrlBuilder(this.personsCollection.self + "&sort=activity_start,is_director")

        expect(spy).toBeCalledWith(url)
    }

    public async testGenerateSortersWithFilterValuesIsArray () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection })

        const sorters = []

        this.privateCall(
            datagrid,
            "generateSorters",
            ["fullName"],
            [false, false],
            sorters
        )

        this.assertEqual(
            sorters,
            [
                { sort: "ASC", choice: "first_name" },
                { sort: "ASC", choice: "last_name" }
            ]
        )
    }

    public async testGenerateSortersWithFilterValuesIsNotArray () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection })

        const sorters = []

        this.privateCall(
            datagrid,
            "generateSorters",
            ["activityStart"],
            [false, false],
            sorters
        )

        this.assertEqual(
            sorters,
            [{ sort: "ASC", choice: "activity_start" }]
        )
    }

    public testGroupByIconIsOpen () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection2 })
        this.assertEqual(this.privateCall(datagrid, "groupByIcon", true), "chevronUp")
    }

    public testGroupByIconIsNotOpen () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection2 })
        this.assertEqual(this.privateCall(datagrid, "groupByIcon", false), "chevronDown")
    }

    public testGroupByWithValue () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection2 })
        this.assertEqual(this.privateCall(datagrid, "groupByValue", "f"), "f")
    }

    public testGroupByWithoutValue () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection2 })
        this.assertEqual(this.privateCall(datagrid, "groupByValue", null), "N/A")
    }

    public testRowItemClassesStripped () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection2, stripped: true })
        this.assertEqual(this.privateCall(datagrid, "rowItemClasses"), "stripped OxDatagrid-tableRow")
    }

    public testRowItemClassesNotStripped () {
        const datagrid = this.vueComponent({ columns: this.columns, value: this.personsCollection2 })
        this.assertEqual(this.privateCall(datagrid, "rowItemClasses"), "OxDatagrid-tableRow")
    }

    // @TODO: Issue due to offset's value reset to null in searchItems func
    // public async testSearchItems () {
    //     const datagrid = this.mountComponent({ columns: this.columns, value: this.personsCollection })
    //
    //     // @ts-ignore
    //     const spy = jest.spyOn(datagrid.vm, "refresh")
    //
    //     datagrid.vm["search"] = "mysearch"
    //     this.privateCall(datagrid.vm, "searchItems")
    //
    //     const url = new OxUrlBuilder("http://localhost/api/sample/persons?fieldsets=default%2Cextra&limit=2&permissions=true&relations=nationality&schema=true&search=mysearch")
    //     url["_queryParameters"] = {
    //         fieldsets: [
    //             "default",
    //             "extra"
    //         ],
    //         relations: [
    //             "nationality"
    //         ],
    //         filters: [],
    //         sort: [],
    //         offset: null,
    //         limit: "2",
    //         search: "mysearch"
    //     } as OxUrlQueryParameters
    //
    //     expect(spy).toBeCalledWith(url)
    // }

    public testUpdateItemEvent (): void {
        const datagrid = this.mountComponent({ columns: this.columns, value: this.personsCollection2 })
        this.privateCall(datagrid.vm, "updateItem", this.person1)

        const events = datagrid.emitted("updateItem")
        this.assertHaveLength(events, 1)

        const lastEvent = Array.isArray(events) ? events[0] : false
        this.assertEqual(
            lastEvent,
            [this.person1]
        )
    }

    public testDeleteItemEvent (): void {
        const datagrid = this.mountComponent({ columns: this.columns, value: this.personsCollection2 })
        this.privateCall(datagrid.vm, "deleteItem", this.person1)

        const events = datagrid.emitted("deleteItem")
        this.assertHaveLength(events, 1)

        const lastEvent = Array.isArray(events) ? events[0] : false
        this.assertEqual(
            lastEvent,
            [this.person1]
        )
    }

    public testFilterItemsEvent (): void {
        const datagrid = this.mountComponent({ columns: this.columns, value: this.personsCollection })
        this.privateCall(datagrid.vm, "filterItems", ["filter1"])

        const events = datagrid.emitted("filterItems")
        this.assertHaveLength(events, 1)

        const lastEvent = Array.isArray(events) ? events[0] : false
        this.assertEqual(
            lastEvent,
            [["filter1"]]
        )
    }

    public testInputEvent (): void {
        const datagrid = this.mountComponent({ columns: this.columns, value: this.personsCollection })
        this.privateCall(datagrid.vm, "input", [this.person1])

        this.assertEqual(
            datagrid.vm["selectedItems"],
            [this.person1]
        )

        this.assertEqual(
            datagrid.vm["showMassActions"],
            true
        )
    }
}

class SamplePerson extends OxObject {
    constructor () {
        super()
        this.type = "sample_person"
    }

    protected _relationsTypes = {
        sample_nationality: SampleNationality
    }

    get firstName (): string {
        return super.get("first_name")
    }

    set firstName (value: string) {
        super.set("first_name", value)
    }

    get lastName (): string {
        return super.get("last_name")
    }

    set lastName (value: string) {
        super.set("last_name", value)
    }

    get fullName (): string {
        return this.firstName + " " + this.lastName
    }

    get isDirector (): boolean {
        return super.get("is_director")
    }

    set isDirector (value: boolean) {
        super.set("is_director", value)
    }

    get isDirectorString (): string {
        return this.isDirector ? tr("CSamplePerson.is_director.y") : tr("CSamplePerson.is_director.n")
    }

    get birthdate (): string {
        return this.birthdateData ? new Date(this.birthdateData).toLocaleDateString("fr") : ""
    }

    get birthdateData (): string {
        return super.get("birthdate")
    }

    set birthdateData (value: string) {
        super.set("birthdate", value)
    }

    get sex (): string {
        return super.get("sex")
    }

    set sex (value: string) {
        super.set("sex", value)
    }

    get sexIcon (): string | undefined {
        if (!this.sex) {
            return undefined
        }

        return this.sex === "m" ? "male" : "female"
    }

    get activityStart (): number | string {
        return this.activityStartData ? new Date(this.activityStartData).getFullYear() : ""
    }

    get activityStartData (): string {
        return super.get("activity_start")
    }

    set activityStartData (value: string) {
        super.set("activity_start", value)
    }

    get profilePicture (): string | undefined {
        return this.links.profile_picture
    }

    get nationality (): SampleNationality | null {
        return this.loadForwardRelation<SampleNationality>("nationality")
    }

    set nationality (value: SampleNationality | null) {
        this.setForwardRelation("nationality", value)
    }
}

class SampleNationality extends OxObject {
    constructor () {
        super()
        this.type = "sample_nationality"
    }

    get name (): string {
        if (!this.attributes.name) {
            return ""
        }
        return this.attributes.name.charAt(0).toUpperCase() + this.attributes.name.slice(1)
    }

    get code (): string {
        return this.attributes.code
    }

    get flag (): string {
        return this.attributes.flag
    }
}

(new OxDatagridTest()).launchTests()
