{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="create-sejours-for-consults" method="post" action="?m=dPcabinet&dosql=do_create_sejours_for_consults"
      enctype="multipart/form-data" onsubmit="return onSubmitFormAjax(this, {useFormAction: true}, 'result-sejours-for-consult')">
  <input type="hidden" name="m" value="dPcabinet"/>
  <input type="hidden" name="dosql" value="do_create_sejours_for_consults"/>

  <h2>{{tr}}CConsultation-Create sejours for consults{{/tr}}</h2>

  <div class="small-info">
    {{tr}}CConsultation-Create sejours infos{{/tr}}
  </div>

  <table class="main form">
    <tr>
      <th style="width: 50%;">{{tr}}File{{/tr}}</th>
      <td>
        {{mb_include module=system template=inc_inline_upload paste=false extensions='txt' multi=false}}
      </td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button type="submit" class="import button">{{tr}}Create{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="result-sejours-for-consult"></div>