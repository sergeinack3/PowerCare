/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import PlaceholderLine from "@/components/Appbar/PlaceholderLine/PlaceholderLine"
import {shallowMount, Wrapper} from '@vue/test-utils'
import { Placeholder } from "@/components/Appbar/Models/AppbarModel"
import Vuetify from 'vuetify'
import OxSerializerCore from "@/components/Core/OxSerializerCore"

/**
 * Test pour la classe GroupLine
 */
export default class PlaceholderLineTest extends OxTest {
    protected component = PlaceholderLine

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
    protected vueComponent (props: object): PlaceholderLine {
        return this.mountComponent(props).vm as PlaceholderLine
    }

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object): Wrapper<PlaceholderLine> {
        return shallowMount(this.component, {
            propsData: props,
            vuetify: new Vuetify()
        }) as Wrapper<PlaceholderLine>
    }

    public async testClick (): Promise<void> {
        const placeholderData = (await this.getPlaceholders())[1]
        const placeholderLine = this.mountComponent({ placeholder: placeholderData })
        const mockedClickFunc = jest.fn((x, y) => (x + y))
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        window.funcTest = mockedClickFunc
        this.privateCall(placeholderLine.vm, "click")
        expect(mockedClickFunc).toBeCalled()
        expect(mockedClickFunc.mock.results[0].value).toEqual("testArg1testArg2")
    }
}

(new PlaceholderLineTest()).launchTests()
