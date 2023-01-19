{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  window.onMergeComplete = LaboBacterio.refreshList;
</script>

<table class="tbl">
  <tr>
    <th class="narrow">
      <button class="merge notext" onclick="LaboBacterio.merge();">{{tr}}Merge{{/tr}}</button>
    </th>
    <tH>
      {{mb_title class=CLaboratoireBacterio field=libelle}}
    </tH>
    <th>
      {{mb_title class=CLaboratoireBacterio field=adresse}}
    </th>
    <th>
      {{mb_title class=CLaboratoireBacterio field=tel}}
    </th>
    <th>
      {{mb_title class=CLaboratoireBacterio field=fax}}
    </th>
    <th>
      {{mb_title class=CLaboratoireBacterio field=mail}}
    </th>
  </tr>
  
  {{foreach from=$laboratoires_bacterio item=_laboratoire_bacterio}}
    <tr {{if !$_laboratoire_bacterio->actif}}class="hatching opacity-50"{{/if}}>
      <td>
        <input type="checkbox" class="merge_labo_bacterio" value="{{$_laboratoire_bacterio->_id}}" />
        
        <button class="edit notext" onclick="LaboBacterio.edit('{{$_laboratoire_bacterio->_id}}')"></button>
      </td>
      <td>
        {{mb_value object=$_laboratoire_bacterio field=libelle}}
      </td>
      <td>
        {{mb_value object=$_laboratoire_bacterio field=_adresse}}
      </td>
      <td>
        {{mb_value object=$_laboratoire_bacterio field=tel}}
      </td>
      <td>
        {{mb_value object=$_laboratoire_bacterio field=fax}}
      </td>
      <td>
        {{mb_value object=$_laboratoire_bacterio field=mail}}
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td>
        {{tr}}CLaboratoirebacterio.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
