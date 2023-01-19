/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import Vue from "vue"
import Vuex from "vuex"
import { Notification } from "@/components/Core/OxNotify/OxNotifyModel"
import { Alert } from "@/components/Core/OxAlert/OxAlertModel"
import { Tab, Module, TabBadgeModel } from "@/components/Appbar/Models/AppbarModel"
import ModuleSerializer from "@/components/Appbar/Serializer/ModuleSerializer"
import { appbarPropTransformer } from "@/components/Appbar/Serializer/DataSerializer"
// import VuexPersistence from 'vuex-persist'

Vue.use(Vuex)

export default new Vuex.Store({
    state: {
        /**
         * Main parameters
         */
        rootUrl: "",
        baseUrl: "",
        loadings: ([] as string[]),

        /**
         * Global configurations
         */
        configurations: {},
        configuration_promises: [],
        group_configurations: {},
        group_configuration_promises: [],
        preferences: {},

        /**
         * Field specifications
         */
        specs: [],
        saved_specs: ([] as string[]),

        /**
         * Api cache
         */
        /* eslint-disable  @typescript-eslint/no-explicit-any */
        api_cache: ([] as any[]),

        /**
         * Notifications
         */
        notifications: [] as Notification[],

        /**
         * Alerts
         */
        alert: null as Alert|null,

        /**
         * Appbar
         */
        current_module: {} as Module,
        tab_active: "",
        tab_badges: [] as Array<TabBadgeModel>
    },
    getters: {
        /**
         * Main parameters
         */
        url: (state) => {
            return state.baseUrl
        },
        rooturl: (state) => {
            return state.rootUrl
        },
        loading: (state) => {
            return state.loadings.length > 0
        },

        /**
         * Global configurations
         */
        conf: (state) => (conf) => {
            if (typeof (state.configurations[conf]) !== "undefined") {
                return state.configurations[conf]
            }
            return "undefined"
        },
        gconf: (state) => (gconf) => {
            if (typeof (state.group_configurations[gconf]) !== "undefined") {
                return state.group_configurations[gconf]
            }
            return "undefined"
        },
        pref: (state) => (label) => {
            return typeof (state.preferences[label]) !== "undefined" ? state.preferences[label] : false
        },

        hasConfigurationPromise: (state) => (conf) => {
            return typeof (state.configuration_promises[conf]) !== "undefined"
        },
        configurationPromise: (state) => (conf) => {
            return typeof (state.configuration_promises[conf]) === "undefined" ? false : state.configuration_promises[conf]
        },
        hasGroupConfigurationPromise: (state) => (conf) => {
            return typeof (state.group_configuration_promises[conf]) !== "undefined"
        },
        groupConfigurationPromise: (state) => (conf) => {
            return typeof (state.group_configuration_promises[conf]) === "undefined" ? false : state.group_configuration_promises[conf]
        },

        /**
         * Field specifications
         */
        spec: (state) => (objectType, objectField) => {
            return state.specs[objectType] && state.specs[objectType][objectField] ? state.specs[objectType][objectField] : false
        },
        // hasSpecByType: (state) => (objectType) => { return objectType },
        hasSpecByFieldset: (state) => (objectType, objectFieldset) => {
            if (state.saved_specs.indexOf(objectType + "-" + objectFieldset) === -1) {
                return false
            }
            return true
        },
        hasSpecByFieldsets: (state) => (objectType, objectFieldsets) => {
            for (let i = 0; i < objectFieldsets.length; i++) {
                if (state.saved_specs.indexOf(objectType + "-" + objectFieldsets[i]) === -1) {
                    return false
                }
            }
            return true
        },

        /**
         * API Cache
         */
        getApiCache: (state) => (key) => {
            if (typeof (state.api_cache[key]) === "undefined") {
                return false
            }
            return state.api_cache[key]
        },

        /**
         * Notifications
         */
        getNotifications: (state) => {
            return state.notifications
        },

        /**
         * Alert
         */
        getAlert: (state) => {
            return state.alert
        },

        /**
         * Appbar
         */
        getCurrentModule: (state) => {
            return state.current_module
        },
        getStandardTabs: (state) => {
            const tabs = state.current_module.standard_tabs.slice(0)
            tabs.sort((a, b) => {
                return state.current_module.tabs_order.indexOf(a.tab_name) - state.current_module.tabs_order.indexOf(b.tab_name)
            })
            return tabs
        },
        getPinnedTabs: (state) => {
            return state.current_module.pinned_tabs
        },
        getTabActive: (state) => {
            return state.tab_active
        },
        currentTabIsParam: (state) => {
            return state.current_module.param_tabs.filter((tab) => {
                return tab.tab_name === state.tab_active
            }).length > 0
        },
        currentTabIsConfig: (state) => {
            return state.current_module.configure_tab.filter((tab) => {
                return tab.tab_name === state.tab_active
            }).length > 0
        },
        getTabBadge: (state) => (moduleName: string, tabName: string) => {
            return state.tab_badges.find((_tabBadge: TabBadgeModel) => {
                return _tabBadge.module_name === moduleName && _tabBadge.tab_name === tabName
            }) as TabBadgeModel
        }
    },
    mutations: {
        /**
         * Main parameters
         */
        setBaseUrl: (state, baseUrl: string) => {
            state.baseUrl = baseUrl
        },
        setRootUrl: (state, rootUrl: string) => {
            state.rootUrl = rootUrl
        },
        addLoading: (state) => {
            state.loadings.push("_")
        },
        removeLoading: (state) => {
            state.loadings.shift()
        },
        resetLoading: (state) => {
            state.loadings = []
        },
        /**
         * Global configurations
         */
        /* eslint-disable  @typescript-eslint/no-explicit-any */
        setConfiguration: (state, conf: { name: string; value: any }) => {
            state.configurations[conf.name] = conf.value
        },
        /* eslint-disable  @typescript-eslint/no-explicit-any */
        setGroupConfiguration: (state, conf: { name: string; value: any }) => {
            state.group_configurations[conf.name] = conf.value
        },
        setPreference: (state, pref: { name: string; value: string }) => {
            state.preferences[pref.name] = pref.value
        },
        setPreferences: (state, prefs: {name: string; value: string}[]) => {
            state.preferences = prefs
        },
        setConfigurationPromise: (state, params: { conf: string; promise: Promise<string> }) => {
            if (typeof (state.configuration_promises[params.conf]) !== "undefined") {
                return
            }
            state.configuration_promises[params.conf] = params.promise
        },
        removeConfigurationPromise: (state, conf: string) => {
            if (typeof (state.configuration_promises[conf]) === "undefined") {
                return
            }
            delete (state.configuration_promises[conf])
        },
        setGroupConfigurationPromise: (state, params: { conf: string; promise: Promise<string> }) => {
            if (typeof (state.group_configuration_promises[params.conf]) !== "undefined") {
                return
            }
            state.group_configuration_promises[params.conf] = params.promise
        },
        removeGroupConfigurationPromise: (state, conf: string) => {
            if (typeof (state.group_configuration_promises[conf]) === "undefined") {
                return
            }
            delete (state.group_configuration_promises[conf])
        },

        /**
         * Field specifications
         */
        setSpec: (state, object: { type: string; specs: any; fieldsets: string[] }) => {
            if (!state.specs[object.type]) {
                state.specs[object.type] = object.specs
            }
            else {
                Object.assign(state.specs[object.type], object.specs)
            }
            object.fieldsets.forEach(
                (_fieldset) => {
                    state.saved_specs.push(object.type + "-" + _fieldset)
                }
            )
        },

        /**
         * Api Cache
         */
        setApiCache: (state, cache: { key: string; value: any }) => {
            state.api_cache[cache.key] = cache.value
        },

        /**
         * Notifications
         */
        addNotification: (state, notification: Notification) => {
            state.notifications.push(notification)
        },
        removeNotification: (state, key: number) => {
            const notificationIndex = state.notifications.findIndex(notification => notification.key === key)
            if (notificationIndex !== -1) {
                state.notifications.splice(notificationIndex, 1)
            }
        },
        callbackDoneNotification: (state, key: number) => {
            const notificationIndex = state.notifications.findIndex(notification => notification.key === key)
            if (notificationIndex !== -1) {
                state.notifications[notificationIndex].callbackDone = true
            }
        },
        removeAllNotifications: (state) => {
            state.notifications = []
        },
        /**
         * Alert
         */
        setAlert: (state, alert: Alert) => {
            state.alert = alert
        },
        unsetAlert: (state) => {
            state.alert = null
        },

        /**
         * Appbar
         */
        setCurrentModule: (state, module: Module) => {
            state.current_module = module
        },
        setTabActive: (state, tabName: string) => {
            state.tab_active = tabName
        },
        pinTab: (state, tab: Tab) => {
            let newStandardTabs = state.current_module.standard_tabs
            newStandardTabs = newStandardTabs.filter((standardTab) => {
                return standardTab.tab_name !== tab.tab_name
            })
            Vue.set(state.current_module, "standard_tabs", newStandardTabs)

            const newPinnedTabs = state.current_module.pinned_tabs
            newPinnedTabs.push(tab)
            Vue.set(state.current_module, "pinned_tabs", newPinnedTabs)
        },
        unpinTab: (state, tab: Tab) => {
            const newStandardTabs = state.current_module.standard_tabs.slice(0)
            newStandardTabs.push(tab)
            Vue.set(state.current_module, "standard_tabs", newStandardTabs)
            let newPinnedTabs = state.current_module.pinned_tabs.slice(0)
            newPinnedTabs = newPinnedTabs.filter((pinnedTab) => {
                return pinnedTab.tab_name !== tab.tab_name
            })
            Vue.set(state.current_module, "pinned_tabs", newPinnedTabs)
        },
        setPinnedTabs: (state, tabs: Array<Tab>) => {
            Vue.set(state.current_module, "pinned_tabs", tabs)
        },
        addTabBadge: (state, tabBadge: TabBadgeModel) => {
            state.tab_badges.push(tabBadge)
        },
        removeTabBadge: (state, tabBadge: TabBadgeModel) => {
            state.tab_badges = state.tab_badges.filter((_tabBadge) => {
                return _tabBadge.module_name !== tabBadge.module_name || _tabBadge.tab_name !== tabBadge.tab_name
            })
        }
    },
    actions: {
        setCurrentModule ({ commit }, module) {
            commit("setCurrentModule", ModuleSerializer.serialize(appbarPropTransformer(module)))
        },
        async pinTab ({ commit }, { tab, provider }) {
            commit("pinTab", tab)
            provider.putPinnedTabs()
        },
        async unpinTab ({ commit }, { tab, provider }) {
            commit("unpinTab", tab)
            provider.putPinnedTabs()
        },
        async setPinnedTabs ({ commit }, { tabs, provider }) {
            commit("setPinnedTabs", tabs)
            provider.putPinnedTabs()
        },
        updateTabBadge ({ commit, getters }, tabBadge) {
            if (getters.getTabBadge(tabBadge.module_name, tabBadge.tab_name) !== undefined) {
                commit("removeTabBadge", tabBadge)
            }
            commit("addTabBadge", tabBadge)
        }
    }
    // ,
    // plugins: [ vuexSession.plugin ]
})
