{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  {{foreach from=$prestations item=_prestations key=object_class}}
    <tr>
      <th class="title" colspan="4">{{tr}}{{$object_class}}.all{{/tr}}</th>
    </tr>
    <tr>
      <th class="category">{{mb_label class=$object_class field=nom}}</th>
      <th class="category narrow">{{mb_label class=$object_class field=type_hospi}}</th>
      <th class="category narrow">{{tr}}CPrestationExpert-type_pec{{/tr}}</th>
      <th class="category narrow">Nb. d'items</th>
    </tr>
    {{foreach from=$_prestations item=_prestation}}
      <tr id="prestation_{{$_prestation->_guid}}" class="prestation {{if $prestation_guid == $_prestation->_guid}}selected{{/if}} {{if !$_prestation->actif}}hatching opacity-60{{/if}}">
        <td>
          <a href="#1"
             onclick="Prestation.updateSelected('{{$_prestation->_guid}}', 'prestation'); Prestation.editPrestation('{{$_prestation->_id}}', '{{$_prestation->_class}}')">
            {{$_prestation->nom}}
          </a>
        </td>
        <td>
          {{if $_prestation->type_hospi}}
            {{mb_value object=$_prestation field=type_hospi}}
          {{else}}
            {{tr}}All{{/tr}}
          {{/if}}
        </td>
        <td>
          {{if $_prestation->M}}
            M
          {{/if}}
          {{if $_prestation->C}}
            C
          {{/if}}
          {{if $_prestation->O}}
            O
          {{/if}}
          {{if $_prestation->SSR}}
            SSR
          {{/if}}
        </td>
        <td>{{$_prestation->_count_items}}</td>
      </tr>
      {{foreachelse}}
      <tr>
        <td colspan="4" class="empty">{{tr}}{{$object_class}}.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  {{/foreach}}
</table>
