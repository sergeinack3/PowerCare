{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=preparation_salle ajax=$ajax}}

<script>
  Main.add(function() {
    PreparationSalle.form = getForm('filterOps');
    PreparationSalle.numero_panier_mandatory = {{if "dPsalleOp COperation numero_panier_mandatory"|gconf}}1{{else}}0{{/if}};
    PreparationSalle.makeAutocompletes();
    PreparationSalle.refreshList();
  });
</script>

<div>
  <form name="filterOps" method="get" onsubmit="return PreparationSalle.refreshList();">
    <input type="hidden" name="m" value="planningOp" />
    <input type="hidden" name="a" value="ajax_list_ops" />

    {{mb_key object=$operation}}
    {{mb_field object=$operation field=_prepa_order_col hidden=true}}
    {{mb_field object=$operation field=_prepa_order_way hidden=true}}

    <table class="form">
      <tr>
        {{me_form_field mb_object=$operation mb_field=_prepa_dt_min nb_cells=2 class=narrow}}
          {{mb_field object=$operation field=_prepa_dt_min form=filterOps register=true}}
        {{/me_form_field}}

        {{me_form_field mb_object=$operation mb_field=_prepa_chir_id nb_cells=2}}
          {{mb_field object=$operation field=_prepa_chir_id hidden=true}}
          <input type="text" name="_prepa_chir_id_view" placeholder="{{tr}}CMediusers-select-praticien{{/tr}}"
                 value="{{$operation->_ref_prepa_chir->_view}}" />

          <button type="button" class="erase notext" onclick="$V(this.form._prepa_chir_id, ''); $V(this.form._prepa_chir_id_view, '');">
            {{tr}}Erase{{/tr}}
          </button>
        {{/me_form_field}}

        {{me_form_field mb_object=$operation mb_field=_prepa_bloc_id nb_cells=2}}
          <select name="_prepa_bloc_id" onchange="$V(this.form._prepa_salle_id, '', false);">
            <option value="">&mdash; {{tr}}CBlocOperatoire.select{{/tr}}</option>
            {{foreach from=$blocs item=_bloc}}
              <option value="{{$_bloc->_id}}" {{if $operation->_prepa_bloc_id == $_bloc->_id}}selected{{/if}}>
                {{$_bloc->_view}}
              </option>
            {{/foreach}}
          </select>
        {{/me_form_field}}

        {{me_form_field mb_object=$operation mb_field=_prepa_libelle nb_cells=2}}
          {{mb_field object=$operation field=_prepa_libelle}}
        {{/me_form_field}}
      </tr>
      <tr>
        {{me_form_field mb_object=$operation mb_field=_prepa_dt_max nb_cells=2 class=narrow}}
          {{mb_field object=$operation field=_prepa_dt_max form=filterOps register=true}}
        {{/me_form_field}}

        {{me_form_field mb_object=$operation mb_field=_prepa_spec_id nb_cells=2}}
          {{mb_field object=$operation field=_prepa_spec_id hidden=true}}
          <input type="text" name="_prepa_spec_id_view" placeholder="{{tr}}CMediusers-select-cabinet{{/tr}}"
                 value="{{$operation->_ref_prepa_spec->_view}}" />
          <button type="button" class="erase notext" onclick="$V(this.form._prepa_spec_id, ''); $V(this.form._prepa_spec_id_view, '');">
            {{tr}}Erase{{/tr}}
          </button>
        {{/me_form_field}}

        {{me_form_field mb_object=$operation mb_field=_prepa_salle_id nb_cells=2}}
          <select name="_prepa_salle_id" onchange="$V(this.form._prepa_bloc_id, '', false);">
            <option value="">&mdash; {{tr}}CSalle.select{{/tr}}</option>
            {{foreach from=$blocs item=_bloc}}
              <optgroup label="{{$_bloc->_view}}">
                {{foreach from=$_bloc->_ref_salles item=_salle}}
                  <option value="{{$_salle->_id}}" {{if $_salle->_id == $operation->_prepa_salle_id}}selected{{/if}}>
                    {{$_salle->_view}}
                  </option>
                {{/foreach}}
              </optgroup>
            {{/foreach}}
          </select>
        {{/me_form_field}}

        {{me_form_field mb_object=$operation mb_field=_prepa_libelle_prot nb_cells=2}}
          {{mb_field object=$operation field=_prepa_libelle_prot}}
        {{/me_form_field}}
      </tr>
      <tr>
        <td colspan="2"></td>

        {{me_form_field mb_object=$operation mb_field=_prepa_type_intervention nb_cells=2}}
          {{mb_field object=$operation field=_prepa_type_intervention}}
        {{/me_form_field}}

        {{me_form_bool mb_object=$operation mb_field=_prepa_urgence nb_cells=2}}
        {{mb_field object=$operation field=_prepa_urgence typeEnum=checkbox}}
        {{/me_form_bool}}

        {{me_form_field mb_object=$operation mb_field=_filter_panier nb_cells=2}}
          {{mb_field object=$operation field=_filter_panier}}
        {{/me_form_field}}
      </tr>

      <tr>
        <td class="button" colspan="8">
          <button class="search">{{tr}}Filter{{/tr}}</button>

          <button type="button" class="tick" onclick="PreparationSalle.validateSelection();">
            {{tr}}CMaterielOperatoire-Validate selection{{/tr}}
          </button>
        </td>
      </tr>
    </table>
  </form>
</div>

<div id="ops_materiel"></div>