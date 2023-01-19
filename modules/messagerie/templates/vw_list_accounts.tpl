{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=messagerie script=UserEmail}}
{{mb_script module=system script=exchange_source}}


<table class="tbl">
  <tr>
    <th>Compte</th>
    <th>Libellé</th>
    <th>Hôte</th>
    <th class="narrow">Type</th>
    <th class="narrow">Nb Mails</th>
  </tr>
  {{foreach from=$sources item=_source}}
    {{assign var=class value=""}}
    {{if !$_source->active}}
      {{assign var=class value="hatching"}}
    {{/if}}
    <tr>
      <td class="{{$class}}">
        <button class="edit notext" onclick="ExchangeSource.editSource('{{$_source->_guid}}', true, '{{$_source->name}}', '')">{{tr}}Edit{{/tr}}</button>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_source->_guid}}');">
          {{$_source}}
        </span>
      </td>
      <td class="{{$class}}">
        {{$_source->libelle}}
      </td>
      <td class="{{$class}}">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_source->_ref_metaobject->_guid}}');">
          {{$_source->_ref_metaobject}}
        </span>
      </td>
      <td class="{{$class}}">
        {{$_source->type}}
      </td>
      <td class="{{$class}} {{if !$_source->_nb_ref_mails}}empty{{/if}}">
        {{$_source->_nb_ref_mails}}
      </td>
    </tr>
  {{/foreach}}
</table>
