<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    patientName.sch :
    Contenu :
        Contrôle du nom du patient 
    Paramètres d'appel :
        néant
    Historique :
        05/06/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="patientName" is-a="personName">
    <p>Conformité du nom du patient, nullFlavor interdit</p>
    <param name="elt" value="cda:patient/cda:name"/>
    <param name="vue_elt" value="'patient/name'"/>
    <param name="nullFlavor" value="0"/>
</pattern>