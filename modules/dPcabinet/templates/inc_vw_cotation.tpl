{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=reglement}}

<script>
  Main.add(function() {
    Reglement.consultation_id = '{{$consult->_id}}';
    Reglement.user_id = '{{$consult->_ref_chir->_id}}';
    Reglement.only_cotation = 1;
    Reglement.cotation_full = 1;
    Reglement.reload();
  });
</script>

<div id="facturation"></div>