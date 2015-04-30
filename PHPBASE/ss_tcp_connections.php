<?php
/* Cacti */
/* do NOT run this script through a web browser */
if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
   die("<br><strong>This script is only meant to run at the command line.</strong>");
}

$no_http_headers = true;

/* display No errors */
error_reporting(0);

if (!isset($called_by_script_server)) {
        array_shift($_SERVER["argv"]);

        print call_user_func_array("ss_tcp_connections", $_SERVER["argv"])."\n";
}

function ss_tcp_connections ($hostname, $snmp_auth) {
        $snmp = explode(":", $snmp_auth);
        $snmp_version = $snmp[0];
        $snmp_port    = $snmp[1];
        $snmp_timeout = $snmp[2];

        $snmp_auth_username   = "";
        $snmp_auth_password   = "";
        $snmp_auth_protocol   = "";
        $snmp_priv_passphrase = "";
        $snmp_priv_protocol   = "";
        $snmp_context         = "";
        $snmp_community = "";

        if ($snmp_version == 3) {
                $snmp_auth_username   = $snmp[4];
                $snmp_auth_password   = $snmp[5];
                $snmp_auth_protocol   = $snmp[6];
                $snmp_priv_passphrase = $snmp[7];
                $snmp_priv_protocol   = $snmp[8];
                $snmp_context         = $snmp[9];
        }else{
                $snmp_community = $snmp[3];
        }

        # setup shell command with options for version 2c and version 3
        $cmd = '/usr/bin/snmpnetstat -v '.($snmp_version == 2 ? '2c' : '1').' '.
             ($snmp_version == 3
                ? '-u '.$snmp_auth_username.' -A '.$snmp_auth_password.' -a '.$snmp_auth_protocol.' -X '.$snmp_priv_passphrase.' -x '.$snmp_priv_protocol.' -n '.$snmp_context
                : '-c '.$snmp_community ).' -t '.$snmp_timeout.' -Cn -Cp tcp '.$hostname.':'.$snmp_port;

        # initialize all the vars we'll use
        $ret = '';
        $_estab = $_listen = $_timewait = $_timeclose = $_finwait1 = $_finwait2 = $_synsent = $_synrecv = $_closewait = 0;

        # run the command and loop through all the lines returned
        exec($cmd, $lines);
        foreach ($lines as $l){
                if (strpos($l, 'ESTABLISHED'))  $_estab++;
                if (strpos($l, 'LISTEN'))       $_listen++;
                if (strpos($l, 'TIMEWAIT'))     $_timewait++;
                if (strpos($l, 'TIMECLOSE'))    $_timeclose++;
                if (strpos($l, 'FINWAIT1'))     $_finwait1++;
                if (strpos($l, 'FINWAIT2'))     $_finwait2++;
                if (strpos($l, 'SYNSENT'))      $_synsent++;
                if (strpos($l, 'SYNRECV'))      $_synrecv++;
                if (strpos($l, 'CLOSEWAIT'))    $_closewait++;
        }

        return "established:$_estab listen:$_listen timewait:$_timewait timeclose:$_timeclose finwait1:$_finwait1 finwait2:$_finwait2 synsent:$_synsent synrecv:$_synrecv closewait:$_closewait";
}

?>
