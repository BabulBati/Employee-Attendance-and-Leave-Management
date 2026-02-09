<?php
session_start();

// $pdo = new PDO(
//     "mysql:host=localhost;dbname=attendance_system",
//     "root",
//     "",
//     [
//         PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
//         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
//     ]
// );

$pdo = new PDO(
    "mysql:host=localhost;dbname=np03cs4s250043;charset=utf8mb4",
    "np03cs4s250043",
    "cGRr1prihO",
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);
