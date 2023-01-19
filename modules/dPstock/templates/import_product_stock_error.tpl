{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{if $errors.nofile}}
  <div class="small-error">
    <span>{{tr}}common-error-No file found.{{/tr}}</span>
  </div>
{{else}}
  {{if $errors.ext|@count}}
    <div class="small-error">
      <span>{{tr}}CProductStockLocation-format_invalid{{/tr}}</span>
      <ul>
        {{foreach from=$errors.ext item=_error}}
          <li>{{$_error}}</li>
        {{/foreach}}
      </ul>
    </div>
  {{/if}}
  {{if $errors.nodata|@count}}
    <div class="small-error">
      <span>{{tr}}CProductStockLocation-import-no-data-found{{/tr}}</span>
      <ul>
        {{foreach from=$errors.nodata item=_error}}
          <li>{{$_error}}</li>
        {{/foreach}}
      </ul>
    </div>
  {{/if}}
  {{if $total_errors_lines !== "0"}}
    {{if $errors.lines.count|@count}}
      <div class="small-warning">
        <span>{{tr}}CProductStockLocation-count-error{{/tr}}</span>
        <ul>
          {{foreach from=$errors.lines.count item=_error}}
            <li>{{$_error}}</li>
          {{/foreach}}
      </div>
      </ul>
    {{/if}}
    {{if $errors.lines.type|@count}}
      {{foreach from=$errors.lines.count item=_line}}
      {{/foreach}}
      <div class="small-warning">
        <span>{{tr}}CProductStockLocation-type-error{{/tr}}</span>
        <ul>
          {{foreach from=$errors.lines.type item=_error}}
            <li>{{$_error}}</li>
          {{/foreach}}
        </ul>
      </div>
    {{/if}}
    {{if $errors.lines.libelle|@count}}
      <div class="small-warning">
        <span>{{tr}}CProductStockLocation-libelle-error{{/tr}}</span>
        <ul>
          {{foreach from=$errors.lines.libelle item=_error}}
            <li>{{$_error}}</li>
          {{/foreach}}
        </ul>
      </div>
    {{/if}}
    {{if $errors.lines.actif|@count}}
      <div class="small-warning">
        <span>{{tr}}CProductStockLocation-actif-error{{/tr}}</span>
        <ul>
          {{foreach from=$errors.lines.actif item=_error}}
            <li>{{$_error}}</li>
          {{/foreach}}
        </ul>
      </div>
    {{/if}}
  {{/if}}
{{/if}}
