<?php
if (!defined('ABSPATH')) exit;

/**
 * Proxy de comunicação com Gemini API — Justiça Multiportas RJ
 * Versão: 2.0.0
 */

add_action('wp_ajax_jmrjai_proxy', 'jmrjai_proxy_handler');
add_action('wp_ajax_nopriv_jmrjai_proxy', 'jmrjai_proxy_handler');
// ======================================================
// Teste de Diagnóstico (para painel administrativo)
// ======================================================
add_action('wp_ajax_jmrai_diagnostic_test', 'jmrai_diagnostic_test');
add_action('wp_ajax_nopriv_jmrai_diagnostic_test', 'jmrai_diagnostic_test');

function jmrai_diagnostic_test() {
    check_ajax_referer('jmrai_diagnostic', 'nonce');

    $response = [
        'status' => 'ok',
        'php_version' => phpversion(),
        'wp_version' => get_bloginfo('version'),
        'session_id' => session_id(),
        'memory_status' => function_exists('jmrjai_debug_memory_status') ? jmrjai_debug_memory_status() : 'sem histórico',
        'time' => current_time('mysql'),
    ];

    wp_send_json_success($response);
}

function jmrjai_proxy_handler() {

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'jmrjai_nonce')) {
        wp_send_json_error(['message' => 'Acesso negado.']);
    }

    $message = sanitize_text_field($_POST['message'] ?? '');
    if (!$message) wp_send_json_error(['message' => 'Mensagem vazia.']);

    $api_key = trim(get_option('jmrjai_apikey', ''));
    if (!$api_key) wp_send_json_error(['message' => 'API Key não configurada.']);

    $session_id = jmrjai_get_session_id();
    $history = jmrjai_get_chat_history($session_id);

    $history[] = ["role" => "user", "parts" => [["text" => $message]]];

    if (count($history) > 12) $history = array_slice($history, -12);

    $payload = [
        "contents" => $history,
        "generationConfig" => [
            "temperature" => 0.55,
            "maxOutputTokens" => 900,
            "topP" => 0.9
        ]
    ];

    $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'pt-BR', 0, 2);

    $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$api_key}";

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 45
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        wp_send_json_error(['message' => "Erro de conexão com Gemini: $error"]);
    }

    $data = json_decode($response, true);

    if (isset($data['error'])) {
        wp_send_json_error(['message' => $data['error']['message'] ?? 'Erro desconhecido']);
    }

    $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    if (!$reply) wp_send_json_error(['message' => 'Nenhuma resposta retornada.']);

    $history[] = ["role" => "model", "parts" => [["text" => $reply]]];
    jmrjai_save_chat_history($session_id, $history);

    wp_send_json_success(['reply' => $reply, 'lang' => $lang]);
}
