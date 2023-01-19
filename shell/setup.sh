#!/bin/sh

BASH_PATH=$(dirname $0)
. $BASH_PATH/utils.sh
MB_PATH=$(cd $BASH_PATH/../; pwd);

########
# Configures groups and mods for Mediboard directories
########

announce_script "Mediboard directories groups and mods"

#if [ "$#" -lt 1 ]
#then 
#  echo "Usage: $0 <sub_dir> [ -g <apache_group> ]"
#  echo " [ -g <apache_group>] is the name of the primary group for Apache user"
#  echo " [ -d <sub_dir> [modules|style] ] is the sub-directory you want to apply changes on"
#  exit 1
#fi

darwin_kernel=$(uname -a|cut -d' ' -f1)

# Pour mac
if [ $darwin_kernel = "Darwin" ]
then
  APACHE_USER=$(ps -ef|grep httpd|grep -v grep|head -2|tail -1|cut -d' ' -f4)
  APACHE_GROUP=$(groups $APACHE_USER|cut -d' ' -f1)

# Distributions linux
else
  APACHE_USER=$(ps -ef|grep apache|grep -v grep|head -2|tail -1|cut -d' ' -f1)
  APACHE_GROUP=$(groups $APACHE_USER|cut -d' ' -f3)
fi

args=$(getopt g:d: $*)

if [ $? != 0 ] ; then
  echo "Invalid argument. Check your command line"; exit 0;
fi

set -- $args
for i; do
  case "$i" in
    -g) APACHE_GROUP=$2; shift 2;;
    -d) sub_dir=$2; shift 2;;
    --) shift ; break ;;
  esac
done

grep $APACHE_GROUP: /etc/group >/dev/null
if [ $? -ne "0" ]
then
  echo "Error: group '$APACHE_GROUP' does not exist"
  exit 1
fi

# Check optionnal sub-directory
SUB_DIR=$2
  if [ "$SUB_DIR" = "modules" ]
  then
    BASE_PATH="modules/*"
  else
    if [ "$SUB_DIR" = "style" ]
    then
      BASE_PATH="style/*"
    else
      BASE_PATH="*"
      SUB_PATH="lib/ tmp/ files/ vendor/ includes/ modules/*/locales/ locales/*/ modules/eai/resources"
    fi
  fi

# Change to Mediboard directory
cd $MB_PATH

# Change group to allow Appache to access files as group
echo $BASE
chgrp -R $APACHE_GROUP $BASE_PATH
check_errs $? "Failed to change files group to '$APACHE_GROUP'" "Files group changed to '$APACHE_GROUP'!"

# Remove write access to all files for group and other
chmod -R go-w $BASE_PATH
check_errs $? "Failed to protect all files from writing" "Files protected from writing!"

# Give write access to Apache for some directories
chmod -R g+w $SUB_PATH
check_errs $? "Failed to allow Apache writing to mandatory files" "Apache writing allowed for mandatory files!"
