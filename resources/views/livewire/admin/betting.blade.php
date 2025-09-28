<div class="mx-auto max-w-6xl">
    <div class="flex flex-col gap-12">
        <div class="grid grid-cols-3 gap-6">
            <div class="flex w-68 flex-col gap-2 uppercase">
                <p class="uppercase text-center">bets & payout</p>
                <div class="space-y-1">
                    <p class="flex justify-between">
                        <span>total bets:</span>
                        <span>50,000</span>
                    </p>
                    <p class="flex justify-between">
                        <span>total payout:</span>
                        <span>50,000</span>
                    </p>
                </div>
            </div>
            <div>
                <div class="flex justify-between">
                    <p class="uppercase">teller name</p>
                    <flux:input />
                </div>
                <div class="flex justify-between">
                    <p class="uppercase">ticket number</p>
                    <flux:input />
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex flex-col gap-1 flex-1">
                    <p class="uppercase text-center">fight</p>
                    <flux:select class="uppercase">
                        <flux:select.option>all</flux:select.option>
                    </flux:select>
                </div>
                <div class="flex flex-col gap-1 flex-1">
                    <p class="uppercase text-center">side</p>
                    <flux:select class="uppercase">
                        <flux:select.option>all</flux:select.option>
                    </flux:select>
                </div>
                <div class="flex flex-col gap-1 flex-1">
                    <p class="uppercase text-center">status</p>
                    <flux:select class="uppercase">
                        <flux:select.option>all</flux:select.option>
                    </flux:select>
                </div>
            </div>
        </div>

        <div class="flex-1">
            <div class="overflow-x-auto">
                <x-table class="min-w-full">
                    <thead
                        class="border-b dark:border-white/10 border-black/10 hover:bg-white/5 bg-black/5 transition-all">
                        <tr>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">fight no.</th>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">teller payout</th>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">ticket #</th>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">side</th>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">amount pay in</th>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">teller payout</th>
                            <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">amount payout</th>
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
