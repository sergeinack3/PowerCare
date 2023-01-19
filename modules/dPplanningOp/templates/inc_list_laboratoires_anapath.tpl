{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  window.onMergeComplete = LaboAnapath.refreshList;
</script>

<table class="tbl">
  <tr>
    <th class="narrow">
      <button class="merge notext" onclick="LaboAnapath.merge();">{{tr}}Merge{{/tr}}</button>
    </th>
    <tH>
      {{mb_title class=CLaboratoireAnapath field=libelle}}
    </tH>
    <th>
      {{mb_title class=CLaboratoireAnapath field=adresse}}
    </th>
    <th>
      {{mb_title class=CLaboratoireAnapath field=tel}}
    </th>
    <th>
      {{mb_title class=CLaboratoireAnapath field=fax}}
    </th>
    <th>
      {{mb_title class=CLaboratoireAnapath field=mail}}
    </th>
  </tr>

  {{foreach from=$laboratoires_anapath item=_laboratoire_anapath}}
    <tr {{if !$_laboratoire_anapath->actif}}class="hatching opacity-50"{{/if}}>
      <td>
        <input type="checkbox" class="merge_labo_anapath" value="{{$_laboratoire_anapath->_id}}" />

        <button class="edit notext" onclick="LaboAnapath.edit('{{$_laboratoire_anapath->_id}}')"></button>
      </td>
      <td>
        {{mb_value object=$_laboratoire_anapath field=libelle}}
      </td>
      <td>
        {{mb_value object=$_laboratoire_anapath field=_adresse}}
      </td>
      <td>
        {{mb_value object=$_laboratoire_anapath field=tel}}
      </td>
      <td>
        {{mb_value object=$_laboratoire_anapath field=fax}}
      </td>
      <td>
        {{mb_value object=$_laboratoire_anapath field=mail}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td>
        {{tr}}CLaboratoireAnapath.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
