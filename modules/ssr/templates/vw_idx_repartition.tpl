{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  showLegende = function() {
    new Url('ssr', 'vw_legende').requestModal();
  };
  Main.add(function () {
    Calendar.regField(getForm("selDate").date, null, { noView: true} );

    Repartition.current_m = "{{$m}}";
  });
</script>

{{mb_script module=ssr script=repartition}}

<form name="Edit-CBilanSSR" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
  <input type="hidden" name="m" value="ssr" />
  <input type="hidden" name="dosql" value="do_bilan_ssr_aed" />
  <input type="hidden" name="del" value="0" />
  {{mb_key object=$bilan}}
  {{mb_field object=$bilan field=sejour_id hidden=1}}
  {{mb_field object=$bilan field=technicien_id hidden=1}}
</form>

<form name="Repartition_auto-CBilanSSR" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="dosql" value="do_bilan_ssr_repartition_auto_aed" />
  <input type="hidden" name="service_id" value="" />
  <input type="hidden" name="show_cancelled_services" value="" />
  <input type="hidden" name="date" value="{{$date}}" />
  {{mb_field object=$bilan field=technicien_id hidden=1}}
</form>

<table class="main">
  <tr>
    <th colspan="2">
      <big>{{tr var1=$date|date_format:$conf.date}}ssr-planning_repartition{{/tr}}</big>
      <form name="selDate" action="?" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="{{$actionType}}" value="vw_idx_repartition" />
        <input type="hidden" name="dialog" value="{{$dialog}}" />
        <input type="hidden" name="readonly" value="{{if $readonly}}1{{else}}0{{/if}}" />
        <input type="hidden" name="date" class="date" value="{{$date}}" onchange="this.form.submit()" />
      </form>
      <button type="button" class="search me-tertiary" style="float: right;" onclick="showLegende();">{{tr}}Legend{{/tr}}</button>
    </th>
  </tr>
  
  <tr>
    <td>
      <div id="plateaux">
        <script>
          ViewPort.SetAvlHeight('plateaux', 1);
          // Debugage du scroll de la div de la liste des prescriptions
          Position.includeScrollOffsets = true;
          Event.observe('plateaux', 'scroll', function(event) { Position.prepare(); });
        </script>

        {{if $conf.ssr.repartition.show_tabs}}
          <script>
            Main.add(Control.Tabs.create.curry('tabs-plateaux', true));
          </script>
          <ul id="tabs-plateaux" class="control_tabs">
            {{foreach from=$plateaux item=_plateau}}
            <li>
              <a href="#{{$_plateau->_guid}}">
                {{$_plateau}}
              </a>
            </li>
            {{/foreach}}
          </ul>
        {{/if}}

        {{foreach from=$plateaux item=_plateau}}
          {{mb_include module=ssr template=inc_repartition_plateau plateau=$_plateau}}
        {{foreachelse}}
          <div class="small-warning">
            {{tr}}CGroups-back-plateaux_techniques.empty{{/tr}}
          </div>
        {{/foreach}}
      </div>
    </td>

    <td style="width: 160px;">
      <div id="non-repartis">
        <script>
        ViewPort.SetAvlHeight('non-repartis', 1);
        </script>
        {{mb_include module=ssr template=inc_sejours_non_affectes}}
      </div>
    </td>
  </tr>
</table>
