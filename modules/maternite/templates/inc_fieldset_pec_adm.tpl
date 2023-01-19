{{*
 * @package Mediboard\
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=use_cpi value="dPplanningOp CSejour use_charge_price_indicator"|gconf}}
{{assign var=required_uf_med value="dPplanningOp CSejour required_uf_med"|gconf}}

<script>
  Main.add(() => {
    {{if $required_uf_med === "obl" && "dPplanningOp CSejour only_ufm_first_second"|gconf}}
      Placement.lock_uf_med = true;
    {{/if}}

    Placement.preselectUf();
  });
</script>

<fieldset>
  <legend>{{tr}}CConsultation-Pec adm{{/tr}}</legend>

  <table class="form me-no-box-shadow">
    <tr>
      {{me_form_field mb_object=$consult mb_field=_prat_id label=CConsultation-_prat_id_sf nb_cells=2}}
        {{mb_field object=$consult field=_prat_id hidden=true onchange="Placement.preselectUf();"}}
        <input type="text" name="_prat_id_view" value="{{$consult->_ref_chir->_view}}" placeholder="{{tr}}CMediusers-praticien{{/tr}}" />
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field mb_object=$consult mb_field=_datetime nb_cells=2}}
        {{mb_field object=$consult field=_datetime form=pecPatiente register=true}}
      {{/me_form_field}}
    </tr>

    <tr class="sejour_part" {{if !$show_sejour}}style="display: none;"{{/if}}>
      {{assign var=field_mode_entree value=mode_entree}}

      {{if $modes_entree|@count}}
        {{assign var=field_mode_entree value=mode_entree_id}}
      {{/if}}

      {{me_form_field mb_object=$sejour mb_field=$field_mode_entree nb_cells=2}}
        {{if $modes_entree|@count}}
          <select name="mode_entree_id">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{foreach from=$modes_entree item=_mode_entree}}
              <option value="{{$_mode_entree->_id}}" {{if $sejour->mode_entree_id == $_mode_entree->_id}}selected{{/if}}>{{$_mode_entree->_view}}</option>
            {{/foreach}}
          </select>
        {{else}}
          {{mb_field object=$sejour field=mode_entree emptyLabel="Choose"}}
        {{/if}}
      {{/me_form_field}}
    </tr>

    {{if $use_cpi != "no"}}
      <tr class="sejour_part" {{if !$show_sejour}}style="display: none;"{{/if}}>
        {{me_form_field mb_object=$consult mb_field=_charge_id nb_cells=2 title_label="CSejour-_charge_id"}}

          <select name="_charge_id" class="ref {{if $use_cpi == "obl"}}notNull{{/if}}">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{foreach from='Ox\Mediboard\PlanningOp\CChargePriceIndicator::getList'|static_call:'consult' item=_cpi name=charge}}
              <option value="{{$_cpi->_id}}" {{if $smarty.foreach.charge.first}}selected{{/if}}>
                {{$_cpi|truncate:50:"...":false}}
              </option>
            {{/foreach}}
          </select>
        {{/me_form_field}}
      </tr>
    {{/if}}

    {{if $required_uf_med !== "no"}}
      <!-- Selection de l'unité de soins -->
      <tr class="sejour_part" {{if !$show_sejour}}style="display: none;"{{/if}}>
        {{me_form_field mb_object=$consult mb_field=_uf_medicale_id nb_cells=2}}
        {{assign var=ufs value='Ox\Mediboard\Hospi\CUniteFonctionnelle::getUFs'|static_call:$sejour}}
          <select name="_uf_medicale_id" class="ref {{if $required_uf_med === "obl"}}notNull{{/if}}">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{foreach from=$ufs.medicale item=_uf}}
              <option value="{{$_uf->_id}}">
                {{mb_value object=$_uf field=libelle}}
              </option>
            {{/foreach}}
          </select>
        {{/me_form_field}}
      </tr>
    {{/if}}

    <tr class="suivi_part" style="display: none;">
      {{me_form_field mb_object=$consult->_ref_suivi_grossesse mb_field=type_suivi nb_cells=2}}
        {{mb_field object=$consult->_ref_suivi_grossesse field=type_suivi}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_field mb_object=$consult mb_field=motif nb_cells=2}}
        {{mb_field object=$consult field=motif}}
      {{/me_form_field}}
    </tr>
  </table>
</fieldset>
