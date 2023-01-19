/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import INIcon from "../INIcon/INIcon.vue"
import { Component, Prop } from "vue-property-decorator"
import INVue from "../INVue/INVue"

/**
 * Wrapper des champs de saisie de texte de l'Install
 */
@Component({ components: { INIcon } })
export default class INField extends INVue {
  @Prop({ default: "" })
  private placeholder!: string

  @Prop({ default: false })
  private scalable!: boolean

  @Prop({ default: "" })
  private defaultValue!: string

  @Prop({ default: false })
  private canReset!: boolean

  @Prop({ default: false })
  private isPassword!: boolean

  private value: string = this.defaultValue || ""

  /**
   * Classes de l'input
   */
  private get inputClassName (): object {
      return {
          scalable: this.scalable,
          dirty: this.value !== "",
          canReset: this.canReset
      }
  }

  /**
   * Type de l'input
   */
  private get type (): string {
      return this.isPassword ? "password" : "text"
  }

  /**
   * Remise à zéro du champs
   */
  private resetField (): void {
      this.updateInput("")
  }

  private pressEnter (): void {
      this.$emit("enter", this.value)
  }

  private updateInput (value :string) :void {
      this.value = value
      this.$emit("input", value)
  }

  private clickLabel (): void {
      (<HTMLElement> this.$refs.input).focus()
  }
}
