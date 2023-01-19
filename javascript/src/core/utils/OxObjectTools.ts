/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import OxObject from "@/core/models/OxObject"
import { OxObjectAttributes, OxObjectRelationships } from "@/core/types/OxObjectTypes"
import { isEqual, cloneDeep } from "lodash"

/**
 * Return all keys with values that have changed between original and mutated object
 *
 * @param {OxObject} from - Original object
 * @param {OxObject} to - Mutated object
 *
 * @returns {OxObjectAttributes} key: value that have changed
 */
export function getOxObjectsDiff<O extends OxObject> (from: O, to: O): OxObjectAttributes {
    const fromAttr = from.attributes
    const toAttr = to.attributes
    const diffAttr: OxObjectAttributes = {}
    for (const key in toAttr) {
        if (fromAttr[key] !== toAttr[key]) {
            if (!(key in fromAttr)) {
                console.warn("Key " + key + " does not exists on original object")
            }
            diffAttr[key] = toAttr[key]
        }
    }

    return diffAttr
}

/**
 * Return all relations that have changed between original and mutated object
 *
 * @param {OxObject} from - Original object
 * @param {OxObject} to - Mutated object
 *
 * @returns {OxObjectRelationships} relations that have changed
 */
export function getRelationDiff<O extends OxObject> (from: O, to: O): OxObjectRelationships {
    const fromRelationShips = from.relationships
    const toRelationShips = to.relationships

    if (!fromRelationShips) {
        return toRelationShips
    }

    const diffRelationShips: OxObjectRelationships = {}
    for (const key in toRelationShips) {
        if (!isEqual(fromRelationShips[key], toRelationShips[key])) {
            diffRelationShips[key] = toRelationShips[key]
        }
    }

    return diffRelationShips
}

/**
 * Clone deep object
 * @param {OxObject} object - Original object
 *
 * @returns {OxObject} Clone Object
 */
export function cloneObject<O extends OxObject> (object: O): O {
    return cloneDeep(object)
}
