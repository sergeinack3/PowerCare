{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  window.onMergeComplete = Ampli.refreshList;
</script>

<table class="tbl">
  <tr>
    <th class="narrow">
      <button class="merge notext" onclick="Ampli.merge();">{{tr}}Merge{{/tr}}</button>
    </th>
    <th class="halfPane">
      {{mb_title class=CAmpli field=libelle}}
    </th>
    <th>
      {{mb_title class=CAmpli field=unite_rayons_x}}
    </th>
    <th>
      {{mb_title class=CAmpli field=unite_pds}}
    </th>
  </tr>

  {{foreach from=$amplis item=_ampli}}
    <tr {{if !$_ampli->actif}}class="hatching opacity-50"{{/if}}>
      <td>
        <input type="checkbox" class="merge_ampli" value="{{$_ampli->_id}}" />

        <button class="edit notext" onclick="Ampli.edit('{{$_ampli->_id}}')"></button>
      </td>
      <td>
        {{mb_value object=$_ampli field=libelle}}
      </td>
      <td>
        {{mb_value object=$_ampli field=unite_rayons_x}}
      </td>
      <td>
        {{mb_value object=$_ampli field=unite_pds}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td>
        {{tr}}CAmpli.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
