


    <div class="container">
        <h2>Hero Section Settings</h2>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('hero.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Description</label>
                <textarea name="description" class="form-control" required></textarea>
            </div>

            <div class="mb-3">
                <label>Button Text</label>
                <input type="text" name="button_text" class="form-control">
            </div>

            <div class="mb-3">
                <label>Button Link</label>
                <input type="url" name="button_link" class="form-control">
            </div>

            <div class="mb-3">
                <label>Upload Video (MP4, MOV, AVI)</label>
                <input type="file" name="video" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
        </form>

        @if($hero)
            <h3 class="mt-5">Current Hero Section</h3>
            <h4>{{ $hero->title }}</h4>
            <p>{{ $hero->description }}</p>

            @if($hero->video)
                <video controls width="400">
                    <source src="{{ asset('storage/' . $hero->video) }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            @endif

            <form action="{{ route('hero.update', $hero->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="submit" class="btn btn-warning mt-3" value="Update">
            </form>

            <form action="{{ route('hero.delete', $hero->id) }}" method="POST">
                @csrf
                <input type="submit" class="btn btn-danger mt-3" value="Delete">
            </form>
        @endif
    </div>

