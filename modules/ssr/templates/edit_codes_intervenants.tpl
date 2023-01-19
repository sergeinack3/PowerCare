{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=code_intervenant ajax=1}}
<script>
  Main.add(function() {
    var oForm = getForm("searchIntervenant");
    CodeIntervenant.current_m = '{{$m}}';
    new Url("ssr", "ajax_interv_autocomplete")
      .autoComplete(oForm.keywords_nom, "", {
        minChars: 2,
        dropdown: true,
        width: "250px",
        select: "interv",
        callback: function(input, querystring) {
          return querystring + "&exclude_without_code=" + oForm.exclude_without_code.checked;
        },
        afterUpdateElement: function(field, selected) {
          $V(oForm.keywords_nom, selected.down('span.view').getText().trim(), false);
          CodeIntervenant.selectIntervenant(oForm.exclude_without_code.checked, selected.get("id"));
        }
      });
  });
</script>

<form name="searchIntervenant" method="get" onsubmit="return false;">
  <input type="text" name="keywords_nom" value="" class="autocomplete"/>
  <button class="erase notext" onclick="$V(this.form.keywords_nom, '', false); CodeIntervenant.selectIntervenant(null)">{{tr}}Erase{{/tr}}</button>
  <label>
    <input type="checkbox" name="exclude_without_code" onclick="CodeIntervenant.selectIntervenant(this.checked ? 1 : 0)"
           {{if $exclude_without_code == "true"}}checked{{/if}} />
   {{tr}}CIntervenantCdARR.exclude_without_code{{/tr}}
  </label>
</form>

<div id="intervenants_list">
  {{mb_include module=ssr template=edit_codes_intervenants_list}}
</div>
