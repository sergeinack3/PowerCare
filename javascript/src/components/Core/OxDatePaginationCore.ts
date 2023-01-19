/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxDate } from "oxify"

/**
 * OxDatePaginationCore
 *
 * Classe intermédiaire. Gestion de la pagination en tant que filtre pour les dates.
 */
export default class OxDatePaginationCore {
    // Date courante
    public currentDate: Date = new Date()
    // Nom du paramètre à retourner (ex: month, day, date, ...)
    private dateParam: string
    // Mode de traitement de la date : Par jour, mois, semaine, ...
    public mode: "daily" | "weekly" | "monthly" = "daily"

    /**
     * @inheritDoc
     */
    constructor (date: Date, dateParam: string, mode: "daily" | "weekly" | "monthly") {
        if (mode === "monthly") {
            date.setDate(1)
        }
        else if (mode === "weekly") {
            // unavailable
        }
        else if (mode === "daily") {
            // unavailable
        }
        this.currentDate = date
        this.dateParam = dateParam
        this.mode = mode
        return this
    }

    /**
     * Génération de paramètres pour un lien self (type rafraichissement)
     *
     * @return object
     */
    public genSelfParam () {
        return this.genParameters(0)
    }

    /**
     * Génération de paramètres pour un lien next (type prochaine page)
     *
     * @return object
     */
    public genNextParam () {
        return this.genParameters(1)
    }

    /**
     * Génération de paramètres pour un lien prev (type précèdente page)
     *
     * @return object
     */
    public genPreviousParam () {
        return this.genParameters(-1)
    }

    /**
     * Génération de paramètres
     *
     * @param {1|-1|0} way - Sens de défilement
     *                    1  : Page suivante (next)
     *                    0  : Page actuelle (self)
     *                    -1 : Page précèdente (prev)
     *
     * @return object
     */
    public genParameters (way: 1 | -1 | 0) {
        if (this.mode === "monthly") {
            this.currentDate.setMonth(this.currentDate.getMonth() + way)
        }
        else if (this.mode === "weekly") {
            // unavaiable
        }
        else if (this.mode === "daily") {
            this.currentDate.setDate(this.currentDate.getDate() + way)
        }
        return {
            [this.dateParam]: OxDate.getYMD(this.currentDate)
        }
    }
}
