{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=ssr_class value=""}}
{{if $sejour->annule == "1"}}
{{assign var=ssr_class value=ssr-annule}}
{{elseif !$sejour->entree_reelle}}
{{assign var=ssr_class value=ssr-prevu}}
{{elseif $sejour->sortie_reelle}}
{{assign var=ssr_class value=ssr-termine}}
{{/if}}

<tr class="{{$ssr_class}}">
  {{assign var=replacement value=$sejour->_ref_replacement}}
  
  <td class="text ssr-repartition {{if $replacement && $replacement->_id}} arretee {{/if}}">
    {{if $remplacement}} 
    <div>
      {{mb_include module=ssr template=inc_sejour_repartition}}
    </div>
    {{else}}
    <div class="{{if $can->edit}} draggable {{/if}}" id="{{$sejour->_guid}}">
      {{if $can->edit}}
        <script>Repartition.draggableSejour('{{$sejour->_guid}}')</script>
      {{/if}}

      {{mb_include module=ssr template=inc_sejour_repartition}}
    </div>
    {{/if}}
  </td>
</tr>