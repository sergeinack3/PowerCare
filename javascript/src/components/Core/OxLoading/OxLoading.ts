/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Prop } from "vue-property-decorator"
import OxVue from "@/components/Core/OxVue"
import OxVueApi from "@/components/Core/OxVueApi"
import OxStoreCore from "@/components/Core/OxStores/OxStoreCore"

/**
 * OxLoading
 *
 * Composant d'affichage de chargement
 */
@Component
export default class OxLoading extends OxVue {
    @Prop({ default: false })
    private forceLoad!: boolean

    /**
     * Récupération du status de chargement
     *
     * @return boolean
     */
    private get loading (): boolean {
        return this.forceLoad || OxVueApi.loading()
    }

    /**
     * Récupération des classes appliquées au container OxLoading
     *
     * @return object
     */
    private get loadingClassNames () {
        return {
            displayed: this.loading
        }
    }

    /**
     * Retrait du statut de chargement de tous les éléments de chargement en store
     */
    public static unloadAll (): void {
        OxStoreCore.commit("resetLoading")
    }
}
