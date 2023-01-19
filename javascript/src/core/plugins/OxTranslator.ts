import { tr } from "@/core/utils/OxTranslator"

export default {
    install (Vue) {
        Vue.prototype.$tr = tr
    }
}
