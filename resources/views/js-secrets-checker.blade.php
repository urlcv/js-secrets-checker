{{-- JavaScript Secrets Checker — frontend-only scanner --}}
@php
    $toolUrl = url('/tools/js-secrets-checker');
@endphp
<style>[x-cloak]{display:none!important}</style>
<div
    class="space-y-5"
    data-tool-url="{{ $toolUrl }}"
    x-data="jsSecretsChecker()"
>
    {{-- Tip --}}
    <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
        <p class="font-semibold">How it works</p>
        <p class="mt-1">Paste any JavaScript — a webpack bundle, a single file, or a snippet — and the scanner checks 30+ regex patterns for hard-coded API keys, tokens, endpoints, and sensitive strings. Everything runs in your browser; your code never leaves the page.</p>
    </div>

    {{-- Input --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <label for="js-input" class="block text-sm font-medium text-gray-700">JavaScript code</label>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="loadSample()"
                    class="text-xs text-primary-600 hover:text-primary-700 font-medium transition-colors"
                >Load example</button>
                <button
                    type="button"
                    @click="clear()"
                    x-show="code.length > 0"
                    class="text-xs text-gray-400 hover:text-gray-600 font-medium transition-colors"
                >Clear</button>
            </div>
        </div>
        <textarea
            id="js-input"
            x-model="code"
            @input.debounce.300ms="scan()"
            rows="14"
            class="w-full rounded-xl border border-gray-300 bg-white font-mono text-sm p-4 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 placeholder-gray-400 resize-y"
            placeholder="// Paste your JavaScript here…
const API_KEY = 'sk_live_abc123…';"
            spellcheck="false"
        ></textarea>
        <div class="flex items-center justify-between text-xs text-gray-400">
            <span x-text="code.length ? code.split('\\n').length + ' lines · ' + code.length.toLocaleString() + ' chars' : 'Waiting for input…'"></span>
            <span x-show="scanTime > 0" x-text="'Scanned in ' + scanTime + ' ms'"></span>
        </div>
    </div>

    {{-- Results --}}
    <div x-show="scanned" x-cloak class="space-y-5">

        {{-- Summary bar --}}
        <div class="rounded-xl border p-4 flex flex-wrap items-center gap-4 text-sm"
            :class="findings.length === 0
                ? 'border-emerald-200 bg-emerald-50 text-emerald-900'
                : 'border-red-200 bg-red-50 text-red-900'"
        >
            <template x-if="findings.length === 0">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-medium">No secrets detected — looking clean.</span>
                </div>
            </template>
            <template x-if="findings.length > 0">
                <div class="flex flex-wrap items-center gap-4 w-full">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                        <span class="font-medium" x-text="findings.length + ' finding' + (findings.length === 1 ? '' : 's') + ' detected'"></span>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs font-medium">
                        <span x-show="countBySeverity('critical') > 0" class="px-2 py-0.5 rounded-full bg-red-600 text-white" x-text="countBySeverity('critical') + ' critical'"></span>
                        <span x-show="countBySeverity('high') > 0" class="px-2 py-0.5 rounded-full bg-orange-500 text-white" x-text="countBySeverity('high') + ' high'"></span>
                        <span x-show="countBySeverity('medium') > 0" class="px-2 py-0.5 rounded-full bg-amber-500 text-white" x-text="countBySeverity('medium') + ' medium'"></span>
                        <span x-show="countBySeverity('low') > 0" class="px-2 py-0.5 rounded-full bg-blue-500 text-white" x-text="countBySeverity('low') + ' low'"></span>
                    </div>
                </div>
            </template>
        </div>

        {{-- Filters --}}
        <div x-show="findings.length > 0" class="flex flex-wrap gap-2">
            <button type="button" @click="filter = 'all'"
                class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors"
                :class="filter === 'all' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300'">
                All <span x-text="'(' + findings.length + ')'"></span>
            </button>
            <template x-for="sev in ['critical','high','medium','low']" :key="sev">
                <button type="button" @click="filter = sev" x-show="countBySeverity(sev) > 0"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors capitalize"
                    :class="filter === sev ? sevBtnActive(sev) : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300'"
                    x-text="sev + ' (' + countBySeverity(sev) + ')'">
                </button>
            </template>
        </div>

        {{-- Findings list --}}
        <div x-show="findings.length > 0" class="space-y-3">
            <template x-for="(f, idx) in filteredFindings()" :key="idx">
                <div class="rounded-xl border border-gray-200 bg-white overflow-hidden">
                    <div class="flex items-start gap-3 p-4">
                        <span class="mt-0.5 shrink-0 w-2 h-2 rounded-full"
                            :class="{
                                'bg-red-500': f.severity === 'critical',
                                'bg-orange-500': f.severity === 'high',
                                'bg-amber-500': f.severity === 'medium',
                                'bg-blue-400': f.severity === 'low'
                            }"></span>
                        <div class="flex-1 min-w-0 space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-semibold text-gray-900" x-text="f.title"></span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold uppercase tracking-wider"
                                    :class="{
                                        'bg-red-100 text-red-700': f.severity === 'critical',
                                        'bg-orange-100 text-orange-700': f.severity === 'high',
                                        'bg-amber-100 text-amber-700': f.severity === 'medium',
                                        'bg-blue-100 text-blue-700': f.severity === 'low'
                                    }"
                                    x-text="f.severity"></span>
                            </div>
                            <p class="text-xs text-gray-500" x-text="f.description"></p>
                            <div class="font-mono text-xs bg-gray-50 rounded-lg px-3 py-2 text-gray-700 overflow-x-auto whitespace-pre" x-text="'Line ' + f.line + ': ' + f.match"></div>
                            <p class="text-xs text-emerald-700 bg-emerald-50 rounded-lg px-3 py-2" x-text="'💡 ' + f.recommendation"></p>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Export --}}
        <div x-show="findings.length > 0" class="flex flex-wrap gap-2">
            <button type="button" @click="copyReport()"
                class="px-4 py-2.5 rounded-xl text-sm font-medium transition-colors"
                :class="copied ? 'bg-emerald-50 border border-emerald-300 text-emerald-700' : 'bg-primary-600 text-white hover:bg-primary-700'">
                <span x-show="!copied">Copy report</span>
                <span x-show="copied" x-cloak>Copied!</span>
            </button>
            <button type="button" @click="downloadReport()"
                class="px-4 py-2.5 rounded-xl text-sm font-medium border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition-colors">
                Download JSON
            </button>
        </div>
    </div>

    {{-- Empty state --}}
    <div x-show="!scanned" x-cloak class="rounded-xl border border-dashed border-gray-200 bg-gray-50 p-8 text-center text-sm text-gray-500">
        <svg class="mx-auto h-10 w-10 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"/></svg>
        Paste JavaScript above to scan for hard-coded secrets.
    </div>
