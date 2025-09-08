@extends('layouts.public')

@section('content')
<div class="container mx-auto py-12">
    <div class="bg-white p-6 rounded-lg shadow-lg max-w-lg mx-auto text-center">
        <h1 class="text-2xl font-bold mb-4">Selesaikan Pembayaran Anda</h1>
        <p class="text-gray-600 mb-6">Total yang harus dibayar: Rp {{ number_format($booking->total_price, 0, ',', '.') }}</p>

        <button id="pay-button" class="bg-indigo-600 text-white font-bold py-3 px-6 rounded-lg shadow-md hover:bg-indigo-700 transition duration-300">
            Bayar dengan Midtrans
        </button>

        <p class="mt-4 text-sm text-gray-500">
            Pembayaran akan diproses melalui Midtrans Snap.
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script type="text/javascript">
    document.getElementById('pay-button').onclick = function(){
        // Gunakan token Snap yang dikirim dari controller
        snap.pay('{{ $snapToken }}', {
            onSuccess: function(result){
                // Aksi setelah pembayaran berhasil
                alert("Pembayaran berhasil!");
                console.log(result);
                window.location.href = "{{ route('bookings.show', $booking->id) }}";
            },
            onPending: function(result){
                // Aksi jika pembayaran tertunda
                alert("Pembayaran tertunda, silakan selesaikan pembayaran Anda!");
                console.log(result);
            },
            onError: function(result){
                // Aksi jika pembayaran gagal
                alert("Pembayaran gagal!");
                console.log(result);
            }
        });
    };
</script>
@endpush