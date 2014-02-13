#!/bin/bash
# Edux backend

#set -e

eduxoneshot() {
	local TASKS=""

	TASKS+="eduxsftp "
	SFTP="sftp.pjwstk.edu.pl"

	echo "EDUXONESHOT: Calling tasks: $TASKS"
	if [ $(echo "$TASKS" | wc -w) -gt 0 ]; then
		for TASK in $TASKS; do
			$TASK &
		done
	fi
	return 0
}

eduxcron() {
	local TASKS=""

	#TASKS+="eduxupdate "
	#ourRepo="origin"
	#ourBranch=$(git rev-parse --abbrev-ref HEAD)

	#TASKS+="eduxsftp "
	echo "EDUXCRON: Calling tasks: $TASKS"
	if [ $(echo "$TASKS" | wc -w) -gt 0 ]; then
		while :; do
			for TASK in $TASKS; do
				$TASK &
			done
			sleep 60
		done
	fi
	return 0
}

eduxupdate() {
	git pull $ourRepo $ourBranch
}

eduxsftp() {
	# Check if our mirror is mounted already
	if [ $(mount | grep $SFTP | wc -l) -eq 0 ]; then
		local remoteMountPoint="public"
		echo "EDUXSFTP: It looks like our SSHFS isn't available yet, mounting..."
		echo "EDUXSFTP: User: $SFTPUSER"
		if [ ! -z "$SFTPPASS" ]; then
			echo "EDUXSFTP: Password is set!"
		else
			echo "EDUXSFTP: ERROR: Password is null!"
			return 1
		fi
		echo "EDUXSFTP: Host: $SFTP"
		echo "EDUXSFTP: Mount Point: $SCRIPTDIR/../sftp"
		echo "EDUXSFTP: Remote Mount Point: $remoteMountPoint"
		echo "EDUXSTFP: Mounting now!"
		mkdir -p $SCRIPTDIR/../sftp
		echo "$SFTPPASS" | sshfs -C -o ro -o reconnect -o auto_cache -o cache_timeout=86400 -o allow_other -o auto_unmount -o password_stdin -o ServerAliveInterval=60 -o ServerAliveCountMax=5 -o StrictHostKeyChecking=no $SFTPUSER@$SFTP:$remoteMountPoint $SCRIPTDIR/../sftp
		echo "EDUXSFTP: Done!"
		if [ $(mount | grep $SFTP | wc -l) -eq 0 ]; then
			echo "EDUXSFTP: It looks like we failed, sad"
		else
			echo "EDUXSFTP: It looks like it works fine!"
		fi
	fi
}

# Initial variables
BACKGROUND=false
SCRIPTDIR=$(dirname $(realpath $0))

# Check user
USER=$(ls -l $0 | awk '{print $3}')

# This should be enough in most cases, however we could have user with /bin/false shell, therefore we can't use su -c
# Let's MAKE SURE that our user has /bin/bash shell
ORIGUSER=$USER
USER=$(id -u $USER)
USER=$(cat /etc/passwd | grep -i "$USER" | grep -i "/bin/bash" | head -n 1 | cut -d':' -f1)

# Turn on logging
echo -n "" > $SCRIPTDIR/../../private/edux.log
chown $USER $SCRIPTDIR/../../private/edux.log
exec 1>$SCRIPTDIR/../../private/edux.log
exec 2>&1
echo "EDUX: Welcome!"
echo "EDUX: Detected user $USER"
echo "EDUX: Detected folder $SCRIPTDIR"

# Parse args
for arg in $@; do
	case "$arg" in
		"background") BACKGROUND=true ;;
	esac
done

# If we're called as root, fix it, drop privileges
echo "EDUX: Called as $(whoami)"
if [ $(whoami) != "$USER" ] && [ $(whoami) != "$ORIGUSER" ]; then
	# Only root can read user and pass, so let's store it for future use
	rm -f /tmp/edux
	echo $(grep "user" $SCRIPTDIR/../../private/sftp.pass | cut -d'=' -f2) >> /tmp/edux
	echo $(grep "pass" $SCRIPTDIR/../../private/sftp.pass | cut -d'=' -f2) >> /tmp/edux
	chown $USER /tmp/edux
	echo "su $USER -c "bash $0""
	su $USER -c "bash $0 &"
	exit 0
fi

# Make sure we're running in the background
if (! $BACKGROUND); then
	bash $0 "background" &
	exit 0
fi

# Set traps to prevent leaving zombies behind
trap "kill 0" SIGINT SIGTERM EXIT

# Now when we're ready, we can make use of our user and pass
SFTPUSER=$(sed -n 1p /tmp/edux)
SFTPPASS=$(sed -n 2p /tmp/edux)
rm -f /tmp/edux

# Call all services
cd $SCRIPTDIR/..
eduxoneshot
eduxcron &

# Wait for cron to finish, which is unlikely to happen if at least one task is defined
wait

# Finish
exit 0