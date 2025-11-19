<?php
if (!defined('ABSPATH')) exit;

/**
 * Aba: 🧠 Personalidade
 * Define o comportamento, tom e instruções de sistema do assistente.
 */

$system_instruction = esc_textarea(get_option('jmrai_system_instruction',
"Você é um assistente jurídico empático, ético e informativo, com foco no apoio a mediação e conciliação de conflitos. 
Explique conceitos com clareza e educação, utilizando linguagem simples e acessível. 
Evite emitir opiniões pessoais e mantenha sempre postura respeitosa e imparcial."));

$tone = esc_attr(get_option('jmrai_tone', 'formal'));
?>

<form method="post" action="options.php" id="jmrai-personality-form">
    <?php settings_fields('jmrai_settings_group'); ?>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="jmrai_system_instruction">📜 Instruções do Sistema</label></th>
            <td>
                <textarea name="jmrai_system_instruction" id="jmrai_system_instruction"
                          rows="8" class="large-text"
                          placeholder="Descreva como a IA deve agir..."><?php echo $system_instruction; ?></textarea>
                <p class="description">Essas instruções moldam a personalidade e o comportamento da IA em todas as respostas.</p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="jmrai_tone">🎭 Tom da Conversa</label></th>
            <td>
                <select name="jmrai_tone" id="jmrai_tone">
                    <option value="formal" <?php selected($tone, 'formal'); ?>>Formal e técnico</option>
                    <option value="empatico" <?php selected($tone, 'empatico'); ?>>Empático e acolhedor</option>
                    <option value="neutro" <?php selected($tone, 'neutro'); ?>>Neutro e objetivo</option>
                    <option value="educacional" <?php selected($tone, 'educacional'); ?>>Educacional e explicativo</option>
                    <option value="criativo" <?php selected($tone, 'criativo'); ?>>Criativo e inspirador</option>
                </select>
                <p class="description">Define o estilo de linguagem adotado nas respostas.</p>
            </td>
        </tr>
    </table>
    <?php submit_button('Salvar Personalidade'); ?>
</form>

<script>
(function(){
    document.querySelector('#jmrai-personality-form').addEventListener('submit', () => {
        alert('🧠 Personalidade atualizada com sucesso!');
    });
})();
</script>
