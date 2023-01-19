{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=module_etiquette_pat   value=urgences}}
{{mb_default var=template_etiquette_pat value=inc_patient_placement}}

{{foreach from=$grille item=ligne}}
    <tr>
        {{foreach from=$ligne item=_zone}}
            {{if $_zone|instanceof:"Ox\Mediboard\Hospi\CAffectation" && $_zone->sejour_id === null}}
                <td class="chambre blockedBedroom" style="background-color: #ccc;"
                    rowspan="{{$_zone->_ref_lit->_ref_chambre->_ref_emplacement->hauteur}}"
                    colspan="{{$_zone->_ref_lit->_ref_chambre->_ref_emplacement->largeur}}" >
                    <small class="shadow" style="background-color: #ccc;">{{$_zone->_ref_lit->_ref_chambre->nom}}</small>
                    <div class="patient" onmouseover="ObjectTooltip.createEx(this, '{{$_zone->_guid}}')">
                        {{tr}}CChambre-BLOCKED{{/tr}}<br>
                        {{$_zone->rques}}
                    </div>
                </td>
            {{elseif $_zone != "0"}}
                <td
                  data-lit-id="{{foreach from=$_zone->_ref_lits item=i name=foo}}{{if $smarty.foreach.foo.first}}{{$i->_id}} {{/if}}{{/foreach}}"
                  data-nb-lits="{{$_zone->_ref_lits|@count}}" rowspan="{{$_zone->_ref_emplacement->hauteur}}"
                  colspan="{{$_zone->_ref_emplacement->largeur}}" class="chambre"
                  data-chambre-id="{{$_zone->chambre_id}}" data-service-id="{{$_zone->service_id}}"
                  style="background-color:#{{$_zone->_ref_emplacement->color}}{{if $IS_MEDIBOARD_EXT_DARK}}60{{/if}};">
                    <small class="shadow"
                           style="background-color:#{{$_zone->_ref_emplacement->color}};">{{$_zone}} {{if $_zone->is_examination_room && $module_etiquette_pat == "maternite"}}({{tr}}CChambre-is_examination_room{{/tr}}){{/if}}</small>
                    {{assign var=chambre   value=$_zone->chambre_id}}
                    {{if isset($listSejours.$chambre|smarty:nodefaults)}}
                        {{foreach from=$listSejours.$chambre item=_sejour}}
                            {{mb_include module=$module_etiquette_pat template=$template_etiquette_pat}}
                        {{/foreach}}
                    {{/if}}
                </td>
            {{else}}
                <td></td>
            {{/if}}
        {{/foreach}}
    </tr>
    {{foreachelse}}
    {{if $exist_plan == 0}}
        <div
          class="small-warning">{{tr var1=$name_grille}}CService-msg-In order to have access to the functionalities, please configure the service floor plan{{/tr}}
            .
        </div>
    {{else}}
        <div
          class="small-warning">{{tr var1=$name_grille}}CService-msg-In order to have access to the functionalities, please indicate a service in the module Hospitalization - Infrastruscture tab - Service tab{{/tr}}
        </div>
    {{/if}}
{{/foreach}}
