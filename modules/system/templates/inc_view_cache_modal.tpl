{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="cache-modal">
  <div class="cache-modal-title">
      {{tr}}CacheManager-msg-confirm delete{{/tr}}
  </div>
  <div class="cache-modal-content">
      {{if $cache}}
        <div class="cache-modal-content-text">
            {{if $module}}
              - {{tr}}CacheManager-msg-for module{{/tr}}
              <b>{{$module}}</b>
              </br>({{$cache|stripslashes}})
              </br>
            {{else}}
              - {{tr}}CacheManager-cache_values.{{$cache}}{{/tr}}</br>
            {{/if}}
        </div>
      {{/if}}
      {{if $module && $keys}}
        <div class="cache-modal-content-text">
          </br>- {{tr}}CacheManager-msg-concerned keys{{/tr}} </br>
            {{foreach from="|"|explode:$keys item=key}}
              { <b>{{$key}}</b> }
            {{/foreach}}
        </div>
      {{/if}}
      {{if $target}}
        <div class="cache-modal-content-text">
          </br>- {{tr}}CacheManager-msg-on targets{{/tr}}
          <b>{{$target}}</b>
          </br>
        </div>
      {{/if}}
  </div>
  <div class="cache-modal-actions">
    <button class="me-tertiary" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
    <button class="me-primary"
            onclick="CacheManager.clear('{{$cache}}', '{{$target}}', '{{$layer}}');">{{tr}}Confirm{{/tr}}</button>
  </div>
</div>

{{* CACHE CLEAR RESULTS TABLE *}}
<table id="CacheManagerOutputsTab" class="tbl" style="margin: 0;">
  <tbody>
  <tr>
    <th class="section" colspan="2">
        {{tr}}common-Message|pl{{/tr}}
    </th>
  </tr>
  <tr>
    <td id="CacheManagerOutputs">
      <div class="info">{{tr}}CacheManager-msg-cache_logs_will_be_displayed_here{{/tr}}</div>
    </td>
  </tr>
  </tbody>
</table>
</div>
