<?php
if (!defined('ABSPATH')) exit;

/**
 * Painel Administrativo — Justiça Multiportas • Assistente AI
 * v2.0.4
 * Inclui abas: Geral | Personalidade | Aparência | Diagnóstico
 */

add_action('admin_menu', function () {
    add_options_page(
        'Justiça Multiportas • Assistente AI',
        'Justiça Multiportas AI',
        'manage_options',
        'justicamultiportasrj-ai-settings',
        'jmrjai_render_settings_page'
    );
});
// ==========================================================
//  CARREGA OS SCRIPTS DA GALERIA DE MÍDIA
// ==========================================================
add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'settings_page_justicamultiportasrj-ai-settings') {
        wp_enqueue_media(); // 🔥 Importante — permite abrir a biblioteca de mídia
    }
});

add_action('admin_init', function () {
    // Registra todos os campos de configuração
    register_setting('jmrai_settings_group', 'jmrai_apikey');
    register_setting('jmrai_settings_group', 'jmrai_welcome');
    register_setting('jmrai_settings_group', 'jmrai_avatar');
    register_setting('jmrai_settings_group', 'jmrai_system_instruction');
    register_setting('jmrai_settings_group', 'jmrai_tone');
    register_setting('jmrai_settings_group', 'jmrai_theme');
    register_setting('jmrai_settings_group', 'jmrai_enable_floating', [
        'type' => 'boolean',
        'default' => 1,
        'sanitize_callback' => fn($v) => $v ? 1 : 0,
    ]);
});

/**
 * Renderização da página principal do painel
 */
function jmrjai_render_settings_page() {
    if (!current_user_can('manage_options')) return;

    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';

    $tabs = [
        'general'      => '⚙️ Geral',
        'personality'  => '🧠 Personalidade',
        'appearance'   => '🎨 Aparência',
        'diagnostics'  => '🔍 Diagnóstico',
    ];

    ?>
    <div class="wrap">
        <h1>⚖️ Justiça Multiportas • Assistente AI</h1>
        <p class="description">Gerencie as configurações gerais, aparência e comportamento da IA.</p>

        <h2 class="nav-tab-wrapper" style="margin-top:25px;">
            <?php foreach ($tabs as $key => $label): ?>
                <?php
                $active_class = ($active_tab === $key) ? 'nav-tab-active' : '';
                $url = admin_url('options-general.php?page=justicamultiportasrj-ai-settings&tab=' . $key);
                ?>
                <a href="<?php echo esc_url($url); ?>" class="nav-tab <?php echo esc_attr($active_class); ?>">
                    <?php echo esc_html($label); ?>
                </a>
            <?php endforeach; ?>
        </h2>

        <div style="background:#fff;padding:20px;border:1px solid #ccd0d4;border-top:none;margin-top:-1px;">
            <?php
            $tab_file = JMRJAI_PATH . 'admin/tabs/' . $active_tab . '.php';
            if (file_exists($tab_file)) {
                include $tab_file;
            } else {
                echo '<p>❌ Aba não encontrada.</p>';
            }
            ?>
        </div>

        <hr style="margin-top:30px;">
        <p style="font-size:13px;opacity:0.7;">
            Plugin versão <?php echo esc_html(JMRJAI_VERSION); ?> • Desenvolvido por 
            <strong>Charles Vasconcelos & Azimov IA</strong> — 
            <a href="https://github.com/charlesvsouza" target="_blank">repositório oficial</a>.
        </p>
    </div>
    <?php
}

/* ============================================================
   AJAX: Teste de Diagnóstico
============================================================ */
add_action('wp_ajax_jmrjai_diagnostic_test', 'jmrjai_diagnostic_test');
add_action('wp_ajax_nopriv_jmrjai_diagnostic_test', 'jmrjai_diagnostic_test');

function jmrjai_diagnostic_test() {
    check_ajax_referer('jmrjai_diagnostic', 'nonce');

    $response = [
        'success' => true,
        'plugin_version' => defined('JMRJAI_VERSION') ? JMRJAI_VERSION : 'Indefinida',
        'php_version' => phpversion(),
        'wp_version' => get_bloginfo('version'),
        'server_time' => date('Y-m-d H:i:s'),
        'memory_limit' => ini_get('memory_limit'),
        'status' => '✅ Comunicação bem-sucedida com o servidor WordPress.'
    ];

    wp_send_json($response);
}
