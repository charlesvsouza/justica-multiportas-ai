<?php
if (!defined('ABSPATH')) exit;

/**
 * Aba: ⚙️ Diagnóstico
 * Exibe status técnico e permite testar a comunicação com o servidor.
 */

$api_key   = get_option('jmrai_apikey', '');
$session   = function_exists('jmrjai_get_session_id') ? jmrjai_get_session_id() : '(sessão não iniciada)';
$history   = function_exists('jmrjai_debug_memory_status') ? jmrjai_debug_memory_status() : [];
$avatar    = esc_url(get_option('jmrai_avatar', plugin_dir_url(__FILE__) . '../../assets/img/geniodetoga.png'));
$plugin_v  = defined('JMRJAI_VERSION') ? JMRJAI_VERSION : 'Desconhecida';
?>

<div class="wrap">
    <h2>⚙️ Diagnóstico do Sistema</h2>
    <p>Use esta aba para verificar o estado de funcionamento do plugin <strong>Justiça Multiportas • Assistente AI</strong>.</p>

    <table class="widefat striped" style="max-width:820px;margin-top:15px;">
        <thead>
            <tr><th>Componente</th><th>Status</th></tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Versão do Plugin</strong></td>
                <td><?php echo esc_html($plugin_v); ?></td>
            </tr>

            <tr>
                <td><strong>Chave da API Gemini</strong></td>
                <td>
                    <?php echo $api_key ? '✅ Configurada' : '⚠️ Não informada'; ?>
                    <?php if ($api_key): ?>
                        <code style="opacity:0.6;"><?php echo esc_html(substr($api_key, 0, 8)) . '••••••••'; ?></code>
                    <?php endif; ?>
                </td>
            </tr>

            <tr>
                <td><strong>ID da Sessão</strong></td>
                <td><code><?php echo esc_html($session); ?></code></td>
            </tr>

            <tr>
                <td><strong>Histórico de Conversa</strong></td>
                <td>
                    <?php if (!empty($history) && isset($history['history_count'])): ?>
                        🗂️ <?php echo esc_html($history['history_count']); ?> mensagens salvas
                        <br><small>Chave: <code><?php echo esc_html($history['history_key']); ?></code></small>
                    <?php else: ?>
                        📭 Nenhum histórico registrado.
                    <?php endif; ?>
                </td>
            </tr>

            <tr>
                <td><strong>Avatar da IA</strong></td>
                <td>
                    <img src="<?php echo esc_url($avatar); ?>" width="64" height="64"
                         style="border-radius:50%;box-shadow:0 0 5px rgba(0,0,0,0.2);vertical-align:middle;">
                    <span style="margin-left:10px;"><?php echo basename($avatar); ?></span>
                </td>
            </tr>
        </tbody>
    </table>

    <h3 style="margin-top:30px;">🔍 Teste de Comunicação com o Servidor</h3>
    <button type="button" class="button button-primary" id="jmrai-run-diagnostic">Executar Teste</button>
    <pre id="jmrai-diagnostic-result"
         style="margin-top:15px;background:#f8f8f8;padding:10px;border-radius:6px;max-width:820px;overflow:auto;"></pre>
</div>

<script>
(function(){
    const btn = document.getElementById('jmrai-run-diagnostic');
    const output = document.getElementById('jmrai-diagnostic-result');

    if (!btn || !output) return;

    btn.addEventListener('click', async () => {
        output.textContent = '⏳ Testando comunicação com o servidor...';
        btn.disabled = true;

        try {
            const res = await fetch(ajaxurl, {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'jmrjai_diagnostic_test',
                    nonce: '<?php echo wp_create_nonce('jmrjai_diagnostic'); ?>'
                })
            });

            const json = await res.json();
            output.textContent = JSON.stringify(json, null, 2);
        } catch (e) {
            output.textContent = '❌ Erro ao executar teste: ' + e.message;
        }

        btn.disabled = false;
    });
})();
</script>
