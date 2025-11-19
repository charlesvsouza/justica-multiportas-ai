(function () {

    /* ================================================================
       READY FUNCTION — executa quando o DOM estiver pronto
    ================================================================ */
    function ready(fn) {
        if (document.readyState !== "loading") return fn();
        document.addEventListener("DOMContentLoaded", fn);
    }

    /* ================================================================
       TEXT-TO-SPEECH (TTS)
       ================================================================ */
    let isReading = false;

    function stopReading() {
        if (window.speechSynthesis) window.speechSynthesis.cancel();
        isReading = false;
    }

    function speak(text, btn) {
        stopReading(); // garante que nenhuma leitura anterior esteja ativa
        if (!window.speechSynthesis) return;

        const utter = new SpeechSynthesisUtterance(text);
        utter.lang = navigator.language || "pt-BR";
        utter.rate = 1;
        utter.pitch = 1;
        utter.volume = 1;

        isReading = true;
        if (btn) btn.textContent = "🔇";

        utter.onend = utter.onerror = () => {
            isReading = false;
            if (btn) btn.textContent = "🗣️";
        };

        window.speechSynthesis.speak(utter);
    }

    /* ================================================================
       FALLBACK DO AVATAR (caso o link falhe)
    ================================================================ */
    if (!window.JMRJAI_Settings) window.JMRJAI_Settings = {};

    if (
        !JMRJAI_Settings.avatarUrl ||
        typeof JMRJAI_Settings.avatarUrl !== "string" ||
        !JMRJAI_Settings.avatarUrl.trim().length
    ) {
        JMRJAI_Settings.avatarUrl =
            "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJYAAACWCAYAAAA8AXHiAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAEUklEQVR4nO3dQW7bQBCFYf8uRNUlsMGIEoMTXDAzKEqAl0RZ5pTq0rKpbv7+mH5lwD98+c3ZZQAgIIIAAAggggAACCJxE5rWfqPfncfq13bMcX+/rq5tbMx1ffQ9U6zWZb+JpQfSRdmDJ6mMkNujcdkAXMzTA2V5J8D4HSmRmMCX6sVFi/ywD4m/1gVPL3ofNnhNfKq/nvEPMzTjzWUwM6yqjH53Clfi5YdbXgVJxixOz4lkLZlwl9a+F8yrp3hMcS9J3Ek0/f6GceCuv6tVR9bXJXGcr4+HqrOrPDPF4cfpClf7WwLRprG5wGQyWW6LNh+z03FUr96w2AfjHkaA+Oqba8b/DGxA6V3kZbOQyG8m1L11p3QHwlYt5Oq9rTi4SY8/pqQyPu+thZnW7r+Yl17cHY+pNxqpvJp5kZXPRcN3uOk1tm1gL5O7pXN8ecbArlU/B9rhRf8SHkO75mQxdjq5RvlVg7x+DjJX5vEsOxD8N6EXC1Dyvcn0jsXeNwcvL9drs3UydOqPVd1mT4im3ZjPRMTsQb7I+G8tlgdO8Xj+lYnJ8wzEr9W0Tfmr1qUOkrH0Sv+7bRX7fB7r7iqn4TjMbZ8E7/bAObxPS3zFsWYPuRWu83c4vbc4v3R4xx/7+ZqGVkeatxXY3zqA32T8f7K3X4vV34R1bnG5vR7lyvwrlDvsLb8vPaH+lmA5HHnNvdsUAcMEIIIIAAAggggAACCCCAwI8BeGpaK93Z4iYAAAAASUVORK5CYII=";
    }

    /* ================================================================
       MARKDOWN PARSER — protege e formata o texto
    ================================================================ */
    function markdownToHtml(text) {
        if (!text) return "";

        // Sanitização básica
        text = text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");

        // **negrito**
        text = text.replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>");

        // *itálico*
        text = text.replace(/\*(.*?)\*/g, "<em>$1</em>");

        // Listas
        text = text.replace(
            /(^(\*|-)\s.*(?:\n|$))+?/gm,
            block => {
                const items = block.trim().split("\n").map(line => {
                    return "<li>" + line.replace(/^(\*|-)\s+/, "") + "</li>";
                }).join("");
                return "<ul>" + items + "</ul>";
            }
        );

        // Links [texto](url)
        text = text.replace(
            /\[(.*?)\]\((https?:\/\/.*?)\)/g,
            `<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>`
        );

        // Quebras de linha
        return text.replace(/\n/g, "<br>");
    }

    /* ================================================================
       LOCAL STORAGE — armazena histórico local do chat
    ================================================================ */
    const ChatMemory = {
        KEY: "jmrjai_chat_history",

        load() {
            try {
                return JSON.parse(localStorage.getItem(this.KEY)) || [];
            } catch {
                return [];
            }
        },

        save(list) {
            localStorage.setItem(this.KEY, JSON.stringify(list));
        },

        clear() {
            localStorage.removeItem(this.KEY);
        }
    };

    /* ================================================================
       CSS INLINE — fallback automático e responsivo
    ================================================================ */
    function injectFallbackCSS() {
        if (document.getElementById("jmrjai-inline-style")) return;

        const css = `
        .jmrjai-hidden { display:none !important; }

        /* BOTÃO FLUTUANTE */
        #jmrjai-floating-btn {
            position: fixed;
            right: 20px;
            bottom: 22px;
            width: 70px;
            height: 70px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 4px 14px rgba(0,0,0,0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 2147483000;
        }
        #jmrjai-floating-btn img {
            width: 64px;
            height: 64px;
            border-radius: 50%;
        }

        /* CONTAINER DO CHAT */
        #jmrjai-container {
            position: fixed;
            right: 20px;
            bottom: 110px;
            width: 360px;
            height: 520px;
            background: white;
            border-radius: 14px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid #d0e8ff;
            z-index: 2147482999;
            transition: all .25s ease;
        }

        #jmrjai-container.expanded {
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            border-radius: 0 !important;
        }

        #jmrjai-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: #dff4ff;
            border-bottom: 1px solid #bfe4ff;
        }

        #jmrjai-controls {
            margin-left: auto;
            display: flex;
            gap: 14px;
            font-size: 22px;
        }
        #jmrjai-controls span { cursor: pointer; }

        /* ÁREA DE MENSAGENS */
        #jmrjai-messages {
            flex: 1;
            overflow-y: auto;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            background: #fff;
        }

        .jm-msg {
            max-width: 80%;
            position: relative;
            border-radius: 12px;
        }

        .jm-user {
            align-self: flex-end;
            background: #ececec;
            padding: 10px 12px;
        }

        .jm-ai {
            align-self: flex-start;
            background: #e7f5ff;
            padding: 12px 14px 48px 60px;
            color: #003d66;
        }

        .jm-ai::before {
            content: "";
            position: absolute;
            left: 10px;
            top: 10px;
            width: 38px;
            height: 38px;
            background-image: url('${JMRJAI_Settings.avatarUrl}');
            background-size: cover;
            border-radius: 50%;
            box-shadow: 0 0 8px rgba(0,150,255,0.4);
        }

        .jm-msg-inner {
            font-size: 15px;
            line-height: 1.5;
            word-break: break-word;
        }

        /* BOTÃO DE LEITURA */
        .jm-tts-btn {
            position: absolute;
            bottom: 6px;
            right: 10px;
            font-size: 20px;
            cursor: pointer;
            background: none;
            border: none;
        }

        /* BOTÃO "VER MAIS" */
        .jm-readmore {
            margin-top: 4px;
            font-size: 13px;
            background: none;
            border: none;
            cursor: pointer;
            color: #0077cc;
        }

        /* ÁREA DE INPUT */
        #jmrjai-input-area {
            display: flex;
            gap: 8px;
            padding: 10px;
            background: #f3f9ff;
            border-top: 1px solid #cfeaff;
        }

        /* DIGITANDO... */
        .typing-dots span {
            display: inline-block;
            width: 6px;
            height: 6px;
            background: #007bff;
            border-radius: 50%;
            margin-right: 4px;
            animation: blink 1.4s infinite both;
        }

        .typing-dots span:nth-child(2) { animation-delay: .2s; }
        .typing-dots span:nth-child(3) { animation-delay: .4s; }

        @keyframes blink {
            0%, 80%, 100% { opacity: 0; transform: scale(0.8); }
            40% { opacity: 1; transform: scale(1); }
        }
        `;

        const style = document.createElement("style");
        style.id = "jmrjai-inline-style";
        style.appendChild(document.createTextNode(css));
        document.head.appendChild(style);
    }

    /* ================================================================
       APPEND MESSAGE — adiciona mensagens à interface
    ================================================================ */
    function appendMessage(role, text) {
        const area = document.getElementById("jmrjai-messages");
        if (!area) return;

        const bubble = document.createElement("div");
        bubble.className = "jm-msg " + (role === "user" ? "jm-user" : "jm-ai");

        const inner = document.createElement("div");
        inner.className = "jm-msg-inner";
        inner.innerHTML = markdownToHtml(text);
        bubble.appendChild(inner);

        /* === Funções exclusivas para mensagens da IA === */
        if (role === "ai") {
            // Botão TTS
            const ttsBtn = document.createElement("button");
            ttsBtn.className = "jm-tts-btn";
            ttsBtn.textContent = "🗣️";
            bubble.appendChild(ttsBtn);

            const fullText = text;
            ttsBtn.addEventListener("click", () => {
                if (!isReading) speak(fullText, ttsBtn);
                else {
                    stopReading();
                    ttsBtn.textContent = "🗣️";
                }
            });

            // "Ver mais"
            setTimeout(() => {
                const maxHeight = 420;
                const realHeight = inner.scrollHeight;

                if (realHeight > maxHeight) {
                    inner.style.maxHeight = maxHeight + "px";
                    inner.style.overflow = "hidden";

                    const more = document.createElement("button");
                    more.className = "jm-readmore";
                    more.textContent = "... ver mais";

                    more.addEventListener("click", () => {
                        inner.style.maxHeight = "none";
                        inner.style.overflow = "visible";
                        more.remove();
                        setTimeout(() => area.scrollTop = area.scrollHeight, 100);
                    });

                    bubble.appendChild(more);
                }
            }, 60);
        }

        // Adiciona na área e rola até o fim
        area.appendChild(bubble);
        setTimeout(() => area.scrollTop = area.scrollHeight, 30);
    }

    /* ================================================================
       SHOW TYPING — mostra "Digitando..." estilo WhatsApp
    ================================================================ */
    function showTyping() {
        const area = document.getElementById("jmrjai-messages");
        if (!area) return;

        const bubble = document.createElement("div");
        bubble.className = "jm-msg jm-ai jm-typing";
        bubble.innerHTML = `
            <div class="jm-msg-inner">
                <div class="typing-dots"><span></span><span></span><span></span></div>
            </div>
        `;

        area.appendChild(bubble);
        setTimeout(() => area.scrollTop = area.scrollHeight, 20);
    }
    /* ================================================================
       ENVIO DE MENSAGENS — AJAX para o proxy PHP
    ================================================================ */
    async function sendToProxy(text) {
        const fd = new FormData();
        fd.append("action", "jmrjai_proxy");
        fd.append("message", text);
        fd.append("nonce", JMRJAI_Settings.nonce);

        try {
            const req = await fetch(JMRJAI_Settings.ajaxUrl, {
                method: "POST",
                body: fd
            });

            const json = await req.json();

            if (json.success && json.data.reply) return json.data.reply;
            return "❌ " + (json.data?.message || "Erro inesperado.");
        } catch {
            return "❌ Falha ao conectar ao servidor.";
        }
    }

    /* ================================================================
       MAIN — Inicialização e eventos do chat
    ================================================================ */
    ready(function () {
        injectFallbackCSS();
        stopReading();

        /* ============================================================
           BOTÃO FLUTUANTE
        ============================================================ */
        let btn = document.getElementById("jmrjai-floating-btn");
        if (!btn) {
            btn = document.createElement("div");
            btn.id = "jmrjai-floating-btn";
            btn.innerHTML = `<img src="${JMRJAI_Settings.avatarUrl}" alt="AI">`;
            document.body.appendChild(btn);
        }

        /* ============================================================
           CONTAINER DO CHAT
        ============================================================ */
        let cont = document.getElementById("jmrjai-container");
        if (!cont) {
            cont = document.createElement("div");
            cont.id = "jmrjai-container";
            cont.classList.add("jmrjai-hidden");
            cont.innerHTML = `
                <div id="jmrjai-header">
                    <img src="${JMRJAI_Settings.avatarUrl}" class="avatar" width="40" height="40">
                    <div id="jmrjai-title">Justiça Multiportas • Assistente AI</div>
                    <div id="jmrjai-controls">
                        <span id="jmrjai-expand" title="Expandir/Restaurar">🗖</span>
                        <span id="jmrjai-clear" title="Limpar conversa">🗑</span>
                        <span id="jmrjai-close" title="Fechar">✖</span>
                    </div>
                </div>

                <div id="jmrjai-messages"></div>

                <div id="jmrjai-input-area">
                    <input type="text" id="jmrjai-input" placeholder="Digite sua dúvida...">
                    <button id="jmrjai-send">Enviar</button>
                </div>
            `;
            document.body.appendChild(cont);
        }

        /* ============================================================
           ELEMENTOS PRINCIPAIS
        ============================================================ */
        const closeBtn = document.getElementById("jmrjai-close");
        const expandBtn = document.getElementById("jmrjai-expand");
        const clearBtn  = document.getElementById("jmrjai-clear");
        const sendBtn   = document.getElementById("jmrjai-send");
        const input     = document.getElementById("jmrjai-input");
        const area      = document.getElementById("jmrjai-messages");

        /* ============================================================
           RESTAURA HISTÓRICO LOCAL
        ============================================================ */
        const old = ChatMemory.load();
        if (old.length) {
            old.forEach(m => appendMessage(m.role, m.text));
        } else if (JMRJAI_Settings.welcome) {
            appendMessage("ai", JMRJAI_Settings.welcome);
            ChatMemory.save([{ role: "ai", text: JMRJAI_Settings.welcome }]);
        }

        /* ============================================================
           EVENTOS DE CONTROLE
        ============================================================ */
        // Abrir chat
        btn.addEventListener("click", () => {
            cont.classList.remove("jmrjai-hidden");
            btn.style.display = "none";
            setTimeout(() => area.scrollTop = area.scrollHeight, 70);
        });

        // Fechar chat
        closeBtn.addEventListener("click", () => {
            stopReading();
            cont.classList.add("jmrjai-hidden");
            btn.style.display = "flex";
        });

        // Expandir / restaurar
        expandBtn.addEventListener("click", () => {
            cont.classList.toggle("expanded");
            expandBtn.textContent = cont.classList.contains("expanded") ? "🗗" : "🗖";
            setTimeout(() => area.scrollTop = area.scrollHeight, 100);
        });

        // Limpar conversa
        clearBtn.addEventListener("click", async () => {
            stopReading();
            ChatMemory.clear();
            area.innerHTML = "";

            const fd = new FormData();
            fd.append("action", "jmrjai_clear_history");
            fd.append("nonce", JMRJAI_Settings.nonce);
            await fetch(JMRJAI_Settings.ajaxUrl, { method: "POST", body: fd });

            if (JMRJAI_Settings.welcome) {
                appendMessage("ai", JMRJAI_Settings.welcome);
                ChatMemory.save([{ role: "ai", text: JMRJAI_Settings.welcome }]);
            }
        });

        /* ============================================================
           ENVIO DE MENSAGENS — handleSend()
        ============================================================ */
        async function handleSend() {
            const text = input.value.trim();
            if (!text) return;

            input.value = "";
            stopReading();
            appendMessage("user", text);

            const hist = ChatMemory.load();
            hist.push({ role: "user", text });
            ChatMemory.save(hist);

            showTyping();

            const reply = await sendToProxy(text);

            // Remove “digitando...”
            const typing = area.querySelector(".jm-typing");
            if (typing) {
                typing.style.opacity = "0";
                setTimeout(() => typing.remove(), 150);
            }

            appendMessage("ai", reply);

            const updated = ChatMemory.load();
            updated.push({ role: "ai", text: reply });
            ChatMemory.save(updated);
        }

        sendBtn.addEventListener("click", handleSend);
        input.addEventListener("keydown", e => {
            if (e.key === "Enter") handleSend();
        });
    });

})(); // Fim do script
