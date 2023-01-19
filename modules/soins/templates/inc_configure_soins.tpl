{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  redirectOffline = function(type, embed) {
    switch(type) {
      case 'bilan':
        var url = new Url("soins", "offline_bilan_service");
        break;
      case 'plan':
        var url = new Url("soins", "offline_plan_soins");
        url.addParam("dialog", 1);
        break;
      case 'sejour':
        var url = new Url("soins", "offline_sejours");
        url.addParam("dialog", 1);
        break;
      case 'sejour_lite':
        var url = new Url("soins", "offline_sejours_lite");
        url.addParam("dialog", 1);
        break;
      case 'ordonnances':
        var url = new Url("soins", "offline_prescriptions_multipart", "raw");
        url.addParam("dialog", 0);
    }

    url.addParam("service_id", $("service_id").value);
    url.addParam("g", '{{$g}}');

    if (embed) {
      url.addParam("embed", 1);
      url.addParam("_aio", "savefile");
      url.pop(500, 400, "Vue embarquée");
    }
    else {
      url.open();
    }
  };
  checkTasks = function(type) {
    var url = new Url('soins', 'ajax_purge_tasks');
    url.addParam('type', type);
    url.requestUpdate('purge_tasks');
  };

  computeScoreGPM = function () {
    var form = getForm('filter_score_gmp');

    new Url('soins', 'ajax_vw_compute_score_gmp')
      .addFormData(form)
      .requestModal('60%', '60%');
  };

  Main.add(function () {
    var form = getForm("filter_score_gmp");
    Calendar.regField(form._date_min);
    Calendar.regField(form._date_max);
  });
</script>

<table class="form">
  <col style="width: 50%" />
  <tr>
    <th class="category" colspan="2">
      {{tr}}Offline{{/tr}}
    </th>
  </tr>
  <tr>
    <td style="text-align: right">
      {{tr}}CChambre-service_id{{/tr}} :
      <select id="service_id">
        {{foreach from=$services item=_service}}
          <option value='{{$_service->_id}}'>{{$_service->nom}}</option>
        {{/foreach}}
        <option value="NP">{{tr}}CService-Not placed{{/tr}}</option>
      </select>
    </td>
    <td>
      <button type="button" class="search" onclick="redirectOffline('sejour');">{{tr}}CSejour.all{{/tr}}</button>
      <button type="button" class="download" onclick="redirectOffline('sejour', true);">{{tr}}Download{{/tr}} {{tr}}CSejour.all{{/tr}}</button>
      <br />
      <button type="button" class="search" onclick="redirectOffline('sejour_lite');">{{tr}}CSejour.all{{/tr}} (lite)</button>
      {{if "dPprescription"|module_active}}
        <br/>
        <button type="button" class="search" onclick="redirectOffline('bilan');">{{tr}}CService.bilan{{/tr}}</button>
        <br />
        {{if "planSoins"|module_active}}
          <button type="button" class="search" onclick="redirectOffline('plan');">{{tr}}CService.plan_soins{{/tr}}</button>
          <br />
        {{/if}}
        <button type="button" class="search" onclick="redirectOffline('ordonnances');">{{tr}}CService.ordonnances{{/tr}}</button>
      {{/if}}
    </td>
  </tr>
  <tr>
    <th class="category" colspan="2">
      {{tr}}Tools{{/tr}}
    </th>
  </tr>
  <tr>
    <td>
      <button type="button" class="search" onclick="checkTasks('check');">
        {{tr}}config-soins-See the tasks performed without an author{{/tr}}
      </button><br/>
      <button type="button" class="change" onclick="checkTasks('repair');">
        {{tr}}config-soins-Correct spots performed without author per 100{{/tr}}
      </button><br />

      <form name="filter_score_gmp" method="get" onsubmit="return Sisra.filterExchange(this)">
        <table class="form">
          <tr>
            <th>{{tr}}common-Start date{{/tr}}</th>
            <td><input name="_date_min" type="hidden"></td>
            <th>{{tr}}common-End date{{/tr}}</th>
            <td><input name="_date_max" type="hidden"></td>
            <td style="text-align: right">
              {{tr}}CChambre-service_id{{/tr}} :
              <select name="_service_id" id="_service_id">
                <option value=''>{{tr}}CService.all{{/tr}}</option>
                {{foreach from=$services item=_service}}
                  <option value='{{$_service->_id}}'>{{$_service->nom}}</option>
                {{/foreach}}
                <option value="NP">{{tr}}CService-Not placed{{/tr}}</option>
              </select>
            </td>
            <td>
              <button type="button" class="change" onclick="computeScoreGPM();">
                {{tr}}CExamGir-See the GMP EHPAD score{{/tr}}
              </button>
            </td>
          </tr>
        </table>
      </form>
    </td>
    <td id="purge_tasks"></td>
  </tr>
</table>

<form name="EditConfig" action="?m={{$m}}&amp;{{$actionType}}=configure" method="post" onsubmit="return onSubmitFormAjax(this)">
  {{mb_configure module=$m}}
  <table class="form">
    {{mb_include module=system template=configure_handler class_handler=CObservationEmailHandler}}

    <tr>
      <td class="button" colspan="2">
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
