/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

let modulesListJsonFile

try {
    // eslint-disable-next-line @typescript-eslint/no-var-requires
    modulesListJsonFile = require("./modulesList.json")
}
catch (err) {
    console.error(err)
    modulesListJsonFile = null
}

type ModulesListJson = { modules: string[] } | null

/**
 * Checks if the specified module exists in the current project instance
 *   => it helps to prevent components import from non-existent modules
 *
 * @param {string} moduleName - Specified module name
 * @param {ModulesListJson|null} modulesListJson - Custom modules list Json (should only be used for test)
 *
 * @return {boolean}
 */
export function moduleExists (moduleName: string, modulesListJson: ModulesListJson = null): boolean {
    modulesListJson = modulesListJson ?? modulesListJsonFile

    return modulesListJson ? modulesListJson.modules.includes(moduleName) : false
}
