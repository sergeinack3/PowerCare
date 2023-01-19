/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { defineStore } from "pinia"
import OxObject from "@/core/models/OxObject"

/**
 * Store containing all included objects (as OxObject) in JSON:API received
 */
export const useIncludedStore = defineStore("included", {
    state: () => ({
        objects: [] as OxObject[]
    }),
    getters: {
        findObject: (state) => {
            return (type: string, id: string) => state.objects.find(
                (object) => object.type === type && object.id === id
            )
        },
        findAllObjectsByAttribute: (state) => {
            return (type: string, attributeKey: string, attributeValue: string) => {
                return state.objects.filter((object) => {
                    return object.type === type && object.attributes[attributeKey].toString() === attributeValue
                })
            }
        }
    }
})
