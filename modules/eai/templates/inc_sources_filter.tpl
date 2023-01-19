<!-- Filtres -->
<table class="main">
  <tr>
    <td style="text-align: center;">
      <form action="?" name="filterSource" method="get" onsubmit="return Source.viewAllFilter(this)">
        <input type="hidden" name="m" value="{{$m}}"/>
        <table class="main layout">
          <tr>
            <td class="separator expand" onclick="MbObject.toggleColumn(this, $(this).next())"></td>
            <td>

              <table class="form">

                <tr>

                  <!-- Name -->
                  <th style="width: auto">
                      {{mb_label object=$exchange_source field=name}}
                  </th>
                  <td style="width: auto" class="me-text-align-left">
                      {{mb_field object=$exchange_source  field=name }}
                  </td>

                  <!-- Role -> Prod ou Qualif -->
                  <th style="width: auto">
                      {{mb_label object=$exchange_source field=role}}
                  </th>
                  <td style="width: auto" class="me-text-align-left">
                      {{mb_field object=$exchange_source  field=role emptyLabel="All"}}
                  </td>

                  <!-- Actif ou Non -->
                  <th style="width: auto">
                      {{mb_label object=$exchange_source field=active}}
                  </th>
                  <td style="width: auto" class="me-text-align-left">
                      {{mb_field object=$exchange_source  field=active }}
                      <input name="active" type="radio" value="" checked="checked"/>
                      <label>{{tr}}All{{/tr}} </label>
                  </td>

                  <!-- Loggable ou Non -->
                  <th style="width: auto">
                      {{mb_label object=$exchange_source field=loggable}}
                  </th>
                  <td style="width: auto" class="me-text-align-left">
                      {{mb_field object=$exchange_source  field=loggable }}
                      <input name="loggable" type="radio" value="" checked="checked"/>
                      <label>{{tr}}All{{/tr}} </label>
                  </td>

                  <!-- Bloqué ou non -->
                  <th style="width: auto">
                      {{mb_label object=$exchange_source field=_blocked}}
                  </th>
                  <td style="width: auto" class="me-text-align-left">
                      {{mb_field object=$exchange_source  field=_blocked emptyLabel="All"}}
                  </td>

                </tr>

                <tr>
                  <td colspan="12" class="me-text-align-left">
                    <button type="submit" onclick="Source.viewAllFilter(this.up('form'))" class="search">{{tr}}Filter{{/tr}}</button>
                  </td>

                </tr>

              </table>
            </td>
          </tr>
      </form>
    </td>
  </tr>
</table>
