{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=facturation script=tdb_facturiere ajax=true}}
{{mb_script module=cabinet     script=reglement      ajax=true}}

<script>
  Main.add(
    function() {
      Control.Tabs.create(
        "facture_switch",
        true,
        {
          afterChange: function(elt) {
            TdbFacturiere.switchFacture(elt.get('facture_class'), elt);
          }
        }
      );
    }
  );
</script>
<ul class="control_tabs" id="facture_switch">
  <li>
    <a href="#cabinet">
      {{tr}}CFactureCabinet{{/tr}}
    </a>
  </li>
  <li>
    <a href="#etablissement">
      {{tr}}CFactureEtablissement{{/tr}}
    </a>
  </li>
</ul>
<div id="cabinet" data-facture_class="CFactureCabinet"></div>
<div id="etablissement" data-facture_class="CFactureEtablissement"></div>
