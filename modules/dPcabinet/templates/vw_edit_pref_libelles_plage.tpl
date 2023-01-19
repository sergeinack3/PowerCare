{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=edit_consultation ajax=true}}
{{mb_default var=is_tamm_consultation value=0}}
<script>
  Main.add(function () {
    {{foreach from=$libelles item=_libelle}}
    LibellesPlage.createSpan('{{$_libelle}}');
    {{/foreach}}
  });
</script>
<table class="main">
  <tr>
    <th class="title" colspan="2">{{tr}}pref-see_plages_consult_libelle{{/tr}}</th>
  </tr>
  <tr>
    <td {{if !$is_tamm_consultation}}colspan="2"{{/if}}>
      <form name="edit_see_plages_consult_libelle" method="post" onsubmit="return false">
        <input type="hidden" name="m" value="admin"/>
        <input type="hidden" name="dosql" value="do_preference_aed"/>
        <input type="hidden" name="user_id" value="{{$app->user_id}}"/>
        <input type="hidden" name="pref[see_plages_consult_libelle]" value="{{$app->user_prefs.see_plages_consult_libelle}}">
        <table class="main">
          <tr>
            <td class="button" colspan="2">
              <input type="text" name="name_libelle_to_add"/>
              <button class="add" type="button" onclick="LibellesPlage.createSpan($V(this.form.name_libelle_to_add));">
                {{tr}}Add{{/tr}}
              </button>
            </td>
          </tr>
          <tr style="height:50px;">
            <th style="width: 100px;">{{tr}}pref-edit_see_plages_consult_libelle-selected{{/tr}}:</th>
            <td>
              <div id="plages_consult_libelles"></div>
            </td>
          </tr>
          <tr>
            <td class="button" colspan="2">
              <button type="button" class="save" onclick="LibellesPlage.storePref();">{{tr}}Save{{/tr}}</button>
              <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>
    </td>
    {{if $is_tamm_consultation}}
      <td>
        <table>
          <tr>
            <th>
              <label for="_show_cancelled"
                     title="{{tr}}CPlageConsult-action-Show canceled consultation|pl{{/tr}}">
                {{tr}}CPlageConsult-action-Show canceled consultation|pl{{/tr}}
              </label>
            </th>
            <td>
              <input type="checkbox" name="show_cancelled"
                     onchange="$V(getForm('filters_planning').show_cancelled, $V(this) ? 1: 0);getForm('filters_planning').submit();"
                     {{if $show_cancelled}}checked{{/if}}>
            </td>
          </tr>
          {{if $app->user_prefs.useTAMMSIH}}
          <tr>
            <th>
              <label for="_show_patient_events"
                     title="{{tr}}pref-show_intervention{{/tr}}">
                  {{tr}}pref-show_intervention{{/tr}}
              </label>
            </th>
            <td>
              <input type="checkbox" name="show_patient_events"
                     onchange="App.savePref('show_intervention',  (this.checked) ? '1' : '0', getForm('filters_planning').submit());"
                     {{if $app->user_prefs.show_intervention}}checked{{/if}}/>
            </td>
          </tr>
          {{/if}}
        </table>
      </td>
    {{/if}}
  </tr>
</table>

