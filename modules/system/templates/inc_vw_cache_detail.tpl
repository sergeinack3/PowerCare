{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$detail item=_entry key=_key}}
  <tr>
    <td colspan="2">
      {{if $type !== 'opcode'}}
      <a href="#1" onclick="CacheViewer.showKeyDetail(this);"
         data-type="{{$type}}"
         data-key="{{$_entry.key}}">
        {{if $_entry.key|strpos:'session' !== false}}{{$_key|truncate:12}}{{else}}{{$_key}}{{/if}}
      </a>
      {{else}}
        {{$_key}}
      {{/if}}
    </td>
    <td>{{$_entry.size|decabinary}}</td>
    <td>
      {{if $_entry.ctime}}
        {{$_entry.ctime}} ({{$_entry.ctime|rel_datetime}})
      {{/if}}
    </td>
    <td>
      {{if $_entry.mtime}}
        {{$_entry.mtime}} ({{$_entry.mtime|rel_datetime}})
      {{/if}}
    </td>
    <td>
      {{if $_entry.atime}}
        {{$_entry.atime}} ({{$_entry.atime|rel_datetime}})
      {{/if}}
    </td>
    <td>{{$_entry.hits}}</td>
    <td>{{$_entry.ttl}}</td>
    <td>{{$_entry.ref_count}}</td>
  </tr>
{{foreachelse}}
  <tr>
    <td colspan="9">
      <div class="warning">{{tr}}ISharedMemory-msg-Key is empty{{/tr}}</div>
    </td>
  </tr>
{{/foreach}}