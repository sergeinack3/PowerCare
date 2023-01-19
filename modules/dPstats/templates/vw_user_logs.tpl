{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Calendar.regField(getForm("typevue").date, null, {noView: true});
  }
</script>

<table class="main">

  <tr>
    <th>
      <form action="?" name="typevue" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="{{$actionType}}" value="{{$action}}" />

        Journaux utilisateurs du {{$date|date_format:$conf.longdate}}
        <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />

        <label for="user_id" title="Filtre possible sur un utilisateur">Utilisateur</label>
        <select name="user_id" onchange="this.form.submit();">
          <option value="">&mdash; {{tr}}All{{/tr}}</option>
          {{mb_include module=mediusers template=inc_options_mediuser list=$users selected=$user_id}}
        </select>

        <label for="interval" title="Echelle d'affichage">Intervalle</label>
        <select name="interval" onchange="this.form.submit();">
          <option value="one-week" {{if $interval == "one-week"    }} selected {{/if}}>1 semaine (par heure)</option>
          <option value="eight-weeks" {{if $interval == "eight-weeks" }} selected {{/if}}>8 semaines (par jour)</option>
          <option value="one-year" {{if $interval == "one-year"    }} selected {{/if}}>1 an (par semaine)</option>
          <option value="four-years" {{if $interval == "four-years"  }} selected {{/if}}>4 ans (par mois)</option>
        </select>

      </form>
    </th>
  </tr>

</table>

{{mb_include template=inc_graph_user_logs}}

