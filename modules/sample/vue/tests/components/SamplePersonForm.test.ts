/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import SamplePersonForm from "@modules/sample/vue/components/SamplePersonForm/SamplePersonForm.vue"
import { Component } from "vue"
import { createLocalVue, shallowMount } from "@vue/test-utils"
import pinia from "@/core/plugins/OxPiniaCore"
import OxTranslator from "@/core/plugins/OxTranslator"
import SamplePerson from "@modules/sample/vue/models/SamplePerson"
import { setActivePinia } from "pinia"
import { OxSchema } from "@/core/types/OxSchema"
import { storeSchemas } from "@/core/utils/OxStorage"

const localVue = createLocalVue()
localVue.use(OxTranslator)

/* eslint-disable dot-notation */

/**
 * SamplePersonForm tests
 */
export default class SamplePersonFormTest extends OxTest {
    protected component = SamplePersonForm

    private person = new SamplePerson()
    private createLink = "/api/sample/persons"
    private nationalitiesLink = "/api/sample/nationalities"

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

        this.person.id = "1"
        this.person.type = "sample_person"
        this.person.attributes = {
            last_name: "Romance",
            first_name: "Viviane",
            is_director: true,
            birthdate: "1912-07-04",
            sex: "f",
            activity_start: "1998-03-03"
        }
        this.person.relationships = {
            nationality: {
                data: {
                    type: "sample_nationality",
                    id: "113"
                }
            }
        }
        this.person.links = {
            self: "/api/sample/persons/1",
            schema: "/api/schemas/sample_person?fieldsets=default,extra",
            history: "/api/history/sample_person/1",
            profile_picture: "?m=files&raw=thumbnail&document_id=36892&thumb=0"
        }

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

    public testAddButtonLabel () {
        const personForm = this.vueComponent({
            person: null,
            createLink: this.createLink,
            nationalitiesLink: this.nationalitiesLink
        })
        this.assertEqual(this.privateCall(personForm, "actionBtnLabel"), "CSamplePerson-title-add")
    }

    public testEditButtonLabel () {
        const personForm = this.vueComponent({
            person: this.person,
            createLink: this.createLink,
            nationalitiesLink: this.nationalitiesLink
        })
        this.assertEqual(this.privateCall(personForm, "actionBtnLabel"), "CSamplePerson-title-modify")
    }

    public testDeletePersonEvent (): void {
        const personForm = this.mountComponent({
            person: this.person,
            createLink: this.createLink,
            nationalitiesLink: this.nationalitiesLink
        })
        this.privateCall(personForm.vm, "deletePerson", this.person)

        const events = personForm.emitted("deletePerson")
        this.assertHaveLength(events, 1)

        const lastEvent = Array.isArray(events) ? events[0] : false
        this.assertEqual(
            lastEvent,
            [this.person]
        )
    }
}

(new SamplePersonFormTest()).launchTests()
