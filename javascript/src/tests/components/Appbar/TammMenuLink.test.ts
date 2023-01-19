/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import TammMenuLink from "@/components/Appbar/NavModules/NavModulesTamm/TammMenuLink/TammMenuLink"
import { MenuLink } from "@/components/Appbar/Models/AppbarModel"
import { Wrapper } from "@vue/test-utils"

/**
 * Test pour la classe TammMenuLink
 */
export default class TammMenuLinkTest extends OxTest {
    protected component = TammMenuLink

    private actionLink: MenuLink = {
        title: "LinkTest",
        href: null,
        action: "TestObject.funcTest"
    }

    private simpleActionLink: MenuLink = {
        title: "LinkTest",
        href: null,
        action: "funcTest"
    }

    private hrefLink: MenuLink = {
        title: "LinkTest",
        href: "?m=moduleTest",
        action: null
    }

    private externalHrefLink: MenuLink = {
        title: "LinkTest",
        href: "https://link-test",
        action: null
    }

    /**
     * @inheritDoc
     */
    protected vueComponent (props: object): TammMenuLink {
        return this.mountComponent(props).vm as TammMenuLink
    }

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object): Wrapper<TammMenuLink> {
        return super.mountComponent(props) as Wrapper<TammMenuLink>
    }

    public testHrefLinkIsLink () {
        const link = this.vueComponent({ link: this.hrefLink })
        this.assertTrue(this.privateCall(link, "isLink"))
    }

    public testActionLinkIsNotLink () {
        const link = this.vueComponent({ link: this.actionLink })
        this.assertFalse(this.privateCall(link, "isLink"))
    }

    public testTargetForInteralLink () {
        const link = this.vueComponent({ link: this.hrefLink })
        this.assertEqual(this.privateCall(link, "target"), "_self")
    }

    public testTargetForExternalLink () {
        const link = this.vueComponent({ link: this.externalHrefLink })
        this.assertEqual(this.privateCall(link, "target"), "_blank")
    }

    public testCallJSFunction () {
        const link = this.vueComponent({ link: this.simpleActionLink })
        const mockedFunc = jest.fn()
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        window.funcTest = mockedFunc
        this.privateCall(link, "callFunction")
        expect(mockedFunc).toBeCalled()
    }

    public testDontCallFunction () {
        const link = this.vueComponent({ link: this.hrefLink })
        const mockedFunc = jest.fn()
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        window.funcTest = mockedFunc
        this.privateCall(link, "callFunction")
        expect(mockedFunc).not.toBeCalled()
    }

    public testCallJSFunctionWithObject () {
        const link = this.vueComponent({ link: this.actionLink })
        const mockedFunc = jest.fn()
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        window.TestObject = {}
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        window.TestObject.funcTest = mockedFunc
        this.privateCall(link, "callFunction")
        expect(mockedFunc).toBeCalled()
    }
}

(new TammMenuLinkTest()).launchTests()
