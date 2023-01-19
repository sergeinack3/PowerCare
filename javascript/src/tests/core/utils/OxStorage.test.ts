/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import OxObject from "@/core/models/OxObject"
import { setActivePinia } from "pinia"
import pinia from "@/core/plugins/OxPiniaCore"
import { useIncludedStore } from "@/core/stores/included"
import { useSchemaStore } from "@/core/stores/schema"
import {
    getObject,
    getObjectsByAttribute, getSchema,
    storeObject,
    storeObjects,
    storeSchema,
    storeSchemas
} from "@/core/utils/OxStorage"

/**
 * OxStorage tests
 */
export default class OxStorageTest extends OxTest {
    protected component = "OxStorage"
    private schemaStore
    private includedStore

    protected beforeAllTests () {
        super.beforeAllTests()
        setActivePinia(pinia)
        this.includedStore = useIncludedStore()
        this.schemaStore = useSchemaStore()
    }

    protected afterTest () {
        super.afterTest()
        this.includedStore.objects = []
        this.schemaStore.schema = []
    }

    public testStoreObjects () {
        const object1 = new TestObject()
        object1.id = "1"
        object1.active = true
        object1.description = "Object 1"
        object1.title = "Object"
        object1.value = 4
        const object2 = new TestObject()
        object2.id = "2"
        object2.active = false
        object2.description = "Object 2"
        object2.title = "Object"
        object2.value = 5
        const result = storeObjects([object1, object2])
        expect(result).toEqual([object1, object2])
        expect(this.includedStore.objects).toEqual([object1, object2])
    }

    public testStoreNewObject () {
        const object1 = new TestObject()
        object1.id = "1"
        object1.active = true
        object1.description = "Object 1"
        object1.title = "Object"
        object1.value = 4
        const result = storeObject(object1)
        expect(result).toEqual(object1)
        expect(this.includedStore.objects).toEqual([object1])
    }

    public testStoreExistingObject () {
        const object1 = new TestObject()
        object1.id = "1"
        object1.description = "Object 1"
        object1.title = "Object"
        object1.value = 4
        storeObject(object1)

        const object2 = new TestObject()
        object2.id = "2"
        object2.description = "Object 2"
        object2.title = "Object"
        object2.value = 5
        storeObject(object2)

        const object1Update = new TestObject()
        object1Update.id = "1"
        object1Update.active = true
        object1Update.description = "Object 1 modified"
        object1Update.value = 4
        storeObject(object1Update)

        const expected = new TestObject()
        expected.id = "1"
        expected.description = "Object 1 modified"
        expected.active = true
        expected.value = 4
        expected.title = "Object"
        expect(this.includedStore.objects).toEqual([expected, object2])
    }

    public testGetExistingObject () {
        const object1 = new TestObject()
        object1.id = "1"
        object1.active = true
        object1.description = "Object 1"
        object1.title = "Object"
        object1.value = 4
        storeObject(object1)
        expect(getObject("test_object", "1")).toEqual(object1)
    }

    public testGetNonExistentObject () {
        const object1 = new TestObject()
        object1.id = "1"
        object1.active = true
        object1.description = "Object 1"
        object1.title = "Object"
        object1.value = 4
        storeObject(object1)
        expect(getObject("another_object", "1")).toBeUndefined()
        expect(getObject("test_object", "2")).toBeUndefined()
    }

    public testFindAllObjectsByAttribute () {
        const object1 = new TestObject()
        object1.id = "1"
        object1.description = "Object 1"
        object1.title = "Bad object"
        object1.value = 4
        object1.active = false
        storeObject(object1)

        const object2 = new TestObject()
        object2.id = "2"
        object2.description = "Object 2"
        object2.title = "Good object"
        object2.value = 5
        object2.active = true
        storeObject(object2)

        const object3 = new TestObject()
        object3.id = "3"
        object3.description = "Object 3"
        object3.title = "Good object"
        object3.value = 10
        object3.active = true
        storeObject(object3)

        const object4 = new TestObject()
        object4.id = "4"
        object4.description = "Object 4"
        object4.title = "Bad object"
        object4.value = 20
        object4.active = false
        storeObject(object4)

        expect(getObjectsByAttribute("test_object", "title", "Good object"))
            .toEqual([object2, object3])
    }

    public testStoreSchemas () {
        const schema1 = {
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
        }
        const schema2 = {
            owner: "test_object",
            field: "value",
            type: "boolean",
            fieldset: "extra",
            autocomplete: null,
            placeholder: null,
            notNull: false,
            confidential: null,
            default: null,
            libelle: "Valeur",
            label: "Valeur",
            description: "Valeur"
        }
        const result = storeSchemas([schema1, schema2])
        expect(result).toEqual([schema1, schema2])
        expect(this.schemaStore.schema).toEqual([schema1, schema2])
    }

    public testStoreSchema () {
        const schema = {
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
        }
        const result = storeSchema(schema)
        expect(result).toEqual(schema)
        expect(this.schemaStore.schema).toEqual([schema])
    }

    public testGetExistingSchema () {
        const schema1 = {
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
        }
        const schema2 = {
            owner: "test_object",
            field: "value",
            type: "boolean",
            fieldset: "extra",
            autocomplete: null,
            placeholder: null,
            notNull: false,
            confidential: null,
            default: null,
            libelle: "Valeur",
            label: "Valeur",
            description: "Valeur"
        }
        storeSchemas([schema1, schema2])
        const result = getSchema("test_object", "name")
        expect(result).toEqual(schema1)
    }

    public testGetNonExistingSchema () {
        const schema = {
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
        }
        storeSchema(schema)
        expect(() => getSchema("test_object", "value"))
            .toThrowError("Schema for field test_object.value has not been stored")
        expect(() => getSchema("another_object", "name"))
            .toThrowError("Schema for field another_object.name has not been stored")
    }
}

class TestObject extends OxObject {
    constructor () {
        super()
        this.type = "test_object"
    }

    get description (): string {
        return super.get("description")
    }

    set description (value: string) {
        this.set("description", value)
    }

    get title (): string {
        return super.get("title")
    }

    set title (value: string) {
        this.set("title", value)
    }

    get value (): number {
        return super.get("value")
    }

    set value (value: number) {
        this.set("value", value)
    }

    get active (): boolean {
        return super.get("active")
    }

    set active (value: boolean) {
        this.set("active", value)
    }
}

(new OxStorageTest()).launchTests()
