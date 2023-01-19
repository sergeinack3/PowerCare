/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import MenuIcon from "./MenuIcon/MenuIcon.vue"
import Deconnexion from "./Deconnexion/Deconnexion.vue"
import { Component, Prop } from "vue-property-decorator"
import INVue from "../../INVue/INVue"
import DependancesProvider from "../../INProvider/DependancesProvider"

/**
 * Gestion du menu supérieur du Install
 */
@Component({ components: { MenuIcon, Deconnexion } })
export default class Menu extends INVue {
  @Prop()
  private selectedChapter!: string

  @Prop({ default: false })
  private connected!: boolean

  @Prop({ default: false })
  private compact!: boolean

  private checkDependanceLoaded = false
  private checkDependances: any = {}

  private chapterClick (chapter: string): void {
      if (chapter === "Installation") {
          this.loadCheckDependances()
      }
      this.$emit("chapterclick", chapter)
  }

  private disconnect (): void {
      this.$emit("disconnect")
  }

  private get labelClassName (): object {
      return {
          "Menu-titleLabelCompact": this.compact
      }
  }

  private menuClick (): void {
      document.location.href = "../"
  }

  public async loadFlags (): Promise<void> {
      await this.loadCheckDependances()
  }

  private async loadCheckDependances (): Promise<void> {
      this.checkDependanceLoaded = false
      this.checkDependances = await new DependancesProvider().getData()
      this.checkDependanceLoaded = true
  }

  private get checkDependancesInfo (): {icon: string, state: string|null} {
      return {
          icon: (!this.checkDependanceLoaded
              ? "hourglass"
              : (this.checkDependances.packages && this.checkDependances.libraries ? "check" : "exclamation")),
          state: (!this.checkDependanceLoaded
              ? null
              : (this.checkDependances.packages && this.checkDependances.libraries ? "ok" : "nok"))
      }
  }
}
