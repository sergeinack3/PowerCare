/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import { callJSFunction } from "@/components/Core/OxUtilsCore"

/**
 * Test pour OxUtilsCore
 */
export default class OxUtilsCoreTest extends OxTest {
    protected component = "OxUtilsCore"

    public async testCallJSFunctionWithObject () {
        const mockedFunc = jest.fn(x => x + 10)
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        window.TestObject = {}
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        window.TestObject.testFunc = mockedFunc
        callJSFunction("TestObject.testFunc", [10])
        expect(mockedFunc).toBeCalled()
        expect(mockedFunc.mock.results[0].value).toEqual(20)
    }

    public async testCallJSFunctionWithFunctionWithTwoArguments () {
        const mockedFunc = jest.fn((x, y) => x + y)
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        window.testFunc = mockedFunc
        callJSFunction("testFunc", [10, 20])
        expect(mockedFunc).toBeCalled()
        expect(mockedFunc.mock.results[0].value).toEqual(30)
    }

}

(new OxUtilsCoreTest()).launchTests()
