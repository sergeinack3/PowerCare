{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="applyProtocoleOp" method="post">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_apply_protocole_op" />
  <input type="hidden" name="operation_id" value="{{$operation_id}}" />

  <fieldset>
    <legend>
      {{tr}}CProtocoleOperatoire{{/tr}} : {{$protocole_operatoire->_view}}
    </legend>

    {{mb_include module=planningOp template=inc_vw_descriptions_protocole_operatoire}}

    <table class="tbl">
      <tr>
        <th class="narrow"></th>
        <th>
          {{mb_title class=CMaterielOperatoire field=_related_product}}
        </th>
        <th class="narrow">
            {{mb_title class=CMaterielOperatoire field=completude_panier}}
        </th>
        <th class="narrow">
          {{mb_title class=CMaterielOperatoire field=qte_prevue}}
        </th>
      </tr>

      <tr>
        <th class="section" colspan="4">{{tr}}CMaterielOperatoire-DM usage unique{{/tr}}</th>
      </tr>

      {{foreach from=$protocole_operatoire->_refs_materiels_operatoires_dm item=_materiel_operatoire}}
        {{mb_include module=planningOp template=inc_line_materiel_operatoire advanced_prot=1}}
      {{foreachelse}}
      <tr>
        <td colspan="4">
          {{tr}}CMaterielOperatoire.none{{/tr}}
        </td>
      </tr>
      {{/foreach}}

      <tr>
        <th class="section" colspan="4">{{tr}}CMaterielOperatoire-code_cip{{/tr}}</th>
      </tr>

      {{foreach from=$protocole_operatoire->_refs_materiels_operatoires_produit item=_materiel_operatoire}}
        {{mb_include module=planningOp template=inc_line_materiel_operatoire advanced_prot=1}}
        {{foreachelse}}
        <tr>
          <td colspan="4">
            {{tr}}CMaterielOperatoire.none{{/tr}}
          </td>
        </tr>
      {{/foreach}}

      <tr>
        <th class="section" colspan="4">{{tr}}CMaterielOperatoire-DM sterilisables{{/tr}}</th>
      </tr>

      {{foreach from=$protocole_operatoire->_refs_materiels_operatoires_dm_sterilisables item=_materiel_operatoire}}
        {{mb_include module=planningOp template=inc_line_materiel_operatoire advanced_prot=1}}
        {{foreachelse}}
        <tr>
          <td colspan="4">
            {{tr}}CMaterielOperatoire.none{{/tr}}
          </td>
        </tr>
      {{/foreach}}

    </table>

    <div class="me-align-auto me-margin-top-8" style="text-align: center;">
      <button type="button" class="tick oneclick me-primary"
              onclick="ProtocoleOp.apply('{{$protocole_operatoire->_id}}', '{{$protocole_operatoire->_view|JSAttribute}}');">{{tr}}Apply{{/tr}}</button>
      <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
    </div>
  </fieldset>
</form>