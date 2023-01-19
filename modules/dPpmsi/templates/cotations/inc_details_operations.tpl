{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPplanningOp script=operation ajax=true}}
{{mb_script module=dPplanningOp script=sejour ajax=true}}

<script type="text/javascript">
  toggleOperations = function(field) {
    $$('input.select_operations').each(function(checkbox) {
      checkbox.checked = field.checked;
    });
  };
</script>

<table class="tbl">
  <tr>
    <th class="title" colspan="9" style="background-color: #68c; color: #fff; font-size: 1.2em; font-weight: bold;">
      {{tr}}pmsi-title-details_cotation{{/tr}} {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$chir}}
      {{tr}}date.from{{/tr}} {{$begin_date}} {{tr}}date.to{{/tr}} {{$end_date}}
    </th>
  </tr>
  <tr>
    <th colspan="2">
      {{tr}}CPatient{{/tr}}
    </th>
    <th>
      {{tr}}COperation{{/tr}}
    </th>
    <th class="narrow">
      {{tr}}COperation-_actes_non_cotes{{/tr}}
    </th>
    <th>
      {{tr}}COperation-codes_ccam{{/tr}}
    </th>
    <th>
      {{tr}}pmsi-title-details_cotation-created_by_chir{{/tr}}
    </th>
    <th>
      {{tr}}pmsi-title-details_cotation-created_by_anesth{{/tr}}
    </th>
    <th>
      {{tr}}pmsi-title-details_cotation-unexported_acts{{/tr}}
    </th>
    <th>
      <input type="checkbox" name="_export_operations" onchange="toggleOperations(this);">
      <button type="button" title="Exporter les actes des interventions sélectionnées" onclick="exportActs();">
        <i class="fa fa-share fa-lg" style="color: #142328"></i>
      </button>
    </th>
  </tr>
  {{foreach from=$operations item=_operation}}
    {{assign var=_patient value=$_operation->_ref_patient}}
    {{assign var=_sejour value=$_operation->_ref_sejour}}
    <tr class="alternate">
      <td class="narrow">
        <button class="injection notext" type="button" onclick="Operation.dossierBloc('{{$_operation->_id}}', function() {Control.Modal.close();showDetailsFor('{{$chir->_id}}', '{{$period}}');});">
          Dossier de bloc
        </button>
        <button type="button" class="search notext" onclick="Sejour.showDossierPmsi('{{$_operation->sejour_id}}', '{{$_patient->_id}}', function() {Control.Modal.close();showDetailsFor('{{$chir->_id}}', '{{$period}}');});">
          Dossier PMSI
        </button>
      </td>
      <td>
        <strong onmouseover="ObjectTooltip.createEx(this, '{{$_patient->_guid}}');">
          {{$_patient}}
        </strong>
      </td>
      <td class="text">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_guid}}')">
            {{$_operation}}
          </span>
        {{if $_sejour->libelle}}
          <div class="compact">
              {{$_sejour->libelle}}
          </div>
        {{/if}}
        {{if $_operation->libelle}}
          <div class="compact">
              {{$_operation->libelle}}
          </div>
        {{/if}}
      </td>
      <td>
        {{if !$_operation->_ext_codes_ccam}}
          <div class="empty">Aucun prévu</div>
        {{else}}
          {{$_operation->_actes_non_cotes}} acte(s)
        {{/if}}
      </td>
      <td class="text">
        {{foreach from=$_operation->_ext_codes_ccam item=_code}}
          <div>
            {{$_code->code}}
          </div>
        {{/foreach}}
      </td>

      <td>
        {{foreach from=$_operation->_ref_actes_ccam item=_act}}
          {{if !$_act->_ref_executant->_is_anesth}}
            <div class="">
              {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_act->_ref_executant initials=border}}
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_act->_guid}}')">
                {{$_act->code_acte}}-{{$_act->code_activite}}-{{$_act->code_phase}}
                {{if $_act->modificateurs}}
                  MD:{{$_act->modificateurs}}
                {{/if}}
                {{if $_act->montant_depassement}}
                  DH:{{$_act->montant_depassement|currency}}
                {{/if}}
              </span>
            </div>
          {{/if}}
        {{/foreach}}
      </td>

      <td>
        {{foreach from=$_operation->_ref_actes_ccam item=_act}}
          {{if $_act->_ref_executant->_is_anesth}}
            <div class="">
              {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_act->_ref_executant initials=border}}
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_act->_guid}}')">
                {{$_act->code_acte}}-{{$_act->code_activite}}-{{$_act->code_phase}}
                {{if $_act->modificateurs}}
                  MD:{{$_act->modificateurs}}
                {{/if}}
                {{if $_act->montant_depassement}}
                  DH:{{$_act->montant_depassement|currency}}
                {{/if}}
              </span>
            </div>
          {{/if}}
        {{/foreach}}
      </td>

      <td>
        {{foreach from=$_operation->_ref_actes_ccam item=_act}}
          {{if !$_act->sent}}
            <div>
              {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_act->_ref_executant initials=border}}
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_act->_guid}}')">
                {{$_act->code_acte}}-{{$_act->code_activite}}-{{$_act->code_phase}}
              </span>
            </div>
          {{/if}}
        {{/foreach}}
      </td>
      <td>
        {{if $_operation->_ref_actes_ccam|@count}}
          <input type="checkbox" class="select_operations" name="export-{{$_operation->_guid}}" data-guid="{{$_operation->_guid}}">
          <button type="button" title="Exporter les actes de l'intervention" onclick="exportActs('{{$_operation->_guid}}')">
            <i class="fa fa-share" style="color: #142328"></i>
          </button>
        {{/if}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="7" class="empty">{{tr}}COperation.none_non_cotee{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>