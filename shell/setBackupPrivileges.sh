#!/bin/sh

if [ "$#" -lt 4 ]
then
  echo "Usage: $0 <username> <password> <action> <db_user>"
  echo " <username> A user with grant privilege"
  echo " <db_user>"
  echo " <action> is grant for adding select rights on the db, revoke to remove select rights"
  exit 1
fi

user=$1
password=$2
action=$3
dbuser=$4

if [ "$action" = "revoke" ]; then
  query="REVOKE SELECT ON *.* FROM '$dbuser'@'%';"
elif [ "$action" = "grant" ]; then
  query="GRANT SELECT ON *.* TO '$dbuser'@'%';"
else
  echo " The <action> must be 'grant' or 'revoke'"
fi

mysql --user=$user --password=$password --execute="$query"