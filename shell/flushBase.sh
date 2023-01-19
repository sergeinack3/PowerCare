#!/bin/sh

BASH_PATH=$(dirname $0)
. $BASH_PATH/utils.sh
MB_PATH=$(cd $BASH_PATH/../; pwd);

########
# Flush database
########

announce_script "Database flush"

if [ "$#" -lt 3 ]
then 
  echo "Usage: $0 <username> <password> <database>"
  echo " <username> to access database"
  echo " <password> authenticate user"
  echo " <database> to flush, eg mediboard"
  exit 1
fi

user=$1
password=$2
database=$3

info_script "Flushing '$database' database"

tables=$(mysql -u $user -p$password -e "select table_name from information_schema.tables where table_schema='$database' order by table_name")

if [ ${#tables} = 0 ]
then
  check_errs 2 "Failed to retrieve tables from $database database"
fi

# Replace spaces by ',' for the flush command
tables=$(echo $tables|tr ' ' ',')

mysql -u $user -p$password -e "flush tables $tables" $database

check_errs $? "Failed to flush tables from $database database" "Succesfully flushed tables from $database database"