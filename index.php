<?php
require_once 'db.php';

header('Content-Type: text/html; charset=UTF-8');

$form_data = [
    'full_name' => '', 'phone' => '', 'email' => '', 'birth_date' => '',
    'gender' => '', 'biography' => '', 'contract_accepted' => false, 'languages' => []
];
$errors = [];
$success_message = '';

$allowed_languages = getAllowedLanguages();
$allowed_genders = getAllowedGenders();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение данных
    $form_data['full_name'] = trim($_POST['full_name'] ?? '');
    $form_data['phone'] = trim($_POST['phone'] ?? '');
    $form_data['email'] = trim($_POST['email'] ?? '');
    $form_data['birth_date'] = trim($_POST['birth_date'] ?? '');
    $form_data['gender'] = $_POST['gender'] ?? '';
    $form_data['biography'] = trim($_POST['biography'] ?? '');
    $form_data['contract_accepted'] = isset($_POST['contract_accepted']);
    $form_data['languages'] = $_POST['languages'] ?? [];

    // Валидация
    if (empty($form_data['full_name'])) {
        $errors['full_name'] = 'ФИО обязательно для заполнения.';
    } elseif (!preg_match('/^[а-яА-Яa-zA-Z\s]+$/u', $form_data['full_name'])) {
        $errors['full_name'] = 'ФИО должно содержать только буквы и пробелы.';
    } elseif (strlen($form_data['full_name']) > 150) {
        $errors['full_name'] = 'ФИО не должно превышать 150 символов.';
    }

    // Телефон – проверка количества цифр (10–12 цифр)
    if (empty($form_data['phone'])) {
        $errors['phone'] = 'Телефон обязателен.';
    } else {
        $digits = preg_replace('/\D/', '', $form_data['phone']);
        $digitCount = strlen($digits);
        if ($digitCount < 10 || $digitCount > 12) {
            $errors['phone'] = 'Номер телефона должен содержать от 10 до 12 цифр (например, +7 918 463-42-21).';
        }
    }

    if (empty($form_data['email'])) {
        $errors['email'] = 'Email обязателен.';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Некорректный формат email.';
    }

    if (empty($form_data['birth_date'])) {
        $errors['birth_date'] = 'Дата рождения обязательна.';
    } else {
        $date = DateTime::createFromFormat('Y-m-d', $form_data['birth_date']);
        if (!$date || $date->format('Y-m-d') !== $form_data['birth_date']) {
            $errors['birth_date'] = 'Некорректная дата. Используйте формат ГГГГ-ММ-ДД.';
        } elseif ($date > new DateTime('today')) {
            $errors['birth_date'] = 'Дата рождения не может быть позже сегодняшнего дня.';
        }
    }

    if (empty($form_data['gender'])) {
        $errors['gender'] = 'Выберите пол.';
    } elseif (!in_array($form_data['gender'], $allowed_genders)) {
        $errors['gender'] = 'Недопустимое значение пола.';
    }

    if (empty($form_data['languages'])) {
        $errors['languages'] = 'Выберите хотя бы один язык программирования.';
    } else {
        foreach ($form_data['languages'] as $lang) {
            if (!in_array($lang, $allowed_languages)) {
                $errors['languages'] = 'Выбран недопустимый язык.';
                break;
            }
        }
    }

    if (strlen($form_data['biography']) > 10000) {
        $errors['biography'] = 'Биография не должна превышать 10000 символов.';
    }

    if (!$form_data['contract_accepted']) {
        $errors['contract_accepted'] = 'Необходимо подтвердить ознакомление с контрактом.';
    }

    // Сохранение при отсутствии ошибок
    if (empty($errors)) {
        try {
            $pdo = getDB();
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO application 
                (full_name, phone, email, birth_date, gender, biography, contract_accepted)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $form_data['full_name'],
                $form_data['phone'],
                $form_data['email'],
                $form_data['birth_date'],
                $form_data['gender'],
                $form_data['biography'],
                $form_data['contract_accepted'] ? 1 : 0
            ]);
            $app_id = $pdo->lastInsertId();

            // Маппинг языков
            $lang_map = [];
            $stmt = $pdo->query("SELECT id, name FROM language");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $lang_map[$row['name']] = $row['id'];
            }

            $stmt = $pdo->prepare("INSERT INTO application_language (application_id, language_id) VALUES (?, ?)");
            foreach ($form_data['languages'] as $lang_name) {
                if (isset($lang_map[$lang_name])) {
                    $stmt->execute([$app_id, $lang_map[$lang_name]]);
                }
            }

            $pdo->commit();
            $success_message = 'Данные успешно сохранены!';
            // Очищаем форму
            $form_data = array_map(function() { return ''; }, $form_data);
            $form_data['languages'] = [];
            $form_data['contract_accepted'] = false;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors['db'] = 'Ошибка БД: ' . $e->getMessage();
        }
    }
}

// Получаем список языков для отображения в select
$pdo = getDB();
$languages_from_db = $pdo->query("SELECT name FROM language ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
if (empty($languages_from_db)) {
    $languages_from_db = $allowed_languages;
}

include 'form.php';
?>