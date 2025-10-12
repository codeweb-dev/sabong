<div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
    <div class="flex flex-col gap-6 w-full lg:w-1/2">
        <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold uppercase text-center">
            cash on hand : {{ number_format($cashOnHand, 2) }}
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 px-4 sm:px-8 md:px-12 lg:px-20">
            <flux:button wire:click="addAmount(100)" icon="plus" class="text-sm sm:text-base">100</flux:button>
            <flux:button wire:click="addAmount(200)" icon="plus" class="text-sm sm:text-base">200</flux:button>
            <flux:button wire:click="addAmount(500)" icon="plus" class="text-sm sm:text-base">500</flux:button>
            <flux:button wire:click="addAmount(1000)" icon="plus" class="text-sm sm:text-base">1,000</flux:button>
            <flux:button wire:click="addAmount(5000)" icon="plus" class="text-sm sm:text-base">5,000</flux:button>
            <flux:button wire:click="addAmount(10000)" icon="plus" class="text-sm sm:text-base">10,000</flux:button>
        </div>

        <div>
            <flux:input.group>
                <flux:input wire:model="amount" type="number" placeholder="Enter Here" class="text-sm sm:text-base" />
                <flux:button wire:click="clearAmount" icon="x-mark" class="uppercase text-sm sm:text-base">clear
                </flux:button>
            </flux:input.group>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <flux:button class="uppercase text-sm sm:text-base md:text-lg">meron</flux:button>
            <flux:button class="uppercase text-sm sm:text-base md:text-lg">wala</flux:button>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 lg:gap-18">
            <div class="flex flex-col gap-2">
                <p class="text-lg sm:text-xl uppercase">bet history</p>
                <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                    <p class="text-lg sm:text-xl uppercase">fight#</p>
                    <div class="flex items-center gap-2">
                        <flux:select placeholder="Choose fight" class="min-w-0">
                            <flux:select.option>1</flux:select.option>
                        </flux:select>
                        <flux:button class="uppercase text-sm sm:text-base" icon="arrow-path">refresh</flux:button>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-center lg:justify-end">
                <div class="grid grid-cols-2 gap-2 w-full sm:w-auto">
                    <flux:button class="uppercase text-sm">reprint</flux:button>
                    <flux:button class="uppercase text-sm">cancel</flux:button>
                    <flux:input class="text-sm" placeholder="Input 1" />
                    <flux:input class="text-sm" placeholder="Input 2" />
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <x-table class="min-w-full">
                <thead class="border-b dark:border-white/10 border-black/10 hover:bg-white/5 bg-black/5 transition-all">
                    <tr>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">fight #</th>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">side</th>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">amount</th>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm">ticket #</th>
                        <th class="px-2 sm:px-3 py-3 uppercase text-center text-xs sm:text-sm hidden sm:table-cell">date
                            & time</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="hover:bg-white/5 bg-black/5 transition-all">
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">empty</td>
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">empty</td>
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">empty</td>
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm">empty</td>
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm hidden sm:table-cell">empty</td>
                    </tr>
                </tbody>
            </x-table>
        </div>
    </div>

    <div class="flex flex-col gap-4 lg:gap-6 w-full lg:w-1/2">
        <div class="w-full h-100 overflow-hidden border border-zinc-700 rounded-lg bg-zinc-900">
            <livewire:welcome :small-screen="true" />
        </div>

        <div class="flex flex-col gap-2 items-center justify-center">
            <p class="text-lg sm:text-xl uppercase">payout</p>
            <div class="w-full max-w-sm sm:max-w-md lg:max-w-2xl mx-auto">
                <flux:input class="w-full text-sm sm:text-base" />
            </div>
        </div>

        <div class="flex flex-col gap-2 items-center justify-center">
            <p class="text-lg sm:text-xl uppercase">ticket #</p>
            <div class="w-full max-w-sm sm:max-w-md lg:max-w-2xl mx-auto">
                <flux:input class="w-full text-sm sm:text-base" />
            </div>
        </div>

        <div class="flex flex-col gap-2 items-center justify-center text-center">
            <p class="text-lg sm:text-xl uppercase">Preview Print</p>
            <p class="text-sm sm:text-base lg:text-xl uppercase text-gray-400">
                No receipt yet (need receipt)
            </p>
        </div>
    </div>
</div>
