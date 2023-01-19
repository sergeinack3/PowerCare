{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=compteRendu script=document ajax=true}}

<form id="select_cr" method="post">
  <table class="tbl">
    <tr>
      <th class="title" colspan="3">
        {{tr}}Choose{{/tr}} {{tr}}CCompteRendu|pl{{/tr}}
      </th>
    </tr>
    <tr>
      <th>{{tr}}common-Name{{/tr}}</th>
      <th>{{tr}}common-Context{{/tr}}</th>
      <th id="selected_doc">{{tr}}Selected{{/tr}}</th>
    </tr>
    {{foreach from=$modeles item=_modele}}
      <tr>
        <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_modele->_guid}}')">
            {{$_modele}}
          </span>
        </td>
        <td>
          {{tr}}{{$_modele->object_class}}{{/tr}}
        </td>
        <td class="selected_doc">
          {{foreach from=$modeles_to_pack item=_modele_to_pack}}
            {{if $_modele_to_pack->modele_id == $_modele->_id}}
              <input type="checkbox" class="cr" name="{{$_modele->_id}}"
                     {{if $_modele_to_pack->is_selected}}checked{{/if}}>
            {{/if}}
          {{/foreach}}
        </td>
      </tr>
    {{/foreach}}
    <td class="button" colspan="3">
      <button type="button" class="modify" onclick="Document.createUnmergePack('{{$pack_id}}', '{{$object_id}}', '{{$object_class}}')">{{tr}}Save{{/tr}}</button>
    </td>
  </table>
</form>
