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
        return 'Enter a URL to scan its front-end JavaScript for hard-coded API keys, tokens, endpoints, and sensitive strings before attackers find them.';
    }

    public function descriptionMd(): ?string
    {
        return <<<'MD'
## JavaScript Secrets Checker

Enter any public URL and we'll fetch the page, extract every inline and external JavaScript asset, and scan them against 30+ patterns for hard-coded secrets.

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

- Front-end developers checking production bundles before or after deploy
- Security engineers auditing third-party scripts
- DevOps teams checking CI artefacts for leaked credentials
- Bug bounty hunters looking for low-hanging fruit
- Anyone who's ever thought "surely nobody hard-coded a key in the client bundle"
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
        return ['javascript', 'secrets', 'api-keys', 'tokens', 'security', 'scanner', 'devtools'];
    }

    public function inputSchema(): array
    {
        return [
            'url' => [
                'type'        => 'string',
                'label'       => 'Website URL',
                'placeholder' => 'https://example.com',
                'required'    => true,
                'max_length'  => 2048,
                'help'        => 'Enter the full URL of the page to scan.',
            ],
        ];
    }

    public function run(array $input): array
    {
        return [];
    }

    public function rateLimitPerMinute(): int
    {
        return 10;
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
