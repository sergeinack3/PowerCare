{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{assign var=use_acte_date_now value=$app->user_prefs.use_acte_date_now}}

<script>
  onChangeDate = function(form, field) {
    $V(getForm('selectTarif').elements['_datetime'], $V(field));

    return form.onsubmit();
  };

  submitTarifSejour = function(form) {
    {{if $consult->_id}}
      return onSubmitFormAjax(form, {onComplete: loadActes.curry({{$sejour->_id}}, {{$sejour->praticien_id}})});
    {{else}}
      return onSubmitFormAjax(form, {onComplete: loadActes.curry({{$sejour->_id}}, {{$sejour->praticien_id}})});
    {{/if}}
  };

  exportActs = function() {
    new Url('pmsi', 'export_multiple_actes_pmsi')
      .addParam('object_guids', Object.toJSON(['{{$sejour->_guid}}']))
      .requestUpdate('systemMsg', {
        method:        'post',
        getParameters: {m: 'pmsi', a: 'export_multiple_actes_pmsi'}
      });
  };

  {{if $consult->_id}}
    function loadActes() {
      var url = new Url("cabinet", "ajax_vw_actes");
      url.addParam("consult_id", "{{$consult->_id}}");
      url.requestUpdate("Actes");
    }
  {{/if}}
</script>

<table class="form me-no-align me-compact me-no-box-shadow">
  <tr>
    {{if $sejour->sortie_reelle}}
      <td>
        <button class="tick" onclick="exportActs();">Exporter les actes</button>
      </td>
    {{/if}}
    <th>{{mb_label object=$sejour field="exec_tarif"}}</th>
    <td>
      <!-- Formulaire date d'éxécution de tarif -->
      <form name="editExecTarif" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this);">
        {{mb_key object=$sejour}}
        {{mb_class object=$sejour}}
        {{mb_field object=$sejour field="exec_tarif" form="editExecTarif" register=true onchange="onChangeDate(this.form, this);"}}
      </form>
    </td>
    <th><label for="_codable_guid">Tarif</label></th>
    <td>
      <form name="selectTarif" action="?m={{$m}}" method="post" onsubmit="return submitTarifSejour(this);">
        {{if $consult->_id}}
          {{mb_class object=$consult}}
          {{mb_key   object=$consult}}
          <input type="hidden" name="_datetime" value="{{$consult->_datetime}}">
        {{else}}
          {{mb_class object=$sejour}}
          {{mb_key   object=$sejour}}
          <input type="hidden" name="_datetime" value="{{$sejour->exec_tarif}}">
        {{/if}}
        <input type="hidden" name="_bind_tarif" value="1"/>
        <input type="hidden" name="_delete_actes" value="0"/>
        <input type="hidden" name="entree_prevue" value="{{$sejour->entree_prevue}}">
        <input type="hidden" name="sortie_prevue" value="{{$sejour->sortie_prevue}}">
        {{mb_field object=$sejour field=codes_ccam hidden=true}}

          <select name="_codable_guid" class="str" onchange="this.form.onsubmit();">
          <option value="" selected>&mdash; {{tr}}Choose{{/tr}}</option>
          {{if $tarifs.user|@count}}
            <optgroup label="{{tr}}CConsultation-Practitioner price{{/tr}}">
              {{foreach from=$tarifs.user item=_tarif}}
                <option value="{{$_tarif->_guid}}" {{if $_tarif->_precode_ready}}class="checked"{{/if}}>{{$_tarif}}</option>
              {{/foreach}}
            </optgroup>
          {{/if}}
          {{if $tarifs.func|@count}}
            <optgroup label="{{tr}}CConsultation-Office price{{/tr}}">
              {{foreach from=$tarifs.func item=_tarif}}
                <option value="{{$_tarif->_guid}}" {{if $_tarif->_precode_ready}}class="checked"{{/if}}>{{$_tarif}}</option>
              {{/foreach}}
            </optgroup>
          {{/if}}
          {{if "dPcabinet Tarifs show_tarifs_etab"|gconf && $tarifs.group|@count}}
            <optgroup label="{{tr}}CConsultation-Etablishment price{{/tr}}">
              {{foreach from=$tarifs.group item=_tarif}}
                <option value="{{$_tarif->_guid}}" {{if $_tarif->_precode_ready}}class="checked"{{/if}}>{{$_tarif}}</option>
              {{/foreach}}
            </optgroup>
          {{/if}}
        </select>
      </form>
    </td>
  </tr>
</table>
