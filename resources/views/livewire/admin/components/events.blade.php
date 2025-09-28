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
                            {{ $event->created_at->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                        </td>
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                            {{ $event->event_name }}
                        </td>
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                            {{ $event->description ?? '-' }}
                        </td>
                        <td class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center">
                            {{ ucfirst($event->status) }}
                        </td>
                    </tr>
                @empty
                    <tr class="hover:bg-white/5 bg-black/5 transition-all">
                        <td colspan="4" class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center text-gray-400">
                            No events found
                        </td>
                    </tr>
                @endforelse
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
                    <td colspan="6" class="px-2 sm:px-3 py-4 text-xs sm:text-sm text-center text-gray-400">
                        No fight history yet
                    </td>
                </tr>
            </tbody>
        </x-table>
    </div>
</div>
