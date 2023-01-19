{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=use_charge_price_indicator       value="dPplanningOp CSejour use_charge_price_indicator"|gconf}}
{{assign var=provenance_domicile_pec_non_org  value="dPurgences CRPU provenance_domicile_pec_non_org"|gconf}}
{{assign var=required_from_when_transfert     value="dPurgences CRPU required_from_when_transfert"|gconf}}

<script>
  ProtocoleRPU.contraintesProvenance  = {{$contrainteProvenance|@json}};
  ProtocoleRPU.contraintesDestination = {{$contrainteDestination|@json}};

  updateModeEntree = function(select) {
    var selected = select.options[select.selectedIndex];
    var form = select.form;
    $V(form.mode_entree, selected.get("mode"));
    $V(form.provenance, selected.get("provenance"));
  };

  changeModeEntree = function(mode_entree) {
    loadTransfert(mode_entree);
  };

  loadTransfert = function(mode_entree) {
    {{if $required_from_when_transfert }}
    var oform = getForm('editProtocoleRPU');
    var provenance = $(oform.provenance);
    if (mode_entree == 7) {
      provenance.addClassName('notNull');
      $('labelFor_editProtocoleRPU_provenance').addClassName('notNull');
    }
    else {
      provenance.removeClassName('notNull');
      $('labelFor_editProtocoleRPU_provenance').removeClassName('notNull');
      $('labelFor_editProtocoleRPU_provenance').removeClassName('error');
    }
    {{/if}}
  };
  changeProvenanceWithEntree = function(entree) {
    {{if $provenance_domicile_pec_non_org }}
    if (entree.value === "8") {
      $V(entree.form.elements.provenance, "5");
    }
    {{/if}}
  };
</script>

