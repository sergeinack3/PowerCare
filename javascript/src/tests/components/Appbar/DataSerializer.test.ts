/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import { AppbarProp } from "@/components/Appbar/Models/AppbarModel"
import { appbarPropTransformer } from "@/components/Appbar/Serializer/DataSerializer"

/**
 * Serialization methods tests
 */
export default class DataSerializerTest extends OxTest {
    protected component = "DataSerializer"

    public testAppbarPropTransformerWithDataAndLinks () {
        const propData: AppbarProp = {
            datas: {
                a: "test",
                b: 2,
                c: true
            },
            links: {
                link1: "test",
                link2: "test2"
            }
        }
        expect(appbarPropTransformer(propData)).toEqual({
            a: "test",
            b: 2,
            c: true,
            _links: {
                link1: "test",
                link2: "test2"
            }
        })
    }

    public testAppbarPropTransformerWithOnlyData () {
        const propData: AppbarProp = {
            datas: {
                a: "test",
                b: 2,
                c: true
            }
        }
        expect(appbarPropTransformer(propData)).toEqual({
            a: "test",
            b: 2,
            c: true,
            _links: undefined
        })
    }
}

(new DataSerializerTest()).launchTests()
