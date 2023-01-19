{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th>Type d'objet lié</th>
    <th>Nombre FS == DB</th>
    <th>Nombre FS != DB</th>
    <th>Nombre FS == DB == 0</th>
    <th>Nombre DB != (FS == 0)</th>
  </tr>

  {{foreach from=$infos key=_object_class item=_infos}}
      {{assign var=count_ok value=$_infos.ok|@count}}
      {{assign var=count_nok value=$_infos.nok|@count}}
      {{assign var=count_empty_ok value=$_infos.empty_ok|@count}}
      {{assign var=count_empty_nok value=$_infos.empty_nok|@count}}
      <tr>
        <td>{{$_object_class}} : {{tr}}{{$_object_class}}{{/tr}}</td>
        <td>{{$_infos.count_ok|number_format:0:'':' '}}</td>
        <td>{{$_infos.count_nok|number_format:0:'':' '}}</td>
        <td>{{$_infos.count_empty_ok|number_format:0:'':' '}}</td>
        <td>{{$_infos.count_empty_nok|number_format:0:'':' '}}</td>
      </tr>
  {{/foreach}}
</table>