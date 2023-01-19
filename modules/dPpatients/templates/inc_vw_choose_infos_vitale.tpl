<script>
  InfosVitale = {
    submitForm: function () {
      if (!window.mbHostVitale) {
        window.mbHostVitale = new VitaleCard();
      }
      var donnees_benef = [];
      $$('input.infos-vitale-check').each(function (checkbox) {
        if (checkbox.checked) {
          donnees_benef.push(checkbox.get('name'));
        }
      }.bind(this));
      var donnees_assure = [];
      $$('input.infos-vitale-assure-check').each(function (checkbox) {
        if (checkbox.checked) {
          donnees_assure.push(checkbox.get('name'));
        }
      }.bind(this));
      var show = [donnees_benef, donnees_assure];
      window.mbHostVitale.setPatientForm(getForm("editFrm"), '{{$rank}}', show, true);
    },

    onGroupElementChecked: function (groupCheckbox, index) {
      groupCheckbox.up('tr')[groupCheckbox.checked ? 'removeClassName' : 'addClassName']('not-printable');
      groupCheckbox.up('table.infos-vitale-table').select('.infos-vitale-check-' + index).each(
        function (checkbox) {
          checkbox.checked = groupCheckbox.checked;
          checkbox.up('tr')[checkbox.checked ? 'removeClassName' : 'addClassName']('not-printable');
        }
      )
    },

    onElementChecked: function (currentCheckbox, index) {
      var table = currentCheckbox.up('table.infos-vitale-table');
      var groupCheckbox = table.down('.infos-vitale-group-check-' + index);
      groupCheckbox.checked = false;
      table.select('.infos-vitale-check-' + index).each(
        function (checkbox) {
          if (groupCheckbox.checked) {
            return false;
          }
          groupCheckbox.checked = checkbox.checked ? checkbox.checked : groupCheckbox.checked;
        }
      );
    },

    initGroupCheckbox: function () {
      var previousGindex;
      $$('input.infos-vitale-check').each(function (checkbox, i) {
        var gindex = checkbox.get('gindex');
        if (checkbox.checked && previousGindex !== gindex) {
          this.onElementChecked(checkbox, checkbox.get('gindex'));
          previousGindex = gindex;
        }
      }.bind(this))

    }
  };

  Main.add(
    function () {
      InfosVitale.initGroupCheckbox();
    }
  );

</script>


<table class="tbl infos-vitale-table">
  {{foreach name="groupItem" from=$values item=_object key=_categorie}}
    {{assign var=group_item_index value=$smarty.foreach.groupItem.index}}
    <tr>
    <td class="narrow not-printable">
      <input name="" class="infos-vitale-group-check-{{$group_item_index}}" type="checkbox"
             onclick="InfosVitale.onGroupElementChecked(this,{{$group_item_index}})" />
    </td>
    <th colspan="2">
      {{tr}}{{$_categorie}}{{/tr}}
    </th>
    {{foreach name="item" from=$_object item=_value key=_label}}
      <tr>
        <td class="narrow not-printable">
          <input class="infos-vitale{{if $_categorie == "assure"}}-assure{{/if}}-check infos-vitale-check-{{$group_item_index}}"
                 type="checkbox" data-gindex="{{$group_item_index}}"
                 data-name="{{$_label}}"
                 onclick="InfosVitale.onElementChecked(this, {{$group_item_index}})" {{if $_value.checked}}checked{{/if}}/>
        </td>
        <td>
          {{tr}}{{$_label}}{{/tr}}
        </td>
        <td {{if $_label == "libelleExo" || $_label == "adresse"}}class="text"{{/if}}>
          {{if is_array($_value.value)}}
            <table class="tbl">
              {{foreach from=$_value.value item=_item_value key=_label_value}}
                <tr>
                  {{if $_label_value}}
                    <td>
                      {{tr}}{{$_label_value}}{{/tr}}
                    </td>
                  {{/if}}
                  <td>
                    {{$_item_value}}
                  </td>
                </tr>
              {{/foreach}}
            </table>
          {{else}}
            {{$_value.value}}
          {{/if}}

        </td>
      </tr>
    {{/foreach}}
    </tr>
  {{/foreach}}
  <tr>
    <td class="button not-printable" style="text-align:center;" id="button" colspan="3">
      <button type="button" class="tick" onclick="InfosVitale.submitForm();Control.Modal.close();">{{tr}}Validate{{/tr}}</button>

      <button type="button" class="print" onclick="this.up('table').print();">{{tr}}Print{{/tr}}</button>
    </td>
  </tr>
</table>