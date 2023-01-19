/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxTest } from "oxify"
import ModuleSerializer from "@/components/Appbar/Serializer/ModuleSerializer"
import OxSerializerCore from "@/components/Core/OxSerializerCore"
import { ApiData } from "@/components/Models/ApiResponseModel"
import { Module, Tab } from "@/components/Appbar/Models/AppbarModel"
import { appbarPropTransformer } from "@/components/Appbar/Serializer/DataSerializer"

/**
 * Test pour la classe ModuleSerializer
 */
export default class ModuleSerializerTest extends OxTest {
    protected component = ModuleSerializer

    private moduleProp = {
        datas: {
            mod_name: "dPpatients",
            mod_category: "dossier_patient",
            tabs: [
                {
                    mod_name: "moduleTest",
                    tab_name: "tab1",
                    is_standard: true,
                    is_param: false,
                    is_config: false,
                    pinned_order: 1,
                    _links: {
                        tab_url: "tab-1-url"
                    }
                },
                {
                    mod_name: "moduleTest",
                    tab_name: "tab2",
                    is_standard: true,
                    is_param: false,
                    is_config: false,
                    pinned_order: null,
                    _links: {
                        tab_url: "tab-2-url"
                    }
                },
                {
                    mod_name: "moduleTest",
                    tab_name: "tab3",
                    is_standard: true,
                    is_param: false,
                    is_config: false,
                    pinned_order: null,
                    _links: {
                        tab_url: "tab-3-url"
                    }
                },
                {
                    mod_name: "moduleTest",
                    tab_name: "tab4",
                    is_standard: true,
                    is_param: false,
                    is_config: false,
                    pinned_order: 0,
                    _links: {
                        tab_url: "tab-4-url"
                    }
                },
                {
                    mod_name: "moduleTest",
                    tab_name: "tab5",
                    is_standard: false,
                    is_param: true,
                    is_config: false,
                    pinned_order: null,
                    _links: {
                        tab_url: "tab-5-url"
                    }
                },
                {
                    mod_name: "moduleTest",
                    tab_name: "tab6",
                    is_standard: false,
                    is_param: true,
                    is_config: false,
                    pinned_order: null,
                    _links: {
                        tab_url: "tab-6-url"
                    }
                },
                {
                    mod_name: "moduleTest",
                    tab_name: "tab7",
                    is_standard: false,
                    is_param: false,
                    is_config: true,
                    pinned_order: null,
                    _links: {
                        tab_url: "tab-7-url"
                    }
                }
            ]
        },
        links: {
            module_url: "?m=dPpatients",
            tabs: "/mediboard/api/modules/dPpatients/tabs"
        }
    }

    private moduleApiData = {
        data: {
            type: "module",
            id: "42",
            attributes: {
                mod_name: "moduleTest",
                mod_type: "user",
                mod_version: "1.00",
                mod_active: true,
                mod_ui_active: true,
                mod_category: "category_test",
                mod_package: "package_test",
                mod_custom_color: null,
                tabs: [
                    {
                        type: "tab",
                        id: "tab1",
                        attributes: {
                            mod_name: "moduleTest",
                            tab_name: "tab1",
                            is_standard: true,
                            is_param: false,
                            is_config: false,
                            pinned_order: 1
                        },
                        links: {
                            tab_url: "tab-1-url"
                        }
                    },
                    {
                        datas: {
                            mod_name: "moduleTest",
                            tab_name: "tab2",
                            is_standard: true,
                            is_param: false,
                            is_config: false,
                            pinned_order: null
                        },
                        links: {
                            tab_url: "tab-2-url"
                        }
                    },
                    {
                        datas: {
                            mod_name: "moduleTest",
                            tab_name: "tab3",
                            is_standard: true,
                            is_param: false,
                            is_config: false,
                            pinned_order: null
                        },
                        links: {
                            tab_url: "tab-3-url"
                        }
                    },
                    {
                        datas: {
                            mod_name: "moduleTest",
                            tab_name: "tab4",
                            is_standard: true,
                            is_param: false,
                            is_config: false,
                            pinned_order: 0
                        },
                        links: {
                            tab_url: "tab-4-url"
                        }
                    },
                    {
                        datas: {
                            mod_name: "moduleTest",
                            tab_name: "tab5",
                            is_standard: false,
                            is_param: true,
                            is_config: false,
                            pinned_order: null
                        },
                        links: {
                            tab_url: "tab-5-url"
                        }
                    },
                    {
                        datas: {
                            mod_name: "moduleTest",
                            tab_name: "tab6",
                            is_standard: false,
                            is_param: true,
                            is_config: false,
                            pinned_order: null
                        },
                        links: {
                            tab_url: "tab-6-url"
                        }
                    },
                    {
                        datas: {
                            mod_name: "moduleTest",
                            tab_name: "tab7",
                            is_standard: false,
                            is_param: false,
                            is_config: true,
                            pinned_order: null
                        },
                        links: {
                            tab_url: "tab-7-url"
                        }
                    }
                ]
            },
            links: {
                self: "self",
                schema: "schema",
                history: "history",
                module_url: "url",
                tabs: "tabs"
            }
        }
    }

