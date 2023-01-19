{{*
 * @package Mediboard\Webservices
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
var series = {{$series|@json}};
var options = {{$options|@json}};

function fillSelect(select, dest) {
  var url = new Url("webservices", "ajax_filter_web_func");
  url.addParam("service_demande", select);
  url.addParam("type", dest);
  if (dest == 'fonction') {
    url.addParam("web_service_demande", $V(getForm('formStat').web_service));
  }
  url.requestUpdate(dest, {onComplete: function() {
    if (dest == 'web_service') {
      fillSelect($V(getForm('formStat').service), 'fonction');
    }
  }});
}

Main.add(function(){  
  var oFormStat = getForm("formStat");
  Calendar.regField(oFormStat.date_min);
  Calendar.regField(oFormStat.date_max);

  {{if $service}}
    Flotr.draw($('graph'), series, options);
  
    fillSelect('{{$service}}', 'web_service');
  {{/if}}
});
</script>

<form name="formStat" method="get" action="?">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="tab" value="{{$tab}}" />
  
  <table class="form">
    <tr>
      <th class="title" colspan="6">{{tr}}stats-echanges-soap{{/tr}}</th>
    </tr>
    <tr>
      <th class="category me-text-align-left">{{tr}}early{{/tr}}</th>
      <th class="category me-text-align-left">{{tr}}end{{/tr}}</th>
      <th class="category me-text-align-left">{{tr}}CEchangeSOAP-type{{/tr}}</th>
      <th class="category me-text-align-left">{{tr}}CEchangeSOAP-web_service_name{{/tr}}</th>
      <th class="category me-text-align-left">{{tr}}CEchangeSOAP-function_name{{/tr}}</th>
    </tr>
    <tr>
      <td>  
        <input type="hidden" name="date_min" value="{{$date_min}}" />
      </td>
      <td>
        <input type="hidden" name="date_max" value="{{$date_max}}" />
      </td>
      <td>
        <select class="str" name="service" onchange="fillSelect(this.value, 'web_service')">
          <option value="">&mdash; Liste des types de services</option>
          {{foreach from=$services item=_service}}
            <option value="{{$_service}}" {{if $service == $_service}} selected="selected"{{/if}}>
              {{$_service}}
            </option>
          {{/foreach}}
        </select>
      </td>
      <td>
        <select class="str" id="web_service" name="web_service" 
           onchange="fillSelect($V(getForm('formStat').service), 'fonction')">
          <option value="">&mdash; Liste des web services</option>
        </select>
      </td>
      <td>
        <select class="str" id="fonction" name="fonction">
          <option value="">&mdash; Liste des fonctions</option>
        </select>
      </td>
    </tr>
    <tr>
      <td class="button narrow" colspan="6">
        <button class="search" type="submit">{{tr}}Filter{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div style="height: 400px; margin: 1em;" id="graph"></div>