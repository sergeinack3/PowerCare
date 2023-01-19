{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function confirmRestoration(form) {
    if (!$V(form.do_it)) {
      var url = new Url("system", "ajax_count_restorable_objects");
      url.addParam("object_class", $V(form.object_class));
      url.addParam("date", $V(form.date));
      url.addParam("user_id", $V(form.user_id));
      url.requestUpdate("count-restorable", {onComplete: function(){
        var n = parseInt($("count-restorable").innerHTML);
        $(form.do_it).up("tr").setVisible(n > 0);
      }});
      return false;
    }
    else {
      return checkForm(form) && confirm("Voulez-vous réellement restaurer les objets à la date du "+$V(form.date_da)+" ?");
    }
  }

  Main.add(function() {
    getForm("restoreObjects").elements.object_class.makeAutocomplete({width: "20em"});
  });
</script>

<form name="restoreObjects" method="post" action="?m=system&tab={{$tab}}" onsubmit="return confirmRestoration(this)">
  <input type="hidden" name="m" value="system" />
  <input type="hidden" name="dosql" value="do_object_restore" />

  <table class="main form">
    <tr>
      <th></th>
      <td>
        <div class="small-warning">
          La restauration d'objets à une date antérieure est une <strong>opération critique</strong>, à utiliser avec une extrème prudence.
        </div>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$log field=object_class}}</th>
      <td>
        <select name="object_class">
          {{foreach from=$classes item=_class}}
            <option value="{{$_class}}" {{if $log->object_class == $_class}}selected{{/if}}>{{$_class}} &ndash; {{tr}}{{$_class}}{{/tr}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$log field=date}}</th>
      <td>{{mb_field object=$log field=date register=true form=restoreObjects}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$log field=user_id}}</th>
      <td>
        <select name="user_id">
          <option value="">&mdash; Tous</option>
          {{mb_include module=mediusers template=inc_options_mediuser list=$users selected=$log->user_id}}
        </select>
      </td>
    </tr>

    <tr style="display: none;">
      <th><label for="do_it">Effectuer</label></th>
      <td><input type="checkbox" name="do_it" /></td>
    </tr>

    <tr>
      <th></th>
      <td>
        <button class="change">{{tr}}Restore{{/tr}}</button>
        <div class="small-info">
          <span id="count-restorable">0</span> objets seront restaurés
        </div>
      </td>
    </tr>
  </table>
</form>
