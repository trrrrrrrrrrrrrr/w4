<?php
require_once 'db.php';
header('Content-Type: text/html; charset=UTF-8');

// Функция для сохранения данных в cookies на год
function saveToCookie($name, $value) {
    setcookie($name, $value, time() + 365 * 24 * 3600, '/');
}

// Функция для сохранения ошибок (до конца сессии браузера)
function saveErrors($errors) {
    setcookie('form_errors', json_encode($errors), 0, '/');
}

// Функция для сохранения введённых значений (при ошибках)
function saveValues($values) {
    setcookie('form_values', json_encode($values), 0, '/');
}

// Если есть GET-параметр success, показываем сообщение
$success_message = '';
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = 'Данные успешно сохранены!';
}

// Если есть cookies с ошибками, читаем их и удаляем
$errors = [];
if (isset($_COOKIE['form_errors'])) {
    $errors = json_decode($_COOKIE['form_errors'], true);
    setcookie('form_errors', '', 1, '/'); // удаляем
}

// Читаем сохранённые значения из cookies (приоритет: сначала из cookies, потом из сохранённых на год)
$form_data = [
    'full_name' => '', 'phone' => '', 'email' => '', 'birth_date' => '',
    'gender' => '', 'biography' => '', 'contract_accepted' => false, 'languages' => []
];

// Если есть временные значения (при ошибках) – используем их
if (isset($_COOKIE['form_values'])) {
    $temp_values = json_decode($_COOKIE['form_values'], true);
    if (is_array($temp_values)) {
        $form_data = array_merge($form_data, $temp_values);
    }
    setcookie('form_values', '', 1, '/'); // удаляем после использования
} else {
    // Иначе пытаемся прочитать долгоживущие cookies (на год) для каждого поля
    $cookie_fields = ['full_name', 'phone', 'email', 'birth_date', 'gender', 'biography'];
    foreach ($cookie_fields as $field) {
        if (isset($_COOKIE[$field])) {
            $form_data[$field] = $_COOKIE[$field];
        }
    }
    if (isset($_COOKIE['contract_accepted'])) {
        $form_data['contract_accepted'] = (bool)$_COOKIE['contract_accepted'];
    }
    if (isset($_COOKIE['languages'])) {
        $form_data['languages'] = explode(',', $_COOKIE['languages']);
    }
}

