{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=translation ajax=true}}

<script>
  Main.add(function () {
    Control.Tabs.create('tabs-modules-translations', true);

    {{foreach from=$translations key=_module item=_type}}
      {{assign var=count_new value=$counts.new.modules.$_module}}
      {{assign var=count_old value=$counts.old.modules.$_module}}
      {{assign var=count_same value=$counts.same.modules.$_module}}

      {{math assign=total equation="x+y+z" x=$count_new y=$count_old z=$count_same}}
      {{math assign=changes equation="x+y" x=$count_new y=$count_old}}

      Control.Tabs.setTabCount('tab-translate-{{$_module}}', {{$changes}}, {{$total}});
    {{/foreach}}

  });
</script>

<h2>{{tr}}File{{/tr}} : {{$file_name}}</h2>

<ul style="margin-bottom: 10px;">
  <li>
    <strong>{{tr}}system-translations import count.new{{/tr}}</strong> : {{$counts.new.total}}
  </li>
  <li>
    <strong>{{tr}}system-translations import count.old{{/tr}}</strong> : {{$counts.old.total}}
  </li>
  <li>
    <strong>{{tr}}system-translations import count.same{{/tr}}</strong> : {{$counts.same.total}}
  </li>
</ul>

<table class="main layout">
  <tr>
    <td width="10%">
      <button class="save" type="button" onclick="Translation.doTranslations();">
        {{tr}}system-action-Save all translations{{/tr}}
      </button>

      <ul class="control_tabs_vertical" id="tabs-modules-translations">
        {{foreach from=$translations key=_module item=_type}}
          <li>
            <a href="#tab-translate-{{$_module}}">
              {{if $_module == 'undefined'}}
                {{tr}}common-undefined{{/tr}}
              {{else}}
                {{tr}}module-{{$_module}}-court{{/tr}}
              {{/if}}
            </a>
          </li>
        {{/foreach}}
      </ul>
    </td>

    <td>
      {{foreach from=$translations key=_module item=_type}}
        <div id="tab-translate-{{$_module}}" style="display: none;">
          {{mb_include module=system template=inc_compare_translations tran=$_type selected_module=$_module}}
        </div>
      {{/foreach}}
    </td>
  </tr>
</table>