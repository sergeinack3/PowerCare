/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import { shallowMount, Wrapper } from "@vue/test-utils"
import GroupSelector from "@/components/Appbar/GroupSelector/GroupSelector"

/* eslint-disable dot-notation */

/**
 * Test pour la classe GroupSelector
 */
export default class GroupSelectorTest extends OxTest {
    protected component = GroupSelector
    private functions = [
        {
            _id: "1",
            text: "Fonction 1",
            group_id: "1",
            is_main: true
        },
        {
            _id: "2",
            text: "Fonction 2",
            group_id: "3",
            is_main: false
        }
    ]

    private groups = [
        {
            _id: "1",
            text: "Group 1",
            raison_sociale: null,
            is_main: true,
            is_secondary: false
        },
        {
            _id: "2",
            text: "Group 2",
            raison_sociale: null,
            is_main: false,
            is_secondary: false

        },
        {
            _id: "3",
            text: "Group 3",
            raison_sociale: null,
            is_main: false,
            is_secondary: false

        }
    ]

    private groupSelected = {
        _id: "1",
        text: "Group 1",
        raison_sociale: null,
        is_main: true,
        is_secondary: false
    }

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object, stubs: any[] = []): Wrapper<GroupSelector> {
        return super.mountComponent(props, stubs) as Wrapper<GroupSelector>
    }

    public testUseSearchFieldWhenMoreThanSixGroups () {
        const groups = [...this.groups]
        groups.push(
            {
                _id: "4",
                text: "Group 4",
                raison_sociale: null,
                is_main: false,
                is_secondary: false

            },
            {
                _id: "5",
                text: "Group 5",
                raison_sociale: null,
                is_main: false,
                is_secondary: false

            },
            {
                _id: "6",
                text: "Group 6",
                raison_sociale: null,
                is_main: false,
                is_secondary: false

            },
            {
                _id: "7",
                text: "Group 7",
                raison_sociale: null,
                is_main: false,
                is_secondary: false

            }
        )
        const groupSelector = this.mountComponent({
            groupSelected: this.groupSelected,
            functions: this.functions,
            groupsData: groups
        })
        this.assertTrue(this.privateCall(groupSelector.vm, "useSearchField"))
    }

    public testDontUseSearchFieldWhenLessThanSevenGroups (): void {
        const groupSelector = this.mountComponent({
            groupSelected: this.groupSelected,
            functions: this.functions,
            groupsData: this.groups
        })
        this.assertFalse(this.privateCall(groupSelector.vm, "useSearchField"))
    }

    public testShowCurrentGroupWhenNoGroup (): void {
        const groupSelector = this.mountComponent({
            groupSelected: this.groupSelected,
            functions: this.functions,
            groupsData: []
        })
        this.assertTrue(this.privateCall(groupSelector.vm, "showCurrentGroup"))
    }

    public async testClickOutside (): Promise<void> {
        const groupSelector = this.mountComponent({
            groupSelected: this.groupSelected,
            functions: this.functions,
            groupsData: this.groups
        })
        this.privateCall(groupSelector.vm, "clickOutside")
        await groupSelector.vm.$nextTick()
        this.assertTrue(groupSelector.emitted("input"))
        // @ts-ignore
        this.assertEqual(groupSelector.emitted("input")[0], [false])
    }

    public testSelectedGroup (): void {
        const groupSelector = this.mountComponent({
            groupSelected: this.groupSelected,
            functions: this.functions,
            groupsData: this.groups
        })
        this.assertTrue(this.privateCall(groupSelector.vm, "isSelected", this.groupSelected))
    }

    public testNonSelectionGroup (): void {
        const otherGroup = {
            _id: "10",
            text: "Group 10",
            raison_sociale: null,
            is_main: false,
            is_secondary: false
        }
        const groupSelector = this.mountComponent({
            groupSelected: this.groupSelected,
            functions: this.functions,
            groupsData: this.groups
        })
        this.assertFalse(this.privateCall(groupSelector.vm, "isSelected", otherGroup))
    }

    public testGetExistentFunctionName (): void {
        const groupSelector = this.mountComponent({
            groupSelected: this.groupSelected,
            functions: this.functions,
            groupsData: this.groups
        })
        this.assertEqual(
            this.privateCall(groupSelector.vm, "getFunctionName", this.groups[2]),
            "Fonction 2"
        )
    }

    public testGetNonExistentFunctionName (): void {
        const groupSelector = this.mountComponent({
            groupSelected: this.groupSelected,
            functions: this.functions,
            groupsData: this.groups
        })
        this.assertFalse(this.privateCall(groupSelector.vm, "getFunctionName", this.groups[1]))
    }

    public async testSearchInGroups () {
        const groups = [...this.groups]
        groups.push(
            {
                _id: "4",
                text: "Group 4",
                raison_sociale: null,
                is_main: false,
                is_secondary: false

            },
            {
                _id: "5",
                text: "Group 5",
                raison_sociale: null,
                is_main: false,
                is_secondary: false

            },
            {
                _id: "6",
                text: "Group 6",
                raison_sociale: null,
                is_main: false,
                is_secondary: false

            },
            {
                _id: "7",
                text: "Group 7",
                raison_sociale: null,
                is_main: false,
                is_secondary: false

            }
        )
        const groupSelector = this.mountComponent({
            groupSelected: this.groupSelected,
            functions: this.functions,
            groupsData: groups
        })
        this.privateCall(groupSelector.vm, "changeFilter", "oup 3")
        await groupSelector.vm.$nextTick()
        this.assertEqual(
            this.privateCall(groupSelector.vm, "filtredGroups"),
            [{
                _id: "3",
                text: "Group 3",
                raison_sociale: null,
                is_main: false,
                is_secondary: false
            }]
        )
        this.privateCall(groupSelector.vm, "resetFilter")
        await groupSelector.vm.$nextTick()
        this.assertHaveLength(this.privateCall(groupSelector.vm, "filtredGroups"), 7)
    }

    public testGetFunctionByGroup () {
        const groupSelector = this.mountComponent({
            groupSelected: this.groupSelected,
            functions: this.functions,
            groupsData: this.groups
        })
        this.assertEqual(
            groupSelector.vm["getFunctionByGroupId"]("1"),
            {
                _id: "1",
                text: "Fonction 1",
                group_id: "1",
                is_main: true
            }
        )
    }

    public testGroupRadioWhenShowRadio () {
        const groupSelector = this.mountComponent(
            {
                groupSelected: this.groupSelected,
                functions: this.functions,
                groupsData: this.groups,
                showRadio: true
            },
            ["GroupRadio"]
        )

        this.assertEqual(this.privateCall(groupSelector.vm, "groupLineOrGroupRadioComponent"), "GroupRadio")
    }

    public testGroupLineWhenShowRadioDefault () {
        const groupSelector = this.mountComponent({
            groupSelected: this.groupSelected,
            functions: this.functions,
            groupsData: this.groups
        })

        this.assertEqual(this.privateCall(groupSelector.vm, "groupLineOrGroupRadioComponent"), "GroupLine")
    }

    public testSelectGroup () {
        const hrefSpy = jest.fn()
        // @ts-ignore
        delete window.location
        // @ts-ignore
        window.location = {}
        Object.defineProperty(window.location, "href", {
            get: () => "http://localhost/mediboard",
            set: hrefSpy
        })
        const groupSelector = this.mountComponent({
            groupSelected: this.groupSelected,
            functions: this.functions,
            groupsData: this.groups
        })
        this.privateCall(groupSelector.vm, "selectGroup", "2")
        expect(hrefSpy).toBeCalledWith("http://localhost/mediboard?g=2")
    }

    public testSelectGroupWithFunction () {
        const hrefSpy = jest.fn()
        // @ts-ignore
        delete window.location
        // @ts-ignore
        window.location = {}
        Object.defineProperty(window.location, "href", {
            get: () => "http://localhost/mediboard",
            set: hrefSpy
        })
        const groupSelector = this.mountComponent({
            groupSelected: this.groupSelected,
            functions: this.functions,
            groupsData: this.groups
        })
        this.privateCall(groupSelector.vm, "selectGroup", "3")
        expect(hrefSpy).toBeCalledWith("http://localhost/mediboard?g=3&f=2")
    }
}

(new GroupSelectorTest()).launchTests()
