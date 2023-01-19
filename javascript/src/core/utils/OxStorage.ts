/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import OxObject from "@/core/models/OxObject"
import { useIncludedStore } from "@/core/stores/included"
import { OxSchema } from "@/core/types/OxSchema"
import { useSchemaStore } from "@/core/stores/schema"
import { merge } from "lodash"

/**
 * includedStore management of several OxObjects
 * @param {OxObject[]} objects - Objects to store
 *
 * @returns {OxObject[]} the stored objects
 */
export function storeObjects<DataType extends OxObject> (objects: DataType[]): DataType[] {
    return objects.map((object) => {
        return storeObject<DataType>(object)
    })
}

/**
 * includedStore management for one OxObject
 * @param {OxObject} object - The object to store
 *
 * @returns {OxObject} The stored object
 */
export function storeObject<DataType extends OxObject> (object: DataType): DataType {
    const store = useIncludedStore()

    const currentStoreObjectIndex = store.objects.findIndex(
        (_object) => object.type === _object.type && object.id === _object.id
    )

    if (currentStoreObjectIndex === -1) {
        store.objects.push(object)
    }
    else {
        const currentStoreObject = store.objects[currentStoreObjectIndex]
        store.objects.splice(currentStoreObjectIndex, 1, merge(currentStoreObject, object))
    }

    return getObject(object.type, object.id) as DataType
}

/**
 * Get an object of given type and id from includedStore
 * @param {string} type - Object type
 * @param {string} id - Object id
 *
 * @returns {OxObject} the matching object
 */
export function getObject<DataType extends OxObject> (type: string, id: string): DataType {
    const store = useIncludedStore()
    return store.findObject(type, id) as DataType
}

/**
 * Get all objects of given type from includedStore, having an attribute "attributeName" matching "attributeValue"
 * @param {string} type - Object type
 * @param {string} attributeName - Name of the attribute
 * @param {string} attributeValue - Expected value for the attributeName
 *
 * @returns {OxObject[]} the matching objects
 */
export function getObjectsByAttribute<T extends OxObject> (type: string, attributeName: string, attributeValue: string): T[] {
    const store = useIncludedStore()
    return store.findAllObjectsByAttribute(type, attributeName, attributeValue) as T[]
}

/**
 * schemaStore management of several OxSchema
 * @param {OxSchema[]} schemas - Schemas to store
 *
 * @returns {OxSchema[]} the stored schemas
 */
export function storeSchemas (schemas: OxSchema[]): OxSchema[] {
    return schemas.map((schema) => {
        return storeSchema(schema)
    })
}

/**
 * schemaStore management for one OxSchema
 * @param {OxSchema} schema - The schema to store
 *
 * @returns {OxSchema} The stored schema
 */
export function storeSchema (schema: OxSchema): OxSchema {
    const store = useSchemaStore()

    // TODO : Logique de gérer si doublon, merge les deux objets pour ne pas perdre de la data
    store.schema.push(schema)

    return getSchema(schema.owner, schema.field)
}

/**
 * Get a field schema from schemaStore
 * @param {string} resourceName
 * @param {string} fieldName
 *
 * @returns {OxSchema} the matching schema
 */
export function getSchema (resourceName: string, fieldName: string): OxSchema {
    const store = useSchemaStore()
    const schema = store.findSchema(resourceName, fieldName)
    if (schema === undefined) {
        throw new Error("Schema for field " + resourceName + "." + fieldName + " has not been stored")
    }
    return schema as OxSchema
}
