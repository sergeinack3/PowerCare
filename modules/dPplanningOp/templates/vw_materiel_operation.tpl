{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=readonly value=0}}

{{mb_script module=planningOp script=protocole_op ajax=$ajax}}
{{mb_script module=patients script=patient ajax=$ajax}}

{{assign var=dmi_active value="dmi"|module_active}}

<script>
  Main.add(function() {
    {{if $dmi_active}}
      ProtocoleOp.dmi_active = true;
    {{/if}}

    ProtocoleOp.operation_id = '{{$operation->_id}}';
    ProtocoleOp.chir_id = '{{$operation->chir_id}}';
    ProtocoleOp.bdm = '{{'Ox\Mediboard\Medicament\CMedicament::getBase'|static_call:null}}';
    ProtocoleOp.mode = '{{$mode}}';
    ProtocoleOp.readonly = {{$readonly}};

    ProtocoleOp.makeAutocompletesProduit(getForm('addMaterielOp'));
    ProtocoleOp.makeAutocompleteProtocole(getForm('searchProtocoleOp'));
    ProtocoleOp.refreshListMaterielsOperation();
  });
</script>

{{assign var=sejour value=$operation->_ref_sejour}}
{{assign var=patient value=$sejour->_ref_patient}}

{{mb_include module=salleOp template=inc_header_operation selOp=$operation readonly=true}}

<form name="editMateriel" method="post" class="prepared">
  {{mb_class object=$materiel_op}}
  {{mb_key   object=$materiel_op}}
  <input type="hidden" name="del" value="0" />
</form>

<form name="delConsommation" method="post" class="prepared">
  {{mb_class object=$consommation}}
  {{mb_key object=$consommation}}
  <input type="hidden" name="del" value="1" />
</form>

<form name="toggleLockConsommation" method="post" class="prepared">
  {{mb_class object=$operation}}
  {{mb_key object=$operation}}
  {{mb_field object=$operation field=consommation_user_id hidden=true}}
  {{mb_field object=$operation field=consommation_datetime hidden=true}}
</form>

<form name="removeProtocole" method="post" class="prepared">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_remove_protocole_operation" />
  <input type="hidden" name="operation_id" value="{{$operation->_id}}" />
  <input type="hidden" name="protocole_operatoire_id" />
</form>

{{if in_array($mode, array("protocole", "validation"))}}
  {{foreach from=$operation->_ref_protocoles_operatoires item=protocole_operatoire}}
    <fieldset class="me-align-auto me-margin-top-8">
      <legend>
        {{mb_value object=$protocole_operatoire field=libelle}}
      </legend>

      {{mb_include module=planningOp template=inc_vw_descriptions_protocole_operatoire}}
    </fieldset>
  {{/foreach}}
{{/if}}

<div class="me-align-auto me-margin-top-4">
  <table class="main me-w100">
    <tr>
      <td class="halfPane">
        <div class="not-printable">
          {{if $mode === "validation"}}
            {{if !$readonly}}
              <form name="validerAllMateriels" method="post"
                    onsubmit="return onSubmitFormAjax(this, ProtocoleOp.refreshListMaterielsOperation);">
                <input type="hidden" name="m" value="planningOp" />
                <input type="hidden" name="dosql" value="do_valide_all_materiels_operatoires" />
                <input type="hidden" name="operation_id" value="{{$operation->_id}}" />
                <button class="tick">{{tr}}CMaterielOperatoire-Validate all{{/tr}}</button>
              </form>
            {{/if}}
          {{else}}
            {{if $mode !== "consommation"}}
              <form name="searchProtocoleOp" method="get">
                <input type="text" name="_protocole_op_libelle" style="font-size: 1.3em; font-weight: bold; width: 210px;"
                       placeholder="{{tr}}CProtocoleOperatoire-Choose protocole{{/tr}}" />
              </form>
            {{/if}}

            <form name="addMaterielOp" method="post">
              {{mb_class object=$materiel_op}}
              {{mb_key   object=$materiel_op}}

              {{mb_field object=$materiel_op field=operation_id hidden=true}}

              {{if $dmi_active}}
                {{mb_field object=$materiel_op field=dm_id onchange="ProtocoleOp.applyProduct();" hidden=true}}

                <input type="text" name="_product_keywords" style="font-size: 1.3em; font-weight: bold;"
                       placeholder="{{tr}}CMaterielOperatoire-Choose DM{{/tr}}" />
              {{/if}}

              {{mb_field object=$materiel_op field=bdm hidden=true}}
              {{mb_field object=$materiel_op field=code_cip onchange="ProtocoleOp.applyProduct();" hidden=true}}

              <input type="text" name="produit" style="font-size: 1.3em; font-weight: bold;"
                     placeholder="{{tr}}CMaterielOperatoire-Choose product{{/tr}}" />
            </form>
          {{/if}}

          {{if $mode === "consommation" && !$readonly}}
            {{if $operation->consommation_user_id}}
              <button type="button" class="unlock" onclick="ProtocoleOp.toggleLockConsommation();">
                {{tr}}COperation-Unlock consommation{{/tr}}
              </button>
            {{else}}
              <button type="button" class="lock" onclick="ProtocoleOp.toggleLockConsommation(1);">
                {{tr}}COperation-Lock consommation{{/tr}}
              </button>
            {{/if}}
          {{/if}}

          <button type="button" class="print not-printable" onclick="this.up('div.content').print();">{{tr}}Print{{/tr}}</button>
        </div>
      </td>
      <th class="narrow" style="white-space: nowrap;">
        {{tr}}CProtocoleOperatoire-Protocoles applyed{{/tr}} :
      </th>
      <td class="text" id="list_protocoles_operation">
        {{foreach from=$operation->_ref_protocoles_operatoires item=_protocole_op name=protocole_op}}
          {{if in_array($mode, array("validation", "consommation")) || $readonly}}
            {{$_protocole_op->_view}}{{if !$smarty.foreach.protocole_op.last}}, {{/if}}
          {{else}}
            <button type="button" class="remove" title="{{tr}}Delete{{/tr}}"
                    onclick="ProtocoleOp.removeProtocoleOperation(this, '{{$_protocole_op->_id}}', '{{$_protocole_op->_view|JSAttribute}}');">
              {{$_protocole_op->_view}}
            </button>
          {{/if}}
        {{/foreach}}
      </td>
    </tr>
  </table>
</div>

<div id="materiels_area"></div>
