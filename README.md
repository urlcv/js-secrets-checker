# JavaScript Secrets Checker

> Part of the [URLCV](https://urlcv.com) free tools suite — built for developers and security teams.

**Live tool:** [urlcv.com/tools/js-secrets-checker](https://urlcv.com/tools/js-secrets-checker)

---

## What it does

Paste any front-end JavaScript — a webpack bundle, a single file, or a snippet — and get an instant audit of every hard-coded secret, credential, or sensitive-looking string hiding in it.

### Detects

| Category | Examples |
|----------|---------|
| **Cloud keys** | AWS access keys (`AKIA…`), Google API keys (`AIza…`), Azure subscription keys |
| **Payment tokens** | Stripe secret/publishable keys (`sk_live_…`, `pk_live_…`) |
| **SaaS tokens** | GitHub PATs, GitLab tokens, Slack bot tokens, SendGrid keys, Twilio keys, npm tokens |
| **Auth credentials** | JWTs, Bearer tokens, Basic Auth headers, passwords in URLs |
| **Private keys** | PEM-encoded RSA, EC, DSA, OPENSSH, PGP private key blocks |
| **Database URIs** | MongoDB, PostgreSQL, MySQL, Redis, AMQP connection strings |
| **Generic secrets** | Variables named `password`, `secret`, `api_key`, `token` assigned to string literals |
| **Internal endpoints** | Localhost, private IP ranges (10.x, 172.16–31.x, 192.168.x), staging/dev subdomains |
| **Encoded strings** | Suspiciously long Base64 strings that may be encoded credentials |

### Features

- **30+ regex rules** with severity levels (critical, high, medium, low)
- **Line-number references** for each finding
- **Actionable recommendations** for every detection
- **Filter by severity** to triage quickly
- **Copy report** as plain text or **download as JSON**
- **Sample code** loader to see the tool in action

### Privacy

Everything runs in your browser. Your code is never sent to a server, never stored, never logged.

---

## Technical details

- **Type:** Frontend-only (Alpine.js) — no server round-trip, no data stored
- **Framework integration:** Laravel package with Blade view
- **Namespace:** `URLCV\JsSecretsChecker`
- **Service provider:** `URLCV\JsSecretsChecker\Laravel\JsSecretsCheckerServiceProvider`

---

## Installation (via the main URLCV app)

```json
"repositories": [
    { "type": "vcs", "url": "https://github.com/urlcv/js-secrets-checker.git" }
],
"require": {
    "urlcv/js-secrets-checker": "dev-main"
}
```

```bash
composer update urlcv/js-secrets-checker
php artisan tools:sync
```

---

## Part of URLCV

[URLCV](https://urlcv.com) automates CV parsing, candidate scoring, and shortlist generation — so recruiters can place more candidates, faster.

[Start a free trial →](https://urlcv.com/register)
