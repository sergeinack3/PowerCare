{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl me-margin-top-0">
  <tr id="list_files-trigger">
    <th class="category" colspan="3">
      {{$count_object}} {{tr}}CFile-file(s{{/tr}}

      <script>
        Main.add(function () {
          new PairEffect("list_files", { 
            bStoreInCookie: true
          });
        });
      </script>
    </th>
  </tr>
  
 <tbody id="list_files" style="display: none;">
{{if $count_object}} 
{{foreach from=$object->_ref_files item=_file}}
  <tr>
    <td>
      <a href="#" class="action" 
         onclick="File.popup('{{$object->_class}}','{{$object->_id}}','{{$_file->_class}}','{{$_file->_id}}');"
         onmouseover="ObjectTooltip.createEx(this, '{{$_file->_guid}}', 'objectView')">
        {{$_file}}
      </a>
    </td>
  </tr>
{{/foreach}}
{{/if}}
</table>



  