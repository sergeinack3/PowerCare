{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $modal}}
<div id="info_types">
  {{/if}}

  <table class="tbl me-no-align me-no-box-shadow">
    <tr>
      <th class="title" colspan="3">
        <button type="button" class="new notext" onclick="InfoGroup.editInfoType();" id="btn_new_info_type" style="float:left;">
          {{tr}}CInfoType-msg-new{{/tr}}
        </button>

        {{tr}}CInfoType-title-list{{/tr}}
      </th>
    </tr>
    <tr>
      <th>
        {{mb_title class=CInfoType field=name}}
      </th>
      <th>
        {{tr}}CInfoType-back-infos_group{{/tr}}
      </th>
      <th style="width: 20px;"></th>
    </tr>
    {{foreach from=$types item=type}}
      <tr>
        <td>
          {{mb_value object=$type field=name}}
        </td>
        <td>
          {{$type->_count_infos}}
        </td>
        <td style="width: 20px;">
          <button type="button" class="edit notext" onclick="InfoGroup.editInfoType('{{$type->_id}}');">{{tr}}Edit{{/tr}}</button>
        </td>
      </tr>
      {{foreachelse}}
      <tr>
        <td class="empty" colspan="3">
          {{tr}}CInfoType.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
  </table>

  {{if $modal}}
</div>
{{/if}}