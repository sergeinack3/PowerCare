{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_ternary var=logiciel test=$type_mb value="OX Mediboard" other="TAMM"}}
{{mb_ternary var=bdm test=$type_mb value="BCB" other="Vidal Hoptimal"}}

{{if $prat->_id}}
  {{*Header*}}
  <div style="position: absolute; top: 10px; right: 10px;">
    <button class="print not-printable" onclick="window.print();">{{tr}}Print{{/tr}}</button>
  </div>
  <div style="overflow: hidden; width: 100%; white-space: nowrap; margin: 5px 0px -10px 0px;" >
    <img src="./images/pictures/ox.jpg" alt="Ox icon" style="width: 50px; height: 50px;display: inline-block; margin-bottom: 25px;">
    <div style="display: inline-block">
      <span style="color: #3f7ebd; font-size: 20px; font-weight: bold;">{{tr}}ROSP-OpenXtrem{{/tr}}</span>
      <br>
      <span style="color: #666666; font-size: 12px; font-weight: bold;">{{tr}}ROSP-SIS{{/tr}}</span>
    </div>
    <div style="display: inline-block; width: 450px; height: 2px; border-top: 2px solid #666;"></div>
  </div>

  {{*Body*}}
  <div style="padding: 5px;">
    <p style="font-size: 1.2em">{{tr var1=$logiciel var2=$year_stats}}ROSP-justif_year{{/tr}}</p>
    <p style="font-size: 1.4em">{{tr var1=$prat var2=$logiciel var3=$anciennete}}ROSP-certificat{{/tr}}</p>
    <p style="font-size: 1.4em; padding-bottom: 8px;">{{tr}}ROSP-listing{{/tr}}</p>
    <ul style="font-size: 1.4em">
      <li>{{tr var1=$bdm}}ROSP-lap{{/tr}}</li>
      <li>{{tr}}ROSP-dmp{{/tr}}</li>
      <li>{{tr}}ROSP-Pyxvital{{/tr}}</li>
      <li>{{tr}}ROSP-dm{{/tr}}</li>
      <li>{{tr}}ROSP-mssante{{/tr}}</li>
    </ul>
  </div>
  <div style="text-align: right; width: 100%; margin-top: 50px;">
    <span style="font-size: 1.3em">{{tr}}ROSP-societe{{/tr}}</span>
    {{if $version}}<p style="font-size: 1.3em" class="me-margin-10">{{tr}}Version{{/tr}} {{$version}}</p>{{/if}}
  </div>
{{else}}
  <h1>{{tr}}common-Practitioner.choose_select{{/tr}}</h1>
{{/if}}
