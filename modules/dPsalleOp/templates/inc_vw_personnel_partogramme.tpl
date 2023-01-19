{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=print value=false}}

<td colspan="4">
  <fieldset class="me-small me-no-align">
    <legend>{{tr}}CAnesthPerop-legend-Staff room{{/tr}}</legend>
    <table class="form me-no-box-shadow me-compact me-margin-2">
      <tr>
        <th class="category" colspan="2">{{tr}}CPersonnel.emplacement.sagefemme{{/tr}}</th>
        <th class="category" colspan="2">{{tr}}CPersonnel.emplacement.aux_puericulture{{/tr}}</th>
        <th class="category" colspan="2">{{tr}}CPersonnel.emplacement.aide_soignant{{/tr}}</th>
        <th class="category">{{tr}}COperation-visitors{{/tr}}</th>
      </tr>
      <tr>
        <td style="text-align: center; width: 10%;" class="me-text-align-right">
          {{mb_include module=salleOp template=inc_vw_personnel_form emplacement=sagefemme}}
        </td>
        <td style="width: 10%;">
          {{foreach from=$affectations_operation item=affectation}}
            {{if $affectation->_ref_personnel->emplacement == "sagefemme"}}
              {{assign var="affectation_id" value=$affectation->_id}}
              <div>
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$affectation->_ref_personnel->_ref_user}}

                <form name="delete_personnel_{{$affectation->_guid}}" method="post" onsubmit="return onSubmitFormAjax(this);">
                  {{mb_key   object=$affectation}}
                  {{mb_class object=$affectation}}

                  <button type="button" class="trash notext" style="margin-left: 10px;" onclick="SalleOp.deletePersonnelPartogramme(
                    this.form,{typeName:'', objName:'{{$affectation->_ref_personnel->_ref_user->_view|smarty:nodefaults|JSAttribute}}', ajax: true}, '{{$selOp->_id}}'
                    );">
                    {{tr}}Delete{{/tr}}
                  </button>
                </form>
                <br />
                <span class="opacity-60">
                        {{tr}}CPersonnel.emplacement.{{$affectation->_ref_personnel->emplacement}}{{/tr}}
                </span>
              </div>
            {{/if}}
          {{/foreach}}
        </td>
        <td style="text-align: center; width: 10%;" class="me-text-align-right">
          {{mb_include module=salleOp template=inc_vw_personnel_form emplacement=aux_puericulture}}
        </td>
        <td style="width: 10%;">
          {{foreach from=$affectations_operation item=affectation}}
            {{if $affectation->_ref_personnel->emplacement == "aux_puericulture"}}
              {{assign var="affectation_id" value=$affectation->_id}}
              <div>
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$affectation->_ref_personnel->_ref_user}}

                <form name="delete_personnel_{{$affectation->_guid}}" method="post" onsubmit="return onSubmitFormAjax(this);">
                  {{mb_key   object=$affectation}}
                  {{mb_class object=$affectation}}

                  <button type="button" class="trash notext" style="margin-left: 10px;" onclick="SalleOp.deletePersonnelPartogramme(
                    this.form,{typeName:'', objName:'{{$affectation->_ref_personnel->_ref_user->_view|smarty:nodefaults|JSAttribute}}', ajax: true}, '{{$selOp->_id}}'
                    );">
                    {{tr}}Delete{{/tr}}
                  </button>
                </form>
                <br />
                <span class="opacity-60">
                          {{tr}}CPersonnel.emplacement.{{$affectation->_ref_personnel->emplacement}}{{/tr}}
                </span>
              </div>
            {{/if}}
          {{/foreach}}
        </td>
        <td style="text-align: center; width: 10%;" class="me-text-align-right">
          {{mb_include module=salleOp template=inc_vw_personnel_form emplacement=aide_soignant}}
        </td>
        <td style="width: 10%;">
          {{foreach from=$affectations_operation item=affectation}}
            {{if $affectation->_ref_personnel->emplacement == "aide_soignant"}}
              {{assign var="affectation_id" value=$affectation->_id}}
              <div>
                {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$affectation->_ref_personnel->_ref_user}}

                <form name="delete_personnel_{{$affectation->_guid}}" method="post" onsubmit="return onSubmitFormAjax(this);">
                  {{mb_key   object=$affectation}}
                  {{mb_class object=$affectation}}

                  <button type="button" class="trash notext" style="margin-left: 10px;" onclick="SalleOp.deletePersonnelPartogramme(
                    this.form,{typeName:'', objName:'{{$affectation->_ref_personnel->_ref_user->_view|smarty:nodefaults|JSAttribute}}', ajax: true}, '{{$selOp->_id}}'
                    );">
                    {{tr}}Delete{{/tr}}
                  </button>
                </form>
                <br />
                <span class="opacity-60">
                        {{tr}}CPersonnel.emplacement.{{$affectation->_ref_personnel->emplacement}}{{/tr}}
                </span>
              </div>
            {{/if}}
          {{/foreach}}
        </td>
        <td style="width: 20%;">
          <form name="visitors" method="post">
            {{mb_key   object=$selOp}}
            {{mb_class object=$selOp}}
            {{if $modif_operation && !$print}}
              {{mb_field object=$selOp field=visitors onchange="return onSubmitFormAjax(this.form);" form=visitors rows="2" aidesaisie="validateOnBlur: 0, width: '100%'"}}
            {{else}}
              {{mb_value object=$selOp field=visitors}}
            {{/if}}
          </form>
        </td>
      </tr>
    </table>
  </fieldset>
</td>

