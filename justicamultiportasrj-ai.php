<?php
/**
 * Plugin Name: Justiça Multiportas RJ • Assistente de IA
 * Plugin URI: https://github.com/charlesvsouza/justica-multiportas-ai
 * Description: Assistente de Inteligência Artificial baseado no modelo Gemini 2.0 Flash EXP, integrado ao WordPress com leitura de voz, memória persistente e layout responsivo.
 * Version: 2.0.4
 * Author: Charles Vasconcelos & Azimov IA
 * Author URI: https://github.com/charlesvsouza
 * License: GPLv2 or later
 * Text Domain: jmrjai
 * Update URI: https://raw.githubusercontent.com/charlesvsouza/justica-multiportas-ai/main/update.json
 */

if (!defined('ABSPATH')) exit;

// ===========================================================
// CONSTANTES E DEFINIÇÕES
// ===========================================================
define('JMRJAI_PATH', plugin_dir_path(__FILE__));
define('JMRJAI_URL', plugin_dir_url(__FILE__));
define('JMRJAI_VERSION', '2.0.4');

// ===========================================================
// INCLUDES PRINCIPAIS
// ===========================================================
require_once JMRJAI_PATH . 'includes/chat-proxy.php';
require_once JMRJAI_PATH . 'includes/chat-memory.php';
require_once JMRJAI_PATH . 'admin/settings-page.php';

// ===========================================================
// FRONT-END SCRIPTS E ESTILOS
// ===========================================================
add_action('wp_enqueue_scripts', function () {

    $avatar = trim(get_option('jmrai_avatar', ''));
    if (!$avatar) $avatar = JMRJAI_URL . 'assets/img/geniodetoga.png';

    wp_enqueue_script('jmrjai-chat-js', JMRJAI_URL . 'public/js/chat-widget.js', [], JMRJAI_VERSION, true);
    wp_enqueue_style('jmrjai-chat-css', JMRJAI_URL . 'public/css/chat-style.css', [], JMRJAI_VERSION);

    wp_localize_script('jmrjai-chat-js', 'JMRJAI_Settings', [
        'ajaxUrl'   => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('jmrjai_nonce'),
        'avatarUrl' => esc_url($avatar),
        'welcome'   => get_option('jmrai_welcome', __('Olá! Sou o Assistente de Justiça. Como posso ajudar?', 'jmrjai')),
    ]);
});

// ===========================================================
// ATIVAÇÃO E DESATIVAÇÃO DO PLUGIN
// ===========================================================
register_activation_hook(__FILE__, function () {
    // Inicializa opções padrão na ativação
    if (!get_option('jmrai_welcome')) {
        update_option('jmrai_welcome', 'Olá! Sou o Assistente de Justiça. Como posso ajudar?');
    }
    if (!get_option('jmrai_enable_floating')) {
        update_option('jmrai_enable_floating', 1);
    }
});

register_deactivation_hook(__FILE__, function () {
    // Mantém dados, apenas exemplo
    // delete_option('jmrai_apikey');
});
