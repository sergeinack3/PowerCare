{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function() {
  
    $("view_dmi").setStyle({height: document.viewport.getHeight()*0.95+"px"});
  });
</script>
<iframe id="view_dmi" src="{{$url_application}}" style="width: 100%;"/>
