<link rel="stylesheet" href="assets/css/chatbot.css" type="text/css">

<div class="cbw-root" id="cbwRoot">
  <div class="cbw-panel" id="cbwPanel" role="dialog" aria-label="Chat assistant" aria-hidden="true">
    <div class="cbw-header">
      <div class="cbw-header-avatar"><i class="fa fa-comment" aria-hidden="true"></i></div>
      <div class="cbw-header-text">
        <div class="cbw-header-title">Rental Assistant</div>
        <div class="cbw-header-status">Online</div>
      </div>
      <button type="button" class="cbw-header-close" id="cbwCloseBtn" aria-label="Close chat">&times;</button>
    </div>
    <div class="cbw-messages" id="cbwMessages"></div>
    <div class="cbw-input-row">
      <textarea class="cbw-input" id="cbwInput" rows="1" placeholder="Ask about cars, booking, pricing..." maxlength="2000"></textarea>
      <button type="button" class="cbw-send" id="cbwSendBtn" aria-label="Send message">
        <i class="fa fa-paper-plane" aria-hidden="true"></i>
      </button>
    </div>
  </div>
  <button type="button" class="cbw-launcher" id="cbwLauncher" aria-label="Open chat">
    <i class="fa fa-comment cbw-icon-chat" aria-hidden="true"></i>
    <i class="fa fa-times cbw-icon-close" aria-hidden="true"></i>
    <span class="cbw-badge" id="cbwBadge"></span>
  </button>
</div>

<script src="assets/js/chatbot.js"></script>
