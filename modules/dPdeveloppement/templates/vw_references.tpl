{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPdeveloppement script=references_check ajax=true}}

<script>
Main.add(function() {
  var url = new Url('dPdeveloppement', 'ajax_vw_references');
  url.requestUpdate('ref-check-tables');
});
</script>

<h2>{{tr}}CRefCheckTable{{/tr}}</h2>

<table class="main layout" style="margin-bottom: 20px !important;">
  <tr>
    <td style="width: 50%">
      <fieldset>
        <legend>{{tr}}dPdeveloppement-ref_check-Check options{{/tr}}</legend>
        <form name="exec-integrity-checker" method="get" onsubmit="return onSubmitFormAjax(this, null, 'result-check-integrity')">
          <input type="hidden" name="m" value="dPdeveloppement"/>
          <input type="hidden" name="a" value="ajax_check_integrity"/>
          <input type="hidden" name="continue" value="0"/>
          <input type="hidden" name="js" value="1"/>


          <table class="main form">
            <tr>
              <th>{{mb_title class=CRefCheckTable field=class}}</th>
              <td>
                <input type="text" name="class" value="" readonly/>
              </td>
            </tr>

            <tr>
              <th>{{mb_title class=CRefCheckField field=field}}</th>
              <td>
                <input type="text" name="field" value="" readonly/>
              </td>
            </tr>

            <tr>
              <th>{{tr}}CRefCheckTable-chunks-size{{/tr}}</th>
              <td>
                <select name="chunk_size">
                  {{foreach from=$chunks item=_size}}
                    <option value="{{$_size}}" {{if $_size == 10000}}selected{{/if}}>
                      {{$_size|number_format:0:',':' '}}
                    </option>
                  {{/foreach}}
                </select>
              </td>
            </tr>

            <tr>
              <th>{{tr}}CRefCheckTable-delay{{/tr}}</th>
              <td>
                <input type="number" name="delay" value="1" size="3"/>
              </td>
            </tr>

            <tr>
              <td class="button" colspan="2">
                <button id="integrity-start" class="button change" type="button" onclick="ReferencesCheck.startIntegrityCheck()">
                  {{tr}}CRefCheckTable-action-Start{{/tr}}
                </button>
                <button id="integrity-stop" class="button stop" type="button" disabled onclick="ReferencesCheck.stopIntegrityCheck();">
                  {{tr}}CRefCheckTable-action-Stop{{/tr}}
                </button>

                <button id="integrity-stop" class="button erase" type="button" onclick="ReferencesCheck.resetIntegrityCheck();">
                  {{tr}}CRefCheckTable-action-Reset{{/tr}}
                </button>
              </td>
            </tr>
          </table>
        </form>
      </fieldset>
    </td>
    <td>
      <fieldset>
        <legend>{{tr}}dPdeveloppement-ref_check-Result{{/tr}}</legend>
        <div id="result-check-integrity"></div>
      </fieldset>
    </td>
  </tr>
</table>

<div id="ref-check-tables"></div>