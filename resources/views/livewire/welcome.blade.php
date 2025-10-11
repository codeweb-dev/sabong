<div
    class="{{ $isSmallScreen ? 'transform scale-50 origin-top-left w-[200%] h-[200%] overflow-hidden rounded-lg' : '' }}">
    @if ($currentEvent)
        <div class="flex flex-col md:flex-row h-screen {{ $isSmallScreen ? 'bg-zinc-900' : '' }}">
            <aside
                class="w-full md:w-78 flex flex-row md:flex-col gap-3 md:gap-6 px-4 md:px-6 py-4 md:py-8 border-b md:border-b-0 md:border-e border-zinc-200 dark:border-zinc-700 overflow-y-auto scrollbar-hide">
                @foreach ($fights as $fight)
                    <div
                        class="flex-1 md:p-12 py-8 px-12 bg-green-400 text-black flex items-center justify-center text-3xl md:text-4xl rounded-2xl relative">
                        <p>{{ $fight->fight_number }}</p>

                        <flux:badge variant="solid" color="red" class="absolute top-2 right-2">
                            {{ $fight->status }}</flux:badge>
                    </div>
                @endforeach
            </aside>

            <div class="flex-1 flex flex-col overflow-auto">
                <div class="flex items-center">
                    <div class="flex flex-col flex-1 border border-zinc-200 dark:border-zinc-700">
                        <p
                            class="text-2xl md:text-5xl text-center font-bold py-5 border-b border-zinc-200 dark:border-zinc-700">
                            FIGHT#</p>
                        <p class="text-2xl md:text-5xl text-center font-bold py-5">
                            {{ $activeFight?->fight_number ?? '-' }}</p>
                    </div>

                    <div class="flex flex-col flex-1 border border-zinc-200 dark:border-zinc-700">
                        <p
                            class="text-2xl md:text-5xl text-center font-bold py-5 border-b border-zinc-200 dark:border-zinc-700">
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
                            <p class="text-center text-5xl md:text-7xl font-bold">0</p>
                            <p class="text-center text-xl md:text-3xl mt-2">PAYOUT : 0</p>
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
                            class="p-6 md:p-10 flex-1 text-black flex items-center justify-center text-3xl md:text-4xl rounded-2xl bg-green-400">
                            <p class="text-center font-bold">WALA</p>
                        </div>
                        <div class="p-4 md:p-6 flex-1">
                            <p class="text-center text-5xl md:text-7xl font-bold">0</p>
                            <p class="text-center text-xl md:text-3xl mt-2">PAYOUT : 0</p>
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
        <div class="flex flex-col md:flex-row h-screen {{ $isSmallScreen ? 'bg-zinc-900' : '' }}">
            <aside
                class="w-full md:w-78 flex flex-row md:flex-col gap-3 md:gap-6 px-4 md:px-6 py-4 md:py-8 border-b md:border-b-0 md:border-e border-zinc-200 dark:border-zinc-700 overflow-y-auto scrollbar-hide">
            </aside>

            <div class="flex-1 flex flex-col overflow-auto">
                <div class="flex items-center">
                    <div class="flex flex-col flex-1 border border-zinc-200 dark:border-zinc-700">
                        <p
                            class="text-2xl md:text-5xl text-center font-bold py-5 border-b border-zinc-200 dark:border-zinc-700">
                            FIGHT#</p>
                        <p class="text-2xl md:text-5xl text-center font-bold py-5">-</p>
                    </div>

                    <div class="flex flex-col flex-1 border border-zinc-200 dark:border-zinc-700">
                        <p
                            class="text-2xl md:text-5xl text-center font-bold py-5 border-b border-zinc-200 dark:border-zinc-700">
                            BETTING IS</p>
                        <p class="text-2xl md:text-5xl text-center font-bold py-5">-</p>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row w-full gap-4 md:gap-6 px-4 md:px-6 mb-6 mt-6">
                    <div class="flex-1">
                        <div
                            class="p-6 md:p-10 flex-1 text-black flex items-center justify-center text-3xl md:text-4xl rounded-2xl bg-red-400">
                            <p class="text-center font-bold">MERON</p>
                        </div>
                        <div class="p-4 md:p-6 flex-1">
                            <p class="text-center text-5xl md:text-7xl font-bold">0</p>
                            <p class="text-center text-xl md:text-3xl mt-2">PAYOUT : 0</p>
                        </div>
                        <div class="p-4 md:p-6 flex-1">
                            <p class="text-center text-4xl md:text-5xl font-bold">-</p>
                        </div>
                        <div
                            class="p-6 md:p-10 flex-1 text-black flex items-center justify-center text-3xl md:text-4xl rounded-2xl bg-white">
                            <p class="text-center font-bold uppercase">-</p>
                        </div>
                    </div>

                    <div class="md:hidden">
                        <flux:separator class="my-8" />
                    </div>

                    <div class="flex-1">
                        <div
                            class="p-6 md:p-10 flex-1 text-black flex items-center justify-center text-3xl md:text-4xl rounded-2xl bg-green-400">
                            <p class="text-center font-bold">WALA</p>
                        </div>
                        <div class="p-4 md:p-6 flex-1">
                            <p class="text-center text-5xl md:text-7xl font-bold">0</p>
                            <p class="text-center text-xl md:text-3xl mt-2">PAYOUT : 0</p>
                        </div>

                        <div class="p-4 md:p-6 flex-1">
                            <p class="text-center text-4xl md:text-5xl font-bold">-</p>
                        </div>

                        <div
                            class="p-6 md:p-10 flex-1 text-black flex items-center justify-center text-3xl md:text-4xl rounded-2xl bg-white">
                            <p class="text-center font-bold uppercase">-</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
