{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form id="meeting-edit" name="meeting-edit" method="post" data-meeting-id="{{$meeting->_id}}" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$meeting}}
  {{mb_key object=$meeting}}
  {{mb_field object=$meeting field=reunion_id hidden=true}}

  <table class="table width100">
    <tr>
      <th class="title" colspan="2">{{tr var1=$an_appointment->_ref_plageconsult->date}}CReunion-date %s meeting{{/tr}}</th>
    </tr>

    <tr>
      <td class="width50"><h4 style="margin: 5px">{{tr}}CReunion-Participants{{/tr}}:</h4>
        <ul>
            {{foreach from=$practitioners item=_practitioner}}
              <li>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_practitioner}}</li>
            {{/foreach}}
        </ul>
      </td>

      <td colspan="2" style="text-align: right">
        <button class="send generate-all"
                type="button"
                style="vertical-align: middle; margin: 10px 20px 10px auto; font-size: 15px; width: 100px; height: 35px;"> {{tr}}Send{{/tr}}</button>
      </td>
    </tr>

    <tr>
      <th style="text-align: left">{{tr}}CReunion-Order of the day{{/tr}}</th>
      <th style="text-align: left">{{mb_label object=$meeting field=remarques}}</th>
    </tr>

    <tr>
      <td>{{mb_field object=$meeting field=motif register=true form=meeting-edit}}</td>
      <td>{{mb_field object=$meeting field=remarques register=true form=meeting-edit}}</td>
    </tr>
  </table>
</form>

<hr style="margin: 20px; border-color: #ccc;">
