<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    administrativeGenderCode.sch :
    Contenu :
        Règles de contrôle du sexe d'un paient ou d'un sujet de soins 
    Paramètres d'appel :
        néant
    Historique :
        08/06/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="administrativeGenderCode">
    <p>Conformité du code sexe du patient ou du subject, nullFlavor autorisé</p>
    <rule context="cda:administrativeGenderCode">
        <let name="NF" value="@nullFlavor"/>
        <let name="sex" value="@code"/>
        <assert test="$sex = 'M' or $sex = 'F' or $sex = 'U' or $NF = 'UNK' or $NF = 'NASK' or $NF = 'ASKU' or $NF = 'NAV' or $NF = 'MSK'">
            Erreur de conformité CI-SIS : l'élément administrativeGenderCode doit être présent, avec code sexe ou un nullFlavor autorisé 
            (valeur trouvée <value-of select="@*"/>).
        </assert>
    </rule>
</pattern>