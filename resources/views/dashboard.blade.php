<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="font-bold text-xl text-gray-900 dark:text-white leading-tight">
                    {{ __('Dashboard') }}
                </h2>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                    Welcome back, {{ auth()->user()->name }} • {{ now()->format('d M Y, h:i A') }} ({{ config('app.timezone') }})
                </p>
            </div>
            <div class="inline-flex items-center gap-2 self-start sm:self-auto rounded-xl bg-emerald-500/10 border border-emerald-500/20 px-3 py-1.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Sanctum API & Dual-Auth Ready
            </div>
        </div>
    </x-slot>

    <div class="py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Summary Metrics Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Users</div>
                <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ \App\Models\User::count() }}</div>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Email & Mobile login enabled</div>
            </div>

            <div class="rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-gray-400">Environment</div>
                <div class="mt-2 text-2xl font-bold text-indigo-600 dark:text-indigo-400">PHP {{ PHP_VERSION }}</div>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Laravel v{{ app()->version() }}</div>
            </div>

            <div class="rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-gray-400">Database & Mailer</div>
                <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ strtoupper(config('database.default')) }}</div>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">DB: {{ config('database.connections.mysql.database') }} • Mail: {{ config('mail.default') }}</div>
            </div>

            <div class="rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-wider text-gray-400">Timezone</div>
                <div class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ config('app.timezone') }}</div>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ now()->format('D, d M Y H:i:s') }}</div>
            </div>
        </div>

        <!-- Git Self-Updater & Diagnostics Control Widget -->
        <div x-data="{
                loadingInfo: false,
                updating: false,
                info: {
                    branch: 'Loading...',
                    commit: 'Loading...',
                    remote: 'Loading...',
                    clean: true,
                    changes: '',
                    timestamp: '--'
                },
                consoleOutput: 'Ready. Click \'Check Repo Status\' or \'Pull & Update\' to execute commands.',
                async fetchGitInfo() {
                    this.loadingInfo = true;
                    try {
                        const res = await fetch('{{ route('git.info') }}', {
                            headers: { 'Accept': 'application/json' }
                        });
                        const data = await res.json();
                        this.info = data;
                        this.consoleOutput = `[${data.timestamp}] Repository Diagnostics:\nBranch : ${data.branch}\nCommit : ${data.commit}\nRemote : ${data.remote}\nStatus : ${data.changes}`;
                    } catch (e) {
                        this.consoleOutput = 'Error fetching git info: ' + e.message;
                    } finally {
                        this.loadingInfo = false;
                    }
                },
                async runGitUpdate() {
                    this.updating = true;
                    this.consoleOutput = 'Executing git pull, database migrations, and cache flush...\n';
                    try {
                        const res = await fetch('{{ route('git.update') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').getAttribute('content')
                            }
                        });
                        const data = await res.json();
                        this.consoleOutput = `[${data.timestamp}]\n` + data.output;
                        await this.fetchGitInfo();
                    } catch (e) {
                        this.consoleOutput = 'Error executing update: ' + e.message;
                    } finally {
                        this.updating = false;
                    }
                }
            }"
            x-init="fetchGitInfo()"
            class="rounded-3xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-6 sm:p-8 shadow-sm">

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-gray-200 dark:border-gray-800">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                        <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <span>Git Self-Updater & Deployment Console</span>
                    </h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Inspect repository diagnostics, pull latest commits from origin, run database migrations, and flush caches.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button @click="fetchGitInfo()" :disabled="loadingInfo || updating" type="button"
                            class="inline-flex items-center gap-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/80 transition disabled:opacity-50">
                        <svg :class="{ 'animate-spin': loadingInfo }" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <span>Refresh Status</span>
                    </button>

                    <button @click="runGitUpdate()" :disabled="updating" type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 px-4 py-2.5 text-xs font-semibold text-white shadow-md shadow-indigo-600/25 transition disabled:opacity-50">
                        <svg :class="{ 'animate-spin': updating }" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        <span x-text="updating ? 'Updating...' : 'Pull & Update'">Pull & Update</span>
                    </button>
                </div>
            </div>

            <!-- Repository Diagnostics Grid -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="rounded-2xl bg-gray-50 dark:bg-gray-800/60 p-4 border border-gray-200/60 dark:border-gray-700/60">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Active Branch</span>
                    <p class="mt-1 font-mono text-sm font-bold text-indigo-600 dark:text-indigo-400" x-text="info.branch"></p>
                </div>

                <div class="rounded-2xl bg-gray-50 dark:bg-gray-800/60 p-4 border border-gray-200/60 dark:border-gray-700/60">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Latest Commit</span>
                    <p class="mt-1 font-mono text-xs font-medium text-gray-800 dark:text-gray-200 truncate" x-text="info.commit"></p>
                </div>

                <div class="rounded-2xl bg-gray-50 dark:bg-gray-800/60 p-4 border border-gray-200/60 dark:border-gray-700/60">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Remote Origin</span>
                    <p class="mt-1 font-mono text-xs font-medium text-gray-800 dark:text-gray-200 truncate" x-text="info.remote"></p>
                </div>
            </div>

            <!-- Terminal Output Log Box -->
            <div class="mt-6">
                <div class="flex items-center justify-between rounded-t-2xl bg-gray-900 px-4 py-2.5 border border-b-0 border-gray-800">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                        <span class="ms-2 font-mono text-xs text-gray-400">stdout — bash</span>
                    </div>
                    <span class="font-mono text-[11px] text-gray-500" x-text="info.timestamp"></span>
                </div>
                <pre class=" rounded-b-2xl bg-gray-950 p-4 font-mono text-xs leading-relaxed text-emerald-400 overflow-x-auto border border-gray-800 max-h-72 overflow-y-auto" x-text="consoleOutput"></pre>
            </div>
        </div>
    </div>
</x-app-layout>
