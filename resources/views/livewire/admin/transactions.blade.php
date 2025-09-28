<div class="mx-auto max-w-6xl">
    <div class="flex flex-col gap-12">
        <div class="flex items-center gap-6">
            <div class="flex w-68 flex-col gap-2 uppercase">
                <div class="space-y-1">
                    <p class="flex justify-between">
                        <span>revolving:</span>
                        <span>100,000</span>
                    </p>
                    <p class="flex justify-between">
                        <span>total transfer:</span>
                        <span>50,000</span>
                    </p>
                    <p class="flex justify-between">
                        <span>total received:</span>
                        <span>50,000</span>
                    </p>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <flux:button class="flex-1 uppercase">transfer</flux:button>
                    <flux:button class="flex-1 uppercase">received</flux:button>
                </div>
            </div>

            <div class="flex-1">
                <div class="overflow-x-auto">
                    <x-table class="min-w-full">
                        <thead
                            class="border-b dark:border-white/10 border-black/10 hover:bg-white/5 bg-black/5 transition-all">
                            <tr>
                                <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">teller name</th>
                                <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">amount</th>
                                <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">receiver</th>
                                <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">note</th>
                                <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">status</th>
                                <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">date</th>
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
        </div>

        <div class="flex-1">
            <p class="text-lg sm:text-xl uppercase">fight history</p>
            <div class="overflow-x-auto">
                <x-table class="min-w-full">
                    <thead
                        class="border-b dark:border-white/10 border-black/10 hover:bg-white/5 bg-black/5 transition-all">
                        <tr>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">teller name</th>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">coh</th>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">cash in</th>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">cash out</th>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">total bets</th>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">total payout</th>
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
    </div>
</div>
