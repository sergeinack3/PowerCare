/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import OxObject from "@/core/models/OxObject"
import OxCollection from "@/core/models/OxCollection"
import { OxJsonApi, OxJsonApiRelationships } from "@/core/types/OxApiTypes"
import {
    createJsonApiSkeleton,
    dataTransformer,
    extractSchemasFromJsonApi,
    includedTransformer,
    oxObjectTransformer
} from "@/core/utils/OxJsonApiTransformer"
import { storeObjects, storeSchemas } from "@/core/utils/OxStorage"
import oxApiService from "@/core/utils/OxApiService"
import { getOxObjectsDiff, getRelationDiff } from "@/core/utils/OxObjectTools"
import { OxCollectionMeta } from "@/core/types/OxCollectionTypes"
import { OxObjectAttributes } from "@/core/types/OxObjectTypes"

/**
 * Return an OxObject from an url
 * @param {OxObject} ObjectType - Type of OxObject that should be returned
 * @param {string} url - Resource url
 *
 * @returns {OxObject}
 */
export async function getObjectFromJsonApiRequest<DataType extends OxObject> (
    ObjectType: new() => DataType,
    url: string
): Promise<DataType> {
    const resource = await oxApiService.get<OxJsonApi>(url)
    return getObjectFromJsonApi(ObjectType, resource.data)
}

/**
 * Return a OxCollection from an url
 * @param {OxObject} ObjectType - Type of OxObject that should be returned
 * @param {string} url - Resource url
 *
 * @returns {OxCollection}
 */
export async function getCollectionFromJsonApiRequest<DataType extends OxObject> (
    ObjectType: new() => DataType,
    url: string
): Promise<OxCollection<DataType>> {
    const resource = await oxApiService.get<OxJsonApi>(url)
    return getCollectionFromJsonApi(ObjectType, resource.data)
}

/**
 * Return an OxObject from a JSON:API
 * @param {OxObject} ObjectType - Type of OxObject that should be returned
 * @param {OxJsonApi} json - The JSON:API containing the object
 *
 * @returns {OxObject}
 */
export function getObjectFromJsonApi<DataType extends OxObject> (
    ObjectType: new() => DataType,
    json: OxJsonApi
): DataType {
    const result = dataTransformer(ObjectType, json)

    if (Array.isArray(result)) {
        throw new Error("Get array instead of object")
    }

    storeIncluded(ObjectType, json)
    storeSchemas(extractSchemasFromJsonApi(json))

    return result
}

/**
 * Return a OxCollection from a JSON:API
 * @param {OxObject} ObjectType - Type of OxObject that should be returned
 * @param {OxJsonApi} json - The JSON:API containing the objects
 *
 * @returns {OxCollection}
 */
export function getCollectionFromJsonApi<DataType extends OxObject> (
    ObjectType: new() => DataType,
    json: OxJsonApi
): OxCollection<DataType> {
    const collection = new OxCollection<DataType>()
    let result = dataTransformer(ObjectType, json)

    if (!Array.isArray(result)) {
        result = [result]
    }

    collection.objects = result
    collection.links = json.links
    collection.meta = json.meta as OxCollectionMeta | undefined

    storeIncluded(ObjectType, json)
    storeSchemas(extractSchemasFromJsonApi(json))

    return collection
}

/**
 * Post to backend given objects in JSON:API format
 * @param objects - Object or objects to store
 * @param {string } url - Url to call
 */
export async function createJsonApiObjects<O extends OxObject> (
    objects: O,
    url: string
): Promise<O>;
export async function createJsonApiObjects<O extends OxObject> (
    objects: O[],
    url: string
): Promise<OxCollection<O>>;
export async function createJsonApiObjects (
    objects: [],
    url: string
): Promise<[]>;
export async function createJsonApiObjects<O extends OxObject> (
    objects: O | O[] | [],
    url: string
): Promise<O | OxCollection<O> | []> {
    const isArray = Array.isArray(objects)
    let data
    if (isArray) {
        data = objects.map((object: O) => {
            return oxObjectTransformer(object)
        })
    }
    else {
        data = oxObjectTransformer(objects)
    }
    const response = (await oxApiService.post<OxJsonApi>(url, createJsonApiSkeleton(data))).data

    if (isArray && !objects.length) {
        return []
    }
    else if (isArray) {
        return getCollectionFromJsonApi(objects[0].constructor as new() => O, response)
    }
    else {
        // Get first object if only one object is posted
        response.data = response.data[0]
        return getObjectFromJsonApi(objects.constructor as new() => O, response)
    }
}

/**
 * Update backend object based on modified fields between original object and mutated object
 * @param {OxObject} from - Original object
 * @param {OxObject} to - Mutated object
 *
 * @returns {OxObject} The updated object
 */
export async function updateJsonApiObject<O extends OxObject> (from: O, to: O): Promise<O> {
    const diffAttr = getOxObjectsDiff(from, to)
    const diffRelations = getRelationDiff(from, to)
    const object = oxObjectTransformer(to)
    object.attributes = diffAttr
    object.relationships = diffRelations as OxJsonApiRelationships

    if (from.constructor.name !== to.constructor.name) {
        throw new Error("No matching object type between " + from.constructor.name + " and " + to.constructor.name)
    }

    if (to.links.self === undefined) {
        throw new Error("Missing self links on object")
    }

    const response = (await oxApiService.patch<OxJsonApi>(to.links.self, createJsonApiSkeleton(object))).data
    return getObjectFromJsonApi(to.constructor as new() => O, response)
}

/**
 * Update backend object based on given attribute's values
 *
 * @param {OxObject} object - Object to update
 * @param {OxObjectAttributes} attributes - Attribute's values we want to update on the given object
 *
 * @returns {OxObject} The updated object
 */
export async function updateJsonApiObjectFields<O extends OxObject> (
    object: O,
    attributes: OxObjectAttributes
): Promise<O> {
    const objectJsonApi = oxObjectTransformer(object)
    objectJsonApi.attributes = attributes
    objectJsonApi.relationships = {} as OxJsonApiRelationships

    if (object.links.self === undefined) {
        throw new Error("Missing self links on object")
    }

    const response = (await oxApiService.patch<OxJsonApi>(object.links.self, createJsonApiSkeleton(objectJsonApi))).data
    return getObjectFromJsonApi(object.constructor as new() => O, response)
}

/**
 * Delete backend object
 * @param {OxObject} object - Original object
 *
 * @returns {boolean} Success request
 */
export async function deleteJsonApiObject<O extends OxObject> (object: O): Promise<boolean> {
    if (object.links.self === undefined) {
        throw new Error("Missing self links on object")
    }

    await oxApiService.delete<OxJsonApi>(object.links.self)

    return true
}

/**
 * Store included of given JSON:API
 *
 * @param {OxObject} ObjectType - Type of OxObject that should be store
 * @param {OxJsonApi} json - The JSON:API containing the included
 */
function storeIncluded<T extends OxObject> (ObjectType: new () => T, json: OxJsonApi) {
    const included = includedTransformer(ObjectType, json)
    if (included.length) {
        storeObjects(included)
    }
}
