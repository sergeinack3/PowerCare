{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=print value=0}}

<table class="tbl">
  <tr>
    {{if !$print}}
    <th class="narrow">
      <button class="new notext me-primary" onclick="ProtocoleOp.editMateriel(null, '{{$protocole_op->_id}}');">
        {{tr}}CMaterielOperatoire-title-create{{/tr}}
      </button>
    </th>
    {{/if}}
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

  {{foreach from=$protocole_op->_refs_materiels_operatoires_dm item=_materiel_operatoire}}
  <tr>
    {{if !$print}}
    <td>
      <button class="edit notext"
              onclick="ProtocoleOp.editMateriel('{{$_materiel_operatoire->_id}}', '{{$_materiel_operatoire->protocole_operatoire_id}}');">
        {{tr}}Edit{{/tr}}
      </button>
    </td>
    {{/if}}
    <td class="me-valign-top">
      {{$_materiel_operatoire->_view}}
    </td>
    <td class="me-text-align-center">
        {{if $_materiel_operatoire->completude_panier}}
          <i class="fa fa-check" style="color: forestgreen;"></i>
        {{else}}
          <i class="fas fa-times" style="color: red;"></i>
        {{/if}}
    </td>
    <td>
      {{mb_value object=$_materiel_operatoire field=qte_prevue}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" colspan="4">
      {{tr}}CMaterielOperatoire.none{{/tr}}
    </td>
  </tr>
  {{/foreach}}

  <tr>
    <th class="section" colspan="4">{{tr}}CMaterielOperatoire-code_cip{{/tr}}</th>
  </tr>

  {{foreach from=$protocole_op->_refs_materiels_operatoires_produit item=_materiel_operatoire}}
    <tr>
      {{if !$print}}
      <td>
        <button class="edit notext"
                onclick="ProtocoleOp.editMateriel('{{$_materiel_operatoire->_id}}', '{{$_materiel_operatoire->protocole_operatoire_id}}');">
          {{tr}}Edit{{/tr}}
        </button>
      </td>
      {{/if}}
      <td class="me-valign-top">
        {{$_materiel_operatoire->_view}}
      </td>
      <td class="me-text-align-center">
          {{if $_materiel_operatoire->completude_panier}}
            <i class="fa fa-check" style="color: forestgreen;"></i>
          {{else}}
            <i class="fas fa-times" style="color: red;"></i>
          {{/if}}
      </td>
      <td>
        {{mb_value object=$_materiel_operatoire field=qte_prevue}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="4">
        {{tr}}CMaterielOperatoire.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}

  <tr>
    <th class="section" colspan="4">{{tr}}CMaterielOperatoire-DM sterilisables{{/tr}}</th>
  </tr>

  {{foreach from=$protocole_op->_refs_materiels_operatoires_dm_sterilisables item=_materiel_operatoire}}
    <tr>
      {{if !$print}}
        <td>
          <button class="edit notext"
                  onclick="ProtocoleOp.editMateriel('{{$_materiel_operatoire->_id}}', '{{$_materiel_operatoire->protocole_operatoire_id}}');">
            {{tr}}Edit{{/tr}}
          </button>
        </td>
      {{/if}}
      <td class="me-valign-top">
        {{$_materiel_operatoire->_view}}
      </td>
      <td class="me-text-align-center">
          {{if $_materiel_operatoire->completude_panier}}
            <i class="fa fa-check" style="color: forestgreen;"></i>
          {{else}}
            <i class="fas fa-times" style="color: red;"></i>
          {{/if}}
      </td>
      <td>
        {{mb_value object=$_materiel_operatoire field=qte_prevue}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="4">
        {{tr}}CMaterielOperatoire.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>