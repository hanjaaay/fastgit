@extends('layouts.public')

@section('content')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/attraction-detail.css') }}">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIINfQSi7dL+yQalTbtQQpt/LfX8ZCfdjRY=" crossorigin=""/>
<style>
    #mapid {
        height: 400px;
        width: 100%;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(229, 231, 235, 1);
    }
    /* Menghilangkan border dan radius card Bootstrap */
    .card-footer {
        border-top: none;
        border-radius: 0;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJEQ2PW4QAzNAyogjQe71sCL6ayNc=" crossorigin=""></script>
<script src="{{ asset('js/attraction-detail.js') }}"></script>
<script>
    @if($attraction->latitude && $attraction->longitude)
        var map = L.map('mapid').setView([{{ $attraction->latitude }}, {{ $attraction->longitude }}], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        L.marker([{{ $attraction->latitude }}, {{ $attraction->longitude }}])
            .addTo(map)
            .bindPopup("<b>{{ $attraction->name }}</b><br>{{ $attraction->location }}")
            .openPopup();
    @endif
</script>
@endpush
<div class="hero-section relative bg-cover bg-center h-[450px] md:h-[600px] lg:h-[750px] overflow-hidden"
    style="background-image: url('{{ $attraction->featured_image ? asset('storage/' . $attraction->featured_image) : asset('path/to/default/hero_image.jpg') }}');">
    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/60 to-transparent"></div>
    <div class="relative z-10 flex flex-col items-center justify-end h-full pb-32 px-4">
        <div class="max-w-4xl text-center">
            
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold text-white text-center drop-shadow-3xl leading-tight mb-6">
                {{ $attraction->name }}
            </h1>
            <div class="flex items-center justify-center space-x-6 text-white">
                <div class="flex items-center">
                    <i class="fas fa-map-marker-alt text-2xl mr-2"></i>
                    
                    <span class="text-xl">{{ $attraction->city }}, {{ $attraction->province }}</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-clock text-2xl mr-2"></i>
                    <span class="text-xl">
                        
                        @if($attraction->opening_hours && $attraction->closing_hours)
                            {{ \Carbon\Carbon::parse($attraction->opening_hours)->format('H:i') }} - {{ \Carbon\Carbon::parse($attraction->closing_hours)->format('H:i') }}
                        @else
                            Jam Operasional Tidak Tersedia
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="py-12 bg-gray-50 -mt-20 relative z-20 rounded-t-3xl shadow-3xl">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <div class="lg:w-2/3">
                <div class="bg-white overflow-hidden shadow-4xl sm:rounded-lg mb-8 border border-gray-100">
                    <div class="p-8">
                        <div class="flex justify-between items-center mb-8">
                            <a href="{{ route('attractions.index') }}" class="inline-flex items-center text-indigo-700 hover:text-indigo-900 font-semibold transition duration-300 transform hover:-translate-x-1">
                                <i class="fas fa-arrow-left mr-2"></i> <span>Back to All Attractions</span>
                            </a>
                            <div class="flex items-center space-x-4">
                                <button class="share-button p-2 text-gray-600 hover:text-indigo-600 transition-colors">
                                    <i class="fas fa-share-alt"></i>
                                </button>
                                <button class="wishlist-button p-2 text-gray-600 hover:text-red-600 transition-colors">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                        </div>

                        @if($attraction->description)
                            <div class="mb-12">
                                <h3 class="text-2xl font-bold text-gray-900 mb-4">About This Place</h3>
                                
                                <p class="text-gray-700 leading-relaxed">{{ $attraction->description }}</p>
                            </div>
                        @endif

                        <div class="mb-12">
                            <h3 class="text-2xl font-bold text-gray-900 mb-4">Photo Gallery</h3>
                            
                            @if($attraction->gallery && is_array($attraction->gallery) && count($attraction->gallery) > 0)
                                <div class="photo-gallery grid grid-cols-2 md:grid-cols-3 gap-4">
                                    @foreach($attraction->gallery as $imagePath)
                                        <div class="gallery-item">
                                            <img src="{{ asset('storage/' . $imagePath) }}"
                                                alt="{{ $attraction->name }} gallery image"
                                                class="object-cover w-full h-full rounded-md shadow-sm">
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-600">No additional photos available.</p>
                            @endif
                        </div>

                        <div class="mb-12">
                            <h3 class="text-2xl font-bold text-gray-900 mb-4">Facilities</h3>
                            
                            @if($attraction->facilities && is_array($attraction->facilities) && count($attraction->facilities) > 0)
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                    @foreach($attraction->facilities as $facility)
                                        <div class="flex items-center space-x-2 text-gray-700">
                                            <i class="fas fa-check-circle text-green-500"></i>
                                            <span>{{ $facility }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-600">No facilities listed.</p>
                            @endif
                        </div>

                        <div class="mb-12">
                            <h3 class="text-2xl font-bold text-gray-900 mb-4">Location</h3>
                            
                            @if($attraction->latitude && $attraction->longitude)
                                <div id="mapid" class="map-container"></div>
                            @else
                                <div class="w-full h-[400px] bg-gray-200 rounded-lg overflow-hidden shadow-xl border border-gray-100 flex items-center justify-center">
                                    <span class="text-gray-400">Map data not available.</span>
                                </div>
                            @endif
                        </div>

                        <div class="mb-12">
                            <h3 class="text-2xl font-bold text-gray-900 mb-4">Ticket Options</h3>
                            
                            @if($attraction->tickets->isNotEmpty())
                                <div class="space-y-4">
                                    
                                    @foreach($attraction->tickets->sortBy('price') as $ticket)
                                        <div class="bg-blue-50 p-4 rounded-lg shadow-sm flex justify-between items-center border border-blue-100">
                                            <div>
                                                <h4 class="font-semibold text-lg text-blue-800">{{ $ticket->name }}</h4>
                                                <p class="text-blue-700">{{ $ticket->description }}</p>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-2xl font-bold text-blue-900">Rp {{ number_format($ticket->price, 0, ',', '.') }}</div>
                                                @if($ticket->notes)
                                                    <small class="text-blue-600">{{ $ticket->notes }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-gray-600">No ticket options listed yet.</p>
                            @endif
                        </div>

                        <div class="mb-12">
                            <h3 class="text-2xl font-bold text-gray-900 mb-4">Customer Reviews</h3>
                            
                            @if($attraction->reviews->isEmpty())
                                <p class="text-gray-600">No reviews yet. Be the first to review this amazing attraction!</p>
                            @else
                                <div class="space-y-6">
                                    
                                    @foreach($attraction->reviews as $review)
                                        <div class="review-card bg-gray-50 rounded-lg p-6">
                                            <div class="flex items-center mb-4">
                                                <div class="flex-shrink-0">
                                                    @if($review->user && $review->user->avatar)
                                                         <img src="{{ asset('storage/avatars/' . $review->user->avatar) }}"
                                                             alt="{{ $review->user->name }}"
                                                             class="h-12 w-12 rounded-full object-cover">
                                                    @else
                                                         <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                                                             {{ $review->user ? substr($review->user->name, 0, 1) : '?' }}
                                                         </div>
                                                    @endif
                                                </div>
                                                <div class="ml-4">
                                                    <p class="font-semibold text-gray-900">{{ $review->user ? $review->user->name : 'Anonymous' }}</p>
                                                    <p class="text-sm text-gray-500">{{ $review->created_at->format('d M Y') }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center mb-2">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="rating-star h-5 w-5 {{ $i <= $review->rating ? 'text-amber-500' : 'text-gray-300' }}"
                                                         fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                @endfor
                                            </div>
                                            <p class="text-gray-700">{{ $review->comment }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:w-1/3">
                <div class="sticky top-8">
                    <div class="booking-card bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden">
                        <div class="p-6">
                            <div class="price-animation text-3xl font-bold text-gray-900 mb-4">
                                
                                @if($attraction->tickets->isNotEmpty())
                                    Rp {{ number_format($attraction->tickets->first()->price, 0, ',', '.') }}
                                    <span class="text-sm font-normal text-gray-500">/person (Starting From)</span>
                                @else
                                    <span class="text-lg text-gray-500">No Tickets Available</span>
                                @endif
                            </div>

                            <div class="space-y-4 mb-6">
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-clock w-6"></i>
                                    <span class="ml-2">
                                        
                                        @if($attraction->opening_hours && $attraction->closing_hours)
                                            {{ \Carbon\Carbon::parse($attraction->opening_hours)->format('H:i') }} - {{ \Carbon\Carbon::parse($attraction->closing_hours)->format('H:i') }}
                                        @else
                                            Jam Operasional Tidak Tersedia
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-map-marker-alt w-6"></i>
                                    
                                    <span class="ml-2">{{ $attraction->city }}, {{ $attraction->province }}</span>
                                </div>
                            </div>
                            
                            {{-- Hanya tampilkan tombol ini --}}
                            @auth
                                
                                <a href="{{ route('bookings.create', $attraction) }}"
                                   class="booking-button block w-full py-4 px-6 text-center text-white bg-red-600 hover:bg-red-700 rounded-lg font-bold text-lg transition duration-300 transform hover:scale-105 shadow-lg">
                                    <i class="fas fa-ticket-alt mr-2"></i>
                                    Book Now - Rp {{ number_format($ticketPriceForButton, 0, ',', '.') }}
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                   class="booking-button block w-full py-4 px-6 text-center text-white bg-red-600 hover:bg-red-700 rounded-lg font-bold text-lg transition duration-300 transform hover:scale-105 shadow-lg">
                                    <i class="fas fa-sign-in-alt mr-2"></i>
                                    Login to Book
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-12">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">You Might Also Like</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @forelse($relatedAttractions as $relatedAttraction)
                    {{-- Mengubah Bootstrap card menjadi Tailwind card --}}
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden h-full transform hover:scale-105 transition duration-300">
                        @if($relatedAttraction->featured_image)
                            <img src="{{ asset('storage/' . $relatedAttraction->featured_image) }}"
                                class="w-full h-48 object-cover"
                                alt="{{ $relatedAttraction->name }}">
                        @else
                            <div class="bg-gray-200 w-full h-48 flex items-center justify-center text-gray-400 text-6xl">
                                <i class="fas fa-image"></i>
                            </div>
                        @endif
                        <div class="p-4">
                            <h5 class="font-bold text-xl text-gray-900 mb-1">{{ $relatedAttraction->name }}</h5>
                            <p class="text-gray-600 text-sm mb-2 flex items-center">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                {{ $relatedAttraction->city }}, {{ $relatedAttraction->province }}
                            </p>
                            <p class="text-gray-500 text-sm">{{ Str::limit($relatedAttraction->description, 50) }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                            <div>
                                <small class="text-gray-500">Starting from</small>
                                <div class="font-bold text-lg text-indigo-600">
                                    @if($relatedAttraction->tickets->isNotEmpty())
                                        Rp {{ number_format($relatedAttraction->tickets->first()->price, 0, ',', '.') }}
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('attractions.show', $relatedAttraction) }}"
                                class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition duration-300">
                                View Detail
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="col-span-full text-gray-600 text-center">No related attractions found.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection