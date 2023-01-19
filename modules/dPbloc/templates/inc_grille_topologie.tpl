{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=module_etiquette_pat   value=bloc}}
{{mb_default var=template_etiquette_pat value=inc_patient_placement}}

{{foreach from=$grille item=ligne}}
  <tr>
    {{foreach from=$ligne item=_zone}}
      {{if $_zone != "0" && $_zone->_ref_emplacement_salle}}
        {{assign var=emplacement_salle value=$_zone->_ref_emplacement_salle}}
        <td
          data-salle_id="{{$_zone->_id}}"
          rowspan="{{$emplacement_salle->hauteur}}"
          colspan="{{$emplacement_salle->largeur}}" class="salle"
          data-bloc_id="{{$_zone->bloc_id}}"
          data-form_name="save_room_op"
          style="background-color:#{{$emplacement_salle->color}}{{if $IS_MEDIBOARD_EXT_DARK}}60{{/if}};">
          <small class="shadow" style="background-color:#{{$emplacement_salle->color}};">{{$_zone}}</small>
          {{assign var=salle_id   value=$_zone->salle_id}}
          {{if isset($listOperations.$salle_id|smarty:nodefaults)}}
            {{foreach from=$listOperations.$salle_id item=_operation}}
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
  {{if $exist_plan == 1}}
    <div class="small-warning">
      {{tr var1=$name_grille}}CBlocOperatoire-msg-In order to have access to the functionalities, please configure the operating plan floor plan %s{{/tr}}
    </div>
  {{else}}
    <div class="small-warning">
      {{tr var1=$name_grille}}CBlocOperatoire-msg-In order to access the functionalities, please indicate a block %s{{/tr}}
    </div>
  {{/if}}
{{/foreach}}
