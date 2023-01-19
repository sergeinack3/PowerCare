{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$bris item=_bris}}
  <tr>
    <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_bris->_ref_user}}</td>
    <td><span onmouseover="ObjectTooltip.createEx(this, '{{$_bris->_ref_object->_guid}}')">{{$_bris->_ref_object}}</span></td>
    <td class="text">{{mb_value object=$_bris field=comment}}</td>
    <td>{{mb_value object=$_bris field=date}}</td>
  </tr>
{{foreachelse}}
  <tr>
    <td colspan="4" class="empty">{{tr}}CBrisDeGlace.none{{/tr}}</td>
  </tr>
{{/foreach}}