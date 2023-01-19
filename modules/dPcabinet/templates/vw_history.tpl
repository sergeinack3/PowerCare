{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="4" class="title">
      Dossier de {{$consult->_view}}
    </th>
  </tr>
  
  <tr>
    <th>Utilisateur</th>
    <th>Date</th>
    <th>{{tr}}Action{{/tr}}</th>
    <th>Propriétés</th>
  </tr>
  
  {{foreach from=$consult->_ref_logs item=curr_object}}
    <tr>
      <td>{{$curr_object->_ref_user->_view}}</td>
      <td>{{$curr_object->date|date_format:$conf.datetime}}</td>
      <td>{{tr}}CUserLog.type.{{$curr_object->type}}{{/tr}}</td>
      <td>
        {{foreach from=$curr_object->_fields|smarty:nodefaults item=curr_field}}
        {{$curr_field}}<br />
        {{/foreach}}
      </td>
    </tr>
  {{/foreach}}

  {{foreach from=$consult->_refs_dossiers_anesth item=_dossier_anesth}}
    <tr>
      <th colspan="4" class="title">
        Consultation Préanesthesique
      </th>
    </tr>

    {{foreach from=$_dossier_anesth->_ref_logs item=curr_object}}
      <tr>
        <td>{{$curr_object->_ref_user->_view}}</td>
        <td>{{$curr_object->date|date_format:$conf.datetime}}</td>
        <td>{{tr}}CUserLog.type.{{$curr_object->type}}{{/tr}}</td>
        <td>
          {{foreach from=$curr_object->_fields|smarty:nodefaults item=curr_field}}
            {{$curr_field}}<br />
          {{/foreach}}
        </td>
      </tr>
    {{/foreach}}
  {{/foreach}}
</table>