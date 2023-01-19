{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=lettre value=false}}
{{assign var=icone_nc value=false}}
<span>
  {{if $sejour->type == "ambu"}}
    {{assign var=lettre value="X"}}
  {{elseif $curr_affectation->sortie|iso_date == $demain}}
    {{if $aff_next->_id}}
      {{assign var=lettre value="OC"}}
    {{else}}
      {{assign var=lettre value="O"}}
    {{/if}}
  {{elseif $curr_affectation->sortie|iso_date == $date}}
    {{if $aff_next->_id}}
      {{assign var=lettre value="OoC"}}
    {{else}}
      {{assign var=lettre value="Oo"}}
    {{/if}}
  {{/if}}
  {{if $sejour->nuit_convenance}}
    {{assign var=icone_nc value=true}}
  {{/if}}
</span>

{{if $lettre}}
  {{mb_include module=hospi template=inc_vw_icone_sejour}}
{{/if}}

{{if $sejour->mode_sortie == "transfert" && $sejour->etablissement_sortie_id}}
  <span class="sortie_transfert" style="float: left;"
        title="{{tr}}CSejour.sortie_transfert{{/tr}}: {{$sejour->loadRefEtablissementTransfert()}}"
  >
    {{tr}}CSejour.sortie_transfert.court{{/tr}}
  </span>
{{/if}}