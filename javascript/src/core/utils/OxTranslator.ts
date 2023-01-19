/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Translator string
 *
 * @param {string} key - Translator key
 * @param {string} values - values inject in translator
 * @param {boolean} plural - use plural
 *
 * @return {string}
 */
export function tr (key: string, values: string|null = null, plural = false): string {
    // @ts-ignore
    return window.$T(key + (plural ? "|pl" : ""), values)
}
