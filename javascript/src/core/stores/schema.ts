/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { defineStore } from "pinia"
import { OxSchema } from "@/core/types/OxSchema"

/**
 * Store containing all schemas
 */
export const useSchemaStore = defineStore("schema", {
    state: () => ({
        schema: [] as OxSchema[]
    }),
    getters: {
        findSchema: (state) => {
            return (resourceName: string, fieldName: string): OxSchema | undefined => state.schema.find(
                (object) => {
                    return object.owner === resourceName && object.field === fieldName
                }
            )
        },
        isSchemaExists: (state) => {
            return (resourceName: string, fieldset: string): boolean => state.schema.findIndex(
                (schema) => {
                    return schema.owner === resourceName && schema.fieldset === fieldset
                }
            ) !== -1
        }
    }
})
