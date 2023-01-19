{{*
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=astreintes script=plage}}

<table class="tbl">
  <tr>
    <th colspan="6" class="title">
      <button class="print not-printable" style="float: right;" onclick="window.print();">{{tr}}Print{{/tr}}</button>
      {{if $end_period}}Du{{/if}}
      {{$start_period|date_format:$conf.longdate}}
      {{if $end_period}}
        au {{$end_period|date_format:$conf.longdate}}
      {{/if}}
    </th>
  </tr>
  <tr>
    <th>{{tr}}common-Label{{/tr}}</th>
    <th>{{mb_title class=CCategorieAstreinte field=name}}</th>
    <th>{{tr}}User{{/tr}}</th>
    <th>{{tr}}common-Date|pl{{/tr}}</th>
    <th>{{tr}}common-Duration{{/tr}}</th>
    <th>{{tr}}common-Type{{/tr}}</th>
  </tr>
  {{foreach from=$astreintes item=_astreinte}}
    <tr>
      <td style="background:#{{$_astreinte->_color}} !important; color:#{{$_astreinte->_font_color}} !important">{{$_astreinte->libelle}}</td>
      <td>{{mb_value object=$_astreinte->_ref_category field=name}}</td>
      <td>{{mb_value object=$_astreinte->_ref_user field=_user_last_name}}<br />
        <strong>{{mb_value object=$_astreinte field=phone_astreinte}}</strong></td>
      <td>{{mb_include module="system" template="inc_interval_datetime" from=$_astreinte->start to=$_astreinte->end}}</td>
      <td>{{mb_include module="system" template="inc_vw_duration" duration=$_astreinte->_duree}}</td>
      <td>{{mb_value object=$_astreinte field=type}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="6" class="empty">{{tr}}CPlageAstreinte.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
