<?php

return [
    "providers" => [
        "itexmo" => [
            "email" => env("ITEXMO_EMAIL"),
            "password" => env("ITEXMO_PASSWORD"),
            "apicode" => env("ITEXMO_APICODE"),
        ]
    ]
];
