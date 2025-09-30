<div>





            <!--Error Messages -->
            @if ($message && isset($message["error"]))
                <div class="alert alert-danger alert-dismissible fade show text-white" role="alert">
                    <span class="alert-icon"><i class="fas fa-exclamation-triangle" style="font-size:2em"></i></span>
                    <span class="alert-text">&nbsp;<strong>Error!</strong>

                            <ul class="list-unstyled mb-0 mt-2">

                                <li>{{ $message["error"] }}</li>

                            </ul>


                    </span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if ($message && isset($message["success"]))

                <div class="alert alert-success alert-dismissible fade show text-white" role="alert">
                    <span class="alert-icon"><i class="fas fa-thumbs-up" style="font-size:2em"></i></span>
                    <span class="alert-text">&nbsp;<strong>Success!</strong> {{ $message["success"]}}

                    </span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif









</div>
