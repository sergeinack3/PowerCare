{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<input type="hidden" name="pref[{{$var}}]" value="{{$pref.user}}"/>
<script>
  Main.add(function(){
    var e = $("form-edit-preferences_pref[{{$var}}]");
    e.colorPicker({
      allowEmpty: true,
      change: function(color) {
        this.value = color ? color.toHexString() : '';
      }.bind(e)
    });
  });
</script>
