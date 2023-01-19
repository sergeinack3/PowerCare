{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
function setClose(icone) {
  var oSelector = window.opener.IconeSelector;
  oSelector.set(icone);
  window.close();
}
</script>

<h2>{{tr}}CConsultationCategorie-Available Icon|pl{{/tr}}</h2>
<div>
  {{foreach from=$icones item="icone"}}
    <a href="#"><img src="./modules/dPcabinet/images/categories/{{$icone}}" onclick="setClose('{{$icone}}')" alt="" /></a>
  {{/foreach}}
</div>