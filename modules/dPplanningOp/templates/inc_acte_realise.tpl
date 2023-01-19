{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $codable->_ref_actes|@count}}
  {{assign var=nb_actes value=0}}
  {{assign var=count value=0}}
  {{foreach from=$codable->_ref_actes item="acte" name="tab_codable"}}
    {{if $acte->executant_id|in_array:$prat_ids && (($order == 'acte_execution' && 'Ox\Core\CMbDT::date'|static_call:$acte->execution == $key) || $order == 'sortie_reelle')}}
      {{math assign=count equation="x+1" x=$count}}
      {{if $nb_actes != 0}}
        <tr>
      {{/if}}
      {{assign var=nb_actes value=1}}
        <td>
          {{if $acte->_class == "CActeCCAM"}}
            <a href="#code-{{$acte->code_acte}}" onclick="viewCCAM('{{$acte->code_acte}}');">{{$acte->code_acte}}</a>
          {{elseif $acte->_class !== "CFraisDivers"}}
            {{$acte->code}}
          {{else}}
            {{$acte->_shortview}}
          {{/if}}
        </td>
        {{if $acte->_class == "CActeCCAM"}}
          <td>{{$acte->code_activite}}</td>
          <td>{{$acte->code_phase}}</td>
          <td>{{$acte->modificateurs}}</td>
          <td>{{$acte->code_association}}</td>
        {{else}}
          <td colspan="4"></td>
        {{/if}}
        <td>
          {{if $acte->_class == "CActeCCAM"}}
            <button id="regle-{{$acte->_id}}" type="button"
              class="{{if $acte->regle}}cancel{{else}}tick{{/if}} notext"
              onclick="submitActeCCAM(getForm('reglement-{{$acte->_guid}}'), '{{$acte->_id}}', 'regle')">
              Changer
            </button>
          {{/if}}
          {{mb_value object=$acte field=montant_base}}
        </td>
        <td>
          {{if $acte->montant_depassement && $acte->_class == "CActeCCAM"}}
            <button id="regle_dh-{{$acte->_id}}" type="button"
              class="{{if $acte->regle_dh}}cancel{{else}}tick{{/if}} notext"
              onclick="submitActeCCAM(getForm('reglement-{{$acte->_guid}}'), '{{$acte->_id}}', 'regle_dh')">
              Changer
            </button>
          {{/if}}
          {{mb_value object=$acte field=montant_depassement}}
        </td>
        <td>
          {{mb_value object=$acte field=_montant_facture}}
          {{if $acte->_class == "CActeCCAM"}}
            <div id="divreglement-{{$acte->_guid}}">
              <form name="reglement-{{$acte->_guid}}" method="post" action="">
                <input type="hidden" name="dosql" value="do_acteccam_aed" />
                <input type="hidden" name="m" value="dPsalleOp" />
                <input type="hidden" name="acte_id" value="{{$acte->_id}}" />
                <input type="hidden" name="object_class" value="{{$acte->object_class}}" />
                <input type="hidden" name="object_id" value="{{$acte->object_id}}" />
                <input type="hidden" name="_check_coded" value="0" />
                <input type="hidden" name="regle" value="{{$acte->regle}}" />
                <input type="hidden" name="regle_dh" value="{{$acte->regle_dh}}" />
                <input type="hidden" name="execution" value="{{$acte->execution}}" />
                {{foreach from=$acte->_modificateurs item="modificateur"}}
                  <input type="hidden" name="modificateur_{{$modificateur}}" value="on" />
                {{/foreach}}
              </form>
            </div>
          {{/if}}
        </td>
      {{if $count == 1 && "dPfacturation"|module_active && "dPplanningOp CFactureEtablissement use_facture_etab"|gconf && $see_facture}}
        {{assign var=facture value=$sejour->_ref_facture}}
        <td rowspan="{{$nbActes.$sejour_id}}" {{if $facture->_id}}id="{{$facture->_guid}}"{{/if}}>
          {{if $facture->_id}}
            {{mb_include module=facturation template=inc_vw_reglements_etab}}
          {{else}}
            <div class="small-warning">Pas de facture</div>
          {{/if}}
        </td>
      {{/if}}
      </tr>
    {{/if}}
  {{foreachelse}}
    <td class="empty" colspan="10">
    {{tr}}CActe.none{{/tr}}
    </td>
  {{/foreach}}
{{/if}}
