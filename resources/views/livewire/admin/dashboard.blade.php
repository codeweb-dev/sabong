<div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
    <div class="flex flex-col gap-6 w-full lg:w-1/2">
        <p class="text-lg sm:text-xl uppercase">events</p>
        <div class="overflow-x-auto">
            <x-table class="min-w-full">
                <thead class="border-b dark:border-white/10 border-black/10 hover:bg-white/5 bg-black/5 transition-all">
                    <tr>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">date</th>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">event name</th>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">description</th>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="hover:bg-white/5 bg-black/5 transition-all">
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">empty</td>
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">empty</td>
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">empty</td>
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">empty</td>
                    </tr>
                </tbody>
            </x-table>
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
            <flux:button class="uppercase">create event</flux:button>
        </div>

        <div class="flex items-center justify-between">
            <flux:button class="uppercase">start event</flux:button>
            <flux:button class="uppercase">end event</flux:button>
        </div>
    </div>
</div>