<form name="editProtocoleRPU" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_class object=$protocole_rpu}}
  {{mb_key   object=$protocole_rpu}}

  {{mb_field object=$protocole_rpu field=group_id hidden=1}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$protocole_rpu}}

    <tr>
      <th class="halfPane">
        {{mb_label object=$protocole_rpu field=libelle}}
      </th>
      <td>
        {{mb_field object=$protocole_rpu field=libelle}}
      </td>
    </tr>

    <tr>
      <th>
        {{mb_label object=$protocole_rpu field=actif typeEnum=checkbox}}
      </th>
      <td>
        {{mb_field object=$protocole_rpu field=actif typeEnum=checkbox}}
      </td>
    </tr>

    <tr>
      <th>
        {{mb_label object=$protocole_rpu field=default typeEnum=checkbox}}
      </th>
      <td>
        {{mb_field object=$protocole_rpu field=default typeEnum=checkbox}}
      </td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        {{mb_include module=files template=inc_button_add_docitems context=$protocole_rpu}}
      </td>
    </tr>

    <tr>
      <th class="category" colspan="2">
        {{tr}}CProtocoleRPU.infos_rpu{{/tr}}
      </th>
    </tr>

    <tr>
      <td>
        <fieldset>
          <legend>{{tr}}CRPU-pec_adm{{/tr}}</legend>

          <table class="form me-no-align me-no-box-shadow">
            {{if $list_mode_entree|@count}}
              <th>
                {{mb_label object=$protocole_rpu field=mode_entree_id}}
              </th>
              <td>
                {{mb_field object=$protocole_rpu field=mode_entree hidden=true
                 onchange="ProtocoleRPU.updateProvenance(this.value, true); changeModeEntree(this.value); changeProvenanceWithEntree(this);"}}
                <select name="mode_entree_id" style="width: 20em;" onchange="updateModeEntree(this);">
                  <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                  {{foreach from=$list_mode_entree item=_mode}}
                    <option value="{{$_mode->_id}}" data-mode="{{$_mode->mode}}" data-provenance="{{$_mode->provenance}}"
                            {{if $protocole_rpu->mode_entree_id == $_mode->_id}}selected{{/if}}>
                      {{$_mode}}
                    </option>
                  {{/foreach}}
                </select>
              </td>
            {{else}}
              <th>
                {{mb_label object=$protocole_rpu field=mode_entree}}
              </th>
              <td>
                {{mb_field object=$protocole_rpu field=mode_entree style="width: 20em;" emptyLabel="Choose"
                onchange="ProtocoleRPU.updateProvenance(this.value, true);changeModeEntree(this.value);changeProvenanceWithEntree(this);"}}
              </td>
            {{/if}}
            <tr>
              <th style="width: 10em;">
                {{mb_label object=$protocole_rpu field=transport}}
              </th>
              <td>
                {{mb_field object=$protocole_rpu field=transport style="width: 20em;" emptyLabel="Choose"}}
              </td>
            </tr>
            <tr>
              <th style="width: 10em;">
                {{mb_label object=$protocole_rpu field=provenance}}
              </th>
              <td>
                {{mb_field object=$protocole_rpu field=provenance style="width: 20em;" emptyLabel="Choose"}}
              </td>
            </tr>
            <tr>
              <th style="width: 10em;">
                {{mb_label object=$protocole_rpu field=pec_transport}}
              </th>
              <td>
                {{mb_field object=$protocole_rpu field=pec_transport style="width: 20em;" emptyLabel="Choose"}}
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
      <td>
        <fieldset>
          <legend>
            {{tr}}CRPU.pecMed{{/tr}}
          </legend>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <th style="width: 10em;">
                {{mb_label object=$protocole_rpu field=responsable_id}}
              </th>
              <td>
                <select name="responsable_id" style="width: 20em;">
                  <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                  {{mb_include module=mediusers template=inc_options_mediuser selected=$protocole_rpu->responsable_id list=$urgentistes}}
                </select>
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
    </tr>
    <tr>
      <td>
        <fieldset>
          <legend>{{tr}}CRPU-Geolocalisation{{/tr}}</legend>
          <table class="form me-no-align me-no-box-shadow">
            <tr>
              <th style="width: 10em;">
                {{mb_label object=$protocole_rpu field=uf_soins_id}}
              </th>
              <td>
                <select name="uf_soins_id" style="width: 20em;">
                  <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                  {{foreach from=$ufs.soins item=_uf}}
                    <option value="{{$_uf->_id}}" {{if $protocole_rpu->uf_soins_id == $_uf->_id}}selected{{/if}}>
                      {{mb_value object=$_uf field=libelle}}
                    </option>
                  {{/foreach}}
                </select>
              </td>
            </tr>
            {{if $use_charge_price_indicator != "no"}}
              <tr>
                <th>
                  {{mb_label object=$protocole_rpu field=charge_id}}
                </th>
                <td>
                  <select name="charge_id" style="width: 20em;">
                    <option value=""> &mdash; {{tr}}Choose{{/tr}}</option>

                    {{foreach from='Ox\Mediboard\PlanningOp\CChargePriceIndicator::getList'|static_call:null item=_cpi}}
                      {{if in_array($_cpi->type, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:null) || ($protocole_rpu->_charge_id == $_cpi->_id)}}
                      <option value="{{$_cpi->_id}}" {{if $protocole_rpu->charge_id == $_cpi->_id}}selected{{/if}}>
                        {{$_cpi|truncate:50:"...":false}}
                      </option>
                      {{/if}}
                    {{/foreach}}
                  </select>
                </td>
              </tr>
            {{/if}}
            <tr>
              <th>
                {{mb_label object=$protocole_rpu field=box_id}}
              </th>
              <td>
                {{mb_include module=hospi template=inc_select_lit field=box_id selected_id=$protocole_rpu->box_id listService=$services width_select="20em;"}}
              </td>
            </tr>
          </table>
        </fieldset>
      </td>
      <td></td>
    </tr>

    {{assign var=libelle_protocole value=$protocole_rpu->libelle|smarty:nodefaults|JSAttribute}}
    {{mb_include module=system template=inc_form_table_footer object=$protocole_rpu
                 options="{typeName: 'le protocole RPU', objName: '$libelle_protocole'}"
                 options_ajax="Control.Modal.close"}}
  </table>
</form>
