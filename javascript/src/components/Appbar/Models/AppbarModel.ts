/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/* eslint-disable camelcase */

export interface Tab {
    tab_name: string
    mod_name?: string
    _links: {
        tab_url: string
    }
    is_standard?: boolean
    is_param?: boolean
    is_config?: boolean
    pinned_order?: number | null
}

export interface Module {
    mod_name: string
    mod_category: string
    pinned_tabs: Tab[]
    standard_tabs: Tab[]
    configure_tab: Tab[]
    param_tabs: Tab[]
    tabs?: object
    _links: {
        tabs: string
        module_url: string
    }
    isActive?: boolean
    tabs_order: Array<string>
}

export interface Group {
    _id: string
    text: string
    is_main: boolean
    is_secondary: boolean
    _links: {
        groups: string
    }
}

export interface UserInfo {
    _can_change_password: string
    _color: string
    _dark_mode: boolean
    _font_color: string
    _initial: string
    _is_admin: boolean
    _is_patient: boolean
    _is_sso: boolean
    _links: {
        default: string
        edit_infos: string
        logout: string
    }
    _user_first_name: string
    _user_last_name: string
    _user_username: string
}

export interface Function {
    _id: string
    group_id: number
    is_main: boolean
    text: string
}

export interface InfoMaj {
    title: string
    release_title: string
}

export interface Placeholder {
    _id: string
    action: string
    action_args: Array<string>
    counter: string
    icon: string | number
    init_action: string
    label: string
}

export interface MenuLink {
    title: string
    href: string | null
    action: string | null
}

export interface MenuSection {
    title: string
    links: Array<MenuLink>
}

export interface TabBadgeModel {
    module_name: string
    tab_name: string,
    counter: number,
    color: string
}

export interface AppbarProp {
    datas: {
        [key: string]: any
    },
    links?: {
        [key: string]: string
    }
}
