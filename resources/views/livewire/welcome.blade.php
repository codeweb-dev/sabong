<div
    class="{{ $isSmallScreen ? 'transform scale-50 origin-top-left w-[200%] h-[200%] overflow-hidden rounded-lg' : '' }}">
    @if ($showWinnerOverlay && $activeFight)
        @php
            $winner = $winnerSide;

            // Winner gradient background
            $winnerGradient = match ($winner) {
                'meron' => 'from-red-500 to-red-800',
                'wala' => 'from-blue-500 to-blue-800',
                'draw' => 'from-emerald-400 to-emerald-700',
                'cancel' => 'from-zinc-400 to-zinc-700',
                default => 'from-zinc-500 to-zinc-800',
            };

            // Winner text formatting
            $winnerText = match ($winner) {
                'meron' => 'MERON WINS',
                'wala' => 'WALA WINS',
                'draw' => 'DRAW',
                'cancel' => 'CANCELLED',
                default => 'RESULT',
            };
        @endphp

        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/70">
            <div class="relative w-[90%] max-w-6xl rounded-3xl shadow-2xl overflow-hidden border border-white/10">
                <div class="bg-gradient-to-br {{ $winnerGradient }} px-6 py-8 md:px-12 md:py-24 text-center text-white">

                    <p class="text-3xl uppercase tracking-[0.35em] mb-3">
                        Official Result
                    </p>

                    <p class="text-4xl md:text-9xl font-extrabold mb-4 leading-tight drop-shadow-lg">
                        {{ $winnerText }}
                    </p>

                    <p class="text-4xl mb-4">
                        Fight #{{ $activeFight?->fight_number ?? '-' }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if ($currentEvent)
        <div class="flex flex-col md:flex-row h-screen {{ $isSmallScreen ? 'bg-zinc-200 dark:bg-zinc-900' : '' }}">
            <aside
                class="w-full md:w-78 flex flex-row md:flex-col gap-3 px-4 md:px-6 py-4 md:py-8 border-b md:border-b-0 md:border-e border-zinc-500 dark:border-zinc-700 overflow-y-auto scrollbar-hide">

                @php
                    $activeStatuses = ['start', 'open', 'close'];
                    $activeFight = $fights->firstWhere(fn($f) => in_array($f->status, $activeStatuses));
                    $currentFight = $activeFight ?: $fights->firstWhere('status', 'pending');
                    $completedFights = $fights
                        ->where('id', '!=', optional($currentFight)->id)
                        ->whereNotIn('status', ['pending', 'start', 'open', 'close'])
                        ->reverse()
                        ->take(4);

                    function fightColor($fight)
                    {
                        return match ($fight->winner) {
                            'meron' => 'bg-red-400 text-white',
                            'wala' => 'bg-blue-400 text-white',
                            'draw' => 'bg-green-400 text-black',
                            'cancel' => 'bg-gray-400 text-black',
                            default => ' bg-black dark:bg-white text-white dark:text-black',
                        };
                    }
                @endphp

                @if ($currentFight)
                    <div
                        class="py-8 px-12 md:p-12 {{ fightColor($currentFight) }} flex flex-col items-center justify-center text-3xl md:text-4xl rounded-2xl relative transition-all duration-300">
                        <p>{{ $currentFight->fight_number }}</p>

                        @if ($currentFight->winner)
                            <flux:badge class="mt-2" size="lg" variant="solid" color="black">
                                {{ strtoupper($currentFight->winner) }}
                            </flux:badge>
                        @endif
                    </div>
                @endif

                @foreach ($completedFights as $fight)
                    <div
                        class="py-8 px-12 md:p-12 {{ fightColor($fight) }} flex flex-col items-center justify-center text-3xl md:text-4xl rounded-2xl relative transition-all duration-300">
                        <p>{{ $fight->fight_number }}</p>

                        @if ($fight->status === 'done' && $fight->winner)
                            <flux:badge class="mt-2" size="lg" variant="solid" color="black">
                                {{ strtoupper($fight->winner) }}
                            </flux:badge>
                        @endif
                    </div>
                @endforeach
            </aside>

            <div class="flex-1 flex flex-col overflow-auto">
                <div class="flex items-center">
                    <div class="flex flex-col flex-1 border-b border-zinc-500 dark:border-zinc-700">
                        <p
                            class="text-2xl md:text-5xl text-center font-bold py-5 border-b border-zinc-500 dark:border-zinc-700">
                            FIGHT#</p>
                        <p class="text-2xl md:text-5xl text-center font-bold py-5">
                            {{ $activeFight?->fight_number ?? '-' }}</p>
                    </div>

                    <div class="flex flex-col flex-1 border-b border-l border-zinc-500 dark:border-zinc-700">
                        <p
                            class="text-2xl md:text-5xl text-center font-bold py-5 border-b border-zinc-500 dark:border-zinc-700">
                            BETTING IS
                        </p>
                        <p class="text-2xl md:text-5xl text-center font-bold py-5 uppercase">
                            {{ $activeFight?->status === 'start' ? '-' : $activeFight?->status ?? '-' }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row w-full gap-4 md:gap-6 px-4 md:px-6 mb-6 mt-6">
                    <div class="flex-1">
                        <div
                            class="p-6 md:p-10 flex-1 text-black flex items-center justify-center text-3xl md:text-4xl rounded-2xl bg-red-400">
                            <p class="text-center font-bold">MERON</p>
                        </div>
                        <div class="p-4 md:p-6 flex-1">
                            <p class="text-center text-5xl md:text-7xl font-bold">
                                {{ number_format($totalMeronBet ?? 0, 0) }}
                            </p>
                            <p class="text-center text-xl md:text-3xl mt-2">
                                PAYOUT : {{ $showPayout ? $meronPayoutDisplay : 0 }}
                            </p>
                        </div>
                        <div class="p-4 md:p-6 flex-1">
                            <p class="text-center text-4xl md:text-5xl font-bold uppercase">
                                {{ $activeFight ? ($activeFight->status === 'start' ? '-' : ($activeFight->meron ? 'open' : 'locked')) : '-' }}
                            </p>
                        </div>
                        <div
                            class="p-6 md:p-10 flex-1 text-black flex items-center justify-center text-3xl md:text-4xl rounded-2xl bg-white">
                            <p class="text-center font-bold uppercase">
                                {{ $activeFight?->fighter_a ?? 'Fighter Meron' }}
                            </p>
                        </div>
                    </div>

                    <div class="md:hidden">
                        <flux:separator class="my-8" />
                    </div>

                    <div class="flex-1">
                        <div
                            class="p-6 md:p-10 flex-1 text-black flex items-center justify-center text-3xl md:text-4xl rounded-2xl bg-blue-400">
                            <p class="text-center font-bold">WALA</p>
                        </div>
                        <div class="p-4 md:p-6 flex-1">
                            <p class="text-center text-5xl md:text-7xl font-bold">
                                {{ number_format($totalWalaBet ?? 0, 0) }}
                            </p>
                            <p class="text-center text-xl md:text-3xl mt-2">
                                PAYOUT : {{ $showPayout ? $walaPayoutDisplay : 0 }}
                            </p>
                        </div>
                        <div class="p-4 md:p-6 flex-1">
                            <p class="text-center text-4xl md:text-5xl font-bold uppercase">
                                {{ $activeFight ? ($activeFight->status === 'start' ? '-' : ($activeFight->wala ? 'open' : 'locked')) : '-' }}
                            </p>
                        </div>
                        <div
                            class="p-6 md:p-10 flex-1 text-black flex items-center justify-center text-3xl md:text-4xl rounded-2xl bg-white">
                            <p class="text-center font-bold uppercase">
                                {{ $activeFight?->fighter_b ?? 'Fighter Wala' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <x-welcome-placeholder :isSmallScreen="$isSmallScreen" />
    @endif
</div>
