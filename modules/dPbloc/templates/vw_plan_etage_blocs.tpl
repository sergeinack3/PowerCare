{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=bloc script=drag_salle ajax=true}}

<script>
  Main.add(function () {
    Control.Tabs.create('tab_plan_blocs', true);
  });
</script>

<div id="plan_etage_blocs">
  <table class="main" style="text-align:center;">
    <tr>
      <td>
        <table>
          <tr>
            <td>
              <form name="planEtageBloc" action="#" method="get">
                <select name="blocs_id" style="width: 12em;" multiple size="{{$blocs|@count}}">
                  <option value="">&mdash; {{tr}}CBlocOperatoire{{/tr}}</option>
                  {{foreach from=$blocs item=_bloc}}
                    <option value="{{$_bloc->_id}}"
                            {{if in_array($_bloc->_id, $blocs_selected)}}selected="selected"{{/if}}>{{$_bloc->nom}}</option>
                  {{/foreach}}
                </select>
                <button type="button" class="search" onclick="PlanEtageBloc.refreshPlan();">{{tr}}Filter{{/tr}}</button>
              </form>
            </td>
          </tr>
          <tr>
            <td class="conteneur_salles_non_places">
              <div id="list-salles-non-placees">
                <ul id="tab_plan_blocs" class="control_tabs">
                  {{foreach from=$salles_non_placees item=_salles key=_salle_id}}
                    {{assign var=salle value=$blocs.$_salle_id}}
                    <li><a href="#{{$salle->_guid}}">{{$salle->nom}}</a></li>
                  {{/foreach}}
                </ul>

                {{foreach from=$salles_non_placees item=_salles key=_salle_id}}
                  {{assign var=salle value=$blocs.$_salle_id}}
                  <div id="{{$salle->_guid}}">
                    {{foreach from=$_salles item=_salle}}
                      <div data-salle-id="{{$_salle->_id}}" class="salle draggable"
                        {{if $_salle->_ref_emplacement_salle}}
                        (data-largeur-nb="{{$_salle->_ref_emplacement_salle->largeur}}"
                        data-hauteur-nb="{{$_salle->_ref_emplacement_salle->hauteur}}" style="background-color:#{{$_salle->_ref_emplacement_salle->color}};"
                        {{/if}}>
                        <form name="EmplacementSalle-{{$_salle->_id}}" action="" method="post">
                          <input type="hidden" name="emplacement_salle_id" value="" />
                          <input type="hidden" name="@class" value="CEmplacementSalle" />
                          <input type="hidden" name="del" value="0" />
                          <input type="hidden" name="salle_id" value="{{$_salle->_id}}" />
                          <input type="hidden" name="plan_x" value="" />
                          <input type="hidden" name="plan_y" value="" />
                        </form>
                        {{$_salle->nom}}
                      </div>
                    {{/foreach}}
                  </div>
                {{/foreach}}
              </div>
            </td>
          </tr>
        </table>
      </td>
      <td>
        <table>
          <tr>
            <th class="title">{{if $blocs_selected|@count == 1}}{{tr}}CBlocOperatoire-Block plan{{/tr}} '{{$bloc->nom}}'{{else}}{{tr}}CBlocOperatoire-Block plan-court{{/tr}}{{/if}}</th>
          </tr>
          <tr>
            <td>
              {{if $warning}}
                <div class="error" style="text-align: left;">
                  {{tr}}CEmplacementSalle-Attention collisions are present on the location of the rooms{{/tr}}
                </div>
              {{/if}}
              <div id="grille">
                <table class="main tbl" id="table_grille">
                  {{foreach from=$grille item=ligne key=y}}
                  <tr>
                    {{foreach from=$ligne item=_zone_salles key=x}}
                      {{if $_zone_salles|@count}}
                        {{foreach from=$_zone_salles item=_zone key=nb_zone}}
                          <td data-x="{{$x}}" data-y="{{$y}}"
                              class="conteneur-salle draggable {{if $_zone_salles|@count > 1}}error{{/if}}"
                              rowspan="{{$_zone->_ref_emplacement_salle->hauteur}}" colspan="{{$_zone->_ref_emplacement_salle->largeur}}">
                            <div data-salle-id="{{$_zone->salle_id}}" class="salle {{if $_zone_salles|@count > 1}}error{{/if}}"
                                 data-largeur-nb="{{$_zone->_ref_emplacement_salle->largeur}}"
                                 data-hauteur-nb="{{$_zone->_ref_emplacement_salle->hauteur}}"
                                 style="background-color:#{{$_zone->_ref_emplacement_salle->color}};height:{{$_zone->_ref_emplacement_salle->hauteur*60}}px">

                              <form name="EmplacementSalle-{{$_zone->_id}}" action="" method="post">
                                {{mb_class object=$_zone->_ref_emplacement_salle}}
                                {{mb_key object=$_zone->_ref_emplacement_salle}}
                                <input type="hidden" name="del" value="0" />
                                <input type="hidden" name="salle_id" value="{{$_zone->salle_id}}" />
                                <input type="hidden" name="plan_x" value="" />
                                <input type="hidden" name="plan_y" value="" />
                              </form>
                              <a href="#" onclick="PlanEtageBloc.show('{{$_zone->salle_id}}');">{{$_zone->nom}}</a>
                              {{if $_zone_salles|@count > 1}}
                                <div class="compact" style="white-space: pre-wrap;color:red">
                                  Collision: {{foreach from=$_zone_salles item=_zone_other}}{{if $_zone->salle_id != $_zone_other->salle_id}}{{$_zone_other}} {{/if}}{{/foreach}}
                                </div>
                              {{/if}}
                              <div class="compact"
                                   style="display: block;white-space: pre-wrap;color:black;">{{$_zone->code}}</div>
                            </div>
                          </td>
                        {{/foreach}}
                      {{else}}
                        <td data-x="{{$x}}" data-y="{{$y}}" class="conteneur-salle"></td>
                      {{/if}}
                    {{/foreach}}
                    {{/foreach}}
                  </tr>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</div>
