{{-- JavaScript Secrets Checker — URL-based scanner with server-side fetch --}}

<div x-data="jsSecretsChecker()" class="space-y-6">

    {{-- Intro --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-primary-700">Front-End Security Audit</p>
        <h2 class="mt-2 text-xl font-semibold text-gray-900">Scan any website for hard-coded secrets in JavaScript</h2>
        <p class="mt-2 text-sm leading-6 text-gray-600">
            Enter a URL and we'll fetch the page, extract every inline and external JavaScript asset, and check them against 30+ patterns for API keys, tokens, credentials, and sensitive strings.
        </p>

        <button
            type="button"
            class="mt-3 text-xs text-gray-500 underline hover:text-gray-700"
            @click="showLimitations = !showLimitations"
            x-text="showLimitations ? 'Hide limitations' : 'Show limitations'"
        ></button>
        <div x-show="showLimitations" x-cloak x-transition class="mt-3 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
            <ul class="list-disc pl-5 space-y-1">
                <li>Checks the <strong>first page load only</strong> — JS loaded dynamically or behind auth is not scanned.</li>
                <li>Up to <strong>40 external scripts</strong> are fetched per scan.</li>
                <li>Some sites may block automated requests — a missing result does not mean the asset is clean.</li>
                <li>This is a <strong>lightweight public check</strong>, not a full security assessment or penetration test.</li>
            </ul>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-3">
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-emerald-700">What you get</div>
                <p class="mt-1 text-sm text-emerald-900">A prioritised list of hard-coded secrets, leaked credentials, and internal endpoints found in the site's JavaScript.</p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-amber-700">Why it matters</div>
                <p class="mt-1 text-sm text-amber-900">Secrets in client-side JS are visible to anyone with a browser. Attackers actively scan for exposed keys and tokens.</p>
            </div>
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-blue-700">Who it's for</div>
                <p class="mt-1 text-sm text-blue-900">Developers, security engineers, pentesters, agencies, and founders checking production sites.</p>
            </div>
        </div>
    </div>

    {{-- Input --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 space-y-4">
        <div class="text-sm font-semibold text-gray-900">Website URL</div>
        <form @submit.prevent="runScan" class="flex flex-col gap-3 sm:flex-row sm:items-start">
            <div class="flex-1">
                <input
                    type="url"
                    class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    placeholder="https://example.com"
                    x-model="url"
                    :disabled="busy"
                    required
                >
                <p class="mt-1 text-xs text-gray-400">HTTPS URLs only. The page and its JavaScript assets are fetched server-side.</p>
            </div>
            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed shrink-0"
                :disabled="busy || !url.trim()"
            >
                <template x-if="busy">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </template>
                <span x-text="busy ? 'Scanning…' : 'Scan for Secrets'"></span>
            </button>
        </form>

        <div class="flex flex-wrap gap-2">
            <span class="text-xs text-gray-400">Try:</span>
            <button type="button" class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-1 text-xs text-gray-600 hover:bg-gray-100" @click="url = 'https://react.dev'; runScan()">react.dev</button>
            <button type="button" class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-1 text-xs text-gray-600 hover:bg-gray-100" @click="url = 'https://vuejs.org'; runScan()">vuejs.org</button>
            <button type="button" class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-1 text-xs text-gray-600 hover:bg-gray-100" @click="url = 'https://github.com'; runScan()">github.com</button>
        </div>
    </div>

    {{-- Error --}}
    <template x-if="lastError">
        <div class="rounded-2xl border border-red-200 bg-red-50 p-5">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 text-red-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <div>
                    <div class="text-sm font-medium text-red-800">Scan failed</div>
                    <p class="mt-1 text-sm text-red-700" x-text="lastError"></p>
                </div>
            </div>
        </div>
    </template>

    {{-- Loading --}}
    <template x-if="busy">
        <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center">
            <svg class="animate-spin h-8 w-8 text-primary-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <p class="mt-3 text-sm text-gray-600">Fetching page and scanning JavaScript assets…</p>
            <p class="mt-1 text-xs text-gray-400">This usually takes 5–20 seconds depending on the number of scripts.</p>
        </div>
    </template>

    {{-- Results --}}
    <template x-if="result && !busy">
        <div class="space-y-5">

            {{-- Summary --}}
            <div class="rounded-2xl border p-5" :class="result.total_findings === 0 ? 'border-emerald-200 bg-emerald-50' : 'border-red-200 bg-red-50'">
                <div class="flex items-start gap-3">
                    <div class="text-2xl" x-text="result.total_findings === 0 ? '\u2705' : '\u{1F6A8}'"></div>
                    <div class="flex-1">
                        <div class="text-base font-semibold" :class="result.total_findings === 0 ? 'text-emerald-900' : 'text-red-900'" x-text="result.total_findings === 0 ? 'No secrets detected — looking clean' : result.total_findings + ' finding' + (result.total_findings === 1 ? '' : 's') + ' detected'"></div>
                        <p class="mt-1 text-sm" :class="result.total_findings === 0 ? 'text-emerald-700' : 'text-red-700'" x-text="result.total_findings === 0 ? 'None of the JavaScript assets on this page appear to contain hard-coded secrets or sensitive strings.' : 'Potential secrets, credentials, or sensitive strings were found in the page\'s JavaScript assets.'"></p>

                        <div class="mt-3 flex flex-wrap gap-4 text-xs text-gray-500">
                            <span><strong x-text="result.scripts_found"></strong> external scripts</span>
                            <span><strong x-text="result.scripts_analysed"></strong> analysed</span>
                            <span><strong x-text="result.inline_scripts"></strong> inline scripts</span>
                        </div>

                        <div x-show="result.total_findings > 0" class="mt-3 flex flex-wrap gap-2 text-xs font-medium">
                            <span x-show="result.by_severity.critical > 0" class="px-2 py-0.5 rounded-full bg-red-600 text-white" x-text="result.by_severity.critical + ' critical'"></span>
                            <span x-show="result.by_severity.high > 0" class="px-2 py-0.5 rounded-full bg-orange-500 text-white" x-text="result.by_severity.high + ' high'"></span>
                            <span x-show="result.by_severity.medium > 0" class="px-2 py-0.5 rounded-full bg-amber-500 text-white" x-text="result.by_severity.medium + ' medium'"></span>
                            <span x-show="result.by_severity.low > 0" class="px-2 py-0.5 rounded-full bg-blue-500 text-white" x-text="result.by_severity.low + ' low'"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div x-show="result.total_findings > 0" class="flex flex-wrap gap-2">
                <button type="button" @click="filter = 'all'"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors"
                    :class="filter === 'all' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300'">
                    All <span x-text="'(' + result.total_findings + ')'"></span>
                </button>
                <template x-for="sev in ['critical','high','medium','low']" :key="sev">
                    <button type="button" @click="filter = sev" x-show="result.by_severity[sev] > 0"
                        class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors capitalize"
                        :class="filter === sev ? sevBtnActive(sev) : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300'"
                        x-text="sev + ' (' + result.by_severity[sev] + ')'">
                    </button>
                </template>
            </div>

            {{-- Findings --}}
            <div x-show="result.total_findings > 0" class="space-y-3">
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
                                <div class="font-mono text-xs bg-gray-50 rounded-lg px-3 py-2 text-gray-700 overflow-x-auto">
                                    <div class="text-[10px] text-gray-400 mb-1" x-text="f.source + ' — line ' + f.line"></div>
                                    <div class="whitespace-pre break-all" x-text="f.match"></div>
                                </div>
                                <div class="text-xs text-emerald-700 bg-emerald-50 rounded-lg px-3 py-2" x-text="'\uD83D\uDCA1 ' + f.recommendation"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Export --}}
            <div x-show="result.total_findings > 0" class="flex flex-wrap gap-2">
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

            {{-- Scanned URL --}}
            <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 p-4 text-center text-xs text-gray-400">
                Scanned <span x-text="result.final_url"></span> &middot;
                <span x-text="result.scripts_found + ' external + ' + result.inline_scripts + ' inline scripts'"></span> &middot;
                <span x-text="result.total_findings + ' finding' + (result.total_findings === 1 ? '' : 's')"></span>
            </div>
        </div>
    </template>
</div>

@push('scripts')
<script>
function jsSecretsChecker() {
    return {
        url: '',
        busy: false,
        lastError: null,
        result: null,
        filter: 'all',
        copied: false,
        showLimitations: false,

        async runScan() {
            const raw = this.url.trim();
            if (!raw) return;

            let scanUrl = raw;
            if (!/^https?:\/\//i.test(scanUrl)) {
                scanUrl = 'https://' + scanUrl;
            }

            try {
                new URL(scanUrl);
            } catch {
                this.lastError = 'Please enter a valid URL.';
                return;
            }

            if (!/^https:/i.test(scanUrl)) {
                this.lastError = 'Only HTTPS URLs are supported.';
                return;
            }

            this.url = scanUrl;
            this.busy = true;
            this.lastError = null;
            this.result = null;
            this.filter = 'all';
            this.copied = false;

            try {
                const response = await fetch('/tools/js-secrets-scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({ url: scanUrl }),
                });

                const data = await response.json();

                if (!response.ok) {
                    this.lastError = data.error || 'Something went wrong. Please try again.';
                    return;
                }

                this.result = data;
            } catch (e) {
                this.lastError = 'Network error — could not reach the scan endpoint. Please try again.';
            } finally {
                this.busy = false;
            }
        },

        filteredFindings() {
            if (!this.result) return [];
            if (this.filter === 'all') return this.result.findings;
            return this.result.findings.filter(f => f.severity === this.filter);
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

        copyReport() {
            if (!this.result) return;
            const text = this.buildTextReport();
            navigator.clipboard.writeText(text).then(() => {
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 2000);
            });
        },

        downloadReport() {
            if (!this.result) return;
            const blob = new Blob([JSON.stringify(this.result, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'js-secrets-report.json';
            a.click();
            URL.revokeObjectURL(url);
        },

        buildTextReport() {
            const r = this.result;
            let out = `JavaScript Secrets Checker Report\n`;
            out += `${'='.repeat(50)}\n`;
            out += `URL: ${r.url}\n`;
            out += `Final URL: ${r.final_url}\n`;
            out += `Scripts: ${r.scripts_found} external, ${r.inline_scripts} inline\n`;
            out += `Findings: ${r.total_findings}\n\n`;

            for (const f of r.findings) {
                out += `[${f.severity.toUpperCase()}] ${f.title}\n`;
                out += `  Source: ${f.source} — line ${f.line}\n`;
                out += `  Match: ${f.match}\n`;
                out += `  ${f.description}\n`;
                out += `  Recommendation: ${f.recommendation}\n\n`;
            }

            out += `${'='.repeat(50)}\n`;
            out += `Scanned with https://urlcv.com/tools/js-secrets-checker\n`;
            return out;
        },
    };
}
</script>
@endpush
