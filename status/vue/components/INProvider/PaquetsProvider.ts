/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import INProvider from "./INProvider"

/**
 * Provider principal de status
 */
export default class PaquetsProvider extends INProvider {
    constructor () {
        super()
        this.url = "packages"
    }

    protected translateData (data: any): object {
        const attributes = data.attributes
        return {
            composerUrl: attributes.urls.Composer,
            packagistUrl: attributes.urls.Packagist,
            version: attributes.version,
            countRequired: attributes.count.required,
            countInstalled: attributes.count.installed,
            packages: attributes.packages.map(pack => {
                return {
                    name: pack.name,
                    versionRequired: pack.version_required,
                    versionInstalled: pack.version_installed,
                    description: pack.description,
                    license: pack.license,
                    isInstalled: pack.is_installed,
                    isDev: pack.is_dev,
                    url: attributes.urls.Packages + pack.name
                }
            })
        }
    }
}
