{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  EditCheckList = {
    url:  null,
    edit: function (salle_id, date, multi_ouverture) {
      var url = new Url('salleOp', 'ajax_edit_checklist');
      url.addParam('date', date);
      url.addParam('salle_id', salle_id);
      url.addParam('bloc_id', 0);
      url.addParam('type', 'ouverture_salle');
      if (multi_ouverture) {
        url.addParam('multi_ouverture', multi_ouverture);
      }
      url.requestModal(null, null, {onClose: Control.Modal.refresh});
    }
  };
</script>
<table class="main tbl">
  <tr>
    <th class="title" colspan="2">
      {{tr}}mod-maternite-tab-ajax_checklist_maternite{{/tr}}
    </th>
  </tr>
  <tr>
    <th style="width:50%;">{{tr}}CSalle{{/tr}}</th>
    <th>{{tr}}CGroups-back-daily_check_lists{{/tr}}</th>
  </tr>
  {{foreach from=$listSalles item=_salle}}
    {{assign var=salle_id value=$_salle->_id}}
    {{if array_key_exists($salle_id, $date_last_checklist)}}
      <tr>
        <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_salle->_guid}}');">
            {{$_salle->_view}}
          </span>
        </td>
        <td>
          <div class="info">
            {{tr}}CDailyCheckList.last_validation{{/tr}}: {{$date_last_checklist.$salle_id|date_format:$conf.datetime}}
          </div>
          {{if $date_last_checklist.$salle_id|date_format:$conf.date != $date|date_format:$conf.date}}
            <button class="checklist" type="button" onclick="EditCheckList.edit('{{$salle_id}}', '{{$date}}', true);">
              {{tr}}CDailyCheckList.validation{{/tr}}
            </button>
          {{else}}
            <button class="checklist" type="button" onclick="EditCheckList.edit('{{$salle_id}}', '{{$date}}', true);">
              {{tr}}CDailyCheckList._type.ouverture_salle{{/tr}}
            </button>
          {{/if}}
        </td>
      </tr>
    {{/if}}
  {{/foreach}}
</table>
