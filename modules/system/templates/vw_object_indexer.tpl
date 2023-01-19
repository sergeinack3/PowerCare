{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=object_indexer ajax=true}}

<table class="main tbl">
  <tr>
    <th class="narrow"></th>
    <th>{{tr}}CObjectIndexer-name{{/tr}} <input type="text" oninput="ObjectIndexer.filter(this, 'name');" /></th>
    <th>{{tr}}CObjectIndexer-class{{/tr}} <input type="text" oninput="ObjectIndexer.filter(this, 'class');" /></th>
    <th>{{tr}}CObjectIndexer-creation_datetime{{/tr}}</th>
    <th title="{{tr}}CObjectIndexer-build_time-desc{{/tr}}">{{tr}}CObjectIndexer-build_time{{/tr}}</th>
    <th>{{tr}}CObjectIndexer-total_size{{/tr}}</th>
    <th>{{tr}}CObjectIndexer-nb_keys{{/tr}}</th>
    <th>{{tr}}CObjectIndexer-nb_objects{{/tr}}</th>
    <th>{{tr}}CObjectIndexer-average_object_count_by_key{{/tr}}</th>
    <th class="narrow"></th>
  </tr>

  {{foreach from=$indexes_infos item=_index_infos}}
    <tr class="_object-indexer">
      <td>
        <button class="search notext" onclick="ObjectIndexer.displayIndex('{{$_index_infos.index_key}}')"></button>
      </td>
      <td class="_object-indexer_name">{{$_index_infos.name}}</td>
      <td class="_object-indexer_class">{{$_index_infos.class}}</td>
      <td style="text-align: center;">{{mb_ditto name=creation_datetime value=$_index_infos.creation_datetime}}</td>
      <td>{{$_index_infos.build_time}}</td>
      <td>{{$_index_infos.total_size}}</td>
      <td>{{$_index_infos.nb_keys}}</td>
      <td>{{$_index_infos.nb_objects}}</td>
      <td>{{$_index_infos.average_object_count_by_key}}</td>
      <td>
        <button onclick="ObjectIndexer.remove('{{$_index_infos.name}}')" class="trash notext" type="button"></button>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="10">{{tr}}CObjectIndexer.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
