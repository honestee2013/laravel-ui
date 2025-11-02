            <!--Error Messages -->
            @if ($errors->any() || session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show text-white" role="alert">
                    <span class="alert-icon"><i class="fas fa-exclamation-triangle" style="font-size:2em"></i></span>
                    <span class="alert-text">&nbsp;<strong>Error!</strong>
                        @if ($errors->any())
                            <ul class="list-unstyled mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    @if(str_contains($error, 'multi select form fields.'))
                                        <li>{{ str_replace('multi select form fields.', ' ', $error) }}</li>
                                    @else
                                        <li>{{ str_replace('fields.', ' ', $error) }}</li>
                                    @endif

                                @endforeach

                            </ul>
                        @else
                            {{ session('error') }}
                        @endif





                    </span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if (Session::has('success_message'))
                <div class="alert alert-success alert-dismissible fade show text-white" role="alert">
                    <span class="alert-icon"><i class="fas fa-thumbs-up" style="font-size:2em"></i></span>
                    <span class="alert-text">&nbsp;<strong>Success!</strong> {!! session('success_message') !!}

                    </span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif




