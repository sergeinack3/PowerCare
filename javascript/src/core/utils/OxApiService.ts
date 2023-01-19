/**
 * @package Openxtrem\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/**
 * Api calls
 */
import axios, { AxiosInstance, AxiosRequestConfig, AxiosResponse } from "axios"
import { addError } from "@/core/utils/OxNotifyManager"

// Axios
const oxApiService: AxiosInstance = axios.create({
    headers: {
        "Content-Type": "application/vnd.api+json",
        "X-Requested-With": "XMLHttpRequest"
    },
    timeout: 50000, // timeout
    withCredentials: true,
    validateStatus: (status: number) => status >= 200 && status < 400
})

// request axios
oxApiService.interceptors.request.use(
    (config: AxiosRequestConfig) => {
        return config
    },
    (error) => {
        // Do something with request error
        console.error("error:", error) // for debug
        Promise.reject(error)
    }
)

// response axios
oxApiService.interceptors.response.use(
    (res: AxiosResponse) => {
        // Before do something with api return
        return res
    },
    (error) => {
        addError(error.message)
        console.error(error)
        return Promise.reject(error)
    }
)

export default oxApiService
