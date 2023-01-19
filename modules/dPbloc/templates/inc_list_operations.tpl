{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=multisalle value=0}}
{{mb_default var=distinct_plages value=0}}

<table class="tbl me-small">
  <tr>
    <th class="title text" colspan="4">
      {{if $multisalle}}
        <button type="button" class="hslip notext" style="float: left;"
                onclick="MultiSalle.reloadOps({{if $distinct_plages}}0{{else}}1{{/if}});">
          {{if $distinct_plages}}
            Afficher une seule liste de patients
          {{else}}
            Afficher les patients par plage
          {{/if}}
        </button>
      {{/if}}
      Patients à placer {{if isset($salle|smarty:nodefaults)}}({{$salle}}){{/if}}
      <br />
      {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$chir}} -
      {{$date|date_format:$conf.longdate}}
    </th>
  </tr>

  {{assign var=multi_salle value=1}}

  {{foreach from=$operations item=_op}}
    {{assign var=plage_id value=$_op->plageop_id}}
    {{assign var=plage value=$plages.$plage_id}}
    {{mb_include module=bloc template=inc_line_interv}}
  {{foreachelse}}
    <tr>
      <td colspan="4" class="empty">
        {{tr}}COperation.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
