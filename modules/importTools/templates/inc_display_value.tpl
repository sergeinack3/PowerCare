{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if strpos($col_info.Type,'blob') === false}}
  {{if $value !== null}}
    {{if $col_info.is_text}}
      <code style="background: rgba(180,180,255,0.3)">
        <span class="search">
          {{if $tooltip}}
            {{$value|spancate}}
          {{else}}
            {{$value}}
          {{/if}}
        </span>
      </code>
    {{else}}
      {{if $col_info.foreign_key}}
        {{assign var=_fk value="."|explode:$col_info.foreign_key}}
        <span onmouseover="ObjectTooltip.createEx(this, null, 'import_tools', {dsn: '{{$dsn}}', table: '{{$_fk.0}}', start: 0, where_column: '{{$_fk.1}}', where_value: '{{$value}}', tooltip: 1});">
          <span class="search">{{$value}}</span>
        </span>
      {{else}}
        <span class="search">{{$value}}</span>
      {{/if}}
    {{/if}}
  {{else}}
    <span class="empty" style="color: #ccc;">NULL</span>
  {{/if}}
{{else}}
  {{if $value|strlen === 0}}
    <span class="empty">[Empty blob]</span>
  {{else}}
    {{assign var=_encoded value=$value|smarty:nodefaults|base64_encode}}

    <a href="data:image/png;base64,{{$_encoded}}" target="_blank" style="display: inline-block;"
       onmouseover="ObjectTooltip.createDOM(this, DOM.img({src: 'data:image/png;base64,{{$_encoded}}', style: 'max-width: 400px'}))">
      PNG
    </a>
    <a href="data:image/jpeg;base64,{{$_encoded}}" target="_blank" style="display: inline-block;"
       onmouseover="ObjectTooltip.createDOM(this, DOM.img({src: 'data:image/jpeg;base64,{{$_encoded}}', style: 'max-width: 400px'}))">
      JPEG
    </a>
    <a href="data:application/pdf;base64,{{$_encoded}}" target="_blank" style="display: inline-block;">
      PDF
    </a>
    <a href="data:application/rtf;base64,{{$_encoded}}" target="_blank" style="display: inline-block;">
      RTF
    </a>
    <a href="data:text/plain;base64,{{$_encoded}}" target="_blank" style="display: inline-block;">
      [Blob {{$value|strlen}}o]
    </a>
  {{/if}}
{{/if}}