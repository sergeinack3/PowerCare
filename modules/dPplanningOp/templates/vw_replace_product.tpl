{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    {{if "dmi"|module_active}}
      ProtocoleOp.dmi_active = true;
    {{/if}}

    ['productFrom', 'productTo'].each(function(_form) {
      ProtocoleOp.makeAutocompletesProduit(getForm(_form), _form === 'productFrom' ? 1 : 0);
    });
  });
</script>

<form name="replaceProduct" method="post">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_replace_product_protocole_op" />
  <input type="hidden" name="protocole_op_ids" />
  <input type="hidden" name="operation_ids" />
  <input type="hidden" name="mode_operation" />
  <input type="hidden" name="dm_id_from" />
  <input type="hidden" name="code_cip_from" />
  <input type="hidden" name="dm_id_to" />
  <input type="hidden" name="code_cip_to" />
</form>

<table class="tbl">
  <tr>
    <td style="width: 33%;">
      <form name="productFrom" method="get">
        {{mb_include module=planningOp template=inc_form_replace_product}}
      </form>
    </td>
    <td style="text-align: center; width: 33%;">
      <h2>
        {{tr}}common-by{{/tr}}
      </h2>
    </td>
    <td>
      <form name="productTo" method="get">
        {{mb_include module=planningOp template=inc_form_replace_product}}
      </form>
    </td>
  </tr>
  <tr>
    <td class="button" colspan="3">
      <button class="tick me-primary" id="view_replace"
              onclick="ProtocoleOp.seeProtocoles();" disabled>{{tr}}common-action-View{{/tr}}</button>
      <button class="tick" id="validate_replace"
              onclick="ProtocoleOp.validerReplacement();" disabled>{{tr}}CProtocoleOperatoire-Validate replacement{{/tr}}</button>
      <button class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
    </td>
  </tr>

  <tr>
    <td colspan="3" id="replacement_result"></td>
  </tr>
</table>