#!/bin/bash

###########################################
# Mediboard Bash Development Tools Helper #
###########################################
# Features :
# - handle all web services with one command (start|stop|restart|status), should be configurable)
# - run local tests (cs, stan) on files changed between current state and specific git reference
#
# How to use :
# sudo cp shell/mb-tools.sh /usr/local/bin/mb
# sudo chmod +x /usr/local/bin/mb
# mb (will display basic usage guide)
#
# Notes :
# - WSL filesystem issues with Git index manipulation (untested features)
#
# To do :
# - Backport helper, be able to backport a commit on multiple releases at once

# Global variables
web_dir=/var/www/html/mediboard
services=( php7.4-fpm apache2 mysql ssh )

#Script arguments
command=$1
scope=$2
target=$3

# Writes file list to temp file depending on required target for tests
# Scopes :
# - last : files from last commit only (implementing the last n commits is possible)
# - index : files changed on the working copy
# - branch: files than have changed between target branch and current local branch i.e. HEAD
load() {
    cecho "Loading file list from Git..."
    truncate -s 0 tmp/changed_files.txt
    case "$1" in
        last)
           git show --pretty="" --name-only HEAD^..HEAD > tmp/changed_files.txt
           cecho "Last commit has $(wc -l tmp/changed_files.txt) changed files" cyan
           cat tmp/changed_files.txt
           ;;
        index)
           cecho "Checking index is discouraged with Git under WSL for Windows (Filesystem issues)"
           git ls-files -mo --exclude-standard > tmp/changed_files.txt
           cecho "Current index has $(wc -l tmp/changed_files.txt) changed files" cyan
           cat tmp/changed_files.txt
           ;;
        branch)
           git fetch origin "$2" || cecho "Branch $2 does not exist on origin. Update manually."
           git diff --name-only HEAD..origin/$2 > tmp/changed_files.txt
           cecho "Between HEAD and $2 : $(wc -l tmp/changed_files.txt) changed files" cyan
           cat tmp/changed_files.txt
           ;;
        *)
           error "Invalid argument Scope : $1"
    esac
}

# Command that runs on error with message and usage display
error() {
  cecho "$1" red
  cecho "-- Help --"
  cecho "Command: $0 {start|stop|status|restart|clear|install|cs|stan}"
  cecho "Options:"
  cecho "- Scope: {last|index|branch}"
  exit 1
}

cs() {
    if [ -s tmp/changed_files.txt ]; then
      cecho 'Running Code Sniffer...' yellow
      vendor/bin/phpcs -p --file-list=tmp/changed_files.txt --report=full --report-json=tmp/codesniffer.json --standard=OpenxtremCodingStandard
    fi
}

# Check on last commit changed files
stan() {
    if [ -s tmp/changed_files.txt ]; then
      cecho 'Running PHPStan...' yellow
      vendor/bin/phpstan analyse $(cat tmp/changed_files.txt) --configuration phpstan.neon
    fi
}

# Build phase
build() {
   cecho 'Build phase launched...' yellow
   clear
   composer install
   cecho 'Build phase terminated.' green
}

# Fresh-install (untested, use at your own risks)
install() {
    build
    composer ox-install-config
    composer ox-install-database
}

# Discard cache and static data
clear() {
    composer ox-clear-cache
    rm -rf tmp/*
    cecho 'Cache and temporary (tmp/)folder cleared.' green
    composer dumpautoload
}

# Start services
start() {
    cecho 'Starting all services...' yellow
    for service in ${services[*]};
    do
        sudo service ${service} start
    done
    cecho 'All services started.' green
}

#Stop services
stop() {
    cecho 'Stopping all services...' yellow
    for service in ${services[*]};
    do
        sudo service ${service} stop
    done
    cecho 'All services stopped.' green
}

#Status services
status() {
    for service in ${services[*]};
    do
        sudo service ${service} status
    done
}

run() {
    case "$1" in
        start)
           start
           ;;
        stop)
           stop
           ;;
        cs)
           load "$2" "$3"
           cs
           ;;
        stan)
           load "$2" "$3"
           stan
           ;;
        build)
           build
           ;;
        clear)
           clear
           ;;
        install)
           install
           ;;
        restart)
           stop
           start
           ;;
        status)
           status
           ;;
        *)
           error "Invalid Command : $1"
    esac
}

# Colored cecho function that takes a color as single argument
cecho(){
    local exp=$1;
    local color=$2;
    if ! [[ ${color} =~ ^[0-9]$ ]] ; then
       case $(echo ${color} | tr '[:upper:]' '[:lower:]') in
        black) color=0 ;;
        red) color=1 ;;
        green) color=2 ;;
        yellow) color=3 ;;
        blue) color=4 ;;
        magenta) color=5 ;;
        cyan) color=6 ;;
        white|*) color=7 ;; # white or invalid color
       esac
    fi
    tput setaf ${color};
    echo ${exp};
    tput sgr0;
}

cd ${web_dir}
run ${command} ${scope} ${target}
