{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=selected_module value=''}}

<form name="import-trads-{{$selected_module}}" method="post" onsubmit="return onSubmitFormAjax(this);">
  <input type="hidden" name="m" value="system"/>
  <input type="hidden" name="dosql" value="do_import_translations_overwrite"/>

  <table class="main tbl">
    {{foreach from=$tran key=_type item=_translations}}
      {{if !empty($_translations)}}
        <tr>
          <th class="section" colspan="5">
            {{tr}}system-translations import type.{{$_type}}{{if count($_translations) > 1}}|pl{{/if}}{{/tr}}
            ({{$counts.$_type.modules.$selected_module}})
          </th>
        </tr>

        <tr>
          <th class="narrow">
            {{if $_type != 'same'}}
              <input type="checkbox" checked onclick="Translation.checkAllTranslation(this, 'translation-{{$selected_module}}')"/>
            {{/if}}
          </th>
          <th>{{tr}}CTranslationOverwrite-source{{/tr}}</th>
          <th>{{tr}}CTranslationOverwrite-_old_translation{{/tr}}</th>
          <th>{{tr}}CTranslationOverwrite-translation{{/tr}}</th>
          <th class="narrow">{{tr}}CTranslationOverwrite-language{{/tr}}</th>
        </tr>

        {{foreach from=$_translations item=_translation}}
          <tr {{if $_type == 'same'}}class="hatching"{{/if}}>
            <td class="narrow">
              {{if $_type != 'same'}}
                <input type="checkbox" name="translation-{{$selected_module}}-{{$_translation.key}}"
                       class="translation translation-{{$selected_module}}" value="{{$_translation.key}}"
                       data-trad="{{$_translation.new_value}}" data-lang="{{$_translation.lang}}" checked/>
              {{/if}}
            </td>
            <td class="text">
              <label for="translation-{{$selected_module}}-{{$_translation.key}}">
                <strong>{{$_translation.key}}</strong>
              </label>
            </td>
            <td class="text">
              {{$_translation.old_value}}
            </td>
            <td class="text">
              {{$_translation.new_value}}
            </td>
            <td>
              {{$_translation.lang}}
            </td>
          </tr>
        {{/foreach}}
      {{/if}}
    {{/foreach}}

    <tr>
      <td colspan="5" class="button">
        <button class="button save" type="button" onclick="Translation.doTranslations('{{$selected_module}}');">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="result-test-{{$selected_module}}"></div>