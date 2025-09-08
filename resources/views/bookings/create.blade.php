<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2 class="text-2xl font-bold mb-6">Book {{ $attraction->name }}</h2>

                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md relative mb-4" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('bookings.store', $attraction) }}">
                        @csrf

                        <div class="space-y-4">
                            <div>
                                <label for="ticket_id" class="block text-sm font-medium text-gray-700">Select Ticket Type <span class="text-red-500">*</span></label>
                                <select name="ticket_id" id="ticket_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm @error('ticket_id') border-red-500 @enderror" required>
                                    <option value="">-- Choose a ticket --</option>
                                    @foreach($attraction->tickets as $ticket)
                                    <option value="{{ $ticket->id }}" data-price="{{ $ticket->price }}" {{ old('ticket_id') == $ticket->id ? 'selected' : '' }}>
                                        {{ $ticket->name }} - Rp {{ number_format($ticket->price, 0, ',', '.') }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('ticket_id')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="visit_date" class="block text-sm font-medium text-gray-700">Visit Date <span class="text-red-500">*</span></label>
                                <input type="date" name="visit_date" id="visit_date"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm @error('visit_date') border-red-500 @enderror"
                                    min="{{ date('Y-m-d') }}"
                                    value="{{ old('visit_date') }}" required>
                                @error('visit_date')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700">Number of Tickets <span class="text-red-500">*</span></label>
                                <input type="number" name="quantity" id="quantity"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm @error('quantity') border-red-500 @enderror"
                                    value="{{ old('quantity', 1) }}" min="1" required>
                                @error('quantity')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">Special Notes (Optional)</label>
                                <textarea name="notes" id="notes" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm @error('notes') border-red-500 @enderror"
                                    placeholder="Any special requests or notes...">{{ old('notes') }}</textarea>
                                @error('notes')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Proceed to Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>