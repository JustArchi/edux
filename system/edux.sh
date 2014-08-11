#!/bin/bash

#     _             _     _ _   _      _
#    / \   _ __ ___| |__ (_) \ | | ___| |_
#   / _ \ | '__/ __| '_ \| |  \| |/ _ \ __|
#  / ___ \| | | (__| | | | | |\  |  __/ |_
# /_/   \_\_|  \___|_| |_|_|_| \_|\___|\__|
#
# Copyright 2014 Łukasz "JustArchi" Domeradzki
# Contact: JustArchi@JustArchi.net
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#    http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

set -e

eduxoneshot() {
	local MODE="$1"
	case "$MODE" in
		"START") ;;
		"STOP") ;;
		*) return 1
	esac

	local TASKS=()
	TASKS+=('eduxsftp')

	if [[ ! -z "${TASKS[@]}" ]]; then
		for TASK in "${TASKS[@]}"; do
			echo "EDUXONESHOT: Calling task: $TASK"
			"$TASK" "$MODE"
		done
	fi
	return 0
}

eduxphpcron() {
	php -f "$SCRIPTDIR/../../private/cron.php" > "$SCRIPTDIR/../../private/cron.log" 2>&1
}

eduxcron() {
	local TASKS=()

	if [[ -e "$SCRIPTDIR/../../private/cron.php" ]]; then
		echo "EDUXCRON: Found cron.php file, executing every 60 seconds"
		TASKS+=('eduxphpcron')
	else
		echo "EDUXCRON: Could not find cron.php file, eduxphpcron is disabled"
	fi

	if [[ ! -z "${TASKS[@]}" ]]; then
		while :; do
			for TASK in "${TASKS[@]}"; do
				echo "EDUXCRON: Calling task: $TASK"
				"$TASK" &
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
	date
	kill 0 # Bye
	exit 0
}

eduxwatcher() {
	local WATCHFOLDER="$SCRIPTDIR/../../private/backend"
	mkdir -p "$WATCHFOLDER"
	find "$WATCHFOLDER" -type f -exec rm -f {} \;
	echo "EDUXWATCHER: Active!"
	while :; do
		inotifywait -qqe create "$WATCHFOLDER"
		find "$WATCHFOLDER" -type f -mindepth 1 -maxdepth 1 | while read EVENT; do
			rm -f "$EVENT"
			case "$(basename "$EVENT")" in
				"RESTART") eduxstop && eduxstart ;;
				"STOP") eduxexit ;;
			esac
		done
		sleep 1
	done
}

eduxsftp() {
	local SFTP="sftp.pjwstk.edu.pl"
	local MODE="$1"
	case "$MODE" in
		"START")
			if [[ "$(mount | grep -q "$SFTP"; echo $?)" -ne 0 ]]; then
				local remoteMountPoint="public"
				echo "EDUXSFTP: It looks like our SSHFS isn't available yet, mounting..."
				if [[ ! -z "$SFTPPASS" ]]; then
					echo "EDUXSFTP: Password is set!"
				else
					echo "EDUXSFTP: ERROR: Password is null!"
					return 1
				fi
				echo "EDUXSFTP: User: $SFTPUSER + Host: $SFTP + Mount Point: $SCRIPTDIR/../sftp + Remote Mount Point: $remoteMountPoint"
				echo "EDUXSTFP: Mounting now!"
				mkdir -p "$SCRIPTDIR/../sftp"
				echo "$SFTPPASS" | sshfs -C -o password_stdin -o modules=iconv,from_code=ISO-8859-2,to_code=UTF-8 -o ro -o reconnect -o auto_cache -o allow_other -o auto_unmount -o ServerAliveInterval=60 -o ServerAliveCountMax=5 -o StrictHostKeyChecking=no "$SFTPUSER@$SFTP:$remoteMountPoint" "$SCRIPTDIR/../sftp"
				echo "EDUXSFTP: Done!"
				if [[ "$(mount | grep -q "$SFTP"; echo $?)" -ne 0 ]]; then
					echo "EDUXSFTP: It looks like we failed mounting, sad"
				else
					echo "EDUXSFTP: It looks like it mounted fine!"
				fi
			else
				echo "EDUXSFTP: It looks like we have mounted SFTP already!"
			fi
		;;
		"STOP")
			if [[ "$(mount | grep -q "$SFTP"; echo $?)" -ne 0 ]]; then
				echo "EDUXSFTP: SFTP is unmounted already!"
			else
				echo "EDUXSTFP: Unmounting now!"
				kill "$(pidof sshfs)"
				echo "EDUXSFTP: Done!"
				sleep 1
				if [[ "$(mount | grep -q "$SFTP"; echo $?)" -ne 0 ]]; then
					echo "EDUXSFTP: It looks like it unmounted fine!"
				else
					echo "EDUXSFTP: It looks like we failed unmounting!"
				fi
			fi
		;;
	esac
}

# Initial variables
BACKGROUND=0
SCRIPTDIR="$(dirname "$0")"

# Check user
# This should be enough in most cases, however we could have user with /bin/false shell, therefore we can't use su -c
# Let's MAKE SURE that our user has /bin/bash shell
ORIGUSER="$(stat -c %U "$0")"
USER="$(grep "$(id -u "$ORIGUSER")" /etc/passwd | grep -i "/bin/bash" | head -n 1 | cut -d':' -f1)"

# Parse args
for ARG in "$@"; do
	case "$ARG" in
		background|BACKGROUND) BACKGROUND=1 ;;
	esac
done

# Make sure we're running in the background
if [[ "$BACKGROUND" -ne 1 ]]; then
	echo
	bash "$0" "background" $@ &
	exit 0
fi

SharedPasswords="$SCRIPTDIR/../../private/edux.pass"

# If we're called as root, fix it, drop privileges
if [[ "$(whoami)" != "$USER" && "$(whoami)" != "$ORIGUSER" ]]; then
	# Only root can read user and pass, so let's store it for future use
	Passwords="$SCRIPTDIR/../../private/sftp.pass"
	SharedPasswords="$SCRIPTDIR/../../private/edux.pass"
	cp -p "$Passwords" "$SharedPasswords"
	chown "$USER" "$SharedPasswords"
	su "$USER" -c "bash $0 $@ &"
	exit 0
elif [[ ! -f "$SharedPasswords" ]]; then
	echo "ERROR: $SharedPasswords could not be found!"
else
	SFTPUSER="$(sed -n 1p "$SharedPasswords")"
	SFTPPASS="$(sed -n 2p "$SharedPasswords")"
	rm -f "$SharedPasswords"
fi

# Turn on logging
exec 1>"$SCRIPTDIR/../../private/edux.log"
exec 2>&1

# Set traps to prevent leaving zombies behind
trap "kill 0" SIGINT SIGTERM EXIT

date
echo "EDUX: Started!"
echo "EDUX: Detected user $USER"
echo "EDUX: Detected folder $SCRIPTDIR"

# Call all services
eduxstart
eduxcron &
eduxwatcher &

# Wait for all childs to finish, which is unlikely to happen if at least one task is defined
echo "EDUX: Waiting for all childs to finish..."
wait

# Finish
eduxexit
exit 0
