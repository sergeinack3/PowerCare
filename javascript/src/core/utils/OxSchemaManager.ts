/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { useSchemaStore } from "@/core/stores/schema"
import oxApiService from "@/core/utils/OxApiService"
import { AxiosResponse } from "axios"
import { storeSchemas } from "@/core/utils/OxStorage"
import { OxJsonApi } from "@/core/types/OxApiTypes"
import { schemaTransformer } from "@/core/utils/OxJsonApiTransformer"

const schemaUrl = "api/schemas/"

/**
 * Ensure application provides all schemas for given resource
 * @param {string} resourceName - Resource type name
 * @param {string[]} fieldsets - Resource fieldset
 *
 * @return {boolean} true when process is ended successfully
 */
export async function prepareForm (resourceName: string, fieldsets: string[] = ["default"]): Promise<boolean> {
    // Check if asked fields are in schemaStore
    const schemaStore = useSchemaStore()
    const missingFieldsets = fieldsets.filter((fieldset) => {
        return !schemaStore.isSchemaExists(resourceName, fieldset)
    })

    // If having missing fieldsets, let's get them
    if (missingFieldsets.length > 0) {
        const json = (await getSchemasFromJsonApiRequest(resourceName, missingFieldsets)).data
        storeSchemas(schemaTransformer(json))
    }

    return true
}

/**
 * Get resource schemas via API
 * @param {string} resourceName - Resource type name
 * @param {string[]} fieldsets - Resource fieldset
 *
 * @returns {OxSchema[]}
 */
function getSchemasFromJsonApiRequest (resourceName: string, fieldsets: string[]): Promise<AxiosResponse<OxJsonApi>> {
    let url = schemaUrl + resourceName
    if (fieldsets.length > 0) {
        url += "?fieldsets=" + fieldsets.join(",")
    }
    return oxApiService.get(url)
}
