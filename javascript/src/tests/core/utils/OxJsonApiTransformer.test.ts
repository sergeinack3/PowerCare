/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import OxObject from "@/core/models/OxObject"
import {
    createJsonApiSkeleton,
    dataTransformer, extractSchemasFromJsonApi,
    includedTransformer,
    itemTransformer, oxObjectTransformer,
    schemaTransformer
} from "@/core/utils/OxJsonApiTransformer"
import { OxJsonApi, OxJsonApiData } from "@/core/types/OxApiTypes"

/**
 * OxJsonApiTransformer tests
 */
export default class OxJsonApiTransformerTest extends OxTest {
    protected component = "OxJsonApiTransformer"

    private objectJsonApi = {
        data: {
            type: "test_object",
            id: "1",
            attributes: {
                name: "Test object",
                description: "A testing object",
                value: "12",
                active: true
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
            authors: "dev@openxtrem.com"
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
                    value: "12",
                    active: true
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
                    value: "16",
                    active: false
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
        meta: {
            copyright: "OpenXtrem-2022",
            authors: "dev@openxtrem.com"
        }
    }

    private schemas = {
        data: [
            {
                type: "schema",
                id: "429b23f96004afaa4ee3b31e7ca34c0e",
                attributes: {
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
                }
            },
            {
                type: "schema",
                id: "0a9092e87e8ddb890799273a6c8e8596",
                attributes: {
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
            },
            {
                type: "schema",
                id: "0a9092e87e8ddb890799273a6c8e8596",
                attributes: {
                    owner: "test_object",
                    field: "value",
                    type: "number",
                    fieldset: "default",
                    autocomplete: null,
                    placeholder: null,
                    notNull: true,
                    confidential: null,
                    default: null,
                    libelle: "Valeur",
                    label: "Valeur",
                    description: "Valeur"
                }
            }
        ]
    }

    public testDataTransformerWithObject () {
        const result = dataTransformer(TestObject, this.objectJsonApi as unknown as OxJsonApi) as TestObject
        expect(result).toBeInstanceOf(TestObject)
    }

    public testDataTransformerWithCollection () {
        const result = dataTransformer(TestObject, this.collectionJsonApi as unknown as OxJsonApi) as TestObject[]
        expect(result).toBeInstanceOf(Array)
        expect(result).toHaveLength(3)
    }

    public testItemTransformer () {
        const result = itemTransformer(TestObject, this.objectJsonApi.data as unknown as OxJsonApiData)
        expect(result).toBeInstanceOf(TestObject)
        expect(result.id).toBe(this.objectJsonApi.data.id)
        expect(result.type).toBe(this.objectJsonApi.data.type)
        expect(result.attributes).toMatchObject(this.objectJsonApi.data.attributes)
        expect(result.links).toMatchObject(this.objectJsonApi.data.links)
        expect(result.meta).toMatchObject(this.objectJsonApi.data.meta)
    }

    public testIncludedTransformerWithoutIncluded () {
        const jsonWithoutIncluded = { ...this.collectionJsonApi } as unknown as OxJsonApi
        const result = includedTransformer(TestObject, jsonWithoutIncluded)
        expect(result).toStrictEqual([])
    }

    public testIncludedTransformer () {
        const result = includedTransformer(TestObject, this.objectJsonApi as unknown as OxJsonApi)
        expect(result).toBeInstanceOf(Array)
        expect(result).toHaveLength(2)
        expect(result[0]).toBeInstanceOf(AnotherObject)
        expect(result[1]).toBeInstanceOf(AnotherObject)
    }

    public testIncludedTransformerWithUnknownRelation () {
        const jsonWithUnknownRelation = { ...this.objectJsonApi }
        jsonWithUnknownRelation.included.push({
            type: "unknown_type",
            id: "404",
            attributes: {
                name: "Unknown",
                color: "#000",
                active: false
            }
        })
        expect(() => includedTransformer(TestObject, jsonWithUnknownRelation as unknown as OxJsonApi))
            .toThrowError(new Error("Type 'unknown_type' missing in TestObject's _relationsTypes"))
    }

    public testSchemaTransformerWithArray () {
        const result = schemaTransformer(this.schemas as unknown as OxJsonApi)
        expect(result).toBeInstanceOf(Array)
        expect(result).toHaveLength(3)
        expect(result[0]).toMatchObject(this.schemas.data[0].attributes)
        expect(result[1]).toMatchObject(this.schemas.data[1].attributes)
        expect(result[2]).toMatchObject(this.schemas.data[2].attributes)
    }

    public testSchemaTransformer () {
        const schema = { ...this.schemas }
        // @ts-ignore
        schema.data = schema.data[0]
        const result = schemaTransformer(schema as unknown as OxJsonApi)
        expect(result).toMatchObject(this.schemas.data[0].attributes)
    }

    public testExtractSchemasFromJsonApi () {
        const ressourceWithSchema = JSON.parse(JSON.stringify(this.objectJsonApi))
        /* eslint-disable-next-line dot-notation */
        ressourceWithSchema.meta["schema"] = this.schemas.data.map((_schema) => {
            return { ..._schema.attributes }
        })
        const result = extractSchemasFromJsonApi(ressourceWithSchema as unknown as OxJsonApi)
        expect(result).toBeInstanceOf(Array)
        expect(result).toHaveLength(3)
        expect(result[0]).toMatchObject(this.schemas.data[0].attributes)
        expect(result[1]).toMatchObject(this.schemas.data[1].attributes)
        expect(result[2]).toMatchObject(this.schemas.data[2].attributes)
    }

    public testExtractEmptySchemaFromJsonApi () {
        const result = extractSchemasFromJsonApi(this.objectJsonApi as unknown as OxJsonApi)
        expect(result).toBeInstanceOf(Array)
        expect(result).toHaveLength(0)
    }

    public testOxObjectTransformer () {
        const object = new TestObject()
        object.id = "12"
        object.attributes = { ...this.objectJsonApi.data.attributes }
        object.relationships = { ...this.objectJsonApi.data.relationships }
        const result = oxObjectTransformer(object)
        expect(result).toMatchObject({
            id: object.id,
            type: object.type,
            attributes: object.attributes,
            relationships: object.relationships
        })
    }

    public testCreateJsonApiSkeletonForOnlyOneObject () {
        const data = JSON.parse(JSON.stringify(this.objectJsonApi.data))
        const result = createJsonApiSkeleton(data)
        expect(this.objectJsonApi.data.attributes.active).not.toEqual(data.attributes.active)
        expect(data.attributes.active).toEqual(1)
        expect(result).toMatchObject({
            data: data
        })
    }

    public testCreateJsonApiSkeletonForMultipleObject () {
        const data = JSON.parse(JSON.stringify(this.collectionJsonApi.data))

        const result = createJsonApiSkeleton(data)
        expect(this.collectionJsonApi.data[0].attributes.active).not.toEqual(data[0].attributes.active)
        expect(this.collectionJsonApi.data[1].attributes.active).not.toEqual(data[1].attributes.active)
        expect(data[0].attributes.active).toEqual(1)
        expect(data[1].attributes.active).toEqual(0)
        expect(result).toMatchObject({
            data: data
        })
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

(new OxJsonApiTransformerTest()).launchTests()
