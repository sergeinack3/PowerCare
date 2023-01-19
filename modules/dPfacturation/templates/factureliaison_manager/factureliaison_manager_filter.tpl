{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=facture_selected_guid value=""}}
<script>
  Main.add(
    function() {
      FactuTools.FactuLiaisonManager.triggerFastSelection('{{$selected_guid}}', '{{$facture_selected_guid}}');
    }
  );
</script>
{{mb_include module=facturation template="tdb_cotation/tdb_cotation_filter" use_factureliaison=true}}