// Обработка POST-запроса (отправка формы)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_data = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'birth_date' => trim($_POST['birth_date'] ?? ''),
        'gender' => $_POST['gender'] ?? '',
        'biography' => trim($_POST['biography'] ?? ''),
        'contract_accepted' => isset($_POST['contract_accepted']),
        'languages' => $_POST['languages'] ?? []
    ];

    $validation_errors = [];
    $allowed_languages = getAllowedLanguages();
    $allowed_genders = getAllowedGenders();

    // 1. ФИО
    if (empty($post_data['full_name'])) {
        $validation_errors['full_name'] = 'ФИО обязательно для заполнения.';
    } elseif (!preg_match('/^[а-яА-Яa-zA-Z\s]+$/u', $post_data['full_name'])) {
        $validation_errors['full_name'] = 'ФИО должно содержать только буквы (русские или латинские) и пробелы.';
    } elseif (strlen($post_data['full_name']) > 150) {
        $validation_errors['full_name'] = 'ФИО не должно превышать 150 символов.';
    }

    // 2. Телефон – количество цифр 10-12
    if (empty($post_data['phone'])) {
        $validation_errors['phone'] = 'Телефон обязателен.';
    } else {
        $digits = preg_replace('/\D/', '', $post_data['phone']);
        $digitCount = strlen($digits);
        if ($digitCount < 10 || $digitCount > 12) {
            $validation_errors['phone'] = 'Номер телефона должен содержать от 10 до 12 цифр (допустимы +, -, пробелы, скобки). Пример: +7 (918) 463-42-21.';
        }
    }

    // 3. Email
    if (empty($post_data['email'])) {
        $validation_errors['email'] = 'Email обязателен.';
    } elseif (!filter_var($post_data['email'], FILTER_VALIDATE_EMAIL)) {
        $validation_errors['email'] = 'Введите корректный email, например: name@domain.ru.';
    }

    // 4. Дата рождения
    if (empty($post_data['birth_date'])) {
        $validation_errors['birth_date'] = 'Дата рождения обязательна.';
    } else {
        $date = DateTime::createFromFormat('Y-m-d', $post_data['birth_date']);
        if (!$date || $date->format('Y-m-d') !== $post_data['birth_date']) {
            $validation_errors['birth_date'] = 'Дата должна быть в формате ГГГГ-ММ-ДД.';
        } elseif ($date > new DateTime('today')) {
            $validation_errors['birth_date'] = 'Дата рождения не может быть в будущем.';
        }
    }

    // 5. Пол
    if (empty($post_data['gender'])) {
        $validation_errors['gender'] = 'Выберите пол.';
    } elseif (!in_array($post_data['gender'], $allowed_genders)) {
        $validation_errors['gender'] = 'Недопустимое значение пола.';
    }

    // 6. Языки
    if (empty($post_data['languages'])) {
        $validation_errors['languages'] = 'Выберите хотя бы один язык программирования.';
    } else {
        foreach ($post_data['languages'] as $lang) {
            if (!in_array($lang, $allowed_languages)) {
                $validation_errors['languages'] = 'Выбран недопустимый язык.';
                break;
            }
        }
    }

    // 7. Биография
    if (strlen($post_data['biography']) > 10000) {
        $validation_errors['biography'] = 'Биография не должна превышать 10000 символов.';
    }

    // 8. Согласие
    if (!$post_data['contract_accepted']) {
        $validation_errors['contract_accepted'] = 'Необходимо подтвердить ознакомление с контрактом.';
    }

    // Если ошибки есть – сохраняем в cookies и редирект на GET
    if (!empty($validation_errors)) {
        saveErrors($validation_errors);
        saveValues($post_data);
        header('Location: index.php');
        exit;
    }

    // Нет ошибок – сохраняем в БД
    try {
        $pdo = getDB();
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO application 
            (full_name, phone, email, birth_date, gender, biography, contract_accepted)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $post_data['full_name'],
            $post_data['phone'],
            $post_data['email'],
            $post_data['birth_date'],
            $post_data['gender'],
            $post_data['biography'],
            $post_data['contract_accepted'] ? 1 : 0
        ]);
        $app_id = $pdo->lastInsertId();

        // Маппинг языков
        $lang_map = [];
        $stmt = $pdo->query("SELECT id, name FROM language");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $lang_map[$row['name']] = $row['id'];
        }

        $stmt = $pdo->prepare("INSERT INTO application_language (application_id, language_id) VALUES (?, ?)");
        foreach ($post_data['languages'] as $lang_name) {
            if (isset($lang_map[$lang_name])) {
                $stmt->execute([$app_id, $lang_map[$lang_name]]);
            }
        }

        $pdo->commit();

        // Сохраняем значения в долгоживущие cookies (на год)
        saveToCookie('full_name', $post_data['full_name']);
        saveToCookie('phone', $post_data['phone']);
        saveToCookie('email', $post_data['email']);
        saveToCookie('birth_date', $post_data['birth_date']);
        saveToCookie('gender', $post_data['gender']);
        saveToCookie('biography', $post_data['biography']);
        saveToCookie('contract_accepted', $post_data['contract_accepted'] ? '1' : '0');
        saveToCookie('languages', implode(',', $post_data['languages']));

        // Успешное сохранение – редирект с параметром success
        header('Location: index.php?success=1');
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $validation_errors['db'] = 'Ошибка БД: ' . $e->getMessage();
        saveErrors($validation_errors);
        saveValues($post_data);
        header('Location: index.php');
        exit;
    }
}

// Получаем список языков для отображения в select
$pdo = getDB();
$languages_from_db = $pdo->query("SELECT name FROM language ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
if (empty($languages_from_db)) {
    $languages_from_db = getAllowedLanguages();
}

include 'form.php';
?>