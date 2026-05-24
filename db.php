<?php
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $db_host = 'localhost';
        $db_user = 'u82311';          
        $db_pass = '6649813';         
        $db_name = 'u82311';          
        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Ошибка подключения к БД: " . $e->getMessage());
        }
    }
    return $pdo;
}

// Массив допустимых языков (для валидации)
function getAllowedLanguages() {
    return [
        'Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python',
        'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'
    ];
}

// Допустимые значения пола
function getAllowedGenders() {
    return ['male', 'female'];
}
?>