#!/bin/bash
# Edux backend

set -e

eduxoneshot() {
	case "$1" in
		"START") ;;
		"STOP") ;;
		*) return 1
	esac
	local MODE="$1"
	local TASKS=""

	TASKS+="eduxsftp "

	if [ $(echo "$TASKS" | wc -w) -gt 0 ]; then
		for TASK in $TASKS; do
			echo "EDUXONESHOT: Calling task: $TASK"
			$TASK "$MODE"
		done
	fi
	return 0
}

eduxphpcron() {
	php -f $SCRIPTDIR/../../private/cron.php >$SCRIPTDIR/../../private/cron.log 2>&1
}

eduxcron() {
	local TASKS=""

	if [ -e $SCRIPTDIR/../../private/cron.php ]; then
		echo "EDUXCRON: Found cron.php file, executing every 60 seconds"
		TASKS+="eduxphpcron "
	else
		echo "EDUXCRON: Could not find cron.php file, eduxphpcron is disabled"
	fi
	#TASKS+="eduxupdate "

	if [ $(echo "$TASKS" | wc -w) -gt 0 ]; then
		while :; do
			for TASK in $TASKS; do
				echo "EDUXCRON: Calling task: $TASK"
				$TASK &
			done
			sleep 60
		done
	fi
	return 0
}

eduxupdate() {
	git pull origin master
}

eduxstart() {
	echo "EDUX: Starting all services!"
	eduxoneshot "START"
}

eduxstop() {
	echo "EDUX: Stopping all services!"
	eduxoneshot "STOP"
}

eduxexit() {
	eduxstop
	echo "EDUX: Exiting!"
	echo $(date)
	kill 0 # Bye
	exit 0
}

eduxwatcher() {
	local WATCHFOLDER="$SCRIPTDIR/../../private/backend"
	if [ ! -d $WATCHFOLDER ]; then
		echo "EDUXWATCHER: ERROR, NO $WATCHFOLDER"
		return 1
	fi
	echo "EDUXWATCHER: Active!"
	while :; do
		inotifywait -qe create $WATCHFOLDER
		for i in "RESTART" "STOP"; do
			if [ -e $WATCHFOLDER/$i ]; then
				rm -f $WATCHFOLDER/$i
				case "$i" in
					"RESTART") eduxstop && eduxstart ;;
					"STOP") eduxexit ;;
				esac
				break # This will break for loop, not while
			fi
		done
		sleep 1
	done
}

eduxsftp() {
	local SFTP="sftp.pjwstk.edu.pl"
	case "$1" in
		"START")
			if [ $(mount | grep $SFTP | wc -l) -eq 0 ]; then
				local remoteMountPoint="public"
				echo "EDUXSFTP: It looks like our SSHFS isn't available yet, mounting..."
				if [ ! -z "$SFTPPASS" ]; then
					echo "EDUXSFTP: Password is set!"
				else
					echo "EDUXSFTP: ERROR: Password is null!"
					return 1
				fi
				echo "EDUXSFTP: User: $SFTPUSER + Host: $SFTP + Mount Point: $SCRIPTDIR/../sftp + Remote Mount Point: $remoteMountPoint"
				echo "EDUXSTFP: Mounting now!"
				mkdir -p $SCRIPTDIR/../sftp
				echo "$SFTPPASS" | sshfs -C -o ro -o reconnect -o auto_cache -o cache_timeout=86400 -o allow_other -o auto_unmount -o password_stdin -o ServerAliveInterval=60 -o ServerAliveCountMax=5 -o StrictHostKeyChecking=no $SFTPUSER@$SFTP:$remoteMountPoint $SCRIPTDIR/../sftp
				echo "EDUXSFTP: Done!"
				if [ $(mount | grep $SFTP | wc -l) -eq 0 ]; then
					echo "EDUXSFTP: It looks like we failed mounting, sad"
				else
					echo "EDUXSFTP: It looks like it mounted fine!"
				fi
			else
				echo "EDUXSFTP: It looks like we have mounted SFTP already!"
			fi
		;;
		"STOP")
			if [ $(mount | grep $SFTP | wc -l) -eq 0 ]; then
				echo "EDUXSFTP: SFTP is unmounted already!"
			else
				echo "EDUXSTFP: Unmounting now!"
				kill $(pidof sshfs)
				echo "EDUXSFTP: Done!"
				sleep 1
				if [ $(mount | grep $SFTP | wc -l) -eq 0 ]; then
					echo "EDUXSFTP: It looks like it unmounted fine!"
				else
					echo "EDUXSFTP: It looks like we failed unmounting!"
				fi
			fi
		;;
	esac
}

# Initial variables
BACKGROUND=false
SCRIPTDIR=$(dirname $(realpath $0))
SECUREDIR="/var/www/clients/client2"

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

# Parse args
for arg in $@; do
	case "$arg" in
		"background") BACKGROUND=true ;;
	esac
done

# If we're called as root, fix it, drop privileges
if [ $(whoami) != "$USER" ] && [ $(whoami) != "$ORIGUSER" ]; then
	# Only root can read user and pass, so let's store it for future use
	rm -f $SECUREDIR/edux.pass
	echo $(grep "user" $SCRIPTDIR/../../private/sftp.pass | cut -d'=' -f2) >> $SECUREDIR/edux.pass
	echo $(grep "pass" $SCRIPTDIR/../../private/sftp.pass | cut -d'=' -f2) >> $SECUREDIR/edux.pass
	chown $USER $SECUREDIR/edux.pass
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

echo "EDUX: Welcome!"
echo $(date)
echo "EDUX: Detected user $USER"
echo "EDUX: Detected folder $SCRIPTDIR"

# Now when we're ready, we can make use of our user and pass
SFTPUSER=$(sed -n 1p $SECUREDIR/edux.pass)
SFTPPASS=$(sed -n 2p $SECUREDIR/edux.pass)
rm -f $SECUREDIR/edux.pass

# Call all services
cd $SCRIPTDIR/..
eduxstart
eduxcron &
eduxwatcher &

# Wait for all childs to finish, which is unlikely to happen if at least one task is defined
echo "EDUX: Waiting for all childs to finish..."
wait

# Finish
eduxexit
exit 0