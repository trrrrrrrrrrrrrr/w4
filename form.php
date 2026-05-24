<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкета – Лабораторная работа №3</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/imask/7.4.0/imask.min.js"></script>
</head>
<body>
<div class="form-wrapper">
    <div class="form-card">
        <div class="form-header">
            <h1> Регистрационная анкета</h1>
            
        </div>

        <?php if ($success_message): ?>
            <div class="alert success">✅ <?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <strong>⚠️ Исправьте ошибки:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="index.php" id="anketaForm">
            <!-- ФИО -->
            <div class="input-group">
                <label for="full_name">ФИО <span class="required">*</span></label>
                <input type="text" id="full_name" name="full_name" 
                       value="<?= htmlspecialchars($form_data['full_name']) ?>" 
                       placeholder="Иванов Иван Иванович" required>
                <div class="field-hint">Только буквы и пробелы, максимум 150 символов</div>
            </div>

            <!-- Телефон -->
            <div class="input-group">
                <label for="phone">Телефон <span class="required">*</span></label>
                <input type="tel" id="phone" name="phone" 
                       value="<?= htmlspecialchars($form_data['phone']) ?>" 
                       placeholder="+7 (123) 456-78-90" required>
                    <div class="field-hint">Формат: +7 (XXX) XXX-XX-XX </div>
            </div>

            <!-- Email -->
            <div class="input-group">
                <label for="email">E-mail <span class="required">*</span></label>
                <input type="email" id="email" name="email" 
                       value="<?= htmlspecialchars($form_data['email']) ?>" 
                       placeholder="example@domain.com" required>
            </div>

            <!-- Дата рождения -->
            <div class="input-group">
                <label for="birth_date">Дата рождения <span class="required">*</span></label>
                <input type="date" id="birth_date" name="birth_date" 
                       value="<?= htmlspecialchars($form_data['birth_date']) ?>" required>
            </div>

            <!-- Пол -->
            <div class="input-group">
                <label>Пол <span class="required">*</span></label>
                <div class="radio-group">
                    <label><input type="radio" name="gender" value="male" 
                          <?= $form_data['gender'] === 'male' ? 'checked' : '' ?>> Мужской</label>
                    <label><input type="radio" name="gender" value="female" 
                          <?= $form_data['gender'] === 'female' ? 'checked' : '' ?>> Женский</label>
                </div>
            </div>

            <!-- Языки программирования -->
            <div class="input-group">
                <label for="languages">Любимые языки программирования <span class="required">*</span></label>
                <select id="languages" name="languages[]" multiple size="6" required>
                    <?php foreach ($languages_from_db as $lang): ?>
                        <option value="<?= htmlspecialchars($lang) ?>" 
                            <?= in_array($lang, $form_data['languages']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($lang) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="field-hint">Зажмите Ctrl  для выбора нескольких</div>
            </div>

            <!-- Биография -->
            <div class="input-group">
                <label for="biography">Биография</label>
                <textarea id="biography" name="biography" rows="5" 
                          placeholder="Расскажите немного о себе..."><?= htmlspecialchars($form_data['biography']) ?></textarea>
                <div class="field-hint"><span id="bioCounter">0</span> / 10000 символов</div>
            </div>

            <!-- Чекбокс согласия -->
            <div class="input-group checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="contract_accepted" value="1" 
                        <?= $form_data['contract_accepted'] ? 'checked' : '' ?>>
                    <span>Я ознакомлен(а) с <a href="#" class="contract-link">условиями контракта</a> и принимаю их <span class="required">*</span></span>
                </label>
            </div>

            <button type="submit" class="submit-btn"> Сохранить анкету</button>
        </form>

        <div class="footer-links">
            <a href="v.php"> Просмотр сохранённых анкет</a>
            <a href="p.html"> Этапы выполнения (Работа с БД)</a>
        </div>
    </div>
</div>

<script>
    // Маска для телефона
    var phoneInput = document.getElementById('phone');
    if (phoneInput) {
        var mask = IMask(phoneInput, {
            mask: '+{7} (000) 000-00-00',
            lazy: false,
            placeholderChar: '_'
        });
    }

    // Счётчик символов биографии
    var bio = document.getElementById('biography');
    var counter = document.getElementById('bioCounter');
    function updateCounter() {
        if (counter) counter.innerText = bio.value.length;
    }
    if (bio) {
        bio.addEventListener('input', updateCounter);
        updateCounter();
    }

    // Подтверждение перед отправкой (дополнительно)
    var form = document.getElementById('anketaForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!confirm('Проверьте правильность введённых данных. Отправить?')) {
                e.preventDefault();
            }
        });
    }
</script>
</body>
</html>