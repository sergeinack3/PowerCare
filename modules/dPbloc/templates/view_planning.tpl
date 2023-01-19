{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=show_duree_preop value=$conf.dPplanningOp.COperation.show_duree_preop}}
{{assign var="col1" value="dPbloc printing_standard col1"|gconf}}
{{assign var="col2" value="dPbloc printing_standard col2"|gconf}}
{{assign var="col3" value="dPbloc printing_standard col3"|gconf}}

{{mb_include style=mediboard_ext template=open_printable}}
{{if $_page_break}}
  {{mb_include module=bloc template=inc_view_planning_header}}

  {{foreach from=$listDates key=curr_date item=listSalles}}
    {{foreach from=$listSalles key=salle_id item=listPlages name=date_loop}}
      {{foreach from=$listPlages key=curr_plage_id item=curr_plageop name=plage_loop}}
        <table class="tbl table_print" style="page-break-after: always">
          <tr class="clear">
            <td colspan="{{$colspan}}" class="text">
              {{if $curr_plage_id == "hors_plage"}}
                <h2>
                  <strong>Interventions {{if $_hors_plage}}hors plage{{/if}}</strong>
                  du {{$curr_date|date_format:"%a %d/%m/%Y"}}
                </h2>
              {{else}}
                <h2>
                  <strong>
                    {{$curr_plageop->_ref_salle->nom}}
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
                    {{assign var="plageOp_id" value=$curr_plageop->_id}}
                    <!-- Affichage du personnel prevu pour la plage operatoire -->
                    {{foreach from=$affectations_plage.$plageOp_id key=type_affect item=_affectations}}
                      {{if $_affectations|@count}}
                        <strong>{{tr}}CPersonnel.emplacement.{{$type_affect}}{{/tr}} :</strong>
                        {{foreach from=$_affectations item=_personnel}}
                          {{$_personnel->_ref_personnel->_ref_user}};
                        {{/foreach}}
                      {{/if}}
                    {{/foreach}}
                  </div>
                </h2>
              {{/if}}
            </td>
          </tr>

          {{mb_include module=bloc template=inc_view_planning_title}}

          {{if $curr_plage_id == "hors_plage"}}
            {{assign var=listOperations value=$curr_plageop}}
          {{else}}
            {{assign var=listOperations value=$curr_plageop->_ref_operations}}
          {{/if}}
          {{assign var=salle_id value=""}}

          {{mb_include module=bloc template=inc_view_planning_content}}
        </table>
      {{/foreach}}
    {{/foreach}}
  {{/foreach}}
{{else}}
<table class="tbl table_print">
  {{mb_include module=bloc template=inc_view_planning_header}}

  {{foreach from=$listDates key=curr_date item=listSalles}}
    {{foreach from=$listSalles key=salle_id item=listPlages name=date_loop}}
      {{foreach from=$listPlages key=curr_plage_id item=curr_plageop name=plage_loop}}
        <tr class="clear">
          <td colspan="{{$colspan}}" class="text">
            {{if $curr_plage_id == "hors_plage"}}
              <h2>
                <strong>Interventions {{if $_hors_plage}}hors plage{{/if}}</strong>
                du {{$curr_date|date_format:"%a %d/%m/%Y"}}
              </h2>
            {{else}}
              <h2>
                <strong>
                  {{$curr_plageop->_ref_salle->nom}}
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
                  {{assign var="plageOp_id" value=$curr_plageop->_id}}
                  <!-- Affichage du personnel prevu pour la plage operatoire -->
                  {{foreach from=$affectations_plage.$plageOp_id key=type_affect item=_affectations}}
                    {{if $_affectations|@count}}
                      <strong>{{tr}}CPersonnel.emplacement.{{$type_affect}}{{/tr}} :</strong>
                      {{foreach from=$_affectations item=_personnel}}
                        {{$_personnel->_ref_personnel->_ref_user}};
                      {{/foreach}}
                    {{/if}}
                  {{/foreach}}
                </div>
              </h2>
            {{/if}}
          </td>
        </tr>

        {{mb_include module=bloc template=inc_view_planning_title}}

        {{if $curr_plage_id == "hors_plage"}}
          {{assign var=listOperations value=$curr_plageop}}
        {{else}}
          {{assign var=listOperations value=$curr_plageop->_ref_operations}}
        {{/if}}
        {{assign var=salle_id value=""}}

        {{mb_include module=bloc template=inc_view_planning_content}}

        {{if $_page_break && !$smarty.foreach.plage_loop.last && !$_by_bloc}}
        {{* Firefox ne prend pas en compte les page-break sur les div *}}
          <tr class="clear" style="page-break-after: always;">
            <td colspan="{{$colspan}}" style="border: none;">
            {{* Chrome ne prend pas en compte les page-break sur les tr *}}
              <div style="page-break-after: always;"></div>
            </td>
          </tr>
        {{/if}}
      {{/foreach}}

      {{if $_page_break && !$smarty.foreach.date_loop.last}}
        {{* Firefox ne prend pas en compte les page-break sur les div *}}
        <tr class="clear" style="page-break-after: always;">
          <td colspan="{{$colspan}}" style="border: none;">
            {{* Chrome ne prend pas en compte les page-break sur les tr *}}
            <div style="page-break-after: always;"></div>
          </td>
        </tr>
      {{/if}}
    {{/foreach}}
  {{/foreach}}
</table>
{{/if}}

{{mb_include module=bloc template=inc_offline_view_planning}}

{{mb_include style=mediboard_ext template=close_printable}}
