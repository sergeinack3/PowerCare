{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
  {{tr}}CCompteRendu-msg-To preview the document, click the Preview button on the left, then click File > Print Preview (for Firefox){{/tr}}
</div>

<div style="height: 700px" class="greedyPane">
  <textarea id="htmlarea" name="_source">
    {{$_source}}
  </textarea>
</div>