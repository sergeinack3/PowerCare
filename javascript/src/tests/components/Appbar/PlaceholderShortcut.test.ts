/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import { Wrapper } from "@vue/test-utils"
import PlaceholderShortcut from "@/components/Appbar/PlaceholderShortcut/PlaceholderShortcut"
import OxSerializerCore from "@/components/Core/OxSerializerCore"
import { Placeholder } from "@/components/Appbar/Models/AppbarModel"

/**
 * Test pour la classe PlaceholderShortcut
 */
export default class NavTabTest extends OxTest {
    protected component = PlaceholderShortcut

    private placeholdersData = {
        data: [{
            type: "shortcut",
            id: "368145e1a559e52d8da68f489a2bbe13",
            attributes: {
                label: "Saisir une prestation",
                icon: "time",
                disabled: false,
                action: "OXPresta.openQuickPrestationMaker",
                action_args: null,
                init_action: null,
                counter: null
            }
        }, {
            type: "shortcut",
            id: "decba95f634235b27a2b3e9407fe871f",
            attributes: {
                label: "Acces au porte documents",
                icon: "folderOpen",
                disabled: false,
                action: "funcTest",
                action_args: ["testArg1", "testArg2"],
                init_action: null,
                counter: 1
            }
        }, {
            type: "shortcut",
            id: "IDTest",
            attributes: {
                label: "Acces a la messagerie interne",
                icon: "accountGroup",
                disabled: false,
                action: "Messagerie.openModal",
                action_args: ["internal"],
                init_action: "testFunc",
                counter: 0
            }
        }, {
            type: "shortcut",
            id: "7ce3ae8be3a241302195c4a0500e4c6f",
            attributes: {
                label: "Acces a la messagerie",
                icon: "appfine",
                disabled: false,
                action: "",
                action_args: null,
                init_action: null,
                counter: 0
            }
        }, {
            type: "shortcut",
            id: "7ce3ae8be3a241302195c4a0500e4c6f",
            attributes: {
                label: "Ecap",
                icon: "ecap",
                disabled: false,
                action: "",
                action_args: null,
                init_action: null,
                counter: 0
            }
        }]
    }

    private async getPlaceholders (): Promise<Array<Placeholder>> {
        return (await new OxSerializerCore(
            this.placeholdersData.data,
            {},
            {},
            []
        ).translateData()).data as unknown as Array<Placeholder>
    }

    /**
     * @inheritDoc
     */
    protected vueComponent (props: object): PlaceholderShortcut {
        return this.mountComponent(props).vm as PlaceholderShortcut
    }

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object): Wrapper<PlaceholderShortcut> {
        return super.mountComponent(props) as Wrapper<PlaceholderShortcut>
    }

    public async testShowCounterIfExist () {
        const placeholderData = (await this.getPlaceholders())[1]
        const placeholder = this.mountComponent({ placeholder: placeholderData })
        this.assertTrue(this.privateCall(placeholder.vm, "showCounter"))
    }

    public async testHideCounterIfNotExist () {
        const placeholderData = (await this.getPlaceholders())[0]
        const placeholder = this.mountComponent({ placeholder: placeholderData })
        this.assertFalse(this.privateCall(placeholder.vm, "showCounter"))
    }

    public async testInitAction () {
        const placeholderData = (await this.getPlaceholders())[2]
        const mockedFunc = jest.fn((x, y) => [x, y])
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        window.testFunc = mockedFunc
        this.mountComponent({ placeholder: placeholderData })
        expect(mockedFunc).toBeCalled()
        expect(mockedFunc.mock.results[0].value).toEqual(["IDTest_placeholder", "IDTest_counter"])
    }

    public async testClick () {
        const placeholderData = (await this.getPlaceholders())[1]
        const placeholder = this.mountComponent({ placeholder: placeholderData })
        const mockedClickFunc = jest.fn((x, y) => (x + y))
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        window.funcTest = mockedClickFunc
        this.privateCall(placeholder.vm, "click")
        expect(mockedClickFunc).toBeCalled()
        expect(mockedClickFunc.mock.results[0].value).toEqual("testArg1testArg2")
    }

    public async testClickEvent() {
        const placeholderData = (await this.getPlaceholders())[0]
        const placeholder = this.mountComponent({ placeholder: placeholderData, flat: true })
        this.privateCall(placeholder.vm, "click")
        this.assertTrue(placeholder.emitted("click"))
    }

    public async testShowAppfineIcon () {
        const placeholderData = (await this.getPlaceholders())[3]
        const placeholder = this.mountComponent({ placeholder: placeholderData })
        this.assertTrue(this.privateCall(placeholder.vm, "showAppfine"))
        this.assertFalse(this.privateCall(placeholder.vm, "showIcon"))
    }

    public async testShowEcapImage () {
        const placeholderData = (await this.getPlaceholders())[4]
        const placeholder = this.mountComponent({ placeholder: placeholderData })
        this.assertTrue(this.privateCall(placeholder.vm, "showEcap"))
        this.assertFalse(this.privateCall(placeholder.vm, "showIcon"))
    }

    public async testShowDefaultIcon () {
        const placeholderData = (await this.getPlaceholders())[0]
        const placeholder = this.mountComponent({ placeholder: placeholderData })
        this.assertTrue(this.privateCall(placeholder.vm, "showIcon"))
        this.assertFalse(this.privateCall(placeholder.vm, "showEcap"))
        this.assertFalse(this.privateCall(placeholder.vm, "showAppfine"))
    }
}

(new NavTabTest()).launchTests()
