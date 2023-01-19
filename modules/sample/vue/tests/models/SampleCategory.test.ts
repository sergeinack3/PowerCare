/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import SampleCategory from "@modules/sample/vue/models/SampleCategory"

/**
 * SampleCategory tests
 */
export default class SampleCategoryTest extends OxTest {
    protected component = "SampleCategory"

    public testCategoryEmptyName () {
        const category = new SampleCategory()
        expect(category.name).toEqual("")
    }

    public testCategoryNotEmptyName () {
        const category = new SampleCategory()
        category.attributes.name = "category name"
        expect(category.name).toEqual("Category name")
    }
}

(new SampleCategoryTest()).launchTests()
