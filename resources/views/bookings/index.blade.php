@extends('layouts.public')

@section('content')
@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
@endpush

<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <h2 class="text-3xl font-bold text-gray-900 mb-4 md:mb-0">
                <i class="fas fa-receipt mr-2 text-indigo-500"></i>My Bookings
            </h2>
            <a href="{{ route('attractions.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                <i class="fas fa-plus-circle mr-2"></i>Book New Ticket
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <p class="font-bold">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <p class="font-bold">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if($bookings->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($bookings as $booking)
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col h-full hover:shadow-2xl transition duration-300 transform hover:-translate-y-1">
                        <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                            <small class="text-gray-500 font-medium">#{{ $booking->id }}</small>
                            @php
                                $statusColor = 'bg-gray-400 text-gray-800';
                                if ($booking->status === 'paid') {
                                    $statusColor = 'bg-green-500 text-white';
                                } elseif ($booking->status === 'pending') {
                                    $statusColor = 'bg-yellow-500 text-yellow-900';
                                } elseif (in_array($booking->status, ['cancelled', 'denied', 'expired'])) {
                                    $statusColor = 'bg-red-500 text-white';
                                }
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </div>

                        <div class="p-6 flex-grow">
                            @if($booking->touristAttraction->featured_image)
                                <img src="{{ asset('storage/' . $booking->touristAttraction->featured_image) }}"
                                     class="w-full h-40 object-cover rounded-md mb-4" alt="{{ $booking->touristAttraction->name }}">
                            @else
                                <div class="w-full h-40 bg-gray-200 rounded-md mb-4 flex items-center justify-center">
                                    <i class="fas fa-image text-4xl text-gray-400"></i>
                                </div>
                            @endif

                            <h6 class="text-lg font-semibold text-gray-800">{{ $booking->touristAttraction->name }}</h6>
                            
                            <div class="mt-3 text-sm text-gray-600">
                                <p class="mb-1 flex items-center">
                                    <i class="fas fa-calendar-alt mr-2 text-indigo-500"></i>
                                    {{ \Carbon\Carbon::parse($booking->visit_date)->format('d M Y') }}
                                </p>
                                <p class="mb-1 flex items-center">
                                    <i class="fas fa-ticket-alt mr-2 text-indigo-500"></i>
                                    {{ $booking->quantity }} ticket(s)
                                </p>
                                <p class="mb-0 flex items-center">
                                    <i class="fas fa-map-marker-alt mr-2 text-indigo-500"></i>
                                    {{ $booking->touristAttraction->location }}
                                </p>
                            </div>

                            <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-100">
                                <span class="text-gray-500">Total Amount:</span>
                                <span class="text-xl font-bold text-green-600">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="p-6 border-t border-gray-200 grid grid-cols-1 gap-4">
                            <a href="{{ route('bookings.show', $booking) }}" class="w-full text-center bg-white border border-indigo-500 text-indigo-600 font-semibold py-2 px-4 rounded-lg transition duration-300 hover:bg-indigo-50">
                                <i class="fas fa-eye mr-1"></i>View Details
                            </a>

                            @if($booking->status === 'paid')
                                <a href="{{ route('bookings.downloadTicket', $booking) }}" class="w-full text-center bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition duration-300 hover:bg-indigo-700">
                                    <i class="fas fa-download mr-1"></i>Download Ticket
                                </a>
                            @endif

                            @if(in_array($booking->status, ['pending']))
                                <div class="grid grid-cols-2 gap-2">
                                    <a href="{{ route('bookings.edit', $booking) }}" class="text-center bg-yellow-500 hover:bg-yellow-600 text-yellow-900 font-bold py-2 px-4 rounded-lg shadow-sm transition duration-300">
                                        <i class="fas fa-pencil-alt"></i> Edit
                                    </a>
                                    <form action="{{ route('bookings.destroy', $booking) }}" method="POST" class="w-full">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition duration-300" 
                                                onclick="return confirm('Are you sure you want to cancel this booking?')">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            @if($bookings->hasPages())
                <div class="mt-8 flex justify-center">
                    {{ $bookings->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-12 bg-white rounded-lg shadow-lg">
                <i class="fas fa-receipt text-6xl text-gray-300 mb-4"></i>
                <h4 class="mt-3 text-2xl font-semibold text-gray-500">Belum Ada Pemesanan</h4>
                <p class="text-gray-600 mb-6">Anda belum melakukan pemesanan apa pun. Mulailah menjelajahi atraksi kami dan pesan tiket Anda!</p>
                <a href="{{ route('attractions.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-full shadow-md transition duration-300">
                    <i class="fas fa-search mr-2"></i>Jelajahi Atraksi
                </a>
            </div>
        @endif
    </div>
</div>
@endsection