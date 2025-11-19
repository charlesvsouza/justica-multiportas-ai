<?php
if (!defined('ABSPATH')) exit;

/**
 * Aba: 🎨 Aparência
 * Escolha de avatar, cores e visual do widget do assistente.
 */

$avatar = esc_url(get_option('jmrai_avatar', plugin_dir_url(__FILE__) . '../../assets/img/geniodetoga.png'));
$theme  = esc_attr(get_option('jmrai_theme', 'light'));
?>

<form method="post" action="options.php" id="jmrai-appearance-form">
    <?php settings_fields('jmrai_settings_group'); ?>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="jmrai_avatar">🧙 Avatar da IA</label></th>
            <td>
                <input type="text" name="jmrai_avatar" id="jmrai_avatar"
                       value="<?php echo $avatar; ?>" class="regular-text"
                       placeholder="URL do avatar ou escolha da galeria...">
                <button type="button" class="button" id="jmrai-upload-avatar">📁 Selecionar da Galeria</button>
                <p class="description">
                    Você pode colar uma URL direta ou selecionar uma imagem da biblioteca de mídia do WordPress.
                </p>
                <div style="margin-top:10px;">
                    <img id="jmrai-avatar-preview" src="<?php echo $avatar; ?>" width="80" height="80"
                         style="border-radius:50%;box-shadow:0 0 6px rgba(0,0,0,0.25);">
                </div>
            </td>
        </tr>

        <tr>
            <th scope="row"><label for="jmrai_theme">🎨 Tema do Chat</label></th>
            <td>
                <select name="jmrai_theme" id="jmrai_theme">
                    <option value="light" <?php selected($theme, 'light'); ?>>Claro (Padrão)</option>
                    <option value="dark" <?php selected($theme, 'dark'); ?>>Escuro</option>
                    <option value="blue" <?php selected($theme, 'blue'); ?>>Azul Justiça</option>
                    <option value="modern" <?php selected($theme, 'modern'); ?>>Minimalista</option>
                </select>
                <p class="description">Escolha o estilo visual do chat exibido no site.</p>
            </td>
        </tr>
    </table>

    <?php submit_button('Salvar Aparência'); ?>
</form>

<script>
(function(){
    // === SELETOR DE AVATAR ===
    const uploadBtn = document.getElementById('jmrai-upload-avatar');
    const avatarInput = document.getElementById('jmrai_avatar');
    const avatarPreview = document.getElementById('jmrai-avatar-preview');

    if (uploadBtn) {
        uploadBtn.addEventListener('click', function(e) {
            e.preventDefault();

            const frame = wp.media({
                title: 'Selecionar Avatar da IA',
                button: { text: 'Usar este avatar' },
                multiple: false
            });

            frame.on('select', function() {
                const attachment = frame.state().get('selection').first().toJSON();
                avatarInput.value = attachment.url;
                avatarPreview.src = attachment.url;
            });

            frame.open();
        });
    }

    // === PRÉ-VISUALIZAÇÃO AO VIVO ===
    if (avatarInput) {
        avatarInput.addEventListener('input', function(){
            avatarPreview.src = this.value || '<?php echo plugin_dir_url(__FILE__) . '../../assets/img/geniodetoga.png'; ?>';
        });
    }
})();
</script>
