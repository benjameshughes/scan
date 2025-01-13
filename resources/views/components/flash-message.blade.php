<div>
    @if($message)
        <div class="alert alert-{{ $type }} alert-dismissible fade show flash-message" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
</div>