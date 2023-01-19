{{*
* @package Mediboard\Developpement
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPdeveloppement script=route_creator ajax=true}}

<form name="search" action="?" method="get" onsubmit="return onSubmitFormAjax(this, null, 'routes')">
  <input type="hidden" name="m" value="developpement" />
  <input type="hidden" name="a" value="ajax_list_routes" />

  <button type="button" class="new notext" onclick="RouteCreator.showCreateRoute()">{{tr}}Create{{/tr}}</button>

  <input type="text" placeholder="Route, Module, Controller ..." size="50" name="filter" id="filter" value="{{$filter}}">
  <button type="button" onclick="$V(this.form.filter, '');" class="erase notext" title="Vider le champ"></button>

  <button type="submit" class="search"> Rechercher</button>
</form>
<table class="tbl main">
  <thead>
  <th>Route</th>
  <th>Module</th>
  <th>Controller</th>
  <th>Action</th>
  <th>Path</th>
  <th>Method</th>
  </thead>
    {{foreach from=$route_display item=_datas key=_name}}
  <tr onclick="new Url('developpement', 'ajax_details_route')
    .addParam('route', '{{$_name}}')
    .requestModal(800,600, {title : '{{$_name}}' });">
    <td>{{$_name}}</td>
    <td>{{$_datas.module}}</td>
    <td>{{$_datas.controller}}</td>
    <td>{{$_datas.action}}</td>
    <td>{{$_datas.path}}</td>
    <td>{{$_datas.method}}</td>
  </tr>
  {{/foreach}}
</table>