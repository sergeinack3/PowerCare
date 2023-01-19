/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import INVue from "../INVue/INVue"
import INValueBool from "./INValueBool/INValueBool.vue"
import INValueString from "./INValueString/INValueString.vue"
import INValueDatetime from "./INValueDatetime/INValueDatetime.vue"

/**
 * Wrapper des champs de saisie de texte de l'Install
 */
@Component({ components: { INValueBool, INValueString, INValueDatetime } })
export default class INValue extends INVue {
  @Prop({ default: "" })
  private field!: any

  @Prop({ default: 0 })
  private length!: number

  private get fieldView (): any {
      return this.field
  }

  private get fieldType (): string {
      const type = typeof (this.field)
      if (type === "string") {
          const potentialDate = this.field.substr(0, 19)
          if (potentialDate.match(/^\d\d\d\d-((0[1-9])|1[1-2])-(([0-2][0-9])|3[0-1]) (([0-1][0-9])|(2[0-3])):([0-5][0-9]):([0-5][0-9])/)) {
              return "datetime"
          }
      }
      return typeof (this.field)
  }

  private get isEmpty (): boolean {
      return this.field === null || this.field === ""
  }

  private get className (): object {
      return {
          ".empty": this.isEmpty
      }
  }
}
