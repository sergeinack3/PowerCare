{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div>
  <label style="visibility: hidden;" class="veille" title="Cacher les admissions non-sorties des {{$conf.dPurgences.date_tolerance}} derniers jours">
    <input type="checkbox" id="admission_ant" onchange="Veille.toggle(this.checked);" />
    {{tr}}Hide{{/tr}}
    <span>0</span> admission(s) antérieure(s)
  </label>
</div>

<script>
Veille = {
  refresh: function() {
    var label = $$("label.veille")[0];
    var count = $$('tr.veille').length;
    label.setVisibility(count != 0);
    label.down("span").update(count);
  },

  toggle: function(checked) {
    $$('tr.veille').invoke('setVisible', !checked);
  }
}
</script>
