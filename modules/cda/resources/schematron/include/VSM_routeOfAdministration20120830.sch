<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    VSM_routeOfAdministration20120830.sch :
    Contenu :
    Contrôle du code de la voie d'administration

    Paramètres d'appel :
        Néant 
    Historique :
        27/06/11 : CRI ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="RouteOfAdministration" is-a="dansJeuDeValeurs">
    <p>Conformité des demandes d'examen complémentaires</p>
    <param name="path_jdv" value="$jdv_RouteOfAdministration"/>
    <param name="vue_elt" value="ClinicalDocument/component/structuredBody/component/section/entry/substanceAdministration/routeCode"/>
    <param name="xpath_elt" value="/cda:ClinicalDocument/cda:component/cda:structuredBody/cda:component/cda:section/cda:entry/cda:substanceAdministration/cda:routeCode"/>
    <param name="nullFlavor" value="0"/>
</pattern>   


