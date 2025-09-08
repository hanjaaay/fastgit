@extends('layouts.public')

@section('content')
<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-10 text-center">
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight tracking-tight">
                Pesan Tiketmu Sekarang!
            </h1>
            <p class="mt-4 text-lg text-gray-600">
                Amankan tiketmu untuk atraksi terbaik di seluruh Indonesia.
            </p>
        </div>

        @if($attractions->isEmpty())
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded-md relative text-center" role="alert">
                <strong class="font-bold">Info:</strong>
                <span class="block sm:inline ml-2">Maaf, saat ini tidak ada atraksi yang tersedia.</span>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($attractions as $attraction)
                    <div class="bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 overflow-hidden">
                        {{-- Penanganan gambar featured_image --}}
                        @if($attraction->featured_image)
                            <div class="relative w-full h-56">
                                <img src="{{ asset('storage/' . $attraction->featured_image) }}"
                                     class="w-full h-full object-cover"
                                     alt="{{ $attraction->name }}">
                                <div class="absolute inset-0 bg-gradient-to-t from-gray-900 to-transparent opacity-50"></div>
                            </div>
                        @else
                            {{-- Placeholder jika tidak ada gambar featured_image --}}
                            <div class="w-full h-56 bg-gray-200 flex items-center justify-center text-gray-500">
                                <i class="fas fa-image text-5xl"></i>
                            </div>
                        @endif

                        <div class="p-6">
                            <div class="flex items-center text-sm text-gray-500 mb-2">
                                <i class="fas fa-map-marker-alt mr-2 text-indigo-500"></i>
                                {{ $attraction->city }}, {{ $attraction->province }}
                            </div>
                            <h2 class="text-2xl font-bold text-gray-800 mb-2">{{ $attraction->name }}</h2>
                            <p class="text-gray-600 mb-4">{{ Str::limit($attraction->description, 100) }}</p>

                            @if($attraction->facilities && is_array($attraction->facilities) && count($attraction->facilities) > 0)
                                <div class="mt-4">
                                    <small class="text-gray-500 font-medium">Fasilitas:</small>
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        @foreach($attraction->facilities as $facility)
                                            <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-3 py-1 rounded-full">{{ $facility }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="p-6 border-t border-gray-200 flex justify-between items-center bg-gray-50">
                            <div>
                                <span class="text-sm text-gray-500 block">Mulai dari</span>
                                <div class="text-2xl font-bold text-indigo-600">
                                    @if($attraction->tickets->isNotEmpty())
                                        Rp {{ number_format($attraction->tickets->first()->price, 0, ',', '.') }}
                                    @else
                                        Harga di Detail
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('attractions.show', $attraction) }}"
                               class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-full transition duration-300 transform hover:scale-105 shadow-md">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- PERBAIKAN: Tautan Pagination --}}
            @if($attractions instanceof \Illuminate\Pagination\LengthAwarePaginator)
                <div class="mt-12 flex justify-center">
                    {{ $attractions->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
@endpush
@endsection