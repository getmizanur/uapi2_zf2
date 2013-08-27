<?php
return array (
    "endpoints" => array (
        "Login user" => array (
            "user-login-get" => array (
                "methods" => array (
                    "MethodName" => "List Users by Group ID",
                    "Synopsis" => "Login user and return session id",
                    "HttpMethod" => "GET",
                    "URI" => "/login?userid=:userid&pin=:pin&companyid=:companyid&deviceid=:deviceid&verbos=:verbos",
                    "parameters" => array (
                        ":userid" => array (
                            "Required" => "Y",
                            "Default" => "",
                            "Type" => "Integer",
                            "Description" => "User activation code",
                        ),
                        ":pin" => array (
                            "Required" => "Y",
                            "Default" => "",
                            "Type" => "Integer",
                            "Description" => "User pin",
                        ),
                        ":deviceid" => array (
                            "Required" => "Y",
                            "Default" => "",
                            "Type" => "Text",
                            "Description" => "Device ID",
                        ),
                        ":companyid" => array (
                            "Required" => "Y",
                            "Default" => "",
                            "Type" => "Integer",
                            "Description" => "Company ID",
                        ),
                        ":verbos" => array (
                            "Required" => "N",
                            "Default" => "",
                            "Type" => "Text",
                            "Description" => "Verbos (optional)",
                        ),
                    ),
                ),
            ),
        ),
        "Register Device" => array (
            "register-device-get" => array (
                "methods" => array (
                    "MethodName" => "Register device",
                    "Synopsis" => "Register a device for given user and return device pin",
                    "HttpMethod" => "GET",
                    "URI" => "/registerDevice?userid=:userid&pin=:pin&companyid=:companyid&verbos=:verbos",
                    "parameters" => array (
                        ":userid" => array (
                            "Required" => "Y",
                            "Default" => "",
                            "Type" => "Integer",
                            "Description" => "User activation code",
                        ),
                        ":pin" => array (
                            "Required" => "Y",
                            "Default" => "",
                            "Type" => "Integer",
                            "Description" => "User pin",
                        ),
                        ":companyid" => array (
                            "Required" => "Y",
                            "Default" => "",
                            "Type" => "Integer",
                            "Description" => "Company ID",
                        ),
                        ":verbos" => array (
                            "Required" => "N",
                            "Default" => "",
                            "Type" => "Text",
                            "Description" => "Verbos (optional)",
                        ),
                    ),                
                ),
            ),
        ),
    ),
);
