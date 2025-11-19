<?php
if (!defined('ABSPATH')) exit;

/**
 * Justiça Multiportas • Assistente AI
 * Chat Memory Manager v2.0.0
 * Suporte híbrido: visitante (sem login) + transiente temporário
 */

/* =======================================================
   SISTEMA DE MEMÓRIA — APENAS PARA VISITANTES
======================================================= */

/**
 * Recupera histórico da conversa via transient
 */
function jmrjai_get_chat_history($session_id) {
    $key = "jmrjai_history_{$session_id}";
    $history = get_transient($key);
    return is_array($history) ? $history : [];
}

/**
 * Salva histórico atualizado da conversa
 */
function jmrjai_save_chat_history($session_id, $history) {
    $key = "jmrjai_history_{$session_id}";
    set_transient($key, $history, 60 * 30); // 30 minutos
}

/**
 * Limpa o histórico da sessão
 */
function jmrjai_clear_chat_history($session_id) {
    delete_transient("jmrjai_history_{$session_id}");
}

/**
 * Retorna ou cria um ID de sessão persistente por visitante
 */
function jmrjai_get_session_id() {
    if (!session_id()) session_start();

    if (empty($_SESSION['jmrjai_session'])) {
        $_SESSION['jmrjai_session'] = wp_generate_uuid4();
    }

    return $_SESSION['jmrjai_session'];
}

/* =======================================================
   AJAX — Limpar histórico (tanto visitantes quanto logados)
======================================================= */
add_action('wp_ajax_jmrjai_clear_history', 'jmrjai_clear_history_ajax');
add_action('wp_ajax_nopriv_jmrjai_clear_history', 'jmrjai_clear_history_ajax');

function jmrjai_clear_history_ajax() {

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'gemfall_nonce')) {
        wp_send_json_error(['message' => 'Nonce inválido.']);
    }

    $session_id = jmrjai_get_session_id();
    jmrjai_clear_chat_history($session_id);

    wp_send_json_success(['message' => 'Histórico apagado com sucesso.']);
}

/* =======================================================
   AUXILIAR — Compacta histórico para prompt contínuo
======================================================= */
function jmrjai_format_history_for_prompt($session_id, $new_message) {
    $history = jmrjai_get_chat_history($session_id);

    // Adiciona nova entrada
    $history[] = ["role" => "user", "parts" => [["text" => $new_message]]];

    // Mantém apenas últimas 12 trocas
    if (count($history) > 12) {
        $history = array_slice($history, -12);
    }

    jmrjai_save_chat_history($session_id, $history);

    return $history;
}

/* =======================================================
   DEPURAÇÃO (uso futuro no painel administrativo)
======================================================= */
function jmrjai_debug_memory_status() {
    $session_id = jmrjai_get_session_id();
    $key = "jmrjai_history_{$session_id}";
    $history = get_transient($key);

    return [
        'session_id' => $session_id,
        'history_count' => is_array($history) ? count($history) : 0,
        'history_key' => $key
    ];
}
