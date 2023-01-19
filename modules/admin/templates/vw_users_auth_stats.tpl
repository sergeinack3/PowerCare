{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    form = getForm("typevue");
    if ($V(form.elements.to_update) === "1") {
      form.onsubmit();
      $V(form.elements.to_update, "0");
    }
    Calendar.regField(getForm("typevue").date, null);
  });
</script>

<div id="users_auth_tab">
  <table class="main">
    <tr>
      <th>
        <form action="" name="typevue" method="get" onsubmit="return onSubmitFormAjax(this, null, 'users_auth_graph');">
          <input type="hidden" name="m" value="{{$m}}" />
          <!--input type="hidden" name="a" value="vw_graph_access_logs" /-->
          <input type="hidden" name="a" value="vw_users_auth_stats_graph" />
          <input type="hidden" name="to_update" value="1" />

          <table class="form me-width-auto me-margin-auto me-text-align-center">
            <tr>
              <th>{{tr}}CUserAuthentication|pl{{/tr}} du</th>
              <td>
                <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.onsubmit()" />
              </td>
              <th>
                  {{mb_label class=CUserAuthentication field=_domain}}
              </th>
              <td>
                  {{mb_field class=CUserAuthentication field=_domain typeEnum='radio' value=$domain}}
              </td>
            </tr>
            <tr>
              <th>
                <label for="interval" title="Echelle d'affichage">Intervalle</label>
              </th>
              <td>
                <select name="interval" onchange="this.form.onsubmit();">
                  <option value="eight-weeks"  {{if $interval == "eight-weeks" }} selected {{/if}}>8 semaines (par jour)</option>
                  <option value="one-year"     {{if $interval == "one-year"    }} selected {{/if}}>1 an (par semaine)   </option>
                  <option value="four-years"   {{if $interval == "four-years"  }} selected {{/if}}>4 ans (par mois)     </option>
                  <option value="twenty-years" {{if $interval == "twenty-years"}} selected {{/if}}>20 ans (par an)      </option>
                </select>
              </td>
              <th>
                <label for="exclude_current_function">{{tr}}CUserAuthentication-stats-exclude-current-function{{/tr}}</label>
              </th>
              <td>
                <input type="checkbox" name="exclude_current_function" value="1" {{if $exclude_current_function == "1"}}checked{{/if}} />
              </td>
            </tr>
            <tr>
              <td colspan="4" class="me-text-align-center">
                <button type="submit" class="search">{{tr}}Search{{/tr}}</button>
              </td>
            </tr>
          </table>
        </form>
      </th>
    </tr>
    
    <tr>
      <td colspan="2">
        <div id="users_auth_graph"></div>
      </td>
    </tr>
  </table>
</div>
