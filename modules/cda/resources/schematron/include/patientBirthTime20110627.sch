<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    patientBirthTime.sch :
    Contenu :
        Règles de contrôle de la date et heure locale de production du document 
    Paramètres d'appel :
        néant
    Historique :
        05/06/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="patientBirthTime" is-a="IVL_TS">
    <p>Conformité de la date et heure de naissance du patient, nullFlavor autorisé</p>
    <param name="elt" value="cda:patient/cda:birthTime"/>
    <param name="vue_elt" value="'patient/birthTime'"/>
    <param name="nullFlavor" value="1"/>
</pattern>