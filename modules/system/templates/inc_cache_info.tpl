{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main form">
  <col style="width: 120px;" />

  <tr>
    <th colspan="3" class="category">Global</th>
  </tr>

  <tr>
    <th>Engine</th>
    <td>{{$info.engine}} {{$info.version}}</td>
  </tr>
  <tr>
    <th>Hits</th>
    <td>{{$info.hit_rate}}% (<small>{{$info.hits}}</small>)</td>
  </tr>
  <tr>
    <th>Misses</th>
    <td>{{$info.misses}}</td>
  </tr>
  <tr>
    <th>Total</th>
    <td>{{$info.total|decabinary}}</td>
  </tr>
  <tr>
    <th>Used</th>
    <td>{{$info.used|decabinary}}</td>
  </tr>
</table>

<table class="main form">
  <col style="width: 120px;" />
  <col style="width: 80px;" />
  <tr>
    <th colspan="9" class="category">Entrées en cache</th>
  </tr>
  <tr style="font-weight: bold; border-bottom: 1px solid #ddd;">
    <th>Total</th>
    <td>{{$info.instance_count}}</td>
    <td>{{$info.instance_size|decabinary}}</td>
    <td>ctime</td>
    <td>mtime</td>
    <td>atime</td>
    <td>hits</td>
    <td>TTL</td>
    <td>Ref count</td>
  </tr>
  {{foreach from=$info.entries_by_prefix key=_key item=_entry}}
    <tbody>
      <tr>
        <th>
          <a href="#1" class="trigger-detail"
             onclick="CacheViewer.showDetail(this)"
             data-prefix="{{$_key}}"
             data-type="{{$type}}">
            {{$_key}}
          </a>
        </th>
        <td>{{$_entry.count}}</td>
        <td colspan="7">{{$_entry.size|decabinary}}</td>
      </tr>
    </tbody>
    <tbody class="cache-detail"></tbody>
  {{/foreach}}
</table>