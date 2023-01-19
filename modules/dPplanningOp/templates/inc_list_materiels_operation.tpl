{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=mode value=""}}
{{mb_default var=print value=0}}

<table class="{{if $print}}print{{else}}tbl{{/if}}">
  {{if $print}}
    <tr>
      <th class="category" colspan="{{if $print}}3{{else}}4{{/if}}">
        {{tr}}CMaterielOperatoire{{/tr}}
      </th>
    </tr>
  {{/if}}

  <tr>
    {{if $mode === "validation"}}
      <th class="narrow">{{tr}}CMaterielOperatoire-Materiel ok{{/tr}}</th>
      <th class="narrow">{{tr}}CMaterielOperatoire-Materiel missing{{/tr}}</th>
    {{elseif !$print}}
      <th class="narrow"></th>
    {{/if}}
    <th {{if $print}}style="border: solid #aaa 1px; text-align: left;"{{/if}}>
      {{mb_title class=CMaterielOperatoire field=_related_product}}
    </th>
    <th class="narrow" {{if $print}}style="border: solid #aaa 1px; text-align: left;"{{/if}}>
        {{mb_title class=CMaterielOperatoire field=completude_panier}}
    </th>
    <th class="narrow" {{if $print}}style="border: solid #aaa 1px; text-align: left;"{{/if}}>
      {{mb_title class=CMaterielOperatoire field=qte_prevue}}
    </th>
    {{if $mode === "consommation" || $print}}
      <th class="narrow" {{if $print}}style="border: solid #aaa 1px; text-align: left;"{{/if}}>
        {{mb_title class=CConsommationMateriel field=qte_consommee}}
      </th>
    {{/if}}
  </tr>

  <tr>
    <th class="section" colspan="5">
      {{tr}}CMaterielOperatoire-DM usage unique{{/tr}}
    </th>
  </tr>
  {{foreach from=$operation->_refs_materiels_operatoires_dm item=_materiel_operatoire}}
    {{mb_include module=planningOp template=inc_line_materiel_operatoire}}
  {{foreachelse}}
    <tr>
      <td colspan="5" class="empty">
        {{tr}}CMaterielOperatoire.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}

  <tr>
    <th class="section" colspan="5">
      {{tr}}CMaterielOperatoire-code_cip{{/tr}}
    </th>
  </tr>
  {{foreach from=$operation->_refs_materiels_operatoires_produit item=_materiel_operatoire}}
    {{mb_include module=planningOp template=inc_line_materiel_operatoire}}
  {{foreachelse}}
    <tr>
      <td colspan="5" class="empty">
        {{tr}}CMaterielOperatoire.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}

  <tr>
    <th class="section" colspan="5">
      {{tr}}CMaterielOperatoire-DM sterilisables{{/tr}}
    </th>
  </tr>
  {{foreach from=$operation->_refs_materiels_operatoires_dm_sterilisables item=_materiel_operatoire}}
    {{mb_include module=planningOp template=inc_line_materiel_operatoire sterilisable=1}}
  {{foreachelse}}
  <tr>
    <td colspan="5" class="empty">
      {{tr}}CMaterielOperatoire.none{{/tr}}
    </td>
  </tr>
  {{/foreach}}
</table>
