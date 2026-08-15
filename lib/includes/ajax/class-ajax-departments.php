<?php
namespace MySer;

defined('ABSPATH') || exit;

/**
 * AJAX-обработчики для подразделений
 *
 * @package MySer
 */
class Departments_Handler extends Ajax_Handler
{
    public static function register_hooks()
    {
        $actions = [
            'myser_get_departments',
            'myser_get_department',
            'myser_save_department',
            'myser_delete_department',
        ];
        foreach ($actions as $action) {
            add_action('wp_ajax_' . $action, [self::class, str_replace('myser_', '', $action)]);
        }
    }

    /**
     * Получить список подразделений
     */
    public static function get_departments()
    {
        self::verify_nonce();
        self::check_permissions();
        global $wpdb;
        $table = $wpdb->prefix . 'myser_departments';
        $staff_table = $wpdb->prefix . 'myser_staff';
        $results = $wpdb->get_results(
            "SELECT d.*, " .
            "(SELECT COUNT(*) FROM `$staff_table` s WHERE JSON_CONTAINS(s.department, CAST(d.id AS CHAR))) AS staff_count " .
            "FROM `$table` d ORDER BY d.short_name ASC",
            ARRAY_A
        );
        wp_send_json_success($results ?: []);
    }

    /**
     * Получить одно подразделение по ID
     */
    public static function get_department()
    {
        self::verify_nonce();
        self::check_permissions();
        global $wpdb;
        $id = intval($_POST['dep_id'] ?? 0);
        if ($id <= 0) {
            wp_send_json_error(['message' => 'Не указан ID подразделения']);
        }
        $table = $wpdb->prefix . 'myser_departments';
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM `$table` WHERE id = %d", $id), ARRAY_A);
        if ($row) {
            wp_send_json_success($row);
        } else {
            wp_send_json_error(['message' => 'Подразделение не найдено']);
        }
    }

