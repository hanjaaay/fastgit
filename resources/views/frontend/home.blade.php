<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SYAMSUITE Hotel</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Optional: Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}">SYAMSUITE</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Rooms</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Facilities</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Contact</a></li>
                    <li class="nav-item"><a class="btn btn-primary ms-2" href="{{ route('login') }}">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-dark text-light text-center p-5" style="background: url('https://source.unsplash.com/1600x600/?hotel,resort') no-repeat center/cover;">
        <div class="bg-dark bg-opacity-50 p-5 rounded">
            <h1 class="display-4 fw-bold">Welcome to SYAMSUITE</h1>
            <p class="lead">Experience luxury and comfort in the heart of the city.</p>
            <a href="#" class="btn btn-primary btn-lg mt-3">Book Now</a>
        </div>
    </section>

    <!-- Rooms Section -->
    <section class="container my-5">
        <h2 class="text-center mb-4">Our Rooms</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <img src="https://source.unsplash.com/600x400/?hotel-room" class="card-img-top" alt="Room">
                    <div class="card-body">
                        <h5 class="card-title">Deluxe Room</h5>
                        <p class="card-text">Spacious room with king-size bed, city view, and free Wi-Fi.</p>
                        <a href="#" class="btn btn-outline-primary">Book</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <img src="https://source.unsplash.com/600x400/?suite" class="card-img-top" alt="Room">
                    <div class="card-body">
                        <h5 class="card-title">Executive Suite</h5>
                        <p class="card-text">Luxury suite with living room, private balcony, and premium services.</p>
                        <a href="#" class="btn btn-outline-primary">Book</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <img src="https://source.unsplash.com/600x400/?resort-room" class="card-img-top" alt="Room">
                    <div class="card-body">
                        <h5 class="card-title">Family Room</h5>
                        <p class="card-text">Perfect for families, includes two bedrooms and a shared lounge.</p>
                        <a href="#" class="btn btn-outline-primary">Book</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light text-center p-4">
        <p class="mb-0">&copy; {{ date('Y') }} SYAMSUITE Hotel. All Rights Reserved.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