    private tabsToAdd: Array<Tab> = [
        {
            mod_name: "moduleTest",
            tab_name: "tabAdd1",
            is_standard: true,
            is_param: false,
            is_config: false,
            pinned_order: null,
            _links: { tab_url: "tab-1-url" }
        },
        {
            mod_name: "moduleTest",
            tab_name: "tabAdd2",
            is_standard: true,
            is_param: false,
            is_config: false,
            pinned_order: 0,
            _links: { tab_url: "tab-2-url" }
        },
        {
            mod_name: "moduleTest",
            tab_name: "tabAdd3",
            is_standard: true,
            is_param: false,
            is_config: false,
            pinned_order: null,
            _links: { tab_url: "tab-3-url" }
        },
        {
            mod_name: "moduleTest",
            tab_name: "tabAdd4",
            is_standard: false,
            is_param: false,
            is_config: true,
            pinned_order: null,
            _links: { tab_url: "tab-4-url" }
        }
    ]

    public async testSerializeModuleFromApi () {
        const module = ModuleSerializer.serialize(appbarPropTransformer(this.moduleProp))
        // Pinned tabs tests
        this.assertInstanceOf(module.pinned_tabs, Array)
        this.assertEqual(module.pinned_tabs, [
            { tab_name: "tab4", mod_name: "moduleTest", _links: { tab_url: "tab-4-url" } },
            { tab_name: "tab1", mod_name: "moduleTest", _links: { tab_url: "tab-1-url" } }
        ])
        // Standard tabs tests
        this.assertInstanceOf(module.standard_tabs, Array)
        this.assertEqual(module.standard_tabs, [
            { tab_name: "tab2", mod_name: "moduleTest", _links: { tab_url: "tab-2-url" } },
            { tab_name: "tab3", mod_name: "moduleTest", _links: { tab_url: "tab-3-url" } }
        ])
        // Param tabs tests
        this.assertInstanceOf(module.param_tabs, Array)
        this.assertEqual(module.param_tabs, [
            { tab_name: "tab5", mod_name: "moduleTest", _links: { tab_url: "tab-5-url" } },
            { tab_name: "tab6", mod_name: "moduleTest", _links: { tab_url: "tab-6-url" } }
        ])
        // Config tabs test
        this.assertInstanceOf(module.configure_tab, Array)
        this.assertEqual(module.configure_tab, [
            { tab_name: "tab7", mod_name: "moduleTest", _links: { tab_url: "tab-7-url" } }
        ])
        // Order tab test
        this.assertInstanceOf(module.tabs_order, Array)
        this.assertEqual(module.tabs_order, ["tab1", "tab2", "tab3", "tab4"])
    }

    public async testAddTabsToModule () {
        const emptyModuleApiData = { ...this.moduleProp }
        emptyModuleApiData.datas.tabs.length = 0

        let module = ModuleSerializer.serialize(appbarPropTransformer(emptyModuleApiData))
        // Empty tabs test
        this.assertEqual(module.standard_tabs, [])
        this.assertEqual(module.pinned_tabs, [])
        this.assertEqual(module.param_tabs, [])
        this.assertEqual(module.configure_tab, [])
        this.assertEqual(module.tabs_order, [])
        module = ModuleSerializer.addTabsToModule(module, this.tabsToAdd)
        // Pinned tabs tests
        this.assertInstanceOf(module.pinned_tabs, Array)
        this.assertEqual(module.pinned_tabs, [
            { tab_name: "tabAdd2", mod_name: "moduleTest", _links: { tab_url: "tab-2-url" } }
        ])
        // Standard tabs tests
        this.assertInstanceOf(module.standard_tabs, Array)
        this.assertEqual(module.standard_tabs, [
            { tab_name: "tabAdd1", mod_name: "moduleTest", _links: { tab_url: "tab-1-url" } },
            { tab_name: "tabAdd3", mod_name: "moduleTest", _links: { tab_url: "tab-3-url" } }
        ])
        // Param tabs tests
        this.assertInstanceOf(module.param_tabs, Array)
        this.assertEqual(module.param_tabs, [])
        // Config tabs test
        this.assertInstanceOf(module.configure_tab, Array)
        this.assertEqual(module.configure_tab, [
            { tab_name: "tabAdd4", mod_name: "moduleTest", _links: { tab_url: "tab-4-url" } }
        ])
        // Order tab test
        this.assertInstanceOf(module.tabs_order, Array)
        this.assertEqual(module.tabs_order, ["tabAdd1", "tabAdd2", "tabAdd3"])
    }
}

(new ModuleSerializerTest()).launchTests()
