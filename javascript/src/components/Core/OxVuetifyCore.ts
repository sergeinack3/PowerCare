import Vue from "vue"
import Vuetify from "vuetify/lib"
import fr from "vuetify/src/locale/fr"
import { OxThemeCore, OxDate, colors } from "oxify"

Vue.use(Vuetify)
Vue.mixin({
    data: function () {
        return {
            get OxThemeCore () {
                return OxThemeCore
            },
            get OxDate () {
                return OxDate
            }
        }
    }
})

export default new Vuetify({
    lang: {
        locales: { fr },
        current: "fr"
    },
    icons: {
        iconfont: "mdiSvg"
    },
    theme: {
        themes: {
            light: {
                primary: colors.lightTheme.primary.default,
                secondary: colors.lightTheme.secondary.default,
                accent: colors.lightTheme.secondary["700"],
                error: colors.lightTheme.error.textDefault,
                info: colors.lightTheme.info.textDefault,
                success: colors.lightTheme.success.textDefault,
                warning: colors.lightTheme.warning.textDefault
            },
            dark: {
                primary: colors.darkTheme.primary.default,
                secondary: colors.darkTheme.secondary.default,
                accent: colors.darkTheme.secondary["700"],
                error: colors.darkTheme.error.textDefault,
                info: colors.darkTheme.info.textDefault,
                success: colors.darkTheme.success.textDefault,
                warning: colors.darkTheme.warning.textDefault
            }
        }
    }
})
