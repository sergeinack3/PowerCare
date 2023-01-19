import { Component, Vue } from "vue-property-decorator"
import { lang } from "../INLocales/INLocales"

@Component
export default class INVue extends Vue {
    protected tr (localKey: string, ...args: any[]): string {
        let traduction = lang.t(localKey).toString()
        if (args.length === 0) {
            return traduction
        }
        else {
            args.forEach(
                (value, index) => {
                    traduction = traduction.replace(new RegExp("%" + (index + 1), "g"), value)
                }
            )
        }
        return traduction
    }

    protected datetime (datetime: string): string {
        return this.tr(
            "datetime",
            datetime.substr(0, 4),
            datetime.substr(5, 2),
            datetime.substr(8, 2),
            datetime.substr(11, 2),
            datetime.substr(14, 2),
            datetime.substr(17, 2))
    }

    public static dateToString (date: Date): string {
        const d = date.getDate()
        const m = date.getMonth() + 1
        const y = date.getFullYear()
        const h = date.getHours()
        const i = date.getMinutes()
        const s = date.getSeconds()
        return "" + y + "-" + (m <= 9 ? "0" + m : m) + "-" + (d <= 9 ? "0" + d : d) +
      " " + (h <= 9 ? "0" + h : h) + ":" + (i <= 9 ? "0" + i : i) + ":" + (s <= 9 ? "0" + s : s)
    }
}
