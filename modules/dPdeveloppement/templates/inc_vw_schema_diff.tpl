{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=diff value=$audit->getSchemaDiff()}}

{{if !$diff|instanceof:'Ox\Mediboard\Developpement\SchemaDiff'}}
  <div class="small-error">No diff</div>
  {{mb_return}}
{{/if}}

<style>
  table.hide-no-error tr.audit-no-error {
    display: none;
  }
</style>

{{if $diff->getSchemaErrorsCount()}}
  <div class="small-error">{{tr var1=$diff->getSchemaErrorsCount()}}common-error-Database in error: %d|pl{{/tr}}</div>
{{else}}
  <div class="small-info">{{tr}}common-msg-No database error{{/tr}}</div>
{{/if}}

{{if $diff->getTableErrorsCount()}}
  <div class="small-error">{{tr var1=$diff->getTableErrorsCount()}}common-error-Table in error: %d|pl{{/tr}}</div>
{{else}}
  <div class="small-info">{{tr}}common-msg-No table error{{/tr}}</div>
{{/if}}

{{if $diff->getDbErrorsSigma()}}
  <div class="small-error">
    <ul>
      {{foreach from=$diff->getDbErrorsSigma() key=_db item=_sigma}}
        <li>
          <strong>{{$_db}}</strong> : &#931; {{$_sigma}}
        </li>
      {{/foreach}}
    </ul>
  </div>
{{/if}}

<script>
  Main.add(function () {
    Control.Tabs.create('schema-diff-tabs', true);
  });
</script>

<table class="main layout hide-no-error">
  <col style="width: 10%;" />

  <tr>
    <td style="white-space: nowrap; vertical-align: top;">
      <label>
        <input type="checkbox" onclick="this.up('table').toggleClassName('hide-no-error')" />

        {{tr}}common-action-Display all{{/tr}}
      </label>

      <ul id="schema-diff-tabs" class="control_tabs_vertical small">
        {{foreach from=$diff->getSchemas() item=_db}}
          <li>
            <a href="#schema-diff-{{$_db}}" {{if $diff->isDbInError($_db)}} class="wrong" {{/if}}>
              {{$_db}}
            </a>
          </li>
        {{/foreach}}
      </ul>
    </td>

    <td>
      {{foreach from=$diff->getSchemas() item=_db}}
        {{assign var=first_target value=$audit->getFirstTarget()}}
        {{assign var=second_target value=$audit->getSecondTarget()}}

        <div id="schema-diff-{{$_db}}" style="display: none;">
          <table class="main tbl">
            <tr>
              <th>{{tr}}common-Table{{/tr}}</th>

              <th>{{$first_target->getHostname()}}</th>

              <th>{{$second_target->getHostname()}}</th>
            </tr>

            {{foreach from=$diff->getTables($_db) item=_table}}
              {{assign var=in_error value=false}}

              {{if $diff->isTableMissing($_db, $_table) || $diff->tableHasDifference($_db, $_table)}}
                {{assign var=in_error value=true}}
              {{/if}}

              <tr class="{{if $in_error}} audit-error {{else}} audit-no-error {{/if}}">
                <th class="section">
                  {{$_table}}
                </th>

                {{if !$diff->doesTableExistForFirstHost($_db, $_table)}}
                  <td class="error"></td>
                {{else}}
                  {{assign var=_value value=$diff->getTableCountForFirstHost($_db, $_table)}}

                  {{if !$_value && $_value !== '0'}}
                    <td style="text-align: right;" {{if !$_value && $_value !== '0'}} class="error" {{/if}}>
                      {{$_value}}
                    </td>
                  {{else}}
                    <td style="text-align: right;" {{if $diff->tableHasDifference($_db, $_table)}} class="warning" {{/if}}>
                      {{$_value|integer}}
                    </td>
                  {{/if}}
                {{/if}}

                {{if !$diff->doesTableExistForSecondHost($_db, $_table)}}
                  <td class="error"></td>
                {{else}}
                  {{assign var=_value value=$diff->getTableCountForSecondHost($_db, $_table)}}

                  {{if !$_value && $_value !== '0'}}
                    <td style="text-align: right;" {{if !$_value && $_value !== '0'}} class="error" {{/if}}>
                      {{$_value}}
                    </td>
                  {{else}}
                    <td style="text-align: right;" {{if $diff->tableHasDifference($_db, $_table)}} class="warning" {{/if}}>
                      {{$_value|integer}}
                    </td>
                  {{/if}}
                {{/if}}
              </tr>
            {{/foreach}}
          </table>
        </div>
      {{/foreach}}
    </td>
  </tr>
</table>