<?php
/**
 * Zazsoft Service Alert Helper
 * Manages configuration and persistence for service provider notices.
 */

if (!function_exists('get_zazsoft_alert_config_file')) {
    function get_zazsoft_alert_config_file() {
        return __DIR__ . '/zaz_alert_config.json';
    }
}

if (!function_exists('get_default_zazsoft_config')) {
    function get_default_zazsoft_config() {
        return [
            'is_active' => 0,
            'title' => 'Important Service Notice',
            'badge_text' => 'Service Provider Advisory',
            'message' => "This application requires service verification and routine maintenance from your official software service provider.\n\nPlease contact Zazsoft Technologies immediately to ensure uninterrupted operations and access to system features.",
            'company_name' => 'Zazsoft Technologies',
            'phone' => '+92 300 1234567',
            'whatsapp' => '+92 300 1234567',
            'email' => 'support@zazsoft.com',
            'severity' => 'critical', // critical, warning, info
            'display_mode' => 'once_per_login', // once_per_login, every_page
            'admin_pin' => 'zazsoft@2026',
            'alert_version' => 1
        ];
    }
}

if (!function_exists('get_zazsoft_alert_config')) {
    function get_zazsoft_alert_config($conn = null) {
        $default = get_default_zazsoft_config();
        $configFile = get_zazsoft_alert_config_file();
        $config = $default;

        // 1. Try JSON file first as baseline
        if (file_exists($configFile)) {
            $jsonContent = @file_get_contents($configFile);
            if ($jsonContent) {
                $decoded = json_decode($jsonContent, true);
                if (is_array($decoded)) {
                    $config = array_merge($default, $decoded);
                }
            }
        }

        // 2. If DB connection is available, check system_alerts table
        if ($conn && $conn instanceof mysqli && !$conn->connect_error) {
            try {
                $res = @$conn->query("SELECT * FROM system_alerts WHERE alert_key = 'zazsoft_contact_alert' LIMIT 1");
                if ($res && $row = $res->fetch_assoc()) {
                    $config['is_active'] = (int)($row['is_active'] ?? 0);
                    $config['title'] = $row['title'] ?? $default['title'];
                    $config['badge_text'] = $row['badge_text'] ?? $default['badge_text'];
                    $config['message'] = $row['message'] ?? $default['message'];
                    $config['company_name'] = $row['company_name'] ?? $default['company_name'];
                    $config['phone'] = $row['phone'] ?? $default['phone'];
                    $config['whatsapp'] = $row['whatsapp'] ?? $default['whatsapp'];
                    $config['email'] = $row['email'] ?? $default['email'];
                    $config['severity'] = $row['severity'] ?? $default['severity'];
                    $config['display_mode'] = $row['display_mode'] ?? $default['display_mode'];
                    $config['admin_pin'] = !empty($row['admin_pin']) ? $row['admin_pin'] : $default['admin_pin'];
                    $config['alert_version'] = (int)($row['alert_version'] ?? ($config['alert_version'] ?? 1));

                    // Sync to JSON file for fallback resilience
                    @file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
                }
            } catch (Exception $e) {
                // Silently fallback to JSON
            }
        }

        return $config;
    }
}

if (!function_exists('save_zazsoft_alert_config')) {
    function save_zazsoft_alert_config($data, $conn = null) {
        $current = get_zazsoft_alert_config($conn);
        $merged = array_merge($current, $data);

        // Always ensure integer values
        $merged['is_active'] = !empty($merged['is_active']) ? 1 : 0;
        $merged['alert_version'] = time(); // Update version on save to ensure users get updated alert

        $configFile = get_zazsoft_alert_config_file();
        @file_put_contents($configFile, json_encode($merged, JSON_PRETTY_PRINT));

        // Save to Database if connection is valid
        if ($conn && $conn instanceof mysqli && !$conn->connect_error) {
            try {
                $chk = $conn->query("SHOW TABLES LIKE 'system_alerts'");
                if ($chk && $chk->num_rows > 0) {
                    $stmt = $conn->prepare("INSERT INTO system_alerts 
                        (alert_key, is_active, title, badge_text, message, company_name, phone, whatsapp, email, severity, display_mode, admin_pin, alert_version) 
                        VALUES ('zazsoft_contact_alert', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                        is_active = VALUES(is_active),
                        title = VALUES(title),
                        badge_text = VALUES(badge_text),
                        message = VALUES(message),
                        company_name = VALUES(company_name),
                        phone = VALUES(phone),
                        whatsapp = VALUES(whatsapp),
                        email = VALUES(email),
                        severity = VALUES(severity),
                        display_mode = VALUES(display_mode),
                        admin_pin = VALUES(admin_pin),
                        alert_version = VALUES(alert_version)");
                    if ($stmt) {
                        $isActive = (int)$merged['is_active'];
                        $title = (string)$merged['title'];
                        $badgeText = (string)$merged['badge_text'];
                        $message = (string)$merged['message'];
                        $company = (string)$merged['company_name'];
                        $phone = (string)$merged['phone'];
                        $whatsapp = (string)$merged['whatsapp'];
                        $email = (string)$merged['email'];
                        $severity = (string)$merged['severity'];
                        $displayMode = (string)$merged['display_mode'];
                        $adminPin = (string)$merged['admin_pin'];
                        $alertVersion = (int)$merged['alert_version'];

                        $stmt->bind_param('issssssssssi', 
                            $isActive, $title, $badgeText, $message, $company, 
                            $phone, $whatsapp, $email, $severity, $displayMode, 
                            $adminPin, $alertVersion
                        );
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            } catch (Exception $e) {
                // Ignore DB error, file has already been saved
            }
        }

        return $merged;
    }
}
