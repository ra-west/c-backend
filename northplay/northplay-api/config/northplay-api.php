<?php
// config for Northplay/NorthplayApi
return [
    'frontend' => env('APP_FRONTEND', 'east.ovh'),
    'backend' =>  env('APP_URL', 'east.ovh'),
    'registration_bonus' => [
        "enabled" => true,
        "currency" => "LTC",
        "amount" => 110000000, //int amount, 1 unit = 100000000 (8 decimals)
    ],
    'loyalty_levels' => [
        [
            "id" => 0,
            "rank" => "Unranked",
            "points" => 0,
            "freespins" => false,
            "freespins_slot" => false,
        ],
        [
            "id" => 1,
            "rank" => "Copper",
            "points" => 500,
            "freespins" => 10,
            "freespins_slot" => "softswiss/PennyPelican",
        ],
        [
            "id" => 2,
            "rank" => "Iron",
            "points" => 2500,
            "moneywheel_average" => 5,
            "freespins" => 25,
            "freespins_slot" => "softswiss/PennyPelican",
        ],
        [
            "id" => 3,
            "rank" => "Silver",
            "points" => 10000,
            "freespins" => 50,
            "freespins_slot" => "softswiss/PennyPelican",
        ],
        [
            "id" => 4,
            "rank" => "Gold",
            "points" => 50000,
            "freespins" => 100,
            "freespins_slot" => "softswiss/PennyPelican",
        ],
        [
            "id" => 5,
            "rank" => "Platinum",
            "points" => 200000,
            "freespins" => 200,
            "freespins_slot" => "softswiss/PennyPelican",
        ],
        [
            "id" => 6,
            "rank" => "Diamond",
            "points" => 450000,
            "freespins" => 500,
            "freespins_slot" => "softswiss/PennyPelican",
        ],
	],
    'evercookie' => [
        'name' => env('EVERCOOKIE_COOKIE_NAME', 'northplay'),
    ],
    'cryptapi' => [
        "callback" => (env('APP_URL')."/casino/callbacks/cryptapi"),
        "BTC" => [
            "ticker" => "btc",
            "to_address" => "bc1qjh7dual33yvezf62yl4w6vxeewe68xesuj2ejx",
        ],
        "TRX" => [
            "ticker" => "trx",
            "to_address" => "TXgEGdqMV8Xt5xbDrkyrJh7pV3tKhMyiTo",
        ],
        "LTC" => [
            "ticker" => "ltc",
            "to_address" => "LRkKNouarjfvoVL1F2Re85bHkbwpNRM9MY",
        ],
        "ETH" => [
            "ticker" => "eth",
            "to_address" => "0xBdBfcf8cE48C0BebC75Da4E12794d298b2Bb836C",
        ],
        "BNB" => [
            "ticker" => "bep20/bnb",
            "to_address" => "0xBdBfcf8cE48C0BebC75Da4E12794d298b2Bb836C",
        ],
        "DOGE" => [
            "ticker" => "doge",
            "to_address" => "A15TWJPjaSRzXC5MNuifKJnKswETQEkAKa",
        ],
    ],
    'cryptapi_ticker' => [
            "btc" => "BTC",
            "ltc" => "LTC",
            "bep20/bnb" => "BNB",
            "doge" => "DOGE",
            "eth" => "ETH",
            "trx" => "TRX",
    ],
    'openai_key' => "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",
    'seeder_data' =>  [
        "config" => [
                "environment" => [
                    "master_access_key" => "key0214190129412",
                    "force_cache_reset_metadata" => "yes",
                    "force_exchange_rate_update" => "yes",
                ],
                "exchange_rate_keys" => [
                    "coinmarketcap" => "c0a60bf0-ffef-4c50-8e76-2d64ff8878ee",
                ],
                "scoobiedog" => [
                    "sd_host" => env('SCOOBIEDOG_HOST', 'dev.northplay.me'),
                    "sd_apikey" => "e03b960509a9f281b708de47ad1f1056",
                    "sd_secret" => "aPjo8uBQDhWh",
                ],
                "global" => [
                        "links_twitter" => "https://twitter.com/casino",
                        "links_github" => "https://github.com/ryan-northplay",
                        "links_email" => "hiho@east.ovh",
                        "page_url" => "https://casino.east.ovh",
                ],
        ],
        "currency" => [
            "USD" => [
                "symbol_id" => "USD",
                "name" => "US Dollar",
                "type" => "fiat",
                "decimals" => "2",
                "rate_usd" => "1.00",
                "active" => false,
            ],
            "EUR" => [
                "symbol_id" => "EUR",
                "name" => "Euro",
                "type" => "fiat",
                "decimals" => "2",
                "rate_usd" => "0.9114110",
                "active" => false,
            ],
            "GBP" => [
                "symbol_id" => "GBP",
                "name" => "British Pound",
                "type" => "fiat",
                "decimals" => "2",
                "rate_usd" => "0.8033450",
                "active" => false,
            ],
            "BTC" => [
                "symbol_id" => "BTC",
                "name" => "Bitcoin",
                "type" => "crypto",
                "decimals" => "8",
                "rate_usd" => "0.0000520",
                "active" => true,
            ],
            "LTC" => [
                "symbol_id" => "LTC",
                "name" => "Litecoin",
                "type" => "crypto",
                "decimals" => "8",
                "rate_usd" => "0.0189060",
                "active" => true,
            ],
            "ETH" => [
                "symbol_id" => "ETH",
                "name" => "Ethereum",
                "type" => "crypto",
                "decimals" => "8",
                "rate_usd" => "0.0007640",
                "active" => true,
            ],
            "TRX" => [
                "symbol_id" => "TRX",
                "name" => "Tron",
                "type" => "crypto",
                "decimals" => "8",
                "rate_usd" => "11",
                "active" => true,
            ],
            "BNB" => [
                "symbol_id" => "BNB",
                "name" => "BNB",
                "type" => "crypto",
                "decimals" => "8",
                "rate_usd" => "0.0036330",
                "active" => true,
            ],
            "DOGE" => [
                "symbol_id" => "DOGE",
                "name" => "Dogecoin",
                "type" => "crypto",
                "decimals" => "8",
                "rate_usd" => "16.6548880",
                "active" => true,
            ],
        ],
    ],
];
