{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=hl7          script=test_hl7}}
{{mb_script module=dPhospi      script=affectation}}
{{mb_script module=dPpatients   script=patient}}
{{mb_script module=dPpatients   script="autocomplete"}}

<script>
  showServices = function () {
    new Url('hl7', 'ajax_show_entities')
        .requestUpdate('test_hl7v2_mfn');
  }

  Main.add(function () {
    tabs = Control.Tabs.create('tabs-test_hl7v2', true, {
      afterChange: function (name_div) {
        switch (name_div.id) {
          case 'test_hl7v2_mfn':
            showServices();
            break;
          default :
        }
      }
    });
  });
</script>

<ul id="tabs-test_hl7v2" class="control_tabs">
  <li><a href="#test_hl7v2_pam">{{tr}}CPAM{{/tr}}</a></li>
  <li><a href="#test_hl7v2_pdq">{{tr}}CPDQ{{/tr}}</a></li>
  <li><a href="#test_hl7v2_pix">{{tr}}CPIX{{/tr}}</a></li>
  <li><a href="#test_hl7v2_dec">{{tr}}CDEC{{/tr}}</a></li>
  <li><a href="#test_hl7v2_svs">{{tr}}CSVS{{/tr}}</a></li>
  <li><a href="#test_hl7v2_mfn">{{tr}}CMFN{{/tr}}</a></li>
  <li><a href="#test_hl7v2_oru">{{tr}}CORU{{/tr}}</a></li>
  <li><a href="#test_hl7v2_xdsb">{{tr}}CXDSb{{/tr}}</a></li>
  <li><a href="#test_hl7v2_mfn">MFN - National</a></li>
  <li><a href="#test_hl7v2_oru">ORU R01</a></li>
</ul>

<div id="test_hl7v2_pam" style="display: none">
  {{mb_include module=hl7 template=inc_vw_pam}}
</div>

<div id="test_hl7v2_pdq" style="display: none">
  {{mb_include module=hl7 template=inc_vw_pdq}}
</div>

<div id="test_hl7v2_pix" style="display: none">
  {{mb_include module=hl7 template=inc_vw_pix}}
</div>

<div id="test_hl7v2_dec" style="display: none">
  {{mb_include module=hl7 template=inc_vw_dec}}
</div>

<div id="test_hl7v2_svs" style="display: none">
  {{mb_include module=hl7 template=inc_vw_svs}}
</div>

<div id="test_hl7v2_mfn" style="display: none">
</div>

<div id="test_hl7v2_oru" style="display: none">
  {{mb_include module=hl7 template=inc_vw_oru}}
</div>

<div id="test_hl7v2_xdsb" style="display: none">
  {{* mb_include module=hl7 template=inc_vw_xdsb *}}
</div>