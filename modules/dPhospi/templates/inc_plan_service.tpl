{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$grille item=ligne}}
  <tr>
    {{foreach from=$ligne item=_zone }}
      {{if $_zone!="0" && $_zone->service_id == $key}}
        <td data-service-id="{{$_zone->service_id}}" data-chambre-id="{{$_zone->chambre_id}}"
            data-lit-id="{{foreach from=$_zone->_ref_lits item=i name=foo}}{{if $smarty.foreach.foo.first}}{{$i->_id}} {{/if}}{{/foreach}}"
            data-nb-lits="{{$_zone->_ref_lits|@count}}"
            class="chambre" colspan="{{$_zone->_ref_emplacement->largeur}}" rowspan="{{$_zone->_ref_emplacement->hauteur}}"
            id="chambre_topo-{{$_zone->chambre_id}}"
            style="background-color:#{{$_zone->_ref_emplacement->color}}{{if $IS_MEDIBOARD_EXT_DARK}}15{{/if}};width:{{$_zone->_ref_emplacement->largeur*120}}px;height:{{$_zone->_ref_emplacement->hauteur*80}}px;">
          {{if $can->edit}}
            <script>
              Main.add(function () {
                var container = $('chambre_topo-{{$_zone->chambre_id}}');
                Droppables.add(container, {
                  onDrop: function (element, zonedrop) {
                    ChoiceLit.selectAction(element, zonedrop);
                  }
                });
              });
            </script>
          {{/if}}
          <small style="background-color:#{{$_zone->_ref_emplacement->color}}{{if $IS_MEDIBOARD_EXT_DARK}}15{{/if}};">{{$_zone}}</small>
          {{foreach from=$chambres_affectees item=_affectation}}
            {{if $_affectation->_ref_lit && $_affectation->_ref_lit->chambre_id == $_zone->chambre_id}}
              {{mb_include module=hospi template=inc_vw_patient_affectation}}
            {{/if}}
          {{/foreach}}
        </td>
      {{else}}
        <td class="chambre"></td>
      {{/if}}
    {{/foreach}}
  </tr>
  {{foreachelse}}
  Pas de plan existant pour ce service
{{/foreach}}