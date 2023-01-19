{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=pref_context value=1}}

<script>
  resetTokens{{$var}} = function(button) {
    button.hide();
    $V(button.form.elements['pref[{{$var}}]'], '');
    button.next("div").update("&mdash; Idem");
  };

  moveToken{{$var}} = function(area, sens) {
    sens = sens || "up";

    var div = area.up("div");

    var sibling = null;

    if (sens === "up") {
      sibling = div.previous("div");
    }

    if (sens === "down") {
      sibling = div.next("div");
    }

    if (!sibling) {
      return;
    }

    div = div.remove();

    if (sens === "up") {
      sibling.insert({before: div});
    }
    else {
      sibling.insert({after: div});
    }

    var tokens = div.up("div").select("div").collect(function(_div) {
      return _div.get("token");
    });

    var input = div.up("form").elements["pref[{{$var}}]"];

    $V(input, tokens.join("|"));

    {{if $pref_context}}
      input.next("button").show();
    {{/if}}
  };
</script>

<input type="hidden" name="pref[{{$var}}]" value="{{if $pref.user}}{{$pref.user}}{{else}}{{$pref.default}}{{/if}}" />

{{if $pref_context}}
  <button type="button" class="cancel notext" style="float: right; {{if !$pref.user}}display: none;{{/if}}"
          onclick="resetTokens{{$var}}(this);">{{tr}}Cancel{{/tr}}</button>
{{/if}}

{{mb_include module=admin template=inc_pref_value_rank pref_readonly=0}}