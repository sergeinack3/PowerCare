#!/bin/sh

BASH_PATH=$(dirname $0)
. $BASH_PATH/utils.sh

########
# Backups mediboard database on a daily basis
########

announce_script "Mediboard daily backup"

if [ "$#" -lt 2 ]
then
  sh $BASH_PATH/baseBackup.sh hotcopy mbadmin adminmb mediboard /var/backup
else
  user=$1
  pass=$2
  sh $BASH_PATH/baseBackup.sh hotcopy $user $pass mediboard /var/backup
fi