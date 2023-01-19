#!/bin/sh

########
# Utilities
########

force_dir() {
  DIRPATH=$1
  if [ ! -d $DIRPATH ]
  then mkdir $DIRPATH
  fi
}

check_errs() {
  RETURNCODE=$1
  FAILURETEXT=$2
  SUCCESSTEXT=$3
  DATETIME=$(date +%Y-%m-%dT%H-%M-%S)

  cecho "[${DATETIME}] \c"
  cecho ">> status: \c" bold

  if [ "${RETURNCODE}" -ne "0" ]
  then
    cecho "ERROR # ${RETURNCODE} : ${FAILURETEXT}" red
    # as a bonus, make our script exit with the right error code.
    cecho "...Exiting..." bold
    exit ${RETURNCODE}
  fi

  cecho "${SUCCESSTEXT}"
}

warn_errs() {
  RETURNCODE=$1
  FAILURETEXT=$2
  SUCCESSTEXT=$3
  DATETIME=$(date +%Y-%m-%dT%H-%M-%S)

  cecho "[${DATETIME}] \c"
  cecho ">> warning:: \c" bold

  if [ "${RETURNCODE}" -ne "0" ]
  then
    cecho "ERROR # ${RETURNCODE} : ${FAILURETEXT}"
  else
    cecho "${SUCCESSTEXT}"
  fi
}

announce_script() {
  SCRIPTNAME=$1
  cecho "--- $SCRIPTNAME ($(date)) ---" bold
}

info_script() {
  INFO=$1
  DATETIME=$(date +%Y-%m-%dT%H-%M-%S)
  cecho "[${DATETIME}] \c"
  cecho ">> info: \c" bold
  cecho "${INFO}"
}

force_file() {
  FILE=$1
  if [ ! -e $FILE ]
  then touch $FILE
  fi
}

cecho () {
  # $1 = message
  # $2 = color
  message=$1                   
  color=${2:-"default"}        # Defaults to nothing, if not specified.

  case $color in
    bold    ) color="\033[1m"    ;;
    black   ) color="\033[0;30m" ;;
    red     ) color="\033[0;31m" ;;
    green   ) color="\033[0;32m" ;;
    yellow  ) color="\033[0;33m" ;;
    blue    ) color="\033[0;34m" ;;
    magenta ) color="\033[0;35m" ;;
    cyan    ) color="\033[0;36m" ;;
    white   ) color="\033[0;37m" ;;
    default ) color='' ;;
    *) 
      echo "Usage: second color param should be one of black, red, green, yellow, blue, magenta, cyan, white" 
      return 1
      ;;
  esac

  undashed=$(ls -l /bin/sh | grep bash)
  if [ -z "$undashed" ]; then
    echo "$color$message"
  else
    echo -e "$color$message"
  fi

  tput sgr0                    # Reset to normal.
}
