/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

import INProvider from "./INProvider"

/**
 * Provider principal de status
 */
export default class ErrorsProvider extends INProvider {
    constructor () {
        super()
        this.url = "errors"
    }

  private fieldApi: object = {
      id: "id",
      type: "type",
      datetime: "datetime",
      errorLogId: "error_log_id",
      errorType: "error_type",
      file: "file",
      requestUid: "request_uid",
      serverIp: "server_ip",
      message: "text",
      userId: "user_id"
  }

  protected translateData (data: any): object {
      return data.map(
          (error) => {
              return {
                  id: error.id,
                  type: error.type,
                  datetime: error.attributes.datetime,
                  errorLogId: error.attributes.error_log_id,
                  errorType: error.attributes.error_type,
                  file: error.attributes.file,
                  requestUid: error.attributes.request_uid,
                  serverIp: error.attributes.server_ip,
                  message: error.attributes.text,
                  userId: error.attributes.user_id
              }
          }
      )
  }

  protected getFieldApi (field: string): string {
      if (!this.fieldApi[field]) {
          return field
      }
      return this.fieldApi[field]
  }
}
