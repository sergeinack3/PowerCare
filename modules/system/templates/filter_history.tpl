{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=object_selector ajax=$ajax}}
{{mb_script module=system script=class_indexer}}
{{mb_script module=mediusers script=CMediusers ajax=$ajax}}

<script>
  changePage = (start) => {
    $V(getForm("filterFrm").start, start);
  };

  setStats = (val) => {
    $V($("filterFrm").stats, val);
  };

  setCsv = (val) => {
    $V($("filterFrm").csv, val);
    if (val == '1') {
      $("filterFrm").target = "_blank";
      $V($("filterFrm").suppressHeaders, '1');
      $V($(filterFrm).ajax , '1');
      $("filterFrm").submit();
    }
    $V($("filterFrm").csv, '0');
    $("filterFrm").target = "";
    $V($(filterFrm).ajax , '0');
    $V($("filterFrm").suppressHeaders, '0');
  };

  emptyClass = (form) => {
    $V(form.object_class, '');
    $V(form.autocomplete_input, '');
    $V(form.object_class.up('td').down('input'), '');
    $V(form.object_id, '');
  };

  Main.add(function() {
    var form = getForm("filterFrm");
    form.getElements().each(function(e){
      e.observe("change", function(){
        $V(form.start, 0);
      });
    });

    ClassIndexer.autocomplete(form.autocomplete_input, form.object_class, {profile: 'full'});

    $V(form.interval, '{{$interval}}', false);

    ObjectSelector.init = function() {
      this.sForm     = "filterFrm";
      this.sId       = "object_id";
      this.sView     = "object_id";
      this.sClass    = "object_class";
      this.onlyclass = "false";
      this.pop();
    }
  });
</script>

<form name="filterFrm" id="filterFrm" method="get" onsubmit="return onSubmitFormAjax(this, null, 'history_content')">
  <input type="hidden" name="m" value="system" />
  <input type="hidden" name="a" value="view_history" />
  <input type="hidden" name="ajax" value="0" />
  <input type="hidden" name="suppressHeaders" value="0" />
  <input type="hidden" name="dialog" value="{{$dialog}}" />
  <input type="hidden" name="start" value="{{$start|default:0}}" onchange="this.form.onsubmit()" />
  <input type="hidden" name="only_list" value="1" />
  <input type="hidden" name="stats" value="{{$stats}}" />
  <input type="hidden" name="csv" value="{{$csv}}" />
    {{mb_field class=CUserLog field=object_class hidden=true canNull=true}}

  <table class="form">
    <tr>
      <th>{{mb_label object=$filter field=user_id}}</th>
      <td>
        <script>
          Main.add(CMediusers.standardAutocomplete.curry('filterFrm', 'user_id', '_view'));
        </script>
        <input type="hidden" name="user_id" value="{{$filter->user_id}}" />
        <input type="text" name="_view" class="autocomplete" placeholder="&mdash; {{tr}}All{{/tr}}" value="{{$filter->_ref_user}}" />
        <button type="button" class="cancel notext" onclick="$V(this.form.user_id, ''); $V(this.form._view, '');"></button>
      </td>

      <th>{{mb_label object=$filter field=object_class}}</th>
      <td>
        <input type="text" name="autocomplete_input" size="40">
        <button type="button" class="cancel notext" onclick="emptyClass(this.form)"></button>
      </td>

      <th>{{mb_label object=$filter field="_date_min"}}</th>
      <td>{{mb_field object=$filter field="_date_min" form="filterFrm" register=true}}</td>

    </tr>
    <tr>
      <th>{{mb_label object=$filter field=type}}</th>
      <td>{{mb_field object=$filter field=type canNull=true emptyLabel="Choose"}}</td>

      <th>{{mb_label object=$filter field=object_id}}</th>
      <td>
        {{mb_field object=$filter field=object_id canNull=true}}
        <button type="button" class="search" onclick="ObjectSelector.init()">Chercher un objet</button>
      </td>
      <th>{{mb_label object=$filter field="_date_max"}}</th>
      <td>{{mb_field object=$filter field="_date_max" form="filterFrm" register=true}}</td>
    </tr>
    <tr>
      <td class="button" colspan="4">
        <button class="search me-primary" onclick="$V(this.form.start, '0', false); setStats('0'); setCsv('0');">{{tr}}Search{{/tr}}</button>
        <button type="button" class="download" onclick="setStats('0'); setCsv('1');">{{tr}}Download{{/tr}}</button>
      </td>
      <td class="button" colspan="2">
        <label for="interval" title="Echelle d'affichage">Intervalle</label>
        <select name="interval" onchange="this.form.onsubmit();">
          <option value="one-week"    {{if $interval == "one-week"    }} selected {{/if}}>1 semaine  (par heure)     </option>
          <option value="eight-weeks" {{if $interval == "eight-weeks" }} selected {{/if}}>8 semaines (par jour)      </option>
          <option value="one-year"    {{if $interval == "one-year"    }} selected {{/if}}>1 an       (par semaine)   </option>
          <option value="four-years"  {{if $interval == "four-years"  }} selected {{/if}}>4 ans      (par mois)      </option>
        </select>
        <button class="lookup" onclick="setStats('1')">{{tr}}Statistics{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
