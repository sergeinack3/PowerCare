<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    legalAuthenticatorName.sch :
    Contenu :
        Contrôle du nom du responsable du document 
    Paramètres d'appel :
        néant
    Historique :
        05/06/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="legalAuthenticatorName" is-a="personName">
    <p>Conformité du nom d'un proche du patient, nullFlavor interdit</p>
    <param name="elt" value="cda:legalAuthenticator/cda:assignedEntity/cda:assignedPerson/cda:name"/>
    <param name="vue_elt" value="'legalAuthenticator/assignedEntity/assignedPerson/name'"/>
    <param name="nullFlavor" value="0"/>
</pattern>