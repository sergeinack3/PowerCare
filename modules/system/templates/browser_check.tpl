{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=system script=browser_check}}

<script type="text/javascript">

Main.add(function() {
  BandwidthTest.launchTest();
});
</script>

<div style="font-size: 120%; margin: 50px;">

<div class="small-info">
  <div><strong>Cette page est un autodiagnostic permettant de savoir si votre poste 
  permet l'usage de Mediboard dans de bonnes conditions.</strong></div>
  <div>Les vérifications sont nature qualitatives et quantitatives.</div>
</div>

<h1>1. Navigateur</h1>
<div class="compact">Les navigateurs sont très inégaux en termes de performances et fonctionalités</div>
<div class="compact">Pour vous permettre la meilleure expérience web possible, l'usage de Mediboard exige un navigateur moderne.</div>
<div class="small-success">
  Navigateur de type <strong>Mozilla Firefox</strong> en version <strong>12</strong>.
</div>

<h1>2. Résolution</h1>
<div class="compact">Le contenu riche des affichage nécessite une résolution minimale pour des raisons évidentes de confort,
 en particulier sur des dispositifs mobiles.</div>
<div class="compact">Pensez à jouer ce test en mettant votre navigateur en condition réelles (plein écran par exemple).</div> 
<div class="small-success">
  la résolution est de <strong>1420x960</strong> supérieur au minimum de 1280x640.
</div>


<h1>3. Performances</h1>
<div class="compact">Pour plus de conforts et de fluidité, il est préférable d'utiliser poste de travail récent.</div>
<div class="compact">Cette section en vérifie les performances en vitesse de rendu, le test prend quelques secondes.</div>
<div class="small-success">
  Score de rendu <strong>1281</strong> supérieur au minimum de 600.
</div>

<h1>4. Bande passante</h1>
<div class="compact">Comme toute application Mediboard a besoin d'un minimum de bande passante.</div>
<div class="compact">Cette section vérifie les débits montants et descendants vers le serveur de Mediboard, le test prend quelques secondes.</div>

<div class="compact">
  <br />
  Débit descendant
  <div class="progressBar" style="width:300px; margin: 3px 1em; height: 20px;" id="dlspeedref">
    <div class="bar normal" style="width: 100%"></div>
    <div class="text" style="height: 100%; line-height: 20px;"><span></span> Kb/s</div>
  </div>
  <div class="progressBar" style="width:300px; margin: 3px 1em; height: 20px;" id="dlspeed">
    <div class="bar normal" style="width: 0%"></div>
    <div class="text" style="height: 100%; line-height: 20px;"><span><img src="style/mediboard_ext/images/icons/loading.gif" /></span> Kb/s</div>
  </div>

  <br />
  Débit montant
  <div class="progressBar" style="width:300px; margin: 3px 1em; height: 20px;" id="ulspeedref">
    <div class="bar normal" style="width: 100%"></div>
    <div class="text" style="height: 100%; line-height: 20px;"><span></span> Kb/s</div>
  </div>
  <div class="progressBar" style="width:300px; margin: 3px 1em; height: 20px;" id="ulspeed">
    <div class="bar normal" style="width: 0%"></div>
    <div class="text" style="height: 100%; line-height: 20px;"><span><img src="style/mediboard_ext/images/icons/loading.gif" /></span> Kb/s</div>
  </div>
</div>

  <!--
<div class="small-warning">
  <div>Débit descendant de <strong>410kb/s</strong> inférieur au minimum de 512kb/s</div>
  <div>Débit montant de <strong>389kb/s</strong> supérieur au minimum de 80kb/s</div>
</div>
  -->
