/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import INProvider from "./INProvider"

/**
 * Provider principal de status
 */
export default class PrerequisProvider extends INProvider {
    constructor () {
        super()
        this.url = "requirements"
    }

    protected translateData (data: any): object {
        const attributes = data.attributes

        return {
            pathAccess: attributes.path_access,
            phpExtensions: attributes.php_extensions,
            phpVersion: {
                nameRequired: attributes.php_version.version_required,
                description: attributes.php_version.description,
                check: attributes.php_version.check,
                nameInstalled: attributes.php_version.version_installed
            },
            sqlVersion: {
                nameRequired: attributes.sql_version.version_required,
                description: attributes.sql_version.description,
                check: attributes.sql_version.check,
                nameInstalled: attributes.sql_version.version_installed
            },
            urlRestrictions: attributes.url_restrictions
        }
    }
}
