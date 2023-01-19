/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import OxObject from "@/core/models/OxObject"
import { OxJsonApi } from "@/core/types/OxApiTypes"
import { setActivePinia } from "pinia"
import pinia from "@/core/plugins/OxPiniaCore"
import { getObject, getSchema } from "@/core/utils/OxStorage"
import OxCollection from "@/core/models/OxCollection"
import oxApiService from "@/core/utils/OxApiService"
import { createJsonApiSkeleton, oxObjectTransformer } from "@/core/utils/OxJsonApiTransformer"
import { cloneObject } from "@/core/utils/OxObjectTools"
import {
    createJsonApiObjects,
    deleteJsonApiObject,
    getCollectionFromJsonApi, getCollectionFromJsonApiRequest, getObjectFromJsonApi,
    getObjectFromJsonApiRequest,
    updateJsonApiObject, updateJsonApiObjectFields
} from "@/core/utils/OxApiManager"

const axiosResponseCollection = {
    data: {
        data: [
            {
                type: "test_object",
                id: "316",
                attributes: {
                    name: "Test",
                    release: "2022-08-17",
                    duration: "16:18:47",
                    csa: "12",
                    languages: "en"
                },
                links: {
                    self: "self"
                }
            },
            {
                type: "test_object",
                id: "321",
                attributes: {
                    name: "Test test",
                    release: "2022-09-19",
                    duration: "01:42:00",
                    csa: "12",
                    languages: "fr|ger|it"
                },
                links: {
                    self: "self"
                }
            }
        ],
        meta: {
            date: "2022-09-19 11:06:39+02:00",
            copyright: "OpenXtrem-2022",
            authors: "dev@openxtrem.com",
            count: 2,
            total: 2
        },
        links: {
            self: "self",
            first: "first",
            last: "last"
        }
    }
}
const axiosResponseObject = {
    data: {
        data: [
            {
                type: "test_object",
                id: "321",
                attributes: {
                    name: "Test test",
                    release: "2022-09-19",
                    duration: "01:42:00",
                    csa: "12",
                    languages: "fr|ger|it"
                },
                links: {
                    self: "self"
                }
            }
        ],
        meta: {
            date: "2022-09-19 11:06:39+02:00",
            copyright: "OpenXtrem-2022",
            authors: "dev@openxtrem.com",
            count: 1,
            total: 1
        },
        links: {
            self: "self",
            first: "first",
            last: "last"
        }
    }
}

jest.spyOn(oxApiService, "get").mockImplementation((url: string) => {
    if (url.includes("collection")) {
        return new Promise((resolve) => {
            resolve(axiosResponseCollection)
        })
    }
    else {
        return new Promise((resolve) => {
            resolve(axiosResponseObject)
        })
    }
})

jest.spyOn(oxApiService, "post").mockImplementation((url, config: any) => {
    if (Array.isArray(config.data)) {
        return new Promise((resolve) => {
            resolve(axiosResponseCollection)
        })
    }
    else {
        return new Promise((resolve) => {
            resolve(axiosResponseObject)
        })
    }
})

jest.spyOn(oxApiService, "patch").mockImplementation(() => {
    return new Promise((resolve) => {
        resolve(axiosResponseObject)
    })
})

jest.spyOn(oxApiService, "delete").mockImplementation(() => {
    return new Promise((resolve) => {
        resolve("")
    })
})

/**
 * OxJsonApiManager tests
 */
export default class OxJsonApiManagerTest extends OxTest {
    protected component = "OxJsonApiManager"

    protected beforeAllTests () {
        super.beforeAllTests()
        setActivePinia(pinia)
    }

    protected afterTest () {
        super.afterTest()
        jest.clearAllMocks()
    }

