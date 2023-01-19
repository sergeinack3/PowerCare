{{*
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="4">{{$title|smarty:nodefaults}}</th>
  </tr>
  <tr>
    <th>{{tr}}Person{{/tr}}</th>
    <th>{{tr}}Category{{/tr}} / {{tr}}Label{{/tr}}</th>
    <th>{{tr}}Telephone{{/tr}}</th>
    <th>{{tr}}Shift{{/tr}}</th>
  </tr>
  {{foreach from=$plages_astreinte item=_plage}}
  {{assign var=user value=$_plage->_ref_user}}
    <tr>
      <td style="background:#{{$_plage->_color}};">
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_plage->_ref_user}}
      </td>
      <td>
        {{if $_plage->_ref_category}}{{$_plage->_ref_category->name}}{{/if}}
        {{if $_plage->categorie && $_plage->libelle}} - {{/if}}
        {{$_plage->libelle}}
      </td>

      <td>
        <i class="me-icon phone me-primary"></i>
        <strong>{{mb_value object=$_plage field=phone_astreinte}}</strong>
        {{if $_plage->_ref_user->_user_astreinte}}({{mb_value object=$_plage->_ref_user field=_user_astreinte}}){{/if}}
      </td>
      <td>
        {{mb_include module=system template=inc_interval_datetime from=$_plage->start to=$_plage->end}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="4" class="empty" style="height: 40px;">{{tr}}CPlageAstreinte.none{{/tr}}</td>
    </tr>
  {{/foreach}}
  {{mb_include module=astreintes template=inc_legend_planning_astreinte}}
</table>
