/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component } from "vue-property-decorator"
import Chapitre from "../Chapitre"
import INTabs from "../../../INTabs/INTabs.vue"
import INLoading from "../../../INLoading/INLoading.vue"
import LogsProvider from "../../../INProvider/LogsProvider"
import ErrorsProvider from "../../../INProvider/ErrorsProvider"
import ErrorsBufferProvider from "../../../INProvider/ErrorsBufferProvider"
import INTable from "../../../INTable/INTable.vue"
import INPagination from "../../../INProvider/INPagination"
import INValue from "../../../INValue/INValue.vue"

/**
 * Gestion de la page de Monitoring de status
 */
@Component({ components: { INTabs, INLoading, INTable, INValue } })
export default class ErreurLog extends Chapitre {
  private logs: object[] = []
  private errors: object[] = []
  private errorsBufferFilesCount = 0
  private errorsBufferLastUpdate = ""
  private errorsBufferPath = ""

  private errorPagination: INPagination = new INPagination()
  private logPagination: INPagination = new INPagination()

  private logsColumns: string[] = ["date", "level", "message"]
  private errorsColumns: Array<object|string> = ["datetime", "errorType", { field: "message", length: 100 }, "file"]

  private moreLogLoaded = true

  private currentTab = "Logs"
  private tabs: object[] = [
      {
          label: this.tr("Logs"),
          id: "Logs"
      },
      {
          label: this.tr("Errors"),
          id: "Errors"
      }
  ]

  private selectTab (tab: string): void {
      this.currentTab = tab
  }

  protected erreurLogScroll (event: {target: any}): void {
      this.scroll(event)
      if (this.currentTab === "Errors" || !this.moreLogLoaded) {
          return
      }
      const container = event.target
      if (!container || (container.scrollHeight > (container.offsetHeight + container.scrollTop))) {
          return
      }
      this.loadMore()
  }

  public async load (): Promise<void> {
      this.errorPagination = new INPagination(new ErrorsProvider())
      this.logPagination = new INPagination(new LogsProvider())
      this.logs = await this.logPagination.getData()
      this.errors = await this.errorPagination.getData()
      const errorsBufferData = await new ErrorsBufferProvider()
          .getData()
      this.errorsBufferFilesCount = errorsBufferData.filesCount
      this.errorsBufferLastUpdate = errorsBufferData.lastUpdate
      this.errorsBufferPath = errorsBufferData.path
  }

  private async loadMore (): Promise<void> {
      this.moreLogLoaded = false
      this.logs = this.logs.concat(await this.logPagination.next())
      this.moreLogLoaded = true
  }

  private async previousErrorPage (): Promise<void> {
      this.errors = await this.errorPagination.previous()
  }

  private async lastErrorPage (): Promise<void> {
      this.errors = await this.errorPagination.last()
  }

  private async nextErrorPage (): Promise<void> {
      this.errors = await this.errorPagination.next()
  }

  private async firstErrorPage (): Promise<void> {
      this.errors = await this.errorPagination.first()
  }

  private async sortError (field: string): Promise<void> {
      this.errorPagination.currentSort = field
      this.errors = await this.errorPagination.getData()
  }
}