    private objectJsonApi = {
        data: {
            type: "test_object",
            id: "1",
            attributes: {
                name: "Test object",
                description: "A testing object",
                value: "12"
            },
            relationships: {
                category: {
                    data: {
                        type: "another_object",
                        id: "3000"
                    }
                }
            },
            links: {
                self: "self"
            },
            meta: {
                someKey: "value"
            }
        },
        included: [
            {
                type: "another_object",
                id: "3000",
                attributes: {
                    name: "Object 1",
                    color: "#EA34DA",
                    active: true
                }
            },
            {
                type: "another_object",
                id: "3001",
                attributes: {
                    name: "Object 2",
                    color: "#0EBB84",
                    active: false
                }
            }
        ],
        meta: {
            copyright: "OpenXtrem-2022",
            authors: "dev@openxtrem.com",
            schema: [
                {
                    id: "429b23f96004afaa4ee3b31e7ca34c0e",
                    owner: "test_object",
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
                    description: "Titre"
                },
                {
                    id: "0a9092e87e8ddb890799273a6c8e8596",
                    owner: "test_object",
                    field: "description",
                    type: "str",
                    fieldset: "default",
                    autocomplete: null,
                    placeholder: null,
                    notNull: true,
                    confidential: null,
                    default: null,
                    libelle: "Description",
                    label: "Description",
                    description: "Description"
                }
            ]
        }
    }

    private collectionJsonApi = {
        data: [
            {
                type: "test_object",
                id: "1",
                attributes: {
                    name: "Test object",
                    description: "A testing object",
                    value: "12"
                },
                links: {
                    self: "self"
                }
            },
            {
                type: "test_object",
                id: "2",
                attributes: {
                    name: "Test object",
                    description: "Another testing object",
                    value: "16"
                },
                links: {
                    self: "self"
                }
            },
            {
                type: "test_object",
                id: "3",
                attributes: {
                    name: "Test object",
                    description: "Again Another testing object",
                    value: "16"
                },
                links: {
                    self: "self"
                }
            }
        ],
        links: {
            self: "self",
            schema: "schema",
            history: "history"
        },
        meta: {
            copyright: "OpenXtrem-2022",
            authors: "dev@openxtrem.com"
        }
    }

    public testGetObjectFromJsonApiObject () {
        const result = getObjectFromJsonApi(TestObject, this.objectJsonApi as unknown as OxJsonApi)
        expect(result).toBeInstanceOf(TestObject)
        expect(getObject("another_object", "3000")).toBeInstanceOf(AnotherObject)
        expect(getObject("another_object", "3001")).toBeInstanceOf(AnotherObject)
        expect(getSchema("test_object", "name")).toMatchObject(this.objectJsonApi.meta.schema[0])
        expect(getSchema("test_object", "description")).toMatchObject(this.objectJsonApi.meta.schema[1])
    }

    public testGetObjectFromJsonApiCollection () {
        expect(() => getObjectFromJsonApi(TestObject, this.collectionJsonApi as unknown as OxJsonApi))
            .toThrowError(new Error("Get array instead of object"))
    }

    public testGetCollectionFromJsonApiObject () {
        const result = getCollectionFromJsonApi(TestObject, this.objectJsonApi as unknown as OxJsonApi)
        expect(result).toBeInstanceOf(OxCollection)
        expect(result.objects).toBeInstanceOf(Array)
        expect(result.objects).toHaveLength(1)
        expect(result.objects[0]).toBeInstanceOf(TestObject)
    }

    public testGetCollectionFromJsonApiCollection () {
        const result = getCollectionFromJsonApi(TestObject, this.collectionJsonApi as unknown as OxJsonApi)
        expect(result).toBeInstanceOf(OxCollection)
        expect(result.objects).toBeInstanceOf(Array)
        expect(result.objects).toHaveLength(3)
        expect(result.links).toMatchObject(this.collectionJsonApi.links)
        expect(result.meta).toMatchObject(this.collectionJsonApi.meta)
    }

    public async testCreateJsonApiObject () {
        const object = new TestObject()
        const result = await createJsonApiObjects(object, "url")
        expect(oxApiService.post).toHaveBeenCalledWith(
            "url",
            createJsonApiSkeleton(oxObjectTransformer(object))
        )
        expect(result).toEqual(getObjectFromJsonApi(TestObject, axiosResponseObject.data as unknown as OxJsonApi))
    }

