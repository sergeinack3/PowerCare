import Vue from "vue"

declare module "vue/types/vue" {
    interface Vue {
        $tr: (key: string, values: string|null = null, plural = false) => string
        $moduleExists: (moduleName: string, modulesListJson: ModulesListJson = null) => boolean
    }
}
