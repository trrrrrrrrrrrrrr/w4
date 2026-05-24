<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкета – Лабораторная работа №4</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/imask/7.4.0/imask.min.js"></script>
    <style>
        /* Дополнительные стили для подсветки ошибок */
        .input-error {
            border: 2px solid #ef4444 !important;
            background-color: #fff5f5 !important;
        }
        .error-text {
            color: #ef4444;
            font-size: 0.75rem;
            margin-top: 4px;
        }
    </style>
</head>
<body>
<div class="form-wrapper">
    <div class="form-card">
        <div class="form-header">
            <h1>📋 Регистрационная анкета</h1>
            <p>Заполните все поля – данные будут сохранены в базе и в cookies на год</p>
        </div>

        <?php if ($success_message): ?>
            <div class="alert success">✅ <?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <strong>⚠️ Исправьте следующие ошибки:</strong>
                <ul>
                    <?php foreach ($errors as $field => $error): ?>
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
                       placeholder="Иванов Иван Иванович"
                       class="<?= isset($errors['full_name']) ? 'input-error' : '' ?>">
                <?php if (isset($errors['full_name'])): ?>
                    <div class="error-text"><?= htmlspecialchars($errors['full_name']) ?></div>
                <?php else: ?>
                    <div class="field-hint">Только буквы и пробелы, максимум 150 символов</div>
                <?php endif; ?>
            </div>

            <!-- Телефон -->
            <div class="input-group">
                <label for="phone">Телефон <span class="required">*</span></label>
                <input type="tel" id="phone" name="phone" 
                       value="<?= htmlspecialchars($form_data['phone']) ?>" 
                       placeholder="+7 (918) 463-42-21"
                       class="<?= isset($errors['phone']) ? 'input-error' : '' ?>">
                <?php if (isset($errors['phone'])): ?>
                    <div class="error-text"><?= htmlspecialchars($errors['phone']) ?></div>
                <?php else: ?>
                    <div class="field-hint">Формат: +7 (XXX) XXX-XX-XX (от 10 до 12 цифр)</div>
                <?php endif; ?>
            </div>

            <!-- Email -->
            <div class="input-group">
                <label for="email">E-mail <span class="required">*</span></label>
                <input type="email" id="email" name="email" 
                       value="<?= htmlspecialchars($form_data['email']) ?>" 
                       placeholder="example@domain.com"
                       class="<?= isset($errors['email']) ? 'input-error' : '' ?>">
                <?php if (isset($errors['email'])): ?>
                    <div class="error-text"><?= htmlspecialchars($errors['email']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Дата рождения -->
            <div class="input-group">
                <label for="birth_date">Дата рождения <span class="required">*</span></label>
                <input type="date" id="birth_date" name="birth_date" 
                       value="<?= htmlspecialchars($form_data['birth_date']) ?>"
                       class="<?= isset($errors['birth_date']) ? 'input-error' : '' ?>">
                <?php if (isset($errors['birth_date'])): ?>
                    <div class="error-text"><?= htmlspecialchars($errors['birth_date']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Пол -->
            <div class="input-group">
                <label>Пол <span class="required">*</span></label>
                <div class="radio-group">
                    <label><input type="radio" name="gender" value="male" 
                          <?= $form_data['gender'] === 'male' ? 'checked' : '' ?>
                          class="<?= isset($errors['gender']) ? 'input-error' : '' ?>"> Мужской</label>
                    <label><input type="radio" name="gender" value="female" 
                          <?= $form_data['gender'] === 'female' ? 'checked' : '' ?>
                          class="<?= isset($errors['gender']) ? 'input-error' : '' ?>"> Женский</label>
                </div>
                <?php if (isset($errors['gender'])): ?>
                    <div class="error-text"><?= htmlspecialchars($errors['gender']) ?></div>
                <?php endif; ?>
            </div>

            <!-- Языки -->
            <div class="input-group">
                <label for="languages">Любимые языки программирования <span class="required">*</span></label>
                <select id="languages" name="languages[]" multiple size="6" 
                        class="<?= isset($errors['languages']) ? 'input-error' : '' ?>">
                    <?php foreach ($languages_from_db as $lang): ?>
                        <option value="<?= htmlspecialchars($lang) ?>" 
                            <?= in_array($lang, $form_data['languages']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($lang) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['languages'])): ?>
                    <div class="error-text"><?= htmlspecialchars($errors['languages']) ?></div>
                <?php else: ?>
                    <div class="field-hint">Зажмите Ctrl (Cmd) для выбора нескольких</div>
                <?php endif; ?>
            </div>

            <!-- Биография -->
            <div class="input-group">
                <label for="biography">Биография</label>
                <textarea id="biography" name="biography" rows="5" 
                          class="<?= isset($errors['biography']) ? 'input-error' : '' ?>"
                          placeholder="Расскажите немного о себе..."><?= htmlspecialchars($form_data['biography']) ?></textarea>
                <?php if (isset($errors['biography'])): ?>
                    <div class="error-text"><?= htmlspecialchars($errors['biography']) ?></div>
                <?php else: ?>
                    <div class="field-hint"><span id="bioCounter">0</span> / 10000 символов</div>
                <?php endif; ?>
            </div>

            <!-- Чекбокс согласия -->
            <div class="input-group checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="contract_accepted" value="1" 
                        <?= $form_data['contract_accepted'] ? 'checked' : '' ?>
                        class="<?= isset($errors['contract_accepted']) ? 'input-error' : '' ?>">
                    <span>Я ознакомлен(а) с <a href="#" class="contract-link">условиями контракта</a> и принимаю их <span class="required">*</span></span>
                </label>
                <?php if (isset($errors['contract_accepted'])): ?>
                    <div class="error-text"><?= htmlspecialchars($errors['contract_accepted']) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="submit-btn">💾 Сохранить анкету</button>
        </form>

        <div class="footer-links">
            <a href="v.php">📊 Просмотр сохранённых анкет</a>
            
        </div>
    </div>
</div>

<script>
    // Маска для телефона (чисто для удобства, не влияет на валидацию)
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
    if (bio && counter) {
        function updateCounter() {
            counter.innerText = bio.value.length;
        }
        bio.addEventListener('input', updateCounter);
        updateCounter();
    }
</script>
</body>
</html>