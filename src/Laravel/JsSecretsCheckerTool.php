<?php

declare(strict_types=1);

namespace URLCV\JsSecretsChecker\Laravel;

use App\Tools\Contracts\ToolInterface;

class JsSecretsCheckerTool implements ToolInterface
{
    public function slug(): string
    {
        return 'js-secrets-checker';
    }

    public function name(): string
    {
        return 'JavaScript Secrets Checker';
    }

    public function summary(): string
    {
        return 'Paste front-end JavaScript and instantly find hard-coded API keys, tokens, endpoints, and sensitive strings before attackers do.';
    }

    public function descriptionMd(): ?string
    {
        return <<<'MD'
## JavaScript Secrets Checker

Paste any front-end JavaScript — a bundle, a single file, or a snippet — and get an instant audit of every hard-coded secret, credential, or sensitive-looking string hiding in it.

### What it detects

- **Cloud provider keys** — AWS access keys (`AKIA…`), Google API keys (`AIza…`), Azure keys
- **Payment & SaaS tokens** — Stripe (`sk_live_…`, `pk_live_…`), Twilio, SendGrid, Slack, GitHub, GitLab, npm tokens
- **Authentication secrets** — JWTs (`eyJ…`), Bearer tokens, OAuth client secrets, basic-auth credentials in URLs
- **Private keys** — RSA, EC, PGP, SSH private key blocks embedded in strings
- **Database & infrastructure** — connection strings (mongodb://, postgres://, mysql://), Redis URLs, SMTP credentials
- **Generic patterns** — variables named `password`, `secret`, `api_key`, `token`, `auth` assigned to string literals
- **Internal endpoints** — localhost URLs, internal IP ranges (10.x, 172.16–31.x, 192.168.x), staging/dev subdomains
- **Encoded secrets** — suspiciously long Base64 strings that look like encoded credentials

### Severity levels

Each finding is tagged **critical**, **high**, **medium**, or **low** so you can triage quickly.

### Who it's for

- Front-end developers reviewing bundles before deploy
- Security engineers auditing third-party scripts
- DevOps teams checking CI artifacts for leaked credentials
- Bug bounty hunters looking for low-hanging fruit
- Anyone who's ever thought "surely nobody hard-coded a key in the client bundle"

### Privacy

Everything runs in your browser. Your code is never sent to a server, never stored, never logged.
MD;
    }

    public function mode(): string
    {
        return 'frontend';
    }

    public function isAsync(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function categories(): array
    {
        return ['security'];
    }

    public function tags(): array
    {
        return ['javascript', 'secrets', 'api-keys', 'tokens', 'security', 'scanner'];
    }

    public function inputSchema(): array
    {
        return [];
    }

    public function run(array $input): array
    {
        return [];
    }

    public function rateLimitPerMinute(): int
    {
        return 0;
    }

    public function cacheTtlSeconds(): int
    {
        return 0;
    }

    public function sortWeight(): int
    {
        return 95;
    }

    public function frontendView(): ?string
    {
        return 'js-secrets-checker::js-secrets-checker';
    }
}
