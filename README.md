<div align="center">

# ⚙️ GhostWire

**Ephemeral, End-to-End Isolated Session Web Messaging**

*A zero-trace, lightweight platform built for untraceable, privacy-first communication.*

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=flat-square&logo=javascript&logoColor=black)](https://developer.mozilla.org)
[![License](https://img.shields.io/badge/License-MIT-blue.style=flat-square)](#license)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat-square)](http://makeapullrequest.com)

</div>

---

## 🔒 Overview

**GhostWire** is a self-hosted, lightweight web-based communication tool designed for absolute privacy and ephemeral data exchange. Engineered with strict **End-to-End (E2E) Session Isolation**, GhostWire ensures that no persistent identifiers, access logs, or sensitive message history remain after a session ends.

Whether exchanging self-destructing text messages or encrypted file payloads, GhostWire operates on a zero-trust model between client and server.

---

## ✨ Key Features

- **E2E Session Isolation:** Every chat session operates in an isolated environment with cryptographic key pairing localized to client memory.
- **Zero-Trace Architecture:** Server acts solely as a stateless broker. No message history is stored permanently.
- **Encrypted File Transfer:** Securely upload and share temporary payloads handled via ephemeral storage routines.
- **Client-Side Storage Management:** Dynamic session key retention and local payload handling using custom Web Storage workers.
- **Minimalist & Fast:** Built using native PHP and modern ES6 JavaScript—no heavy frameworks, bloat, or external dependencies.

---

## 📁 Project Architecture

```
ghostwire/
├── assets/
│   ├── css/
│   │   └── main.css        # Responsive, dark-mode focused UI styling
│   └── js/
│       ├── app.js          # Main application logic & session controller
│       └── storage.js      # Client-side encrypted state & storage handler
├── config/
│   ├── app.php             # Core application & environment settings
│   └── security.php        # Security parameters, headers & crypto defaults
├── includes/
│   ├── functions.php       # Helper functions & utility methods
│   ├── storage.php       # Ephemeral storage engine interface
│   └── upload.php        # Secure temporary file upload handler
├── api.php                 # Asynchronous AJAX/Fetch endpoint broker
├── chat.php                # Active chat session interface
├── file.php                # Ephemeral file retrieval endpoint
└── index.php               # Landing page & session initializer
```

---

## 🚀 Quick Start

### Prerequisites

- **Web Server:** Nginx, Apache, or Caddy
- **PHP:** `8.1` or higher (with `mbstring`, `json`, and `openssl` extensions enabled)

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/stackvoided/ghostwire.git
   cd ghostwire
   ```

2. **Configure Environment:**
   Adjust security standards and application constants inside the `config/` directory:
   ```bash
   nano config/app.php
   nano config/security.php
   ```

3. **Set File Permissions:**
   Ensure your web server has write permissions for temporary storage directories (if used for file uploads):
   ```bash
   chmod -R 755 includes/
   ```

4. **Serve Application:**
   Using PHP's built-in server for local development:
   ```bash
   php -S localhost:8000
   ```
   Navigate to `http://localhost:8000` in your browser.

---

## 🛡️ Security Model

1. **Memory-Only State:** Session parameters reside strictly in browser memory (`assets/js/storage.js`) and self-destruct upon tab/browser closure.
2. **Strict File Handling:** File uploads managed via `includes/upload.php` are sanitized, assigned randomized cryptographic tokens, and scheduled for immediate purge after consumption.
3. **Hardened Headers:** Configured through `config/security.php` to prevent CSP bypasses, MIME-sniffing, and clickjacking attacks.

---

## 🤝 Contributing

Contributions are welcome! Please feel free to open an issue or submit a Pull Request.

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

Distributed under the MIT License. See `LICENSE` for more information.
