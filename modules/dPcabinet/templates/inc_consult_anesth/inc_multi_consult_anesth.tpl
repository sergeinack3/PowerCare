{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=dossiers_anesth value=$consult->_refs_dossiers_anesth}}

{{mb_default var=onlycreate value=false}}
{{mb_default var=id_moebius_dhe value=false}}

<script>
  reloadDossierAnesth = function(dossier_anesth_id) {
    var url = new Url("cabinet", "edit_consultation", "tab");
    url.addParam("selConsult", "{{$consult->_id}}");
    url.addParam("dossier_anesth_id", dossier_anesth_id);
    url.redirect();
  }

  GestionDA = {
    url: null,
    edit: function() {
      var url = new Url("cabinet", "vw_gestion_da");
      url.addParam("conusultation_id", '{{$consult->_id}}');
      url.requestModal(1000);
      GestionDA.url = url;
    }
  }
</script>

<table>
  {{assign var=operation value=$consult_anesth->_ref_operation}}
  {{if $consult_anesth->operation_id}}
    {{assign var=sejour value=$consult_anesth->_ref_operation->_ref_sejour}}
    <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour->_guid}}')">
      <strong>{{tr}}CSejour{{/tr}} :</strong>
      Dr {{$sejour->_ref_praticien->_view}} -
      {{if $sejour->type!="ambu" && $sejour->type!="exte"}} {{$sejour->_duree_prevue}} jour(s) -{{/if}}
      {{mb_value object=$sejour field=type}}
    </span>
    {{if $sejour->circuit_ambu}}
      <span id="sejour_circuit_ambu" class="texticon dhe_flag_circuit_ambu" title="{{tr}}CSejour-circuit_ambu-desc{{/tr}}" style="margin-left: 2px; font-size: 0.8em;">
        {{tr}}CSejour-circuit_ambu-court{{/tr}}: {{$sejour->circuit_ambu}}
      </span>
    {{/if}}
    <br/>
    <span onmouseover="ObjectTooltip.createEx(this, '{{$operation->_guid}}', null, { view_tarif: true })">
      <strong>{{tr}}COperation{{/tr}} :</strong>
      le <strong>{{$operation->_datetime|date_format:$conf.longdate}}</strong>
      par le <strong>Dr {{$operation->_ref_chir->_view}}</strong>
        {{if $operation->libelle}}
          [{{$operation->libelle}}]
        {{/if}}
        {{if $operation->cote}}
          ({{mb_label object=$operation field=cote}}:
          {{mb_value object=$operation field=cote}})
        {{/if}}
    </span>
    <br>
    {{if $id_moebius_dhe}}
      <strong>{{tr}}CMoebiusAPI-Selected protocol{{/tr}}:</strong> {{$id_moebius_dhe->_view}}
    {{/if}}
  {{else}}
    {{if $consult_anesth->date_interv || $consult_anesth->chir_id || $consult_anesth->libelle_interv}}
      <tr>
        <th class="me-text-align-right">{{mb_label object=$consult_anesth field=date_interv}}</th>
        <td class="me-valign-middle">{{mb_value object=$consult_anesth field=date_interv}}</td>
      </tr>
      <tr>
        <th class="me-text-align-right">{{mb_label object=$consult_anesth field=chir_id}}</th>
        <td class="me-valign-middle">{{mb_value object=$consult_anesth field=chir_id}}</td>
      </tr>
      <tr>
        <th class="me-text-align-right">{{mb_label object=$consult_anesth field=libelle_interv}}</th>
        <td class="me-valign-middle">{{mb_value object=$consult_anesth field=libelle_interv}}</td>
      </tr>
    {{/if}}
    <tr>
      <td colspan="2">L'intervention n'est pas liée</td>
    </tr>
  {{/if}}
  <tr>
    <td colspan="2" >
      <button type="button" class="edit me-tertiary" onclick="GestionDA.edit();">
        Gérer le{{if $dossiers_anesth|@count > 1}}s {{$dossiers_anesth|@count}} dossiers {{else}} dossier{{/if}}
      </button>
      {{if $consult_anesth->operation_id && "moebius"|module_active && $app->user_prefs.ViewConsultMoebius}}
        <button type="button" class="edit me-tertiary" onclick="ConsultMoebius.gestionIdsExterne('{{$consult_anesth->_id}}', '{{$consult->_id}}', '{{$consult_anesth->_ref_chir->_id}}');">
          {{tr}}mod-moebius-tab-vw_gestion_id_externes{{/tr}}
        </button>

        {{if !$id_moebius_dhe}}
          <div class="small-warning">{{tr}}moebius-consult_anesth-no_protocole_dhe{{/tr}}</div>
        {{/if}}
      {{/if}}

      {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf &&
           "moebius"|module_active && $app->user_prefs.ViewConsultMoebius }}
        {{mb_include module=appFineClient template=inc_show_ant count_new_antecedents=$consult_anesth->_ref_sejour->_ref_dossier_medical->_count_antecedents}}
      {{/if}}
    </td>
  </tr>
</table>
