{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl" id="table_grille">
  {{foreach from=$grille item=ligne key=y }}
  <tr>
    {{foreach from=$ligne item=_zone key=x}}
      {{if $_zone!='0'}}
        <td data-x="{{$x}}" data-y="{{$y}}" class="conteneur-chambre draggable" rowspan="{{$_zone->_ref_emplacement->hauteur}}"
            colspan="{{$_zone->_ref_emplacement->largeur}}">
          <div data-chambre-id="{{$_zone->chambre_id}}" class="chambre" data-largeur-nb="{{$_zone->_ref_emplacement->largeur}}"
               data-hauteur-nb="{{$_zone->_ref_emplacement->hauteur}}"
               style="background-color:#{{$_zone->_ref_emplacement->color}};height:{{$_zone->_ref_emplacement->hauteur*60}}px">

            <form name="Emplacement-{{$_zone->_id}}" action="" method="post">
              {{mb_key object=$_zone->_ref_emplacement}}
              {{mb_class object=$_zone->_ref_emplacement}}
              <input type="hidden" name="del" value="0" />
              <input type="hidden" name="chambre_id" value="{{$_zone->chambre_id}}" />
              <input type="hidden" name="plan_x" value="" />
              <input type="hidden" name="plan_y" value="" />
            </form>
            <a href="#" onclick="PlanEtage.show('{{$_zone->chambre_id}}');">{{$_zone}}</a>
            <div class="compact"
                 style="display: block;white-space: pre-wrap;color:black;">{{$_zone->caracteristiques|truncate:60}}</div>
          </div>
        </td>
      {{else}}
        <td data-x="{{$x}}" data-y="{{$y}}" class="conteneur-chambre"></td>
      {{/if}}
    {{/foreach}}
    {{/foreach}}
  </tr>
</table>