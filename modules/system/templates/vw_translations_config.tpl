{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="config-translations" method="post"
      onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Control.Modal.close();}});">
  <input type="hidden" name="m" value="system"/>
  <input type="hidden" name="dosql" value="do_save_configuration_translation"/>
  <input type="hidden" name="feature" value="{{$feature}}"/>

  <h2>{{tr}}CTranslationOverwrite-source{{/tr}} <samp>{{$feature}}</samp></h2>

  <table class="main tbl">
    <tr>
      <td width="20%"></td>
      <th width="40%">{{tr}}system-translations-base{{/tr}}</th>
      <th width="40%">
        {{tr}}system-translations-replacement{{/tr}}
        <button class="help notext" type="button" onclick="App.openMarkdownHelp()">{{tr}}Help{{/tr}}</button>
      </th>
    </tr>

    <tr>
      <th>{{tr}}Config{{/tr}}</th>
      <td class="text">
        {{if $trans_bdd->_old_translation}}
          {{$trans_bdd->_old_translation}}
        {{else}}
          {{tr}}{{$feature}}{{/tr}}
        {{/if}}
      </td>
      <td class="text">
        <textarea maxlength="100" type="text" style="width: 100%;"
                  name="translation">{{if $trans_bdd && $trans_bdd->translation}}{{$trans_bdd->translation}}{{/if}}</textarea>
      </td>
    </tr>

    <tr>
      <th>{{tr}}CRemplacement-description{{/tr}}</th>
      <td class="text">
        {{if $trans_bdd_desc->_old_translation}}
          {{$trans_bdd_desc->_old_translation}}
        {{/if}}
      </td>
      <td class="text">
        <textarea maxlength="1000" type="text" style="width: 100%;"
                  name="translation_desc">{{if $trans_bdd_desc && $trans_bdd_desc->translation}}{{$trans_bdd_desc->translation}}{{/if}}</textarea>
      </td>
    </tr>

    <tr>
      <td class="button" colspan="3">
        <button type="submit" class="button save">
          {{tr}}Save{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>