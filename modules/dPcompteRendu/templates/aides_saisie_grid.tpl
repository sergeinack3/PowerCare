{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if @$object->_aides_all_depends.$property}}

<script>
  Main.add(function() {
    Control.Tabs.create("tabs-aides-depend");
    Control.Tabs.create("tabs-aides-depend2");
  });
</script>

{{assign var=aidesByDepend1 value=$object->_aides_all_depends.$property}}
{{assign var=numCols value=4}}
{{math equation="100/$numCols" assign=width format="%.1f"}}

<table class="main">
  <tr>
    <td style="white-space: nowrap;" class="narrow">
      <ul class="control_tabs_vertical" id="tabs-aides-depend">
      {{foreach from=$aidesByDepend1 key=depend1 item=aidesByDepend2}}
        <li class="{{$aidesByDepend2|@count|ternary:"":"empty"}}">
          <a href="#{{$object->_class}}-{{$depend1}}">{{tr}}{{$object->_class}}.{{$depend_field_1}}.{{$depend1}}{{/tr}} 
            <small>({{$aidesByDepend2|@count}})</small>
          </a>
        </li>
      {{/foreach}}
      </ul>
    </td>
      
    <td>
      {{foreach from=$aidesByDepend1 key=depend1 item=aidesByDepend2}}
        <div id="{{$object->_class}}-{{$depend1}}" style="display: none;">
          <ul class="control_tabs" id="tabs-aides-depend2">
          {{foreach from=$aidesByDepend2 key=depend2 item=aides}}
            <li><a href="#{{$object->_class}}-{{$depend1}}-{{if $depend2}}{{$depend2}}{{else}}all{{/if}}">{{tr}}{{$object->_class}}.{{$depend_field_2}}.{{$depend2}}{{/tr}} <small>({{$aides|@count}})</small></a></li>
          {{/foreach}}
          </ul>

          {{foreach from=$aidesByDepend2 key=depend2 item=aides}}
            <table id="{{$object->_class}}-{{$depend1}}-{{if $depend2}}{{$depend2}}{{else}}all{{/if}}" class="main tbl" style="table-layout: fixed;">
              <tr>
              {{foreach from=$aides item=_aide name=_aides}}
                {{assign var=i value=$smarty.foreach._aides.index}}
                {{if $_aide->_owner == "user"}}
                  {{assign var=owner_icon value="user"}}
                {{elseif $_aide->_owner == "func"}}
                  {{assign var=owner_icon value="user-function"}}
                {{else}}
                  {{assign var=owner_icon value="group"}}
                {{/if}}
                  <td title="{{$_aide->text}}" style="width: {{$width}}%;">
                    <img style="float:right; clear: both; opacity: 0.3;" 
                         src="images/icons/{{$owner_icon}}.png" 
                         title="{{$_aide->_ref_owner}}" />
                  
                    <label>
                      <button type="button" class="tick notext" onclick='applyHelper("{{$_aide->name}}","{{$_aide->text}}")'></button>
                      {{$_aide->name}}
                    </label>
                  </td>
                {{if ($i % $numCols) == ($numCols-1) && !$smarty.foreach._aides.last}}</tr><tr>{{/if}}
              {{/foreach}}
              </tr>
            </table>
          {{/foreach}}
        </div>
      {{/foreach}}
    </td>
  </tr>
</table>
{{/if}}
