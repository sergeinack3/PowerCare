{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=use_fallback value=false}}

{{if isset($app->user_prefs.FALLBACK_LOCALE|smarty:nodefaults) && $app->user_prefs.FALLBACK_LOCALE != $app->user_prefs.LOCALE}}
  {{assign var=use_fallback value=true}}
{{/if}}

{{if $conf.locale_warn}}
  <!-- Locales warns -->
  {{if !$ajax}}
    <form action="?m={{$m}}" name="UnlocForm" style="display: none" method="post" class="prepared" onsubmit="return Localize.onSubmit(this);">
      <input type="hidden" name="{{$actionType}}" value="{{$action}}" />
      <input type="hidden" name="m" value="dPdeveloppement" />
      <input type="hidden" name="dosql" value="do_translate_aed" />

      <div style="height: 700px; overflow-y: scroll;">
        <table class="form">
          <tr>
            <th class="title" colspan="{{if $use_fallback}}3{{else}}2{{/if}}">{{tr}}system-title-unlocalized{{/tr}}</th>
          </tr>
          <tr>
            <th>{{tr}}Language{{/tr}}</th>
            <td {{if $use_fallback}}colspan="2"{{/if}}><input type="text" readonly="readonly" name="language" value="{{$app->user_prefs.LOCALE}}" /></td>
          </tr>

          {{if $use_fallback}}
            <tr>
              <th>{{tr}}pref-FALLBACK_LOCALE{{/tr}}</th>
              <td colspan="2"><input type="text" readonly="readonly" name="fallback_language" value="{{$app->user_prefs.FALLBACK_LOCALE}}" /></td>
            </tr>
          {{/if}}


          <tr>
            <th>{{tr}}Module{{/tr}}</th>
            <td {{if $use_fallback}}colspan="2"{{/if}}>
              <select name="module">
                <option value="common">&mdash; common</option>
                {{foreach from=$modules key=module_name item=_module}}
                <option value="{{$module_name}}" {{if $module_name == $m}} selected="selected" {{/if}}>
                  {{tr}}{{$_module}}{{/tr}}
                </option>
                {{/foreach}}
              </select>
            </td>
          </tr>

          <tbody>
          </tbody>

        {{*  <tr>*}}
        {{*    <td><input type="text" style="width:100%;" placeholder="Traduction supplémentaire" onblur="nameInputLocalisation(this)"></td>*}}
        {{*    <td {{if $use_fallback}}colspan="2"{{/if}}><input type="text" id="suppTranslation" disabled="disabled" size="70"></td>*}}
        {{*  </tr>*}}

        </table>
      </div>

      <div style="text-align: center">
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
        <button class="cancel" type="button" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
      </div>
    </form>
  {{/if}}

  <script type="text/javascript">
  Main.add(Localize.populate.curry({{$app|static:unlocalized|@json}}));

    nameInputLocalisation = function(formElt) {
      if (formElt.value) {
        var suppTran = $("suppTranslation");
        suppTran.name = "s["+formElt.value+"]";
        suppTran.enable();
      } else {
        suppTran.disable();
      }
    }
  </script>
{{/if}}