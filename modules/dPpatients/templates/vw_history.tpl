{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="10" class="title">
      Dossier de {{$patient}}
    </th>
  </tr>

  <tr>
    <th>{{mb_title class=CUserLog field=object_class}}</th>
    <th>{{mb_title class=CUserLog field=object_id}}</th>
    <th>{{mb_title class=CUserLog field=user_id}}</th>
    <th>{{mb_title class=CUserLog field=ip_address}}</th>
    <th colspan="2">{{mb_title class=CUserLog field=date}}</th>
    <th>{{mb_title class=CUserLog field=type}}</th>
    <th colspan="3">{{mb_title class=CUserLog field=fields}}</th>
  </tr>
  
  {{mb_include module=system template=inc_history_line logs=$patient->_ref_logs object=$patient}}

  {{if $patient->_ref_consultations}}
    <tr>
      <th colspan="10" class="title">
        Consultations
      </th>
    </tr>
    {{foreach from=$patient->_ref_consultations item=_consult}}
      {{if $_consult->_ref_logs}}
        <tr>
          <th colspan="10" class="category">
            <strong>
              Consultation du {{mb_value object=$_consult field=_date}}
              par le Dr {{$_consult->_ref_plageconsult->_ref_chir}}
            </strong>
          </th>
        </tr>
        {{mb_include module=system template=inc_history_line logs=$_consult->_ref_logs object=$_consult}}
      {{/if}}
    {{/foreach}}

  {{/if}}
  
  {{if $patient->_ref_sejours}}
    <tr>
      <th colspan="10" class="title">
        Séjours
      </th>
    </tr>
    {{foreach from=$patient->_ref_sejours item=curr_object}}
      <tr>
        <th colspan="10" class="category">
          <strong>{{$curr_object}}</strong>
        </th>
      </tr>
      {{mb_include module=system template=inc_history_line logs=$curr_object->_ref_logs object=$curr_object}}

      {{foreach from=$curr_object->_ref_operations item=curr_operation}}
        <tr>
          <th colspan="10" class="category">
            {{$curr_operation}} le {{$curr_operation->_datetime|date_format:$conf.date}}
          </th>
        </tr>
        {{mb_include module=system template=inc_history_line logs=$curr_operation->_ref_logs object=$curr_operation}}
      {{/foreach}}

      {{foreach from=$curr_object->_ref_affectations item=curr_affect}}
        <tr>
          <th colspan="10" class="category">
            Affectation
            du {{mb_value object=$curr_affect field=entree}}
            au {{mb_value object=$curr_affect field=sortie}}
          </th>
        </tr>
        {{mb_include module=system template=inc_history_line logs=$curr_affect->_ref_logs object=$curr_affect}}

      {{/foreach}}
    {{/foreach}}
  {{/if}}
</table>