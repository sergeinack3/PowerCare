<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    healthcareFacilityTypeCode.sch :
    Contenu :
        Contrôle du type d'organisation de prise en charge du patient dans l'en-tête CDA  (nullFlavor non autorisé)
        Spécialisation du pattern dansJeuDeValeurs
    Paramètres d'appel :
        Néant 
    Historique :
        31/05/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="healthcareFacilityTypeCode" is-a="dansJeuDeValeurs">
    <p>Conformité au CI-SIS du healthcareFacilityTypeCode de la prise en charge</p>
    <param name="path_jdv" value="$jdv_healthcareFacilityTypeCode"/>
    <param name="vue_elt" value="'componentOf/encompassingEncounter/location/healtCareFacility/code'"/>
    <param name="xpath_elt" value="cda:encompassingEncounter/cda:location/cda:healthCareFacility/cda:code"/>
    <param name="nullFlavor" value="0"/>
</pattern>   