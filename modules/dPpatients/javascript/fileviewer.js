/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

popFile = window.popFile || function (objectClass, objectId, elementClass, elementId, sfn) {
  new Url().ViewFilePopup(objectClass, objectId, elementClass, elementId, sfn);
};