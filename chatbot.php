<?php
$chatbotBase = $base ?? '';
$chatbotEmail = current_user()['email'] ?? '';
?>
<div class="ai-chatbot" data-ai-chatbot data-base="<?= e($chatbotBase) ?>">
  <button class="ai-chatbot-button" type="button" data-chatbot-button aria-label="Open NovaTech AI Assistant">
    <span>AI</span>
  </button>
  <section class="ai-chatbot-panel" data-chatbot-panel hidden>
    <header class="ai-chatbot-head">
      <div><strong>NovaTech AI Assistant</strong><small>Gadget finder</small></div>
      <button type="button" data-chatbot-close aria-label="Close chatbot">×</button>
    </header>
    <div class="ai-chatbot-messages" data-chatbot-messages>
      <div class="ai-message bot">Hi, I'm NovaTech AI Assistant. I can help you search gadgets in our store or submit unavailable gadget requests to admin for approval.</div>
      <div class="ai-chatbot-chips" aria-label="Quick chatbot actions">
        <button type="button" data-chatbot-chip="Search iPhone">Search iPhone</button>
        <button type="button" data-chatbot-chip="Find earbuds">Find earbuds</button>
        <button type="button" data-chatbot-chip="Request a gadget">Request a gadget</button>
        <button type="button" data-chatbot-chip="Check request status">Check request status</button>
      </div>
    </div>
    <form class="ai-chatbot-form" data-chatbot-form>
      <input type="hidden" name="email" value="<?= e($chatbotEmail) ?>" data-chatbot-email>
      <input name="message" data-chatbot-input autocomplete="off" placeholder="Ask for a gadget product..." required>
      <button type="submit">Send</button>
    </form>
  </section>
</div>
