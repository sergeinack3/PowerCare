{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=diff value=$audit->getLogDiff()}}

{{if !$diff|instanceof:'Ox\Mediboard\Developpement\LogDiff'}}
  <div class="small-error">No diff</div>
  {{mb_return}}
{{/if}}

<script>
  Main.add(function () {
    Control.Tabs.create('log-diff-tabs', true);
  });
</script>

{{if $diff->getSigmaByClass()}}
  <button type="button" class="search"
          onclick="Modal.open('audit-sigma-by-class', {showClose: true, width: '600px', height: '800px'});">
    Erreurs
  </button>
  <div id="audit-sigma-by-class" style="display: none;">
    <div class="small-info">
      Journaux par classe présents sur un hôte, mais pas sur l'autre.
    </div>

    <table class="main tbl">
      <tr>
        <th>Classe</th>
        <th>Hôte 1</th>
        <th>Hôte 2</th>
      </tr>
      {{foreach from=$diff->getSigmaByClass() key=_class item=_targets}}
        <tr>
          <th>{{$_class}}</th>
          <td>{{$_targets.first}}</td>
          <td>{{$_targets.second}}</td>
        </tr>
      {{/foreach}}
    </table>
  </div>
{{else}}
  <div class="small-info">{{tr}}common-msg-No error{{/tr}}</div>
  {{mb_return}}
{{/if}}

<table class="main layout">
  <col style="width: 10%;"/>

  <tr>
    <td style="white-space: nowrap; vertical-align: top;">
      <ul id="log-diff-tabs" class="control_tabs_vertical small">
        {{foreach from=$diff->getDiffDays() item=_day}}
          <li>
            <a href="#log-diff-{{$_day}}">
              <em>{{$diff->getSigmaForDay($_day)}}</em> &mdash; {{$_day|date_format:$conf.date}}
            </a>
          </li>
        {{/foreach}}
      </ul>
    </td>

    <td>
      {{foreach from=$diff->getDiffDays() item=_day}}
        {{assign var=first_target value=$audit->getFirstTarget()}}
        {{assign var=second_target value=$audit->getSecondTarget()}}

        {{assign var=first_logs value=$diff->getFirstLogs()}}
        {{assign var=second_logs value=$diff->getSecondLogs()}}
        <div id="log-diff-{{$_day}}" style="display: none;">
          <table class="main tbl">
            <tr>
              <th class="title" colspan="4">{{$first_target->getHostname()}}</th>

              <th class="title" colspan="4">{{$second_target->getHostname()}}</th>
            </tr>

            {{foreach from=$diff->getDiffByHourForDay($_day) key=_time item=_types}}
              <tr>
                <th class="category" colspan="8">
                  {{$_time}} h
                </th>
              </tr>
              {{foreach from=$_types key=_type item=_diff}}
                {{if $_diff.first || $_diff.second || $_diff.diff}}
                  <tr>
                    <th class="section" colspan="8">{{$_type}}</th>
                  </tr>
                  {{foreach from=$_diff.first item=_logs}}
                    {{foreach from=$_logs item=_log_id}}
                      {{assign var=_log value=$first_logs->getLogFromType($_type, $_log_id)}}
                      <tr>
                        <td style="width: 12.5%;">{{$_log.id}}</td>
                        <td style="width: 12.5%;">{{$_log.date|date_format:$conf.datetime}}</td>
                        <td style="width: 12.5%;">{{$_log.object_class}}</td>
                        <td style="width: 12.5%;">{{$_log.object_id}}</td>

                        <td class="warning" colspan="4"></td>
                      </tr>
                    {{/foreach}}
                  {{/foreach}}

                  {{foreach from=$_diff.second item=_logs}}
                    {{foreach from=$_logs item=_log_id}}
                      {{assign var=_log value=$second_logs->getLogFromType($_type, $_log_id)}}
                      <tr>
                        <td class="warning" colspan="4"></td>

                        <td style="width: 12.5%;">{{$_log.id}}</td>
                        <td style="width: 12.5%;">{{$_log.date|date_format:$conf.datetime}}</td>
                        <td style="width: 12.5%;">{{$_log.object_class}}</td>
                        <td style="width: 12.5%;">{{$_log.object_id}}</td>
                      </tr>
                    {{/foreach}}
                  {{/foreach}}

                  {{foreach from=$_diff.diff item=_logs}}
                    {{foreach from=$_logs item=_log_id}}
                      {{assign var=_first_log value=$first_logs->getLogFromType($_type, $_log_id)}}
                      {{assign var=_second_log value=$second_logs->getLogFromType($_type, $_log_id)}}
                      <tr>
                        {{* FIRST *}}
                        <td style="width: 12.5%;">{{$_first_log.id}}</td>

                        <td
                          style="width: 12.5%;" {{if ($_first_log.date !== $_second_log.date)}} class="warning" {{/if}}>
                          {{$_first_log.date|date_format:$conf.datetime}}
                        </td>

                        <td
                          style="width: 12.5%;"{{if ($_first_log.object_class !== $_second_log.object_class)}} class="warning" {{/if}}>
                          {{$_first_log.object_class}}
                        </td>

                        <td
                          style="width: 12.5%;"{{if ($_first_log.object_id !== $_second_log.object_id)}} class="warning" {{/if}}>
                          {{$_first_log.object_id}}
                        </td>

                        {{* SECOND *}}
                        <td style="width: 12.5%;">{{$_second_log.id}}</td>

                        <td
                          style="width: 12.5%;"{{if ($_second_log.date !== $_first_log.date)}} class="warning" {{/if}}>
                          {{$_second_log.date|date_format:$conf.datetime}}
                        </td>

                        <td
                          style="width: 12.5%;"{{if ($_second_log.object_class !== $_first_log.object_class)}} class="warning" {{/if}}>
                          {{$_second_log.object_class}}
                        </td>

                        <td
                          style="width: 12.5%;"{{if ($_second_log.object_id !== $_first_log.object_id)}} class="warning" {{/if}}>
                          {{$_second_log.object_id}}
                        </td>
                      </tr>
                    {{/foreach}}
                  {{/foreach}}
                {{/if}}
              {{/foreach}}
            {{/foreach}}
          </table>
        </div>
      {{/foreach}}
    </td>
  </tr>
</table>
