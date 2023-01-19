{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title">{{tr}}CMotifSFMU.search{{/tr}}</th>
  </tr>
  <tr>
    <td class="button">
      <select onchange="CCirconstance.displayMotifFromCategorie(this.value)">
        <option value="">{{tr}}CMotifSFMU.Select-categorie{{/tr}}</option>
        {{foreach from=$categories item=_categorie}}
          <option value="{{$_categorie.categorie}}">{{$_categorie.categorie}}</option>
        {{/foreach}}
      </select>
    </td>
  </tr>
</table>
<br/>
<div id="motif_sfmu_by_category">
</div>