    public async testCreateJsonApiObjects () {
        const object1 = new TestObject()
        object1.attributes.label = "test"
        const object2 = new TestObject()
        object2.attributes.label = "test2"
        const result = await createJsonApiObjects([object1, object2], "url")
        expect(oxApiService.post).toHaveBeenCalledWith(
            "url",
            createJsonApiSkeleton([object1, object2].map((object) => {
                return oxObjectTransformer(object)
            }))
        )
        expect(result).toEqual(getCollectionFromJsonApi(TestObject, axiosResponseCollection.data as unknown as OxJsonApi))
    }

    public async testUpdateJsonApiObject () {
        const object1 = new TestObject()
        object1.attributes.label = "test"
        object1.id = "1"
        object1.attributes.value = true
        object1.links.self = "self"
        const object2 = cloneObject(object1)
        object2.attributes.label = "test2"
        const result = await updateJsonApiObject(object1, object2)
        expect(oxApiService.patch).toHaveBeenCalledWith(
            "self",
            { data: { attributes: { label: "test2" }, id: "1", relationships: {}, type: "test_object" } }
        )
        expect(result).toEqual(getObjectFromJsonApi(TestObject, axiosResponseObject.data as unknown as OxJsonApi))
    }

    public async testUpdateDifferentObject () {
        const object1 = new TestObject()
        object1.links.self = "self"
        const object2 = new AnotherObject()
        object2.links.self = "self"
        try {
            await updateJsonApiObject(object1, object2)
        }
        catch (e) {
            expect(e).toEqual(new Error("No matching object type between TestObject and AnotherObject"))
        }
    }

    public async testUpdateObjectWithoutSelf () {
        const object1 = new TestObject()
        const object2 = cloneObject(object1)
        try {
            await updateJsonApiObject(object1, object2)
        }
        catch (e) {
            expect(e).toEqual(new Error("Missing self links on object"))
        }
    }

    public async testUpdateJsonApiObjectFields () {
        const object1 = new TestObject()
        object1.attributes.label = "test"
        object1.id = "1"
        object1.attributes.value = true
        object1.links.self = "self"
        const result = await updateJsonApiObjectFields(object1, { label: "test2" })
        expect(oxApiService.patch).toHaveBeenCalledWith(
            "self",
            { data: { attributes: { label: "test2" }, id: "1", relationships: {}, type: "test_object" } }
        )
        expect(result).toEqual(getObjectFromJsonApi(TestObject, axiosResponseObject.data as unknown as OxJsonApi))
    }

    public async testUpdateObjectFieldsWithoutSelf () {
        const object1 = new TestObject()
        try {
            await updateJsonApiObjectFields(object1, { label: "test" })
        }
        catch (e) {
            expect(e).toEqual(new Error("Missing self links on object"))
        }
    }

    public async testDeleteJsonApiObject () {
        const object = new TestObject()
        object.links.self = "self"
        await deleteJsonApiObject(object)
        expect(oxApiService.delete).toHaveBeenCalledWith("self")
    }

    public async testDeleteJsonApiObjectWithoutSelf () {
        const object = new TestObject()
        try {
            await deleteJsonApiObject(object)
        }
        catch (e) {
            expect(e).toEqual(new Error("Missing self links on object"))
        }
    }

    public async testGetObjectFromJsonApiRequest () {
        const result = await getObjectFromJsonApiRequest(TestObject, "url")
        expect(result).toBeInstanceOf(TestObject)
        expect(oxApiService.get).toHaveBeenCalledWith("url")
    }

    public async testGetCollectionFromJsonApiRequest () {
        const result = await getCollectionFromJsonApiRequest(TestObject, "url")
        expect(result).toBeInstanceOf(OxCollection)
        expect(result.objects[0]).toBeInstanceOf(TestObject)
        expect(oxApiService.get).toHaveBeenCalledWith("url")
    }
}

class TestObject extends OxObject {
    constructor () {
        super()
        this.type = "test_object"
    }

    protected _relationsTypes = {
        another_object: AnotherObject
    }
}

class AnotherObject extends OxObject {
    constructor () {
        super()
        this.type = "another_object"
    }
}

(new OxJsonApiManagerTest()).launchTests()
