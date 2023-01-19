{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $patient->_id}}
  <form method="get" action="?" id="list_Relations">
    <ul style="text-align: left;">
      <li>
        <input type="checkbox" name="object" onclick="attach.setObject(this, '{{$patient->_guid}}'); checkrelation();"
               {{if $patient->_id == $dossier_id}}checked{{/if}} />
        <strong onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}')">{{$patient}} (Patient)</strong> ({{mb_value object=$patient field=naissance}})
      </li>

      <li><strong>Séjours</strong></li>
    {{foreach from=$patient->_ref_sejours item=_sejour}}
      <li>
        <input type="checkbox" name="object" onclick="attach.setObject(this, '{{$_sejour->_guid}}');checkrelation();"
               {{if $_sejour->_id == $dossier_id}}checked{{/if}} />
        {{$_sejour}}
      </li>
      {{foreach from=$_sejour->_ref_operations item=_op}}
        <li style="margin-left: 15px; padding-left: 15px; border-left: solid 1px grey;">
          <input type="checkbox" name="object" onclick="attach.setObject(this, '{{$_op->_guid}}'); checkrelation();" />
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_op->_guid}}')">
                    {{tr}}dPplanningOp-COperation of{{/tr}} {{mb_value object=$_op field=_datetime}}
                  </span>
          avec le Dr {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_op->_ref_chir}}
          {{if $_op->annulee}}<span style="color: red;">[ANNULE]</span>{{/if}}
        </li>
      {{foreachelse}}
        <li class="empty">{{tr}}COperation.none{{/tr}}</li>
      {{/foreach}}

      {{foreach from=$_sejour->_ref_consultations item=_consult}}
        <li style="margin-left:15px; padding-left: 15px; border-left: solid 1px grey;">
          <input type="checkbox" name="object" onclick="attach.setObject(this, '{{$_consult->_guid}}'); checkrelation(); "/>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_guid}}')">
          {{tr}}CConsultation-consult-on{{/tr}} {{mb_value object=$_consult field=_datetime}}
          </span>
          avec le Dr {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_consult->_ref_chir}}
          {{if $_consult->annule}}<span style="color: red;">[ANNULE]</span>{{/if}}
        </li>
      {{/foreach}}

    {{foreachelse}}
      <li class="empty">{{tr}}CSejour.none{{/tr}}</li>
    {{/foreach}}

    <li><strong>Consultations</strong></li>
    {{foreach from=$patient->_ref_consultations item=_consult}}
      <li>
        <input type="checkbox" name="object" onclick="attach.setObject(this, '{{$_consult->_guid}}'); checkrelation();" />
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_consult->_guid}}')">
          {{tr}}CConsultation-consult-on{{/tr}} {{mb_value object=$_consult field=_datetime}}
        </span>
        avec le Dr {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_consult->_ref_chir}}
        {{if $_consult->annule}}<span style="color: red;">[ANNULE]</span>{{/if}}
      </li>
      {{foreachelse}}
      <li class="empty">{{tr}}CConsultation.none{{/tr}}</li>
    {{/foreach}}
    </ul>
  </form>
{{else}}
  <ul>
    <li class="empty">{{tr}}CPatient.none{{/tr}}</li>
  </ul>
{{/if}}
