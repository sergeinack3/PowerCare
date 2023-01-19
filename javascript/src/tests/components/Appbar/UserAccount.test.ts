/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import UserAccount from "@/components/Appbar/UserAccount/UserAccount"
import { Wrapper } from "@vue/test-utils"

/**
 * Test pour la classe UserAccount
 */
export default class UserAccountTest extends OxTest {
    protected component = UserAccount

    private userInfo = {
        _can_change_password: "1",
        _color: "CCCCFF",
        _dark_mode: false,
        _font_color: "000000",
        _id: "985",
        _initial: "YG",
        _is_admin: false,
        _is_patient: false,
        _links: {},
        _type: "mediuser",
        _ui_style: "mediboard",
        _user_first_name: "Yvan",
        _user_last_name: "GRADMIN",
        _user_sexe: "u",
        _user_username: "yvang"
    }

    private infoMaj = {
        release_title: "Màj il y a 5 semaines",
        title: "\nMise à jour le 02/12/2021 09:00:00\nRévision : 1111"
    }

    /**
     * @inheritDoc
     */
    protected vueComponent (props: object): UserAccount {
        return this.mountComponent(props).vm as UserAccount
    }

    /**
     * @inheritDoc
     */
    protected mountComponent (props: object): Wrapper<UserAccount> {
        return super.mountComponent(props) as Wrapper<UserAccount>
    }

    public testShowPasswordIfCanChange () {
        const userAccount = this.vueComponent({ userInfo: this.userInfo, infoMaj: this.infoMaj })
        this.assertTrue(this.privateCall(userAccount, "showPassword"))
    }

    public testShowPasswordIfPatient () {
        const userPatientInfo = { ...this.userInfo }
        userPatientInfo._is_patient = true
        userPatientInfo._can_change_password = "0"
        const userAccount = this.vueComponent({ userInfo: userPatientInfo, infoMaj: this.infoMaj })
        this.assertTrue(this.privateCall(userAccount, "showPassword"))
    }

    public testHidePasswordIfCantChange () {
        const userInfo = { ...this.userInfo }
        userInfo._can_change_password = "0"
        const userAccount = this.vueComponent({ userInfo: userInfo, infoMaj: this.infoMaj })
        this.assertFalse(this.privateCall(userAccount, "showPassword"))
    }

    public testUserIsNotPatient () {
        const userAccount = this.vueComponent({ userInfo: this.userInfo, infoMaj: this.infoMaj })
        this.assertTrue(this.privateCall(userAccount, "isNotPatient"))
    }

    public testUserIsPatient () {
        const userPatientInfo = { ...this.userInfo }
        userPatientInfo._is_patient = true
        const userAccount = this.vueComponent({ userInfo: userPatientInfo, infoMaj: this.infoMaj })
        this.assertFalse(this.privateCall(userAccount, "isNotPatient"))
    }

    public testDarkTheme () {
        const userAccount = this.vueComponent({ userInfo: this.userInfo, infoMaj: this.infoMaj })
        this.assertEqual(this.privateCall(userAccount, "darkTheme"), this.userInfo._dark_mode)
    }

    public testShowMajInfo () {
        const userAccount = this.vueComponent({ userInfo: this.userInfo, infoMaj: this.infoMaj })
        this.assertTrue(this.privateCall(userAccount, "showMajInfo"))
    }

    public testHideMajInfo () {
        const infoMaj = { ...this.infoMaj }
        infoMaj.title = ""
        infoMaj.release_title = ""
        const userAccount = this.vueComponent({ userInfo: this.userInfo, infoMaj: infoMaj })
        this.assertFalse(this.privateCall(userAccount, "showMajInfo"))
    }

    public async testClickOutside () {
        const userAccount = this.mountComponent({ userInfo: this.userInfo, infoMaj: this.infoMaj })
        this.privateCall(userAccount.vm, "clickOutside")
        await userAccount.vm.$nextTick()
        this.assertTrue(userAccount.emitted("input"))
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        this.assertEqual(userAccount.emitted("input")[0], [false])
    }

    public testLockSession () {
        const userAccount = this.mountComponent({ userInfo: this.userInfo, infoMaj: this.infoMaj })
        const mockedFunc = jest.fn()
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        window.Session = {}
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        window.Session.lock = mockedFunc
        this.privateCall(userAccount.vm, "lockSession")
        expect(mockedFunc).toBeCalled()
    }

    public testSwitchUser () {
        const userAccount = this.mountComponent({ userInfo: this.userInfo, infoMaj: this.infoMaj })
        const mockedFunc = jest.fn()
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        window.UserSwitch = {}
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        window.UserSwitch.popup = mockedFunc
        this.privateCall(userAccount.vm, "switchUser")
        expect(mockedFunc).toBeCalled()
    }

    public testEnabledDarkTheme () {
        const userAccount = this.mountComponent({ userInfo: this.userInfo, infoMaj: this.infoMaj })
        const mockedFunc = jest.fn((x, y, z) => [x, y, z])
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        window.App = {}
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        window.App.savePref = mockedFunc
        this.privateCall(userAccount.vm, "switchTheme", true)
        expect(mockedFunc).toBeCalled()
        expect(mockedFunc.mock.results[0].value[0]).toEqual("mediboard_ext_dark")
        expect(mockedFunc.mock.results[0].value[1]).toEqual("1")
        expect(mockedFunc.mock.results[0].value[2]).toBeInstanceOf(Function)
    }

    public testDisabledDarkTheme () {
        const userAccount = this.mountComponent({ userInfo: this.userInfo, infoMaj: this.infoMaj })
        const mockedFunc = jest.fn((x, y, z) => [x, y, z])
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        window.App = {}
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        window.App.savePref = mockedFunc
        this.privateCall(userAccount.vm, "switchTheme", false)
        expect(mockedFunc).toBeCalled()
        expect(mockedFunc.mock.results[0].value[0]).toEqual("mediboard_ext_dark")
        expect(mockedFunc.mock.results[0].value[1]).toEqual("0")
        expect(mockedFunc.mock.results[0].value[2]).toBeInstanceOf(Function)
    }

    public testChangePasswordForPatient () {
        const userPatientInfo = { ...this.userInfo }
        userPatientInfo._is_patient = true
        const userAccount = this.mountComponent({ userInfo: userPatientInfo, infoMaj: this.infoMaj })
        const mockedFunc = jest.fn()
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        window.patientChangePassword = mockedFunc
        this.privateCall(userAccount.vm, "changePassword")
        expect(mockedFunc).toBeCalled()
    }

    public testDefaultChangePassword () {
        const userAccount = this.mountComponent({ userInfo: this.userInfo, infoMaj: this.infoMaj })
        const mockedFunc = jest.fn()
        // eslint-disable-next-line @typescript-eslint/ban-ts-ignore
        // @ts-ignore
        window.popChgPwd = mockedFunc
        this.privateCall(userAccount.vm, "changePassword")
        expect(mockedFunc).toBeCalled()
    }
}

(new UserAccountTest()).launchTests()
