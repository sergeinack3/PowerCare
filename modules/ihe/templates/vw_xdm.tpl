<h2>Importation VSM.</h2>

<div class="small-info">
  Cet import permet de récupérer une VSM (Volet de Synthèse Médical) au format IHE XDM.
</div>

<form method="post" onsubmit="" name="import" enctype="multipart/form-data">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="dosql" value="do_import_ihe_xdm"/>

  <input type="hidden" name="MAX_FILE_SIZE" value="4096000" />
  <input type="file" name="import" />

  <button type="submit" class="submit">{{tr}}Save{{/tr}}</button>
</form>