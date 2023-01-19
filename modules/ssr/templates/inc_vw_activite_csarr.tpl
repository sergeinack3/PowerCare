{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm("editCsarr");

    // Disabled the seance type when the user changes the code
    {{foreach from=$type_seance key=key_type item=_type}}
      var type = '{{$key_type}}';
      var value_type = '{{$_type}}';

      form.select('option[value="'+ type +'"]')[0].disabled = value_type;
    {{/foreach}}
  });
</script>

{{foreach from=$activite->_ref_modulateurs item=_modulateur}}
  <label title="{{$_modulateur->_libelle}}">
    <input type="checkbox" class="modulateur" id="modulateur_{{$_modulateur->modulateur}}"
           onchange="CsARR.addModulateurValues(this.form, this);"
           value="{{$_modulateur->modulateur}}" />
     {{$_modulateur->modulateur}}
  </label>
{{foreachelse}}
  <span class="empty">
    {{tr}}CElementPrescriptionToCsarr-Modulateurs.none{{/tr}}
  </span>
{{/foreach}}
