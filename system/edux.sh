#!/bin/bash

# Sync local repo with remote git repo
update() {
	cd ~/www/edux
	local ourBranch=`git rev-parse --abbrev-ref HEAD`
	local ourRepo="origin"
	while :; do
		git pull $ourRepo $ourBranch
		if [ -e DEVELOPMENT ]; then
			sleep 60
		else
			sleep 3600
		fi
	done
}

# Initial variables
BACKGROUND=false
USER="pmir"

# Parse args
for arg in $@; do
	case "$arg" in
		"background")
			BACKGROUND=true
			;;
		*)
			continue
	esac
done

# If we're called as root, fix it
if [ `whoami` != "$USER" ]; then
	su $USER -c "bash $0 $@ &"
	exit 0
fi

# Make sure we're running in the background
if (! $BACKGROUND); then
	bash $0 "background" $@ &
	exit 0
fi

# Turn off verbose
exec 1>/dev/null
exec 2>&1

# Set traps to prevent leaving zombies behind
trap "kill 0" SIGINT SIGTERM EXIT

# Turn on all daemons
update &

# Wait for them to finish, which is unlikely to happen, as they're permanent daemons
wait

# Finish
exit 0
