/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"
import { OxButton, OxIcon, OxSwitch } from "oxify"
import { Function, Group, InfoMaj, UserInfo } from "@/components/Appbar/Models/AppbarModel"
const GroupSelector = () => import(/* webpackChunkName: "GroupSelector" */ "@/components/Appbar/GroupSelector/GroupSelector.vue")

/**
 * UserAccount
 * User account component
 */
@Component({ components: { OxButton, OxIcon, OxSwitch, GroupSelector } })
export default class UserAccount extends OxVue {
    @Prop({ default: false })
    private value!: boolean

    @Prop()
    private userInfo!: UserInfo

    @Prop()
    private infoMaj!: InfoMaj

    @Prop({ default: false })
    private isTamm!: boolean

    @Prop({ default: false })
    private showGroup!: boolean

    @Prop()
    private groupSelected!: Group

    @Prop()
    private functions!: Array<Function>

    private get showPassword (): boolean {
        return this.userInfo._is_patient || this.userInfo._can_change_password === "1"
    }

    private get isNotPatient (): boolean {
        return !this.userInfo._is_patient
    }

    private get darkTheme (): boolean {
        return this.userInfo._dark_mode
    }

    private get showMajInfo (): boolean {
        return this.infoMaj.title !== ""
    }

    private clickOutside () {
        this.$emit("input", false)
    }

    private lockSession () {
        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
        // @ts-ignore
        window.Session.lock()
    }

    private switchUser () {
        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
        // @ts-ignore
        window.UserSwitch.popup()
    }

    private logout () {
        document.location.href = this.userInfo._links.logout
    }

    private switchTheme (value: boolean) {
        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
        // @ts-ignore
        // eslint-disable-next-line brace-style
        window.App.savePref("mediboard_ext_dark", value ? "1" : "0", function () { location.reload() })
    }

    private changePassword () {
        if (this.userInfo._is_patient) {
            // eslint-disable-next-line @typescript-eslint/ban-ts-comment
            // @ts-ignore
            window.patientChangePassword()
        }
        else {
            // eslint-disable-next-line @typescript-eslint/ban-ts-comment
            // @ts-ignore
            window.popChgPwd()
        }
    }

    private editAccount () {
        window.location.href = this.userInfo._links.edit_infos
    }

    private showCGU () {
        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
        // @ts-ignore
        window.MediboardExt.showCGU()
    }
}
