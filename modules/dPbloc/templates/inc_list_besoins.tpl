{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=usage value=0}}

{{foreach from=$besoins item=_besoin}}
  {{assign var=type_ressource value=$_besoin->_ref_type_ressource}}
  {{assign var=_usage value=$_besoin->_ref_usage}}
  <tr>
    <td style="width: 12px; background: #{{$_besoin->_color}}">
    </td>
    <td class="text" {{if $type == "operation_id"}}style="width: 50%"{{/if}}>
      <div style="float: right">
        <button type="button" class="trash notext" {{if $usage || $_usage->_id}}disabled{{/if}} title="{{tr}}Delete{{/tr}}"
          {{if $object_id}}
            onclick="onDelBesoin('{{$_besoin->_id}}', '{{$type_ressource->libelle|smarty:nodefaults|JSAttribute}}')"
          {{else}}
            onclick="delBesoinNonStored('{{$type_ressource->_id}}')"
          {{/if}}></button>
        {{if $type == "operation_id"}}
          <button type="button" class="modele_etiquette notext"
            onclick="showPlanning('{{$_besoin->type_ressource_id}}', '{{$object_id}}', '{{$_usage->_id}}', '{{$_besoin->_id}}', '{{$usage}}')" title="Planning"></button>
        {{/if}}
      </div>
      <strong>
        {{$type_ressource->libelle}}
      </strong>
    </td>
    {{if $type == "operation_id"}}
      <td {{if !$_usage->_id}}class="empty"{{/if}}>
        {{if $_usage->_id}}
          <form name="delUsage{{$_usage->_id}}" method="post">
            <input type="hidden" name="m" value="bloc" />
            <input type="hidden" name="dosql" value="do_usage_ressource_aed" />
            <button type="button" {{if !$usage}}disabled{{/if}} class="trash notext" style="float: right;"
              onclick="onDelUsage('{{$_usage->_id}}', '{{$_usage->_ref_ressource}}')"></button>
          </form>
          {{$_usage->_ref_ressource}}
        {{else}}
          Non pourvu
        {{/if}}
      </td>
    {{/if}}
  </tr>
{{foreachelse}}
  <tr>
    <td class="empty">
      {{tr}}CBesoinRessource.none{{/tr}}
    </td>
  </tr>
{{/foreach}}