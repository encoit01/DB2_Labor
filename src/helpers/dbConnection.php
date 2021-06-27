<?php
function dbConnection() {
        $serverName = "mssqlserver,1433";
        $databaseName = "company";
        $uid = "encoit01";
        $pwd = "Password1234";
        return new PDO("sqlsrv:Server = $serverName; Database = $databaseName;", $uid, $pwd);
    }