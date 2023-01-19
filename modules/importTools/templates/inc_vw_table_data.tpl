{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=display_all value=1}}

{{if $dsn === 'std' || $dsn === 'slave'}}
  {{assign var=display_all value=0}}
{{/if}}

{{if $tooltip || $line_compare}}
  {{mb_include module=importTools template=inc_vw_single_line}}
{{else}}
    <h3>
      {{$table}}

      {{if  $display_all}}
        <select onchange="DatabaseExplorer.displayTableData('{{$dsn}}', '{{$table}}', 0, $V(this))">
          {{foreach from=$counts item=_count}}
            <option value="{{$_count}}" {{if $count == $_count}}selected{{/if}}>{{$_count}}</option>
          {{/foreach}}
        </select>

        &mdash;

        <span style="font-weight: normal">
          <label>
            Classe
            <select onchange="DatabaseExplorer.saveTableInfo('{{$dsn}}', '{{$table}}', 'class', $V(this))">
              <option value="">&ndash;</option>
              {{foreach from='Ox\Import\ImportTools\CImportTools'|static:classes item=_class}}
                <option value="{{$_class}}" {{if $_class == $table_info.class}}selected{{/if}}>{{tr}}{{$_class}}{{/tr}}</option>
              {{/foreach}}
            </select>
          </label>

          <label>
            Titre <input type="text" name="title" size="40" value="{{$table_info.title}}"
                         onchange="DatabaseExplorer.saveTableInfo('{{$dsn}}', '{{$table}}', 'title', $V(this))"/>
          </label>

          <label>
            <input type="checkbox" name="hidden" {{if !$table_info.display}}checked{{/if}} value="1"
                   onclick="DatabaseExplorer.saveTableInfo('{{$dsn}}', '{{$table}}', 'display', this.checked?'no':'yes')"/>
            Caché
          </label>

          {{if $hidden_columns}}
            <label>
              <input type="checkbox" name="col-hidden" onclick="DatabaseExplorer.toggleHiddenColumns(this);"/>
                {{tr}}common-action-Display hidden column|pl{{/tr}} <span class="compact">(x {{$hidden_columns}})</span>
            </label>
          {{/if}}
        </span>
      {{/if}}
    </h3>

  <script>
    changePage = function (start) {
      DatabaseExplorer.displayTableData('{{$dsn}}', '{{$table}}', start, '{{$count}}');
    }
  </script>

  <div style="max-width: 600px;">
    {{mb_include module=system template=inc_pagination total=$total step=$count current=$start change_page=changePage jumper=10}}
  </div>

  {{mb_include module=importTools template=inc_vw_table_lines}}
{{/if}}