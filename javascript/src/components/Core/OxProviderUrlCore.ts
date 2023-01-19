/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * OxProviderUrl
 */
export default class OxProviderUrlCore {
    private url: string = ""
    private filters: {
        field: string,
        operator: string,
        value: string
    }[] = []

    constructor (url?: string) {
        this.url = url ? url : ""
        return this
    }

    /**
     * Add a filter to the filters list. Do nothing if the value explicitly is null. To force the null value, use "" or "false" values
     *
     * @param {string} field - Label du champ à filtrer
     * @param {string} operator - See RequestFilter.php for available values
     * @param {string} value - Valeur à filtrer
     *
     * @return {OxProviderUrlCore}
     */
    public addFilter (field: string, operator: string, value: string): OxProviderUrlCore {
        if (value === null) {
            return this
        }
        this.filters.push(
            {
                field: field,
                operator: operator,
                value: value.toString()
            }
        )
        return this
    }

    /**
     * Construction de l'url de la ressource final contenant les divers filtres enregistrés
     *
     * @return {string}
     */
    public buildUrl (): string {
        if (this.filters.length === 0) {
            return this.url
        }
        let url = this.url
        this.filters.forEach(
            (_filter, _index) => {
                url += (_index === 0 ? "?filter=" : ";") + _filter.field + "." + _filter.operator + "." + _filter.value
            }
        )
        return url
    }
}
