{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div>
  <label style="visibility: hidden;" class="missing" title="Cacher les admissions">
    <input type="checkbox" onchange="Missing.toggle(this);" />
    {{tr}}Hide{{/tr}}
    <span>0</span> admission(s) sans RPU
  </label>
</div>

<script>
Missing = {
  refresh: function() {
    var label = $$("label.missing")[0];
    var count = $$('tr.missing').length;
    label.setVisibility(count != 0);
    label.down("span").update(count);
  },

  toggle: function(checkbox) {
    $$('tr.missing').invoke('setVisible', !checkbox.checked);
  }
}
</script>
