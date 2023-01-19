{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>Import de la base de données des codes INSEE / ISO</h2>

{{mb_include module=system template=configure_dsn dsn=INSEE}}

<script type="text/javascript">

  function startINSEE() {
    var url = new Url("dPpatients", "do_import_insee", 'dosql');
    url.requestUpdate("action-insee", {method: 'post'});
  }

  function startCountry() {
    var url = new Url("dPpatients", "do_import_country", 'dosql');
    url.requestUpdate("action-country", {method: 'post'});
  }

</script>

<table class="tbl">
  <tr>
    <th class="narrow">{{tr}}Action{{/tr}}</th>
    <th>{{tr}}Status{{/tr}}</th>
  </tr>
  <tr>
    <td>
      <button class="tick" onclick="startINSEE()">
        Importer les codes INSEE / ISO
      </button>
    </td>
    <td>
      <div id="action-insee"></div>
    </td>
  </tr>

  <tr>
    <td>
      <button class="tick" onclick="startCountry()">
        Importer les codes pays
      </button>
    </td>
    <td>
      <div id="action-country"></div>
    </td>
  </tr>

  <tr>
    <td>
      <form name="import-zipcode" method="post" onsubmit="return onSubmitFormAjax(this, null, 'result-import-zipcode')">
        <input type="hidden" name="m" value="dPpatients"/>
        <input type="hidden" name="dosql" value="do_import_zipcode"/>

        <select name="country">
          {{foreach from=$country_cp key=_country item=_table}}
            <option value="{{$_country}}">{{tr}}common-country-{{$_country}}{{/tr}}</option>
          {{/foreach}}
        </select>

        <button class="tick" type="submit">
          Importer les codes postaux
        </button>
      </form>
    </td>
    <td>
      <div id="result-import-zipcode"></div>
    </td>
  </tr>
</table>
