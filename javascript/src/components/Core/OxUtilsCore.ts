/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/* eslint-disable  @typescript-eslint/no-explicit-any */
export const callJSFunction = (functionName: string, args: Array<any> = []) => {
    const contexts = functionName.split(".")
    let func = window
    for (let i = 0; i < contexts.length; i++) {
        func = func[contexts[i]]
    }
    if (args) {
        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
        // @ts-ignore
        func(...args)
    }
    else {
        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
        // @ts-ignore
        func()
    }
}
