{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  refreshFraisDivers = function(){
    var url = new Url('dPccam', 'refreshFraisDivers');
    url.addParam('object_guid', '{{$object->_guid}}');
    url.requestUpdate('editFraisDivers-{{$object->_guid}}');
  };
  Main.add(refreshFraisDivers);
</script>

<div id="editFraisDivers-{{$object->_guid}}"></div>
