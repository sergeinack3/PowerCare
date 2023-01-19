/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import { Component, Vue } from "vue-property-decorator"

/**
 * Substitue d'OxVue pour les tests
 *
 */
@Component
export default class OxVueForTestsCore extends Vue {
    // Etat du composant.
    public active = true

    public loaded = true

    /**
     * Traduction d'une chaine de caractère
     *
     * @param {string} key - Clef de traduction
     * @param {boolean} plural - Utilisation du pluriel
     *
     * @return {string}
     */
    protected tr (key: string, plural = false): string {
        return key
    }

    /**
     * Traduction d'une chaine de caractère
     *
     * @param {string} key - Clef de traduction
     * @param {boolean} plural - Utilisation du pluriel
     *
     * @return {string}
     */
    public static str (key: string, plural = false): string {
        return key
    }

    /**
     * Lance le chargement de l'application
     */
    protected load (): void {
        this.loaded = false
    }

    /**
     * Désactive l'état de chargement de l'application
     */
    protected unload (): void {
        this.loaded = true
    }

    /**
     * Mise en capitale d'une chaine de caractères
     * @param {string} value - Valeur à modifier
     *
     * @return {string}
     */
    protected capitalize (value: string): string {
        return value.charAt(0).toUpperCase() + value.slice(1)
    }
}
