{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th colspan="4">
      {{$count}} Fichiers
    </th>
  </tr>
  <tr>
    <th>ID</th>
    <th>{{mb_title class=CFile field=file_name}}</th>
    <th>{{mb_title class=CFile field=file_real_filename}}</th>
    <th>{{mb_title class=CFile field=_file_path}}</th>
  </tr>

  {{foreach from=$list_files item=_file}}
    <tr>
      <td>{{$_file.0}}</td>
      <td>{{$_file.1}}</td>
      <td>{{$_file.2}}</td>
      <td>{{$_file.3}}</td>
    </tr>

  {{foreachelse}}

    <tr>
      <td class="empty" colspan="4">
        Pas de purge lancée
      </td>
    </tr>
  {{/foreach}}
</table>