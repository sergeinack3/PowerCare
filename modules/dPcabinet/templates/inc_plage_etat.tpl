{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=multiple value=false}}

{{assign var="pct" value=$_plage->_fill_rate}}
{{if $pct gt 100}}
  {{assign var="pct" value=100}}
{{/if}}
{{if $pct lt 50}}
  {{assign var="backgroundClass" value="empty"}}
{{elseif $pct lt 90}}
  {{assign var="backgroundClass" value="normal"}}
{{elseif $pct lt 100}}
  {{assign var="backgroundClass" value="booked"}}
{{else}}
  {{assign var="backgroundClass" value="full"}}
{{/if}}

<div class="progressBar">
  <div class="bar {{$backgroundClass}}" style="width: {{$pct}}%;"></div>
  <div class="text">
    {{if $_plage->locked}}
      <i class="me-icon lock me-primary" style="float: right; height: 12px; width: 12px"></i>
    {{/if}}
    {{if $_plage->_ref_agenda_praticien->sync}}
      <i style="float: right;line-height: 11px;" class="fas fa-sync-alt"></i>
    {{/if}}
    <a class="me-line-height-12"
       href="#{{$_plage->_id}}"
       onclick="{{if $online && $multipleMode}}
                  RDVmultiples.loadPlageConsultSlot(this, {{$_plage->_id}}, null, {{if $multiple}}true{{else}}false{{/if}}); return false;
                {{else}}
                  {{if $online}}
                    PlageConsultSelector.loadPlageConsult({{$_plage->_id}}, 0);
                  {{else}}
                    PlageConsultSelector.loadPlageConsult({{$_plage->_id}}, {{$_plage->_id}});
                  {{/if}}
                {{/if}}"
       onmouseover="ObjectTooltip.createDOM(this, 'place-{{$_plage->_id}}-tooltip')">
      {{$_plage->date|date_format:"%A %d"}}
    </a>
    <div id="place-{{$_plage->_id}}-tooltip" style="display: none">
      <table class="tbl me-no-box-shadow">
        <tr>
          <th class="title" colspan="2">{{$_plage->date|date_format:"%A %d %B %Y"}}</th>
        </tr>
      </table>
      <table>
        {{assign var=current_hour value=false}}
        {{assign var=div_float value=right}}
        {{foreach from=$_plage->_disponibilities item=_disponible key=_place}}
          {{if !$current_hour || $current_hour !== $_place|date_format:"%H"}}
            {{if $current_hour}}
              {{assign var=div_float value=left}}
                  </div>
                </td>
              </tr>
            {{/if}}
            {{assign var=current_hour value=$_place|date_format:"%H"}}
            <tr>
              <td>
                <div style="float: {{$div_float}}">
          {{/if}}
              {{if $_disponible}}
                {{assign var=div_color value="#993333"}}
                {{assign var=i_class value="fa-times"}}
                {{assign var=div_trad value="CPlageConsult_planning_disponibility_1"}}
              {{else}}
                {{assign var=div_color value="#339933"}}
                {{assign var=i_class value="fa-check"}}
                {{assign var=div_trad value="CPlageConsult_planning_disponibility_0"}}
              {{/if}}
                  <div style="
                          display: inline-block;
                          padding: 2px;
                          margin: 2px;
                          width: 50px;
                          border: 1px solid {{$div_color}};
                          border-radius: 5px;"
                       title="{{tr}}{{$div_trad}}{{/tr}}">
                    <i class="fas {{$i_class}}" style="color: {{$div_color}};"></i>
                    {{$_place|date_format:$conf.time}}
                  </div>
        {{foreachelse}}
          <tr>
            <td colspan="2">{{tr}}None{{/tr}}
        {{/foreach}}
          </td>
        </tr>
      </table>
    </div>
  </div>
</div>
