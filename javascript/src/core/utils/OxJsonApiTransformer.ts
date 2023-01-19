/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxJsonApi, OxJsonApiData, OxJsonApiRelationships } from "@/core/types/OxApiTypes"
import OxObject from "@/core/models/OxObject"
import { OxSchema } from "@/core/types/OxSchema"
import { OxObjectAttributes } from "@/core/types/OxObjectTypes"

/**
 * Transform data value of a JSON:API into OxObject or array of OxObject
 * @param {OxObject} ObjectType - Type of OxObject that should be returned
 * @param {OxJsonApi} json - The JSON:API to transform
 *
 * @returns {OxObject | OxObject[]} the transformed objects from JSON:API
 */
export function dataTransformer<DataType extends OxObject> (
    ObjectType: new () => DataType,
    json: OxJsonApi
): DataType | DataType[] {
    let result

    // Check if data is array
    if (Array.isArray(json.data)) {
        result = json.data.map((item) => {
            return itemTransformer(ObjectType, item)
        })
    }
    else {
        result = itemTransformer(ObjectType, json.data)
    }
    return result
}

/**
 * Transform item from JSON:API to an OxObject
 * @param {OxObject} ObjectType - Type of OxObject that should be returned
 * @param {OxJsonApiData} item - Item to transform
 *
 * @returns {OxObject} the transformed object
 */
export function itemTransformer<DataType extends OxObject> (
    ObjectType: new () => DataType,
    item: OxJsonApiData
): DataType {
    const object = new ObjectType()
    object.id = item.id
    object.type = item.type
    object.attributes = item.attributes
    object.relationships = item.relationships
    object.links = item.links ?? {}
    object.meta = item.meta
    return object
}

/**
 * Get OxObject corresponding to included
 * @param {OxObject} ObjectType - Type of OxObject knowing the included relation type
 * @param {OxJsonApi} json - The JSON:API containing the included
 *
 * @returns {OxObject[]} the OxObjects corresponding to the included
 */
export function includedTransformer <DataType extends OxObject> (
    ObjectType: new () => DataType,
    json: OxJsonApi
): OxObject[] {
    if (!json.included) {
        return []
    }
    const object = new ObjectType()
    return json.included.map((include) => {
        const objectType = object.relationsTypes[include.type]
        if (objectType === undefined) {
            throw new Error("Type '" + include.type + "' missing in " + object.constructor.name + "'s _relationsTypes")
        }
        return itemTransformer(object.relationsTypes[include.type], include)
    })
}

/**
 * Transform a complete JSON:API into OxSchema[]
 * @param {OxJsonApi} json - The JSON:API to transform
 *
 * @returns {OxSchema[]} the transformed schema from JSON:API
 */
export function schemaTransformer (json: OxJsonApi): OxSchema[] {
    let result

    // Check if data is array
    if (Array.isArray(json.data)) {
        result = json.data.map((item) => {
            return item.attributes as OxSchema
        })
    }
    else {
        result = json.data.attributes as OxSchema
    }
    return result
}

/**
 * Returns schemas from JSON:API's meta key
 * @param {OxJsonApi} json - The JSON:API containing the schemas
 *
 * @returns {OxSchema[]} The extracted schema from JSON:API
 */
export function extractSchemasFromJsonApi (json: OxJsonApi): OxSchema[] {
    if (json.meta !== undefined && "schema" in json.meta &&
        Array.isArray(json.meta.schema)
    ) {
        return json.meta.schema as OxSchema[]
    }

    return []
}

/**
 * Transform OxObject to JSON:API item
 * @param {OxObject} object - Object to transform
 *
 * @returns {Partial<OxJsonApiData>} the JSON:API item
 */
export function oxObjectTransformer<O extends OxObject> (object: O): Partial<OxJsonApiData> {
    const jsonObject: Partial<OxJsonApiData> = {}
    jsonObject.id = object.id
    jsonObject.type = object.type
    jsonObject.attributes = object.attributes
    jsonObject.relationships = object.relationships as OxJsonApiRelationships

    return jsonObject
}

/**
 * Create JSON:API skeleton
 * @param {Partial<OxJsonApiData>[] | Partial<OxJsonApiData>} data
 */
export function createJsonApiSkeleton (data: Partial<OxJsonApiData>[] | Partial<OxJsonApiData>) {
    if (Array.isArray(data)) {
        data.forEach(function (obj) {
            if (obj.attributes) {
                sanitizeAttributes(obj.attributes)
            }
        })
    }
    else if (data.attributes) {
        sanitizeAttributes(data.attributes)
    }

    return {
        data: data
    }
}

/**
 * Does some modifications on OxObject attributes just before POST/PATCH API requests
 * @param {OxObjectAttributes} attributes
 */
function sanitizeAttributes (attributes: OxObjectAttributes) {
    for (const key in attributes) {
        // Cast boolean into 0 or 1 for backend compatibility
        attributes[key] = typeof attributes[key] === "boolean"
            ? Number(attributes[key])
            : attributes[key]
    }
}
