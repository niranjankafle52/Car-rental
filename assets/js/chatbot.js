(function () {
    'use strict';

    var root = document.getElementById('cbwRoot');
    if (!root) return;

    var launcher = document.getElementById('cbwLauncher');
    var closeBtn = document.getElementById('cbwCloseBtn');
    var panel = document.getElementById('cbwPanel');
    var messagesEl = document.getElementById('cbwMessages');
    var input = document.getElementById('cbwInput');
    var sendBtn = document.getElementById('cbwSendBtn');
    var badge = document.getElementById('cbwBadge');

    var isOpen = false;
    var isSending = false;
    var hasGreeted = false;

    function scrollToBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function addMessage(text, kind) {
        var bubble = document.createElement('div');
        bubble.className = 'cbw-msg cbw-msg-' + kind;
        bubble.textContent = text;
        messagesEl.appendChild(bubble);
        scrollToBottom();
        return bubble;
    }

    function showTyping() {
        var typing = document.createElement('div');
        typing.className = 'cbw-typing';
        typing.id = 'cbwTyping';
        typing.innerHTML = '<span></span><span></span><span></span>';
        messagesEl.appendChild(typing);
        scrollToBottom();
    }

    function hideTyping() {
        var typing = document.getElementById('cbwTyping');
        if (typing) typing.remove();
    }

    function openPanel() {
        isOpen = true;
        root.classList.add('cbw-open', 'cbw-seen');
        panel.setAttribute('aria-hidden', 'false');
        launcher.setAttribute('aria-label', 'Close chat');
        if (!hasGreeted) {
            hasGreeted = true;
            addMessage("Hi! I'm your Rental Assistant. Ask me about cars, pricing, or how booking works — I'm happy to help.", 'bot');
        }
        setTimeout(function () { input.focus(); }, 150);
    }

    function closePanel() {
        isOpen = false;
        root.classList.remove('cbw-open');
        panel.setAttribute('aria-hidden', 'true');
        launcher.setAttribute('aria-label', 'Open chat');
    }

    function togglePanel() {
        if (isOpen) closePanel(); else openPanel();
    }

    function autoResize() {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 90) + 'px';
    }

    function sendMessage() {
        var text = input.value.trim();
        if (!text || isSending) return;

        addMessage(text, 'user');
        input.value = '';
        autoResize();
        isSending = true;
        sendBtn.disabled = true;
        showTyping();

        fetch('chatbot-api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text })
        })
            .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
            .then(function (result) {
                hideTyping();
                if (result.ok && result.data && result.data.reply) {
                    addMessage(result.data.reply, 'bot');
                } else {
                    var errMsg = (result.data && result.data.error) ? result.data.error : 'Something went wrong. Please try again.';
                    addMessage(errMsg, 'error');
                }
            })
            .catch(function () {
                hideTyping();
                addMessage('Could not reach the assistant. Please check your connection and try again.', 'error');
            })
            .finally(function () {
                isSending = false;
                sendBtn.disabled = false;
            });
    }

    launcher.addEventListener('click', togglePanel);
    closeBtn.addEventListener('click', closePanel);
    sendBtn.addEventListener('click', sendMessage);

    input.addEventListener('input', autoResize);
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isOpen) closePanel();
    });
})();
