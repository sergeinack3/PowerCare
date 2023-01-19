/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import GroupLine from "@/components/Appbar/GroupLine/GroupLine"
import { Wrapper } from "@vue/test-utils"

/**
 * Test pour la classe GroupLine
 */
export default class GroupLineTest extends OxTest {
    protected component = GroupLine

    private group = {
        _id: "1",
        text: "Group test",
        is_main: false,
        is_secondary: false
    }

    /**
     * @inheritDoc
     */
    protected vueComponent (props: object): GroupLine {
        return this.mountComponent(props).vm as GroupLine
    }

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object): Wrapper<GroupLine> {
        return super.mountComponent(props) as Wrapper<GroupLine>
    }

    public testActiveLineClasses (): void {
        const groupLine = this.vueComponent({ group: this.group, functionName: false, actived: true })
        this.assertEqual(this.privateCall(groupLine, "lineClass"), "active")
    }

    public testInactiveLineClasses (): void {
        const groupLine = this.vueComponent({ group: this.group, functionName: false, actived: false })
        this.assertEqual(this.privateCall(groupLine, "lineClass"), "")
    }

    public testMainGroupFunctionClass (): void {
        const mainGroup = { ...this.group }
        mainGroup.is_main = true
        const groupLine = this.vueComponent({ group: mainGroup, functionName: false, actived: true })
        this.assertEqual(this.privateCall(groupLine, "functionClass"), "mainFunction")
    }

    public testSecondaryGroupFunctionClass (): void {
        const secondaryGroup = { ...this.group }
        secondaryGroup.is_secondary = true
        const groupLine = this.vueComponent({ group: secondaryGroup, functionName: false, actived: false })
        this.assertEqual(this.privateCall(groupLine, "functionClass"), "secondaryFunction")
    }

    public testRandomGroupFunctionClass (): void {
        const groupLine = this.vueComponent({ group: this.group, functionName: false, actived: false })
        this.assertEqual(this.privateCall(groupLine, "functionClass"), "")
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

(new GroupLineTest()).launchTests()
