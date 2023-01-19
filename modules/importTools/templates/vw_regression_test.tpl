{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
  {{tr}}mod-importTools-infos-regression-test{{/tr}}.
</div>

<form name="getRegression" method="get" action="?" onsubmit="return onSubmitFormAjax(this, null, 'result_regression')">
  <input type="hidden" name="m" value="importTools"/>
  <input type="hidden" name="a" value="ajax_test_regression"/>
  <table class="main tbl">
    <tr>
      <td>
        {{mb_include module=system template=configure_dsn dsn=regression_first}}
      </td>
      <td>
        {{mb_include module=system template=configure_dsn dsn=regression_second}}
      </td>
    </tr>
    <tr>
      <td align="right">
        <label for="nb_import_tests">{{tr}}mod-importTools-nb-tests{{/tr}} :</label>
      </td>
      <td align="left">
        <input id="nb_import_tests" type="number" name="nb_import_tests" value="1000"/>
      </td>
    </tr>
    <tr>
      <td align="right">
        <label for="import_tag">{{tr}}mod-importTools-import-tag{{/tr}} :</label>
      </td>
      <td align="left">
        <input id="import_tag" type="text" name="import_tag" value=""/>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button class="" type="submit">{{tr}}mod-importTools-regression-start{{/tr}}</button>
      </td>
    </tr>
  </table>
  </form>

<div id="result_regression"></div>


