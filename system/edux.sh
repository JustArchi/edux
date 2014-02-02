#!/bin/bash
# Edux backend

eduxcron() {
	while :; do
		eduxupdate &
		eduxsftp &
		sleep 60
	done
}

eduxupdate() {
	git pull $ourRepo $ourBranch
}

eduxsftp() {
	# Check if our mirror is mounted already
	if [ $(mount | grep $server | wc -l) -eq 0 ]; then
		local remoteMountPoint="public"
		local mountPoint="/home/pmir/www/edux/sftp"
		echo "$pass" | sshfs -o allow_other -o auto_unmount -o password_stdin -oServerAliveInterval=60 -oServerAliveCountMax=5 $user@$server:$remoteMountPoint $mountPoint
	fi
}

# Initial variables
BACKGROUND=false
TESTMODE=false
USER="pmir"

# Parse args
for arg in $@; do
	case "$arg" in
		"background")
			BACKGROUND=true
			;;
		"test")
			TESTMODE=true
			;;
		*)
	esac
done

# If we're called as root, fix it, drop privileges
if [ `whoami` != "$USER" ]; then
	# Only root can read user and pass, so let's store it for future use
	rm -f /tmp/edux
	echo $(grep "user" /home/$USER/system/sftp.pass | cut -d'=' -f2) >> /tmp/edux
	echo $(grep "pass" /home/$USER/system/sftp.pass | cut -d'=' -f2) >> /tmp/edux
	chown $USER:$USER /tmp/edux
	if (! $TESTMODE); then
		su $USER -c "bash $0 &"
		exit 0
	fi
fi

# Make sure we're running in the background
if (! $TESTMODE) && (! $BACKGROUND); then
	bash $0 "background" &
	exit 0
fi

# Turn off verbose
if (! $TESTMODE); then
	exec 1>/dev/null
	exec 2>&1
fi

# Set traps to prevent leaving zombies behind
trap "kill 0" SIGINT SIGTERM EXIT

# Now when we're ready, we can make use of our user and pass
user=$(sed -n 1p /tmp/edux)
pass=$(sed -n 2p /tmp/edux)
rm -f /tmp/edux

# Let's set more variables now
cd /home/$USER/www/edux
ourRepo="origin" # eduxupdate
ourBranch=$(git rev-parse --abbrev-ref HEAD) # eduxupdate
server="sftp.pjwstk.edu.pl" # eduxsftp

# Turn on our local cron
eduxcron &

# Wait for cron to finish, which is unlikely to happen, as it's a permanent daemon
wait

# Finish
exit 0
