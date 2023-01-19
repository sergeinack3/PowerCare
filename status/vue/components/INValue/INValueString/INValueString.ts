/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import INVue from "../../INVue/INVue"
import INButton from "../../INButton/INButton.vue"

/**
 * Wrapper des champs de saisie de texte de status
 */
@Component({ components: { INButton } })
export default class INValueString extends INVue {
  @Prop({ default: "" })
  private field!: any

  @Prop({ default: 0 })
  private length!: number

  private longFieldDisplayed = false

  private get fieldView (): string {
      return this.needShowMore() ? this.field.substr(0, this.length) : this.field
  }

  private get className (): object {
      return {
          "INValueString-breakableField": this.field.length > 35 && this.field.length < 70,
          "INValueString-allBreakableField": this.field.length > 70
      }
  }

  private needShowMore (): boolean {
      return this.length > 0 && this.field.length > this.length
  }

  private showLongField (): void {
      this.longFieldDisplayed = true
  }

  private hideLongField (): void {
      this.longFieldDisplayed = false
  }
}
