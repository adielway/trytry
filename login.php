<?php
require_once __DIR__ . '/config.php';
if (is_logged_in()) {
    redirect_to_dashboard();
}

$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Grading Portal - Login</title>

  <!-- Bootstrap (leave this) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom styles (make sure this is AFTER bootstrap link) -->
  <style>
    /* full height royal-blue background */
    html, body {
      height: 100%;
    }

    body {
  position: relative;
  min-height: 100vh;
  margin: 0;
  font-family: Arial, sans-serif;
  background: #003366; /* fallback royal blue if image fails */
  overflow-x: hidden;
}

body::before {
  content: "";
  position: fixed;
  inset: 0;
  background: url('anhs.jpg') no-repeat center center fixed;
  background-size: cover;
  opacity: 0.25;  /* 25% transparency */
  z-index: -1;   /* keep it behind everything */
}


    /* in case body has .bg-light or other bootstrap utility */
    body.bg-light, .bg-light {
      background: none !important;
    }

    /* card (login box) */
    .login-card {
      width: 100%;
      max-width: 420px;
      border-radius: 12px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.2);
      overflow: hidden;
    }

    /* make the card white and text dark */
    .login-card .card-body {
      background: #ffffff;
      color: #0b1220;
      padding: 28px;
    }

    /* small header area on blue background */
    .login-top {
      padding: 22px;
      text-align: center;
      color: #fff;
    }

    .login-top h3 { margin: 0; font-weight: 600; }

    /* custom button: default royalblue, on hover -> white background */
    .btn-login {
      background-color: #1e40af;
      color: #fff;
      border: 2px solid #1e40af;
      transition: all .15s ease;
    }
    .btn-login:hover, .btn-login:focus {
      background-color: #ffffff;
      color: #1e40af;
      border-color: #ffffff;
      box-shadow: none;
    }

    /* small tweak for form inputs */
    .form-control:focus {
      box-shadow: 0 0 0 0.15rem rgba(30,64,175,0.15);
      border-color: #1e40af;
    }

    /* small responsive padding */
    @media (max-width: 480px) {
      .login-top { padding: 14px; }
      .login-card .card-body { padding: 18px; }
    }

    /* Chatbot container */
#faq-chatbot {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 9999;
}

/* Toggle button */
#chat-toggle {
  background: #1e40af;
  color: #fff;
  padding: 10px 15px;
  border-radius: 20px;
  cursor: pointer;
  font-size: 14px;
}

/* Chat box */
#chat-box {
  width: 300px;
  height: 400px;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.3);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Header */
.chat-header {
  background: #1e40af;
  color: #fff;
  padding: 10px;
  font-weight: bold;
  display: flex;
  justify-content: space-between;
}

/* Body */
.chat-body {
  flex: 1;
  padding: 10px;
  overflow-y: auto;
  font-size: 13px;
}

/* Messages */
.bot-msg {
  background: #f1f5f9;
  padding: 8px;
  margin-bottom: 5px;
  border-radius: 8px;
}

/* FAQ buttons */
.faq-btn {
  width: 100%;
  margin-top: 5px;
  padding: 6px;
  border: none;
  background: #e2e8f0;
  border-radius: 6px;
  cursor: pointer;
  font-size: 12px;
}

.faq-btn:hover {
  background: #cbd5f5;
}

/* Input */
.chat-input {
  display: flex;
  border-top: 1px solid #ddd;
}

.chat-input input {
  flex: 1;
  border: none;
  padding: 8px;
  font-size: 13px;
}

.chat-input button {
  background: #1e40af;
  color: #fff;
  border: none;
  padding: 8px;
}

/* smoother scroll */
.chat-body {
  scroll-behavior: smooth;
}

/* Smooth open/close */
#chat-box {
  width: 300px;
  height: 400px;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.3);
  display: flex;
  flex-direction: column;
  overflow: hidden;

  opacity: 0;
  transform: translateY(20px) scale(0.95);
  pointer-events: none;
  transition: all 0.25s ease;
}

#chat-box.chat-show {
  opacity: 1;
  transform: translateY(0) scale(1);
  pointer-events: auto;
}

/* message animation */
.bot-msg {
  background: #f1f5f9;
  padding: 8px;
  margin-bottom: 6px;
  border-radius: 8px;
  animation: fadeIn 0.25s ease;
}

