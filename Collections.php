<?php

$employees = [
    ['name' => 'John', 'city' => 'Dallas'],
    ['name' => 'Jane', 'city' => 'Austin'],
    ['name' => 'Jake', 'city' => 'Dallas'],
    ['name' => 'Jill', 'city' => 'Dallas'],
];

$offices = [
    ['office' => 'Dallas HQ', 'city' => 'Dallas'],
    ['office' => 'Dallas South', 'city' => 'Dallas'],
    ['office' => 'Austin Branch', 'city' => 'Austin'],
];

// $output = [
//     "Dallas" => [
//         "Dallas HQ" => ["John", "Jake", "Jill"],
//         "Dallas South" => ["John", "Jake", "Jill"],
//     ],
//     "Austin" => [
//         "Austin Branch" => ["Jane"],
//     ],
// ];

$output = $offices->groupBy('city')
                    ->mapWithKeys(function ($officeInCity, $city) use ($employees) {
                        $employeesInCity = $employees->where('city', $city)
                                                        ->pluck('name')
                                                        ->values();
                        $officeMap = collect($officesInCity)
                                        ->pluck('office')
                                        ->mapWithKeys(function ($officeName) use ($employeesInCity) {
                                            return [$officeName => $employeesInCity];
                                        });
                        return [$city => $officeMap];
                    })->array();

// print the output ...