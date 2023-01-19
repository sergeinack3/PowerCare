/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import { moduleExists } from "@/core/utils/OxModulesManager"

/**
 * Test pour OxModulesManager
 */
export default class OxModulesManagerTest extends OxTest {
    protected component = "OxModulesManager"

    private modulesListJson = {
        modules: [
            "dmi",
            "doctolib",
            "sample",
            "system"
        ]
    }

    /**
     * Checks if core module "system" exists in the generated "javascript/src/core/utils/modulesList.json" file
     *   - "modulesList.json" is generated with Vue-CLI build
     *   - "moduleExists" function has an optional parameter "modulesListJson"
     *     => if this parameter is defined, the function uses it
     *     => if not, the function checks in the "modulesList.json" file if the module is into it
     *   - we use a core module like "system" for this test to be sure he is in every instance
     *
     *   /!\ If the "modulesList.json" file is not generated for some reason
     *         => this test crashes cause the "moduleExists" function returns "false"
     */
    public testCoreModuleExistsInJsonFile () {
        expect(moduleExists("system")).toBeTruthy()
    }

    /**
     * Checks if core module "thismoduledoesnotexists" doesn't exists in the generated
     * "javascript/src/core/utils/modulesList.json" file
     */
    public testCoreModuleNotExistsInJsonFile () {
        expect(moduleExists("thismoduledoesnotexists")).toBeFalsy()
    }

    /**
     * Checks if module "sample" exists in the custom Json property modulesListJson
     */
    public testModuleExistsInCustomJson () {
        expect(moduleExists("sample", this.modulesListJson)).toBeTruthy()
    }

    /**
     * Checks if module "thismoduledoesnotexists" doesn't exists in the custom Json property modulesListJson
     */
    public testModuleNotExistsInCustomJson () {
        expect(moduleExists("thismoduledoesnotexists", this.modulesListJson)).toBeFalsy()
    }
}

(new OxModulesManagerTest()).launchTests()
