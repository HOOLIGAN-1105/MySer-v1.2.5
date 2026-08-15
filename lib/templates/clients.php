<?php
defined('ABSPATH') || exit;
?>
<div class="wrap">
    <div class="myser-page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h1 style="margin: 0;">
            <img src="<?php echo MYSER_PLUGIN_URL; ?>assets/admin/images/icons/clients.svg" class="myser-icon" alt="">
            <?php _e('Клиенты', 'myser'); ?>
        </h1>
        <div style="font-size: 0.9em; color: #0073aa; text-align: center; flex: 1;">
            MySer v<?php echo MYSER_VERSION; ?>
        </div>
        <div style="text-align: right; min-width: 150px;">
            <button class="button button-secondary" id="myser-reboot-btn" onclick="myser_reboot_plugin()">♻️ Ребут плагина</button>
            <span id="myser-reboot-status" style="display: block; margin-top: 4px; font-size: 12px;"></span>
        </div>
    </div>
    <div style="margin-bottom: 15px;">
        <button class="button button-primary" onclick="myser_open_client_modal()">+ <?php _e('Добавить клиента', 'myser'); ?></button>
    </div>
    
    <div class="myser-filter-row">
        <input type="text" id="client-search" placeholder="<?php _e('Поиск по имени, фамилии, телефону...', 'myser'); ?>" style="flex: 1; min-width: 200px;">
        <button class="button" onclick="myser_load_clients()"><?php _e('Поиск', 'myser'); ?></button>
    </div>
    
    <div class="myser-table-wrap" id="clients-table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Фамилия</th>
                    <th>Имя</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody id="clients-tbody">
                <tr><td colspan="7">Загрузка...</td></tr>
            </tbody>
        </table>
        <div class="pagination" style="margin-top: 10px; display: flex; gap: 5px; flex-wrap: wrap;">
            <span id="pagination-info">Страница 1</span>
        </div>
    </div>
</div>

<!-- Модальное окно клиента -->
<div id="client-modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:99999; justify-content:center; align-items:center;">
    <div id="client-modal" style="background:#fff; border-radius:8px; padding:25px; width:500px; max-width:90%; max-height:90vh; overflow-y:auto; box-shadow:0 4px 20px rgba(0,0,0,0.3);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h2 id="client-modal-title" style="margin:0;">Добавить клиента</h2>
            <span onclick="myser_close_client_modal()" style="cursor:pointer; font-size:24px; line-height:1;">&times;</span>
        </div>
        
        <input type="hidden" id="client-edit-id" value="">
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
            <div>
                <label style="display:block; margin-bottom:5px; font-weight:600;">Фамилия *</label>
                <input type="text" id="client-last-name" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" placeholder="Иванов">
            </div>
            <div>
                <label style="display:block; margin-bottom:5px; font-weight:600;">Имя *</label>
                <input type="text" id="client-first-name" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" placeholder="Иван">
            </div>
        </div>
        
        <div style="margin-top:15px;">
            <label style="display:block; margin-bottom:5px; font-weight:600;">Отчество</label>
            <input type="text" id="client-middle-name" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" placeholder="Иванович">
        </div>
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-top:15px;">
            <div>
                <label style="display:block; margin-bottom:5px; font-weight:600;">Телефон</label>
                <input type="text" id="client-phone" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" placeholder="+7 (999) 123-45-67">
            </div>
            <div>
                <label style="display:block; margin-bottom:5px; font-weight:600;">Email</label>
                <input type="email" id="client-email" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" placeholder="client@example.com">
            </div>
        </div>
        
        <div style="margin-top:15px;">
            <label style="display:block; margin-bottom:5px; font-weight:600;">Адрес</label>
            <input type="text" id="client-address" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" placeholder="г. Москва, ул. Примерная, д. 1">
        </div>
        
        <div style="margin-top:15px;">
            <label style="display:block; margin-bottom:5px; font-weight:600;">Статус</label>
            <select id="client-status" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;">
                <option value="active">Активный</option>
                <option value="inactive">Неактивный</option>
                <option value="vip">VIP</option>
            </select>
        </div>
        
        <div style="margin-top:15px;">
            <label style="display:block; margin-bottom:5px; font-weight:600;">Заметки</label>
            <textarea id="client-notes" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px; min-height:60px;" placeholder="Дополнительная информация..."></textarea>
        </div>
        
        <div style="margin-top:20px;">
            <label style="display:block; margin-bottom:8px; font-weight:600;">Роли</label>
            <div style="display:flex; flex-wrap:wrap; gap:10px;">
                <label style="display:flex; align-items:center; gap:5px; cursor:pointer;">
                    <input type="checkbox" name="client-roles[]" value="client"> Клиент
                </label>
                <label style="display:flex; align-items:center; gap:5px; cursor:pointer;">
                    <input type="checkbox" name="client-roles[]" value="staff"> Сотрудник
                </label>
                <label style="display:flex; align-items:center; gap:5px; cursor:pointer;">
                    <input type="checkbox" name="client-roles[]" value="supplier"> Поставщик
                </label>
                <label style="display:flex; align-items:center; gap:5px; cursor:pointer;">
                    <input type="checkbox" name="client-roles[]" value="contractor"> Подрядчик
                </label>
            </div>
        </div>
        
        <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
            <button class="button" onclick="myser_close_client_modal()">Отмена</button>
            <button class="button button-primary" onclick="myser_save_client_from_modal()">Сохранить</button>
        </div>
    </div>
</div>

<script>
let current_page = 1;
let total_pages = 1;

