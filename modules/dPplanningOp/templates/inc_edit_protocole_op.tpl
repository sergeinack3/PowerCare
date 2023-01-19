{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=print value=0}}

{{if !$print}}
  <script>
    Main.add(function() {
      ProtocoleOp.makeAutocompletes(getForm('editProtocoleOp'));
    });
  </script>
{{/if}}

<form name="editProtocoleOp" class="{{$protocole_op->_spec}}" method="post" onsubmit="return ProtocoleOp.onSubmit(this);">
  {{mb_class object=$protocole_op}}
  {{mb_key   object=$protocole_op}}

  {{mb_field object=$protocole_op field=validation_praticien_id hidden=true}}
  {{mb_field object=$protocole_op field=validation_praticien_datetime hidden=true}}
  {{mb_field object=$protocole_op field=validation_cadre_bloc_id hidden=true}}
  {{mb_field object=$protocole_op field=validation_cadre_bloc_datetime hidden=true}}

  <table class="form">
    {{if $print}}
      <tr>
        <th class="title modify" colspan="6">
          {{$protocole_op->_view}}
        </th>
      </tr>
    {{else}}
      {{mb_include module=system template=inc_form_table_header object=$protocole_op colspan=6}}
    {{/if}}

    <tr>
      {{if $print}}
        <th>
          {{mb_label object=$protocole_op field=libelle}}
        </th>
        <td>
          {{mb_value object=$protocole_op field=libelle}}
        </td>
      {{else}}
        {{me_form_field mb_object=$protocole_op mb_field=libelle nb_cells=2}}
          {{mb_field object=$protocole_op field=libelle}}
        {{/me_form_field}}
      {{/if}}

      {{if $print}}
        <th>
          {{mb_label object=$protocole_op field=code}}
        </th>
        <td>
          {{mb_value object=$protocole_op field=code}}
        </td>
      {{else}}
        {{me_form_field mb_object=$protocole_op mb_field=code nb_cells=2}}
          {{mb_field object=$protocole_op field=code}}
        {{/me_form_field}}
      {{/if}}

      {{if $print}}
        <th>
          {{mb_label object=$protocole_op field=numero_version}}
        </th>
        <td>
          {{mb_value object=$protocole_op field=numero_version}}
        </td>
      {{else}}
        {{me_form_field mb_object=$protocole_op mb_field=numero_version nb_cells=2}}
          {{mb_field object=$protocole_op field=numero_version}}
        {{/me_form_field}}
      {{/if}}
    </tr>

    <tr>
      {{if $print}}
        <th>
          {{mb_label object=$protocole_op field=chir_id}}
        </th>
        <td>
          {{mb_value object=$protocole_op field=chir_id}}
        </td>
      {{else}}
        {{me_form_field mb_object=$protocole_op mb_field=chir_id nb_cells=2}}
          {{mb_field object=$protocole_op field=chir_id hidden=true
               onchange="
                         \$V(this.form.function_id, '', false);
                         \$V(this.form.function_id_view, '', false);
                         \$V(this.form.group_id, '', false);
                         \$V(this.form.group_id_view, '', false);"}}
          <input type="text" name="chir_id_view" value="{{$protocole_op->_ref_chir->_view}}" />
        {{/me_form_field}}
      {{/if}}

      {{if $print}}
        <th>
          {{mb_label object=$protocole_op field=function_id}}
        </th>
        <td>
          {{mb_value object=$protocole_op field=function_id}}
        </td>
      {{else}}
        {{me_form_field mb_object=$protocole_op mb_field=function_id nb_cells=2}}
          {{mb_field object=$protocole_op field=function_id hidden=true
               onchange="
                    \$V(this.form.chir_id, '', false);
                    \$V(this.form.chir_id_view, '', false);
                    \$V(this.form.group_id, '', false);
                    \$V(this.form.group_id_view, '', false);"}}
            <input type="text" name="function_id_view" value="{{$protocole_op->_ref_function->_view}}" />
        {{/me_form_field}}
      {{/if}}
    </tr>

    {{if !$print}}
      {{me_form_bool mb_object=$protocole_op mb_field=actif nb_cells=6}}
        {{mb_field object=$protocole_op field=actif}}
      {{/me_form_bool}}
    {{/if}}

    <tr>
      {{if $print}}
        <th>
          {{mb_label object=$protocole_op field=remarque}}
        </th>
        <td colspan="5">
          {{mb_value object=$protocole_op field=remarque}}
        </td>
      {{else}}
        {{me_form_field mb_object=$protocole_op mb_field=remarque nb_cells=6 field_class=me-form-group_fullw}}
          {{mb_field object=$protocole_op field=remarque}}
        {{/me_form_field}}
      {{/if}}
    </tr>

    <tr>
      {{if $print}}
        <th>
          {{mb_label object=$protocole_op field=description_equipement_salle}}
        </th>
      {{/if}}

      <td class="me-valign-top" colspan="{{if $print}}2{{else}}3{{/if}}">
        {{if $print}}
          {{mb_value object=$protocole_op field=description_equipement_salle}}
        {{else}}
          {{me_form_field mb_object=$protocole_op mb_field=description_equipement_salle field_class=me-form-group_fullw}}
            {{mb_field object=$protocole_op field=description_equipement_salle}}
          {{/me_form_field}}
        {{/if}}
      </td>

      {{if $print}}
        <th>
          {{mb_label object=$protocole_op field=description_installation_patient}}
        </th>
      {{/if}}

      <td class="me-valign-top" colspan="{{if $print}}2{{else}}3{{/if}}">
        {{if $print}}
          {{mb_value object=$protocole_op field=description_installation_patient}}
        {{else}}
          {{me_form_field mb_object=$protocole_op mb_field=description_installation_patient field_class=me-form-group_fullw}}
            {{mb_field object=$protocole_op field=description_installation_patient}}
          {{/me_form_field}}
        {{/if}}
      </td>
    </tr>
    <tr>
      {{if $print}}
        <th>
          {{mb_label object=$protocole_op field=description_preparation_patient}}
        </th>
      {{/if}}

      <td class="me-valign-top" colspan="{{if $print}}2{{else}}3{{/if}}">
        {{if $print}}
          {{mb_value object=$protocole_op field=description_preparation_patient}}
        {{else}}
          {{me_form_field mb_object=$protocole_op mb_field=description_preparation_patient field_class=me-form-group_fullw}}
            {{mb_field object=$protocole_op field=description_preparation_patient}}
          {{/me_form_field}}
        {{/if}}
      </td>

      {{if $print}}
        <th>
          {{mb_label object=$protocole_op field=description_instrumentation}}
        </th>
      {{/if}}

      <td class="me-valign-top" colspan="{{if $print}}2{{else}}3{{/if}}">
        {{if $print}}
          {{mb_value object=$protocole_op field=description_instrumentation}}
        {{else}}
          {{me_form_field mb_object=$protocole_op mb_field=description_instrumentation field_class=me-form-group_fullw}}
            {{mb_field object=$protocole_op field=description_instrumentation}}
          {{/me_form_field}}
        {{/if}}
      </td>
    </tr>

    {{if !$print}}
      <tr>
        <td colspan="6" class="button">
          <button class="save">{{tr}}Save{{/tr}}</button>
          {{if $protocole_op->_id}}
            <button type="button" class="duplicate" onclick="ProtocoleOp.duplicateProt(this.form);">
              {{tr}}Duplicate{{/tr}}
            </button>

            <button type="button" class="trash"
                    onclick="confirmDeletion(this.form,
                              {typeName: $T('CProtocoleOperatoire'), objName: '{{$protocole_op->libelle|smarty:nodefaults|JSAttribute}}'}, Control.Modal.close)">
              {{tr}}Delete{{/tr}}
            </button>

            {{if $app->_ref_user->isPraticien()}}
              {{if $protocole_op->validation_praticien_id}}
                <button type="button" class="cancel" onclick="ProtocoleOp.invaliderPrat();">
                  {{tr}}CProtocoleOperatoire-Invalidation prat{{/tr}}
                </button>
              {{else}}
                <button type="button" class="tick" onclick="ProtocoleOp.validerPrat();">
                  {{tr}}CProtocoleOperatoire-Validation prat{{/tr}}
                </button>
              {{/if}}
            {{/if}}

            {{if $app->_ref_user->isSurveillantBloc()}}
              {{if $protocole_op->validation_cadre_bloc_id}}
                <button type="button" class="cancel" onclick="ProtocoleOp.invalideCadreBloc();">
                  {{tr}}CProtocoleOperatoire-Invalidation cadre bloc{{/tr}}
                </button>
              {{else}}
                <button type="button" class="tick" onclick="ProtocoleOp.valideCadreBloc();">
                  {{tr}}CProtocoleOperatoire-Validation cadre bloc{{/tr}}
                </button>
              {{/if}}
            {{/if}}
          {{/if}}
        </td>
      </tr>
    {{/if}}
  </table>
</form>

{{if $protocole_op->_id}}
  <div id="materiel_operatoire_area">
    {{mb_include module=planningOp template=inc_list_materiels_op}}
  </div>
{{/if}}
