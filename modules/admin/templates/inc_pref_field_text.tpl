{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<textarea name="pref[{{$var}}]" {{if $readonly}}readonly="readonly"{{/if}} rows="3">{{$pref.user}}</textarea>
