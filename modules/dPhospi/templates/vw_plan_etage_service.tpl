{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Control.Tabs.create('tab_plan_services', true);
  });
</script>
<table class="main" style="text-align:center;">
  <tr>
    <td>
      <table>
        <tr>
          <td>
            <form name="planEtage" action="#" method="get">
              <select name="services_id" class="me-vh60" style="width: 12em;" multiple>
                <option value="">&mdash; Service</option>
                {{foreach from=$services item=_service}}
                  <option value="{{$_service->_id}}"
                          {{if in_array($_service->_id, $service_selected)}}selected="selected"{{/if}}>{{ $_service->nom}}</option>
                {{/foreach}}
              </select>
              <button type="button" class="search" onclick="PlanEtage.refreshPlan();">{{tr}}Filter{{/tr}}</button>
            </form>
          </td>
        </tr>
        <tr>
          <td class="conteneur_chambres_non_places">
            <div id="list-chambres-non-placees">
              <ul id="tab_plan_services" class="control_tabs">
                {{foreach from=$chambres_non_placees item=_service key=_service_id}}
                  {{assign var=service value=$services.$_service_id}}
                  <li><a href="#{{$service->_guid}}">{{$service->nom}}</a></li>
                {{/foreach}}
              </ul>

              {{foreach from=$chambres_non_placees item=_service key=_service_id}}
                {{assign var=service value=$services.$_service_id}}
                <div id="{{$service->_guid}}">
                  {{foreach from=$_service item=_chambre}}
                    <div data-chambre-id="{{$_chambre->_id}}" class="chambre draggable"
                      {{if $_chambre->_ref_emplacement}}
                      (data-largeur-nb="{{$_chambre->_ref_emplacement->largeur}}"
                      data-hauteur-nb="{{$_chambre->_ref_emplacement->hauteur}}"
                      style="background-color:#{{$_chambre->_ref_emplacement->color}}{{if $IS_MEDIBOARD_EXT_DARK}}50{{/if}};"
                      {{/if}}>
                      <form name="Emplacement-{{$_chambre->_id}}" action="" method="post">
                        <input type="hidden" name="emplacement_id" value="" />
                        <input type="hidden" name="@class" value="CEmplacement" />
                        <input type="hidden" name="del" value="0" />
                        <input type="hidden" name="chambre_id" value="{{$_chambre->_id}}" />
                        <input type="hidden" name="plan_x" value="" />
                        <input type="hidden" name="plan_y" value="" />
                      </form>
                      {{$_chambre->nom}}
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
          <th class="title">{{if $service_selected|@count == 1}}Plan du service '{{$service->nom}}'{{else}}Plan{{/if}}</th>
        </tr>
        <tr>
          <td>
            {{if $warning}}
              <div class="error" style="text-align: left;">
                Attention, des collisions sont présentes sur l'emplacement des chambres.
              </div>
            {{/if}}
            <div id="grille">
              <table class="main tbl" id="table_grille">
                {{foreach from=$grille item=ligne key=y }}
                <tr>
                  {{foreach from=$ligne item=_zone_chs key=x}}
                    {{if $_zone_chs|@count}}
                      {{foreach from=$_zone_chs item=_zone key=nb_zone}}
                        <td data-x="{{$x}}" data-y="{{$y}}"
                            class="conteneur-chambre draggable {{if $_zone_chs|@count > 1}}error{{/if}}"
                            rowspan="{{$_zone->_ref_emplacement->hauteur}}" colspan="{{$_zone->_ref_emplacement->largeur}}">
                          <div data-chambre-id="{{$_zone->chambre_id}}" class="chambre {{if $_zone_chs|@count > 1}}error{{/if}}"
                               data-largeur-nb="{{$_zone->_ref_emplacement->largeur}}"
                               data-hauteur-nb="{{$_zone->_ref_emplacement->hauteur}}"
                               style="background-color:#{{$_zone->_ref_emplacement->color}}{{if $IS_MEDIBOARD_EXT_DARK}}50{{/if}};
                                      height:{{$_zone->_ref_emplacement->hauteur*60}}px;">

                            <form name="Emplacement-{{$_zone->_id}}" action="" method="post">
                              {{mb_class object=$_zone->_ref_emplacement}}
                              {{mb_key object=$_zone->_ref_emplacement}}
                              <input type="hidden" name="del" value="0" />
                              <input type="hidden" name="chambre_id" value="{{$_zone->chambre_id}}" />
                              <input type="hidden" name="plan_x" value="" />
                              <input type="hidden" name="plan_y" value="" />
                            </form>
                            <a href="#" onclick="PlanEtage.show('{{$_zone->chambre_id}}');">{{$_zone}}</a>
                            {{if $_zone_chs|@count > 1}}
                              <div class="compact" style="white-space: pre-wrap;color:red">
                                Collision: {{foreach from=$_zone_chs item=_zone_other}}{{if $_zone->chambre_id != $_zone_other->chambre_id}}{{$_zone_other}} {{/if}}{{/foreach}}
                              </div>
                            {{/if}}
                            <div class="compact"
                                 style="display: block;white-space: pre-wrap;color:black;">{{$_zone->caracteristiques|truncate:60}}</div>
                          </div>
                        </td>
                      {{/foreach}}
                    {{else}}
                      <td data-x="{{$x}}" data-y="{{$y}}" class="conteneur-chambre"></td>
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