    /**
     * Сохранить (добавить/обновить) подразделение
     */
    public static function save_department()
    {
        self::verify_nonce();
        self::check_permissions();
        global $wpdb;
        $table = $wpdb->prefix . 'myser_departments';
        $id = intval($_POST['dep_id'] ?? 0);

        $dep_type = sanitize_text_field($_POST['dep_type'] ?? '');
        $order_prefix = strtoupper(sanitize_text_field($_POST['order_prefix'] ?? ''));
        $short_name = sanitize_text_field($_POST['short_name'] ?? '');

        if ($id === 0) {
            // СОЗДАНИЕ
            $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM `$table`");
            if ($count === 0) {
                // Первое подразделение — Головной
                $dep_type = 'head';
                // Если префикс не указан, ставим "MS" для головного
                if (empty($order_prefix)) {
                    $order_prefix = 'MS';
                }
            } else {
                // Для всех последующих — тип по выбору пользователя (branch/remote)
                // Если не указан или указан head — принудительно ставим branch
                if (empty($dep_type) || $dep_type === 'head') {
                    $dep_type = 'branch';
                }
                // Генерируем префикс из short_name, если не указан
                if (empty($order_prefix) && !empty($short_name)) {
                    $order_prefix = strtoupper(substr(preg_replace('/[^a-zA-Zа-яА-Я]/u', '', $short_name), 0, 2));
                }
            }
        } else {
            // РЕДАКТИРОВАНИЕ
            $current = $wpdb->get_row($wpdb->prepare("SELECT dep_type, order_prefix FROM `$table` WHERE id = %d", $id), ARRAY_A);
            if (!$current) {
                wp_send_json_error(['message' => 'Подразделение не найдено']);
            }

            $old_dep_type = $current['dep_type'];

            // Если меняется тип на head
            if ($dep_type === 'head' && $old_dep_type !== 'head') {
                // Найти текущее головное и переключить его на branch
                $wpdb->update(
                    $table,
                    ['dep_type' => 'branch'],
                    ['dep_type' => 'head']
                );
            }

            // Если пытаются сменить головное на что-то другое
            if ($old_dep_type === 'head' && $dep_type !== 'head') {
                // Блокируем, если не переключаем на другое head через отдельный вызов
                // В текущей логике это не должно происходить, так как тип головного нельзя сменить напрямую
                // Но если пользователь явно выбрал другой тип для головного — блокируем
                wp_send_json_error(['message' => 'Нельзя изменить тип головного подразделения. Чтобы назначить новое головное, выберите другой тип для другого подразделения.']);
            }
        }

        // Если префикс пустой, генерируем из short_name
        if (empty($order_prefix) && !empty($short_name)) {
            $order_prefix = strtoupper(substr(preg_replace('/[^a-zA-Zа-яА-Я]/u', '', $short_name), 0, 2));
        }

        // Ограничиваем префикс 2 символами
        $order_prefix = substr($order_prefix, 0, 2);

        $data = [
            'short_name'        => $short_name,
            'full_name'         => sanitize_text_field($_POST['full_name'] ?? ''),
            'order_prefix'      => $order_prefix,
            'city'              => sanitize_text_field($_POST['city'] ?? ''),
            'address'           => sanitize_textarea_field($_POST['address'] ?? ''),
            'address_fact'      => sanitize_textarea_field($_POST['address_fact'] ?? ''),
            'work_phone'        => sanitize_text_field($_POST['work_phone'] ?? ''),
            'email'             => sanitize_email($_POST['email'] ?? ''),
            'inn'               => sanitize_text_field($_POST['inn'] ?? ''),
            'kpp'               => sanitize_text_field($_POST['kpp'] ?? ''),
            'ogrn'              => sanitize_text_field($_POST['ogrn'] ?? ''),
            'okpo'              => sanitize_text_field($_POST['okpo'] ?? ''),
            'okvd'              => sanitize_text_field($_POST['okvd'] ?? ''),
            'bank_account'      => sanitize_text_field($_POST['bank_account'] ?? ''),
            'bank_name'         => sanitize_text_field($_POST['bank_name'] ?? ''),
            'bank_bic'          => sanitize_text_field($_POST['bank_bic'] ?? ''),
            'bank_corr'         => sanitize_text_field($_POST['bank_corr'] ?? ''),
            'director'          => sanitize_text_field($_POST['director'] ?? ''),
            'director_full'     => sanitize_text_field($_POST['director_full'] ?? ''),
            'director_position' => sanitize_text_field($_POST['director_position'] ?? ''),
            'director_vlice'    => sanitize_text_field($_POST['director_vlice'] ?? ''),
            'accountant'        => sanitize_text_field($_POST['accountant'] ?? ''),
            'notes'             => sanitize_textarea_field($_POST['notes'] ?? ''),
            'status'            => intval($_POST['status'] ?? 1),
            'dep_type'          => $dep_type,
        ];

        if ($id > 0) {
            $wpdb->update($table, $data, ['id' => $id]);
            wp_send_json_success(['message' => 'Подразделение обновлено', 'id' => $id]);
        } else {
            $wpdb->insert($table, $data);
            wp_send_json_success(['message' => 'Подразделение добавлено', 'id' => $wpdb->insert_id]);
        }
    }

    /**
     * Удалить подразделение
     */
    public static function delete_department()
    {
        self::verify_nonce();
        self::check_permissions();
        global $wpdb;
        $id = intval($_POST['dep_id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) {
            wp_send_json_error(['message' => 'Не указан ID подразделения']);
        }
        $table = $wpdb->prefix . 'myser_departments';

        // Запрет удаления головного подразделения
        $dep_type = $wpdb->get_var($wpdb->prepare("SELECT dep_type FROM `$table` WHERE id = %d", $id));
        if ($dep_type === 'head') {
            wp_send_json_error(['message' => 'Нельзя удалить головное подразделение. Сначала назначьте другое головным.']);
        }

        $wpdb->delete($table, ['id' => $id]);
        wp_send_json_success(['message' => 'Подразделение удалено']);
    }

    /**
     * Пересчитывает и сохраняет количество сотрудников для каждого подразделения
     */
    protected static function update_department_staff_counts()
    {
        global $wpdb;
        $dept_table = $wpdb->prefix . 'myser_departments';
        $staff_table = $wpdb->prefix . 'myser_staff';

        $departments = $wpdb->get_results("SELECT id FROM `$dept_table`", ARRAY_A);
        foreach ($departments as $dept) {
            $dept_id = $dept['id'];
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM `$staff_table` WHERE JSON_CONTAINS(department, %s)",
                (string) $dept_id
            ));
            $wpdb->update($dept_table, ['staff_count' => (int) $count], ['id' => $dept_id]);
        }
    }
}
