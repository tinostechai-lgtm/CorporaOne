<section class="mk-hero">
    <div class="container">
        <div class="row align-items-center">
            
            <div class="col-xl-6">
                @if($hero->video)
                    <video controls width="100%">
                        <source src="{{ asset('storage/' . $hero->video) }}" type="video/mp4">
                    </video>
                @endif
            </div>
        </div>
    </div>
</section>