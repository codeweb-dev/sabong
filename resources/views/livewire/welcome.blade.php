<div
    class="{{ $isSmallScreen ? 'transform scale-50 origin-top-left w-[200%] h-[200%] overflow-hidden rounded-lg' : '' }}">
    <div class="flex flex-col md:flex-row h-screen {{ $isSmallScreen ? 'bg-zinc-900' : '' }}">
        <aside
            class="w-full md:w-78 flex flex-row md:flex-col gap-3 md:gap-6 px-4 md:px-6 py-4 md:py-8 border-b md:border-b-0 md:border-e border-zinc-200 dark:border-zinc-700 overflow-y-auto scrollbar-hide">
            <div
                class="flex-1 md:p-12 py-8 px-12 bg-green-400 text-black flex items-center justify-center text-3xl md:text-4xl rounded-2xl relative">
                <p>1</p>
                <flux:badge variant="solid" color="red" class="absolute top-2 right-2">Wala</flux:badge>
            </div>
            <div
                class="flex-1 md:p-12 py-8 px-12 bg-red-400 text-black flex items-center justify-center text-3xl md:text-4xl rounded-2xl relative">
                <p>2</p>
                <flux:badge variant="solid" color="green" class="absolute top-2 right-2">Meron</flux:badge>
            </div>
            <div
                class="flex-1 md:p-12 py-8 px-12 bg-green-400 text-black flex items-center justify-center text-3xl md:text-4xl rounded-2xl relative">
                <p>3</p>
                <flux:badge variant="solid" color="blue" class="absolute top-2 right-2">Live</flux:badge>
            </div>
            <div
                class="flex-1 md:p-12 py-8 px-12 bg-red-400 text-black flex items-center justify-center text-3xl md:text-4xl rounded-2xl relative">
                <p>4</p>
                <flux:badge variant="solid" color="green" class="absolute top-2 right-2">Next</flux:badge>
            </div>
            <div
                class="flex-1 md:p-12 py-8 px-12 bg-green-400 text-black flex items-center justify-center text-3xl md:text-4xl rounded-2xl relative">
                <p>5</p>
                <flux:badge variant="solid" color="red" class="absolute top-2 right-2">Next</flux:badge>
            </div>
            <div
                class="flex-1 md:p-12 py-8 px-12 bg-red-400 text-black flex items-center justify-center text-3xl md:text-4xl rounded-2xl relative">
                <p>6</p>
                <flux:badge variant="solid" color="green" class="absolute top-2 right-2">Next</flux:badge>
            </div>
            <div
                class="flex-1 md:p-12 py-8 px-12 bg-green-400 text-black flex items-center justify-center text-3xl md:text-4xl rounded-2xl relative">
                <p>7</p>
                <flux:badge variant="solid" color="red" class="absolute top-2 right-2">Next</flux:badge>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-auto">
            <div class="flex items-center">
                <div class="flex flex-col flex-1 border border-zinc-200 dark:border-zinc-700">
                    <p
                        class="text-2xl md:text-5xl text-center font-bold py-5 border-b border-zinc-200 dark:border-zinc-700">
                        FIGHT#</p>
                    <p class="text-2xl md:text-5xl text-center font-bold py-5">3</p>
                </div>

                <div class="flex flex-col flex-1 border border-zinc-200 dark:border-zinc-700">
                    <p
                        class="text-2xl md:text-5xl text-center font-bold py-5 border-b border-zinc-200 dark:border-zinc-700">
                        BETTING IS</p>
                    <p class="text-2xl md:text-5xl text-center font-bold py-5">OPEN</p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row w-full gap-4 md:gap-6 px-4 md:px-6 mb-6 mt-6">
                <div class="flex-1">
                    <div
                        class="p-6 md:p-10 flex-1 text-black flex items-center justify-center text-3xl md:text-4xl rounded-2xl bg-red-400">
                        <p class="text-center font-bold">MERON</p>
                    </div>
                    <div class="p-4 md:p-6 flex-1">
                        <p class="text-center text-5xl md:text-7xl font-bold">50,000</p>
                        <p class="text-center text-xl md:text-3xl mt-2">PAYOUT : 160</p>
                    </div>
                    <div class="p-4 md:p-6 flex-1">
                        <p class="text-center text-4xl md:text-5xl font-bold text-green-400">OPEN</p>
                    </div>
                    <div
                        class="p-6 md:p-10 flex-1 text-black flex items-center justify-center text-3xl md:text-4xl rounded-2xl bg-white">
                        <p class="text-center font-bold uppercase">fighter a</p>
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
                        <p class="text-center text-5xl md:text-7xl font-bold">50,000</p>
                        <p class="text-center text-xl md:text-3xl mt-2">PAYOUT : 160</p>
                    </div>

                    <div class="p-4 md:p-6 flex-1">
                        <p class="text-center text-4xl md:text-5xl font-bold text-red-400">LOCKED</p>
                    </div>

                    <div
                        class="p-6 md:p-10 flex-1 text-black flex items-center justify-center text-3xl md:text-4xl rounded-2xl bg-white">
                        <p class="text-center font-bold uppercase">fighter b</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
