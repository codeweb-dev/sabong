<div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
    <div class="flex flex-col gap-6 w-full lg:w-1/2">
        <div class="flex items-center justify-between uppercase">
            <div>
                <p>Event: </p>
                <p>Description: </p>
            </div>

            <p>Date: </p>
        </div>

        <div class="flex items-center gap-6 uppercase">
            <div class="flex-1">
                <p class="text-center mb-1">Meron</p>
                <flux:button class="text-sm sm:text-base w-full">0</flux:button>
            </div>
            <div class="flex-1">
                <p class="text-center mb-1">Wala</p>
                <flux:button class="text-sm sm:text-base w-full">0</flux:button>
            </div>
            <div class="flex-1">
                <p class="text-center mb-1">Draw</p>
                <flux:button class="text-sm sm:text-base w-full">0</flux:button>
            </div>
            <div class="flex-1">
                <p class="text-center mb-1">Cancelled</p>
                <flux:button class="text-sm sm:text-base w-full">0</flux:button>
            </div>
        </div>

        <div class="flex items-center border border-zinc-200 dark:border-zinc-700 uppercase">
            <div class="border-r border-zinc-200 dark:border-zinc-700 py-5 flex-1">
                <p class="font-bold text-center">fight # : 03</p>
            </div>

            <div class="py-5 flex-1">
                <p class="font-bold text-center">betting : open</p>
            </div>
        </div>

        <p class="text-lg sm:text-xl uppercase">fight history</p>
        <div class="overflow-x-auto">
            <x-table class="min-w-full">
                <thead class="border-b dark:border-white/10 border-black/10 hover:bg-white/5 bg-black/5 transition-all">
                    <tr>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">fight #</th>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">meron</th>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">wala</th>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">result</th>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">payout</th>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="hover:bg-white/5 bg-black/5 transition-all">
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">empty</td>
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">empty</td>
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">empty</td>
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">empty</td>
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">empty</td>
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">empty</td>
                    </tr>
                </tbody>
            </x-table>
        </div>
    </div>

    <div class="flex flex-col gap-2 w-full lg:w-1/2">
        <div class="w-full h-100 overflow-hidden border border-zinc-700 rounded-lg bg-zinc-900">
            <livewire:welcome :small-screen="true" />
        </div>

        <div class="flex flex-col items-center justify-center gap-3">
            <flux:button class="uppercase">start</flux:button>
            <flux:button class="uppercase">open bet</flux:button>
        </div>

        <div class="flex items-center justify-between">
            <flux:button class="uppercase">meron lock</flux:button>
            <flux:button class="uppercase">wala lock</flux:button>
        </div>

        <div class="flex flex-col items-center justify-center gap-3">
            <flux:button class="uppercase">close bet</flux:button>
        </div>

        <div class="flex items-center justify-between">
            <flux:button class="uppercase">meron wins</flux:button>
            <flux:button class="uppercase">wala wins</flux:button>
        </div>

        <div class="flex items-center justify-between">
            <flux:button class="uppercase">draw</flux:button>
            <flux:button class="uppercase">cancel</flux:button>
        </div>

        <div class="flex flex-col items-center justify-center gap-3">
            <flux:button class="uppercase">end fight</flux:button>
        </div>

        <div class="flex items-center justify-between">
            <div>
                <p class="text-center uppercase mb-1">meron</p>
                <flux:input />
            </div>

            <div>
                <p class="text-center uppercase mb-1">wala</p>
                <flux:input />
            </div>
        </div>
    </div>
</div>