</div>

@push('scripts')
<script>
function jsSecretsChecker() {
    const RULES = [
        { id: 'aws-access-key', title: 'AWS Access Key ID', severity: 'critical', pattern: /(?:^|[^A-Za-z0-9/+=])(?:AKIA[0-9A-Z]{16})(?:[^A-Za-z0-9/+=]|$)/g, description: 'AWS IAM access key — grants API access to your AWS account.', recommendation: 'Move to environment variables or a secrets manager (AWS Secrets Manager, SSM Parameter Store). Rotate this key immediately.' },
        { id: 'aws-secret-key', title: 'AWS Secret Access Key', severity: 'critical', pattern: /(?:aws_secret_access_key|aws_secret|secret_key)\s*[:=]\s*['"`]([A-Za-z0-9/+=]{40})['"`]/gi, description: 'AWS secret key — paired with the access key for full API access.', recommendation: 'Remove from source, rotate the key pair, and use IAM roles or env vars instead.' },
        { id: 'stripe-secret', title: 'Stripe Secret Key', severity: 'critical', pattern: /sk_live_[0-9a-zA-Z]{24,}/g, description: 'Stripe live secret key — can process charges and access account data.', recommendation: 'Never expose secret keys in client-side code. Use server-side only and load from env vars.' },
        { id: 'stripe-publishable', title: 'Stripe Publishable Key', severity: 'low', pattern: /pk_live_[0-9a-zA-Z]{24,}/g, description: 'Stripe publishable key — safe for client-side but confirms you\'re using the live environment.', recommendation: 'Publishable keys are designed for the frontend, but verify you\'re not bundling the secret key alongside it.' },
        { id: 'google-api', title: 'Google API Key', severity: 'high', pattern: /AIza[0-9A-Za-z_-]{35}/g, description: 'Google API key — can access Maps, Firebase, YouTube, and other Google services.', recommendation: 'Restrict the key to specific APIs and referrers in the Google Cloud console. Consider using server-side proxy calls.' },
        { id: 'firebase-config', title: 'Firebase Configuration', severity: 'medium', pattern: /(?:apiKey|authDomain|databaseURL|storageBucket|messagingSenderId|appId)\s*:\s*['"`][^'"`]{8,}['"`]/g, description: 'Firebase config values exposed in client code.', recommendation: 'Firebase config is semi-public by design, but ensure Firestore/RTDB security rules are locked down. Never embed service account keys.' },
        { id: 'github-token', title: 'GitHub Token', severity: 'critical', pattern: /(?:ghp|gho|ghu|ghs|ghr)_[A-Za-z0-9_]{36,}/g, description: 'GitHub personal access token or OAuth token — grants repository and API access.', recommendation: 'Revoke this token immediately on GitHub and use server-side authentication instead.' },
        { id: 'github-classic', title: 'GitHub Classic Token', severity: 'critical', pattern: /github_pat_[A-Za-z0-9_]{22,}/g, description: 'GitHub fine-grained personal access token.', recommendation: 'Revoke and regenerate the token. Store in a secrets manager, never in client code.' },
        { id: 'gitlab-token', title: 'GitLab Token', severity: 'critical', pattern: /glpat-[A-Za-z0-9_-]{20,}/g, description: 'GitLab personal access token.', recommendation: 'Revoke this token in GitLab settings and move to env vars or CI/CD variables.' },
        { id: 'slack-token', title: 'Slack Token', severity: 'critical', pattern: /xox[bpors]-[0-9]{10,}-[0-9a-zA-Z-]{10,}/g, description: 'Slack bot, user, or app token — can read/send messages in your workspace.', recommendation: 'Revoke in Slack app settings. Use server-side integrations only.' },
        { id: 'slack-webhook', title: 'Slack Webhook URL', severity: 'high', pattern: /https:\/\/hooks\.slack\.com\/services\/T[A-Z0-9]{8,}\/B[A-Z0-9]{8,}\/[A-Za-z0-9]{20,}/g, description: 'Slack incoming webhook — anyone with this URL can post to your channel.', recommendation: 'Move webhook URLs to server-side configuration. Regenerate if exposed.' },
        { id: 'twilio-key', title: 'Twilio API Key', severity: 'high', pattern: /SK[0-9a-fA-F]{32}/g, description: 'Twilio API key — can send SMS and make calls.', recommendation: 'Move to server-side and use env vars. Revoke and regenerate if exposed.' },
        { id: 'sendgrid-key', title: 'SendGrid API Key', severity: 'critical', pattern: /SG\.[A-Za-z0-9_-]{22}\.[A-Za-z0-9_-]{43}/g, description: 'SendGrid API key — can send email on your behalf.', recommendation: 'Revoke in SendGrid and regenerate. Email sending must be server-side only.' },
        { id: 'mailgun-key', title: 'Mailgun API Key', severity: 'critical', pattern: /key-[0-9a-zA-Z]{32}/g, description: 'Mailgun API key.', recommendation: 'Revoke and regenerate. Move email operations to server-side code.' },
        { id: 'npm-token', title: 'npm Token', severity: 'critical', pattern: /npm_[A-Za-z0-9]{36,}/g, description: 'npm access token — can publish packages under your account.', recommendation: 'Revoke the token on npmjs.com and regenerate. Never embed in client bundles.' },
        { id: 'heroku-key', title: 'Heroku API Key', severity: 'high', pattern: /[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}/g, description: 'Possible Heroku API key or other UUID-format token.', recommendation: 'If this is an API key, move it to environment variables. UUIDs can also be benign — verify context.', guardFn: (match, line) => /(?:heroku|api[_-]?key|secret|token|auth)/i.test(line) },
        { id: 'jwt-token', title: 'JSON Web Token (JWT)', severity: 'high', pattern: /eyJ[A-Za-z0-9_-]{10,}\.eyJ[A-Za-z0-9_-]{10,}\.[A-Za-z0-9_-]{10,}/g, description: 'A JWT — may contain user identity, roles, or session data. The payload is only base64-encoded, not encrypted.', recommendation: 'Never hard-code JWTs. They should come from authentication flows at runtime.' },
        { id: 'bearer-token', title: 'Bearer Token', severity: 'high', pattern: /['"`]Bearer\s+[A-Za-z0-9_.+/=-]{20,}['"`]/gi, description: 'Hard-coded Bearer auth header — likely an API credential.', recommendation: 'Tokens must be fetched at runtime from a secure auth flow, never baked into source.' },
        { id: 'basic-auth-header', title: 'Basic Auth Header', severity: 'critical', pattern: /['"`]Basic\s+[A-Za-z0-9+/=]{10,}['"`]/gi, description: 'Base64-encoded username:password in an Authorization header.', recommendation: 'Remove immediately. Use secure auth flows and never embed credentials in client code.' },
        { id: 'basic-auth-url', title: 'Credentials in URL', severity: 'critical', pattern: /https?:\/\/[^:\/\s]+:[^@\/\s]+@[^\s'"`,)}{]+/g, description: 'Username and password embedded in a URL (e.g. http://user:pass@host).', recommendation: 'Remove credentials from URLs. Use environment variables and server-side proxy calls.' },
        { id: 'private-key', title: 'Private Key Block', severity: 'critical', pattern: /-----BEGIN\s+(RSA |EC |DSA |OPENSSH |PGP )?PRIVATE KEY( BLOCK)?-----/g, description: 'A PEM-encoded private key — the most sensitive credential possible.', recommendation: 'Remove immediately. Private keys must never appear in client-side code. Rotate the key pair.' },
        { id: 'password-assign', title: 'Hard-coded Password', severity: 'high', pattern: /(?:password|passwd|pwd)\s*[:=]\s*['"`](?![\s'"`])[^'"`]{4,}['"`]/gi, description: 'A variable named "password" assigned to a string literal.', recommendation: 'Remove the hard-coded value. Use env vars, a secrets manager, or prompt users at runtime.' },
        { id: 'secret-assign', title: 'Hard-coded Secret', severity: 'high', pattern: /(?:secret|client_secret|app_secret|api_secret)\s*[:=]\s*['"`](?![\s'"`])[^'"`]{4,}['"`]/gi, description: 'A variable named "secret" assigned to a string literal.', recommendation: 'Move to server-side environment variables or a secrets manager.' },
        { id: 'api-key-assign', title: 'Hard-coded API Key', severity: 'high', pattern: /(?:api[_-]?key|apikey|access[_-]?key)\s*[:=]\s*['"`](?![\s'"`])[^'"`]{8,}['"`]/gi, description: 'A variable referencing an API key assigned to a string literal.', recommendation: 'Load API keys from environment configuration, not source code.' },
        { id: 'token-assign', title: 'Hard-coded Token', severity: 'medium', pattern: /(?:auth[_-]?token|access[_-]?token|refresh[_-]?token)\s*[:=]\s*['"`](?![\s'"`])[^'"`]{8,}['"`]/gi, description: 'A token variable assigned to a string literal.', recommendation: 'Tokens should be retrieved from authentication flows at runtime.' },
        { id: 'connection-string', title: 'Database Connection String', severity: 'critical', pattern: /(?:mongodb(?:\+srv)?|postgres(?:ql)?|mysql|mssql|redis|amqp|amqps):\/\/[^\s'"`}{,)]{10,}/gi, description: 'A database or message-broker connection string — may include credentials.', recommendation: 'Database URIs belong in server-side environment variables only, never in the frontend.' },
        { id: 'internal-url', title: 'Internal / Localhost URL', severity: 'medium', pattern: /https?:\/\/(?:localhost|127\.0\.0\.1|0\.0\.0\.0|10\.\d{1,3}\.\d{1,3}\.\d{1,3}|172\.(?:1[6-9]|2\d|3[01])\.\d{1,3}\.\d{1,3}|192\.168\.\d{1,3}\.\d{1,3})(?::\d+)?[^\s'"`}{,)]*/gi, description: 'An internal or localhost URL — may expose development endpoints, ports, or API paths.', recommendation: 'Replace with environment-specific configuration so internal URLs don\'t ship to production.' },
        { id: 'staging-url', title: 'Staging / Dev URL', severity: 'medium', pattern: /https?:\/\/(?:[a-z0-9-]+\.)*(?:staging|stage|dev|development|test|sandbox|internal|local)\.[a-z0-9.-]+/gi, description: 'A URL pointing to a staging, dev, or test environment.', recommendation: 'Use environment variables for base URLs so staging endpoints don\'t leak to production bundles.' },
        { id: 'azure-key', title: 'Azure Subscription Key', severity: 'high', pattern: /(?:subscription[_-]?key|ocp-apim-subscription-key)\s*[:=]\s*['"`][0-9a-f]{32}['"`]/gi, description: 'Azure API Management subscription key.', recommendation: 'Move to server-side proxy. Rotate the key in the Azure portal.' },
        { id: 'openai-key', title: 'OpenAI API Key', severity: 'critical', pattern: /sk-[A-Za-z0-9]{20,}/g, description: 'OpenAI API key — can incur usage charges and access your models.', recommendation: 'Revoke and regenerate at platform.openai.com. API calls must go through your server.' },
        { id: 'long-base64', title: 'Suspicious Base64 String', severity: 'low', pattern: /['"`](?:[A-Za-z0-9+/]{4}){12,}(?:={0,2})['"`]/g, description: 'A long Base64-encoded string that could be an encoded credential, key, or certificate.', recommendation: 'Decode and inspect the value. If it\'s a credential, remove it from source.' },
    ];

    const SAMPLE = `// Example: front-end config file with leaked secrets
const config = {
    // AWS credentials (DO NOT commit!)
    awsAccessKeyId: "AKIA` + `IOSFODNN7EXAMPLE",
    awsSecretKey: "wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY",

    // Google Maps
    googleMapsKey: "AIzaSyB-example_1234567890abcdefghijk",

    // Internal API
    apiEndpoint: "http://192.168.1.50:3000/api/v2",
    stagingApi: "https://api.staging.example.com/v1",

    // Auth
    api_key: "super-secret-api-key-value-12345678",
    password: "hunter2-production-db-pass",

    // JWT from dev testing
    devToken: "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIn0.Gfx6VO9tcxwk6xqx9yYzSfebfeakZp5JYIgP_edcw_A",

    // Database
    dbUrl: "mongodb+srv://admin:password123@cluster0.mongodb.net/prod",

    // GitHub PAT
    githubToken: "ghp_ABCDEFGHIJKLMNO` + `PQRSTUVWXYZabcdefgh",

    // SendGrid
    emailApiKey: "SG.abcdefghijklmnop` + `qrstuv.wxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890ab",

    // Basic auth in URL
    legacyApi: "https://admin:s3cretP4ss@internal.example.com/api",

    // Private key left in source
    signingKey: \`-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA0Z3VS5JJcds3xfn/yGaF...EXAMPLE
-----END RSA PRIVATE KEY-----\`,
};`;

    return {
        code: '',
        findings: [],
        scanned: false,
        scanTime: 0,
        filter: 'all',
        copied: false,

        scan() {
            if (!this.code.trim()) {
                this.findings = [];
                this.scanned = false;
                return;
            }

            const t0 = performance.now();
            const lines = this.code.split('\n');
            const results = [];
            const seen = new Set();

            for (const rule of RULES) {
                for (let i = 0; i < lines.length; i++) {
                    const line = lines[i];
                    rule.pattern.lastIndex = 0;
                    let m;
                    while ((m = rule.pattern.exec(line)) !== null) {
                        if (rule.guardFn && !rule.guardFn(m[0], line)) continue;

                        const matchText = m[0].trim();
                        const dedupKey = rule.id + ':' + (i + 1) + ':' + matchText;
                        if (seen.has(dedupKey)) continue;
                        seen.add(dedupKey);

                        const displayMatch = matchText.length > 120
                            ? matchText.substring(0, 60) + '…' + matchText.substring(matchText.length - 40)
                            : matchText;

                        results.push({
                            title: rule.title,
                            severity: rule.severity,
                            description: rule.description,
                            recommendation: rule.recommendation,
                            line: i + 1,
                            match: displayMatch,
                            ruleId: rule.id,
                        });
                    }
                }
            }

            results.sort((a, b) => {
                const order = { critical: 0, high: 1, medium: 2, low: 3 };
                return (order[a.severity] ?? 4) - (order[b.severity] ?? 4) || a.line - b.line;
            });

            this.findings = results;
            this.scanned = true;
            this.scanTime = Math.round(performance.now() - t0);
        },

        countBySeverity(sev) {
            return this.findings.filter(f => f.severity === sev).length;
        },

        filteredFindings() {
            if (this.filter === 'all') return this.findings;
            return this.findings.filter(f => f.severity === this.filter);
        },

        sevBtnActive(sev) {
            const map = {
                critical: 'bg-red-600 text-white border-red-600',
                high: 'bg-orange-500 text-white border-orange-500',
                medium: 'bg-amber-500 text-white border-amber-500',
                low: 'bg-blue-500 text-white border-blue-500',
            };
            return map[sev] || '';
        },

        loadSample() {
            this.code = SAMPLE;
            this.scan();
        },

        clear() {
            this.code = '';
            this.findings = [];
            this.scanned = false;
            this.filter = 'all';
        },

        copyReport() {
            const text = this.buildTextReport();
            navigator.clipboard.writeText(text).then(() => {
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 2000);
            });
        },

        downloadReport() {
            const report = {
                tool: 'JavaScript Secrets Checker',
                url: 'https://urlcv.com/tools/js-secrets-checker',
                scannedAt: new Date().toISOString(),
                totalFindings: this.findings.length,
                bySeverity: {
                    critical: this.countBySeverity('critical'),
                    high: this.countBySeverity('high'),
                    medium: this.countBySeverity('medium'),
                    low: this.countBySeverity('low'),
                },
                findings: this.findings,
            };
            const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'js-secrets-report.json';
            a.click();
            URL.revokeObjectURL(url);
        },

        buildTextReport() {
            let out = 'JavaScript Secrets Checker Report\n';
            out += '=================================\n';
            out += 'Generated: ' + new Date().toISOString() + '\n';
            out += 'Findings: ' + this.findings.length + '\n\n';
            for (const f of this.findings) {
                out += `[${f.severity.toUpperCase()}] ${f.title}\n`;
                out += `  Line ${f.line}: ${f.match}\n`;
                out += `  ${f.description}\n`;
                out += `  Recommendation: ${f.recommendation}\n\n`;
            }
            out += '---\nScanned with https://urlcv.com/tools/js-secrets-checker\n';
            return out;
        },
    };
}
</script>
@endpush
