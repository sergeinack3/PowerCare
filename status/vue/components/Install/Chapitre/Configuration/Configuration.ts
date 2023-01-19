/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component } from "vue-property-decorator"
import INLineElement from "../../../INLineElement/INLineElement.vue"
import ConfigurationsProvider from "../../../INProvider/ConfigurationsProvider"
import INField from "../../../INField/INField.vue"
import Chapitre from "../Chapitre"
import INLoading from "../../../INLoading/INLoading.vue"

/* eslint-disable vue/multi-word-component-names */

/**
 * Gestion de la page des configurations de status
 */
@Component({ components: { INLineElement, INField, INLoading } })
export default class Configuration extends Chapitre {
  private configs: object[] = []

  private loaded = false

  public async load (): Promise<void> {
      this.loaded = false
      this.configs = this.extractData(this.parseConfigsToArray(await new ConfigurationsProvider().getData()))
      this.loaded = true
  }

  private filterConfiguration (search): void {
      this.applyFilter(search, this.configs, ["label", "id", "value"])
  }

  private parseConfigsToArray (configs): object[] {
      const array: object[] = []
      Object.keys(configs).forEach(
          (config) => {
              if (typeof (configs[config]) === "object") {
                  Object.keys(configs[config]).forEach(
                      (subConfig) => {
                          array.push(
                              {
                                  label: this.tr("Configs-" + config + "-" + subConfig),
                                  id: config + "-" + subConfig,
                                  value: configs[config][subConfig]
                              }
                          )
                      }
                  )
              }
              else {
                  array.push(
                      {
                          label: this.tr("Configs-" + config),
                          id: config,
                          value: configs[config]
                      }
                  )
              }
          }
      )
      return array
  }
}
