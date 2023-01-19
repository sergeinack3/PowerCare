/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"
import { Group, Function } from "@/components/Appbar/Models/AppbarModel"
import { OxTextField } from "oxify"
const AppbarProvider = () => import(/* webpackChunkName: "GroupSelectorAppbarProvider" */ "@/components/Appbar/Providers/AppbarProvider")
const GroupLine = () => import(/* webpackChunkName: "GroupLine" */ "@/components/Appbar/GroupLine/GroupLine.vue")
const GroupRadio = () => import(/* webpackChunkName: "GroupRadio" */ "@/components/Appbar/GroupRadio/GroupRadio.vue")

/**
 * GroupSelector
 * Current group selection component
 */
@Component({ name: "GroupSelector", components: { GroupLine, GroupRadio, OxTextField } })
export default class GroupSelector extends OxVue {
    @Prop()
    private groupSelected!: Group

    @Prop()
    private functions!: Array<Function>

    @Prop()
    private groupsData!: Array<Group>

    @Prop({ default: false })
    private showRadio!: boolean

    private static readonly GROUP_NUMBER_FOR_SEARCH = 7

    private groups: Array<Group> = []
    private filter = ""
    private loadGroups = true

    private get groupLineOrGroupRadioComponent (): string {
        return this.showRadio ? "GroupRadio" : "GroupLine"
    }

    private get useSearchField (): boolean {
        return this.groups.length >= GroupSelector.GROUP_NUMBER_FOR_SEARCH
    }

    private get showCurrentGroup (): boolean {
        return this.groups.length === 0 && !this.loadGroups
    }

    private get filtredGroups (): Array<Group> {
        if (this.filter === "") {
            return this.groups
        }
        return this.groups.filter((group) => {
            return group.text.toLowerCase().includes(this.filter.toLowerCase())
        })
    }

    private includeGroup () {
        if (document.querySelector(".includeGroup") === null) {
            return []
        }
        return [document.querySelector(".includeGroup")]
    }

    private clickOutside () {
        this.$emit("input", false)
    }

    private async created () {
        if (this.groupsData) {
            this.groups = this.groupsData
            this.loadGroups = false
        }
        else {
            this.loadGroups = true
            // eslint-disable-next-line new-cap
            this.groups = (await new (await AppbarProvider()).default().getEtabs(this.groupSelected._links.groups))
            this.loadGroups = false
            this.setFocus()
        }
    }

    private activated () {
        if (!this.loadGroups) {
            this.setFocus()
        }
    }

    private isSelected (group: Group): boolean {
        return group._id === this.groupSelected._id
    }

    private changeFilter (value: string) {
        this.filter = value
    }

    private resetFilter () {
        this.filter = ""
    }

    private selectGroup (groupId: string) {
        if (groupId === this.groupSelected._id) {
            return
        }
        const url = new URL(window.location.href)
        url.searchParams.set("g", groupId)
        const _function = this.getFunctionByGroupId(groupId)
        if (_function) {
            url.searchParams.set("f", _function._id)
        }
        else {
            url.searchParams.delete("f")
        }

        window.location.href = url.toString()
    }

    private getFunctionName (group: Group): string | boolean {
        const _function = this.getFunctionByGroupId(group._id)
        if (_function) {
            return _function.text
        }
        return false
    }

    private setFocus () {
        if (this.groups.length === 0 || !this.useSearchField || this.showRadio) {
            return
        }
        this.$nextTick(() => {
            // eslint-disable-next-line @typescript-eslint/ban-ts-comment
            // @ts-ignore
            const el = this.$refs.searchField.$el.querySelector("input")
            if (el) {
                el.focus()
            }
        })
    }

    private getFunctionByGroupId (groupId: string): Function {
        return this.functions.find((func: Function) => {
            return func.group_id.toString() === groupId
        }) as Function
    }
}
