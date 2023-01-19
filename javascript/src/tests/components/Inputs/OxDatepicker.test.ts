/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import OxDatepicker from "@/components/Inputs/OxField/OxDatepicker/OxDatepicker"
import { OxTest, OxIconCore } from "oxify"
import { Wrapper } from "@vue/test-utils"

/**
 * Test pour la classe OxDatepicker
 */
export default class OxDatepickerTest extends OxTest {
    protected component = OxDatepicker

    /**
     * @inheritDoc
     */
    protected vueComponent (props: object = {}): OxDatepicker {
        return this.mountComponent(props).vm as OxDatepicker
    }

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object): Wrapper<OxDatepicker> {
        return super.mountComponent(props) as Wrapper<OxDatepicker>
    }

    /**
     * Test rcupration valeur de date
     * @param initialDate { string }
     * @param expectedDate { string }
     */
    @OxTest.scenarios(
        ["WithCorrectDate", "2021-07-31 15:38:00", "31/07/2021"],
        ["WithWrongDate", "", ""]
    )
    public testDateValue (initialDate: string, expectedDate: string): void {
        this.assertEqual(
            this.privateCall(
                this.vueComponent({ value: initialDate }),
                "dateValue"
            ),
            expectedDate
        )
    }

    @OxTest.scenarios(
        ["WithTime", "time", OxIconCore.get("time")],
        ["WithDate", "date", OxIconCore.get("calendar")],
        ["WithDateTime", "datetime", OxIconCore.get("calendar")],
        ["WithMonth", "month", OxIconCore.get("calendar")]
    )
    public testCalendarIcon (dateFormat: string, expectedIcon: string): void {
        this.assertEqual(
            this.privateCall(
                this.vueComponent({ format: dateFormat }),
                "calendarIcon"
            ),
            expectedIcon
        )
    }

    @OxTest.scenarios(
        ["WithTime", "time", "date"],
        ["WithDate", "date", "date"],
        ["WithDateTime", "datetime", "date"],
        ["WithMonth", "month", "month"]
    )
    public testType (dateFormat: string, expectedType: string): void {
        this.assertEqual(
            this.privateCall(
                this.vueComponent({ format: dateFormat }),
                "type"
            ),
            expectedType
        )
    }
}

(new OxDatepickerTest()).launchTests()
