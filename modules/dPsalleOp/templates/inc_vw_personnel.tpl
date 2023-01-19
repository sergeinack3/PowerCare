{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  submitPersonnel = function(oForm) {
    return onSubmitFormAjax(oForm, SalleOp.reloadPersonnel.curry(oForm.object_id.value));
  }
</script>

{{if $app->_ref_user->isPraticien() && !$app->user_prefs.chir_modif_timing}}
  {{assign var=modif_operation value=false}}
{{/if}}

<table class="form">
  <tr>
    <th class="title" {{if $in_salle}}colspan="2"{{/if}}>
      {{tr}}CPersonnel-Staff in the room{{/tr}}
    </th>
  </tr>
  <tr>
    {{if $in_salle}}
      <th class="category" style="width: 50%;">{{tr}}CPersonnel-Planned staff{{/tr}}</th>
    {{/if}}
    <th class="category" style="width: 50%;">
      {{tr}}CPersonnel-Added staff{{/tr}}
      <br />

      {{if $modif_operation}}
        {{mb_include module=salleOp template=inc_vw_personnel_form emplacement=iade}}

        {{mb_include module=salleOp template=inc_vw_personnel_form emplacement=op}}

        {{mb_include module=salleOp template=inc_vw_personnel_form emplacement=instrumentiste}}

        {{mb_include module=salleOp template=inc_vw_personnel_form emplacement=op_panseuse}}

        {{if !$selOp->_ref_sejour->grossesse_id}}
          <br />
        {{/if}}

        {{mb_include module=salleOp template=inc_vw_personnel_form emplacement=manipulateur}}

        {{if $selOp->_ref_sejour->grossesse_id}}
          <br />
        {{/if}}

        {{mb_include module=salleOp template=inc_vw_personnel_form emplacement=circulante}}

        {{mb_include module=salleOp template=inc_vw_personnel_form emplacement=brancardier}}

        {{mb_include module=salleOp template=inc_vw_personnel_form emplacement=aide_soignant}}

        {{if $selOp->_ref_sejour->grossesse_id}}
          {{mb_include module=salleOp template=inc_vw_personnel_form emplacement=sagefemme}}
          {{mb_include module=salleOp template=inc_vw_personnel_form emplacement=aux_puericulture}}
        {{/if}}
      {{/if}}
    </th>
  </tr>

  {{assign var=submit value=submitPersonnel}}

  <tr>
    {{if $in_salle}}
    <!-- Personnel prévu dans la plage op -->
    <td>
      {{foreach from=$tabPersonnel.plage item=affectation}}
        {{assign var="affectation_id" value=$affectation->_id}}
        {{assign var=personnel_id value=$affectation->_ref_personnel->_id}}
        {{assign var="form" value="affectationPersonnel-$personnel_id"}}

        <form name="{{$form}}" action="?m={{$m}}" method="post">
        <input type="hidden" name="m" value="personnel" />
        <input type="hidden" name="dosql" value="do_affectation_aed" />
        <input type="hidden" name="del" value="0" />
        {{mb_key object=$affectation}}
        {{mb_field object=$affectation field=personnel_id hidden=1}}
        {{mb_field object=$affectation field=object_class hidden=1}}
        {{mb_field object=$affectation field=object_id hidden=1}}
        {{mb_field object=$affectation field=parent_affectation_id hidden=1}}
        {{mb_field object=$affectation field=realise value="0" hidden=1}}
        <table class="form me-small-form">
          <tr>
            <td style="vertical-align: middle;{{if $in_salle}} width: 30%;{{/if}}" class="text">
              {{if $affectation->_id}}
                {{mb_include module=system template=inc_object_history object=$affectation}}
              {{/if}}
              {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$affectation->_ref_personnel->_ref_user}}
            <br />
            <span class="opacity-60">{{tr}}CPersonnel.emplacement.{{$affectation->_ref_personnel->emplacement}}{{/tr}}</span>
            </td>
            <td>
              {{if $selOp->date == $dnow}}
                {{mb_include module=dPsalleOp template=inc_field_timing object=$affectation field=_debut}}
                {{mb_include module=dPsalleOp template=inc_field_timing object=$affectation field=_fin disabled=$affectation->_debut|default:'yes'}}
              {{else}}
                {{mb_include module=dPsalleOp template=inc_field_timing object=$affectation field=_debut_dt}}
                {{mb_include module=dPsalleOp template=inc_field_timing object=$affectation field=_fin_dt disabled=$affectation->_debut|default:'yes'}}
              {{/if}}
            </td>
            <td style="width: 25px; vertical-align: middle;">
              {{if $affectation->_id}}
                <button type="button" class="trash notext me-tertiary" onclick="$V(this.form.del, 1); submitPersonnel(this.form);">
                  {{tr}}Delete{{/tr}}
                </button>
              {{/if}}
            </td>
          </tr>
        </table>
       </form>
       <hr style="margin-top: 0" />
     {{foreachelse}}
       <div class="small-info">Aucun personnel prévu</div>
     {{/foreach}}

      <form name="visitors" method="post">
        {{mb_key   object=$selOp}}
        {{mb_class object=$selOp}}
        {{mb_label object=$selOp field=visitors}}
        {{if $modif_operation}}
          {{mb_field object=$selOp field=visitors onchange="return onSubmitFormAjax(this.form);" form=visitors aidesaisie="validateOnBlur: 0, width: '100%'"}}
        {{else}}
          {{mb_value object=$selOp field=visitors}}
        {{/if}}
      </form>
    </td>
    {{/if}}
    <!-- Personnel ajouté pour l'intervention -->
    <td>
      {{foreach from=$tabPersonnel.operation item=affectation}}
      <form name="affectationPersonnel-{{$affectation->_id}}" action="?m={{$m}}" method="post">
        <input type="hidden" name="m" value="personnel" />
        <input type="hidden" name="dosql" value="do_affectation_aed" />
        <input type="hidden" name="del" value="0" />
        {{mb_key object=$affectation}}
        <input type="hidden" name="personnel_id" value="{{$affectation->_ref_personnel->_id}}" />
        <input type="hidden" name="object_class" value="COperation" />
        <input type="hidden" name="object_id" value="{{$selOp->_id}}" />
        <input type="hidden" name="realise" value="0" />

        {{assign var="affectation_id" value=$affectation->_id}}
        {{assign var="form" value="affectationPersonnel-$affectation_id"}}

        <table class="form me-small-form">
          <tr>
            <td  style="vertical-align: middle;{{if $in_salle}} width: 30%;{{/if}}" class="text">
              {{mb_include module=system template=inc_object_history object=$affectation}}
              {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$affectation->_ref_personnel->_ref_user}}
              <br />
              <span class="opacity-60">
                {{tr}}CPersonnel.emplacement.{{$affectation->_ref_personnel->emplacement}}{{/tr}}
              </span>

            </td>
            <td>
              {{if $in_salle}}
                {{if $selOp->date == $dnow}}
                  {{mb_include module=dPsalleOp template=inc_field_timing object=$affectation field=_debut}}
                  {{mb_include module=dPsalleOp template=inc_field_timing object=$affectation field=_fin disabled=$affectation->_debut|default:'yes'}}
                {{else}}
                  {{mb_include module=dPsalleOp template=inc_field_timing object=$affectation field=_debut_dt}}
                  {{mb_include module=dPsalleOp template=inc_field_timing object=$affectation field=_fin_dt disabled=$affectation->_debut|default:'yes'}}
                {{/if}}
              {{/if}}
            </td>
            <td {{if !$in_salle}}class="narrow"{{/if}} style="width: 25px; vertical-align: middle;">
              {{if $modif_operation}}
                <button type="button" class="trash notext me-tertiary" onclick="$V(this.form.del, '1'); submitPersonnel(this.form);">{{tr}}Delete{{/tr}}</button>
              {{/if}}
            </td>
          </tr>
        </table>
      </form>

      <hr style="margin-top: 0" class="me-no-display"/>
      {{foreachelse}}
      <div class="small-info">Aucun personnel ajouté</div>
      {{/foreach}}


      <form name="rques_personnel" method="post">
        {{mb_key   object=$selOp}}
        {{mb_class object=$selOp}}
        {{mb_label object=$selOp field=rques_personnel}}
        {{if $modif_operation}}
          {{mb_field object=$selOp field=rques_personnel onchange="return onSubmitFormAjax(this.form);"}}
        {{else}}
          {{mb_value object=$selOp field=rques_personnel}}
        {{/if}}
      </form>
    </td>
  </tr>
</table>
