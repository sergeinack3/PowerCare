{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$sejour->_ref_suivi_medical|@count}}
  <div class="empty" style="margin-top: 10px;">
    Aucun événement
  </div>
{{/if}}

<ul class="timeline">
  {{foreach from=$sejour->_ref_suivi_medical key=_mix_datetime item=_suivi}}
    {{assign var=_datetime value=$mapping_datetime.$_mix_datetime}}

    {{assign var=_suivi_user   value=""}}
    {{assign var=_suivi_classe value=""}}
    {{assign var=_suivi_icon   value=""}}

    {{if is_array($_suivi|smarty:nodefaults)}}
      {{assign var=first_trans value=$_suivi.0}}

      {{assign var=_suivi_user   value=$first_trans->_ref_user}}
      {{assign var=_suivi_classe value=$first_trans->_class}}
      {{assign var=_suivi_icon   value="fa-bell"}}
    {{elseif $_suivi|instanceof:'Ox\Mediboard\Hospi\CObservationMedicale'}}
      {{assign var=_suivi_user   value=$_suivi->_ref_user}}
      {{assign var=_suivi_classe value=$_suivi->_class}}
      {{assign var=_suivi_icon   value="fa-heartbeat"}}
    {{elseif $_suivi|instanceof:'Ox\Mediboard\Cabinet\CConsultation'}}
      {{assign var=_suivi_user   value=$_suivi->_ref_praticien}}
      {{assign var=_suivi_classe value=$_suivi->_class}}
      {{assign var=_suivi_icon   value="fa-user-md"}}
    {{elseif $_suivi|instanceof:'Ox\Mediboard\Patients\CConstantesMedicales'}}
      {{assign var=_suivi_user value=$_suivi->_ref_user}}
      {{assign var=_suivi_classe value=$_suivi->_class}}
      {{assign var=_suivi_icon   value="fa-chart-bar"}}
    {{elseif $_suivi|instanceof:'Ox\Mediboard\Urgences\CRPU'}}
      {{assign var=_suivi_user   value=$sejour->_ref_praticien}}
      {{assign var=_suivi_classe value=$_suivi->_class}}
      {{assign var=_suivi_icon   value="fa-hospital-alt"}}
    {{/if}}

    <li class="timeline_past evenement-span">
      <time class="timeline_time">
        {{assign var=day_format   value=$_datetime|date_format:"%d"}}
        {{assign var=month_format value=$_datetime|date_format:"%B"}}

        {{assign var=day  value="$day_format $month_format"}}
        {{assign var=hour value=$_datetime|date_format:$conf.time}}
        <span class="timeline_day">{{mb_ditto name=day value=$day replacement=""}}</span>
        <br />
        <span class="timeline_hour">{{$hour}}</span>
      </time>
    </li>
    <li class="evenement-span">
      <div style="border: 0;">
        <div class="timeline_icon timeline_icon_consultations">
          <span>
            <i class="fa {{$_suivi_icon}}"></i>
          </span>
        </div>
      </div>
      <div class="timeline_label">
        <table class="main layout">
          <tr>
            <td style="width: 20%" class="text">
              {{tr}}{{$_suivi_classe}}{{/tr}}

              <br />

              {{if $_suivi_user}}
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_suivi_user}}
              {{/if}}
            </td>
            <td>
              {{if $_suivi_classe === "CTransmissionMedicale"}}
                {{foreach from=$_suivi item=_trans_by_type key=_type}}
                  {{if $_type != "0" && $_trans_by_type|@count}}
                    {{tr}}CTransmissionMedicale-_text_{{$_type}}{{/tr}} :
                    {{foreach from=$_trans_by_type item=_trans}}
                      {{mb_value object=$_trans field=text}}
                    {{/foreach}}
                  {{/if}}
                {{/foreach}}
              {{elseif $_suivi_classe === "CObservationMedicale"}}
                {{mb_value object=$_suivi field=text}}
              {{elseif in_array($_suivi_classe, array("CConsultation", "CRPU"))}}
                {{foreach from=$fields_display.$_suivi_classe item=_field}}
                  {{if $_suivi->$_field}}
                    <div>
                      <u>{{tr}}{{$_suivi_classe}}-{{$_field}}{{/tr}}</u> : {{mb_value object=$_suivi field=$_field}}
                    </div>

                    <br />
                  {{/if}}
                {{/foreach}}
              {{elseif $_suivi_classe === "CConstantesMedicales"}}
                {{foreach from='Ox\Mediboard\Patients\CConstantesMedicales'|static:"list_constantes" key=_key item=_field}}
                  {{if $_key|substr:0:1 != "_" && $_suivi->$_key != null}}
                    {{mb_title object=$_suivi field=$_key}} :
                    {{if array_key_exists("formfields", $_field)}}
                      {{mb_value object=$_suivi field=$_field.formfields.0 size="2"}}
                      {{if array_key_exists(1, $_field.formfields)}}
                        /
                        {{mb_value object=$_suivi field=$_field.formfields.1 size="2"}}
                      {{/if}}
                    {{else}}
                      {{mb_value object=$_suivi field=$_key}}
                    {{/if}} {{$_field.unit}} {{if array_key_exists($_key, $_suivi->_refs_comments)}}({{$_suivi->_refs_comments.$_key->comment}}){{/if}}
                    <br>
                  {{/if}}
                {{/foreach}}
              {{/if}}
            </td>
          </tr>
        </table>
      </div>
    </li>
  {{/foreach}}
</ul>
