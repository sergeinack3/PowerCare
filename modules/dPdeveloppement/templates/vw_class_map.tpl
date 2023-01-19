{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


<form name="search" action="?" method="get" onsubmit="return onSubmitFormAjax(this, null, 'map')">
  <input type="hidden" name="m" value="developpement" />
  <input type="hidden" name="a" value="vw_class_map" />

  <input type="text" placeholder="Classes ..." size="50" name="filter" id="filter" value="{{$filter}}">
  <button type="button" onclick="$V(this.form.filter, '');" class="erase notext" title="Vider le champ"></button>

  <button type="submit" class="search"> Rechercher</button>
</form>

<table class="tbl main">
  <tr>
    <th width="50%">Classes ({{$maps|@count}})</th>
    <th width="50%">Files</th>
  </tr>
  {{foreach from=$maps item=_map key=class}}
    <tr onclick="new Url('developpement', 'vw_class_map_detail')
      .addParam('class', '{{$class|addslashes}}')
      .requestModal(800,600, {title : '{{$_map.short_name}}' });">
      <td><b>{{$class}}</b></td>
      <td>{{$_map.file_relative}}</td>
    </tr>
  {{/foreach}}
</table>