@keyframes fadeIn {
  from {opacity: 0; transform: translateY(5px);}
  to {opacity: 1; transform: translateY(0);}
}

/* input improvements */
.chat-input input:focus {
  outline: none;
}
  </style>
</head>
<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8 col-lg-6">

       <!-- Top area on royal background -->
<div class="login-top text-center">
  <img src="ANHS.png" alt="ANHS Logo" style="max-width: 120px; margin-bottom: 10px;">
  <h3>ANHS Grading Portal</h3>
  <p class="small">Welcome Eagle!</p>
</div>

        <!-- White card -->
        <div class="card login-card mx-auto">
          <div class="card-body">
            <?php if ($error): ?>
              <div class="alert alert-danger"><?= h($error) ?></div>
            <?php endif; ?>

            <form method="post" action="auth.php" autocomplete="off">
              <input type="hidden" name="action" value="login">

              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>

              <div class="d-grid">
                <button type="submit" class="btn btn-login">Log In</button>
              </div>
            </form>

              <div class="alert alert-warning mt-3 text-center">
  ⚠️ Please note: For your privacy, do not screenshot, record, or share your grades.
</div>      
             <p class="mt-3 small text-muted text-center">
              © BSCS STUDENTS

            </p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- optional bootstrap JS (not required for styles) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <div id="faq-chatbot">
  <div id="chat-toggle">💬 FAQ</div>

  <div id="chat-box" class="chat-hidden">
    <div class="chat-header">
      ANHS Help Desk
      <span id="close-chat">✖</span>
    </div>

    <div class="chat-body" id="chat-body"></div>

    <div class="chat-input">
      <input type="text" id="user-input" placeholder="Ask a question...">
      <button onclick="sendMessage()">Send</button>
    </div>
  </div>
</div>

<script>
const chatBox = document.getElementById("chat-box");
const chatBody = document.getElementById("chat-body");
const input = document.getElementById("user-input");

let badCount = 0;

// ✅ keyword-based responses
const responses = {
  password: "Please approach your adviser or any staff in ANHS to request password change.",
  form: "Proceed to the School Clerk to inquire about Form 137.",
  email: "Please contact your adviser to verify your registered email.",
  grades: "If your grades are not showing, inform your subject teacher or adviser."
};

// ❌ inappropriate words filter
const badWords = ["bobo", "tanga", "gago", "fuck", "shit", "ulol"];

// 🧠 initialize chat
function initChat() {
  chatBody.innerHTML = `<div class="bot-msg">Hi! Ask your question 😊</div>`;
  badCount = 0;
}

// 🔘 toggle chatbot
document.getElementById("chat-toggle").onclick = () => {
  chatBox.classList.toggle("chat-show");

  if (chatBox.classList.contains("chat-show")) {
    initChat();
  }
};

// ❌ close chatbot
document.getElementById("close-chat").onclick = () => {
  chatBox.classList.remove("chat-show");
  chatBody.innerHTML = "";
};

// 🎯 send message
function sendMessage() {
  const text = input.value.trim().toLowerCase();
  if (!text) return;

  appendMessage("You", text);

  setTimeout(() => {
    processMessage(text);
  }, 300); // typing delay

  input.value = "";
}

// 🧠 process logic
function processMessage(text) {
  // ❌ check bad words
  for (let word of badWords) {
    if (text.includes(word)) {
      badCount++;

      if (badCount >= 2) {
        appendMessage("Bot", "Please do not ask inappropriate questions.");
      } else {
        appendMessage("Bot", "⚠️ Please keep your questions appropriate.");
      }
      return;
    }
  }

  // ✅ keyword matching
  for (let key in responses) {
    if (text.includes(key)) {
      appendMessage("Bot", responses[key]);
      return;
    }
  }

  // ❓ fallback (nonsense)
  appendMessage("Bot", "Sorry, I don't understand. Please clarify your question.");
}

// 💬 append message
function appendMessage(sender, text) {
  chatBody.innerHTML += `
    <div class="bot-msg">
      <strong>${sender}:</strong> ${text}
    </div>
  `;

  chatBody.scrollTop = chatBody.scrollHeight;
}

// ⌨️ enter key support
input.addEventListener("keypress", function(e) {
  if (e.key === "Enter") {
    sendMessage();
  }
});
</script>
</body>
</html>
