<?php

require 'vendor/autoload.php';

use GuzzleHttp\Client;

const API_URL = 'https://mach-eight.uc.r.appspot.com/';

main();

function main(): void
{
    $n = readline("Enter the number: ");

    if (!is_numeric($n) || abs($n) < 1) {
        print "Enter a natural number.\n";
        return;
    }

    $pairs = get_pairs(
        group_players_by_height(get_players()),
        $n
    );

    if (count($pairs) > 0) {
        print_pairs($pairs);
        return;
    }

    print "No matches found.\n";
}

function print_pairs(array $pairs): void
{
    array_map(fn($pair) => print "$pair\n", $pairs);
}

function get_pairs(array $groupedPlayers, int $n): array
{
    $keys = array_keys($groupedPlayers);

    $lowestSum = $keys[0] * 2;
    $highestSum = $keys[count($keys) - 1] * 2;

    $a1 = [];
    $a2 = [];
    $a3 = [];

    if ($n < $lowestSum || $n > $highestSum) {
        return $a1;
    }

    for ($start = 0, $end = count($groupedPlayers) - 1; $start < $end;) {
        $startKey = $keys[$start];
        $endKey = $keys[$end];

        $sum = $startKey + $endKey;

        if ($sum === $n) {
            get_inner_pairs($groupedPlayers[$startKey], $groupedPlayers[$endKey], $n, $a1);
        }

        if ($startKey * 2 === $n) {
            get_inner_pairs($groupedPlayers[$startKey], $groupedPlayers[$startKey], $n, $a2, $startKey);
        }

        if ($endKey * 2 === $n) {
            get_inner_pairs($groupedPlayers[$endKey], $groupedPlayers[$endKey], $n, $a3, $endKey);
        }

        if ($sum < $n) {
            $start++;
            continue;
        }

        $end--;
    }

    return array_merge($a1, $a2, $a3);
}

function get_inner_pairs(array $a1, array $a2, int $n, array &$ref, int|string $key = null): void
{
    if ($key !== null && ($key * 2) !== $n) {
        return;
    }

    $i = 0;
    foreach ($a1 as $p) {
        $j = 0;

        foreach ($a2 as $p2) {
            if ($p === $p2) {
                continue;
            }

            $ref[] = "$p - $p2";
            $j++;
        }

        if ($key !== null) {
            unset($a2[$i]);
        }

        $i++;
    }
}

function group_players_by_height(array $players): array
{
    $result = array_reduce($players, function (array|null $result, array $player) {
        $result[$player['h_in']][] = "${player['first_name']} ${player['last_name']}";
        return $result;
    });

    ksort($result);

    return $result;
}

function get_players(): array
{
    return json_decode(
        (new Client())->get(API_URL)->getBody()->getContents(),
        true
    )['values'];
}