function myser_load_clients(page = 1) {
    current_page = page;
    const search = document.getElementById('client-search').value;
    jQuery.post(myser_ajax.ajaxurl, {
        action: 'myser_get_clients',
        nonce: myser_ajax.nonce,
        page: page,
        per_page: 20,
        search: search
    }, function(response) {
        if (response.success) {
            let html = '';
            if (response.data.items.length === 0) {
                html = '<tr><td colspan="7">Нет клиентов</td></tr>';
            } else {
                response.data.items.forEach(function(client) {
                    html += `<tr>
                        <td>${client.id}</td>
                        <td>${client.last_name || ''}</td>
                        <td>${client.first_name || ''}</td>
                        <td>${client.phone || ''}</td>
                        <td>${client.email || ''}</td>
                        <td>${client.status || 'active'}</td>
                        <td>
                            <button class="button button-small" onclick="myser_open_client_modal(${client.id})">✏️</button>
                            <button class="button button-small" onclick="myser_delete_client(${client.id})">️</button>
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('clients-tbody').innerHTML = html;
            
            total_pages = response.data.pages || 1;
            let pagination_html = `<span>Страница ${current_page} из ${total_pages}</span>`;
            for (let i = 1; i <= Math.min(total_pages, 10); i++) {
                pagination_html += `<button class="button button-small" onclick="myser_load_clients(${i})" ${i === current_page ? 'disabled' : ''}>${i}</button>`;
            }
            document.querySelector('#clients-table-wrap .pagination').innerHTML = pagination_html;
        } else {
            alert('Ошибка: ' + (response.data?.message || 'Неизвестная ошибка'));
        }
    });
}

// ========== Модальное окно клиента ==========

function myser_open_client_modal(id = null) {
    const overlay = document.getElementById('client-modal-overlay');
    overlay.style.display = 'flex';
    
    if (id) {
        // Редактирование
        document.getElementById('client-modal-title').textContent = 'Редактировать клиента';
        document.getElementById('client-edit-id').value = id;
        
        jQuery.post(myser_ajax.ajaxurl, {
            action: 'myser_get_client',
            nonce: myser_ajax.nonce,
            client_id: id
        }, function(response) {
            if (response.success) {
                const c = response.data;
                document.getElementById('client-last-name').value = c.last_name || '';
                document.getElementById('client-first-name').value = c.first_name || '';
                document.getElementById('client-middle-name').value = c.middle_name || '';
                document.getElementById('client-phone').value = c.phone || '';
                document.getElementById('client-email').value = c.email || '';
                document.getElementById('client-address').value = c.address || '';
                document.getElementById('client-status').value = c.status || 'active';
                document.getElementById('client-notes').value = c.notes || '';
                
                // Отмечаем чекбоксы ролей (из subject_roles если есть, иначе по умолчанию client)
                const currentRoles = (c.subject_roles || '').split(',').map(r => r.trim().toLowerCase());
                document.querySelectorAll('input[name="client-roles[]"]').forEach(cb => {
                    cb.checked = currentRoles.includes(cb.value);
                });
            }
        });
    } else {
        // Добавление
        document.getElementById('client-modal-title').textContent = 'Добавить клиента';
        document.getElementById('client-edit-id').value = '';
        document.getElementById('client-last-name').value = '';
        document.getElementById('client-first-name').value = '';
        document.getElementById('client-middle-name').value = '';
        document.getElementById('client-phone').value = '';
        document.getElementById('client-email').value = '';
        document.getElementById('client-address').value = '';
        document.getElementById('client-status').value = 'active';
        document.getElementById('client-notes').value = '';
        document.querySelectorAll('input[name="client-roles[]"]').forEach(cb => {
            cb.checked = (cb.value === 'client');
        });
    }
}

function myser_close_client_modal() {
    document.getElementById('client-modal-overlay').style.display = 'none';
}

function myser_save_client_from_modal() {
    const id = document.getElementById('client-edit-id').value;
    const last_name = document.getElementById('client-last-name').value.trim();
    const first_name = document.getElementById('client-first-name').value.trim();
    
    if (!last_name || !first_name) {
        alert('Фамилия и Имя обязательны для заполнения');
        return;
    }
    
    // Собираем выбранные роли
    const roles = [];
    document.querySelectorAll('input[name="client-roles[]"]:checked').forEach(cb => {
        roles.push(cb.value);
    });
    
    const data = {
        action: 'myser_save_client',
        nonce: myser_ajax.nonce,
        last_name: last_name,
        first_name: first_name,
        middle_name: document.getElementById('client-middle-name').value.trim(),
        phone: document.getElementById('client-phone').value.trim(),
        email: document.getElementById('client-email').value.trim(),
        address: document.getElementById('client-address').value.trim(),
        status: document.getElementById('client-status').value,
        notes: document.getElementById('client-notes').value.trim(),
        roles: roles
    };
    
    if (id) data.id = id;
    
    jQuery.post(myser_ajax.ajaxurl, data, function(response) {
        if (response.success) {
            myser_close_client_modal();
            myser_load_clients(current_page);
        } else {
            alert('Ошибка: ' + (response.data?.message || 'Неизвестная ошибка'));
        }
    });
}

// Закрытие по клику на оверлей
document.getElementById('client-modal-overlay').addEventListener('click', function(e) {
    if (e.target === this) myser_close_client_modal();
});

// ========== Удаление ==========

function myser_delete_client(id) {
    if (!confirm('Удалить клиента?')) return;
    jQuery.post(myser_ajax.ajaxurl, {
        action: 'myser_delete_client',
        nonce: myser_ajax.nonce,
        client_id: id
    }, function(response) {
        if (response.success) {
            myser_load_clients(current_page);
        } else {
            alert('Ошибка: ' + (response.data?.message || 'Неизвестная ошибка'));
        }
    });
}

// Загрузка при открытии страницы
document.addEventListener('DOMContentLoaded', function() {
    myser_load_clients(1);
});
</script>
