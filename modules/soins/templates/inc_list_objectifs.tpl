{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $object && ($objectifs_list|@count || $objectifs|@count)}}
  <fieldset>
    <legend>Objectifs liés - Cible: {{$object->_view|spancate:30:"...":true}}</legend>

    <select id="objectif_soin_id" method="post"
            onchange="if (this.value == '') return; submitCibleObjectif(this.value, $V(getForm('editTrans').object_class), $V(getForm('editTrans').object_id));">
      <option value="">Ajouter un objectif</option>
      {{foreach from=$objectifs_list item=_objectif}}
        <option value="{{$_objectif->_id}}">{{$_objectif->libelle}}</option>
      {{/foreach}}
    </select>

    {{if $objectifs|@count}}
      <ul>
      {{foreach from=$objectifs key=objectif_soin_cible_id item=_objectif}}
        <li>
          <button type="button" class="trash notext"
                  data-objectif_soin_id="{{$_objectif->objectif_soin_id}}" data-libelle="{{$_objectif->_view}}"
                  onclick="removeObjectif('{{$objectif_soin_cible_id}}');">{{tr}}Delete{{/tr}}</button>{{$_objectif->_view}}
        </li>
      {{/foreach}}
      </ul>
    {{/if}}
  </fieldset>
{{/if}}