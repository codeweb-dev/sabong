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
                @forelse($events as $event)
                    <tr class="hover:bg-white/5 bg-black/5 transition-all">
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                            {{ $event->created_at->timezone('Asia/Manila')->format('M d, Y') }}
                        </td>
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center uppercase">
                            {{ $event->event_name }}
                        </td>
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center uppercase">
                            {{ $event->description ?? '-' }}
                        </td>
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center uppercase">
                            {{ ucfirst($event->status) }}
                        </td>
                    </tr>
                @empty
                    <tr class="hover:bg-white/5 bg-black/5 transition-all">
                        <td colspan="4"
                            class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center text-gray-400 uppercase">
                            No events found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>
    </div>

    <p class="text-lg sm:text-xl uppercase">fight history</p>
    <div class="overflow-x-auto">
        <div class="max-h-[500px] overflow-y-auto">
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
                    @if ($events && $fights->isNotEmpty())
                        @foreach ($fights as $fight)
                            <tr class="hover:bg-white/5 bg-black/5 transition-all">
                                <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">{{ $fight->fight_number }}
                                </td>
                                <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                    {{ $fight->meron_bet ?? 0 }}
                                </td>
                                <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">{{ $fight->wala_bet ?? 0 }}
                                </td>
                                <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center uppercase">
                                    {{ $fight->winner ?? '-' }}
                                </td>
                                <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                                    @php
                                        if ($fight->winner === 'meron') {
                                            $displayPayout = $fight->meron_payout ?? 0;
                                        } elseif ($fight->winner === 'wala') {
                                            $displayPayout = $fight->wala_payout ?? 0;
                                        } elseif (in_array($fight->winner, ['draw', 'cancel'])) {
                                            $displayPayout = 0;
                                        } else {
                                            $displayPayout = 0;
                                        }

                                        $displayPayoutInt =
                                            $fight->winner === 'draw' || $fight->winner === 'cancel'
                                                ? null
                                                : floor($displayPayout * 100);
                                    @endphp

                                    {{ $fight->winner === 'draw' || $fight->winner === 'cancel' ? 'Refund' : $displayPayoutInt }}
                                </td>
                                <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center uppercase">
                                    {{ ucfirst($fight->status) }}
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr class="hover:bg-white/5 bg-black/5 transition-all">
                            <td colspan="6"
                                class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center text-gray-400 uppercase">
                                No fight history yet
                            </td>
                        </tr>
                    @endif
                </tbody>
            </x-table>
        </div>
    </div>

</div>
