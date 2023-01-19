{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  changeLinesVisibility = function (checkbox, class_name) {
    if (checkbox.checked) {
      $$("tr." + class_name).invoke('hide');
    }
    else {
      $$("tr." + class_name).invoke('show');
    }
  };

  filterConfig = function (input, context) {
    var table = $(context);
    table.select(".config-line").invoke("show");

    var term = $V(input);
    if (!term) {
      return;
    }

    table.select(".config-feature").each(function (e) {
      if (!e.getText().like(term)) {
        e.up(".config-line").hide();
      }
    });
  };
</script>

<div class="small-info">
  {{tr}}CConfigurationCompare-msg instructions{{/tr}}
</div>

<form method="post" name="compare-configs" action="?m=system&a=ajax_compare_configs" enctype="multipart/form-data"
      onsubmit="return onSubmitFormAjax(this, {useFormAction: true}, 'result-config-compare')">
  <input type="hidden" name="m" value="system"/>
  <input type="hidden" name="a" value="ajax_compare_configs"/>

  <table class="main form">
    <tr>
      <th style="width:50%;">{{tr}}CConfigurationCompare-files{{/tr}}</th>
      <td style="width:50%;">
        {{mb_include module=system template=inc_inline_upload paste=false lite=true extensions=xml multi=true}}
      </td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        <button type="submit" class="hslip">{{tr}}CConfiguration-action-compare-start{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="result-config-compare"></div>