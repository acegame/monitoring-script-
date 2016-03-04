#!/bin/bash

# ./consumer-check.sh $DAEMON_NAME 
# running : 1, not running : 0 return.                                                                                              
# service 명으로 daemon 프로세서인지 체크하고 없으면 구동중인 프로세서 리스트에서 없는지 한번더 체크 함.                                                                  

if [ -z $1 ]; then 
    echo "proceess not found."
    exit 1
fi

PROCESS="$1"
PROCPID=`service $PROCESS status | awk '{print $3}' | sed 's/)//g'`

#echo "PROCPID - $PROCPID"
if [ -z $PROCPID ]; then 
    PROC2PID=`ps -ef | pgrep $PROCESS`
    if [ -z $PROC2PID ]; then 
        echo "0 ($PROCESS) Process daemon not found."
        exit 1;
    fi   
fi

ALIVECHK=`cat /proc/$PROCPID/status | grep Name | grep -c "$PROCESS"`

echo "$ALIVECHK"
exit
