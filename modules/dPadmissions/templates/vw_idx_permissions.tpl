{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admissions script=admissions}}

{{mb_default var=page value=0}}
{{mb_default var=filterFunction value=''}}

<script>
  function reloadFullPermissions(filterFunction) {
    var oForm = getForm("selType");
    var url = new Url("dPadmissions", "httpreq_vw_all_permissions");
    url.addParam("date", "{{$date}}");
    url.requestUpdate('allAdmissions');
    reloadAdmission(filterFunction);
  }

  function changePage(page) {
    $V(getForm('permissionFilters').page, page);
    reloadPermission();
  }

  function changeFunction(filterFunction) {
    let form = getForm('permissionFilters');
    $V(form.filterFunction, filterFunction);
    $V(form.page, 0);
    reloadPermission();
  }

  function reloadPermission() {
    new Url("dPadmissions", "httpreq_vw_permissions")
    .addFormData(getForm('permissionFilters'))
    .requestUpdate('listPermissions');
  }

  {{assign var=auto_refresh_frequency value="dPadmissions automatic_reload auto_refresh_frequency_permissions"|gconf}}

  Main.add(function () {
    var totalUpdater = new Url("dPadmissions", "httpreq_vw_all_permissions");
    var listUpdater = new Url("dPadmissions", "httpreq_vw_permissions");

    totalUpdater.addParam("date", "{{$date}}");
    listUpdater.addParam("date", "{{$date}}");

    {{if $auto_refresh_frequency != 'never'}}
      totalUpdater.periodicalUpdate('allPermissions', {frequency: {{$auto_refresh_frequency}}});
      listUpdater.periodicalUpdate('listPermissions', {frequency: {{$auto_refresh_frequency}}});
    {{else}}
      totalUpdater.requestUpdate('allPermissions');
      listUpdater.requestUpdate('listPermissions');
    {{/if}}

    $("listPermissions").fixedTableHeaders();
    $("allPermissions").fixedTableHeaders();
  });

</script>

<form name="permissionFilters">
  <input type="hidden" name="filterFunction" value="{{$filterFunction}}"/>
  <input type="hidden" name="date" value="{{$date}}"/>
  <input type="hidden" name="page" value="{{$page}}"/>
</form>

<table class="main">
  <tr>
    <td>
      <a href="#legend" onclick="Admissions.showLegend()" class="button search me-tertiary me-dark">Légende</a>
      {{if "astreintes"|module_active}}{{mb_include module=astreintes template=inc_button_astreinte_day date=$date}}{{/if}}
    </td>
    <td style="float: right;">
      <a href="#" onclick="Admissions.printPermissions('{{$date}}', '{{$type_externe}}');" class="button print me-tertiary">Imprimer</a>
    </td>
  </tr>
  <tr>
    <td style="width: 250px">
      <div id="allPermissions" class="me-align-auto"></div>
    </td>
    <td style="width: 100%">
      <div id="listPermissions" class="me-align-auto"></div>
    </td>
  </tr>
</table>
