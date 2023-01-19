/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import { setActivePinia } from "pinia"
import pinia from "@/core/plugins/OxPiniaCore"
import * as OxStorage from "@/core/utils/OxStorage"
import { prepareForm } from "@/core/utils/OxSchemaManager"
import { useSchemaStore } from "@/core/stores/schema"
import oxApiService from "@/core/utils/OxApiService"

jest.spyOn(oxApiService, "get").mockImplementation((url: string) => {
    return new Promise((resolve) => {
        resolve({
            data: {
                data: [{
                    attributes: {
                        owner: "test_object",
                        field: "desc",
                        type: "text",
                        fieldset: "bonus",
                        autocomplete: null,
                        placeholder: null,
                        notNull: false,
                        confidential: null,
                        default: null,
                        libelle: "Description",
                        label: "Description",
                        description: "Description"
                    }
                }]
            }
        })
    })
})

/**
 * OxSchemaManager tests
 */
export default class OxSchemaManagerTest extends OxTest {
    protected component = "OxSchemaManager"

    protected beforeAllTests () {
        super.beforeAllTests()
        setActivePinia(pinia)
    }

    protected afterTest () {
        super.afterTest()
        const store = useSchemaStore()
        store.schema = []
    }

    public testPrepareFormWithGoodSchemaStore () {
        const store = useSchemaStore()

        store.schema.push({
            owner: "test_object",
            field: "name",
            type: "str",
            fieldset: "default",
            autocomplete: null,
            placeholder: null,
            notNull: true,
            confidential: null,
            default: null,
            libelle: "Nom",
            label: "Nom",
            description: "Nom"
        },
        {
            owner: "test_object",
            field: "value",
            type: "boolean",
            fieldset: "extra",
            autocomplete: null,
            placeholder: null,
            notNull: false,
            confidential: null,
            default: null,
            libelle: "Nom",
            label: "Nom",
            description: "Nom"
        })

        const spyStoreSchema = jest.spyOn(OxStorage, "storeSchemas")
        prepareForm("test_object", ["default", "extra"])

        expect(spyStoreSchema).not.toHaveBeenCalled()
    }

    public async testPrepareFormWithMissingFieldset () {
        const store = useSchemaStore()

        store.schema.push({
            owner: "test_object",
            field: "name",
            type: "str",
            fieldset: "default",
            autocomplete: null,
            placeholder: null,
            notNull: true,
            confidential: null,
            default: null,
            libelle: "Nom",
            label: "Nom",
            description: "Nom"
        },
        {
            owner: "test_object",
            field: "value",
            type: "boolean",
            fieldset: "extra",
            autocomplete: null,
            placeholder: null,
            notNull: false,
            confidential: null,
            default: null,
            libelle: "Nom",
            label: "Nom",
            description: "Nom"
        })

        const spyStoreSchema = jest.spyOn(OxStorage, "storeSchemas")
        await prepareForm("test_object", ["default", "bonus"])

        expect(spyStoreSchema).toHaveBeenCalled()
        expect(store.schema).toHaveLength(3)
    }
}

(new OxSchemaManagerTest()).launchTests()
