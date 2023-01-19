/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import { tr } from "@/core/utils/OxTranslator"

/**
 * OxTranslator tests
 */
export default class OxTranslatorTest extends OxTest {
    protected component = "OxTranslator"

    private tradFunction

    protected beforeTest () {
        super.beforeTest()
        this.tradFunction = jest.fn()
        // @ts-ignore
        window.$T = this.tradFunction
    }

    public testDefaultTrad () {
        tr("traduction-key")
        expect(this.tradFunction).toBeCalledWith("traduction-key", null)
    }

    public testTradWithPlural () {
        tr("traduction-key", null, true)
        expect(this.tradFunction).toBeCalledWith("traduction-key|pl", null)
    }

    public testTradWithValues () {
        tr("traduction-key", "added value")
        expect(this.tradFunction).toBeCalledWith("traduction-key", "added value")
    }
}

(new OxTranslatorTest()).launchTests()
