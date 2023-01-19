{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  savePlanningBlocViewPreference = function(form) {
    var value = $V(form.elements['pref[view_planning_bloc]']);
    var url = "m={{$m}}&tab=";
    if (value == 'timeline') {
      url = url + 'vw_timeline_salles';
    }
    else {
      if (value == 'horizontal') {
        url = url + 'vw_horizontal_planning';
      }
      else {
        url = url + 'vw_suivi_salles';
      }
    }
    $V(form.elements['postRedirect'], url);

    return form.submit();
  }
</script>

<form name="selectPlanningBlocView" method="post" onsubmit="return savePlanningBlocViewPreference(this);">
  <input type="hidden" name="m" value="admin" />
  <input type="hidden" name="dosql" value="do_preference_aed" />
  <input type="hidden" name="user_id" value="{{$app->user_id}}" />
  <input name="postRedirect" value="" type="hidden">

  <table class="form me-no-box-shadow">
    <tr>
      <th>
        <label for="pref[view_planning_bloc]" title="{{tr}}pref-view_planning_bloc-desc{{/tr}}">
          {{tr}}pref-view_planning_bloc-short{{/tr}}
        </label>
      </th>
      <td>
        <select name="pref[view_planning_bloc]">
          <option value="vertical"{{if $app->user_prefs.view_planning_bloc == 'vertical'}} selected{{/if}}>
            {{tr}}pref-view_planning_bloc-vertical{{/tr}}
          </option>
          <option value="timeline"{{if $app->user_prefs.view_planning_bloc == 'timeline'}} selected{{/if}}>
            {{tr}}pref-view_planning_bloc-timeline{{/tr}}
          </option>
          <option value="horizontal"{{if $app->user_prefs.view_planning_bloc == 'horizontal'}} selected{{/if}}>
            {{tr}}pref-view_planning_bloc-horizontal{{/tr}}
          </option>
        </select>
      </td>
    </tr>
    <tr>
      <th>
        <label for="pref[planning_bloc_show_cancelled_operations]" title="{{tr}}pref-planning_bloc_show_cancelled_operations-desc{{/tr}}">
          {{tr}}pref-planning_bloc_show_cancelled_operations-short{{/tr}}
        </label>
      </th>
      <td>
        <select name="pref[planning_bloc_show_cancelled_operations]">
          <option value="0"{{if !$app->user_prefs.planning_bloc_show_cancelled_operations}} selected{{/if}}>{{tr}}No{{/tr}}</option>
          <option value="1"{{if $app->user_prefs.planning_bloc_show_cancelled_operations}} selected{{/if}}>{{tr}}Yes{{/tr}}</option>
        </select>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button type="button" class="save" onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>