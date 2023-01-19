/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import GroupRadio from "@/components/Appbar/GroupRadio/GroupRadio"
import { Wrapper } from "@vue/test-utils"

/**
 * Test pour la classe GroupRadio
 */
export default class GroupRadioTest extends OxTest {
    protected component = GroupRadio

    private group = {
        _id: "1",
        text: "Group test",
        is_main: false,
        is_secondary: false
    }

    /**
     * @inheritDoc
     */
    protected vueComponent (props: object): GroupRadio {
        return this.mountComponent(props).vm as GroupRadio
    }

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object): Wrapper<GroupRadio> {
        return super.mountComponent(props) as Wrapper<GroupRadio>
    }

    public testActiveLineClasses (): void {
        const groupLine = this.vueComponent({ group: this.group, functionName: false, actived: true })
        this.assertEqual(this.privateCall(groupLine, "radioClass"), "active")
    }

    public testInactiveLineClasses (): void {
        const groupLine = this.vueComponent({ group: this.group, functionName: false, actived: false })
        this.assertEqual(this.privateCall(groupLine, "radioClass"), "")
    }

    public async testClickOnLine (): Promise<void> {
        const groupLine = this.mountComponent({ group: this.group, functionName: false, actived: false })
        this.privateCall(groupLine.vm, "selectGroup")
        await groupLine.vm.$nextTick()
        this.assertTrue(groupLine.emitted("click"))
        this.assertHaveLength(groupLine.emitted("click"), 1)
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        this.assertEqual(groupLine.emitted("click")[0], [this.group._id])
    }
}

(new GroupRadioTest()).launchTests()
