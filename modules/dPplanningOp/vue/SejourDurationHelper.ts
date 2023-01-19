/**
 * @package Mediboard\Planning
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { OxDate } from "oxify"

/**
 * SejourDurationHelper
 * Classe utilitaire de gestion de la durée d'un séjour
 */
export default class SejourDurationHelper {
    private entree = ""
    private hours = 0
    private nights = 0
    private sortie = ""
    private type = ""

    public roundSortieTime = true

    /**
     * Récupération du nombre d'heures
     *
     * @return {number}
     */
    public getHours (): number {
        return this.hours
    }

    /**
     * Récupération du nombre de nuits
     *
     * @return {number}
     */
    public getNights (): number {
        return this.nights
    }

    /**
     * Récupération de la date de sortie
     *
     * @return {string}
     */
    public getSortie (): string {
        if (!this.sortie) {
            return ""
        }
        return OxDate.getYMDHms(new Date(this.sortie))
    }

    public getEntree (): string {
        if (!this.entree) {
            return ""
        }
        return OxDate.getYMDHms(new Date(this.entree))
    }

    /**
     * Mise à jour de l'entrée
     * @param {string} type - type du sejour
     */
    public setType (type: string): void {
        this.type = type
    }

    /**
     * Mise à jour de l'entrée
     * @param {string} entree - Date d'entrée
     *
     * @return {SejourDurationHelper}
     */
    public setEntree (entree: string): SejourDurationHelper {
        if (this.entree && entree && this.sortie) {
            const dateA = new Date(this.entree)
            const dateB = new Date(OxDate.getYMD(new Date(this.entree)) + " " + OxDate.getHms(new Date(entree)))
            const diff = OxDate.diff(dateA, dateB)
            if (new Date(this.sortie).getHours() + diff.hou > 23 && dateA < dateB && this.type === "ambu") {
                this.entree = entree
                this.updateHours(new Date(entree).getHours())
                return this
            }
        }
        this.entree = entree
        return this
    }

    /**
     * Mise à jour du nombre d'heures
     * @param {number} hours - Nombre d'heures
     *
     * @return {SejourDurationHelper}
     */
    public updateHours (hours: string | number): SejourDurationHelper {
        const entree = new Date(this.entree)
        if (hours > 23) {
            hours = 23
        }
        if (entree.getHours() + parseInt(hours.toString()) > 23 && this.type === "ambu") {
            hours = 23 - entree.getHours()
        }
        this.hours = parseInt(hours.toString())
        this.updateSortie()
        return this
    }

    /**
     * Mise à jour du nombre de nuits
     * @param {number} nights - Nombre de nuits
     *
     * @return {SejourDurationHelper}
     */
    public updateNights (nights: string | number): SejourDurationHelper {
        this.nights = parseInt(nights.toString())
        this.updateSortie()
        return this
    }

    /**
     * Mise à jour de la date de sortie
     * @param {string} sortie - Date de sortie
     *
     * @return {SejourDurationHelper}
     */
    public updateSortie (): SejourDurationHelper {
        if (!this.entree) {
            return this
        }
        const entree = new Date(this.entree)
        entree.setDate(entree.getDate() + this.nights)
        entree.setHours(entree.getHours() + this.hours)
        if (this.roundSortieTime) {
            entree.setMinutes(0)
        }
        this.sortie = OxDate.getYMDHms(entree)
        return this
    }

    public setSortie (sortie: string): SejourDurationHelper {
        if (!this.entree) {
            return this
        }

        this.sortie = sortie

        if (!sortie) {
            this.hours = 0
            this.nights = 0
            return this
        }

        const diff = OxDate.diff(new Date(this.entree), new Date(this.sortie))
        if (diff.min > 0) {
            diff.hou++
        }
        this.nights = diff.day
        this.hours = diff.hou

        return this
    }
}
