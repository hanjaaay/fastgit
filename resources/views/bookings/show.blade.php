<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Breadcrumb --}}
            <nav class="mb-4">
                <ol class="flex items-center space-x-2 text-gray-500">
                    <li><a href="{{ route('home') }}" class="hover:text-gray-700">Home</a></li>
                    <li><span class="text-gray-400">/</span></li>
                    <li><a href="{{ route('bookings.index') }}" class="hover:text-gray-700">My Bookings</a></li>
                    <li><span class="text-gray-400">/</span></li>
                    <li class="text-gray-700">Booking #{{ $booking->order_id }}</li>
                </ol>
            </nav>

            {{-- Alert Messages --}}
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Main Content Card --}}
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <h4 class="text-2xl font-bold text-gray-800">
                        Booking #{{ $booking->order_id }}
                    </h4>
                    <span class="px-3 py-1 text-sm font-semibold rounded-full
                        @if($booking->status == 'paid') bg-green-100 text-green-800 @elseif($booking->status == 'pending') bg-yellow-100 text-yellow-800 @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>

                <div class="md:flex md:space-x-8">
                    <div class="md:w-2/3">
                        <h5 class="text-indigo-600 font-bold mb-3">Booking Details</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div>
                                <div class="bg-gray-100 rounded-lg p-6">
                                    @if($booking->touristAttraction->featured_image)
                                        <img src="{{ asset('storage/attractions/' . $booking->touristAttraction->featured_image) }}"
                                             class="w-full h-48 object-cover rounded-md mb-4" alt="{{ $booking->touristAttraction->name }}">
                                    @endif
                                    <h6 class="text-lg font-semibold text-gray-800">{{ $booking->touristAttraction->name }}</h6>
                                    <p class="text-sm text-gray-500 mt-1">
                                        <i class="bi bi-geo-alt mr-1"></i>{{ $booking->touristAttraction->location }}
                                    </p>
                                    <p class="text-gray-600 mt-2">{{ Str::limit($booking->touristAttraction->description, 100) }}</p>
                                </div>
                            </div>
                            <div>
                                <div class="bg-gray-100 rounded-lg p-6">
                                    <h6 class="text-lg font-semibold text-gray-800 mb-4">Visit Information</h6>
                                    <div class="mb-4">
                                        <small class="text-gray-500">Visit Date:</small><br>
                                        <strong class="text-gray-800">{{ $booking->visit_date->format('d M Y') }}</strong>
                                    </div>
                                    <div class="mb-4">
                                        <small class="text-gray-500">Number of Tickets:</small><br>
                                        <strong class="text-gray-800">{{ $booking->quantity }} ticket(s)</strong>
                                    </div>
                                    <div class="mb-4">
                                        <small class="text-gray-500">Total Amount:</small><br>
                                        <strong class="text-green-600 text-xl">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</strong>
                                    </div>
                                    @if($booking->notes)
                                        <div class="mb-4">
                                            <small class="text-gray-500">Notes:</small><br>
                                            <strong class="text-gray-800">{{ $booking->notes }}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Booking Timeline --}}
                        <div class="mt-4">
                            <h6 class="text-gray-600 font-semibold mb-3">Booking Timeline</h6>
                            <div class="relative pl-6 before:absolute before:left-3 before:top-0 before:h-full before:w-0.5 before:bg-gray-200">
                                <div class="relative mb-6">
                                    <div class="absolute left-0 top-0 w-3 h-3 bg-indigo-500 rounded-full mt-2 -ml-1"></div>
                                    <div class="pl-5">
                                        <small class="text-gray-500 text-sm">{{ $booking->created_at->format('d M Y H:i') }}</small>
                                        <p class="text-gray-800 font-medium">Booking created</p>
                                    </div>
                                </div>
                                
                                @if($booking->status !== 'pending')
                                    <div class="relative mb-6">
                                        <div class="absolute left-0 top-0 w-3 h-3 bg-green-500 rounded-full mt-2 -ml-1"></div>
                                        <div class="pl-5">
                                            <small class="text-gray-500 text-sm">{{ $booking->updated_at->format('d M Y H:i') }}</small>
                                            <p class="text-gray-800 font-medium">Status updated to {{ $booking->status }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="md:w-1/3">
                        <h5 class="text-indigo-600 font-bold mb-3">Actions</h5>
                        <div class="space-y-4">
                            @if($booking->status === 'pending')
                                <button class="w-full py-3 px-6 rounded-md bg-green-500 text-white font-semibold hover:bg-green-600 transition-colors" id="pay-button">
                                    Proceed to Payment
                                </button>
                                <a href="{{ route('bookings.edit', $booking) }}" class="block text-center w-full py-3 px-6 rounded-md bg-yellow-500 text-white font-semibold hover:bg-yellow-600 transition-colors">
                                    Edit Booking
                                </a>
                                <form action="{{ route('bookings.destroy', $booking) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full py-3 px-6 rounded-md bg-red-500 text-white font-semibold hover:bg-red-600 transition-colors" onclick="return confirm('Are you sure you want to cancel this booking?')">
                                        Cancel Booking
                                    </button>
                                </form>
                            @endif

                            @if($booking->status === 'paid')
                                <a href="{{ route('bookings.ticket', $booking) }}" class="block text-center w-full py-3 px-6 rounded-md bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition-colors">
                                    Download Ticket
                                </a>
                            @endif

                            <a href="{{ route('bookings.index') }}" class="block text-center w-full py-3 px-6 rounded-md border border-gray-300 text-gray-600 font-semibold hover:bg-gray-100 transition-colors">
                                Back to Bookings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Midtrans Script --}}
    @if($booking->status === 'pending')
        <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
        <script type="text/javascript">
            document.getElementById('pay-button').onclick = function(){
                // Mengambil token yang sudah ada dari server-side
                const snapToken = "{{ $snapToken }}"; 

                // Jika token ada, tampilkan pop-up Midtrans
                if (snapToken) {
                    snap.pay(snapToken, {
                        onSuccess: function(result){
                            alert("Pembayaran berhasil!");
                            window.location.reload(); // Refresh halaman untuk menampilkan status terbaru
                        },
                        onPending: function(result){
                            alert("Menunggu pembayaran Anda!");
                            window.location.reload();
                        },
                        onError: function(result){
                            alert("Pembayaran gagal!");
                            console.log(result);
                        },
                        onClose: function(){
                            alert('Anda menutup popup tanpa menyelesaikan pembayaran.');
                        }
                    });
                } else {
                    alert('Gagal mendapatkan token pembayaran. Silakan coba lagi.');
                }
            };
        </script>
    @endif
</x-app-layout>