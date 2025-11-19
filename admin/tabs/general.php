<?php
if (!defined('ABSPATH')) exit;

/**
 * Aba: ⚙️ Geral
 * Configurações básicas de integração com o Gemini e opções globais do assistente.
 */

// Obtém as opções salvas
$apikey = esc_attr(get_option('jmrai_apikey', ''));
$welcome = esc_attr(get_option('jmrai_welcome', 'Olá! Sou o Assistente de Justiça. Como posso ajudar?'));
$enable_floating = (bool) get_option('jmrai_enable_floating', 1);
?>

<form method="post" action="options.php" id="jmrai-general-form">
    <?php settings_fields('jmrai_settings_group'); ?>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="jmrai_apikey">🔑 Chave da API Gemini</label></th>
            <td>
                <input type="password" name="jmrai_apikey" id="jmrai_apikey" 
                       value="<?php echo $apikey; ?>" class="regular-text" placeholder="Insira sua chave da API Gemini...">
                <p class="description">
                    É necessário obter uma chave da API Gemini no site do Google AI Studio.
                    <br>
                    <a href="https://aistudio.google.com/app/apikey" target="_blank">🔗 Obter chave Gemini</a>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row"><label for="jmrai_welcome">💬 Mensagem de Boas-vindas</label></th>
            <td>
                <input type="text" name="jmrai_welcome" id="jmrai_welcome" 
                       value="<?php echo $welcome; ?>" class="regular-text"
                       placeholder="Mensagem exibida ao abrir o chat...">
                <p class="description">Essa mensagem é mostrada na primeira interação do usuário com o assistente.</p>
            </td>
        </tr>

        <tr>
            <th scope="row">💡 Chat Flutuante</th>
            <td>
                <label>
                    <input type="checkbox" name="jmrai_enable_floating" value="1" <?php checked($enable_floating, true); ?>>
                    Ativar o widget flutuante no site
                </label>
                <p class="description">Desmarque para ocultar o botão de chat flutuante na interface pública.</p>
            </td>
        </tr>
    </table>

    <?php submit_button('Salvar Configurações Gerais'); ?>
</form>

<script>
(function(){
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('jmrai-general-form');
        form.addEventListener('submit', () => {
            alert('⚙️ Configurações gerais salvas com sucesso!');
        });
    });
})();
</script>
