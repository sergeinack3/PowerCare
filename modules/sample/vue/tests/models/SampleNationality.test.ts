/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import SampleNationality from "@modules/sample/vue/models/SampleNationality"

/**
 * SampleNationality tests
 */
export default class SampleNationalityTest extends OxTest {
    protected component = "SampleNationality"

    public testNationalityEmptyName () {
        const nationality = new SampleNationality()
        expect(nationality.name).toEqual("")
    }

    public testNationalityNotEmptyName () {
        const nationality = new SampleNationality()
        nationality.attributes.name = "anglaise"
        expect(nationality.name).toEqual("Anglaise")
    }
}

(new SampleNationalityTest()).launchTests()
