<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Главная страница</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .form-title {
            color: #333;
            font-weight: 600;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="form-container p-4">
                    <h2 class="text-center form-title mb-4">ФОРМА</h2>
                    
                    <form id="contactForm">
                                                
                        <div class="mb-3">
                            <label for="name" class="form-label">Имя</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Введите имя" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="+7 (999) 123-45-67" required>
                        </div>

                        
                        <div class="mb-4">
                            <label for="comment" class="form-label">Комментарий</label>
                            <textarea class="form-control" id="comment" name="comment" rows="4" placeholder="Введите комментарий"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Отправить</button>
                    </form>
                    
                    <div id="successMessage" class="alert alert-success mt-3" style="display: none;">
                        Форма успешно отправлена!
                    </div>
                    
                    <div id="errorMessage" class="alert alert-danger mt-3" style="display: none;">
                        Произошла ошибка при отправке формы.
                    </div>
                    
                    @if(isset($lastRequest))
                    <div class="mt-4 p-3 bg-light rounded">
                        <h5 class="text-center mb-3">Последняя заявка</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Имя:</strong><br>
                                <span class="text-muted">{{ $lastRequest->masked_name }}</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Телефон:</strong><br>
                                <span class="text-muted">{{ $lastRequest->masked_phone }}</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Комментарий:</strong><br>
                                <span class="text-muted">{{ $lastRequest->masked_comment }}</span>
                            </div>
                        </div>
                        <div class="text-center mt-2">
                            <small class="text-muted">Дата: {{ $lastRequest->created_at->format('d.m.Y H:i') }} (МСК)</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- jQuery Mask Plugin -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Маска для телефона с jQuery.mask
            $('#phone').mask('+7 (000) 000-00-00', {
                placeholder: '+7 (___) ___-__-__'
            });

            // Обработка отправки формы
            $('#contactForm').on('submit', function(e) {
                e.preventDefault();
                
                // Скрываем предыдущие сообщения
                $('#successMessage, #errorMessage').hide();
                
                // Получаем данные формы
                const formData = {
                    phone: $('#phone').val(),
                    name: $('#name').val(),
                    comment: $('#comment').val(),
                    _token: $('meta[name="csrf-token"]').attr('content')
                };
                
                // Простая валидация
                if (!formData.phone || !formData.name) {
                    $('#errorMessage').text('Пожалуйста, заполните все обязательные поля.').show();
                    return;
                }
                
                // Отправляем данные на сервер
                $.ajax({
                    url: '/form',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        // Показываем сообщение об успехе
                        $('#successMessage').show();
                        
                        // Очищаем форму
                        $('#contactForm')[0].reset();
                        
                        // Обновляем информацию о последней заявке
                        updateLastRequest(formData);
                        
                        // Скрываем сообщение через 3 секунды
                        setTimeout(function() {
                            $('#successMessage').hide();
                        }, 3000);
                    },
                    error: function(xhr) {
                        let errorMessage = 'Произошла ошибка при отправке формы.';
                        
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            errorMessage = Object.values(errors).flat().join('\n');
                        }
                        
                        $('#errorMessage').text(errorMessage).show();
                    }
                });
            });
            
            // Анимация появления формы
            $('.form-container').hide().fadeIn(800);
            
            // Функция для обновления информации о последней заявке
            function updateLastRequest(formData) {
                // Маскируем данные на клиенте
                const maskedPhone = maskPhone(formData.phone);
                const maskedName = maskName(formData.name);
                const maskedComment = maskComment(formData.comment);
                
                // Получаем текущую дату в московском времени
                const now = new Date();
                const dateStr = now.toLocaleDateString('ru-RU') + ' ' + now.toLocaleTimeString('ru-RU', {hour: '2-digit', minute: '2-digit'}) + ' (МСК)';
                
                // Создаем HTML для отображения последней заявки
                const lastRequestHtml = `
                    <div class="mt-4 p-3 bg-light rounded">
                        <h5 class="text-center mb-3">Последняя заявка</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Имя:</strong><br>
                                <span class="text-muted">${maskedName}</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Телефон:</strong><br>
                                <span class="text-muted">${maskedPhone}</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Комментарий:</strong><br>
                                <span class="text-muted">${maskedComment}</span>
                            </div>
                        </div>
                        <div class="text-center mt-2">
                            <small class="text-muted">Дата: ${dateStr}</small>
                        </div>
                    </div>
                `;
                
                // Обновляем или добавляем блок с последней заявкой
                const existingBlock = $('.form-container').find('.bg-light.rounded');
                if (existingBlock.length > 0) {
                    existingBlock.replaceWith(lastRequestHtml);
                } else {
                    $('.form-container').append(lastRequestHtml);
                }
            }
            
            // Функции маскирования данных
            function maskPhone(phone) {
                // Очищаем телефон от всех символов
                const digits = phone.replace(/\D/g, '');
                
                // Если номер начинается с 8, заменяем на 7
                let normalizedPhone = digits;
                if (digits.length == 11 && digits.charAt(0) == '8') {
                    normalizedPhone = '7' + digits.slice(1);
                }
                
                // Если номер 10 цифр, добавляем 7 в начало
                if (digits.length == 10) {
                    normalizedPhone = '7' + digits;
                }
                
                if (normalizedPhone.length >= 4) {
                    const lastFour = normalizedPhone.slice(-4);
                    const masked = '*'.repeat(normalizedPhone.length - 4) + lastFour;
                    return '+7 (' + masked.slice(0, 3) + ') ' + masked.slice(3, 6) + '-' + masked.slice(6, 8) + '-' + masked.slice(8, 12);
                }
                return phone;
            }
            
            function maskName(name) {
                name = name.trim();
                if (name.length <= 2) {
                    return name;
                }
                const first = name.charAt(0);
                const last = name.charAt(name.length - 1);
                const middle = '*'.repeat(name.length - 2);
                return first + middle + last;
            }
            
            function maskComment(comment) {
                if (!comment || comment.trim() === '') {
                    return 'Комментарий отсутствует';
                }
                if (comment.length > 50) {
                    return comment.substring(0, 47) + '...';
                }
                return comment;
            }
        });
    </script>
</body>
</html> 