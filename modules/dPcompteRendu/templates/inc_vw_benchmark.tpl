{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=compteRendu script=fields ajax=$ajax}}

{{if "dPcim10"|module_active}}
  {{mb_script module=cim10 script=CIM ajax=$ajax}}
  {{mb_script module=cim10 script=CISP ajax=$ajax}}
  {{mb_script module=cim10 script=DRC ajax=$ajax}}
  <script type="text/javascript">
    Main.add(function () {
      CIM.onSelectCode = function (button) {
        Fields.insertHTML(`${button.dataset.libelle} (CIM10: ${button.dataset.code})`);
      };

      CISP.onSelectCode = function (button) {
        Fields.insertHTML(`${button.dataset.libelle} (CISP: ${button.dataset.code})`);
      };

      DRC.onSelectCode = function () {
        let transcodings = $("list_transcodings").select(".transcoding.selected");

        $A(transcodings).each(function (transcode) {
          Fields.insertHTML(`${transcode.dataset.libelle} (CIM10: ${transcode.dataset.code})`);
          if (transcodings.length > 1) {
            Fields.insertHTML(`<br>`)
          }
        });

        Control.Modal.close();
      };
    });
  </script>
{{/if}}
{{if "loinc"|module_active}}
  {{mb_script module=loinc script=loinc ajax=$ajax}}
  <script type="text/javascript">
    Main.add(function () {
      Loinc.showCode = function (button) {
        Fields.insertHTML(`${button.dataset.libelle} (LOINC: ${button.dataset.code})`);
      };
    });
  </script>
{{/if}}
{{if "dPccam"|module_active}}
  {{mb_script module=dPplanningOp script=ccam_selector ajax=$ajax}}
  <script type="text/javascript">
    Main.add(function () {

      CCAMSelector.set = function (button) {
        Fields.insertHTML(`${button.dataset.libelle} (CCAM: ${button.dataset.code})`);
      };

      CCAMSelector.setMulti = function () {
        let inputs = $("code_area").select(".multiples_codes:checked");

        $A(inputs).each(function (input) {
          Fields.insertHTML(`${input.dataset.libelle} (CCAM: ${input.dataset.code})`);
          if (inputs.length > 1) {
            Fields.insertHTML(`<br>`)
          }
        })
      };
    });
  </script>
{{/if}}
{{if "dPmedicament"|module_active}}
  {{mb_script module=dPmedicament script=livret_therapeutique ajax=$ajax}}
  <script>
    Livret.target = 'benchmark';
    Livret.ged = 1;

    Main.add(function () {
      Livret.onSelectCode = function (button) {
        Fields.insertHTML(`${button.dataset.libelle} (ATC: ${button.dataset.code})`);
        Control.Modal.close();
      }
    });
  </script>
{{/if}}

<div id="main-content">
  <table class="main">
    <tbody>
    <tr>
      {{if count($benchmarks) > 0}}
        <td style="width: 200px">
          <table class="tbl me-no-hover me-box-shadow-table">
            <tbody>
            <tr>
              <th class="title" colspan="2">
                {{tr}}CCompteRendu-plugin-mbbenchmark{{/tr}}
              </th>
            </tr>
            {{foreach from=$benchmarks item=_benchmark}}
              <tr>
                <td>
                  {{$_benchmark}}
                </td>
                <td class="narrow">
                  <button type="button" class="me-tertiary notext me-float-right"
                          onclick="CKEDITOR.plugins.registered.mbbenchmark.selectBenchmark('{{$_benchmark}}');">
                    <i class="fas fa-chevron-right" style="width: 20px"></i>
                  </button>
                </td>
              </tr>
            {{/foreach}}
            </tbody>
          </table>
        </td>
        <td id="benchmark" class="greedyPane me-padding-left-6">
          <div class="big-info">{{tr}}CCompteRendu-use-benchmark{{/tr}}</div>
        </td>
      {{else}}
        <td>
          <div class="big-info">{{tr}}CCompteRendu-use-benchmark-no{{/tr}}</div>
        </td>
      {{/if}}
    </tr>
    </tbody>
  </table>
</div>
