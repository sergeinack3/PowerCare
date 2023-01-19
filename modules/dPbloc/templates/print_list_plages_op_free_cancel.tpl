{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=bloc script=edit_planning ajax=1}}

{{mb_include style=mediboard_ext template=open_printable}}

<table class="tbl table_print">
  <tr class="clear">
    <th colspan="6">
      <button type="button" style="float: right;"
              class="not-printable"
              onclick="EditPlanning.exportPlagesList();">
        <i class="fas fa-upload"></i> {{tr}}Export-CSV{{/tr}}
      </button>
      <h1 style="margin: auto;">
        <a href="#" onclick="this.up('table').print();">
          {{tr var1=$datetime_min|date_format:$conf.datetime var2=$datetime_max|date_format:$conf.datetime}}CPlageOp-Free vacations and canceled from %s to %s{{/tr}}
          - {{$plages|@count}} plage(s)
        </a>
      </h1>
    </th>
  </tr>

  {{foreach from=$plages key=curr_plage_id item=curr_plageop name=plage_loop}}
    <tr class="clear">
      <td colspan="{{$colspan}}" class="text">
        <h2>
          <strong>
            {{$curr_plageop->_ref_salle->_ref_bloc->_view}} {{$curr_plageop->_ref_salle->nom}}
            -
            {{if $curr_plageop->chir_id}}
              Dr {{$curr_plageop->_ref_chir}}
            {{else}}
              {{$curr_plageop->_ref_spec}}
            {{/if}}
            {{if $curr_plageop->anesth_id}}
              - Anesthesiste : Dr {{$curr_plageop->_ref_anesth}}
            {{/if}}
          </strong>
          <div style="font-size: 70%">
            {{$curr_plageop->date|date_format:"%a %d/%m/%Y"}}
            {{$curr_plageop->_ref_salle->_view}}
            de {{$curr_plageop->debut|date_format:$conf.time}} à {{$curr_plageop->fin|date_format:$conf.time}}
          </div>
        </h2>
      </td>
    </tr>

    <tr>
      <th class="title"></th>
    </tr>
    <tr>
      <td></td>
    </tr>
    {{if $_page_break && !$smarty.foreach.plage_loop.last}}
      {{* Firefox ne prend pas en compte les page-break sur les div *}}
      <tr class="clear" style="page-break-after: always;">
        <td colspan="{{$colspan}}" style="border: none;">
          {{* Chrome ne prend pas en compte les page-break sur les tr *}}
          <div style="page-break-after: always;"></div>
        </td>
      </tr>
    {{/if}}
  {{/foreach}}
</table>

{{mb_include style=mediboard_ext template=close_printable}}
