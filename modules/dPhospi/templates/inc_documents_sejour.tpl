{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=cabinet script=accident_travail register=true}}
{{assign var=modAmeli value="ameli"|module_active}}

{{if $modAmeli}}
  {{mb_script module=ameli script=AvisArretTravail register=true}}
{{/if}}

<script>
  Main.add(function () {
    {{if $modAmeli}}
    AvisArretTravail.loadArretTravail(null, '{{$sejour->_id}}', '{{$sejour->_class}}');
    {{/if}}

    AccidentTravail.loadAccidentTravail(null, '{{$sejour->_id}}', '{{$sejour->_class}}');
  });
</script>

{{mb_include module=patients template=inc_button_vue_globale_docs
patient_id=$sejour->patient_id object=$sejour->_ref_patient context_imagerie=$sejour show_send_mail=1 show_telemis=1}}

{{if $sejour->_ref_patient && "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
  <div class="me-text-align-center">
      {{mb_include module=appFineClient template=inc_show_sas_tabs tabs="order|received" patient=$sejour->_ref_patient count=$count_order}}
  </div>
{{/if}}

<h1>
    {{$sejour}}
</h1>

{{if !$operation_id || $op_with_sejour}}
  <div id="Documents-{{$sejour->_guid}}" class="me-padding-right-4 me-box-sizing-border" style="float: left; width: 50%;">
    <script>
      Document.register('{{$sejour->_id}}', '{{$sejour->_class}}', '{{$sejour->praticien_id}}', 'Documents-{{$sejour->_guid}}', 'normal');
    </script>
  </div>
  <div id="Files-{{$sejour->_guid}}" class="me-padding-left-4 me-box-sizing-border" style="float: left; width: 50%;">
    <script>
      File.register('{{$sejour->_id}}', '{{$sejour->_class}}', "Files-{{$sejour->_guid}}");
    </script>
  </div>
{{/if}}

{{if !$only_sejour}}
  {{if (!$operation_id || $op_with_sejour) && $sejour->_ref_consult_anesth->_id}}
    {{assign var=consult_anesth value=$sejour->_ref_consult_anesth}}
    {{assign var=consult value=$consult_anesth->_ref_consultation}}
    <h2 style="clear: both;">{{tr}}CConsultAnesth{{/tr}} du {{$consult->_date|date_format:$conf.date}}</h2>
    <div id="Documents-{{$consult_anesth->_guid}}" class="me-padding-right-4 me-box-sizing-border" style="float: left; width: 50%;">
      <script>
        Document.register('{{$consult_anesth->_id}}', '{{$consult_anesth->_class}}', '{{$consult_anesth->chir_id}}', 'Documents-{{$consult_anesth->_guid}}', 'normal');
      </script>
    </div>
    <div id="Files-{{$consult->_guid}}" class="me-padding-left-4 me-box-sizing-border" style="float: left; width: 50%;">
      <script>
        File.register('{{$consult->_id}}', '{{$consult->_class}}', "Files-{{$consult->_guid}}");
      </script>
    </div>
  {{/if}}

  {{if !$operation_id && $sejour->_ref_consultations|@count}}
    {{foreach from=$sejour->_ref_consultations item=_consultation}}
      <h2 style="clear: both;">{{tr}}CConsultation{{/tr}} du {{$_consultation->_ref_plageconsult->date|date_format:$conf.date}}</h2>
      <div id="Documents-{{$_consultation->_guid}}" class="me-padding-right-4 me-box-sizing-border" style="float: left; width: 50%;">
        <script>
          Document.register('{{$_consultation->_id}}', '{{$_consultation->_class}}', '{{$_consultation->_ref_plageconsult->chir_id}}', 'Documents-{{$_consultation->_guid}}', 'normal');
        </script>
      </div>
      <div id="Files-{{$_consultation->_guid}}" class="me-padding-left-4 me-box-sizing-border" style="float: left; width: 50%;">
        <script>
          File.register('{{$_consultation->_id}}', '{{$_consultation->_class}}', "Files-{{$_consultation->_guid}}");
        </script>
      </div>
    {{/foreach}}
  {{/if}}

  {{foreach from=$sejour->_ref_operations item=operation}}
    <h2 style="clear: both;">{{tr}}COperation{{/tr}} du {{$operation->_datetime|date_format:$conf.date}} {{if $operation->annulee}}
      <span class="cancelled">({{tr}}COperation-annulee{{/tr}}){{/if}}</span></h2>
    <div id="Documents-{{$operation->_guid}}" class="me-padding-right-4 me-box-sizing-border" style="float: left; width: 50%;">
      <script>
        Document.register('{{$operation->_id}}', '{{$operation->_class}}', '{{$operation->chir_id}}', 'Documents-{{$operation->_guid}}', 'normal');
      </script>
    </div>
    <div id="Files-{{$operation->_guid}}" class="me-padding-left-4 me-box-sizing-border" style="float: left; width: 50%;">
      <script>
        File.register('{{$operation->_id}}', '{{$operation->_class}}', "Files-{{$operation->_guid}}");
      </script>
    </div>
    {{if $operation->_ref_consult_anesth->_id && !$operation_id && (!$sejour->_ref_consult_anesth->_id || $sejour->_ref_consult_anesth->_id != $operation->_ref_consult_anesth->_id)}}
      {{assign var=consult_anesth value=$operation->_ref_consult_anesth}}
      {{assign var=consult value=$consult_anesth->_ref_consultation}}
      <h2 style="clear: both;">{{tr}}CConsultAnesth{{/tr}} du {{$consult_anesth->_date_consult|date_format:$conf.date}}</h2>
      <div id="Documents-{{$consult_anesth->_guid}}" class="me-padding-right-4 me-box-sizing-border" style="float: left; width: 50%;">
        <script>
          Document.register('{{$consult_anesth->_id}}', '{{$consult_anesth->_class}}', '{{$consult_anesth->chir_id}}', 'Documents-{{$consult_anesth->_guid}}', 'normal');
        </script>
      </div>
      <div id="Files-{{$consult->_guid}}" class="me-padding-left-4 me-box-sizing-border" style="float: left; width: 50%;">
        <script>
          File.register('{{$consult->_id}}', '{{$consult->_class}}', "Files-{{$consult->_guid}}");
        </script>
      </div>
    {{/if}}
  {{/foreach}}
{{/if}}

{{if $with_patient}}
  {{assign var=patient value=$sejour->_ref_patient}}
  <h1 style="clear: both;">{{$patient}}</h1>
  <div id="Documents-{{$patient->_guid}}" class="me-padding-right-4 me-box-sizing-border" style="float: left; width: 50%;">
    <script>
      Document.register('{{$patient->_id}}', '{{$patient->_class}}', null, 'Documents-{{$patient->_guid}}', 'normal');
    </script>
  </div>
  <div id="Files-{{$patient->_guid}}" class="me-padding-left-4 me-box-sizing-border" style="float: left; width: 50%;">
    <script>
      File.register('{{$patient->_id}}', '{{$patient->_class}}', "Files-{{$patient->_guid}}");
    </script>
  </div>
{{/if}}

{{if 'lifeline'|module_active}}
  {{mb_include module=lifeline template=inc_get_lifeline_record patient_id=$sejour->patient_id}}
{{/if}}

<table class="form me-no-align me-no-box-shadow">
  <tr class="me-row-valign">
    {{if $modAmeli}}
      <td id="arret_travail" class="halfPane"></td>
    {{/if}}
    <td id="accident_travail_mp" {{if $modAmeli}}class="halfPane"{{/if}}></td>
  </tr>
</table>
