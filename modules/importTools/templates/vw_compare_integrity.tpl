{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Control.Tabs.create("compare-tools-tabs");
  Control.Tabs.setTabCount("CSejour", {{$counts.CSejour}});
  Control.Tabs.setTabCount("CConsultation", {{$counts.CConsultation}});
  Control.Tabs.setTabCount("COperation", {{$counts.COperation}});
  Control.Tabs.setTabCount("CFile", {{$counts.CFile}});
  Control.Tabs.setTabCount("CCompteRendu", {{$counts.CCompteRendu}});
</script>

<ul class="control_tabs small" id="compare-tools-tabs" style="white-space: nowrap;">
  {{foreach from=$diffs key=_class item=_diffs}}
    <li><a href="#{{$_class}}">{{$_class}} ({{tr}}{{$_class}}{{/tr}})</a></li>
  {{/foreach}}
</ul>

{{foreach from=$diffs key=_class item=_diffs}}
  <div id="{{$_class}}" style="display: none;">
    <table class="main tbl">
      {{assign var=i value=0}}
      {{foreach from=$_diffs key=_id item=empty}}
        {{if $i < 100}}
          <tr>
            <td>
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_class}}-{{$_id}}');">{{$_class}}-{{$_id}}</span>
            </td>
          </tr>
        {{/if}}
        {{assign var=i value=$i+1}}
      {{/foreach}}

    </table>
  </div>
{{/foreach}}
